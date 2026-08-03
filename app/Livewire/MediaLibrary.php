<?php

namespace App\Livewire;

use Illuminate\Support\Facades\File;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('')]
class MediaLibrary extends Component
{
    use WithFileUploads;

    public string $search = '';

    public string $filterType = 'all';

    public string $filterFolder = 'all';

    public ?string $selectedPath = null;

    public bool $showUpload = false;

    public bool $showDetail = false;

    public $uploadFile;

    public array $selected = [];

    public string $sortBy = 'newest';

    public array $mediaFiles = [];

    public array $filteredFiles = [];

    public array $folders = [];

    public array $typeCounts = ['all' => 0, 'image' => 0, 'document' => 0, 'other' => 0];

    public ?array $selectedFile = null;

    private static array $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico', 'tiff', 'avif'];

    private static array $docExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'];

    public function mount(): void
    {
        $this->scanMedia();
    }

    public function scanMedia(): void
    {
        $this->mediaFiles = [];
        $directories = [
            public_path('images'),
            public_path('docs'),
            public_path('uploads'),
        ];

        foreach ($directories as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            $this->scanDirectory($dir, $dir);
        }

        $this->recompute();
    }

    public function recompute(): void
    {
        $this->filteredFiles = $this->computeFilteredFiles();
        $this->folders = array_values(array_unique(array_column($this->mediaFiles, 'folder')));
        $this->typeCounts = [
            'all' => count($this->mediaFiles),
            'image' => count(array_filter($this->mediaFiles, fn ($f) => $f['type'] === 'image')),
            'document' => count(array_filter($this->mediaFiles, fn ($f) => $f['type'] === 'document')),
            'other' => count(array_filter($this->mediaFiles, fn ($f) => $f['type'] === 'other')),
        ];
    }

    private function scanDirectory(string $baseDir, string $currentDir): void
    {
        $items = File::allFiles($currentDir);

        foreach ($items as $file) {
            $ext = strtolower($file->getExtension());
            $relativePath = ltrim(str_replace(public_path(), '', $file->getPathname()), '\\/');
            $webPath = str_replace('\\', '/', $relativePath);

            $type = match (true) {
                in_array($ext, self::$imageExts) => 'image',
                in_array($ext, self::$docExts) => 'document',
                default => 'other',
            };

            $folder = 'root';
            $parts = explode('/', $webPath);
            if (count($parts) > 1) {
                $folder = $parts[0];
                if (count($parts) > 2) {
                    $folder .= '/'.$parts[1];
                }
            }

            $size = $file->getSize();
            $dimensions = null;
            if ($type === 'image' && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'avif'])) {
                $dimensions = @getimagesize($file->getPathname());
            }

            $this->mediaFiles[] = [
                'name' => $file->getFilename(),
                'path' => $webPath,
                'url' => asset($webPath),
                'type' => $type,
                'ext' => $ext,
                'folder' => $folder,
                'size' => $size,
                'size_human' => $this->formatBytes($size),
                'width' => $dimensions ? $dimensions[0] : null,
                'height' => $dimensions ? $dimensions[1] : null,
                'modified' => $file->getMTime(),
                'modified_human' => date('M d, Y H:i', $file->getMTime()),
            ];
        }
    }

    public function updatedSearch(): void
    {
        $this->recompute();
    }

    public function updatedFilterType(): void
    {
        $this->recompute();
    }

    public function updatedFilterFolder(): void
    {
        $this->recompute();
    }

    public function updatedSortBy(): void
    {
        $this->recompute();
    }

    private function computeFilteredFiles(): array
    {
        $files = $this->mediaFiles;

        if ($this->search !== '') {
            $q = strtolower($this->search);
            $files = array_filter($files, fn ($f) => str_contains(strtolower($f['name']), $q) || str_contains(strtolower($f['path']), $q));
        }

        if ($this->filterType !== 'all') {
            $files = array_filter($files, fn ($f) => $f['type'] === $this->filterType);
        }

        if ($this->filterFolder !== 'all') {
            $files = array_filter($files, fn ($f) => $f['folder'] === $this->filterFolder);
        }

        $files = array_values($files);

        usort($files, match ($this->sortBy) {
            'oldest' => fn ($a, $b) => $a['modified'] <=> $b['modified'],
            'largest' => fn ($a, $b) => $b['size'] <=> $a['size'],
            'smallest' => fn ($a, $b) => $a['size'] <=> $b['size'],
            default => fn ($a, $b) => $b['modified'] <=> $a['modified'],
        });

        return $files;
    }

    public function selectFile(string $path): void
    {
        $this->selectedPath = $path;
        $this->showDetail = true;

        foreach ($this->mediaFiles as $file) {
            if ($file['path'] === $path) {
                $this->selectedFile = $file;

                return;
            }
        }
        $this->selectedFile = null;
    }

    public function openDetail(): void
    {
        $this->showDetail = true;
    }

    public function showDetail(): void
    {
        $this->showDetail = true;
    }

    public function closeDetail(): void
    {
        $this->showDetail = false;
        $this->selectedPath = null;
        $this->selectedFile = null;
    }

    public function getSelectedFile(): ?array
    {
        return $this->selectedFile;
    }

    public function copyPath(string $path): void
    {
        $this->dispatch('copy-to-clipboard', path: $path);
    }

    public function openUpload(): void
    {
        $this->showUpload = true;
    }

    public function showUpload(): void
    {
        $this->showUpload = true;
    }

    public function hideUpload(): void
    {
        $this->showUpload = false;
    }

    public function closeUpload(): void
    {
        $this->showUpload = false;
    }

    public function toggleUpload(): void
    {
        $this->showUpload = ! $this->showUpload;
    }

    public function uploadMedia(): void
    {
        $this->validate([
            'uploadFile' => 'required|file|max:10240',
        ]);

        $file = $this->uploadFile;
        $dir = public_path('uploads');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $name = $file->getClientOriginalName();
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name);
        $name = ltrim($name, '-');

        $file->move($dir, $name);

        $this->scanMedia();
        $this->uploadFile = null;
        $this->showUpload = false;
    }

    public function deleteFile(string $path): void
    {
        $fullPath = public_path($path);
        if (file_exists($fullPath) && is_file($fullPath)) {
            unlink($fullPath);
            $this->scanMedia();
        }
        $this->closeDetail();
    }

    public function render()
    {
        return view('livewire.media-library');
    }

    private function formatBytes(int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= 1 << (10 * $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}

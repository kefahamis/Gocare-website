<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

trait MediaLibraryState
{
    public string $search = '';

    public string $filterType = 'all';

    public string $filterFolder = 'all';

    public ?string $selectedPath = null;

    public bool $uploadOpen = false;

    public bool $detailOpen = false;

    public $uploadFile;

    public array $selected = [];

    public string $sortBy = 'newest';

    public int $perPage = 24;

    public int $currentPage = 1;

    public int $totalPages = 1;

    public int $totalFiles = 0;

    public array $mediaFiles = [];

    public array $filteredFiles = [];

    public array $pageFiles = [];

    public array $folders = [];

    public array $typeCounts = ['all' => 0, 'image' => 0, 'document' => 0, 'other' => 0];

    public ?array $selectedFile = null;

    protected array $mediaFileMap = [];

    private static array $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico', 'tiff', 'avif'];

    private static array $docExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'];

    public function mount(): void
    {
        $this->mediaFiles = $this->loadMediaIndex();
        $this->recompute();
    }

    public function scanMedia(): void
    {
        Cache::forget($this->mediaCacheKey());
        $this->mediaFiles = $this->buildMediaIndex();
        $this->recompute();
        $this->dispatch('media-library-updated', files: $this->mediaFiles);
    }

    public function recompute(): void
    {
        $this->filteredFiles = $this->computeFilteredFiles();
        $this->totalFiles = count($this->filteredFiles);
        $this->totalPages = max(1, (int) ceil($this->totalFiles / max(1, $this->perPage)));
        $this->currentPage = max(1, min($this->currentPage, $this->totalPages));
        $offset = ($this->currentPage - 1) * $this->perPage;
        $this->pageFiles = array_slice($this->filteredFiles, $offset, $this->perPage);
        $folders = [];
        $counts = ['all' => 0, 'image' => 0, 'document' => 0, 'other' => 0];
        $this->mediaFileMap = [];

        foreach ($this->mediaFiles as $file) {
            $counts['all']++;
            $counts[$file['type']]++;
            $folders[$file['folder']] = true;
            $this->mediaFileMap[$file['path']] = $file;
        }

        $this->folders = array_values(array_keys($folders));
        $this->typeCounts = $counts;
    }

    protected function loadMediaIndex(): array
    {
        return Cache::remember($this->mediaCacheKey(), now()->addMinutes(10), function () {
            return $this->buildMediaIndex();
        });
    }

    protected function buildMediaIndex(): array
    {
        $mediaFiles = [];
        $directories = [
            public_path('images'),
            public_path('docs'),
            public_path('uploads'),
        ];

        foreach ($directories as $dir) {
            if (! is_dir($dir)) {
                continue;
            }

            foreach (File::allFiles($dir) as $file) {
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

                $mediaFiles[] = [
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

        return $mediaFiles;
    }

    protected function mediaCacheKey(): string
    {
        return 'media-library.index.'.md5(static::class);
    }

    public function updatedSearch(): void
    {
        $this->currentPage = 1;
        $this->recompute();
    }

    public function updatedFilterType(): void
    {
        $this->currentPage = 1;
        $this->recompute();
    }

    public function updatedFilterFolder(): void
    {
        $this->currentPage = 1;
        $this->recompute();
    }

    public function updatedSortBy(): void
    {
        $this->currentPage = 1;
        $this->recompute();
    }

    public function goToMediaPage(int $page): void
    {
        $this->currentPage = max(1, min($page, $this->totalPages));
        $this->recompute();
    }

    public function nextMediaPage(): void
    {
        $this->goToMediaPage($this->currentPage + 1);
    }

    public function previousMediaPage(): void
    {
        $this->goToMediaPage($this->currentPage - 1);
    }

    public function firstMediaPage(): void
    {
        $this->goToMediaPage(1);
    }

    public function lastMediaPage(): void
    {
        $this->goToMediaPage($this->totalPages);
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
        $this->detailOpen = true;
        $this->selectedFile = $this->mediaFileMap[$path] ?? null;
    }

    public function openDetail(): void
    {
        $this->detailOpen = true;
    }

    public function detailOpen(): void
    {
        $this->openDetail();
    }

    public function showDetail(): void
    {
        $this->detailOpen = true;
    }

    public function closeDetail(): void
    {
        $this->detailOpen = false;
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
        $this->uploadOpen = true;
    }

    public function showUpload(): void
    {
        $this->uploadOpen = true;
    }

    public function hideUpload(): void
    {
        $this->uploadOpen = false;
    }

    public function closeUpload(): void
    {
        $this->uploadOpen = false;
    }

    public function toggleUpload(): void
    {
        $this->uploadOpen = ! $this->uploadOpen;
    }

    public function uploadMedia(): void
    {
        $this->validate([
            'uploadFile' => 'required|file|max:10240',
        ]);

        $file = $this->uploadFile;
        $dir = public_path('uploads');
        File::ensureDirectoryExists($dir);

        $baseName = pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
        $baseName = preg_replace('/[^a-zA-Z0-9._-]/', '-', $baseName);
        $baseName = preg_replace('/-+/', '-', (string) $baseName);
        $baseName = trim((string) $baseName, '-_.');
        $baseName = $baseName !== '' ? $baseName : 'upload';

        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->guessExtension() ?: ''));
        $name = $extension !== '' ? "{$baseName}.{$extension}" : $baseName;
        $targetPath = $dir.DIRECTORY_SEPARATOR.$name;

        $counter = 1;
        while (file_exists($targetPath)) {
            $suffix = "-{$counter}";
            $name = $extension !== '' ? "{$baseName}{$suffix}.{$extension}" : "{$baseName}{$suffix}";
            $targetPath = $dir.DIRECTORY_SEPARATOR.$name;
            $counter++;
        }

        if (! copy($file->getRealPath(), $targetPath)) {
            throw new \RuntimeException('Unable to save the uploaded file.');
        }

        $this->scanMedia();
        $this->dispatch('media-library-updated', files: $this->mediaFiles);
        $this->dispatch('media-library-toast', message: "Uploaded {$name} successfully.");
        $this->uploadFile = null;
        $this->uploadOpen = false;
    }

    public function deleteFile(string $path): void
    {
        $fullPath = public_path($path);
        if (file_exists($fullPath) && is_file($fullPath)) {
            $deletedName = basename($fullPath);
            unlink($fullPath);
            $this->scanMedia();
            $this->dispatch('media-library-updated', files: $this->mediaFiles);
            $this->dispatch('media-library-toast', message: "Deleted {$deletedName} successfully.");
        }
        $this->closeDetail();
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class MediaPickerController extends Controller
{
    public function browse(): JsonResponse
    {
        $directories = [
            public_path('images'),
            public_path('docs'),
            public_path('uploads'),
        ];

        $files = [];
        $allowedExtensions = [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico',
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv',
        ];

        foreach ($directories as $dir) {
            if (! is_dir($dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $extension = strtolower($file->getExtension());

                if (! in_array($extension, $allowedExtensions, true)) {
                    continue;
                }

                $relativePath = str_replace(public_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
                $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

                $size = $file->getSize();
                $files[] = [
                    'name' => $file->getFilename(),
                    'path' => $relativePath,
                    'ext' => $extension,
                    'size' => $size,
                    'sizeFormatted' => self::formatBytes($size),
                    'is_image' => in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'], true),
                    'url' => '/'.$relativePath,
                    'modified' => $file->getMTime(),
                ];
            }
        }

        usort($files, fn ($a, $b) => $b['modified'] <=> $a['modified']);

        return response()->json($files);
    }

    private static function formatBytes(int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}

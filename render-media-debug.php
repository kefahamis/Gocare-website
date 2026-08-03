<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = new App\Livewire\MediaLibrary();
$c->mount();

$html = view('livewire.media-library', [
    'files' => $c->mediaFiles,
    'folders' => $c->folders,
    'typeCounts' => $c->typeCounts,
    'selectedPath' => null,
    'selectedFile' => null,
    'showUpload' => false,
    'showDetail' => false,
    'filterType' => 'all',
    'filterFolder' => 'all',
    'sortBy' => 'newest',
    'search' => '',
    'mediaFiles' => $c->mediaFiles,
])->render();

if (str_contains($html, 'file://')) {
    echo "FILE:// FOUND\n";
    preg_match_all('/[^\s"\']*file:\/\/[^\s"\']*/', $html, $m);
    print_r(array_unique($m[0]));
} else {
    echo "No file:// references\n";
}

if (str_contains($html, "copyPath('")) {
    echo "BROKEN: inline copyPath('...') with raw quotes\n";
}

$broken = preg_match_all('/@js\(/', $html, $m);
echo "js() usages: " . $broken . "\n";

file_put_contents(__DIR__ . '/storage/app/media-debug.html', $html);
echo "HTML saved (" . strlen($html) . " bytes)\n";

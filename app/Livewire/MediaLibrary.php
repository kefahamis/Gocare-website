<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Support\MediaLibraryState;

#[Layout('')]
class MediaLibrary extends Component
{
    use MediaLibraryState;
    use WithFileUploads;

    public function render()
    {
        return view('livewire.media-library');
    }
}

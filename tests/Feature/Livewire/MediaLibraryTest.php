<?php

namespace Tests\Feature\Livewire;

use App\Livewire\MediaLibrary;
use Livewire\Livewire;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(MediaLibrary::class)
            ->assertStatus(200);
    }
}

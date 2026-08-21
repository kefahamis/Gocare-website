<?php

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class MenuBuilder extends Field
{
    protected string $view = 'filament.forms.components.menu-builder';

    protected Closure|string|null $menuLocation = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated();
    }

    public function menuLocation(Closure|string|null $location): static
    {
        $this->menuLocation = $location;

        return $this;
    }

    public function getMenuLocation(): ?string
    {
        return $this->menuLocation === null ? null : $this->evaluate($this->menuLocation);
    }

    public function getViewData(): array
    {
        return [
            ...parent::getViewData(),
            'menuLocation' => $this->getMenuLocation(),
        ];
    }
}

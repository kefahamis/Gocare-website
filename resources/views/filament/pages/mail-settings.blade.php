<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">Save</x-filament::button>
        </div>
    </form>

    @php($settings = \App\Models\MailSetting::current())

    @if ($settings->last_tested_at)
        <x-filament::section class="mt-6" :heading="'Last test'">
            <p class="text-sm">
                Sent to <strong>{{ $settings->last_tested_to }}</strong>
                {{ $settings->last_tested_at->diffForHumans() }}.
            </p>

            @if ($settings->last_test_error)
                {{-- The provider's own words: that is what identifies a wrong
                     password, a blocked port or an unverified sender. --}}
                <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">
                    Failed: {{ $settings->last_test_error }}
                </p>
            @else
                <p class="mt-2 text-sm text-success-600 dark:text-success-400">
                    Delivered to the mail server without error.
                </p>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>

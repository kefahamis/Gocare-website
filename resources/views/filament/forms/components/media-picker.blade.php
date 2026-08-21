{{-- MediaPicker form field: input + preview + browse button --}}
@php
    $statePath = $getStatePath();
    $state = $getState();
    $placeholder = $getPlaceholder();
    $maxLength = $getMaxLength();
    $previewUrl = null;
    if (!empty($state)) {
        $previewUrl = \App\Models\Slider::publicAssetUrl($state);
    }
@endphp

<div
    x-data="{
        showPicker: false,
        mediaFiles: [],
        mediaSearch: '',
        mediaLoading: false,
        previewUrl: @js($previewUrl),
        selectedPath: @js($state),
        updatePreview(val) {
            this.selectedPath = val;
            if (val && (val.endsWith('.jpg') || val.endsWith('.jpeg') || val.endsWith('.png') || val.endsWith('.gif') || val.endsWith('.webp') || val.endsWith('.svg'))) {
                this.previewUrl = '/' + val.replace(/^\/+/, '');
            } else {
                this.previewUrl = null;
            }
        },
        async openPicker() {
            this.showPicker = true;
            this.mediaLoading = true;
            try {
                const res = await fetch('{{ route("media.browse") }}');
                this.mediaFiles = await res.json();
            } catch(e) { console.error(e); }
            this.mediaLoading = false;
        },
        selectFile(path) {
            this.selectedPath = path;
            this.updatePreview(path);
            $wire.set('{{ $statePath }}', path);
            this.showPicker = false;
        }
    }"
    wire:ignore.self
    class="space-y-2"
>
    {{-- Input row --}}
    <div class="flex items-center gap-2">
        <div class="flex-1">
            <input
                type="text"
                x-model="selectedPath"
                @change="updatePreview($event.target.value); $wire.set('{{ $statePath }}', $event.target.value)"
                placeholder="{{ $placeholder }}"
                maxlength="{{ $maxLength }}"
                class="w-full rounded-lg border border-gray-300 bg-white py-2 px-3 text-sm shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            />
        </div>
        <button
            type="button"
            @click="openPicker()"
            class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 whitespace-nowrap"
        >
            <x-heroicon-s-magnifying-glass class="h-4 w-4" />
            Browse
        </button>
        <a
            href="{{ url('/admin/media') }}"
            target="_blank"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 whitespace-nowrap dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
        >
            <x-heroicon-s-folder-open class="h-4 w-4" />
            Library
        </a>
    </div>

    {{-- Image preview --}}
    <div x-show="previewUrl" x-cloak class="relative">
        <img
            :src="previewUrl"
            alt="Preview"
            class="h-32 w-auto rounded-lg border border-gray-200 object-cover shadow-sm dark:border-gray-600"
            x-init="$watch('previewUrl', val => { if(val) { $nextTick(() => $el.naturalWidth); } })"
            x-on:error="$el.style.display='none'"
        />
        <button
            type="button"
            @click="selectedPath = ''; previewUrl = null; $wire.set('{{ $statePath }}', '')"
            class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600"
        >
            <x-heroicon-s-x-mark class="h-3.5 w-3.5" />
        </button>
    </div>

    {{-- Picker modal --}}
    <div
        x-show="showPicker"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="showPicker = false"
        @keydown.escape.window="showPicker = false"
    >
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-3xl max-h-[80vh] overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-gray-800 flex flex-col"
            @click.stop
        >
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Select Media</h3>
                <button @click="showPicker = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700">
                    <x-heroicon-s-x-mark class="h-5 w-5" />
                </button>
            </div>

            {{-- Search --}}
            <div class="border-b border-gray-200 px-5 py-2 dark:border-gray-700">
                <input
                    type="text"
                    x-model="mediaSearch"
                    placeholder="Search files..."
                    class="w-full rounded-lg border border-gray-300 bg-white py-2 px-3 text-sm shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                />
            </div>

            {{-- Grid --}}
            <div class="flex-1 overflow-y-auto p-5">
                <template x-if="mediaLoading">
                    <div class="text-center py-12 text-gray-500">Loading media files...</div>
                </template>
                <template x-if="!mediaLoading && mediaFiles.length === 0">
                    <div class="text-center py-12 text-gray-500">No files found. Upload files in the Media Library first.</div>
                </template>
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                    <template x-for="file in mediaFiles.filter(f => !mediaSearch || f.name.toLowerCase().includes(mediaSearch.toLowerCase()))" :key="file.path">
                        <button
                            type="button"
                            @click="selectFile(file.path)"
                            class="group relative cursor-pointer overflow-hidden rounded-lg border-2 border-transparent bg-gray-100 transition-all hover:border-primary-500 hover:shadow-md dark:bg-gray-700"
                        >
                            <div class="aspect-square flex items-center justify-center overflow-hidden">
                                <template x-if="file.is_image">
                                    <img :src="file.url" :alt="file.name" class="h-full w-full object-cover transition-transform group-hover:scale-105" loading="lazy" />
                                </template>
                                <template x-if="!file.is_image">
                                    <div class="flex flex-col items-center justify-center gap-1 p-2">
                                        <x-heroicon-o-document-text class="h-8 w-8 text-gray-400" />
                                        <span class="text-xs font-bold uppercase text-gray-500" x-text="file.ext"></span>
                                    </div>
                                </template>
                            </div>
                            <div class="px-2 py-1.5">
                                <div class="text-xs font-medium text-gray-700 truncate dark:text-gray-300" x-text="file.name" :title="file.name"></div>
                                <div class="text-[10px] text-gray-400" x-text="file.sizeFormatted"></div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

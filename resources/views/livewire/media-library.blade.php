{{-- resources/views/livewire/media-library.blade.php --}}
<style>
.media-library{--ml-indigo:#6366f1;--ml-violet:#8b5cf6;--ml-pink:#ec4899;--ml-bg:#f8fafc;--ml-card:#fff;--ml-border:#e2e8f0;--ml-ink:#1e293b;--ml-muted:#64748b;--ml-line:#f1f5f9;max-width:1400px;margin:0 auto;padding:1.5rem}
.ml-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem}
.ml-title{font-size:1.5rem;font-weight:800;color:var(--ml-ink);letter-spacing:-.02em;margin:0}
.ml-sub{font-size:.82rem;color:var(--ml-muted);margin:.2rem 0 0}
.ml-header-actions{display:flex;gap:.5rem}
.ml-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border-radius:.55rem;font-size:.82rem;font-weight:600;cursor:pointer;border:none;transition:all .15s ease;line-height:1.4}
.ml-btn--primary{background:var(--ml-indigo);color:#fff}.ml-btn--primary:hover{background:#4f46e5;box-shadow:0 4px 12px rgba(99,102,241,.25)}
.ml-btn--ghost{background:transparent;color:var(--ml-muted);border:1px solid var(--ml-border)}.ml-btn--ghost:hover{background:var(--ml-line);color:var(--ml-ink)}
.ml-btn--danger{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}.ml-btn--danger:hover{background:#fee2e2}
.ml-btn--full{width:100%;justify-content:center}

.ml-upload-zone{margin-bottom:1.25rem;background:var(--ml-card);border:1px solid var(--ml-border);border-radius:.75rem;padding:1rem}
.ml-upload-area{border:2px dashed #c7d2fe;border-radius:.65rem;padding:2rem 1.5rem;text-align:center;background:#f5f3ff;transition:border-color .2s,background .2s}
.ml-upload-area:hover,.ml-upload-area[data-full]{border-color:var(--ml-indigo);background:#ede9fe}
.ml-upload-text{font-size:.9rem;color:var(--ml-ink);margin:.75rem 0 .25rem;font-weight:500}
.ml-upload-browse{color:var(--ml-indigo);font-weight:700;cursor:pointer;text-decoration:underline}
.ml-upload-hint{font-size:.78rem;color:var(--ml-muted)}
.ml-upload-input{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);border:0}
.ml-upload-actions{display:flex;gap:.5rem;margin-top:.75rem}

.ml-toolbar{display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap}
.ml-search{position:relative;flex:1;min-width:220px}
.ml-search svg{position:absolute;left:.75rem;top:50%;transform:translateY(-50%);pointer-events:none}
.ml-search-input{width:100%;padding:.55rem .75rem .55rem 2.5rem;border:1px solid var(--ml-border);border-radius:.55rem;font-size:.85rem;background:var(--ml-card);color:var(--ml-ink);transition:border-color .15s,box-shadow .15s}
.ml-search-input:focus{outline:none;border-color:var(--ml-indigo);box-shadow:0 0 0 3px rgba(99,102,241,.12)}
.ml-filters{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}
.ml-chips{display:flex;gap:.35rem}
.ml-chip{display:inline-flex;align-items:center;gap:.3rem;padding:.35rem .7rem;border-radius:999px;font-size:.78rem;font-weight:600;border:1px solid var(--ml-border);background:var(--ml-card);color:var(--ml-muted);cursor:pointer;transition:all .15s}
.ml-chip:hover{border-color:#c7d2fe;color:var(--ml-indigo)}
.ml-chip--active{background:var(--ml-indigo);color:#fff;border-color:var(--ml-indigo)}
.ml-chip-count{font-size:.7rem;opacity:.75}
.ml-select{padding:.4rem .65rem;border:1px solid var(--ml-border);border-radius:.45rem;font-size:.78rem;background:var(--ml-card);color:var(--ml-ink);cursor:pointer}

.ml-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(185px,1fr));gap:.85rem}
.ml-card{position:relative;background:var(--ml-card);border:1px solid var(--ml-border);border-radius:.65rem;overflow:hidden;cursor:pointer;transition:all .18s ease}
.ml-card:hover{border-color:#c7d2fe;box-shadow:0 6px 20px rgba(99,102,241,.08);transform:translateY(-1px)}
.ml-card--selected{border-color:var(--ml-indigo);box-shadow:0 0 0 2px rgba(99,102,241,.2)}
.ml-card-thumb{aspect-ratio:4/3;background:var(--ml-line);display:flex;align-items:center;justify-content:center;overflow:hidden}
.ml-card-img{width:100%;height:100%;object-fit:cover;transition:transform .25s}
.ml-card:hover .ml-card-img{transform:scale(1.04)}
.ml-card-img-fallback{display:flex;align-items:center;justify-content:center;width:100%;height:100%}
.ml-card-icon{display:flex;flex-direction:column;align-items:center;gap:.3rem}
.ml-card-ext{font-size:.7rem;font-weight:700;color:var(--ml-muted);letter-spacing:.03em}
.ml-card-info{padding:.6rem .7rem}
.ml-card-name{font-size:.78rem;font-weight:600;color:var(--ml-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:.15rem}
.ml-card-meta{font-size:.72rem;color:var(--ml-muted);display:flex;gap:.4rem;align-items:center}
.ml-card-dims{opacity:.6}
.ml-card-actions{position:absolute;top:.45rem;right:.45rem;display:flex;gap:.25rem;opacity:0;transition:opacity .15s}
.ml-card:hover .ml-card-actions{opacity:1}
.ml-card-btn{width:1.7rem;height:1.7rem;display:inline-flex;align-items:center;justify-content:center;border-radius:.4rem;background:rgba(255,255,255,.92);border:none;color:var(--ml-muted);cursor:pointer;backdrop-filter:blur(4px);transition:all .15s;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.ml-card-btn:hover{background:#fff;color:var(--ml-indigo);box-shadow:0 2px 8px rgba(0,0,0,.12)}

.ml-empty{text-align:center;padding:3rem 1.5rem;color:var(--ml-muted)}
.ml-empty h3{font-size:1.1rem;font-weight:700;color:var(--ml-ink);margin:1rem 0 .25rem}
.ml-empty p{font-size:.85rem}

.ml-toast{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);background:#1e293b;color:#fff;padding:.6rem 1.2rem;border-radius:.55rem;font-size:.82rem;font-weight:600;display:flex;align-items:center;gap:.4rem;box-shadow:0 8px 24px rgba(0,0,0,.18);z-index:9999}
.ml-toast--success{background:#166534}
.ml-toast--info{background:#1e293b}

.ml-overlay{position:fixed;inset:0;background:rgba(15,23,42,.3);z-index:9998;backdrop-filter:blur(2px)}
.ml-detail{position:fixed;top:0;right:0;width:min(420px,92vw);height:100vh;background:var(--ml-card);box-shadow:-8px 0 32px rgba(0,0,0,.12);z-index:9999;display:flex;flex-direction:column;overflow-y:auto}
.ml-detail-head{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.25rem 0}
.ml-detail-title{font-size:1.05rem;font-weight:700;color:var(--ml-ink);margin:0}
.ml-detail-close{width:2rem;height:2rem;display:flex;align-items:center;justify-content:center;border-radius:.4rem;background:transparent;border:none;color:var(--ml-muted);cursor:pointer;transition:all .15s}.ml-detail-close:hover{background:var(--ml-line);color:var(--ml-ink)}
.ml-detail-preview{margin:1rem 1.25rem;border-radius:.6rem;overflow:hidden;background:var(--ml-line);display:flex;align-items:center;justify-content:center;min-height:180px;max-height:280px}
.ml-detail-preview--loading{padding:1rem}
.ml-detail-img{width:100%;height:100%;object-fit:contain;max-height:280px}
.ml-detail-doc-icon{display:flex;flex-direction:column;align-items:center;gap:.4rem;padding:2rem}
.ml-detail-ext{font-size:.85rem;font-weight:700;color:var(--ml-muted)}
.ml-detail-name{padding:0 1.25rem;font-size:.9rem;font-weight:700;color:var(--ml-ink);word-break:break-all;margin-bottom:.75rem}
.ml-detail-meta{padding:0 1.25rem;margin-bottom:1rem}
.ml-detail-row{display:flex;justify-content:space-between;padding:.45rem 0;border-bottom:1px solid var(--ml-line);gap:.5rem}
.ml-detail-label{font-size:.78rem;color:var(--ml-muted);flex-shrink:0}
.ml-detail-value{font-size:.78rem;color:var(--ml-ink);text-align:right;font-weight:500;word-break:break-all}
.ml-detail-path{font-family:monospace;font-size:.72rem;background:var(--ml-line);padding:.2rem .4rem;border-radius:.3rem;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block}
.ml-detail-actions{padding:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:.5rem}
.ml-pagination{display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;margin-top:1rem;padding-top:1rem;border-top:1px solid var(--ml-border)}
.ml-pagination-info{font-size:.78rem;color:var(--ml-muted)}
.ml-pagination-controls{display:flex;align-items:center;gap:.35rem;flex-wrap:wrap}
.ml-page-btn{min-width:2rem;height:2rem;padding:0 .65rem;border-radius:.45rem;border:1px solid var(--ml-border);background:var(--ml-card);color:var(--ml-ink);font-size:.78rem;font-weight:600;cursor:pointer;transition:all .15s ease}
.ml-page-btn:hover:not(:disabled){border-color:#c7d2fe;color:var(--ml-indigo);box-shadow:0 2px 8px rgba(99,102,241,.08)}
.ml-page-btn[disabled]{opacity:.45;cursor:not-allowed}
.ml-page-btn--active{background:var(--ml-indigo);border-color:var(--ml-indigo);color:#fff}

@media(max-width:640px){.ml-grid{grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.6rem}.ml-toolbar{flex-direction:column;align-items:stretch}.ml-filters{flex-wrap:wrap}}
</style>

<div
    class="media-library"
    x-data="{
        toast: { visible: false, message: '', type: 'success' },
        toastTimer: null,
        uploadOpen: $wire.$entangle('uploadOpen'),
        files: @js($mediaFiles),
        folders: @js($folders),
        typeCounts: @js($typeCounts),
        search: '',
        filterType: 'all',
        filterFolder: 'all',
        sortBy: 'newest',
        perPage: 24,
        currentPage: 1,
        filteredFiles: [],
        totalFiles: 0,
        totalPages: 1,
        selectedUploadName: '',
        detailOpen: false,
        selectedPath: null,
        selectedFile: null,
        previewReady: false,
        syncLibrary(files) {
            this.files = files;
            this.rebuildMeta();
            this.refreshFiles();
        },
        rebuildMeta() {
            const folders = new Set();
            const counts = { all: 0, image: 0, document: 0, other: 0 };

            for (const file of this.files) {
                folders.add(file.folder);
                counts.all += 1;
                if (counts[file.type] !== undefined) {
                    counts[file.type] += 1;
                } else {
                    counts.other += 1;
                }
            }

            this.folders = Array.from(folders).sort();
            this.typeCounts = counts;
        },
        setFilterType(type) {
            this.filterType = type;
            this.currentPage = 1;
            this.refreshFiles();
        },
        setFilterFolder(folder) {
            this.filterFolder = folder;
            this.currentPage = 1;
            this.refreshFiles();
        },
        setSortBy(value) {
            this.sortBy = value;
            this.currentPage = 1;
            this.refreshFiles();
        },
        matchesFilters(file) {
            const search = this.search.trim().toLowerCase();
            const matchesSearch = !search
                || file.name.toLowerCase().includes(search)
                || file.path.toLowerCase().includes(search);
            const matchesType = this.filterType === 'all' || file.type === this.filterType;
            const matchesFolder = this.filterFolder === 'all' || file.folder === this.filterFolder;

            return matchesSearch && matchesType && matchesFolder;
        },
        sortedFiles() {
            const files = this.files.filter((file) => this.matchesFilters(file));

            return files.sort((a, b) => {
                switch (this.sortBy) {
                    case 'oldest':
                        return a.modified - b.modified;
                    case 'largest':
                        return b.size - a.size;
                    case 'smallest':
                        return a.size - b.size;
                    default:
                        return b.modified - a.modified;
                }
            });
        },
        refreshFiles() {
            this.filteredFiles = this.sortedFiles();
            this.totalFiles = this.filteredFiles.length;
            this.totalPages = Math.max(1, Math.ceil(this.totalFiles / this.perPage));

            if (this.currentPage > this.totalPages) {
                this.currentPage = this.totalPages;
            }
        },
        get pageFiles() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredFiles.slice(start, start + this.perPage);
        },
        get startPage() {
            return Math.max(1, this.currentPage - 2);
        },
        get endPage() {
            return Math.min(this.totalPages, this.currentPage + 2);
        },
        get pageSummary() {
            const start = this.totalFiles === 0 ? 0 : ((this.currentPage - 1) * this.perPage) + 1;
            const end = Math.min(this.currentPage * this.perPage, this.totalFiles);

            return { start, end };
        },
        goToPage(page) {
            this.currentPage = Math.min(Math.max(1, page), this.totalPages);
        },
        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.visible = true;

            if (this.toastTimer) {
                clearTimeout(this.toastTimer);
            }

            this.toastTimer = setTimeout(() => {
                this.toast.visible = false;
            }, 2200);
        },
        copyPath(path) {
            navigator.clipboard.writeText(path).then(() => {
                this.showToast('URL copied to clipboard');
            });
        },
        openFile(file) {
            this.selectedPath = file.path;
            this.selectedFile = file;
            this.previewReady = file.type !== 'image';
            this.detailOpen = true;
        },
        closeDetail() {
            this.detailOpen = false;
            this.selectedPath = null;
            this.selectedFile = null;
            this.previewReady = false;
        }
    }"
    x-init="refreshFiles()"
    x-on:media-library-updated.window="syncLibrary($event.detail.files); uploadOpen = false; selectedUploadName = ''; $refs.uploadInput.value = ''"
    x-on:copy-to-clipboard.window="
        navigator.clipboard.writeText($event.detail.path).then(() => {
            showToast('URL copied to clipboard');
        });
    "
    x-on:media-library-toast.window="showToast($event.detail.message)"
>
    <div class="ml-header">
        <div>
            <h1 class="ml-title">Media Library</h1>
            <p class="ml-sub"><span x-text="totalFiles">{{ number_format($totalFiles) }}</span> files across <span x-text="folders.length">{{ count($folders) }}</span> folders</p>
        </div>
        <div class="ml-header-actions">
            <button type="button" x-on:click="uploadOpen = !uploadOpen" class="ml-btn ml-btn--primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Upload
            </button>
            <button type="button" wire:click="scanMedia" wire:loading.attr="disabled" wire:target="scanMedia" class="ml-btn ml-btn--ghost" title="Refresh">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            </button>
        </div>
    </div>

    <div x-show="uploadOpen" x-transition x-cloak class="ml-upload-zone">
        <form wire:submit.prevent="uploadMedia">
            <div class="ml-upload-area">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <p class="ml-upload-text">Drop your files here, or <label for="ml-upload-input" class="ml-upload-browse">browse</label></p>
                <p class="ml-upload-hint">Images up to 10 MB. PDF, DOC, XLS up to 10 MB.</p>
                <input
                    x-ref="uploadInput"
                    type="file"
                    id="ml-upload-input"
                    wire:model.live="uploadFile"
                    x-on:change="selectedUploadName = $event.target.files?.[0]?.name || ''"
                    class="ml-upload-input"
                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv"
                />
                <div x-show="selectedUploadName" x-cloak class="mt-3 text-sm font-medium text-indigo-600" x-text="selectedUploadName"></div>
                <div wire:loading wire:target="uploadFile,uploadMedia" class="mt-3 text-sm font-medium text-indigo-600">
                    Uploading file...
                </div>
            </div>
            <div x-show="selectedUploadName" x-cloak class="ml-upload-actions">
                <button type="submit" class="ml-btn ml-btn--primary" wire:loading.attr="disabled" wire:target="uploadFile,uploadMedia">
                    <span wire:loading.remove wire:target="uploadMedia">Start Upload</span>
                    <span wire:loading wire:target="uploadMedia">Uploading...</span>
                </button>
                <button
                    type="button"
                    x-on:click="selectedUploadName = ''; $refs.uploadInput.value = ''; $wire.set('uploadFile', null)"
                    class="ml-btn ml-btn--ghost"
                >
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <div class="ml-toolbar">
        <div class="ml-search">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
                type="text"
                x-model.debounce.250ms="search"
                x-on:input.debounce.250ms="currentPage = 1; refreshFiles()"
                placeholder="Search files by name or path..."
                class="ml-search-input"
            />
        </div>
        <div class="ml-filters">
            <div class="ml-chips">
                @foreach(['all' => 'All', 'image' => 'Images', 'document' => 'Documents'] as $key => $label)
                    <button
                        type="button"
                        x-on:click="setFilterType('{{ $key }}')"
                        :class="filterType === '{{ $key }}' ? 'ml-chip ml-chip--active' : 'ml-chip'"
                    >
                        {{ $label }}
                        <span class="ml-chip-count" x-text="typeCounts['{{ $key }}'] ?? 0">{{ $typeCounts[$key] ?? 0 }}</span>
                    </button>
                @endforeach
            </div>
            <select x-model="filterFolder" x-on:change="currentPage = 1; refreshFiles()" class="ml-select">
                <option value="all">All Folders</option>
                @foreach($folders as $folder)
                    <option value="{{ $folder }}">{{ $folder }}</option>
                @endforeach
            </select>
            <select x-model="sortBy" x-on:change="currentPage = 1; refreshFiles()" class="ml-select">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="largest">Largest</option>
                <option value="smallest">Smallest</option>
            </select>
        </div>
    </div>
    <template x-if="pageFiles.length === 0">
        <div class="ml-empty" x-cloak>
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#c7d2fe" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <h3>No files found</h3>
            <p>Try adjusting your search or filters, or upload new files.</p>
        </div>
    </template>

    <template x-if="pageFiles.length > 0">
        <div x-cloak>
            <div class="ml-grid">
                <template x-for="file in pageFiles" :key="file.path">
                    <div class="ml-card" :class="{ 'ml-card--selected': selectedPath === file.path }" x-on:click="openFile(file)">
                        <div class="ml-card-thumb">
                            <template x-if="file.type === 'image'">
                                <div class="w-full h-full">
                                    <img
                                        :src="file.url"
                                        :alt="file.name"
                                        loading="lazy"
                                        decoding="async"
                                        class="ml-card-img"
                                        x-on:error="$el.style.display='none'; $el.nextElementSibling.style.display='flex'"
                                    />
                                    <div class="ml-card-img-fallback" style="display:none">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#a5b4fc" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    </div>
                                </div>
                            </template>
                            <template x-if="file.type === 'document'">
                                <div class="ml-card-icon">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#f472b6" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                    <span class="ml-card-ext" x-text="file.ext.toUpperCase()"></span>
                                </div>
                            </template>
                            <template x-if="file.type !== 'image' && file.type !== 'document'">
                                <div class="ml-card-icon">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                                    <span class="ml-card-ext" x-text="file.ext.toUpperCase()"></span>
                                </div>
                            </template>
                        </div>

                        <div class="ml-card-info">
                            <div class="ml-card-name" :title="file.name" x-text="file.name"></div>
                            <div class="ml-card-meta">
                                <span x-text="file.size_human"></span>
                                <template x-if="file.width">
                                    <span class="ml-card-dims" x-text="`${file.width} x ${file.height}`"></span>
                                </template>
                            </div>
                        </div>

                        <div class="ml-card-actions" x-on:click.stop>
                            <button
                                type="button"
                                class="ml-card-btn"
                                title="Copy URL"
                                x-on:click="copyPath(file.url)"
                            >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                            <a
                                class="ml-card-btn"
                                :href="file.url"
                                target="_blank"
                                title="Open"
                                x-on:click.stop
                            >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </a>
                        </div>
                    </div>
                </template>
            </div>

            <div class="ml-pagination">
                <div class="ml-pagination-info">
                    <span x-text="pageSummary.start">0</span>-<span x-text="pageSummary.end">0</span> of <span x-text="totalFiles">{{ number_format($totalFiles) }}</span> files
                    on page <span x-text="currentPage">1</span> of <span x-text="totalPages">1</span>
                </div>
                <div class="ml-pagination-controls">
                    <button class="ml-page-btn" type="button" x-on:click="goToPage(1)" :disabled="currentPage === 1">&laquo;</button>
                    <button class="ml-page-btn" type="button" x-on:click="goToPage(currentPage - 1)" :disabled="currentPage === 1">&lsaquo;</button>

                    <template x-for="page in Array.from({ length: endPage - startPage + 1 }, (_, index) => startPage + index)" :key="page">
                        <button
                            class="ml-page-btn"
                            :class="{ 'ml-page-btn--active': page === currentPage }"
                            type="button"
                            x-on:click="goToPage(page)"
                            x-text="page"
                        ></button>
                    </template>

                    <button class="ml-page-btn" type="button" x-on:click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages">&rsaquo;</button>
                    <button class="ml-page-btn" type="button" x-on:click="goToPage(totalPages)" :disabled="currentPage === totalPages">&raquo;</button>
                </div>
            </div>
        </div>
    </template>

    <div
        x-show="toast.visible"
        x-transition
        x-cloak
        class="ml-toast"
        :class="toast.type === 'success' ? 'ml-toast--success' : 'ml-toast--info'"
    >
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
        <span x-text="toast.message"></span>
    </div>

    <div
        x-show="detailOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="ml-overlay"
        x-on:click="closeDetail()"
        x-cloak
    ></div>
    <div
        x-show="detailOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="ml-detail"
        x-cloak
    >
        <template x-if="selectedFile">
            <div>
                <div class="ml-detail-head">
                    <h2 class="ml-detail-title">File Details</h2>
                    <button x-on:click="closeDetail()" class="ml-detail-close">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <div class="ml-detail-preview">
                    <template x-if="selectedFile.type === 'image'">
                        <div class="w-full h-full">
                            <div x-show="!previewReady" class="ml-detail-preview--loading ml-detail-doc-icon" style="min-height:180px">
                                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                <span class="ml-detail-ext">Loading preview</span>
                            </div>
                            <img
                                x-show="previewReady"
                                :src="selectedFile.url"
                                :alt="selectedFile.name"
                                class="ml-detail-img"
                                loading="eager"
                                decoding="async"
                                x-on:load="previewReady = true"
                                x-on:error="previewReady = true"
                            />
                        </div>
                    </template>
                    <template x-if="selectedFile.type !== 'image'">
                        <div class="ml-detail-doc-icon">
                            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#f472b6" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <span class="ml-detail-ext" x-text="selectedFile.ext.toUpperCase()"></span>
                        </div>
                    </template>
                </div>

                <div class="ml-detail-name" x-text="selectedFile.name"></div>

                <div class="ml-detail-meta">
                    <div class="ml-detail-row"><span class="ml-detail-label">Path</span><span class="ml-detail-value ml-detail-path" x-text="selectedFile.path"></span></div>
                    <div class="ml-detail-row"><span class="ml-detail-label">Size</span><span class="ml-detail-value" x-text="selectedFile.size_human"></span></div>
                    <template x-if="selectedFile.width">
                        <div class="ml-detail-row"><span class="ml-detail-label">Dimensions</span><span class="ml-detail-value" x-text="`${selectedFile.width} x ${selectedFile.height} px`"></span></div>
                    </template>
                    <div class="ml-detail-row"><span class="ml-detail-label">Type</span><span class="ml-detail-value" x-text="`${selectedFile.ext.toUpperCase()} (${selectedFile.type})`"></span></div>
                    <div class="ml-detail-row"><span class="ml-detail-label">Modified</span><span class="ml-detail-value" x-text="selectedFile.modified_human"></span></div>
                    <div class="ml-detail-row"><span class="ml-detail-label">Folder</span><span class="ml-detail-value" x-text="selectedFile.folder"></span></div>
                </div>

                <div class="ml-detail-actions">
                    <button x-on:click="copyPath(selectedFile.path)" class="ml-btn ml-btn--primary ml-btn--full">
                    <button x-on:click="copyPath(selectedFile.url)" class="ml-btn ml-btn--primary ml-btn--full">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        Copy URL
                    </button>
                    <a :href="selectedFile.url" target="_blank" class="ml-btn ml-btn--ghost ml-btn--full">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        Open in New Tab
                    </a>
                    <button
                        x-on:click="$wire.deleteFile(selectedFile.path); closeDetail()"
                        wire:confirm="Delete this file permanently? This cannot be undone."
                        class="ml-btn ml-btn--danger ml-btn--full"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Delete File
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }},
            initialItems: @js($getState() ?? []),
            menuLocation: @js($menuLocation ?? 'primary'),
            tree: [],
            expanded: {},
            editingId: null,
            draft: {},
            draftError: null,
            dragId: null,
            dragOverId: null,
            dragPos: null,
            filter: '',

            colorMap: {
                indigo: '#5b5cf6', teal: '#14b8a6', rose: '#f43f5e',
                purple: '#8b5cf6', amber: '#f59e0b', orange: '#f97316',
            },

            init() {
                this.tree = this.normalize(this.initialItems);
            },

            uid() {
                return 'm' + Math.random().toString(36).slice(2, 9);
            },

            normalize(nodes) {
                return (nodes || []).map((n) => ({
                    label: n.label ?? '',
                    header_label: n.header_label ?? '',
                    url: n.url ?? '',
                    description: n.description ?? '',
                    icon: n.icon ?? '',
                    color: n.color ?? '',
                    mega: n.mega ?? null,
                    target: n.target ?? null,
                    children: this.normalize(n.children),
                    _uid: this.uid(),
                }));
            },

            strip(nodes) {
                return (nodes || []).map(({ _uid, children, ...rest }) => ({
                    ...rest,
                    children: this.strip(children),
                }));
            },

            findNode(nodes, uid) {
                for (let i = 0; i < nodes.length; i++) {
                    if (nodes[i]._uid === uid) return { node: nodes[i], parent: nodes, index: i };
                    if (nodes[i].children?.length) {
                        const r = this.findNode(nodes[i].children, uid);
                        if (r) return r;
                    }
                }
                return null;
            },

            isDescendant(ancestorUid, uid) {
                const src = this.findNode(this.tree, ancestorUid);
                if (!src) return false;
                return this.findNode(src.node.children, uid) !== null;
            },

            hasChildren(item) {
                return (item.children || []).length > 0;
            },

            toggle(item) {
                this.expanded[item._uid] = !this.expanded[item._uid];
            },

            isExpanded(item) {
                return !!this.expanded[item._uid];
            },

            commit() {
                this.state = this.strip(this.tree);
            },

            /* ---------------------------------------------------------- */
            /* Drag & drop                                                 */
            /* ---------------------------------------------------------- */
            dragStart(e, uid) {
                this.dragId = uid;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', uid);
            },

            dragOver(e, uid) {
                e.preventDefault();
                e.stopPropagation();
                e.dataTransfer.dropEffect = 'move';
                if (!this.dragId || uid === this.dragId || this.isDescendant(this.dragId, uid)) {
                    this.dragOverId = null;
                    this.dragPos = null;
                    return;
                }
                const rect = e.currentTarget.getBoundingClientRect();
                const y = e.clientY - rect.top;
                this.dragOverId = uid;
                this.dragPos = y < rect.height * 0.3 ? 'before' : (y > rect.height * 0.7 ? 'after' : 'inside');
            },

            drop(e, uid) {
                e.preventDefault();
                e.stopPropagation();
                this.moveItem(this.dragId, uid, this.dragPos);
                this.dragEnd();
            },

            dragEnd() {
                this.dragId = null;
                this.dragOverId = null;
                this.dragPos = null;
            },

            moveItem(dragUid, overUid, pos) {
                if (!dragUid || !overUid || dragUid === overUid) return;
                const src = this.findNode(this.tree, dragUid);
                if (!src) return;

                if (pos === 'inside') {
                    const t = this.findNode(this.tree, overUid);
                    if (!t || this.isDescendant(dragUid, overUid)) return;
                    src.parent.splice(src.index, 1);
                    t.node.children = t.node.children || [];
                    t.node.children.push(src.node);
                    this.expanded[t.node._uid] = true;
                } else {
                    const t = this.findNode(this.tree, overUid);
                    if (!t) return;
                    src.parent.splice(src.index, 1);
                    const t2 = this.findNode(this.tree, overUid);
                    if (!t2) {
                        this.tree.push(src.node);
                    } else {
                        const idx = pos === 'after' ? t2.index + 1 : t2.index;
                        t2.parent.splice(idx, 0, src.node);
                    }
                }
                this.commit();
            },

            dropClass(uid) {
                if (this.dragOverId !== uid) return '';
                if (this.dragPos === 'before') return 'mcb-row--drop-before';
                if (this.dragPos === 'after') return 'mcb-row--drop-after';
                return 'mcb-row--drop-inside';
            },

            /* ---------------------------------------------------------- */
            /* CRUD                                                        */
            /* ---------------------------------------------------------- */
            addItem() {
                const node = { label: 'New item', url: '/', children: [], _uid: this.uid() };
                this.tree.push(node);
                this.expanded[node._uid] = true;
                this.commit();
                this.beginEdit(node);
            },

            addChild(item) {
                const node = { label: 'New child', url: '/', children: [], _uid: this.uid() };
                item.children = item.children || [];
                item.children.push(node);
                this.expanded[item._uid] = true;
                this.commit();
                this.beginEdit(node);
            },

            duplicate(item) {
                const src = this.findNode(this.tree, item._uid);
                if (!src) return;
                const copy = JSON.parse(JSON.stringify(this.strip([item]))[0]);
                copy._uid = this.uid();
                src.parent.splice(src.index + 1, 0, copy);
                this.expanded[copy._uid] = true;
                this.commit();
            },

            remove(item) {
                const src = this.findNode(this.tree, item._uid);
                if (!src) return;
                src.parent.splice(src.index, 1);
                if (this.editingId === item._uid) {
                    this.editingId = null;
                    this.draft = {};
                }
                this.commit();
            },

            beginEdit(item) {
                this.editingId = item._uid;
                this.draft = {
                    label: item.label ?? '',
                    header_label: item.header_label ?? '',
                    url: item.url ?? '',
                    description: item.description ?? '',
                    icon: item.icon ?? '',
                    color: item.color ?? '',
                    mega: item.mega ?? null,
                    target: item.target ?? null,
                };
            },

            saveEdit() {
                if (!this.draft.label.trim()) {
                    this.draftError = 'Label is required.';
                    return;
                }
                this.draftError = null;
                const t = this.findNode(this.tree, this.editingId);
                if (!t) return;
                Object.assign(t.node, {
                    label: this.draft.label.trim(),
                    header_label: this.draft.header_label.trim(),
                    url: this.draft.url.trim(),
                    description: this.draft.description.trim(),
                    icon: this.draft.icon.trim(),
                    color: this.draft.color,
                    mega: this.draft.mega || null,
                    target: this.draft.target || null,
                });
                this.editingId = null;
                this.draft = {};
                this.commit();
            },

            cancelEdit() {
                this.editingId = null;
                this.draft = {};
                this.draftError = null;
            },

            filteredItems() {
                const q = this.filter.trim().toLowerCase();
                if (!q) return this.tree;
                return this.tree.filter((item) => {
                    const own = (item.label + ' ' + (item.url || '')).toLowerCase().includes(q);
                    const children = (item.children || []).some((c) => (c.label + ' ' + (c.url || '')).toLowerCase().includes(q));
                    return own || children;
                });
            },

            editingNode() {
                if (!this.editingId) return null;
                return this.findNode(this.tree, this.editingId)?.node ?? null;
            },

            /* ---------------------------------------------------------- */
            /* Preview helpers                                             */
            /* ---------------------------------------------------------- */
            chipColor(color) {
                return this.colorMap[color] || '#ec7424';
            },
        }"
        x-init="init()"
        class="mcb"
    >
        <div class="mcb-grid">
            <!-- ============================ Builder ============================ -->
            <div class="mcb-pane mcb-pane--tree">
                <div class="mcb-pane-head">
                    <div>
                        <div class="mcb-pane-title">Menu structure</div>
                        <div class="mcb-pane-sub">Drag to reorder · drop on the middle of an item to nest it</div>
                    </div>
                    <button type="button" class="mcb-btn mcb-btn--primary" @click="addItem()">
                        <span class="mcb-btn-icon">+</span> Add item
                    </button>
                </div>

                <div class="mcb-filter">
                    <span class="mcb-filter-icon">&#128269;</span>
                    <input
                        type="text"
                        x-model="filter"
                        placeholder="Filter items…"
                        class="mcb-input"
                    >
                </div>

                <div class="mcb-tree">
                    <template x-if="!tree.length">
                        <div class="mcb-empty">
                            <div class="mcb-empty-title">No items yet</div>
                            <div class="mcb-empty-sub">Add your first item to start building this menu.</div>
                        </div>
                    </template>

                    <template x-for="item in filteredItems()" :key="item._uid">
                        <div class="mcb-item">
                            <!-- Top-level row -->
                            <div
                                class="mcb-row mcb-row--top"
                                :class="dropClass(item._uid)"
                            >
                                <span
                                    class="mcb-grip"
                                    draggable="true"
                                    @dragstart="dragStart($event, item._uid)"
                                    @dragend="dragEnd()"
                                    title="Drag to move"
                                >&#8801;</span>

                                <button
                                    type="button"
                                    class="mcb-chev"
                                    @click="toggle(item)"
                                    :class="{ 'is-open': isExpanded(item) }"
                                >&#9662;</button>

                                <span
                                    class="mcb-chip"
                                    :style="{ background: chipColor(item.color) }"
                                    x-show="item.color"
                                ></span>

                                <div class="mcb-row-main">
                                    <div class="mcb-row-label" x-text="item.label || 'Untitled'"></div>
                                    <div class="mcb-row-url" x-show="item.url" x-text="item.url"></div>
                                </div>

                                <span class="mcb-count" x-show="hasChildren(item)" x-text="item.children.length"></span>

                                <div
                                    class="mcb-actions"
                                    @click.stop
                                    @dragover.prevent
                                    @drop.prevent
                                >
                                    <button type="button" class="mcb-act" title="Edit" @click="beginEdit(item)">&#9998;</button>
                                    <button type="button" class="mcb-act" title="Add child" @click="addChild(item)" x-show="menuLocation !== 'mobile'">&#43;</button>
                                    <button type="button" class="mcb-act" title="Duplicate" @click="duplicate(item)">&#12847;</button>
                                    <button type="button" class="mcb-act mcb-act--danger" title="Delete" @click="remove(item)">&#10005;</button>
                                </div>

                                <!-- Drop zone -->
                                <div class="mcb-drop" @dragover.prevent="dragOver($event, item._uid)" @drop.prevent="drop($event, item._uid)"></div>
                            </div>

                            <!-- Children -->
                            <div class="mcb-children" x-show="isExpanded(item) && hasChildren(item)">
                                <template x-for="child in item.children" :key="child._uid">
                                    <div
                                        class="mcb-row mcb-row--child"
                                        :class="dropClass(child._uid)"
                                    >
                                        <span class="mcb-guide"></span>
                                        <span
                                            class="mcb-grip"
                                            draggable="true"
                                            @dragstart="dragStart($event, child._uid)"
                                            @dragend="dragEnd()"
                                            title="Drag to move"
                                        >&#8801;</span>

                                        <span
                                            class="mcb-chip mcb-chip--sm"
                                            :style="{ background: chipColor(child.color) }"
                                            x-show="child.color"
                                        ></span>

                                        <div class="mcb-row-main">
                                            <div class="mcb-row-label" x-text="child.label || 'Untitled'"></div>
                                            <div class="mcb-row-url" x-show="child.url" x-text="child.url"></div>
                                        </div>

                                        <div class="mcb-actions" @click.stop @dragover.prevent @drop.prevent>
                                            <button type="button" class="mcb-act" title="Edit" @click="beginEdit(child)">&#9998;</button>
                                            <button type="button" class="mcb-act" title="Duplicate" @click="duplicate(child)">&#12847;</button>
                                            <button type="button" class="mcb-act mcb-act--danger" title="Delete" @click="remove(child)">&#10005;</button>
                                        </div>

                                        <div class="mcb-drop" @dragover.prevent="dragOver($event, child._uid)" @drop.prevent="drop($event, child._uid)"></div>
                                    </div>
                                </template>
                            </div>

                            <div class="mcb-children-empty" x-show="isExpanded(item) && !hasChildren(item)" @click="addChild(item)">
                                + Add a child item
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mcb-pane-foot">
                    <button type="button" class="mcb-btn" @click="addItem()">+ Add another item</button>
                    <span class="mcb-hint">Changes save when you press <strong>Save</strong> below.</span>
                </div>
            </div>

            <!-- ============================ Inspector ============================ -->
            <div class="mcb-pane mcb-pane--edit">
                <template x-if="editingNode()">
                    <div>
                        <div class="mcb-pane-head">
                            <div>
                                <div class="mcb-pane-title">Edit item</div>
                                <div class="mcb-pane-sub" x-text="editingNode()?.label"></div>
                            </div>
                            <button type="button" class="mcb-btn mcb-btn--ghost" @click="cancelEdit()">Close</button>
                        </div>

                        <div class="mcb-field">
                            <label class="mcb-label">Label <span class="mcb-req">*</span></label>
                            <input type="text" x-model="draft.label" class="mcb-input" placeholder="e.g. About GoCare">
                            <div class="mcb-error" x-show="draftError" x-text="draftError"></div>
                        </div>

                        <div class="mcb-field">
                            <label class="mcb-label">Header label <span class="mcb-opt">optional</span></label>
                            <input type="text" x-model="draft.header_label" class="mcb-input" placeholder="Shown in the dropdown header">
                        </div>

                        <div class="mcb-field">
                            <label class="mcb-label">URL <span class="mcb-opt">optional</span></label>
                            <input type="text" x-model="draft.url" class="mcb-input" placeholder="/about, /courses, or https://…">
                        </div>

                        <div class="mcb-field">
                            <label class="mcb-label">Description <span class="mcb-opt">optional</span></label>
                            <textarea x-model="draft.description" rows="3" class="mcb-input mcb-input--area" placeholder="Small subtitle under the label"></textarea>
                        </div>

                        <div class="mcb-grid-2">
                            <div class="mcb-field">
                                <label class="mcb-label">Icon <span class="mcb-opt">lucide</span></label>
                                <input type="text" x-model="draft.icon" class="mcb-input" placeholder="e.g. info, globe, heart" list="mcb-icon-list">
                                <datalist id="mcb-icon-list">
                                    <option value="info"></option><option value="home"></option><option value="flag"></option>
                                    <option value="heart"></option><option value="globe"></option><option value="check-circle"></option>
                                    <option value="graduation-cap"></option><option value="stethoscope"></option><option value="utensils"></option>
                                    <option value="users"></option><option value="search"></option><option value="clipboard-list"></option>
                                    <option value="file-edit"></option><option value="calendar"></option><option value="wallet"></option>
                                    <option value="headphones"></option><option value="book-open"></option><option value="bed"></option>
                                    <option value="briefcase"></option><option value="handshake"></option><option value="globe-2"></option>
                                    <option value="heart-pulse"></option><option value="layers"></option><option value="download"></option>
                                    <option value="edit-3"></option><option value="message-circle"></option><option value="images"></option>
                                </datalist>
                            </div>

                            <div class="mcb-field">
                                <label class="mcb-label">Colour</label>
                                <div class="mcb-swatches">
                                    <button
                                        type="button"
                                        class="mcb-swatch"
                                        :class="{ 'is-active': draft.color === '' }"
                                        @click="draft.color = ''"
                                        title="No colour"
                                    >
                                        <span class="mcb-swatch-x">&#10005;</span>
                                    </button>
                                    <template x-for="(hex, name) in colorMap" :key="name">
                                        <button
                                            type="button"
                                            class="mcb-swatch"
                                            :class="{ 'is-active': draft.color === name }"
                                            :style="{ background: hex }"
                                            :title="name"
                                            @click="draft.color = name"
                                        ></button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="mcb-field">
                            <label class="mcb-label">Dropdown type <span class="mcb-opt">optional</span></label>
                            <select x-model="draft.mega" class="mcb-input">
                                <option value="">No dropdown — plain link</option>
                                <option value="standard">Standard mega menu (620px)</option>
                                <option value="resources">Resources mega menu (680px, right)</option>
                            </select>
                        </div>

                        <div class="mcb-field">
                            <label class="mcb-label">Open in</label>
                            <select x-model="draft.target" class="mcb-input">
                                <option value="">Same tab</option>
                                <option value="_blank">New tab</option>
                            </select>
                        </div>

                        <div class="mcb-edit-actions">
                            <button type="button" class="mcb-btn mcb-btn--primary" @click="saveEdit()">Save item</button>
                            <button type="button" class="mcb-btn mcb-btn--ghost" @click="cancelEdit()">Cancel</button>
                        </div>
                    </div>
                </template>

                <template x-if="!editingNode()">
                    <div class="mcb-empty mcb-empty--edit">
                        <div class="mcb-empty-icon">&#9998;</div>
                        <div class="mcb-empty-title">Select an item to edit</div>
                        <div class="mcb-empty-sub">Click the pencil icon on any item to edit its label, URL, icon and dropdown settings.</div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ============================ Preview ============================ -->
        <div class="mcb-preview">
            <div class="mcb-preview-head">
                <div class="mcb-pane-title">Live preview</div>
                <span class="mcb-preview-loc" x-text="menuLocation === 'primary' ? 'Header navigation' : (menuLocation === 'mobile' ? 'Mobile menu' : 'Footer')"></span>
            </div>

            <!-- Primary nav preview -->
            <template x-if="menuLocation === 'primary'">
                <div class="mcb-ph mcb-ph--nav">
                    <div class="mcb-ph-logo">GoCare</div>
                    <div class="mcb-ph-links">
                        <template x-for="item in tree" :key="item._uid">
                            <div class="mcb-ph-link-wrp">
                                <a class="mcb-ph-link" :href="item.url || '#'" x-text="item.label"></a>
                                <div class="mcb-ph-drop" x-show="hasChildren(item) && isExpanded(item)">
                                    <template x-for="child in item.children" :key="child._uid">
                                        <a class="mcb-ph-drop-item" :href="child.url || '#'">
                                            <span class="mcb-ph-drop-ico" :style="{ background: chipColor(child.color) }"></span>
                                            <span class="mcb-ph-drop-copy">
                                                <strong x-text="child.label"></strong>
                                                <small x-show="child.description" x-text="child.description"></small>
                                            </span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                    <span class="mcb-ph-cta">Start Your Journey</span>
                </div>
            </template>

            <!-- Mobile menu preview -->
            <template x-if="menuLocation === 'mobile'">
                <div class="mcb-ph mcb-ph--mobile">
                    <template x-for="item in tree" :key="item._uid">
                        <div class="mcb-mob-item">
                            <a :href="item.url || '#'" x-text="item.label"></a>
                            <div class="mcb-mob-children" x-show="hasChildren(item) && isExpanded(item)">
                                <template x-for="child in item.children" :key="child._uid">
                                    <a :href="child.url || '#'" x-text="child.label"></a>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Footer preview -->
            <template x-if="menuLocation === 'footer'">
                <div class="mcb-ph mcb-ph--footer">
                    <template x-for="col in tree" :key="col._uid">
                        <div class="mcb-footer-col">
                            <div class="mcb-footer-heading" x-text="col.label"></div>
                            <template x-for="link in col.children" :key="link._uid">
                                <a class="mcb-footer-link" :href="link.url || '#'" x-text="link.label"></a>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <style>
        .mcb {
            --mcb-ink: #131629;
            --mcb-muted: #8b92a7;
            --mcb-line: #e9edf5;
            --mcb-indigo: #5b5cf6;
            --mcb-violet: #7c3aed;
            --mcb-navy: #14141f;
            --mcb-canvas: #f6f8fc;
            --mcb-orange: #ec7424;
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            color: var(--mcb-ink);
        }

        .mcb *,
        .mcb *::before,
        .mcb *::after { box-sizing: border-box; }

        .mcb-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr);
            gap: 1rem;
            align-items: start;
        }

        .mcb-pane {
            border: 1px solid var(--mcb-line);
            border-radius: 1rem;
            background: #fff;
            overflow: hidden;
            min-width: 0;
        }

        .mcb-pane-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: 1rem 1.15rem;
            border-bottom: 1px solid var(--mcb-line);
            background: linear-gradient(180deg, #fafbfe, #f6f8fc);
        }

        .mcb-pane-title { font-size: .92rem; font-weight: 800; color: var(--mcb-ink); }
        .mcb-pane-sub { margin-top: .2rem; font-size: .72rem; color: var(--mcb-muted); }

        .mcb-filter { position: relative; margin: .9rem 1rem 0; }
        .mcb-filter-icon {
            position: absolute; left: .75rem; top: 50%; transform: translateY(-50%);
            font-size: .8rem; color: var(--mcb-muted); pointer-events: none;
        }
        .mcb-filter .mcb-input { padding-left: 2.1rem; }

        .mcb-input {
            width: 100%;
            padding: .55rem .8rem;
            border: 1px solid #dfe5ef;
            border-radius: .6rem;
            background: #fff;
            font-size: .85rem;
            font-family: inherit;
            color: var(--mcb-ink);
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .mcb-input:focus { border-color: var(--mcb-indigo); box-shadow: 0 0 0 3px rgba(91, 92, 246, .12); }
        .mcb-input--area { resize: vertical; line-height: 1.5; }

        .mcb-tree { padding: .6rem 1rem 1rem; }
        .mcb-item { position: relative; }

        .mcb-row {
            position: relative;
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-top: .45rem;
            padding: .55rem .7rem;
            border: 1px solid var(--mcb-line);
            border-radius: .7rem;
            background: #fff;
            transition: border-color .15s ease, box-shadow .15s ease, transform .12s ease;
        }

        .mcb-row--top { box-shadow: 0 1px 2px rgba(23, 23, 39, .04); }
        .mcb-row--top .mcb-row-main .mcb-row-label { font-weight: 750; }

        .mcb-row--child {
            margin-top: .35rem;
            background: #fbfcff;
            border-color: #eef1f8;
            margin-left: 1.1rem;
            border-left: 3px solid #dfe5f2;
        }

        .mcb-row:hover { border-color: #cdd6ea; box-shadow: 0 3px 10px rgba(23, 23, 39, .07); }

        .mcb-row--drop-before { border-top: 3px solid var(--mcb-indigo); box-shadow: 0 -2px 0 0 var(--mcb-indigo); }
        .mcb-row--drop-after { border-bottom: 3px solid var(--mcb-indigo); box-shadow: 0 2px 0 0 var(--mcb-indigo); }
        .mcb-row--drop-inside { border-color: var(--mcb-indigo); box-shadow: 0 0 0 3px rgba(91, 92, 246, .18); }

        .mcb-grip {
            flex-shrink: 0;
            cursor: grab;
            color: #c3cbd9;
            font-size: 1rem;
            padding: .1rem .2rem;
            user-select: none;
            transition: color .15s ease;
        }
        .mcb-grip:hover { color: var(--mcb-indigo); }
        .mcb-grip:active { cursor: grabbing; }

        .mcb-chev {
            flex-shrink: 0;
            width: 1.35rem; height: 1.35rem;
            display: inline-flex; align-items: center; justify-content: center;
            border: 1px solid var(--mcb-line);
            border-radius: .4rem;
            background: #fafbfe;
            color: var(--mcb-muted);
            font-size: .7rem;
            cursor: pointer;
            transition: transform .18s ease, color .15s ease, background-color .15s ease;
        }
        .mcb-chev:hover { color: var(--mcb-indigo); background: #f0f1ff; }
        .mcb-chev.is-open { transform: rotate(180deg); color: var(--mcb-indigo); }

        .mcb-chip {
            flex-shrink: 0;
            width: .6rem; height: .6rem;
            border-radius: 999px;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .9), 0 0 0 4px rgba(23, 23, 39, .08);
        }
        .mcb-chip--sm { width: .5rem; height: .5rem; }

        .mcb-row-main { flex: 1; min-width: 0; }
        .mcb-row-label {
            font-size: .84rem;
            color: var(--mcb-ink);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .mcb-row-url {
            margin-top: .1rem;
            font-size: .72rem;
            color: var(--mcb-muted);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .mcb-count {
            flex-shrink: 0;
            min-width: 1.15rem; height: 1.15rem;
            padding: 0 .3rem;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 999px;
            background: #eef0ff;
            color: #4c4de3;
            font-size: .68rem; font-weight: 800;
        }

        .mcb-actions {
            display: flex; align-items: center; gap: .15rem;
            flex-shrink: 0;
            opacity: .35;
            transition: opacity .15s ease;
        }
        .mcb-row:hover .mcb-actions { opacity: 1; }

        .mcb-act {
            width: 1.55rem; height: 1.55rem;
            display: inline-flex; align-items: center; justify-content: center;
            border: none; border-radius: .45rem;
            background: transparent;
            color: var(--mcb-muted);
            font-size: .78rem;
            cursor: pointer;
            transition: color .15s ease, background-color .15s ease;
        }
        .mcb-act:hover { background: #f0f1ff; color: var(--mcb-indigo); }
        .mcb-act--danger:hover { background: #ffeef1; color: #be123c; }

        .mcb-guide {
            flex-shrink: 0;
            width: .55rem; height: 1px;
            background: #dfe5f2;
        }

        .mcb-drop {
            position: absolute; inset: 0;
            z-index: 1;
        }
        .mcb-grip, .mcb-chev, .mcb-chip, .mcb-row-main, .mcb-count, .mcb-actions {
            position: relative; z-index: 2;
        }

        .mcb-children { padding: 0 0 .2rem; }

        .mcb-children-empty {
            margin: .35rem 0 0 2.2rem;
            padding: .4rem .7rem;
            border: 1px dashed #d8dfee;
            border-radius: .55rem;
            color: var(--mcb-muted);
            font-size: .76rem;
            cursor: pointer;
            transition: color .15s ease, border-color .15s ease, background-color .15s ease;
        }
        .mcb-children-empty:hover { color: var(--mcb-indigo); border-color: var(--mcb-indigo); background: #fafbff; }

        .mcb-pane-foot {
            display: flex; align-items: center; justify-content: space-between; gap: .75rem;
            padding: .85rem 1.15rem;
            border-top: 1px solid var(--mcb-line);
            background: #fafbfe;
        }
        .mcb-hint { font-size: .72rem; color: var(--mcb-muted); }

        /* Buttons */
        .mcb-btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .5rem .85rem;
            border: 1px solid var(--mcb-line);
            border-radius: .6rem;
            background: #fff;
            color: var(--mcb-ink);
            font-size: .8rem; font-weight: 750;
            font-family: inherit;
            cursor: pointer;
            transition: all .15s ease;
            white-space: nowrap;
        }
        .mcb-btn:hover { border-color: #cdd6ea; background: #fafbfe; }
        .mcb-btn--primary {
            background: linear-gradient(135deg, var(--mcb-indigo), var(--mcb-violet));
            border: none;
            color: #fff;
            box-shadow: 0 6px 16px rgba(91, 92, 246, .28);
        }
        .mcb-btn--primary:hover { filter: brightness(1.05); background: linear-gradient(135deg, var(--mcb-indigo), var(--mcb-violet)); }
        .mcb-btn--ghost { background: transparent; border-color: transparent; color: var(--mcb-muted); }
        .mcb-btn--ghost:hover { background: #f0f1ff; color: var(--mcb-indigo); border-color: transparent; }
        .mcb-btn-icon { font-size: 1rem; line-height: 1; }

        /* Empty */
        .mcb-empty { padding: 2.4rem 1.5rem; text-align: center; }
        .mcb-empty-title { font-size: .9rem; font-weight: 800; color: var(--mcb-ink); }
        .mcb-empty-sub { margin-top: .35rem; font-size: .78rem; color: var(--mcb-muted); line-height: 1.5; }
        .mcb-empty--edit { border: 1px dashed #d8dfee; border-radius: .8rem; margin: .9rem; padding: 2rem 1.25rem; }
        .mcb-empty-icon {
            width: 2.6rem; height: 2.6rem; margin: 0 auto .6rem;
            display: flex; align-items: center; justify-content: center;
            border-radius: .85rem;
            background: #f0f1ff; color: var(--mcb-indigo);
            font-size: 1.05rem;
        }

        /* Inspector form */
        .mcb-pane--edit { padding-bottom: 1rem; }
        .mcb-field { margin: .9rem 1.1rem 0; }
        .mcb-label {
            display: block;
            margin-bottom: .35rem;
            font-size: .74rem; font-weight: 800;
            color: var(--mcb-ink);
        }
        .mcb-opt { color: var(--mcb-muted); font-weight: 600; }
        .mcb-req { color: #be123c; }
        .mcb-error { margin-top: .3rem; font-size: .72rem; color: #be123c; }
        .mcb-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 .9rem; }

        .mcb-swatches { display: flex; flex-wrap: wrap; gap: .4rem; padding-top: .15rem; }
        .mcb-swatch {
            width: 1.6rem; height: 1.6rem;
            display: inline-flex; align-items: center; justify-content: center;
            border: 2px solid #fff;
            border-radius: 999px;
            cursor: pointer;
            box-shadow: 0 0 0 1px #dfe5ef;
            transition: transform .12s ease, box-shadow .12s ease;
        }
        .mcb-swatch:hover { transform: scale(1.12); }
        .mcb-swatch.is-active { box-shadow: 0 0 0 2px var(--mcb-indigo); transform: scale(1.08); }
        .mcb-swatch-x { font-size: .7rem; color: #a4abc0; }

        .mcb-edit-actions {
            display: flex; gap: .6rem;
            margin: 1.1rem 1.1rem 0;
            padding-top: 1rem;
            border-top: 1px solid var(--mcb-line);
        }

        /* Preview */
        .mcb-preview {
            margin-top: 1rem;
            border: 1px solid var(--mcb-line);
            border-radius: 1rem;
            background: #fff;
            overflow: hidden;
        }
        .mcb-preview-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: .85rem 1.15rem;
            border-bottom: 1px solid var(--mcb-line);
            background: linear-gradient(180deg, #fafbfe, #f6f8fc);
        }
        .mcb-preview-loc {
            padding: .25rem .6rem;
            border-radius: 999px;
            background: #eef0ff; color: #4c4de3;
            font-size: .7rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .05em;
        }

        .mcb-ph { padding: 1.15rem; background: #f8fafc; }
        .mcb-ph--nav {
            display: flex; align-items: center; gap: 1.5rem;
            padding: 1rem 1.25rem;
            border-radius: .8rem;
            background: var(--mcb-navy);
            color: #fff;
        }
        .mcb-ph-logo { font-size: 1.05rem; font-weight: 900; letter-spacing: -.01em; }
        .mcb-ph-links { display: flex; align-items: center; gap: 1.1rem; flex: 1; }
        .mcb-ph-link-wrp { position: relative; }
        .mcb-ph-link {
            color: rgba(255, 255, 255, .88);
            font-size: .78rem; font-weight: 700;
            text-decoration: none;
            transition: color .15s ease;
        }
        .mcb-ph-link:hover { color: #fff; }
        .mcb-ph-drop {
            position: absolute; top: calc(100% + .6rem); left: 0;
            min-width: 15rem;
            padding: .55rem;
            border-radius: .75rem;
            background: #fff;
            box-shadow: 0 18px 40px rgba(23, 23, 39, .18);
        }
        .mcb-ph-drop-item {
            display: flex; align-items: center; gap: .7rem;
            padding: .55rem .6rem;
            border-radius: .55rem;
            text-decoration: none;
            color: var(--mcb-ink);
            transition: background-color .15s ease;
        }
        .mcb-ph-drop-item:hover { background: #f4f6fb; }
        .mcb-ph-drop-ico { width: 1.7rem; height: 1.7rem; flex-shrink: 0; border-radius: .5rem; }
        .mcb-ph-drop-copy { display: flex; flex-direction: column; }
        .mcb-ph-drop-copy strong { font-size: .78rem; }
        .mcb-ph-drop-copy small { font-size: .68rem; color: var(--mcb-muted); margin-top: .05rem; }
        .mcb-ph-cta {
            padding: .5rem .95rem;
            border-radius: 999px;
            background: var(--mcb-orange);
            color: #fff;
            font-size: .74rem; font-weight: 750;
            white-space: nowrap;
        }

        .mcb-ph--mobile { padding: 1rem; border-radius: .8rem; background: #fff; }
        .mcb-mob-item { border-bottom: 1px solid var(--mcb-line); }
        .mcb-mob-item:last-child { border-bottom: none; }
        .mcb-mob-item > a {
            display: block; padding: .7rem .2rem;
            color: var(--mcb-ink); font-size: .82rem; font-weight: 700;
            text-decoration: none;
        }
        .mcb-mob-children { padding: 0 0 .5rem 1rem; }
        .mcb-mob-children a {
            display: block; padding: .35rem .2rem;
            color: var(--mcb-muted); font-size: .78rem;
            text-decoration: none;
        }

        .mcb-ph--footer {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr));
            gap: 1.4rem;
            padding: 1.25rem;
            border-radius: .8rem;
            background: #1a1a2e;
            color: #fff;
        }
        .mcb-footer-heading {
            margin-bottom: .6rem;
            font-size: .76rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .05em;
            color: rgba(255, 255, 255, .92);
        }
        .mcb-footer-link {
            display: block;
            margin: .35rem 0;
            color: rgba(255, 255, 255, .7);
            font-size: .76rem;
            text-decoration: none;
            transition: color .15s ease;
        }
        .mcb-footer-link:hover { color: #fff; }

        @media (max-width: 900px) {
            .mcb-grid { grid-template-columns: 1fr; }
        }
    </style>
</x-dynamic-component>

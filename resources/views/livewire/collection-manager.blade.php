<div>
    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="separator">/</span>
            <span>Collections</span>
        </div>
        <div class="page-header-row">
            <h1 class="page-title">⚙️ Collections</h1>
            <button wire:click="create" class="btn btn-primary">
                <span>+</span> New Collection
            </button>
        </div>
    </div>

    <div class="page-content">
        {{-- Collection Grid --}}
        @if($collections->count())
            <div class="collection-grid">
                @foreach($collections as $col)
                    <div class="collection-card">
                        <div class="collection-card-actions">
                            <button wire:click="edit({{ $col->id }})" class="btn btn-sm btn-secondary btn-icon" title="Edit">✏️</button>
                            <a href="{{ route('collections.fields', $col->slug) }}" class="btn btn-sm btn-secondary btn-icon" title="Fields">🔧</a>
                            @if($confirmingDelete === $col->id)
                                <button wire:click="delete({{ $col->id }})" class="btn btn-sm btn-danger">Yes, Delete</button>
                                <button wire:click="cancelDelete" class="btn btn-sm btn-secondary">Cancel</button>
                            @else
                                <button wire:click="confirmDelete({{ $col->id }})" class="btn btn-sm btn-danger btn-icon" title="Delete">🗑️</button>
                            @endif
                        </div>

                        <a href="{{ route('entries.index', $col->slug) }}">
                            <div class="collection-card-icon">{{ $col->icon }}</div>
                            <div class="collection-card-name">
                                {{ $col->display_name }}
                                @if($col->type === 'static')
                                    <span class="badge badge-warning" style="margin-left: 6px; vertical-align: middle; font-size: 10px;">Static</span>
                                @else
                                    <span class="badge badge-info" style="margin-left: 6px; vertical-align: middle; font-size: 10px;">Tabular</span>
                                @endif
                            </div>
                            <div class="collection-card-desc">{{ $col->description ?: 'No description' }}</div>
                            <div class="collection-card-stats">
                                <div class="collection-card-stat">
                                    <span class="collection-card-stat-value">{{ $col->entries_count }}</span>
                                    <span class="collection-card-stat-label">Entries</span>
                                </div>
                                <div class="collection-card-stat">
                                    <span class="collection-card-stat-value">{{ $col->fields_count }}</span>
                                    <span class="collection-card-stat-label">Fields</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📦</div>
                <div class="empty-state-text">No collections yet</div>
                <div class="empty-state-sub">Create your first collection to start managing game data</div>
                <button wire:click="create" class="btn btn-primary">
                    <span>+</span> Create Collection
                </button>
            </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if($showForm)
        <div class="modal-backdrop" wire:click.self="resetForm">
            <div class="modal-content">
                <div class="modal-title">
                    {{ $editingId ? '✏️ Edit Collection' : '✨ New Collection' }}
                </div>

                <div class="form-group">
                    <label class="form-label">Name (slug) <span class="required">*</span></label>
                    <input type="text" wire:model.debounce.300ms="name" class="form-input" placeholder="e.g. enemies, skills, items">
                    @error('name') <div class="form-help" style="color:var(--danger)">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Display Name <span class="required">*</span></label>
                    <input type="text" wire:model="display_name" class="form-input" placeholder="e.g. Enemies, Skills, Items">
                    @error('display_name') <div class="form-help" style="color:var(--danger)">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Collection Type <span class="required">*</span></label>
                    <select wire:model="type" class="form-select">
                        <option value="tabular">📊 Tabular (Spreadsheet/List)</option>
                        <option value="static">🌲 Static (Raw Nested JSON)</option>
                    </select>
                    @error('type') <div class="form-help" style="color:var(--danger)">{{ $message }}</div> @enderror
                    <div class="form-help">Tabular is best for repetitive database-like records (e.g. items, monsters). Static is best for single-entry hierarchical configurations (e.g. game settings, HUD layout).</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Icon (Emoji)</label>
                        <input type="text" wire:model="icon" class="form-input" placeholder="📦">
                    </div>
                    <div class="form-group">
                        <label class="form-label">ID Field</label>
                        <input type="text" wire:model="id_field" class="form-input" placeholder="ID">
                        <div class="form-help">JSON key used as primary identifier</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Name Field</label>
                        <input type="text" wire:model="name_field" class="form-input" placeholder="Name">
                        <div class="form-help">JSON key shown as display name</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea wire:model="description" class="form-textarea" rows="3" placeholder="Optional description for this collection"></textarea>
                </div>

                <div class="modal-footer">
                    <button wire:click="resetForm" class="btn btn-secondary">Cancel</button>
                    <button wire:click="save" class="btn btn-primary">
                        {{ $editingId ? 'Update' : 'Create' }} Collection
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

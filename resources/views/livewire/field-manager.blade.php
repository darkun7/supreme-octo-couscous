<div>
    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="separator">/</span>
            <a href="{{ route('collections.index') }}">Collections</a>
            <span class="separator">/</span>
            <span>{{ $collection->display_name }} — Fields</span>
        </div>
        <div class="page-header-row">
            <h1 class="page-title">🔧 {{ $collection->display_name }} Fields</h1>
            <div class="btn-group">
                <a href="{{ route('entries.index', $collection->slug) }}" class="btn btn-secondary">
                    📋 View Entries
                </a>
                <button wire:click="openImportForm" class="btn btn-secondary" title="Import fields from JSON structure">
                    📥 Import from JSON
                </button>
                <button wire:click="create" class="btn btn-primary">
                    <span>+</span> Add Field
                </button>
            </div>
        </div>
    </div>

    <div class="page-content">
        {{-- Field Info --}}
        <div class="card mb-20">
            <div class="d-flex align-center gap-16">
                <div style="font-size: 40px;">{{ $collection->icon }}</div>
                <div>
                    <div class="card-title">{{ $collection->display_name }}</div>
                    <div class="text-secondary text-sm">
                        ID Field: <code class="font-mono" style="color:var(--accent-primary-hover)">{{ $collection->id_field }}</code> &nbsp;|&nbsp;
                        Name Field: <code class="font-mono" style="color:var(--accent-primary-hover)">{{ $collection->name_field }}</code>
                    </div>
                    @if($collection->description)
                        <div class="text-tertiary text-sm mt-8">{{ $collection->description }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Field List --}}
        @if(count($fields))
            <div class="field-list" id="sortable-field-list" wire:ignore.self>
                @php
                    $rootFields = collect($fields)->whereNull('parent_key')->where('parent_key', '');
                    $rootFields = $rootFields->merge(collect($fields)->whereNull('parent_key'));
                    $rootFields = collect($fields)->filter(fn($f) => empty($f['parent_key']));
                @endphp

                @foreach($rootFields as $field)
                  <div class="sortable-group" data-id="{{ $field['id'] }}">
                    <div class="field-item">
                        <div class="drag-handle" style="cursor: grab; margin-right: 8px; font-size: 16px; opacity: 0.5;">☰</div>
                        <div class="field-item-key">{{ $field['key'] }}</div>
                        <span class="field-item-type field-type-{{ $field['type'] }}">{{ $field['type'] }}</span>
                        <div class="field-item-label">
                            {{ $field['label'] }}
                            @if($field['required']) <span class="badge badge-warning">required</span> @endif
                            @if($field['is_array']) <span class="badge badge-info">array</span> @endif
                        </div>
                        @if($field['type'] === 'relation' && !empty($field['relation_collection']))
                            <div class="field-item-relation">
                                🔗 {{ $field['relation_collection']['display_name'] ?? 'Unknown' }}
                                @if($field['relation_multiple']) (multi) @endif
                            </div>
                        @endif
                        <div class="field-item-actions">
                            <button wire:click="moveUp({{ $field['id'] }})" class="btn btn-sm btn-secondary btn-icon" title="Move Up">↑</button>
                            <button wire:click="moveDown({{ $field['id'] }})" class="btn btn-sm btn-secondary btn-icon" title="Move Down">↓</button>
                            <button wire:click="edit({{ $field['id'] }})" class="btn btn-sm btn-secondary btn-icon" title="Edit">✏️</button>
                            @if($field['type'] === 'object' || $field['type'] === 'array_of_objects')
                                <button wire:click="createChildField('{{ $field['key'] }}')" class="btn btn-sm btn-success btn-icon" title="Add Child Field">+</button>
                            @endif
                            @if($confirmingDelete === $field['id'])
                                <button wire:click="delete({{ $field['id'] }})" class="btn btn-sm btn-danger">Delete</button>
                                <button wire:click="cancelDelete" class="btn btn-sm btn-secondary">Cancel</button>
                            @else
                                <button wire:click="confirmDelete({{ $field['id'] }})" class="btn btn-sm btn-danger btn-icon" title="Delete">🗑️</button>
                            @endif
                        </div>
                    </div>

                    {{-- Nested fields --}}
                    @if($field['type'] === 'object' || $field['type'] === 'array_of_objects')
                        @php
                            $childFields = collect($fields)->where('parent_key', $field['key']);
                        @endphp
                        @foreach($childFields as $child)
                            <div class="field-item nested">
                                <div class="field-item-key">↳ {{ $child['key'] }}</div>
                                <span class="field-item-type field-type-{{ $child['type'] }}">{{ $child['type'] }}</span>
                                <div class="field-item-label">
                                    {{ $child['label'] }}
                                    @if($child['required']) <span class="badge badge-warning">required</span> @endif
                                </div>
                                @if($child['type'] === 'relation' && !empty($child['relation_collection']))
                                    <div class="field-item-relation">
                                        🔗 {{ $child['relation_collection']['display_name'] ?? 'Unknown' }}
                                    </div>
                                @endif
                                <div class="field-item-actions">
                                    <button wire:click="edit({{ $child['id'] }})" class="btn btn-sm btn-secondary btn-icon">✏️</button>
                                    @if($confirmingDelete === $child['id'])
                                        <button wire:click="delete({{ $child['id'] }})" class="btn btn-sm btn-danger">Delete</button>
                                        <button wire:click="cancelDelete" class="btn btn-sm btn-secondary">Cancel</button>
                                    @else
                                        <button wire:click="confirmDelete({{ $child['id'] }})" class="btn btn-sm btn-danger btn-icon">🗑️</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                  </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">🔧</div>
                <div class="empty-state-text">No fields defined yet</div>
                <div class="empty-state-sub">Define the schema for your {{ $collection->display_name }} collection</div>
                <button wire:click="create" class="btn btn-primary">
                    <span>+</span> Add First Field
                </button>
            </div>
        @endif
    </div>

    {{-- Create/Edit Field Modal --}}
    @if($showForm)
        <div class="modal-backdrop" wire:click.self="resetFieldForm">
            <div class="modal-content">
                <div class="modal-title">
                    {{ $editingFieldId ? '✏️ Edit Field' : '✨ New Field' }}
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Key <span class="required">*</span></label>
                        <input type="text" wire:model="fieldForm.key" class="form-input" placeholder="e.g. SkillIDs, Stats">
                        <div class="form-help">The JSON key name</div>
                        @error('fieldForm.key') <div class="form-help" style="color:var(--danger)">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Label <span class="required">*</span></label>
                        <input type="text" wire:model="fieldForm.label" class="form-input" placeholder="e.g. Skill IDs, Statistics">
                        @error('fieldForm.label') <div class="form-help" style="color:var(--danger)">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Type <span class="required">*</span></label>
                        <select wire:model="fieldForm.type" class="form-select">
                            <option value="string">String</option>
                            <option value="number">Number</option>
                            <option value="boolean">Boolean</option>
                            <option value="array">Array (simple)</option>
                            <option value="object">Object (nested)</option>
                            <option value="array_of_objects">Array of Objects</option>
                            <option value="relation">Relation (linked collection)</option>
                            <option value="color">Color</option>
                            <option value="image_url">Image URL</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Parent Key</label>
                        <select wire:model="fieldForm.parent_key" class="form-select">
                            <option value="">— Root Level —</option>
                            @foreach($this->parentKeys as $pk)
                                <option value="{{ $pk }}">{{ $pk }}</option>
                            @endforeach
                        </select>
                        <div class="form-help">Nest this field inside an object</div>
                    </div>
                </div>

                {{-- Relation-specific fields --}}
                @if($fieldForm['type'] === 'relation')
                    <div class="card mt-16 mb-16" style="background:var(--warning-bg); border-color: rgba(245,158,11,0.2);">
                        <div class="card-title" style="color:var(--warning); margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid rgba(245,158,11,0.2);">
                            🔗 Relation Configuration
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Related Collection</label>
                                <select wire:model="fieldForm.relation_collection_id" class="form-select">
                                    <option value="">— Select —</option>
                                    @foreach($allCollections as $rc)
                                        <option value="{{ $rc['id'] }}">{{ $rc['icon'] }} {{ $rc['display_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Display Field</label>
                                <input type="text" wire:model="fieldForm.relation_display_field" class="form-input" placeholder="Name">
                                <div class="form-help">Field to show as label</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Value Field</label>
                                <input type="text" wire:model="fieldForm.relation_value_field" class="form-input" placeholder="ID">
                                <div class="form-help">Field value to store</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-checkbox-group">
                                <input type="checkbox" wire:model="fieldForm.relation_multiple" class="form-checkbox" id="relation_multiple">
                                <label for="relation_multiple" class="form-checkbox-label">Allow multiple selections (array)</label>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label">Options (JSON or comma-separated)</label>
                    <input type="text" wire:model="fieldForm.options" class="form-input font-mono" placeholder='e.g. ["Fire","Water","Earth"] or Fire,Water,Earth'>
                    <div class="form-help">For select/dropdown fields</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Default Value</label>
                    <input type="text" wire:model="fieldForm.default_value" class="form-input font-mono" placeholder="Default value for new entries">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <div class="form-checkbox-group">
                            <input type="checkbox" wire:model="fieldForm.required" class="form-checkbox" id="field_required">
                            <label for="field_required" class="form-checkbox-label">Required field</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-checkbox-group">
                            <input type="checkbox" wire:model="fieldForm.is_array" class="form-checkbox" id="field_is_array">
                            <label for="field_is_array" class="form-checkbox-label">Is Array</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Help Text</label>
                    <input type="text" wire:model="fieldForm.help_text" class="form-input" placeholder="Tooltip help text for this field">
                </div>

                <div class="modal-footer">
                    <button wire:click="resetFieldForm" class="btn btn-secondary">Cancel</button>
                    <button wire:click="save" class="btn btn-primary">
                        {{ $editingFieldId ? 'Update' : 'Add' }} Field
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Import JSON Modal --}}
    @if($showImportForm)
        <div class="modal-backdrop" wire:click.self="closeImportForm">
            <div class="modal-content">
                <div class="modal-title">
                    📥 Import Fields from JSON
                </div>
                
                <div class="mb-16 text-sm text-secondary">
                    Paste a sample JSON representation of your data. The system will automatically parse it and create fields, guessing the data types (string, number, array, object, color, etc.).
                </div>

                <div class="form-group">
                    <label class="form-label">Sample JSON Payload <span class="required">*</span></label>
                    <textarea wire:model.defer="importJson" class="form-input font-mono" rows="10" placeholder='{
  "name": "Sword",
  "damage": 15,
  "stats": {
    "speed": 1.5,
    "crit_chance": 0.2
  },
  "tags": ["weapon", "melee"]
}'></textarea>
                    @error('importJson') <div class="form-help" style="color:var(--danger)">{{ $message }}</div> @enderror
                </div>

                <div class="modal-footer">
                    <button wire:click="closeImportForm" class="btn btn-secondary">Cancel</button>
                    <button wire:click="processImport" class="btn btn-primary">
                        🚀 Parse & Auto-Generate Fields
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        document.addEventListener('livewire:load', function () {
            function initSortable() {
                let el = document.getElementById('sortable-field-list');
                if (el && !el.sortableInstance) {
                    el.sortableInstance = new Sortable(el, {
                        animation: 150,
                        handle: '.drag-handle',
                        ghostClass: 'sortable-ghost',
                        onEnd: function (evt) {
                            let order = [];
                            document.querySelectorAll('.sortable-group').forEach(item => {
                                let id = item.getAttribute('data-id');
                                if (id) order.push(id);
                            });
                            if (order.length > 0) {
                                @this.updateOrder(order);
                            }
                        }
                    });
                }
            }
            
            initSortable();

            Livewire.hook('message.processed', (message, component) => {
                initSortable();
            });
        });
    </script>
    <style>
        .sortable-ghost {
            opacity: 0.4;
            background: var(--accent-glow) !important;
        }
    </style>
</div>

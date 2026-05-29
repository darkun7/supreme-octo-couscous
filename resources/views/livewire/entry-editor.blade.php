<div>
    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="separator">/</span>
            <a href="{{ route('entries.index', $collection->slug) }}">{{ $collection->display_name }}</a>
            <span class="separator">/</span>
            <span>{{ $isNew ? 'New Entry' : 'Edit Entry' }}</span>
        </div>
        <div class="page-header-row">
            <h1 class="page-title">
                {{ $collection->icon }}
                {{ $isNew ? 'New ' . $collection->display_name : 'Edit: ' . ($formData[$collection->name_field] ?? 'Unnamed') }}
            </h1>
            <div style="display: flex; align-items: center; gap: 12px;">
                @if($isS3Configured)
                    <label class="form-checkbox-group" style="cursor: pointer; user-select: none; background: rgba(99, 102, 241, 0.05); border: 1px solid var(--border-accent); padding: 8px 12px; border-radius: var(--radius-sm); transition: all 150ms; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.background='rgba(99, 102, 241, 0.1)'" onmouseout="this.style.background='rgba(99, 102, 241, 0.05)'">
                        <input type="checkbox" wire:model="saveToS3" class="form-checkbox" style="accent-color: var(--accent-primary); width: 16px; height: 16px; margin: 0; cursor: pointer;">
                        <span class="form-checkbox-label" style="font-weight: 600; color: var(--text-primary); font-size: 13px; display: flex; align-items: center; gap: 6px;">
                            ☁️ Save to S3 too
                        </span>
                    </label>
                @endif
                <div class="btn-group">
                    <button wire:click="toggleRawJson" class="btn btn-secondary">
                        {{ $showRawJson ? '📝 Form View' : '{ } JSON View' }}
                    </button>
                    <button wire:click="save" class="btn btn-success">💾 Save</button>
                    <button wire:click="saveAndBack" class="btn btn-primary">💾 Save & Back</button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">
        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 24px;">
            {{-- Main Form --}}
            <div>
                @if($showRawJson)
                    {{-- Raw JSON Editor --}}
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">{ } Raw JSON Editor</div>
                            <button wire:click="applyRawJson" class="btn btn-primary btn-sm">Apply Changes</button>
                        </div>
                        <textarea
                            wire:model.lazy="rawJson"
                            class="form-textarea"
                            style="min-height: 500px; font-size: 13px;"
                            spellcheck="false"
                        ></textarea>
                    </div>
                @else
                    {{-- Dynamic Form --}}
                    @php
                        $rootFields = $fields->filter(fn($f) => empty($f->parent_key));
                        $objectFields = $rootFields->whereIn('type', ['object', 'array_of_objects']);
                        $simpleFields = $rootFields->whereNotIn('type', ['object', 'array_of_objects']);
                    @endphp

                    {{-- Simple Fields --}}
                    <div class="card mb-20">
                        <div class="card-header">
                            <div class="card-title">📋 Properties</div>
                        </div>

                        @foreach($simpleFields as $field)
                            @include('livewire.partials.field-input', [
                                'field' => $field,
                                'keyPrefix' => '',
                                'value' => data_get($formData, $field->key),
                            ])
                        @endforeach
                    </div>

                    {{-- Nested Object Fields --}}
                    @foreach($objectFields as $objectField)
                        @if($objectField->type === 'object')
                            <div class="object-group mb-20">
                                <div class="object-group-header">
                                    <div class="object-group-title">
                                        📦 {{ $objectField->label }}
                                        <span class="badge badge-info">object</span>
                                    </div>
                                </div>

                                @php
                                    $childFields = $fields->where('parent_key', $objectField->key);
                                @endphp

                                @foreach($childFields as $child)
                                    @include('livewire.partials.field-input', [
                                        'field' => $child,
                                        'keyPrefix' => $objectField->key . '.',
                                        'value' => data_get($formData, $objectField->key . '.' . $child->key),
                                    ])
                                @endforeach
                            </div>
                        @elseif($objectField->type === 'array_of_objects')
                            <div class="object-group mb-20">
                                <div class="object-group-header">
                                    <div class="object-group-title">
                                        📋 {{ $objectField->label }}
                                        <span class="badge badge-info">array of objects</span>
                                    </div>
                                    <button wire:click="addObjectItem('{{ $objectField->key }}')" class="btn btn-sm btn-primary">
                                        + Add Item
                                    </button>
                                </div>

                                @php
                                    $childFields = $fields->where('parent_key', $objectField->key);
                                    $arrayItems = data_get($formData, $objectField->key, []);
                                @endphp

                                <div class="array-objects-container">
                                    @forelse($arrayItems as $idx => $item)
                                        <div class="array-object-item">
                                            <div class="array-object-item-header">
                                                <span>Item #{{ $idx + 1 }}</span>
                                                <button wire:click="removeObjectItem('{{ $objectField->key }}', {{ $idx }})"
                                                        class="btn btn-sm btn-danger btn-icon" title="Remove">✕</button>
                                            </div>

                                            @foreach($childFields as $child)
                                                @include('livewire.partials.field-input', [
                                                    'field' => $child,
                                                    'keyPrefix' => $objectField->key . '.' . $idx . '.',
                                                    'value' => data_get($formData, $objectField->key . '.' . $idx . '.' . $child->key),
                                                ])
                                            @endforeach
                                        </div>
                                    @empty
                                        <div class="text-center text-tertiary text-sm" style="padding: 20px;">
                                            No items yet. Click "Add Item" to create one.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            {{-- Side Panel — JSON Preview --}}
            <div>
                <div class="card" style="position: sticky; top: 100px;">
                    <div class="card-header">
                        <div class="card-title">🔍 JSON Preview</div>
                    </div>
                    <pre class="json-preview"><code>{!! $this->highlightJson(json_encode($formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !!}</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>

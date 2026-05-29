{{-- Dynamic Field Input Partial --}}
{{-- Variables: $field (GameCollectionField), $keyPrefix (string), $value (mixed) --}}
@php
    $fullKey = $keyPrefix . $field->key;
    $fieldId = 'field_' . str_replace('.', '_', $fullKey);

    // Type validation check
    $typeError = null;
    if ($value !== null) {
        if ($field->is_array || $field->relation_multiple) {
            if (!is_array($value)) {
                $typeError = "Expected array, but got " . gettype($value) . " (value: " . (is_string($value) ? "\"$value\"" : json_encode($value)) . ")";
            }
        } else {
            switch ($field->type) {
                case 'number':
                case 'double':
                case 'float':
                    if (!is_numeric($value)) {
                        $typeError = "Expected number, but got " . gettype($value) . " (value: " . (is_string($value) ? "\"$value\"" : json_encode($value)) . ")";
                    }
                    break;
                case 'boolean':
                    if (!is_bool($value) && $value !== 1 && $value !== 0 && $value !== '1' && $value !== '0' && $value !== 'true' && $value !== 'false') {
                        $typeError = "Expected boolean, but got " . gettype($value) . " (value: " . (is_string($value) ? "\"$value\"" : json_encode($value)) . ")";
                    }
                    break;
                case 'string':
                case 'color':
                case 'image_url':
                    if (is_array($value) || is_object($value)) {
                        $typeError = "Expected string/scalar, but got " . gettype($value);
                    }
                    break;
                case 'relation':
                    if (is_array($value) || is_object($value)) {
                        $typeError = "Expected single relation value, but got " . gettype($value);
                    }
                    break;
            }
        }
    }
@endphp

<div class="form-group" wire:key="field-{{ $fullKey }}">
    @if($field->type !== 'boolean')
        <label class="form-label" for="{{ $fieldId }}">
            {{ $field->label }}
            @if($field->required) <span class="required">*</span> @endif
            @if($field->help_text)
                <span title="{{ $field->help_text }}" style="cursor:help; opacity:0.5;">ℹ️</span>
            @endif
        </label>
    @endif

    @if($typeError)
        <div style="font-size: 11px; color: var(--danger); background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); padding: 6px 10px; border-radius: var(--radius-sm); margin-bottom: 8px; display: flex; align-items: center; gap: 6px; line-height: 1.4;">
            ⚠️ <strong>Type Mismatch:</strong> {{ $typeError }}
        </div>
    @endif

    @switch($field->type)
        {{-- STRING --}}
        @case('string')
            @if($field->options && is_array($field->options))
                <select
                    id="{{ $fieldId }}"
                    class="form-select"
                    wire:change="updateField('{{ $fullKey }}', $event.target.value)"
                >
                    <option value="">— Select —</option>
                    @foreach($field->options as $option)
                        <option value="{{ $option }}" {{ (!is_array($value) && $value == $option) ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            @elseif($field->input_type === 'textarea')
                <textarea
                    id="{{ $fieldId }}"
                    class="form-textarea"
                    wire:change="updateField('{{ $fullKey }}', $event.target.value)"
                    placeholder="{{ $field->help_text ?? '' }}"
                >{{ is_array($value) ? json_encode($value) : $value }}</textarea>
            @else
                <input
                    type="text"
                    id="{{ $fieldId }}"
                    class="form-input"
                    value="{{ is_array($value) ? json_encode($value) : $value }}"
                    wire:change="updateField('{{ $fullKey }}', $event.target.value)"
                    placeholder="{{ $field->help_text ?? '' }}"
                >
            @endif
            @break

        {{-- NUMBER --}}
        @case('number')
        @case('double')
        @case('float')
            <input
                type="number"
                id="{{ $fieldId }}"
                class="form-input"
                value="{{ is_array($value) ? 0 : ($value ?? 0) }}"
                wire:change="updateField('{{ $fullKey }}', $event.target.value)"
                step="any"
                style="max-width: 200px;"
            >
            @break

        {{-- BOOLEAN --}}
        @case('boolean')
            <div class="form-checkbox-group">
                <label class="toggle">
                    <input
                        type="checkbox"
                        {{ $value ? 'checked' : '' }}
                        wire:click="toggleBoolean('{{ $fullKey }}')"
                    >
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-checkbox-label" style="cursor:pointer;" wire:click="toggleBoolean('{{ $fullKey }}')">
                    {{ $field->label }}
                    @if($field->required) <span class="required">*</span> @endif
                    @if($field->help_text)
                        <span title="{{ $field->help_text }}" style="cursor:help; opacity:0.5;">ℹ️</span>
                    @endif
                </label>
            </div>
            @break

        {{-- COLOR --}}
        @case('color')
            <div class="d-flex align-center gap-8">
                <input
                    type="color"
                    id="{{ $fieldId }}"
                    value="{{ is_array($value) ? '#000000' : ($value ?? '#000000') }}"
                    wire:change="updateField('{{ $fullKey }}', $event.target.value)"
                    style="width: 50px; height: 38px; padding: 2px; border: 1px solid var(--border-default); border-radius: var(--radius-sm); background: var(--bg-input); cursor: pointer;"
                >
                <input
                    type="text"
                    class="form-input font-mono"
                    value="{{ is_array($value) ? '#000000' : ($value ?? '#000000') }}"
                    wire:change="updateField('{{ $fullKey }}', $event.target.value)"
                    style="max-width: 120px;"
                    placeholder="#000000"
                >
            </div>
            @break

        {{-- IMAGE URL --}}
        @case('image_url')
            <input
                type="text"
                id="{{ $fieldId }}"
                class="form-input"
                value="{{ is_array($value) ? json_encode($value) : $value }}"
                wire:change="updateField('{{ $fullKey }}', $event.target.value)"
                placeholder="https://example.com/image.png"
            >
            @if($value && !is_array($value))
                <div class="mt-8" style="padding: 8px; background: var(--bg-tertiary); border-radius: var(--radius-sm); display: inline-block;">
                    <img src="{{ $value }}" alt="Preview" style="max-width: 100px; max-height: 100px; border-radius: 4px;">
                </div>
            @endif
            @break

        {{-- SIMPLE ARRAY (tags) --}}
        @case('array')
            <div>
                <div class="tag-list mb-8">
                    @if(is_array($value))
                        @foreach($value as $idx => $item)
                            <span class="tag">
                                {{ $item }}
                                <button class="tag-remove" wire:click="removeArrayItem('{{ $fullKey }}', {{ $idx }})">✕</button>
                            </span>
                        @endforeach
                    @endif
                </div>
                <div class="form-inline">
                    <div class="form-group">
                        <input
                            type="text"
                            id="{{ $fieldId }}_input"
                            class="form-input"
                            placeholder="Add item and press Enter"
                            wire:keydown.enter.prevent="addArrayItem('{{ $fullKey }}', $event.target.value); $event.target.value = '';"
                        >
                    </div>
                    <button class="btn btn-sm btn-secondary" onclick="
                        let input = document.getElementById('{{ $fieldId }}_input');
                        @this.call('addArrayItem', '{{ $fullKey }}', input.value);
                        input.value = '';
                    ">+ Add</button>
                </div>
            </div>
            @break

        {{-- RELATION --}}
        @case('relation')
            @if($field->relation_multiple)
                {{-- Multi-select relation --}}
                <div>
                    <div class="tag-list mb-8">
                        @if(is_array($value))
                            @foreach($value as $idx => $relVal)
                                @php
                                    $relLabel = $relVal;
                                    if (isset($relatedOptions[$field->key])) {
                                        $match = collect($relatedOptions[$field->key])->firstWhere('value', $relVal);
                                        if ($match) $relLabel = $match['label'];
                                    }
                                @endphp
                                <span class="tag">
                                    {{ $relLabel }}
                                    <button class="tag-remove" wire:click="removeRelation('{{ $fullKey }}', {{ $idx }})">✕</button>
                                </span>
                            @endforeach
                        @endif
                    </div>
                    <select
                        id="{{ $fieldId }}"
                        class="form-select"
                        wire:change="addRelation('{{ $fullKey }}', $event.target.value); $event.target.value = '';"
                    >
                        <option value="">— Add {{ $field->label }} —</option>
                        @if(isset($relatedOptions[$field->key]))
                            @foreach($relatedOptions[$field->key] as $opt)
                                @if(!is_array($value) || !in_array($opt['value'], $value))
                                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                </div>
            @else
                {{-- Single-select relation --}}
                <select
                    id="{{ $fieldId }}"
                    class="form-select"
                    wire:change="updateField('{{ $fullKey }}', $event.target.value)"
                >
                    <option value="">— Select {{ $field->label }} —</option>
                    @if(isset($relatedOptions[$field->key]))
                        @foreach($relatedOptions[$field->key] as $opt)
                            <option value="{{ $opt['value'] }}" {{ (!is_array($value) && $value == $opt['value']) ? 'selected' : '' }}>
                                {{ $opt['label'] }}
                            </option>
                        @endforeach
                    @endif
                </select>
            @endif
            @break

        {{-- DEFAULT FALLBACK --}}
        @default
            <input
                type="text"
                id="{{ $fieldId }}"
                class="form-input"
                value="{{ is_array($value) ? json_encode($value) : $value }}"
                wire:change="updateField('{{ $fullKey }}', $event.target.value)"
            >
    @endswitch
</div>

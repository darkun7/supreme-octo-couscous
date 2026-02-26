<?php

namespace App\Http\Livewire;

use App\Models\GameCollection;
use App\Models\GameCollectionField;
use App\Models\GameEntry;
use Livewire\Component;

class EntryEditor extends Component
{
    public $collection;
    public $entry;
    public $entryId;
    public $formData = [];
    public $fields = [];
    public $relatedOptions = [];
    public $isNew = false;
    public $showRawJson = false;
    public $rawJson = '';

    protected $listeners = ['refreshRelations' => 'loadRelatedOptions'];

    public function mount(GameCollection $collection, $entryId = null)
    {
        $this->collection = $collection;
        $this->entryId = $entryId;

        $this->fields = $collection->fields()->with('relationCollection')->get();

        if ($entryId && $entryId !== 'new') {
            $this->entry = GameEntry::findOrFail($entryId);
            $this->formData = $this->entry->data ?? [];
            $this->isNew = false;
        } else {
            $this->isNew = true;
            $this->formData = $this->buildDefaultData();
        }

        $this->rawJson = json_encode($this->formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $this->loadRelatedOptions();
    }

    /**
     * Build default data structure from field definitions.
     */
    protected function buildDefaultData()
    {
        $data = [];
        foreach ($this->fields as $field) {
            if ($field->parent_key) continue; // Skip nested fields here

            if ($field->type === 'object') {
                $data[$field->key] = $this->buildDefaultObjectData($field->key);
            } elseif ($field->type === 'array_of_objects') {
                $data[$field->key] = [];
            } elseif ($field->default_value !== null) {
                $data[$field->key] = $field->default_value;
            } else {
                $data[$field->key] = $this->getTypeDefault($field);
            }
        }
        return $data;
    }

    protected function buildDefaultObjectData($parentKey)
    {
        $obj = [];
        $childFields = $this->fields->where('parent_key', $parentKey);
        foreach ($childFields as $field) {
            if ($field->default_value !== null) {
                $obj[$field->key] = $field->default_value;
            } else {
                $obj[$field->key] = $this->getTypeDefault($field);
            }
        }
        return $obj;
    }

    protected function getTypeDefault($field)
    {
        if ($field->is_array || $field->relation_multiple) return [];

        switch ($field->type) {
            case 'number': return 0;
            case 'boolean': return false;
            case 'string': return '';
            case 'relation': return $field->relation_multiple ? [] : '';
            default: return '';
        }
    }

    /**
     * Load options for all relation fields.
     */
    public function loadRelatedOptions()
    {
        $this->relatedOptions = [];
        foreach ($this->fields as $field) {
            if ($field->type === 'relation' && $field->relation_collection_id) {
                $relCollection = GameCollection::find($field->relation_collection_id);
                if ($relCollection) {
                    $entries = GameEntry::where('game_collection_id', $relCollection->id)->get();
                    $displayField = $field->relation_display_field ?? 'Name';
                    $valueField = $field->relation_value_field ?? 'ID';

                    $this->relatedOptions[$field->key] = $entries->map(function ($e) use ($displayField, $valueField) {
                        return [
                            'value' => data_get($e->data, $valueField, $e->id),
                            'label' => data_get($e->data, $displayField, 'Unnamed') . ' (#' . data_get($e->data, $valueField, $e->id) . ')',
                        ];
                    })->toArray();
                }
            }
        }
    }

    /**
     * Update a field value via dot notation.
     */
    public function updateField($key, $value)
    {
        data_set($this->formData, $key, $this->castValue($key, $value));
        $this->rawJson = json_encode($this->formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Cast value to proper type based on field definition.
     */
    protected function castValue($key, $value)
    {
        // Find the field definition
        $parts = explode('.', $key);
        $fieldKey = end($parts);
        $parentKey = count($parts) > 1 ? $parts[count($parts) - 2] : null;

        // Check if parent is an array_of_objects (has numeric index)
        if ($parentKey !== null && is_numeric($parentKey) && count($parts) >= 3) {
            $parentKey = $parts[count($parts) - 3];
        }

        $field = $this->fields->first(function ($f) use ($fieldKey, $parentKey) {
            if ($parentKey && !is_numeric($parentKey)) {
                return $f->key === $fieldKey && $f->parent_key === $parentKey;
            }
            return $f->key === $fieldKey && !$f->parent_key;
        });

        if (!$field) return $value;

        switch ($field->type) {
            case 'number':
                return is_numeric($value) ? (strpos($value, '.') !== false ? (float) $value : (int) $value) : 0;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            default:
                return $value;
        }
    }

    /**
     * Toggle a boolean value.
     */
    public function toggleBoolean($key)
    {
        $current = data_get($this->formData, $key, false);
        data_set($this->formData, $key, !$current);
        $this->rawJson = json_encode($this->formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Add a relation value (for multi-select relations).
     */
    public function addRelation($fieldKey, $value)
    {
        $current = data_get($this->formData, $fieldKey, []);
        if (!is_array($current)) $current = [];

        // Cast the value to proper type
        $field = $this->fields->firstWhere('key', $fieldKey);
        if ($field) {
            $valueField = $field->relation_value_field ?? 'ID';
            // Try to determine if values should be numeric
            if (is_numeric($value)) {
                $value = (int) $value;
            }
        }

        if (!in_array($value, $current)) {
            $current[] = $value;
            data_set($this->formData, $fieldKey, $current);
            $this->rawJson = json_encode($this->formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Remove a relation value.
     */
    public function removeRelation($fieldKey, $index)
    {
        $current = data_get($this->formData, $fieldKey, []);
        if (is_array($current) && isset($current[$index])) {
            array_splice($current, $index, 1);
            data_set($this->formData, $fieldKey, array_values($current));
            $this->rawJson = json_encode($this->formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Add item to a simple array field (tags).
     */
    public function addArrayItem($fieldKey, $value)
    {
        $current = data_get($this->formData, $fieldKey, []);
        if (!is_array($current)) $current = [];
        $current[] = $value;
        data_set($this->formData, $fieldKey, $current);
        $this->rawJson = json_encode($this->formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Remove item from a simple array field.
     */
    public function removeArrayItem($fieldKey, $index)
    {
        $current = data_get($this->formData, $fieldKey, []);
        if (is_array($current) && isset($current[$index])) {
            array_splice($current, $index, 1);
            data_set($this->formData, $fieldKey, array_values($current));
            $this->rawJson = json_encode($this->formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Add a new object to an array_of_objects field.
     */
    public function addObjectItem($fieldKey)
    {
        $current = data_get($this->formData, $fieldKey, []);
        if (!is_array($current)) $current = [];

        $newObj = $this->buildDefaultObjectData($fieldKey);
        $current[] = $newObj;
        data_set($this->formData, $fieldKey, $current);
        $this->rawJson = json_encode($this->formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Remove an object from an array_of_objects field.
     */
    public function removeObjectItem($fieldKey, $index)
    {
        $current = data_get($this->formData, $fieldKey, []);
        if (is_array($current) && isset($current[$index])) {
            array_splice($current, $index, 1);
            data_set($this->formData, $fieldKey, array_values($current));
            $this->rawJson = json_encode($this->formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Toggle raw JSON editor.
     */
    public function toggleRawJson()
    {
        $this->showRawJson = !$this->showRawJson;
        if ($this->showRawJson) {
            $this->rawJson = json_encode($this->formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Apply raw JSON edits back to form data.
     */
    public function applyRawJson()
    {
        $decoded = json_decode($this->rawJson, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $this->formData = $decoded;
            $this->showRawJson = false;
            $this->emit('notify', 'JSON applied successfully!');
        } else {
            $this->emit('notify', 'Invalid JSON: ' . json_last_error_msg());
        }
    }

    /**
     * Save the entry.
     */
    public function save()
    {
        if ($this->isNew) {
            $entry = GameEntry::create([
                'game_collection_id' => $this->collection->id,
                'data' => $this->formData,
            ]);
            $this->entry = $entry;
            $this->entryId = $entry->id;
            $this->isNew = false;
            $this->emit('notify', 'Entry created successfully!');

            // Redirect to edit page
            return redirect()->route('entries.edit', [
                'collection' => $this->collection->slug,
                'entry' => $entry->id,
            ]);
        } else {
            $this->entry->data = $this->formData;
            $this->entry->save();
            $this->emit('notify', 'Entry saved successfully!');
        }
    }

    /**
     * Save and go back to list.
     */
    public function saveAndBack()
    {
        if ($this->isNew) {
            GameEntry::create([
                'game_collection_id' => $this->collection->id,
                'data' => $this->formData,
            ]);
        } else {
            $this->entry->data = $this->formData;
            $this->entry->save();
        }

        $this->emit('notify', 'Entry saved!');
        return redirect()->route('entries.index', $this->collection->slug);
    }

    /**
     * Highlight JSON for preview.
     */
    public function highlightJson($json)
    {
        // Escape HTML
        $json = htmlspecialchars($json);

        // Highlight JSON keys
        $json = preg_replace('/&quot;([^&]+?)&quot;\s*:/', '<span class="json-key">&quot;$1&quot;</span>:', $json);

        // Highlight string values
        $json = preg_replace('/:\s*&quot;([^&]*?)&quot;/', ': <span class="json-string">&quot;$1&quot;</span>', $json);

        // Highlight numbers
        $json = preg_replace('/:\s*(\d+\.?\d*)/', ': <span class="json-number">$1</span>', $json);

        // Highlight booleans
        $json = preg_replace('/:\s*(true|false)/', ': <span class="json-boolean">$1</span>', $json);

        // Highlight null
        $json = preg_replace('/:\s*(null)/', ': <span class="json-null">$1</span>', $json);

        return $json;
    }

    public function render()
    {
        return view('livewire.entry-editor');
    }
}

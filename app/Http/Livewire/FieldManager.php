<?php

namespace App\Http\Livewire;

use App\Models\GameCollection;
use App\Models\GameCollectionField;
use Livewire\Component;

class FieldManager extends Component
{
    public $collection;
    public $fields = [];
    public $allCollections = [];

    // Import state
    public $showImportForm = false;
    public $importJson = '';

    // Form state
    public $showForm = false;
    public $editingFieldId = null;
    public $fieldForm = [
        'key' => '',
        'label' => '',
        'type' => 'string',
        'input_type' => 'text',
        'options' => '',
        'default_value' => '',
        'required' => false,
        'is_array' => false,
        'parent_key' => '',
        'relation_collection_id' => '',
        'relation_display_field' => 'Name',
        'relation_value_field' => 'ID',
        'relation_multiple' => false,
        'help_text' => '',
    ];

    // Delete confirmation
    public $confirmingDelete = null;

    protected $rules = [
        'fieldForm.key' => 'required|string|max:255',
        'fieldForm.label' => 'required|string|max:255',
        'fieldForm.type' => 'required|in:string,number,boolean,array,object,relation,array_of_objects,color,image_url',
        'fieldForm.input_type' => 'required|string|max:50',
        'fieldForm.options' => 'nullable|string',
        'fieldForm.default_value' => 'nullable|string',
        'fieldForm.required' => 'boolean',
        'fieldForm.is_array' => 'boolean',
        'fieldForm.parent_key' => 'nullable|string',
        'fieldForm.relation_collection_id' => 'nullable|integer|exists:game_collections,id',
        'fieldForm.relation_display_field' => 'nullable|string',
        'fieldForm.relation_value_field' => 'nullable|string',
        'fieldForm.relation_multiple' => 'boolean',
        'fieldForm.help_text' => 'nullable|string',
    ];

    public function mount(GameCollection $collection)
    {
        $this->collection = $collection;
        $this->loadFields();
        $this->allCollections = GameCollection::where('game_id', $collection->game_id)->orderBy('display_name')->get()->toArray();
    }

    public function loadFields()
    {
        $this->fields = $this->collection->fields()->with('relationCollection')->get()->toArray();
    }

    public function create()
    {
        $this->resetFieldForm();
        $this->showForm = true;
    }

    public function createChildField($parentKey)
    {
        $this->resetFieldForm();
        $this->fieldForm['parent_key'] = $parentKey;
        $this->showForm = true;
    }

    public function edit($fieldId)
    {
        $field = GameCollectionField::findOrFail($fieldId);
        $this->editingFieldId = $fieldId;
        $this->fieldForm = [
            'key' => $field->key,
            'label' => $field->label,
            'type' => $field->type,
            'input_type' => $field->input_type,
            'options' => $field->options ? json_encode($field->options) : '',
            'default_value' => $field->default_value !== null ? (is_array($field->default_value) ? json_encode($field->default_value) : (string) $field->default_value) : '',
            'required' => $field->required,
            'is_array' => $field->is_array,
            'parent_key' => $field->parent_key ?? '',
            'relation_collection_id' => $field->relation_collection_id ?? '',
            'relation_display_field' => $field->relation_display_field ?? 'Name',
            'relation_value_field' => $field->relation_value_field ?? 'ID',
            'relation_multiple' => $field->relation_multiple,
            'help_text' => $field->help_text ?? '',
        ];
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'game_collection_id' => $this->collection->id,
            'key' => $this->fieldForm['key'],
            'label' => $this->fieldForm['label'],
            'type' => $this->fieldForm['type'],
            'input_type' => $this->fieldForm['input_type'],
            'required' => $this->fieldForm['required'],
            'is_array' => $this->fieldForm['is_array'],
            'parent_key' => $this->fieldForm['parent_key'] ?: null,
            'relation_multiple' => $this->fieldForm['relation_multiple'],
            'help_text' => $this->fieldForm['help_text'] ?: null,
        ];

        // Handle options
        if (!empty($this->fieldForm['options'])) {
            $decoded = json_decode($this->fieldForm['options'], true);
            $data['options'] = $decoded !== null ? $decoded : explode(',', $this->fieldForm['options']);
        } else {
            $data['options'] = null;
        }

        // Handle default value
        if ($this->fieldForm['default_value'] !== '') {
            $decoded = json_decode($this->fieldForm['default_value'], true);
            $data['default_value'] = $decoded !== null ? $decoded : $this->fieldForm['default_value'];
        } else {
            $data['default_value'] = null;
        }

        // Handle relation fields
        if ($this->fieldForm['type'] === 'relation') {
            $data['relation_collection_id'] = $this->fieldForm['relation_collection_id'] ?: null;
            $data['relation_display_field'] = $this->fieldForm['relation_display_field'];
            $data['relation_value_field'] = $this->fieldForm['relation_value_field'];
        } else {
            $data['relation_collection_id'] = null;
            $data['relation_display_field'] = null;
            $data['relation_value_field'] = null;
            $data['relation_multiple'] = false;
        }

        // Auto-set input_type based on type
        $data['input_type'] = $this->resolveInputType($data['type'], $data['input_type']);

        if ($this->editingFieldId) {
            $field = GameCollectionField::findOrFail($this->editingFieldId);
            $field->update($data);
            $this->emit('notify', 'Field updated successfully!');
        } else {
            $data['sort_order'] = GameCollectionField::where('game_collection_id', $this->collection->id)->max('sort_order') + 1;
            GameCollectionField::create($data);
            $this->emit('notify', 'Field created successfully!');
        }

        $this->resetFieldForm();
        $this->loadFields();
    }

    public function confirmDelete($fieldId)
    {
        $this->confirmingDelete = $fieldId;
    }

    public function delete($fieldId)
    {
        GameCollectionField::findOrFail($fieldId)->delete();
        $this->confirmingDelete = null;
        $this->loadFields();
        $this->emit('notify', 'Field deleted!');
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = null;
    }

    public function moveUp($fieldId)
    {
        $field = GameCollectionField::findOrFail($fieldId);
        $prev = GameCollectionField::where('game_collection_id', $this->collection->id)
            ->where('sort_order', '<', $field->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if ($prev) {
            $tmpOrder = $field->sort_order;
            $field->sort_order = $prev->sort_order;
            $prev->sort_order = $tmpOrder;
            $field->save();
            $prev->save();
            $this->loadFields();
        }
    }

    public function moveDown($fieldId)
    {
        $field = GameCollectionField::findOrFail($fieldId);
        $next = GameCollectionField::where('game_collection_id', $this->collection->id)
            ->where('sort_order', '>', $field->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($next) {
            $tmpOrder = $field->sort_order;
            $field->sort_order = $next->sort_order;
            $next->sort_order = $tmpOrder;
            $field->save();
            $next->save();
            $this->loadFields();
        }
    }

    public function updateOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            GameCollectionField::where('game_collection_id', $this->collection->id)
                ->where('id', $id)
                ->update(['sort_order' => $index + 1]);
        }
        $this->loadFields();
        $this->emit('notify', 'Fields reordered using drag and drop!');
    }

    public function resetFieldForm()
    {
        $this->showForm = false;
        $this->editingFieldId = null;
        $this->fieldForm = [
            'key' => '',
            'label' => '',
            'type' => 'string',
            'input_type' => 'text',
            'options' => '',
            'default_value' => '',
            'required' => false,
            'is_array' => false,
            'parent_key' => '',
            'relation_collection_id' => '',
            'relation_display_field' => 'Name',
            'relation_value_field' => 'ID',
            'relation_multiple' => false,
            'help_text' => '',
        ];
    }

    public function updatedFieldFormType($value)
    {
        $this->fieldForm['input_type'] = $this->resolveInputType($value, 'text');
    }

    protected function resolveInputType($type, $current)
    {
        $defaults = [
            'string' => 'text',
            'number' => 'number',
            'boolean' => 'checkbox',
            'array' => 'tags',
            'object' => 'object',
            'relation' => 'relation',
            'array_of_objects' => 'array_of_objects',
            'color' => 'color',
            'image_url' => 'text',
        ];

        return $defaults[$type] ?? $current;
    }

    /**
     * Get unique parent keys for organizing nested fields.
     */
    public function getParentKeysProperty()
    {
        return collect($this->fields)
            ->where('type', 'object')
            ->pluck('key')
            ->unique()
            ->values()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.field-manager');
    }

    public function openImportForm()
    {
        $this->importJson = '';
        $this->showImportForm = true;
    }

    public function closeImportForm()
    {
        $this->importJson = '';
        $this->showImportForm = false;
        $this->resetErrorBag();
    }

    public function processImport()
    {
        $this->validate([
            'importJson' => 'required|string'
        ]);

        $data = json_decode($this->importJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->addError('importJson', 'Invalid JSON payload. Please ensure it is valid JSON.');
            return;
        }
        // Check if it's an array of objects
        if (!is_array($data)) {
            $this->addError('importJson', 'Please provide a valid JSON object.');
            return;
        }
        // Check if it's a list (array with numeric keys)
        $isList = array_keys($data) === range(0, count($data) - 1);
        if ($isList) {

            if (empty($data)) {
                $this->addError('importJson', 'The provided JSON array is empty.');
                return;
            }
            // If first item is an object → valid array of objects
            if (is_array($data[0])) {
                $data = $data[0];
            } else {
                $this->addError('importJson', 'Please provide a JSON object or an array of objects, not a simple array of values.');
                return;
            }
        }

        $this->generateFieldsFromJson($data);
        $this->loadFields();
        $this->closeImportForm();
        $this->emit('notify', 'Fields imported successfully!');
    }

    protected function generateFieldsFromJson($data, $parentKey = '')
    {
        $maxSortOrder = GameCollectionField::where('game_collection_id', $this->collection->id)->max('sort_order') ?? 0;

        foreach ($data as $key => $value) {
            $maxSortOrder++;
            
            // Skip if field already exists
            $exists = collect($this->fields)->where('key', $key)->where('parent_key', $parentKey === '' ? null : $parentKey)->first();
            if ($exists) continue;

            $type = 'string';
            $is_array = false;
            $nestedData = null;

            if (is_int($value) || is_float($value)) {
                $type = 'number';
            } elseif (is_bool($value)) {
                $type = 'boolean';
            } elseif (is_array($value)) {
                if (empty($value)) {
                    $type = 'string'; // Default for empty array
                    $is_array = true;
                } elseif (array_keys($value) === range(0, count($value) - 1)) {
                    // Sequential array
                    $firstItem = $value[0];
                    if (is_array($firstItem)) {
                        $type = 'array_of_objects';
                        $nestedData = $firstItem;
                    } else {
                        // Array of scalars
                        if (is_int($firstItem) || is_float($firstItem)) $type = 'number';
                        elseif (is_bool($firstItem)) $type = 'boolean';
                        else $type = 'string';
                        
                        $is_array = true;
                    }
                } else {
                    // Associative array (Object)
                    $type = 'object';
                    $nestedData = $value;
                }
            } elseif (is_string($value)) {
                if (preg_match('/^#[a-fA-F0-9]{3,6}$/i', $value)) {
                    $type = 'color';
                } elseif (preg_match('/\.(jpg|png|gif|webp|jpeg)$/i', $value)) {
                    $type = 'image_url';
                }
            }

            // Create the field
            $field = GameCollectionField::create([
                'game_collection_id' => $this->collection->id,
                'key' => $key,
                'label' => $this->makeLabel($key),
                'type' => $type,
                'input_type' => $this->resolveInputType($type, 'text'),
                'required' => false,
                'is_array' => $is_array,
                'parent_key' => $parentKey === '' ? null : $parentKey,
                'relation_multiple' => false,
                'sort_order' => $maxSortOrder
            ]);
            
            // Add to local fields cache to prevent duplicates in current pass if keys repeat
            $this->fields[] = $field->toArray();

            if ($nestedData) {
                $this->generateFieldsFromJson($nestedData, $key);
            }
        }
    }

    protected function makeLabel($key)
    {
        $label = str_replace(['_', '-'], ' ', $key);
        // Add space before camel case
        $label = preg_replace('/(?<!^)[A-Z]/', ' $0', $label);
        return ucwords(trim($label));
    }
}

<?php

namespace App\Http\Livewire;

use App\Models\GameCollection;
use App\Models\GameCollectionField;
use App\Models\GameEntry;
use Livewire\Component;

class SpreadsheetEditor extends Component
{
    public $collection;
    public $columns = [];        // All available columns (flattened dot-notation keys)
    public $visibleColumns = []; // Which columns are currently shown
    public $rows = [];           // Entry data indexed by entry ID
    public $dirty = [];          // Track which cells have been modified
    public $search = '';
    public $showColumnPicker = false;
    public $savedCount = 0;
    public $hasChanges = false;
    public $slug;
    public $isS3Configured = false;
    public $saveToS3 = false;

    public function mount($slug)
    {
        $this->slug = $slug;
        
        $activeGameId = session('active_game_id');
        if (!$activeGameId) {
            abort(403, 'No active game selected');
        }

        $this->collection = GameCollection::where('game_id', $activeGameId)
            ->where('slug', $slug)
            ->firstOrFail();

        $this->buildColumns();
        $this->loadRows();

        $this->isS3Configured = GameCollection::isS3Configured();
        if ($this->isS3Configured) {
            $this->saveToS3 = true;
        }
    }

    /**
     * Build flat column list from collection fields, flattening nested objects.
     */
    protected function buildColumns()
    {
        $fields = $this->collection->fields()->orderBy('sort_order')->get();
        $this->columns = [];

        foreach ($fields as $field) {
            if ($field->type === 'object') {
                // Get child fields for this object
                $children = $fields->where('parent_key', $field->key);
                foreach ($children as $child) {
                    if (in_array($child->type, ['object', 'array_of_objects'])) continue;
                    $this->columns[] = [
                        'key' => $field->key . '.' . $child->key,
                        'label' => $field->label . ' → ' . $child->label,
                        'type' => $child->type,
                        'input_type' => $child->input_type,
                        'options' => $child->options,
                        'parent' => $field->key,
                    ];
                }
            } elseif ($field->parent_key) {
                continue; // Already handled via parent
            } elseif (in_array($field->type, ['array_of_objects'])) {
                continue; // Skip complex arrays in spreadsheet mode
            } elseif ($field->type === 'relation' && $field->relation_multiple) {
                continue; // Skip multi-relation (too complex for cells)
            } else {
                $this->columns[] = [
                    'key' => $field->key,
                    'label' => $field->label,
                    'type' => $field->type,
                    'input_type' => $field->input_type,
                    'options' => $field->options,
                    'parent' => null,
                ];
            }
        }

        // Default: show first 10 columns
        $this->visibleColumns = array_slice(array_column($this->columns, 'key'), 0, 10);
    }

    /**
     * Load all entries as rows.
     */
    public function loadRows()
    {
        $query = $this->collection->entries()->orderBy('sort_order');

        $entries = $query->get();
        $this->rows = [];

        foreach ($entries as $entry) {
            $row = ['_id' => $entry->id];
            foreach ($this->columns as $col) {
                $row[$col['key']] = data_get($entry->data, $col['key']);
            }
            $this->rows[$entry->id] = $row;
        }

        $this->dirty = [];
        $this->hasChanges = false;
    }

    /**
     * Called when a cell value changes.
     */
    public function updateCell($entryId, $colKey, $value)
    {
        // Type cast
        $col = collect($this->columns)->firstWhere('key', $colKey);
        if ($col) {
            if ($col['type'] === 'number') {
                $value = is_numeric($value) ? (strpos($value, '.') !== false ? (float) $value : (int) $value) : 0;
            } elseif ($col['type'] === 'boolean') {
                $value = (bool) $value;
            }
        }

        $this->rows[$entryId][$colKey] = $value;
        $this->dirty[$entryId . '.' . $colKey] = true;
        $this->hasChanges = true;
    }

    /**
     * Save all modified entries.
     */
    public function saveAll()
    {
        $entriesToSave = [];

        // Group dirty cells by entry ID
        foreach ($this->dirty as $dirtyKey => $v) {
            $parts = explode('.', $dirtyKey, 2);
            $entryId = $parts[0];
            $entriesToSave[$entryId] = true;
        }

        $saved = 0;
        foreach ($entriesToSave as $entryId => $v) {
            $entry = GameEntry::find($entryId);
            if (!$entry) continue;

            $data = $entry->data;

            foreach ($this->columns as $col) {
                $key = $col['key'];
                if (isset($this->dirty[$entryId . '.' . $key])) {
                    data_set($data, $key, $this->rows[$entryId][$key]);
                }
            }

            $entry->data = $data;
            $entry->save();
            $saved++;
        }

        $this->dirty = [];
        $this->hasChanges = false;
        $this->savedCount = $saved;

        $msg = "Saved {$saved} entries!";
        if ($this->isS3Configured && $this->saveToS3) {
            try {
                $path = $this->collection->uploadToS3();
                $msg .= " & uploaded to S3!";
            } catch (\Exception $e) {
                $msg .= " (S3 upload failed: " . $e->getMessage() . ")";
            }
        }

        session()->flash('spreadsheet-saved', $msg);
    }

    /**
     * Toggle column visibility.
     */
    public function toggleColumn($colKey)
    {
        if (in_array($colKey, $this->visibleColumns)) {
            $this->visibleColumns = array_values(array_filter($this->visibleColumns, fn($c) => $c !== $colKey));
        } else {
            $this->visibleColumns[] = $colKey;
        }
    }

    /**
     * Show/hide all columns.
     */
    public function selectAllColumns()
    {
        $this->visibleColumns = array_column($this->columns, 'key');
    }

    public function deselectAllColumns()
    {
        // Keep at least the first column
        $this->visibleColumns = [($this->columns[0]['key'] ?? '')];
    }

    /**
     * Get filtered rows based on search.
     */
    public function getFilteredRowsProperty()
    {
        if (empty($this->search)) {
            return $this->rows;
        }

        $search = strtolower($this->search);
        return array_filter($this->rows, function ($row) use ($search) {
            foreach ($row as $key => $value) {
                if ($key === '_id') continue;
                if (is_string($value) && str_contains(strtolower($value), $search)) return true;
                if (is_numeric($value) && str_contains((string) $value, $search)) return true;
            }
            return false;
        });
    }

    /**
     * Check if any cell in a row has been modified.
     */
    public function isRowDirty($entryId)
    {
        foreach ($this->dirty as $key => $v) {
            if (strpos($key, $entryId . '.') === 0) return true;
        }
        return false;
    }

    public function render()
    {
        return view('livewire.spreadsheet-editor', [
            'filteredRows' => $this->filteredRows,
        ])->layout('layouts.app');
    }
}

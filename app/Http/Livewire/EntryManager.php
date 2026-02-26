<?php

namespace App\Http\Livewire;

use App\Models\GameCollection;
use App\Models\GameEntry;
use Livewire\Component;
use Livewire\WithPagination;

class EntryManager extends Component
{
    use WithPagination;

    public $collection;
    public $search = '';
    public $sortField = null;
    public $sortDirection = 'asc';
    public $confirmingDelete = null;

    protected $queryString = ['search', 'sortField', 'sortDirection'];

    public function mount(GameCollection $collection)
    {
        $this->collection = $collection;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sort($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function confirmDelete($entryId)
    {
        $this->confirmingDelete = $entryId;
    }

    public function delete($entryId)
    {
        GameEntry::findOrFail($entryId)->delete();
        $this->confirmingDelete = null;
        $this->emit('notify', 'Entry deleted!');
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = null;
    }

    public function duplicateEntry($entryId)
    {
        $entry = GameEntry::findOrFail($entryId);
        $newData = $entry->data;

        // Increment the ID field if it exists
        $idField = $this->collection->id_field;
        if (isset($newData[$idField]) && is_numeric($newData[$idField])) {
            $maxId = GameEntry::where('game_collection_id', $this->collection->id)
                ->get()
                ->max(function ($e) use ($idField) {
                    return $e->data[$idField] ?? 0;
                });
            $newData[$idField] = $maxId + 1;
        }

        // Mark name as copy
        $nameField = $this->collection->name_field;
        if (isset($newData[$nameField])) {
            $newData[$nameField] = $newData[$nameField] . ' (Copy)';
        }

        GameEntry::create([
            'game_collection_id' => $this->collection->id,
            'data' => $newData,
        ]);

        $this->emit('notify', 'Entry duplicated!');
    }

    public function getEntriesProperty()
    {
        $query = GameEntry::where('game_collection_id', $this->collection->id);

        // Search in JSON data
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('data', 'like', '%' . $this->search . '%');
            });
        }

        // Sort by JSON field
        if ($this->sortField) {
            $query->orderByRaw("JSON_EXTRACT(data, '$.{$this->sortField}') {$this->sortDirection}");
        } else {
            $query->orderBy('id', 'asc');
        }

        return $query->paginate(20);
    }

    public function render()
    {
        return view('livewire.entry-manager', [
            'entries' => $this->entries,
        ]);
    }
}

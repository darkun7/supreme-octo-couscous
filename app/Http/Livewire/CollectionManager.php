<?php

namespace App\Http\Livewire;

use App\Models\GameCollection;
use Livewire\Component;
use Illuminate\Support\Str;

class CollectionManager extends Component
{
    public $collections;

    // Form state
    public $showForm = false;
    public $editingId = null;
    public $name = '';
    public $display_name = '';
    public $icon = '📦';
    public $description = '';
    public $id_field = 'ID';
    public $name_field = 'Name';
    public $type = 'tabular';

    // Delete confirmation
    public $confirmingDelete = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'display_name' => 'required|string|max:255',
        'icon' => 'required|string|max:10',
        'description' => 'nullable|string',
        'id_field' => 'required|string|max:255',
        'name_field' => 'required|string|max:255',
        'type' => 'required|string|in:tabular,static',
    ];

    public function mount()
    {
        $this->loadCollections();
    }

    public function getActiveGameId()
    {
        $user = auth()->user();
        if (!$user) return null;
        $activeId = session('active_game_id');
        $games = $user->role === 'super_admin' ? \App\Models\Game::all() : $user->games;
        return $activeId ?? optional($games->first())->id;
    }

    public function loadCollections()
    {
        $gameId = $this->getActiveGameId();
        if (!$gameId) {
            $this->collections = collect();
            return;
        }

        $this->collections = GameCollection::where('game_id', $gameId)
            ->withCount(['entries', 'fields'])
            ->orderBy('sort_order')
            ->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id)
    {
        $collection = GameCollection::findOrFail($id);
        $this->editingId = $id;
        $this->name = $collection->name;
        $this->display_name = $collection->display_name;
        $this->icon = $collection->icon;
        $this->description = $collection->description;
        $this->id_field = $collection->id_field;
        $this->name_field = $collection->name_field;
        $this->type = $collection->type;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'display_name' => $this->display_name,
            'icon' => $this->icon,
            'description' => $this->description,
            'id_field' => $this->id_field,
            'name_field' => $this->name_field,
            'type' => $this->type,
        ];

        if ($this->editingId) {
            $collection = GameCollection::findOrFail($this->editingId);
            $collection->update($data);
            $this->emit('notify', 'Collection updated successfully!');
        } else {
            $gameId = $this->getActiveGameId();
            if (!$gameId) {
                $this->emit('notify', 'Error: No active game selected.');
                return;
            }
            $data['game_id'] = $gameId;
            $data['sort_order'] = GameCollection::where('game_id', $gameId)->max('sort_order') + 1;
            GameCollection::create($data);
            $this->emit('notify', 'Collection created successfully!');
        }

        $this->resetForm();
        $this->loadCollections();
    }

    public function confirmDelete($id)
    {
        $this->confirmingDelete = $id;
    }

    public function delete($id)
    {
        GameCollection::findOrFail($id)->delete();
        $this->confirmingDelete = null;
        $this->loadCollections();
        $this->emit('notify', 'Collection deleted successfully!');
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = null;
    }

    public function resetForm()
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->name = '';
        $this->display_name = '';
        $this->icon = '📦';
        $this->description = '';
        $this->id_field = 'ID';
        $this->name_field = 'Name';
        $this->type = 'tabular';
    }

    public function updatedName($value)
    {
        if (!$this->editingId) {
            $this->display_name = Str::title(str_replace(['_', '-'], ' ', $value));
        }
    }

    public function reorderCollections($orderedIds)
    {
        $gameId = $this->getActiveGameId();
        if (!$gameId) return;

        foreach ($orderedIds as $index => $id) {
            GameCollection::where('id', $id)
                ->where('game_id', $gameId)
                ->update(['sort_order' => $index]);
        }

        $this->loadCollections();
        $this->emit('notify', 'Collection order updated!');

        return redirect()->route('collections.index');
    }

    public function render()
    {
        return view('livewire.collection-manager');
    }
}

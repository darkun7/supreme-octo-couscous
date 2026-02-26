<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\GameCollection;
use App\Models\GameEntry;

class Dashboard extends Component
{
    public $collections;
    public $totalEntries;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $activeGameId = session('active_game_id');
        if ($activeGameId) {
            $this->collections = GameCollection::where('game_id', $activeGameId)
                ->withCount(['entries', 'fields'])
                ->orderBy('sort_order')
                ->get();
            $this->totalEntries = GameEntry::whereIn('game_collection_id', $this->collections->pluck('id'))->count();
        } else {
            $this->collections = collect();
            $this->totalEntries = 0;
        }
    }

    public function reorderCollections($orderedIds)
    {
        $activeGameId = session('active_game_id');
        if (!$activeGameId) return;

        foreach ($orderedIds as $index => $id) {
            GameCollection::where('id', $id)
                ->where('game_id', $activeGameId)
                ->update(['sort_order' => $index]);
        }
        
        $this->loadData();
        $this->emit('notify', 'Collection order updated!');
        
        // We reload the page to make sure that the global sidebar is updated as requested
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}

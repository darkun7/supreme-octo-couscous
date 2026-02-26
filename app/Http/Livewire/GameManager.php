<?php

namespace App\Http\Livewire;

use App\Models\Game;
use Livewire\Component;
use Illuminate\Support\Str;

class GameManager extends Component
{
    public $games;
    
    public $isFormOpen = false;
    public $formId = null;
    public $name = '';
    public $slug = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:games,slug',
    ];

    public function mount()
    {
        $this->loadGames();
    }

    public function loadGames()
    {
        $user = auth()->user();
        if ($user->role === 'super_admin') {
            $this->games = Game::withCount('collections')->get();
        } else {
            $this->games = $user->games()->withCount('collections')->get();
        }
    }

    public function openForm($id = null)
    {
        $this->resetValidation();
        $this->formId = $id;

        if ($id) {
            $game = Game::findOrFail($id);
            $this->name = $game->name;
            $this->slug = $game->slug;
        } else {
            $this->name = '';
            $this->slug = '';
        }

        $this->isFormOpen = true;
    }

    public function closeForm()
    {
        $this->isFormOpen = false;
        $this->formId = null;
    }

    public function updatedName($value)
    {
        if (!$this->formId) {
            $this->slug = Str::slug($value);
        }
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->formId) {
            $rules['slug'] = 'required|string|max:255|unique:games,slug,' . $this->formId;
        }

        $this->validate($rules);

        if ($this->formId) {
            $game = Game::findOrFail($this->formId);
            $game->update([
                'name' => $this->name,
                'slug' => $this->slug,
            ]);
            $this->emit('notify', 'Game updated successfully.');
        } else {
            $game = Game::create([
                'name' => $this->name,
                'slug' => $this->slug,
            ]);

            // If game manager creates a game, assign them to it
            $user = auth()->user();
            if ($user->role === 'game_manager') {
                $user->games()->attach($game->id);
            }

            // Immediately switch to the new game if no game active
            if (!session('active_game_id')) {
                session(['active_game_id' => $game->id]);
            }
            
            $this->emit('notify', 'Game created successfully.');
        }

        $this->closeForm();
        $this->loadGames();
        
        // Refresh page to update sidebar game list if needed
        return redirect()->route('settings.games');
    }

    public function delete($id)
    {
        $game = Game::findOrFail($id);
        
        if (session('active_game_id') == $game->id) {
            session()->forget('active_game_id');
        }

        $game->delete();
        $this->loadGames();
        $this->emit('notify', 'Game deleted permanently.');
        
        return redirect()->route('settings.games');
    }

    public function render()
    {
        return view('livewire.game-manager');
    }
}

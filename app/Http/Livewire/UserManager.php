<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Game;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserManager extends Component
{
    public $users;
    public $games;
    
    public $isFormOpen = false;
    public $formId = null;
    
    // User fields
    public $name = '';
    public $username = '';
    public $email = '';
    public $password = '';
    public $role = 'game_manager';
    public $selectedGames = [];

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username' . ($this->formId ? ',' . $this->formId : ''),
            'email' => 'required|email|max:255|unique:users,email' . ($this->formId ? ',' . $this->formId : ''),
            'role' => 'required|in:super_admin,game_manager',
            'selectedGames' => 'array',
        ];

        if (!$this->formId) {
            $rules['password'] = 'required|string|min:6';
        }

        return $rules;
    }

    public function mount()
    {
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Unauthorized.');
        }
        $this->loadData();
    }

    public function loadData()
    {
        $this->users = User::with('games')->get();
        $this->games = Game::all();
    }

    public function openForm($id = null)
    {
        $this->resetValidation();
        $this->formId = $id;

        if ($id) {
            $user = User::with('games')->findOrFail($id);
            $this->name = $user->name;
            $this->username = $user->username;
            $this->email = $user->email;
            $this->role = $user->role;
            $this->password = '';
            $this->selectedGames = $user->games->pluck('id')->toArray();
        } else {
            $this->name = '';
            $this->username = '';
            $this->email = '';
            $this->role = 'game_manager';
            $this->password = '';
            $this->selectedGames = [];
        }

        $this->isFormOpen = true;
    }

    public function closeForm()
    {
        $this->isFormOpen = false;
        $this->formId = null;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->formId) {
            $user = User::findOrFail($this->formId);
            $user->update($data);
            $this->emit('notify', 'User updated successfully.');
        } else {
            $user = User::create($data);
            $this->emit('notify', 'User created successfully.');
        }

        // Sync games
        if ($this->role === 'super_admin') {
            // Super admins inherently have access to all, but let's clear pivot to keep it clean
            $user->games()->sync([]);
        } else {
            $user->games()->sync($this->selectedGames);
        }

        $this->closeForm();
        $this->loadData();
    }

    public function delete($id)
    {
        if ($id == Auth::id()) {
            $this->emit('notify', 'You cannot delete yourself.');
            return;
        }

        $user = User::findOrFail($id);
        $user->delete();
        $this->loadData();
        $this->emit('notify', 'User deleted entirely.');
    }

    public function loginAs($id)
    {
        // Simple impersonation without return-to-admin logic for this iteration
        Auth::loginUsingId($id);
        
        // Reset active game so it defaults to the new user's scoped game
        session()->forget('active_game_id');
        
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.user-manager');
    }
}

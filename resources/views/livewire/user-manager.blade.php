<div>
    <div class="card mb-20">
        <div class="card-header">
            <h2 class="card-title">System Users</h2>
            <button wire:click="openForm" class="btn btn-primary">
                <span>➕</span> New User
            </button>
        </div>

        @if($users->count() > 0)
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Game Access</th>
                            <th style="width: 150px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 2px;">{{ $user->name }}</div>
                                    <div style="font-size: 11px; color: var(--text-tertiary);">{{ '@'.$user->username }} | {{ $user->email }}</div>
                                </td>
                                <td>
                                    <span class="field-item-type {{ $user->role === 'super_admin' ? 'field-type-array_of_objects' : 'field-type-string' }}">
                                        {{ str_replace('_', ' ', $user->role) }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->role === 'super_admin')
                                        <div class="tag-list">
                                            <span class="tag" style="background: var(--info-bg); border-color: transparent; color: var(--info);">All Games</span>
                                        </div>
                                    @else
                                        @if($user->games->count() > 0)
                                            <div class="tag-list">
                                                @foreach($user->games as $game)
                                                    <span class="tag">{{ $game->name }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span style="font-size: 12px; color: var(--text-tertiary); font-style: italic;">No games assigned</span>
                                        @endif
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div class="btn-group" style="justify-content: flex-end;">
                                        @if($user->id !== auth()->id())
                                            <button wire:click="loginAs({{ $user->id }})" class="btn btn-secondary btn-sm btn-icon" title="Login as {{ $user->name }}">
                                                🎭
                                            </button>
                                        @endif
                                        <button wire:click="openForm({{ $user->id }})" class="btn btn-secondary btn-sm btn-icon" title="Edit User">
                                            ✏️
                                        </button>
                                        @if($user->id !== auth()->id())
                                            <button onclick="if(confirm('Are you absolutely sure you want to delete this user?')) @this.delete({{ $user->id }})" class="btn btn-danger btn-sm btn-icon" title="Delete User">
                                                🗑️
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Form Modal --}}
    @if($isFormOpen)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div class="card" style="width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title">{{ $formId ? 'Edit User' : 'Create New User' }}</h2>
                    <button wire:click="closeForm" style="background: none; border: none; color: var(--text-tertiary); cursor: pointer; font-size: 20px;">×</button>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" wire:model.defer="name" class="form-input">
                        @error('name') <span class="form-help" style="color: var(--danger)">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Username <span class="required">*</span></label>
                        <input type="text" wire:model.defer="username" class="form-input">
                        @error('username') <span class="form-help" style="color: var(--danger)">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address <span class="required">*</span></label>
                    <input type="email" wire:model.defer="email" class="form-input">
                    @error('email') <span class="form-help" style="color: var(--danger)">{{ $message }}</span> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">System Role <span class="required">*</span></label>
                        <select wire:model="role" class="form-select">
                            <option value="game_manager">Game Manager</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                        <div class="form-help">Super Admins automatically have full access to ALL games.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password @if(!$formId) <span class="required">*</span> @endif</label>
                        <input type="password" wire:model.defer="password" class="form-input" placeholder="{{ $formId ? 'Leave blank to keep unchanged' : 'Minimum 6 chars' }}">
                        @error('password') <span class="form-help" style="color: var(--danger)">{{ $message }}</span> @enderror
                    </div>
                </div>

                @if($role === 'game_manager')
                <div style="margin-top: 10px; margin-bottom: 20px; padding: 16px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: var(--radius-sm);">
                    <label class="form-label">Game Access Assignment <span class="required">*</span></label>
                    <div class="form-help mb-12">Select which games this user is allowed to manage:</div>
                    
                    @if($games->count() > 0)
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            @foreach($games as $game)
                                <label class="form-checkbox-group" style="padding: 8px 12px; background: var(--bg-input); border-radius: var(--radius-sm); border: 1px solid var(--border-default);">
                                    <input type="checkbox" wire:model.defer="selectedGames" value="{{ $game->id }}" class="form-checkbox">
                                    <span class="form-checkbox-label" style="font-weight: 600;">{{ $game->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div style="color: var(--warning); font-size: 12px; font-weight: 600;">No existing games. Please create a game first before assigning bounds.</div>
                    @endif
                </div>
                @endif

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-default);">
                    <button wire:click="closeForm" class="btn btn-secondary">Cancel</button>
                    <button wire:click="save" class="btn btn-primary">Save User</button>
                </div>
            </div>
        </div>
    @endif
</div>

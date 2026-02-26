<div>
    <div class="card mb-20">
        <div class="card-header">
            <h2 class="card-title">Games</h2>
            <button wire:click="openForm" class="btn btn-primary">
                <span>➕</span> New Game
            </button>
        </div>

        @if($games->count() > 0)
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Collections</th>
                            <th style="width: 100px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($games as $game)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary);">{{ $game->name }}</div>
                                </td>
                                <td><span class="field-item-type field-type-string">{{ $game->slug }}</span></td>
                                <td>{{ $game->collections_count }}</td>
                                <td style="text-align: right;">
                                    <div class="btn-group" style="justify-content: flex-end;">
                                        <button wire:click="openForm({{ $game->id }})" class="btn btn-secondary btn-sm btn-icon" title="Edit Game">
                                            ✏️
                                        </button>
                                        <button onclick="if(confirm('Are you sure you want to delete this game? All collections and entries will be entirely wiped out.')) @this.delete({{ $game->id }})" class="btn btn-danger btn-sm btn-icon" title="Delete Game">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 40px; color: var(--text-tertiary);">
                <div style="font-size: 48px; margin-bottom: 16px;">🕹️</div>
                <div style="font-size: 16px; font-weight: 500;">No Games Found</div>
                <div style="font-size: 13px; margin-top: 8px;">You haven't created any games yet. Start by creating your first project!</div>
            </div>
        @endif
    </div>

    {{-- Form Modal --}}
    @if($isFormOpen)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div class="card" style="width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto;">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title">{{ $formId ? 'Edit Game' : 'Create New Game' }}</h2>
                    <button wire:click="closeForm" style="background: none; border: none; color: var(--text-tertiary); cursor: pointer; font-size: 20px;">×</button>
                </div>

                <div class="form-group">
                    <label class="form-label">Game Name <span class="required">*</span></label>
                    <input type="text" wire:model="name" class="form-input" placeholder="e.g. Pet Bot">
                    @error('name') <span class="form-help" style="color: var(--danger)">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Slug <span class="required">*</span></label>
                    <input type="text" wire:model="slug" class="form-input" placeholder="e.g. pet-bot">
                    <div class="form-help">URL-friendly identifier. Auto-generated from name if left empty initially.</div>
                    @error('slug') <span class="form-help" style="color: var(--danger)">{{ $message }}</span> @enderror
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-default);">
                    <button wire:click="closeForm" class="btn btn-secondary">Cancel</button>
                    <button wire:click="save" class="btn btn-primary">Save Game</button>
                </div>
            </div>
        </div>
    @endif
</div>

<div>
    {{-- Stats --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; margin-bottom: 32px;">
        <div class="card" style="text-align: center;">
            <div style="font-size: 36px; font-weight: 800; color: var(--accent-primary-hover);">{{ $collections->count() }}</div>
            <div class="text-tertiary text-sm" style="text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Collections</div>
        </div>
        <div class="card" style="text-align: center;">
            <div style="font-size: 36px; font-weight: 800; color: var(--success);">{{ $totalEntries }}</div>
            <div class="text-tertiary text-sm" style="text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Total Entries</div>
        </div>
        <div class="card" style="text-align: center;">
            <div style="font-size: 36px; font-weight: 800; color: var(--warning);">{{ $collections->sum('fields_count') }}</div>
            <div class="text-tertiary text-sm" style="text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Fields Defined</div>
        </div>
    </div>

    {{-- Quick Access --}}
    @if($collections->count())
        <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 16px;">Quick Access <span style="font-size: 12px; font-weight: 500; color: var(--text-tertiary); margin-left: 8px;">(Drag to reorder)</span></h2>
        <div class="collection-grid" id="dashboard-collection-grid" wire:ignore>
            @foreach($collections as $col)
                <div data-id="{{ $col->id }}" class="collection-card sortable-item" style="cursor: grab; display: block; text-decoration: none; position: relative;">
                    <a href="{{ route('entries.index', $col->slug) }}" style="text-decoration: none; color: inherit; display: block; pointer-events: auto;">
                        <div class="collection-card-icon" style="pointer-events: none;">{{ $col->icon }}</div>
                        <div class="collection-card-name" style="pointer-events: none;">{{ $col->display_name }}</div>
                        <div class="collection-card-desc" style="pointer-events: none;">{{ $col->description ?: 'No description' }}</div>
                        <div class="collection-card-stats" style="pointer-events: none;">
                            <div class="collection-card-stat">
                                <span class="collection-card-stat-value">{{ $col->entries_count }}</span>
                                <span class="collection-card-stat-label">Entries</span>
                            </div>
                            <div class="collection-card-stat">
                                <span class="collection-card-stat-value">{{ $col->fields_count }}</span>
                                <span class="collection-card-stat-label">Fields</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script>
            document.addEventListener('livewire:load', function () {
                var el = document.getElementById('dashboard-collection-grid');
                if (el) {
                    var sortable = Sortable.create(el, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        onEnd: function (evt) {
                            var order = [];
                            document.querySelectorAll('#dashboard-collection-grid .sortable-item').forEach(function(item) {
                                order.push(item.getAttribute('data-id'));
                            });
                            @this.reorderCollections(order);
                        },
                    });
                }
            });
        </script>
        <style>
            .sortable-ghost {
                opacity: 0.4;
                background-color: var(--bg-input);
                transform: scale(0.98);
                border: 2px dashed var(--accent-primary);
            }
            .sortable-item:active {
                cursor: grabbing;
            }
        </style>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">🎮</div>
            <div class="empty-state-text">Welcome to Game Manager!</div>
            <div class="empty-state-sub">Start by creating a collection to define your game data structure</div>
            <a href="{{ route('collections.index') }}" class="btn btn-primary">⚙️ Setup Collections</a>
        </div>
    @endif
</div>

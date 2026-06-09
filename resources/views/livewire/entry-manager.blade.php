<div>
    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="separator">/</span>
            <span>{{ $collection->display_name }}</span>
        </div>
        <div class="page-header-row">
            <h1 class="page-title">{{ $collection->icon }} {{ $collection->display_name }}</h1>
            <div class="btn-group">
                <a href="{{ route('entries.spreadsheet', $collection->slug) }}" class="btn btn-secondary">📊 Spreadsheet</a>
                <a href="{{ route('collections.fields', $collection->slug) }}" class="btn btn-secondary">🔧 Fields</a>
                <a href="{{ route('entries.create', $collection->slug) }}" class="btn btn-primary">
                    <span>+</span> New Entry
                </a>
            </div>
        </div>
    </div>

    <div class="page-content">
        {{-- Import/Export --}}
        @livewire('import-export', ['collection' => $collection], key('import-export-' . $collection->id))

        {{-- Search --}}
        <div class="d-flex align-center justify-between gap-16 mb-20 mt-16">
            <div class="search-box" style="flex: 1; max-width: 400px;">
                <span class="search-icon">🔍</span>
                <input type="text" wire:model.debounce.300ms="search" class="form-input" placeholder="Search entries...">
            </div>
            <div class="text-secondary text-sm">
                {{ $entries->total() }} {{ Str::plural('entry', $entries->total()) }}
            </div>
        </div>

        {{-- Table --}}
        @if($entries->count())
            @php
                $displayFields = $collection->fields()
                    ->whereNull('parent_key')
                    ->where('type', '!=', 'object')
                    ->where('type', '!=', 'array_of_objects')
                    ->orderBy('sort_order')
                    ->take(6)
                    ->get();
            @endphp

            <div class="card" style="padding:0; overflow:hidden;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            @foreach($displayFields as $field)
                                <th wire:click="sort('{{ $field->key }}')" style="cursor:pointer;">
                                    {{ $field->label }}
                                    @if($sortField === $field->key)
                                        <span class="sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                            @endforeach
                            <th style="width:180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $entry)
                            <tr>
                                <td style="color:var(--text-tertiary); font-size:12px;">{{ $entry->id }}</td>
                                @foreach($displayFields as $field)
                                    <td>
                                        @php
                                            $val = data_get($entry->data, $field->key);
                                        @endphp

                                        @if($field->type === 'boolean')
                                            @if($val)
                                                <span class="badge badge-success">✓ Yes</span>
                                            @else
                                                <span style="color:var(--text-tertiary)">✗ No</span>
                                            @endif
                                        @elseif($field->type === 'relation')
                                            @if(is_array($val))
                                                <div class="tag-list">
                                                    @foreach(array_slice($val, 0, 3) as $relVal)
                                                        <span class="tag">{{ Str::limit((string) $relVal, 30) }}</span>
                                                    @endforeach
                                                    @if(count($val) > 3)
                                                        <span class="tag" style="background:var(--bg-tertiary); color:var(--text-tertiary);">+{{ count($val) - 3 }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="tag">{{ Str::limit((string) ($val ?? '—'), 30) }}</span>
                                            @endif
                                        @elseif($field->type === 'array')
                                            @if(is_array($val))
                                                <span class="truncate" title="{{ implode(', ', array_map('strval', array_slice($val, 0, 20))) }}">{{ Str::limit(implode(', ', array_map('strval', $val)), 50) }}</span>
                                            @else
                                                <span class="text-tertiary">—</span>
                                            @endif
                                        @elseif($field->type === 'color')
                                            <div class="d-flex align-center gap-8">
                                                <div style="width:20px;height:20px;border-radius:4px;background:{{ $val ?? '#000' }};border:1px solid var(--border-default);"></div>
                                                <span class="font-mono text-sm">{{ $val }}</span>
                                            </div>
                                        @elseif($field->type === 'number')
                                            <span class="font-mono" style="color:var(--warning)">{{ $val ?? 0 }}</span>
                                        @else
                                            @if(is_array($val))
                                                <span class="truncate" title="{{ implode(', ', array_map('strval', array_slice($val, 0, 20))) }}">{{ Str::limit(implode(', ', array_map('strval', $val)), 50) }}</span>
                                            @else
                                                <span class="truncate">{{ Str::limit((string) ($val ?? '—'), 50) }}</span>
                                            @endif
                                        @endif
                                    </td>
                                @endforeach
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('entries.edit', [$collection->slug, $entry->id]) }}" class="btn btn-sm btn-secondary">✏️ Edit</a>
                                        <button wire:click="duplicateEntry({{ $entry->id }})" class="btn btn-sm btn-secondary btn-icon" title="Duplicate">📋</button>
                                        @if($confirmingDelete === $entry->id)
                                            <button wire:click="delete({{ $entry->id }})" class="btn btn-sm btn-danger">Delete</button>
                                            <button wire:click="cancelDelete" class="btn btn-sm btn-secondary">Cancel</button>
                                        @else
                                            <button wire:click="confirmDelete({{ $entry->id }})" class="btn btn-sm btn-danger btn-icon" title="Delete">🗑️</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $entries->links('vendor.livewire.custom-pagination') }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">{{ $collection->icon }}</div>
                <div class="empty-state-text">No entries yet</div>
                <div class="empty-state-sub">Create your first {{ $collection->display_name }} entry or import JSON data</div>
                <a href="{{ route('entries.create', $collection->slug) }}" class="btn btn-primary">
                    <span>+</span> Create Entry
                </a>
            </div>
        @endif
    </div>
</div>

<div>
    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="separator">/</span>
            <a href="{{ route('entries.index', $collection->slug) }}">{{ $collection->display_name }}</a>
            <span class="separator">/</span>
            <span>Spreadsheet</span>
        </div>
        <div class="page-header-row">
            <h1 class="page-title">📊 {{ $collection->display_name }} — Spreadsheet Edit</h1>
            <div class="btn-group">
                <a href="{{ route('entries.index', $collection->slug) }}" class="btn btn-secondary">← Back to List</a>
                <button wire:click="saveAll" class="btn btn-primary {{ !$hasChanges ? 'btn-disabled' : '' }}" {{ !$hasChanges ? 'disabled' : '' }}>
                    💾 Save All Changes
                    @if($hasChanges)
                        <span class="badge badge-warning" style="margin-left: 6px; font-size: 10px;">unsaved</span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <div class="page-content">
        {{-- Flash message --}}
        @if (session()->has('spreadsheet-saved'))
            <div class="notification notification-success" style="margin-bottom: 16px;">
                ✅ {{ session('spreadsheet-saved') }}
            </div>
        @endif

        {{-- Toolbar --}}
        <div class="card" style="padding: 12px 16px; margin-bottom: 16px;">
            <div class="d-flex align-center gap-16" style="flex-wrap: wrap;">
                {{-- Search --}}
                <div class="search-box" style="flex: 1; max-width: 300px;">
                    <span class="search-icon">🔍</span>
                    <input type="text" wire:model.debounce.300ms="search" class="form-input" placeholder="Search entries...">
                </div>

                {{-- Column picker toggle --}}
                <button wire:click="$toggle('showColumnPicker')" class="btn btn-secondary btn-sm">
                    🔧 Columns ({{ count($visibleColumns) }}/{{ count($columns) }})
                </button>

                {{-- Entry count --}}
                <span style="color: var(--text-tertiary); font-size: 13px;">
                    {{ count($filteredRows) }} {{ Str::plural('entry', count($filteredRows)) }}
                    @if($hasChanges)
                        · <span style="color: var(--warning);">{{ count(array_unique(array_map(fn($k) => explode('.', $k, 2)[0], array_keys($dirty)))) }} modified</span>
                    @endif
                </span>
            </div>

            {{-- Column picker panel --}}
            @if($showColumnPicker)
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-default);">
                    <div class="d-flex align-center gap-8" style="margin-bottom: 8px;">
                        <span style="font-size: 12px; color: var(--text-tertiary); font-weight: 600; text-transform: uppercase;">Visible Columns</span>
                        <button wire:click="selectAllColumns" class="btn btn-sm btn-secondary" style="font-size: 11px; padding: 2px 8px;">All</button>
                        <button wire:click="deselectAllColumns" class="btn btn-sm btn-secondary" style="font-size: 11px; padding: 2px 8px;">Minimal</button>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        @foreach($columns as $col)
                            <label style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: var(--radius-sm);
                                background: {{ in_array($col['key'], $visibleColumns) ? 'var(--accent-primary-muted)' : 'var(--bg-tertiary)' }};
                                border: 1px solid {{ in_array($col['key'], $visibleColumns) ? 'var(--accent-primary)' : 'var(--border-default)' }};
                                cursor: pointer; font-size: 12px; color: var(--text-secondary); user-select: none; transition: all 150ms;">
                                <input type="checkbox" wire:click="toggleColumn('{{ $col['key'] }}')" {{ in_array($col['key'], $visibleColumns) ? 'checked' : '' }}
                                    style="width: 12px; height: 12px; accent-color: var(--accent-primary);">
                                {{ $col['label'] }}
                                <span style="color: var(--text-tertiary); font-size: 10px;">({{ $col['type'] }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Spreadsheet Table --}}
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto; max-height: 75vh; overflow-y: auto;">
                <table class="data-table spreadsheet-table" style="min-width: max-content;">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th style="width: 50px; position: sticky; left: 0; z-index: 11; background: var(--bg-card);">#</th>
                            @foreach($columns as $col)
                                @if(in_array($col['key'], $visibleColumns))
                                    <th style="white-space: nowrap; font-size: 11px; padding: 8px 6px;">
                                        <div style="display: flex; flex-direction: column; gap: 2px;">
                                            <span>{{ $col['label'] }}</span>
                                            <span style="color: var(--text-tertiary); font-size: 9px; font-weight: 400;">{{ $col['type'] }}</span>
                                        </div>
                                    </th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filteredRows as $entryId => $row)
                            <tr style="{{ $this->isRowDirty($entryId) ? 'background: rgba(234, 179, 8, 0.05);' : '' }}">
                                <td style="color: var(--text-tertiary); font-size: 11px; position: sticky; left: 0; background: var(--bg-card); z-index: 1; border-right: 1px solid var(--border-default);">
                                    {{ $entryId }}
                                </td>
                                @foreach($columns as $col)
                                    @if(in_array($col['key'], $visibleColumns))
                                        @php
                                            $cellKey = $entryId . '.' . $col['key'];
                                            $isDirty = isset($dirty[$cellKey]);
                                            $val = $row[$col['key']] ?? null;
                                        @endphp
                                        <td style="padding: 2px 3px; {{ $isDirty ? 'background: rgba(234, 179, 8, 0.12); border: 1px solid var(--warning);' : '' }}">
                                            @if($col['type'] === 'boolean')
                                                <div style="display: flex; justify-content: center;">
                                                    <input type="checkbox"
                                                        {{ $val ? 'checked' : '' }}
                                                        wire:change="updateCell({{ $entryId }}, '{{ $col['key'] }}', $event.target.checked)"
                                                        style="width: 16px; height: 16px; accent-color: var(--accent-primary); cursor: pointer;">
                                                </div>
                                            @elseif(!empty($col['options']))
                                                <select
                                                    wire:change="updateCell({{ $entryId }}, '{{ $col['key'] }}', $event.target.value)"
                                                    class="spreadsheet-input spreadsheet-select"
                                                    style="min-width: 90px;">
                                                    <option value="">—</option>
                                                    @foreach($col['options'] as $opt)
                                                        <option value="{{ $opt }}" {{ $val === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($col['type'] === 'number')
                                                <input type="number"
                                                    value="{{ $val ?? 0 }}"
                                                    wire:change="updateCell({{ $entryId }}, '{{ $col['key'] }}', $event.target.value)"
                                                    class="spreadsheet-input spreadsheet-number"
                                                    step="any">
                                            @else
                                                <input type="text"
                                                    value="{{ $val ?? '' }}"
                                                    wire:change="updateCell({{ $entryId }}, '{{ $col['key'] }}', $event.target.value)"
                                                    class="spreadsheet-input"
                                                    style="min-width: {{ strlen($val ?? '') > 20 ? '200px' : '100px' }};">
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Floating save button (bottom-right corner) --}}
        @if($hasChanges)
            <button wire:click="saveAll"
                style="position: fixed; bottom: 24px; right: 24px; z-index: 100;
                    width: 60px; height: 60px; border-radius: 50%;
                    background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary, #7c3aed));
                    border: none; color: white; font-size: 24px; cursor: pointer;
                    box-shadow: 0 4px 20px rgba(139, 92, 246, 0.5), 0 0 40px rgba(139, 92, 246, 0.2);
                    display: flex; align-items: center; justify-content: center;
                    transition: all 200ms; animation: fab-pulse 2s ease-in-out infinite;"
                onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 28px rgba(139, 92, 246, 0.7)'"
                onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 20px rgba(139, 92, 246, 0.5)'"
                title="Save all changes">
                💾
            </button>
            {{-- Modified count badge --}}
            <span style="position: fixed; bottom: 68px; right: 16px; z-index: 101;
                background: var(--warning); color: #000; font-size: 11px; font-weight: 700;
                padding: 2px 8px; border-radius: 10px;
                box-shadow: 0 2px 8px rgba(234, 179, 8, 0.4);">
                {{ count(array_unique(array_map(fn($k) => explode('.', $k, 2)[0], array_keys($dirty)))) }} unsaved
            </span>
        @endif
    </div>

    <style>
        .spreadsheet-table th {
            background: var(--bg-card) !important;
            border-bottom: 2px solid var(--border-default) !important;
        }
        .spreadsheet-table td {
            border: 1px solid var(--border-subtle, rgba(255,255,255,0.04)) !important;
            vertical-align: middle !important;
        }
        .spreadsheet-table tr:hover td {
            background: rgba(139, 92, 246, 0.04) !important;
        }
        .spreadsheet-input {
            width: 100%;
            padding: 4px 6px;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 3px;
            color: var(--text-primary);
            font-size: 12px;
            font-family: var(--font-mono, 'JetBrains Mono', monospace);
            transition: all 120ms;
        }
        .spreadsheet-input:hover {
            background: var(--bg-tertiary);
            border-color: var(--border-default);
        }
        .spreadsheet-input:focus {
            background: var(--bg-tertiary);
            border-color: var(--accent-primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2);
        }
        .spreadsheet-number {
            text-align: right;
            min-width: 70px;
            color: var(--warning);
        }
        .spreadsheet-select {
            cursor: pointer;
            -webkit-appearance: none;
        }
        .spreadsheet-select:hover {
            background: var(--bg-tertiary);
        }

        /* Keyboard nav hint */
        .spreadsheet-table td:focus-within {
            box-shadow: inset 0 0 0 2px var(--accent-primary);
        }

        @keyframes fab-pulse {
            0%, 100% { box-shadow: 0 4px 20px rgba(139, 92, 246, 0.5); }
            50% { box-shadow: 0 4px 30px rgba(139, 92, 246, 0.8), 0 0 60px rgba(139, 92, 246, 0.3); }
        }
    </style>
</div>

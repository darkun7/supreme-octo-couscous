@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation">
        <ul style="display: flex; align-items: center; gap: 4px; list-style: none; padding: 0; margin: 0; flex-wrap: wrap; justify-content: center;">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li>
                    <span style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: var(--radius-sm); color: var(--text-muted); font-size: 13px; cursor: not-allowed; opacity: 0.5;">
                        ← Prev
                    </span>
                </li>
            @else
                <li>
                    <button wire:click="previousPage" wire:loading.attr="disabled" style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: var(--radius-sm); color: var(--text-secondary); font-size: 13px; cursor: pointer; font-family: var(--font-sans); transition: all 150ms;" onmouseover="this.style.background='var(--bg-card-hover)';this.style.color='var(--text-primary)'" onmouseout="this.style.background='var(--bg-tertiary)';this.style.color='var(--text-secondary)'">
                        ← Prev
                    </button>
                </li>
            @endif

            {{-- Pages --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    {{-- Dots --}}
                    <li>
                        <span style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 10px; color: var(--text-muted); font-size: 13px;">
                            …
                        </span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            {{-- Active --}}
                            <li>
                                <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; padding: 8px 10px; background: var(--accent-primary); border: 1px solid var(--accent-primary); border-radius: var(--radius-sm); color: white; font-size: 13px; font-weight: 600;">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            {{-- Inactive --}}
                            <li>
                                <button wire:click="gotoPage({{ $page }})" style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; padding: 8px 10px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: var(--radius-sm); color: var(--text-secondary); font-size: 13px; cursor: pointer; font-family: var(--font-sans); transition: all 150ms;" onmouseover="this.style.background='var(--bg-card-hover)';this.style.color='var(--text-primary)';this.style.borderColor='var(--border-hover)'" onmouseout="this.style.background='var(--bg-tertiary)';this.style.color='var(--text-secondary)';this.style.borderColor='var(--border-default)'">
                                    {{ $page }}
                                </button>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li>
                    <button wire:click="nextPage" wire:loading.attr="disabled" style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: var(--radius-sm); color: var(--text-secondary); font-size: 13px; cursor: pointer; font-family: var(--font-sans); transition: all 150ms;" onmouseover="this.style.background='var(--bg-card-hover)';this.style.color='var(--text-primary)'" onmouseout="this.style.background='var(--bg-tertiary)';this.style.color='var(--text-secondary)'">
                        Next →
                    </button>
                </li>
            @else
                <li>
                    <span style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: var(--radius-sm); color: var(--text-muted); font-size: 13px; cursor: not-allowed; opacity: 0.5;">
                        Next →
                    </span>
                </li>
            @endif
        </ul>
    </nav>

    {{-- Page info --}}
    <div style="text-align: center; margin-top: 12px; font-size: 12px; color: var(--text-tertiary);">
        Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }} results
    </div>
@endif

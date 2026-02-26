<x-layouts.app>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Manage Games</h1>
                <div class="page-breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="separator">/</span>
                    <span>Games</span>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">
        @livewire('game-manager')
    </div>
</x-layouts.app>

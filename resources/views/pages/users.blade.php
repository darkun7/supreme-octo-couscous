<x-layouts.app>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Manage Users</h1>
                <div class="page-breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="separator">/</span>
                    <span>Users</span>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">
        @livewire('user-manager')
    </div>
</x-layouts.app>

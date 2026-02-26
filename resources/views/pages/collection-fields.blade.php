<x-layouts.app :title="$collection->display_name . ' — Fields'">
    @livewire('field-manager', ['collection' => $collection])
</x-layouts.app>

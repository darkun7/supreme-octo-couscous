<x-layouts.app :title="($entryId === 'new' ? 'New ' : 'Edit ') . $collection->display_name">
    @livewire('entry-editor', ['collection' => $collection, 'entryId' => $entryId])
</x-layouts.app>

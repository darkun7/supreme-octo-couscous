<?php

namespace App\Http\Livewire;

use App\Models\GameCollection;
use App\Models\GameEntry;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class ImportExport extends Component
{
    public $collection;
    public $importJson = '';
    public $importMode = 'merge'; // merge or replace
    public $showImport = false;
    public $isS3Configured = false;

    public function mount(GameCollection $collection)
    {
        $this->collection = $collection;
        $this->isS3Configured = GameCollection::isS3Configured();
    }

    public function export()
    {
        $entries = GameEntry::where('game_collection_id', $this->collection->id)
            ->orderBy('sort_order')
            ->get()
            ->pluck('data');

        $idField = $this->collection->id_field;
        $exportData = [];

        foreach ($entries as $data) {
            $key = data_get($data, $idField) 
                ?? data_get($data, strtolower($idField)) 
                ?? data_get($data, strtoupper($idField))
                ?? data_get($data, 'id')
                ?? data_get($data, 'ID');
                
            if ($key !== null && $key !== '') {
                $exportData[$key] = $data;
            } else {
                $exportData[] = $data;
            }
        }

        // Force root to encode as object even if empty or using some numeric-looking keys
        $exportData = (object) $exportData;

        // Use JSON_FORCE_OBJECT if array is associative but PHP considers it sequential, though PHP handles this if keys are strings
        $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = $this->collection->slug . '.json';
        // $filename = $this->collection->slug . '_export_' . date('Y-m-d_His') . '.json';

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function uploadToS3()
    {
        try {
            $path = $this->collection->uploadToS3();
            $this->emit('notify', '✅ Successfully uploaded to S3: ' . $path);
        } catch (\Exception $e) {
            $this->emit('notify', '❌ Failed to upload to S3: ' . $e->getMessage(), 'error');
        }
    }

    public function toggleImport()
    {
        $this->showImport = !$this->showImport;
    }

    public function import()
    {
        $decoded = json_decode($this->importJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->emit('notify', 'Invalid JSON: ' . json_last_error_msg());
            return;
        }

        if (!is_array($decoded)) {
            $this->emit('notify', 'JSON must be an array or object.');
            return;
        }

        // Check if the root JSON is a single flat object or array of objects
        $firstItem = reset($decoded);
        if ($firstItem !== false && !is_array($firstItem)) {
            // Single flat object: wrap in an array so loop works
            $decoded = [$decoded];
        }

        if ($this->importMode === 'replace') {
            GameEntry::where('game_collection_id', $this->collection->id)->delete();
        }

        $idField = $this->collection->id_field;
        $imported = 0;
        $updated = 0;

        foreach ($decoded as $item) {
            if (!is_array($item)) continue;

            $itemKey = data_get($item, $idField) 
                ?? data_get($item, strtolower($idField)) 
                ?? data_get($item, strtoupper($idField))
                ?? data_get($item, 'id')
                ?? data_get($item, 'ID');

            if ($this->importMode === 'merge' && $itemKey !== null) {
                $existing = GameEntry::where('game_collection_id', $this->collection->id)
                    ->get()
                    ->first(function ($e) use ($idField, $itemKey) {
                        $eKey = data_get($e->data, $idField) 
                            ?? data_get($e->data, strtolower($idField)) 
                            ?? data_get($e->data, strtoupper($idField))
                            ?? data_get($e->data, 'id')
                            ?? data_get($e->data, 'ID');
                        return $eKey == $itemKey;
                    });

                if ($existing) {
                    $existing->data = $item;
                    $existing->save();
                    $updated++;
                    continue;
                }
            }

            GameEntry::create([
                'game_collection_id' => $this->collection->id,
                'data' => $item,
            ]);
            $imported++;
        }

        $this->importJson = '';
        $this->showImport = false;
        $this->emit('notify', "Imported {$imported} entries, updated {$updated} entries.");
        $this->emit('entriesUpdated');
    }

    public function render()
    {
        return view('livewire.import-export');
    }
}

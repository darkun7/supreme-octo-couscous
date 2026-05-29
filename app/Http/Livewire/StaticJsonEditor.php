<?php

namespace App\Http\Livewire;

use App\Models\GameCollection;
use App\Models\GameEntry;
use Livewire\Component;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class StaticJsonEditor extends Component
{
    public $collectionId;
    public $collectionName;
    public $collectionIcon;
    public $collectionDescription;
    public $collectionSlug;
    public $jsonContent = '';
    public $entryId;
    public $fileName;
    public $lastSaved;
    public $showImport = false;
    public $importJson = '';
    public $isS3Configured = false;

    public function mount($slug)
    {
        $user = auth()->user();
        $activeId = session('active_game_id');
        $games = $user->role === 'super_admin' ? \App\Models\Game::all() : $user->games;
        $gameId = $activeId ?? optional($games->first())->id;

        $collection = GameCollection::where('game_id', $gameId)
            ->where('slug', $slug)
            ->where('type', 'static')
            ->firstOrFail();

        $this->collectionId = $collection->id;
        $this->collectionName = $collection->display_name;
        $this->collectionIcon = $collection->icon;
        $this->collectionDescription = $collection->description;
        $this->collectionSlug = $collection->slug;

        // Find the JSON file name from seeder config
        $this->fileName = $collection->name . '.json';

        // Load the single entry
        $entry = GameEntry::where('game_collection_id', $collection->id)->first();
        if ($entry) {
            $this->entryId = $entry->id;
            $this->jsonContent = json_encode($entry->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            $this->jsonContent = '{}';
        }

        $this->lastSaved = $entry ? $entry->updated_at->diffForHumans() : null;
        $this->isS3Configured = GameCollection::isS3Configured();
    }

    public function saveJson()
    {
        $decoded = json_decode($this->jsonContent, true);
        if ($decoded === null && $this->jsonContent !== 'null') {
            $this->emit('notify', '❌ Invalid JSON! Please fix syntax errors before saving.', 'error');
            return;
        }

        // Update the database entry
        $entry = GameEntry::find($this->entryId);
        if ($entry) {
            $entry->update(['data' => $decoded]);
        } else {
            $entry = GameEntry::create([
                'game_collection_id' => $this->collectionId,
                'data' => $decoded,
                'sort_order' => 0,
            ]);
            $this->entryId = $entry->id;
        }

        // Also write back to the JSON file
        $filePath = storage_path('app/json/' . $this->fileName);
        File::put($filePath, json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->lastSaved = 'just now';
        $this->emit('notify', '✅ JSON saved successfully!');
    }

    public function export()
    {
        $json = $this->jsonContent;
        $filename = $this->collectionSlug . '.json';
        // $filename = $this->collectionSlug . '_export_' . date('Y-m-d_His') . '.json';

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function uploadToS3()
    {
        $decoded = json_decode($this->jsonContent, true);
        if ($decoded === null && $this->jsonContent !== 'null') {
            $this->emit('notify', '❌ Invalid JSON! Please fix syntax errors before uploading.', 'error');
            return;
        }

        $collection = GameCollection::find($this->collectionId);
        $gameName = \Illuminate\Support\Str::slug($collection->game->name ?? 'game');
        $filename = $this->collectionSlug . '.json';
        $path = '/static/game-manager/' . $gameName . '/json/' . $filename;

        // Ensure we upload the pretty encoded valid JSON
        $json = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        try {
            Storage::disk('s3')->put($path, $json, 'public');
            $this->emit('notify', '✅ Successfully uploaded to S3: ' . $path);
        } catch (\Exception $e) {
            $this->emit('notify', '❌ Failed to upload to S3: ' . $e->getMessage(), 'error');
        }
    }

    public function toggleImport()
    {
        $this->showImport = !$this->showImport;
        $this->importJson = '';
    }

    public function importJson()
    {
        $decoded = json_decode($this->importJson, true);
        if ($decoded === null && $this->importJson !== 'null') {
            $this->emit('notify', '❌ Invalid JSON! Check syntax before importing.', 'error');
            return;
        }

        // Replace the content
        $this->jsonContent = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Save to DB + file
        $this->saveJson();

        $this->showImport = false;
        $this->importJson = '';
        $this->emit('notify', '✅ JSON imported and saved successfully!');
        $this->emit('jsonUpdated');
    }

    public function render()
    {
        return view('livewire.static-json-editor');
    }
}


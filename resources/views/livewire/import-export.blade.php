<div>
    <div class="d-flex gap-8 align-center">
        <button wire:click="toggleImport" class="btn btn-sm btn-secondary">
            {{ $showImport ? '✕ Close' : '📥 Import JSON' }}
        </button>
        <button wire:click="export" class="btn btn-sm btn-secondary">📤 Export JSON</button>
        <button wire:click="uploadToS3" class="btn btn-sm btn-secondary">☁️ Upload S3</button>
    </div>

    @if($showImport)
        <div class="card mt-12">
            <div class="card-header">
                <div class="card-title">📥 Import JSON Data</div>
            </div>

            <div class="form-group">
                <label class="form-label">Import Mode</label>
                <select wire:model="importMode" class="form-select" style="max-width: 250px;">
                    <option value="merge">Merge (update existing, add new)</option>
                    <option value="replace">Replace All (delete existing)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">JSON Data</label>
                <textarea
                    wire:model.defer="importJson"
                    class="form-textarea"
                    style="min-height: 200px;"
                    placeholder='Paste your JSON array here, e.g. [{"ID": 1, "Name": "Sword"}, ...]'
                ></textarea>
            </div>

            <div class="btn-group">
                <button wire:click="import" class="btn btn-primary">Import</button>
                <button wire:click="toggleImport" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    @endif
</div>

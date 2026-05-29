<div>
    {{-- Header --}}
    <div class="page-header">

    <div class="page-breadcrumb">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span class="separator">/</span>
        <span>{{ $collectionName }}</span>
    </div>

    <div class="page-header-row">

        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="font-size: 28px;">{{ $collectionIcon }}</div>

            <div>
                <h1 class="page-title" style="margin: 0;">
                    {{ $collectionName }}
                </h1>
                <p class="text-tertiary text-sm" style="margin: 2px 0 0 0;">
                    {{ $collectionDescription }}
                </p>
            </div>

            <span class="badge badge-warning">
                Static JSON
            </span>
        </div>

        <div class="btn-group">
            @if($lastSaved)
                <span class="text-tertiary text-sm">
                    Saved: {{ $lastSaved }}
                </span>
            @endif

            <button wire:click="toggleImport"
                class="btn btn-secondary {{ $showImport ? 'btn-danger' : '' }}">
                {{ $showImport ? '✕ Close' : '📥 Import' }}
            </button>

            <button wire:click="export" class="btn btn-secondary">
                📤 Export
            </button>

            @if($isS3Configured)
                <button wire:click="uploadToS3" class="btn btn-secondary">
                    ☁️ Upload S3
                </button>
            @endif

            <button wire:click="saveJson" class="btn btn-primary" style="display: flex; align-items: center; gap: 6px;">
                💾 Save
            </button>
        </div>

    </div>

</div>

    {{-- Import Panel --}}
    @if($showImport)
        <div class="card" style="margin-bottom: 16px; border: 1px solid var(--warning); border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                <span style="font-size: 18px;">📥</span>
                <h3 style="font-size: 15px; font-weight: 700; margin: 0;">Import JSON</h3>
                <span class="text-tertiary text-sm">— Paste your JSON data below. This will <strong>replace</strong> the entire current content.</span>
            </div>
            <textarea
                wire:model.defer="importJson"
                spellcheck="false"
                placeholder="Paste your JSON here..."
                style="width: 100%; min-height: 180px; background: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-primary); border-radius: 6px; padding: 12px; font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace; font-size: 12px; line-height: 1.5; resize: vertical; tab-size: 4;"
            ></textarea>
            <div style="display: flex; gap: 8px; margin-top: 10px;">
                <button wire:click="importJson" class="btn btn-primary btn-sm">🔄 Import & Save</button>
                <button wire:click="toggleImport" class="btn btn-secondary btn-sm">Cancel</button>
            </div>
        </div>
    @endif

    {{-- Editor Tabs --}}
    <div style="display: flex; gap: 2px; margin-bottom: 0;">
        <button onclick="switchTab('tree')" id="tab-tree" class="editor-tab active" style="padding: 10px 20px; background: var(--bg-secondary); border: 1px solid var(--border-primary); border-bottom: none; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; font-size: 13px; color: var(--text-primary); transition: all 0.2s;">
            🌳 Tree View
        </button>
        <button onclick="switchTab('code')" id="tab-code" class="editor-tab" style="padding: 10px 20px; background: var(--bg-tertiary); border: 1px solid var(--border-primary); border-bottom: none; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 600; font-size: 13px; color: var(--text-tertiary); transition: all 0.2s;">
            📝 Code Editor
        </button>
    </div>

    {{-- Tree View Panel --}}
    <div id="panel-tree" class="card" style="border-radius: 0 8px 8px 8px; margin-top: 0; border-top: 2px solid var(--accent-primary); max-height: 75vh; overflow-y: auto;">
        <div id="json-tree" style="font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace; font-size: 13px; line-height: 1.6;"></div>
    </div>

    {{-- Code Editor Panel --}}
    <div id="panel-code" class="card" style="border-radius: 0 8px 8px 8px; margin-top: 0; border-top: 2px solid var(--accent-primary); display: none;">
        <textarea
            wire:model.lazy="jsonContent"
            id="json-code-editor"
            spellcheck="false"
            style="width: 100%; min-height: 70vh; background: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-primary); border-radius: 6px; padding: 16px; font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace; font-size: 13px; line-height: 1.6; resize: vertical; tab-size: 4;"
        ></textarea>
        <div id="json-status" style="margin-top: 8px; font-size: 12px; font-weight: 600;"></div>
    </div>

    <style>
        .json-key {
            color: #e06c75;
            font-weight: 600;
            cursor: default;
        }
        .json-string { color: #98c379; }
        .json-number { color: #d19a66; }
        .json-boolean { color: #c678dd; font-weight: 600; }
        .json-null { color: #636d83; font-style: italic; }

        .json-node {
            margin-left: 0;
        }
        .json-children {
            margin-left: 20px;
            border-left: 1px solid var(--border-primary);
            padding-left: 12px;
        }

        .json-toggle {
            cursor: pointer;
            user-select: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px 0;
            border-radius: 4px;
            transition: background 0.15s;
        }
        .json-toggle:hover {
            background: var(--bg-tertiary);
        }
        .json-toggle-icon {
            display: inline-block;
            width: 16px;
            height: 16px;
            text-align: center;
            line-height: 16px;
            font-size: 10px;
            color: var(--text-tertiary);
            background: var(--bg-tertiary);
            border-radius: 3px;
            transition: transform 0.15s;
        }
        .json-toggle.collapsed .json-toggle-icon {
            transform: rotate(-90deg);
        }
        .json-toggle.collapsed + .json-children {
            display: none;
        }
        .json-count {
            font-size: 11px;
            color: var(--text-tertiary);
            font-weight: 400;
            font-style: italic;
        }

        .json-leaf {
            padding: 1px 0;
            display: flex;
            align-items: baseline;
            gap: 6px;
        }

        .editor-tab.active {
            background: var(--bg-secondary) !important;
            color: var(--text-primary) !important;
            border-bottom-color: var(--bg-secondary) !important;
        }

        #json-code-editor:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        /* Scrollbar styling */
        #panel-tree::-webkit-scrollbar {
            width: 8px;
        }
        #panel-tree::-webkit-scrollbar-track {
            background: var(--bg-primary);
            border-radius: 4px;
        }
        #panel-tree::-webkit-scrollbar-thumb {
            background: var(--border-primary);
            border-radius: 4px;
        }
        #panel-tree::-webkit-scrollbar-thumb:hover {
            background: var(--text-tertiary);
        }
    </style>

    <script>
        function switchTab(tab) {
            document.getElementById('panel-tree').style.display = tab === 'tree' ? 'block' : 'none';
            document.getElementById('panel-code').style.display = tab === 'code' ? 'block' : 'none';

            document.getElementById('tab-tree').classList.toggle('active', tab === 'tree');
            document.getElementById('tab-code').classList.toggle('active', tab === 'code');

            document.getElementById('tab-tree').style.background = tab === 'tree' ? 'var(--bg-secondary)' : 'var(--bg-tertiary)';
            document.getElementById('tab-tree').style.color = tab === 'tree' ? 'var(--text-primary)' : 'var(--text-tertiary)';
            document.getElementById('tab-code').style.background = tab === 'code' ? 'var(--bg-secondary)' : 'var(--bg-tertiary)';
            document.getElementById('tab-code').style.color = tab === 'code' ? 'var(--text-primary)' : 'var(--text-tertiary)';

            if (tab === 'tree') {
                renderTree();
            }
            if (tab === 'code') {
                validateJson();
            }
        }

        function renderTree() {
            const container = document.getElementById('json-tree');
            const textarea = document.getElementById('json-code-editor');
            try {
                const data = JSON.parse(textarea.value);
                container.innerHTML = buildTree(data, '', 0);
            } catch (e) {
                container.innerHTML = '<div style="color: var(--danger); padding: 16px;">⚠️ Invalid JSON — switch to Code Editor to fix</div>';
            }
        }

        function buildTree(data, key, depth) {
            if (data === null) {
                return leafNode(key, '<span class="json-null">null</span>');
            }
            if (typeof data === 'boolean') {
                return leafNode(key, `<span class="json-boolean">${data}</span>`);
            }
            if (typeof data === 'number') {
                return leafNode(key, `<span class="json-number">${data}</span>`);
            }
            if (typeof data === 'string') {
                const escaped = data.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                return leafNode(key, `<span class="json-string">"${escaped}"</span>`);
            }
            if (Array.isArray(data)) {
                const count = data.length;
                const collapsed = depth > 1 ? '' : '';
                let html = `<div class="json-node">`;
                html += `<div class="json-toggle ${collapsed}" onclick="this.classList.toggle('collapsed')">`;
                html += `<span class="json-toggle-icon">▼</span>`;
                if (key !== '') html += `<span class="json-key">${key}</span>: `;
                html += `<span class="json-count">[${count} items]</span>`;
                html += `</div>`;
                html += `<div class="json-children">`;
                data.forEach((item, i) => {
                    html += buildTree(item, i, depth + 1);
                });
                html += `</div></div>`;
                return html;
            }
            if (typeof data === 'object') {
                const keys = Object.keys(data);
                const collapsed = depth > 1 ? '' : '';
                let html = `<div class="json-node">`;
                html += `<div class="json-toggle ${collapsed}" onclick="this.classList.toggle('collapsed')">`;
                html += `<span class="json-toggle-icon">▼</span>`;
                if (key !== '') html += `<span class="json-key">${key}</span>: `;
                html += `<span class="json-count">{${keys.length} keys}</span>`;
                html += `</div>`;
                html += `<div class="json-children">`;
                keys.forEach(k => {
                    html += buildTree(data[k], k, depth + 1);
                });
                html += `</div></div>`;
                return html;
            }
            return '';
        }

        function leafNode(key, valueHtml) {
            let html = `<div class="json-leaf">`;
            if (key !== '') html += `<span class="json-key">${key}</span>: `;
            html += valueHtml;
            html += `</div>`;
            return html;
        }

        function validateJson() {
            const textarea = document.getElementById('json-code-editor');
            const status = document.getElementById('json-status');
            try {
                JSON.parse(textarea.value);
                status.innerHTML = '<span style="color: var(--success);">✅ Valid JSON</span>';
            } catch (e) {
                status.innerHTML = `<span style="color: var(--danger);">❌ ${e.message}</span>`;
            }
        }

        // Handle tab key in textarea
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('json-code-editor');
            if (textarea) {
                textarea.addEventListener('keydown', function(e) {
                    if (e.key === 'Tab') {
                        e.preventDefault();
                        const start = this.selectionStart;
                        const end = this.selectionEnd;
                        this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                        this.selectionStart = this.selectionEnd = start + 4;
                    }
                });
                textarea.addEventListener('input', validateJson);
            }

            // Render tree on load
            renderTree();

            // Re-render tree after Livewire updates (import, save)
            if (window.Livewire) {
                Livewire.hook('message.processed', () => {
                    setTimeout(renderTree, 100);
                });
            }
        });
    </script>
</div>

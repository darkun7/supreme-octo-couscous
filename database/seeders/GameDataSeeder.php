<?php

namespace Database\Seeders;

use App\Models\GameCollection;
use App\Models\GameCollectionField;
use App\Models\GameEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class GameDataSeeder extends Seeder
{
    protected $jsonPath;

    // Maps collection slug => which other collection it references
    // key = the field key, value = [target_collection_slug, display_field, value_field, multiple]
    // Supports nested keys with dot notation: 'drop_table.item_id'
    protected $relationMap = [
        'enemies' => [
            'skill_ids' => ['skills', 'name', 'id', true],
            'drop_table.item_id' => ['items', 'name', 'id', false],
            'box_drop_table.box_id' => ['resource_boxes', 'name', 'id', false],
        ],
        'pets' => [
            'skill_ids' => ['skills', 'name', 'id', true],
        ],
        'resource_boxes' => [
            'drop_table.item_id' => ['items', 'name', 'id', false],
        ],
        'enemy_spawn_areas' => [
            'enemy_id' => ['enemies', 'name', 'id', false],
            'area_ids' => ['areas', 'name', 'id', true],
        ],
        'pet_spawn_areas' => [
            'area_id' => ['areas', 'name', 'id', false],
            'pet_ids' => ['pets', 'name', 'id', true],
        ],
        'shop_items' => [
            'item_id' => ['items', 'name', 'id', false],
        ],
        'jobs' => [
            'pre_req.job' => ['jobs', 'name', 'id', false],
        ],
        'craftings' => [
            'ingredients.item_id' => ['items', 'name', 'id', false],
        ],
        'crafting_gears' => [
            'blueprint_item_id' => ['items', 'name', 'id', false],
            'ingredients.item_id' => ['items', 'name', 'id', false],
        ]
    ];

    public function run()
    {
        $this->jsonPath = storage_path('app/json');

        // Clear existing data (disable FK checks for truncate)
        Schema::disableForeignKeyConstraints();
        \App\Models\GameEntry::truncate();
        \App\Models\GameCollectionField::truncate();
        \App\Models\GameCollection::truncate();
        \App\Models\Game::truncate();
        Schema::enableForeignKeyConstraints();

        $game = \App\Models\Game::create([
            'name' => 'Pet Bot',
            'slug' => 'pet-bot',
        ]);

        // Define collections in order (so relations can reference earlier ones)
        $collections = [
            [
                'file' => 'skills.json',
                'name' => 'skills',
                'display_name' => 'Skills',
                'icon' => '⚡',
                'description' => 'All character and enemy skills/abilities',
                'id_field' => 'id',
                'name_field' => 'name',
            ],
            [
                'file' => 'items.json',
                'name' => 'items',
                'display_name' => 'Items',
                'icon' => '🎒',
                'description' => 'Game items including consumables, materials, blueprints',
                'id_field' => 'id',
                'name_field' => 'name',
            ],
            [
                'file' => 'equipment.json',
                'name' => 'equipment',
                'display_name' => 'Equipment',
                'icon' => '⚔️',
                'description' => 'Equipment pieces — weapons, armor, pet gear',
                'id_field' => 'id',
                'name_field' => 'name',
            ],
            [
                'file' => 'shop_items.json',
                'name' => 'shop_items',
                'display_name' => 'Shop Items',
                'icon' => '🛒',
                'description' => 'Items available in the game shop',
                'id_field' => 'item_id',
                'name_field' => 'item_id',
            ],
            [
                'file' => 'enemies.json',
                'name' => 'enemies',
                'display_name' => 'Enemies',
                'icon' => '👹',
                'description' => 'Enemy definitions with stats, skills, and drop tables',
                'id_field' => 'id',
                'name_field' => 'name',
            ],
            [
                'file' => 'pets.json',
                'name' => 'pets',
                'display_name' => 'Pets',
                'icon' => '🐾',
                'description' => 'Starter pets with elements and skills',
                'id_field' => 'id',
                'name_field' => 'name',
            ],
            [
                'file' => 'areas.json',
                'name' => 'areas',
                'display_name' => 'Areas',
                'icon' => '🗺️',
                'description' => 'Game world areas and zones',
                'id_field' => 'id',
                'name_field' => 'name',
            ],
            [
                'file' => 'enemy_spawn_areas.json',
                'name' => 'enemy_spawn_areas',
                'display_name' => 'Enemy Spawn Areas',
                'icon' => '📍',
                'description' => 'Mapping of enemies to their spawn areas',
                'id_field' => 'enemy_id',
                'name_field' => 'enemy_id',
            ],
            [
                'file' => 'pet_spawn_areas.json',
                'name' => 'pet_spawn_areas',
                'display_name' => 'Pet Spawn Areas',
                'icon' => '🐣',
                'description' => 'Mapping of areas to spawnable pets',
                'id_field' => 'area_id',
                'name_field' => 'area_id',
            ],
                        [
                'file' => 'resource_boxes.json',
                'name' => 'resource_boxes',
                'display_name' => 'Resource Boxes',
                'icon' => '📦',
                'description' => 'Loot boxes with material drop tables',
                'id_field' => 'id',
                'name_field' => 'name',
            ],
            [
                'file' => 'jobs.json',
                'name' => 'jobs',
                'display_name' => 'Jobs',
                'icon' => '👷',
                'description' => 'Player jobs and progressions',
                'id_field' => 'id',
                'name_field' => 'name',
            ],
            [
                'file' => 'job_loots.json',
                'name' => 'job_loots',
                'display_name' => 'Job Loots',
                'icon' => '🎣',
                'description' => 'Drop tables for job gathered resources',
                'id_field' => 'id',
                'name_field' => 'id',
            ],
            [
                'file' => 'craftings.json',
                'name' => 'craftings',
                'display_name' => 'Craftings',
                'icon' => '🧪',
                'description' => 'Recipes for consumables, cookies, tokens, and upgrades',
                'id_field' => 'id',
                'name_field' => 'result_item_id',
            ],
            [
                'file' => 'crafting_gears.json',
                'name' => 'crafting_gears',
                'display_name' => 'Crafting Gears',
                'icon' => '🔨',
                'description' => 'Group rules for crafting pet and master gears based on tier',
                'id_field' => 'id',
                'name_field' => 'id',
            ],
            [
                'file' => 'rule_craftings.json',
                'name' => 'rule_craftings',
                'display_name' => 'Crafting Rules',
                'icon' => '📜',
                'description' => 'Static rules for material tiers, equipment tiers, and crafting recipes',
                'id_field' => 'type',
                'name_field' => 'type',
                'type' => 'static',
            ],
        ];

        $sortOrder = 1;
        foreach ($collections as $colConfig) {
            $filePath = $this->jsonPath . '/' . $colConfig['file'];
            if (!File::exists($filePath)) {
                $this->command->warn("⚠️  Skipping {$colConfig['file']} — file not found");
                continue;
            }

            $jsonData = json_decode(File::get($filePath), true);
            if ($jsonData === null) {
                $this->command->warn("⚠️  Skipping {$colConfig['file']} — invalid JSON");
                continue;
            }

            $collectionType = $colConfig['type'] ?? 'tabular';

            // Create the collection
            $collection = \App\Models\GameCollection::create([
                'game_id' => $game->id,
                'name' => $colConfig['name'],
                'slug' => $colConfig['name'],
                'display_name' => $colConfig['display_name'],
                'icon' => $colConfig['icon'],
                'description' => $colConfig['description'],
                'id_field' => $colConfig['id_field'],
                'name_field' => $colConfig['name_field'],
                'type' => $collectionType,
                'sort_order' => $sortOrder++,
            ]);

            // ─── STATIC COLLECTIONS: store entire JSON as one entry ───
            if ($collectionType === 'static') {
                \App\Models\GameEntry::create([
                    'game_collection_id' => $collection->id,
                    'data' => $jsonData,
                    'sort_order' => 0,
                ]);
                $this->command->info("✅ {$colConfig['icon']} {$colConfig['display_name']}: static JSON loaded");
                continue;
            }

            // ─── TABULAR COLLECTIONS: normal field analysis + entries ───

            // If the JSON is an associative array (object keys instead of list), inject the key
            if (array_keys($jsonData) !== range(0, count($jsonData) - 1)) {
                $newJsonData = [];
                foreach ($jsonData as $key => $entryData) {
                    if (is_array($entryData) && !isset($entryData[$colConfig['id_field']])) {
                        $entryData = array_merge([$colConfig['id_field'] => $key], $entryData);
                    }
                    $newJsonData[$key] = $entryData;
                }
                $jsonData = $newJsonData;
            }

            // Analyze ALL entries to auto-detect field schema and enum values
            $entries = array_values($jsonData);
            if (empty($entries)) {
                $this->command->warn("⚠️  {$colConfig['file']} has no entries");
                continue;
            }

            // Step 1: Collect unique values per field across ALL entries (for enum detection)
            $fieldValues = $this->collectFieldValues($entries);

            // Step 2: Create fields using the first entry's structure + enum data from all entries
            $sampleEntry = $entries[0];
            $fields = $this->analyzeAndCreateFields($collection, $sampleEntry, $colConfig['name'], null, $fieldValues, $entries);

            // Step 3: Seed entries
            $entryCount = 0;
            foreach ($jsonData as $key => $entryData) {
                \App\Models\GameEntry::create([
                    'game_collection_id' => $collection->id,
                    'data' => $entryData,
                    'sort_order' => $entryCount,
                ]);
                $entryCount++;
            }

            // Count how many enum fields were detected
            $enumCount = collect($fields)->filter(fn($f) => !empty($f->options))->count();
            $this->command->info("✅ {$colConfig['icon']} {$colConfig['display_name']}: {$entryCount} entries, " . count($fields) . " fields" . ($enumCount ? " ({$enumCount} enums)" : ''));
        }

        // Post-process: set up relation fields now that all collections exist
        $this->setupRelations($game->id);

        $this->command->info('');
        $this->command->info('🎮 Game data seeding complete!');
    }

    /**
     * Collect unique values for each field across ALL entries.
     * This is used to detect enum/dropdown fields.
     * Returns: ['fieldKey' => ['value1', 'value2', ...], 'nested.childKey' => [...]]
     */
    protected function collectFieldValues($entries, $prefix = '')
    {
        $fieldValues = [];

        foreach ($entries as $entry) {
            if (!is_array($entry)) continue;
            $this->collectValuesRecursive($entry, $prefix, $fieldValues);
        }

        return $fieldValues;
    }

    protected function collectValuesRecursive($data, $prefix, &$fieldValues)
    {
        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_string($value)) {
                if (!isset($fieldValues[$fullKey])) {
                    $fieldValues[$fullKey] = [];
                }
                if ($value !== '' && !in_array($value, $fieldValues[$fullKey])) {
                    $fieldValues[$fullKey][] = $value;
                }
            } elseif (is_array($value) && $this->isAssocArray($value)) {
                // Recurse into nested objects
                $this->collectValuesRecursive($value, $fullKey, $fieldValues);
            }
        }
    }

    /**
     * Determine if a string field should be an enum/dropdown.
     * Criteria:
     *  - Has between 2 and 30 unique non-empty values
     *  - Not an ID, Name, Description, or other free-text field
     *  - Ratio of unique values to total entries is <= 50% (shows repetition)
     */
    protected function shouldBeEnum($key, $uniqueValues, $totalEntries)
    {
        $count = count($uniqueValues);

        // Skip fields that are clearly free-text
        $freeTextKeys = ['id', 'name', 'description', 'emoji', 'setgroup'];
        if (in_array(strtolower($key), $freeTextKeys)) {
            return false;
        }

        // Must have at least 2 unique values and no more than 30
        if ($count < 2 || $count > 30) {
            return false;
        }

        // If values are very long strings (avg > 50 chars), probably not an enum
        $avgLen = array_sum(array_map('strlen', $uniqueValues)) / $count;
        if ($avgLen > 50) {
            return false;
        }

        // Good ratio: fewer unique values compared to total entries
        // Or explicitly known enum-like field names
        $enumKeyPatterns = ['type', 'element', 'slot', 'category', 'rarity', 'tier', 'effect', 'equipmenttype', 'equipmentslot'];
        $keyLower = strtolower($key);
        foreach ($enumKeyPatterns as $pattern) {
            if (str_contains($keyLower, $pattern)) {
                return true;
            }
        }

        // If ratio is low (lots of repetition), it's enum-like
        if ($totalEntries > 5 && ($count / $totalEntries) <= 0.5) {
            return true;
        }

        return false;
    }

    /**
     * Analyze a sample entry and create field definitions.
     * Now uses $fieldValues from ALL entries for enum detection.
     */
    protected function analyzeAndCreateFields($collection, $sample, $collectionName, $parentKey = null, $fieldValues = [], $allEntries = [])
    {
        $fields = [];
        $sortOrder = 1;

        // Estimate total entries for enum detection
        $rootCounts = [];
        foreach ($fieldValues as $fk => $fv) {
            if (!str_contains($fk, '.')) {
                $rootCounts[] = count($fv);
            }
        }
        $estimatedTotal = !empty($rootCounts) ? max($rootCounts) : 10;

        foreach ($sample as $key => $value) {
            $fullKey = $parentKey ? "{$parentKey}.{$key}" : $key;

            $fieldData = [
                'game_collection_id' => $collection->id,
                'key' => $key,
                'label' => $this->keyToLabel($key),
                'parent_key' => $parentKey,
                'sort_order' => $sortOrder++,
            ];

            // Determine type from value
            if (is_bool($value)) {
                $fieldData['type'] = 'boolean';
                $fieldData['input_type'] = 'checkbox';
                $fieldData['default_value'] = $value;
            } elseif (is_int($value) || is_float($value)) {
                $fieldData['type'] = 'number';
                $fieldData['input_type'] = 'number';
                $fieldData['default_value'] = $value;
            } elseif (is_string($value) || is_null($value)) {
                $fieldData['type'] = 'string';

                // Check if this should be an enum/dropdown
                $uniqueVals = $fieldValues[$fullKey] ?? [];

                if (!empty($uniqueVals) && $this->shouldBeEnum($key, $uniqueVals, $estimatedTotal)) {
                    $fieldData['input_type'] = 'text';
                    sort($uniqueVals);
                    $fieldData['options'] = $uniqueVals;
                    $this->command->line("      📋 {$fullKey}: enum with " . count($uniqueVals) . " values");
                } else {
                    $fieldData['input_type'] = $this->guessStringInputType($key, $value ?? '');
                }

                // Check if this field is a nested relation (e.g. drop_table.item_id)
                if ($this->isRelationField($collectionName, $fullKey)) {
                    $fieldData['type'] = 'relation';
                    $fieldData['input_type'] = 'relation';
                    $fieldData['relation_multiple'] = false;
                }
            } elseif (is_array($value)) {
                if ($this->isAssocArray($value)) {
                    // Nested object
                    $fieldData['type'] = 'object';
                    $fieldData['input_type'] = 'object';

                    $field = GameCollectionField::firstOrCreate(
                        [
                            'game_collection_id' => $fieldData['game_collection_id'],
                            'key' => $fieldData['key'],
                            'parent_key' => $fieldData['parent_key']
                        ],
                        $fieldData
                    );
                    $fields[] = $field;

                    $childFields = $this->analyzeAndCreateFields($collection, $value, $collectionName, $key, $fieldValues, $allEntries);
                    $fields = array_merge($fields, $childFields);
                    continue;
                } elseif (!empty($value) && is_array($value[0]) && $this->isAssocArray($value[0])) {
                    // Array of objects (non-empty)
                    $fieldData['type'] = 'array_of_objects';
                    $fieldData['input_type'] = 'array_of_objects';

                    $field = GameCollectionField::firstOrCreate(
                        [
                            'game_collection_id' => $fieldData['game_collection_id'],
                            'key' => $fieldData['key'],
                            'parent_key' => $fieldData['parent_key']
                        ],
                        $fieldData
                    );
                    $fields[] = $field;

                    $childFields = $this->analyzeAndCreateFields($collection, $value[0], $collectionName, $key, $fieldValues, $allEntries);
                    $fields = array_merge($fields, $childFields);
                    continue;
                } elseif (empty($value)) {
                    // Empty array — scan all entries to find a non-empty sample
                    $foundSample = $this->findNonEmptyArraySample($allEntries, $key, $parentKey);

                    if ($foundSample !== null && is_array($foundSample) && $this->isAssocArray($foundSample)) {
                        // It's actually an array of objects!
                        $fieldData['type'] = 'array_of_objects';
                        $fieldData['input_type'] = 'array_of_objects';

                        $field = GameCollectionField::firstOrCreate(
                        [
                            'game_collection_id' => $fieldData['game_collection_id'],
                            'key' => $fieldData['key'],
                            'parent_key' => $fieldData['parent_key']
                        ],
                        $fieldData
                    );
                        $fields[] = $field;

                        $childFields = $this->analyzeAndCreateFields($collection, $foundSample, $collectionName, $key, $fieldValues, $allEntries);
                        $fields = array_merge($fields, $childFields);
                        continue;
                    } else {
                        // Truly empty or simple array
                        if ($this->isRelationField($collectionName, $key)) {
                            $fieldData['type'] = 'relation';
                            $fieldData['input_type'] = 'relation';
                            $fieldData['relation_multiple'] = true;
                        } else {
                            $fieldData['type'] = 'array';
                            $fieldData['input_type'] = 'tags';
                        }
                    }
                } else {
                    // Simple array (of strings/numbers)
                    if ($this->isRelationField($collectionName, $key)) {
                        $fieldData['type'] = 'relation';
                        $fieldData['input_type'] = 'relation';
                        $fieldData['relation_multiple'] = true;
                    } else {
                        $fieldData['type'] = 'array';
                        $fieldData['input_type'] = 'tags';
                    }
                }
            }

            $field = GameCollectionField::firstOrCreate(
                [
                    'game_collection_id' => $fieldData['game_collection_id'],
                    'key' => $fieldData['key'],
                    'parent_key' => $fieldData['parent_key']
                ],
                $fieldData
            );
            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * Scan all entries to find a non-empty sample for an array field.
     * Returns the first element of the first non-empty array found, or null.
     */
    protected function findNonEmptyArraySample($allEntries, $key, $parentKey = null)
    {
        foreach ($allEntries as $entry) {
            if ($parentKey) {
                $nested = data_get($entry, $parentKey);
                if (is_array($nested) && isset($nested[$key]) && is_array($nested[$key]) && !empty($nested[$key])) {
                    return $nested[$key][0];
                }
            } else {
                if (isset($entry[$key]) && is_array($entry[$key]) && !empty($entry[$key])) {
                    return $entry[$key][0];
                }
            }
        }
        return null;
    }

    /**
     * Check if a field is a known relation.
     * Supports both simple keys ('skill_ids') and dot-notation ('drop_table.item_id').
     */
    protected function isRelationField($collectionName, $key)
    {
        return isset($this->relationMap[$collectionName][$key]);
    }

    /**
     * Set up relation fields after all collections are created.
     */
    protected function setupRelations()
    {
        foreach ($this->relationMap as $collectionSlug => $relations) {
            if (empty($relations)) continue;

            $collection = GameCollection::where('slug', $collectionSlug)->first();
            if (!$collection) continue;

            foreach ($relations as $fieldKey => $relConfig) {
                [$targetSlug, $displayField, $valueField, $multiple] = $relConfig;

                $targetCollection = GameCollection::where('slug', $targetSlug)->first();
                if (!$targetCollection) continue;

                // Handle dot-notation keys (e.g. 'drop_table.item_id' → parent_key='drop_table', key='item_id')
                if (str_contains($fieldKey, '.')) {
                    [$parentKey, $childKey] = explode('.', $fieldKey, 2);
                    $field = GameCollectionField::where('game_collection_id', $collection->id)
                        ->where('parent_key', $parentKey)
                        ->where('key', $childKey)
                        ->first();
                } else {
                    $field = GameCollectionField::where('game_collection_id', $collection->id)
                        ->where('key', $fieldKey)
                        ->whereNull('parent_key')
                        ->first();
                }

                if ($field) {
                    $field->update([
                        'type' => 'relation',
                        'input_type' => 'relation',
                        'relation_collection_id' => $targetCollection->id,
                        'relation_display_field' => $displayField,
                        'relation_value_field' => $valueField,
                        'relation_multiple' => $multiple,
                    ]);
                    $this->command->info("   🔗 {$collectionSlug}.{$fieldKey} → {$targetSlug}");
                }
            }
        }
    }



    /**
     * Convert a camelCase or PascalCase key to a human-readable label.
     */
    protected function keyToLabel($key)
    {
        // Insert space before uppercase letters
        $label = preg_replace('/([a-z])([A-Z])/', '$1 $2', $key);
        // Handle acronyms like "ID", "HP"
        $label = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1 $2', $label);
        // Handle underscores
        $label = str_replace('_', ' ', $label);
        return ucwords($label);
    }

    /**
     * Guess the appropriate input type for a string field.
     */
    protected function guessStringInputType($key, $value)
    {
        $keyLower = strtolower($key);

        if (in_array($keyLower, ['description'])) return 'textarea';
        if (in_array($keyLower, ['color', 'iconcolor'])) return 'color';
        if (str_contains($keyLower, 'url') || str_contains($keyLower, 'image')) return 'text';

        return 'text';
    }

    /**
     * Check if an array is associative (object) vs sequential (list).
     */
    protected function isAssocArray($arr)
    {
        if (!is_array($arr) || empty($arr)) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}

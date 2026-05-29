<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class GameCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id', 'name', 'slug', 'display_name', 'icon', 'description',
        'id_field', 'name_field', 'type', 'sort_order',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function fields()
    {
        return $this->hasMany(GameCollectionField::class)->orderBy('sort_order');
    }

    public function rootFields()
    {
        return $this->hasMany(GameCollectionField::class)
                    ->whereNull('parent_key')
                    ->orderBy('sort_order');
    }

    public function entries()
    {
        return $this->hasMany(GameEntry::class);
    }

    /**
     * Get fields grouped by parent_key for nested rendering.
     */
    public function getGroupedFieldsAttribute()
    {
        return $this->fields->groupBy(function ($field) {
            return $field->parent_key ?? '__root__';
        });
    }

    /**
     * Get the object/nested parent keys that exist.
     */
    public function getNestedKeysAttribute()
    {
        return $this->fields
            ->whereNotNull('parent_key')
            ->pluck('parent_key')
            ->unique()
            ->values();
    }

    /**
     * Check if S3 is configured in the application.
     */
    public static function isS3Configured()
    {
        return !empty(config('filesystems.disks.s3.key')) &&
               !empty(config('filesystems.disks.s3.secret')) &&
               !empty(config('filesystems.disks.s3.bucket'));
    }

    /**
     * Export collection entries to a JSON string and upload to S3.
     * Returns the destination path on S3.
     */
    public function uploadToS3()
    {
        if ($this->type === 'static') {
            $entry = GameEntry::where('game_collection_id', $this->id)->first();
            $data = $entry ? $entry->data : new \stdClass();
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            $entries = GameEntry::where('game_collection_id', $this->id)
                ->orderBy('sort_order')
                ->get()
                ->pluck('data');

            $idField = $this->id_field;
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

            $exportData = (object) $exportData;
            $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $filename = $this->slug . '.json';
        $gameName = \Illuminate\Support\Str::slug($this->game->name ?? 'game');
        $path = '/static/game-manager/' . $gameName . '/json/' . $filename;

        \Illuminate\Support\Facades\Storage::disk('s3')->put($path, $json, 'public');

        return $path;
    }
}

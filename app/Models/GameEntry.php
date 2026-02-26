<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_collection_id', 'data', 'sort_order',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function collection()
    {
        return $this->belongsTo(GameCollection::class, 'game_collection_id');
    }

    /**
     * Get a nested value from the JSON data using dot notation.
     */
    public function getDataValue($key, $default = null)
    {
        return data_get($this->data, $key, $default);
    }

    /**
     * Set a nested value in the JSON data using dot notation.
     */
    public function setDataValue($key, $value)
    {
        $data = $this->data ?? [];
        data_set($data, $key, $value);
        $this->data = $data;
        return $this;
    }

    /**
     * Get the entry's display name based on collection config.
     */
    public function getDisplayNameAttribute()
    {
        $nameField = $this->collection->name_field ?? 'Name';
        return $this->getDataValue($nameField, 'Unnamed');
    }

    /**
     * Get the entry's ID value based on collection config.
     */
    public function getEntryIdAttribute()
    {
        $idField = $this->collection->id_field ?? 'ID';
        return $this->getDataValue($idField, $this->id);
    }
}

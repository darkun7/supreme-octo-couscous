<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameCollectionField extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_collection_id', 'key', 'label', 'type', 'input_type',
        'options', 'default_value', 'required', 'is_array', 'parent_key',
        'relation_collection_id', 'relation_display_field',
        'relation_value_field', 'relation_multiple', 'sort_order', 'help_text',
    ];

    protected $casts = [
        'options' => 'array',
        'default_value' => 'array',
        'required' => 'boolean',
        'is_array' => 'boolean',
        'relation_multiple' => 'boolean',
    ];

    public function collection()
    {
        return $this->belongsTo(GameCollection::class, 'game_collection_id');
    }

    public function relationCollection()
    {
        return $this->belongsTo(GameCollection::class, 'relation_collection_id');
    }

    /**
     * Get all child fields (fields nested under this field's key).
     */
    public function childFields()
    {
        return GameCollectionField::where('game_collection_id', $this->game_collection_id)
                                  ->where('parent_key', $this->key)
                                  ->orderBy('sort_order')
                                  ->get();
    }

    /**
     * Get the full dot-notation path for this field.
     */
    public function getFullKeyAttribute()
    {
        return $this->parent_key ? "{$this->parent_key}.{$this->key}" : $this->key;
    }

    /**
     * Check if this field is a relation type.
     */
    public function getIsRelationAttribute()
    {
        return $this->type === 'relation';
    }

    /**
     * Check if this field is a nested object container.
     */
    public function getIsObjectAttribute()
    {
        return in_array($this->type, ['object', 'array_of_objects']);
    }
}

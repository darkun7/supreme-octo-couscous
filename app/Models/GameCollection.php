<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
}

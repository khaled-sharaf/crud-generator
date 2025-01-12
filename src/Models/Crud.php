<?php

namespace Khaled\CrudSystem\Models;

use Illuminate\Database\Eloquent\Model;

class Crud extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'file_name',
        'module',
        'old_config',
        'current_config',
        'generated_at',
        'locked',
    ];

    protected $dates = [
        'generated_at',
    ];

    protected $casts = [
        'old_config' => 'array',
        'current_config' => 'array',
        'locked' => 'boolean',
    ];


    public function scopeGenerated($query, $value = true)
    {
        if ($value) {
            return $query->whereNotNull('generated_at');
        }
        return $query->whereNull('generated_at');
    }

    public function scopeLocked($query, $value = true)
    {
        return $query->where('locked', $value);
    }

    public function markAsGenerated($value = true)
    {
        $this->generated_at = $value ? now() : null;
        $this->save();
    }

    public function markAsLocked()
    {
        $this->locked = true;
        $this->save();
    }

    public function generate(array $config, bool $looked = false)
    {
        $this->old_config = $this->current_config;
        $this->current_config = $config;
        $this->looked = $looked;
        $this->generated_at = now();
        $this->save();
    }

    public function getIsGeneratedAttribute()
    {
        return $this->generated_at !== null;
    }

    public static function newCrud(string $name, string $fileName, string $module)
    {
        return self::create([
            'name' => $name,
            'file_name' => $fileName,
            'module' => $module,
        ]);
    }

}

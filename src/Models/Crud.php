<?php

namespace W88\CrudSystem\Models;

use Illuminate\Database\Eloquent\Model;

class Crud extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'module',
        'config',
        'generated_at',
    ];

    protected $dates = [
        'generated_at',
    ];

    protected $casts = [
        'config' => 'array',
    ];


    public function scopeGenerated($query, $value = true)
    {
        if ($value) {
            return $query->whereNotNull('generated_at');
        }
        return $query->whereNull('generated_at');
    }

    public function markAsGenerated($value = true)
    {
        $this->generated_at = $value ? now() : null;
        $this->save();
    }

    public function getIsGeneratedAttribute()
    {
        return $this->generated_at !== null;
    }

    public static function newCrud(string $module, string $name)
    {
        return self::create([
            'name' => $name,
            'module' => $module,
        ]);
    }

}

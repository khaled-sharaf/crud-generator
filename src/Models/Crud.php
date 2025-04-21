<?php

namespace Khaled\CrudSystem\Models;

use Illuminate\Database\Eloquent\Model;
use Khaled\CrudSystem\Services\CrudConfigTransformService;

class Crud extends Model
{

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'crud_sqlite';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'module',
        'frontend_module',
        'file_name',
        'old_config',
        'current_config',
        'generated_at',
    ];

    protected $dates = [
        'generated_at',
    ];

    protected $casts = [
        'old_config' => 'array',
        'current_config' => 'array',
        'generated_at' => 'datetime',
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

    public function generate(array $config, bool $looked = false)
    {
        $this->old_config = $this->current_config;
        $this->current_config = $config;
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

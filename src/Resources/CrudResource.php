<?php

namespace Khaled\CrudSystem\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CrudResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'module' => $this->module,
            'frontend_module' => $this->frontend_module,
            'generated_at' => $this->generated_at ? formatDate($this->generated_at) : null,
            'config' => $this->current_config ?? new \stdClass(),
			'created_at' => formatDate($this->created_at),
			'updated_at' => formatDate($this->updated_at)
        ];
    }
}

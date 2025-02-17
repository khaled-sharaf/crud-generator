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
            'file_name' => $this->file_name,
            'module' => $this->module,
            'generated_at' => $this->generated_at ? formatDate($this->generated_at) : null,
            'locked' => $this->locked,
			'created_at' => formatDate($this->created_at),
			'updated_at' => formatDate($this->updated_at)
        ];
    }
}

<?php

namespace Khaled\CrudSystem\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrudUpdateConfigRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // 'config' => 'required|array',
            'name' => 'required|array',
            'name.en' => 'required|array',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}

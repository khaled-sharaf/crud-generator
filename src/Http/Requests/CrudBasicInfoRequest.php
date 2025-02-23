<?php

namespace Khaled\CrudSystem\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class CrudBasicInfoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('Khaled\CrudSystem\Models\Crud', 'name')->ignore($this->id)
                ->where(function ($query) {
                    $query->where('module', $this->module)
                    ->orWhere('frontend_module', $this->frontend_module);
                })
            ],
            'module' => 'required|string|max:100',
            'frontend_module' => 'required|string|max:100'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->name == 'crud') {
                    $validator->errors()->add(
                        'name',
                        'The name is reserved for internal use.'
                    );
                }
            }
        ];
    }
}

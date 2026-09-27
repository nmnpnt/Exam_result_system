<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMarkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
            'assessment_component_id' => ['required', 'integer', 'exists:assessment_components,id'],
            'marks_obtained' => ['required', 'numeric', 'min:0'],
        ];
    }
}

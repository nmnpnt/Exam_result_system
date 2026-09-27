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
            'enrollment_id' => ['required_without:roll_number', 'integer', 'exists:enrollments,id'],
            'assessment_component_id' => ['required_without:component_name', 'integer', 'exists:assessment_components,id'],
            
            // Allow string-based lookup for UI convenience
            'roll_number' => ['string', 'exists:students,roll_number'],
            'course_code' => ['string', 'exists:courses,code'],
            'component_name' => ['string'],
            
            'marks_obtained' => ['required', 'numeric', 'min:0'],
        ];
    }
}

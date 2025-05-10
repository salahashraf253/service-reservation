<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reservation_datetime' => 'required|date|after:now',
        ];
    }
    public function messages(): array
    {
        return [
            'reservation_datetime.required' => 'The reservation date and time is required.',
            'reservation_datetime.date' => 'The reservation date and time must be a valid date.',
            'reservation_datetime.after' => 'The reservation date and time must be in the future.',
        ];
    }

}

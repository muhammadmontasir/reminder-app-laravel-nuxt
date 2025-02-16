<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class SyncRequest extends FormRequest
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
            'events' => 'required|array|max:100',
            'events.*.client_id' => 'required|uuid',
            'events.*.title' => 'required|string|max:255',
            'events.*.description' => 'nullable|string',
            'events.*.start_time' => 'required|date',
            'events.*.end_time' => 'required|date|after:events.*.start_time',
            'events.*.status' => 'sometimes|in:upcoming,completed',
            'events.*.is_online' => 'sometimes|boolean',
            'events.*.metadata' => 'nullable|array',
            'events.*.reminder_time' => [
                'nullable',
                'date'
            ],
            'events.*.participants' => [
                'nullable',
                'array',
                'required_with:events.*.reminder_time',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        foreach ($value as $email) {
                            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $fail("The email address '{$email}' is invalid.");
                            }
                        }
                    }
                }
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'events.max' => 'You can sync up to 100 events at a time',
            'events.*.client_id.uuid' => 'Each event must have a valid UUID',
            'events.*.end_time.after' => 'The end time must be after the start time',
            'events.*.participants.required_with' => 'Participants are required when setting a reminder time'
        ];
    }
}

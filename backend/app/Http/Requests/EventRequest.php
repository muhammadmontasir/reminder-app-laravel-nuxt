<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class EventRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'status' => 'sometimes|in:upcoming,completed',
            'metadata' => 'nullable|array',
            'client_id' => 'sometimes|uuid',
            'reminder_time' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $reminderTime = Carbon::parse($value);
                        $startTime = Carbon::parse($this->input('start_time'));
                        $now = Carbon::now();
                        
                        if ($reminderTime->isPast()) {
                            $fail('The reminder time must be in the future.');
                        }
                        
                        if ($reminderTime->isAfter($startTime)) {
                            $fail('The reminder time must be before the event start time.');
                        }

                        if (empty($this->input('participants'))) {
                            $fail('At least one participant is required when setting a reminder time.');
                        }
                    }
                }
            ],
            'participants' => [
                'nullable',
                'array',
                'required_with:reminder_time',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        foreach ($value as $email) {
                            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $fail("The email address '{$email}' is invalid.");
                            }
                        }
                    }
                }
            ],
            'participants.*' => 'email'
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.after' => 'The event must start in the future',
            'end_time.after' => 'The end time must be after the start time',
            'participants.array' => 'Participants must be provided as an array of email addresses',
            'participants.*.email' => 'Each participant must be a valid email address'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('participants') && is_string($this->input('participants'))) {
            $data['participants'] = json_decode($this->input('participants'), true);
        }

        if (!$this->has('client_id')) {
            $data['client_id'] = auth()->user()->client_id;
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }
}

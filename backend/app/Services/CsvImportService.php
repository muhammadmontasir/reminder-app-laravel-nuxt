<?php

namespace App\Services;

use App\Repositories\EventRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Events\EventCreated;

class CsvImportService
{
    public function __construct(
        protected EventRepository $eventRepository,
        protected IdGenerationService $idGenerationService
    ) {
    }

    public function import($filePath)
    {
        try {
            if (!($handle = fopen($filePath, 'r'))) {
                throw new \Exception('Unable to open file');
            }

            $headers = fgetcsv($handle);
            $this->validateHeaders($headers);

            $results = [
                'success' => 0,
                'failed' => 0,
                'errors' => []
            ];

            $rowNumber = 1;
            while (($record = fgetcsv($handle)) !== false) {
                $rowNumber++;
                try {
                    $data = array_combine($headers, $record);
                    if ($this->validateRecord($data)) {
                        $this->processRecord($data);
                        $results['success']++;
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                    
                    Log::channel('import')->error('Import row failed', [
                        'row' => $rowNumber,
                        'data' => $data ?? $record,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            fclose($handle);
            return $results;

        } catch (\Exception $e) {
            Log::channel('import')->error('Import failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function importContent(string $content)
    {
        try {
            $handle = fopen('php://temp', 'r+');
            fwrite($handle, $content);
            rewind($handle);

            $headers = fgetcsv($handle);
            $this->validateHeaders($headers);

            $results = [
                'success' => 0,
                'failed' => 0,
                'errors' => []
            ];

            $rowNumber = 1;
            while (($record = fgetcsv($handle)) !== false) {
                $rowNumber++;
                try {
                    $data = array_combine($headers, $record);
                    if ($this->validateRecord($data)) {
                        $this->processRecord($data);
                        $results['success']++;
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                    
                    Log::channel('import')->error('Import row failed', [
                        'row' => $rowNumber,
                        'data' => $data ?? $record,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            fclose($handle);
            return $results;

        } catch (\Exception $e) {
            Log::channel('import')->error('Import failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function validateHeaders(array $headers): void
    {
        $requiredHeaders = ['title', 'start_time', 'end_time'];
        $allowedHeaders = [...$requiredHeaders, 'description', 'reminder_time', 'participants'];
        $missing = array_diff($requiredHeaders, $headers);

        if (!empty($missing)) {
            throw new \Exception('Missing required columns: ' . implode(', ', $missing));
        }
    }

    protected function validateRecord(array $record): bool
    {
        $validator = Validator::make($record, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s|after_or_equal:start_time',
            'reminder_time' => 'nullable|date_format:Y-m-d H:i:s',
            'participants' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        if (!empty($record['reminder_time'])) {
            $reminderTime = Carbon::parse($record['reminder_time']);
            $startTime = Carbon::parse($record['start_time']);
            
            if ($reminderTime->isPast()) {
                throw new \Exception('Reminder time must be in the future');
            }
            
            if ($reminderTime->isAfter($startTime)) {
                throw new \Exception('Reminder time must be before the event start time');
            }

            if (empty($record['participants'])) {
                throw new \Exception('Participants are required when setting a reminder time');
            }
        }

        return true;
    }

    protected function processRecord(array $record)
    {
        $endDate = Carbon::parse($record['end_time']);
        $status = $endDate->isPast() ? 'completed' : 'upcoming';

        $participants = null;
        if (!empty($record['participants'])) {
            $participants = array_map('trim', explode(',', $record['participants']));
            
            foreach ($participants as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception("Invalid email address: {$email}");
                }
            }
        }

        if ($status === 'completed') {
            $record['reminder_time'] = null;
            $participants = null;
        }

        $event = $this->eventRepository->create([
            'client_id' => (string) Str::uuid(),
            'event_id' => $this->idGenerationService->generate(),
            'title' => $record['title'],
            'description' => $record['description'] ?? null,
            'start_time' => $record['start_time'],
            'end_time' => $record['end_time'],
            'reminder_time' => $record['reminder_time'] ?? null,
            'participants' => $participants,
            'status' => $status
        ]);

        if ($event && $status === 'upcoming' && !empty($record['reminder_time'])) {
            event(new EventCreated($event));
        }

        return $event;
    }
}
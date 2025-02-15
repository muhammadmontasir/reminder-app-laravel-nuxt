<?php

namespace App\Services;

use App\Repositories\EventRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
        info('importing content');
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
            'start_time' => 'required|date_format:Y-m-d',
            'end_time' => 'required|date_format:Y-m-d|after_or_equal:start_time',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        return true;
    }

    protected function processRecord(array $record)
    {
        $endDate = \Carbon\Carbon::parse($record['end_time']);
        $status = $endDate->isFuture() ? 'upcoming' : 'completed';

        return $this->eventRepository->create([
            'client_id' => (string) \Str::uuid(),
            'event_id' => $this->idGenerationService->generate(),
            'title' => $record['title'],
            'description' => $record['description'] ?? null,
            'start_time' => $record['start_time'],
            'end_time' => $record['end_time'],
            'status' => $status
        ]);
    }
}
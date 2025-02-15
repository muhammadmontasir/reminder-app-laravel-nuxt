<?php

namespace App\Jobs;

use App\Services\CsvImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCsvImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 900;

    public function __construct(protected string $fileContent)
    {
    }

    public function handle(CsvImportService $importService): void
    {
        try {
            $results = $importService->importContent($this->fileContent);

            Log::channel('import')->info('CSV import completed', [
                'success' => $results['success'],
                'failed' => $results['failed']
            ]);
        } catch (\Exception $e) {
            Log::channel('import')->error('CSV import failed', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('import')->error('CSV import job failed', [
            'error' => $exception->getMessage()
        ]);
    }
}
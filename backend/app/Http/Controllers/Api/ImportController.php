<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Services\CsvImportService;
use App\Jobs\ProcessCsvImport;

class ImportController extends Controller
{
    public function __construct(protected CsvImportService $importService)
    {
    }

    public function import(ImportRequest $request)
    {
        try {
            $file = $request->file('file');
            $content = file_get_contents($file->getPathname());
            
            ProcessCsvImport::dispatch($content);

            return response()->json([
                'message' => 'Import started successfully',
                'job_id' => $file->hashName()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
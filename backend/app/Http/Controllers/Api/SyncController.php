<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncRequest;
use App\Services\SyncService;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    public function __construct(protected SyncService $syncService)
    {
    }

    public function sync(SyncRequest $request)
    {
        try {
            $results = $this->syncService->syncEvents($request->input('events'));
            
            return response()->json([
                'message' => 'Events synchronized successfully',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            Log::channel('sync')->error('Sync failed', [
                'error' => $e->getMessage(),
                'events' => $request->input('events')
            ]);

            return response()->json([
                'message' => 'Sync failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
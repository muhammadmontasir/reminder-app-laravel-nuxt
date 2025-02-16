<?php

namespace App\Services;

use App\Repositories\EventRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SyncService
{
    public function __construct(
        protected EventRepository $eventRepository,
        protected IdGenerationService $idGenerationService
    ) {
    }

    public function syncEvents(array $events)
    {
        try {
            return DB::transaction(function () use ($events) {
                $results = [];
                
                foreach ($events as $eventData) {
                    $eventData['updated_at'] = $eventData['updated_at'] ?? now();
                    $results[] = $this->syncEvent($eventData);
                }

                return [
                    'success' => true,
                    'synced_events' => $results
                ];
            });
        } catch (\Exception $e) {
            Log::channel('sync')->error('Sync failed: ' . $e->getMessage(), [
                'events' => $events,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function syncEvent(array $eventData)
    {
        // Calculate status based on time
        $now = now();
        $startTime = strtotime($eventData['start_time']);
        $endTime = strtotime($eventData['end_time']);
        
        $eventData['status'] = match(true) {
            $endTime < $now->timestamp => 'completed',
            $startTime >= $now->timestamp => 'upcoming',
        };

        $existingEvent = $this->eventRepository->findByClientId($eventData['client_id']);

        if (!$existingEvent) {
            $eventData['event_id'] = $this->idGenerationService->generate();
            return $this->eventRepository->create($eventData);
        }

        // Last write wins - if incoming event is newer or same time, it overwrites
        if (strtotime($eventData['updated_at']) >= strtotime($existingEvent->updated_at)) {
            return $this->eventRepository->update($existingEvent, $eventData);
        }

        return $existingEvent;
    }
}
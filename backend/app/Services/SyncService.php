<?php

namespace App\Services;

use App\Repositories\EventRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Events\EventCreated;

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
                $results = [
                    'created' => [],
                    'updated' => [],
                    'unchanged' => []
                ];
                
                foreach ($events as $eventData) {
                    $eventData['updated_at'] = $eventData['updated_at'] ?? now();
                    
                    if (Carbon::parse($eventData['end_time'])->isPast()) {
                        $eventData['status'] = 'completed';
                        $eventData['reminder_time'] = null;
                        $eventData['participants'] = null;
                    }
                    
                    if (!isset($eventData['event_id'])) {
                        $eventData['event_id'] = $this->idGenerationService->generate();
                        $event = $this->eventRepository->create($eventData);
                        $results['created'][] = $event;
                        EventCreated::dispatch($event);
                    } else {
                        $existingEvent = $this->eventRepository->findByClientIdAndEventId(
                            $eventData['client_id'],
                            $eventData['event_id']
                        );
                        
                        if (!$existingEvent) {
                            $event = $this->eventRepository->create($eventData);
                            $results['created'][] = $event;
                            EventCreated::dispatch($event);
                        } else {
                            // Last write wins - if incoming event is newer or same time
                            if (strtotime($eventData['updated_at']) >= strtotime($existingEvent->updated_at)) {
                                $event = $this->eventRepository->update($existingEvent, $eventData);
                                $results['updated'][] = $event;
                                EventCreated::dispatch($event);
                            } else {
                                $results['unchanged'][] = $existingEvent;
                            }
                        }
                    }
                }

                return [
                    'success' => true,
                    'created' => count($results['created']),
                    'updated' => count($results['updated']),
                    'unchanged' => count($results['unchanged']),
                    'events' => $results
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
}
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
        $endTime = Carbon::parse($eventData['end_time']);
        $eventData['status'] = $endTime->isPast() ? 'completed' : 'upcoming';

        if ($eventData['status'] === 'completed') {
            $eventData['reminder_time'] = null;
            $eventData['participants'] = null;
        } else {
            if (isset($eventData['reminder_time'])) {
                $reminderTime = Carbon::parse($eventData['reminder_time']);
                
                if ($reminderTime->isPast()) {
                    $eventData['reminder_time'] = null;
                    $eventData['participants'] = null;
                } else {
                    if (isset($eventData['participants'])) {
                        $eventData['participants'] = array_values(array_unique(
                            array_map('trim', (array) $eventData['participants'])
                        ));
                    }
                }
            } else {
                $eventData['participants'] = null;
            }
        }

        $existingEvent = $this->eventRepository->findByClientId($eventData['client_id']);

        if (!$existingEvent) {
            $eventData['event_id'] = $this->idGenerationService->generate();
            $event = $this->eventRepository->create($eventData);
            EventCreated::dispatch($event);
            return $event;
        }

        // Last write wins - if incoming event is newer or same time, it overwrites
        if (strtotime($eventData['updated_at']) >= strtotime($existingEvent->updated_at)) {
            $event = $this->eventRepository->update($existingEvent, $eventData);
            EventCreated::dispatch($event);
            return $event;
        }

        return $existingEvent;
    }
}
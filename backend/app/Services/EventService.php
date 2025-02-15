<?php

namespace App\Services;

use App\Repositories\EventRepository;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Collection;

class EventService
{
    public function __construct(
        protected EventRepository $eventRepository,
        protected IdGenerationService $idGenerationService
    ) {
    }

    public function createEvent(array $data)
    {
        $data['client_id'] = (string) Str::uuid();
        $data['event_id'] = $this->idGenerationService->generate();
        
        return $this->eventRepository->create($data);
    }

    public function updateEvent(string $eventId, array $data)
    {
        $event = $this->eventRepository->findByEventId($eventId);
        if (!$event) {
            throw new ModelNotFoundException("Event with ID {$eventId} not found");
        }
        return $this->eventRepository->update($event, $data);
    }

    public function deleteEvent(string $eventId): void
    {
        $event = $this->eventRepository->findByEventId($eventId);
        if (!$event) {
            throw new ModelNotFoundException("Event with ID {$eventId} not found");
        }
        $this->eventRepository->delete($event);
    }

    public function getEvent(string $eventId)
    {
        $event = $this->eventRepository->findByEventId($eventId);
        if (!$event) {
            throw new ModelNotFoundException("Event with ID {$eventId} not found");
        }
        return $event;
    }

    public function getAllEvents(): Collection
    {
        return $this->eventRepository->getAllEventsSorted();
    }
}
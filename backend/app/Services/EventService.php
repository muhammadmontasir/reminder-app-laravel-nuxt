<?php

namespace App\Services;

use App\Repositories\EventRepository;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use App\Mail\EventReminder;
use App\Events\EventCreated;
use Illuminate\Support\Facades\Mail;

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
        
        $endTime = Carbon::parse($data['end_time']);
        $data['status'] = $endTime->isPast() ? 'completed' : 'upcoming';
        
        $event = $this->eventRepository->create($data);
        EventCreated::dispatch($event);
        
        return $event;
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

    public function processReminders(): void
    {
        $now = Carbon::now();
        $events = $this->eventRepository->getUpcomingReminders($now);

        foreach ($events as $event) {
            foreach ($event->participants as $participant) {
                Mail::to($participant)->queue(new EventReminder($event));
            }
            
            $this->eventRepository->markReminderSent($event);
        }
    }

    public function getEventsByParticipant(string $email): Collection
    {
        return $this->eventRepository->findEventsForParticipant($email);
    }
}
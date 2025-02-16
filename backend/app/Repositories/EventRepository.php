<?php

namespace App\Repositories;

use App\Models\Event;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class EventRepository
{
    public function __construct(protected Event $model)
    {
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(Event $event, array $data): Event
    {
        $event->update($data);
        return $event->fresh();
    }

    public function delete(Event $event): void
    {
        $event->delete();
    }

    public function findByClientId(string $clientId)
    {
        return $this->model->where('client_id', $clientId)->first();
    }

    public function findByClientIdAndEventId(string $clientId, string $eventId)
    {
        return $this->model->where([
            'client_id' => $clientId,
            'event_id' => $eventId
        ])->first();
    }

    public function findByEventId(string $eventId): ?Event
    {
        return Event::where('event_id', $eventId)->first();
    }

    public function getAllEventsSorted(): Collection
    {
        return Event::orderBy('start_time', 'desc')
            ->orderBy('end_time', 'desc')
            ->get();
    }

    public function getUpcomingReminders(Carbon $now): Collection
    {
        return $this->model
            ->whereNotNull('reminder_time')
            ->where('reminder_time', '<=', $now)
            ->where('status', 'upcoming')
            ->get();
    }

    public function markReminderSent(Event $event): void
    {
        $event->update([
            'reminder_time' => null
        ]);
    }

    public function findEventsForParticipant(string $email): Collection
    {
        return $this->model
            ->whereJsonContains('participants', $email)
            ->orderBy('start_time', 'desc')
            ->get();
    }
}
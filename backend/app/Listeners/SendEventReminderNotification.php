<?php

namespace App\Listeners;

use App\Events\EventCreated;
use App\Jobs\SendEventReminder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class SendEventReminderNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 300, 600]; // 1 minute, 5 minutes, 10 minutes
    }

    /**
     * Handle the event.
     */
    public function handle(EventCreated $event): void
    {
        if (!$event?->event) {
            \Log::warning('Event data is missing');
            return;
        }

        $participants = $event->event->participants;
        if (!is_array($participants)) {
            $participants = json_decode($event->event->participants, true);
        }

        if (empty($event->event->reminder_time) || empty($participants)) {
            \Log::info('No reminder needed - missing reminder time or participants', [
                'event_id' => $event->event->event_id ?? null,
                'reminder_time' => $event->event->reminder_time ?? null,
                'participants' => $participants ?? null
            ]);
            return;
        }

        $event->event->participants = $participants;

        SendEventReminder::dispatch($event->event)->delay($event->event->reminder_time);
    }

    /**
     * Handle a job failure.
     */
    public function failed(EventCreated $event, Throwable $exception): void
    {
        \Log::error('Failed to schedule event reminders', [
            'event_id' => $event->event->event_id ?? null,
            'reminder_time' => $event->event->reminder_time ?? null,
            'participants' => $event->event->participants ?? null,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
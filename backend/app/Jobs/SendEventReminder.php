<?php

namespace App\Jobs;

use App\Models\Event;
use App\Mail\EventReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEventReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Event $event
    ) {}

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 300, 600]; // 1 minute, 5 minutes, 10 minutes
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->event->participants) {
                Log::info('No participants for event reminder', ['event_id' => $this->event->event_id]);
                return;
            }

            foreach ($this->event->participants as $participant) {
                Mail::to($participant)->send(new EventReminder($this->event));
            }

            Log::info('Event reminder sent successfully', [
                'event_id' => $this->event->event_id,
                'participants' => $this->event->participants
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send event reminder', [
                'event_id' => $this->event->event_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
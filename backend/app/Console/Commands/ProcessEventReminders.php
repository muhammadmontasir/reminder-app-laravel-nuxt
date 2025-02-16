<?php

namespace App\Console\Commands;

use App\Services\EventService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessEventReminders extends Command
{
    protected $signature = 'events:process-reminders';
    protected $description = 'Process pending event reminders';

    public function __construct(
        protected EventService $eventService
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            $this->info('Processing event reminders...');
            $this->eventService->processReminders();
            $this->info('Event reminders processed successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to process event reminders', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error('Failed to process event reminders: ' . $e->getMessage());
        }
    }
} 
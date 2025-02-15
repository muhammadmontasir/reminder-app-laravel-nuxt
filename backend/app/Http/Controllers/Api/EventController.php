<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest;
use App\Services\EventService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    use ApiResponse;

    public function __construct(protected EventService $eventService)
    {
    }

    public function index(): JsonResponse
    {
        $events = $this->eventService->getAllEvents();
        return $this->successResponse(
            $events,
            'Events retrieved successfully'
        );
    }

    public function store(EventRequest $request): JsonResponse
    {
        $event = $this->eventService->createEvent($request->validated());
        return response()->json($event, 201);
    }

    public function show(string $eventId): JsonResponse
    {
        try {
            $event = $this->eventService->getEvent($eventId);
            return $this->successResponse($event, 'Event retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Event not found', Response::HTTP_NOT_FOUND);
        }
    }

    public function update(EventRequest $request, string $eventId): JsonResponse
    {
        try {
            $event = $this->eventService->updateEvent($eventId, $request->validated());
            return $this->successResponse($event, 'Event updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Event not found', Response::HTTP_NOT_FOUND);
        }
    }

    public function destroy(string $eventId): JsonResponse
    {
        try {
            $this->eventService->deleteEvent($eventId);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Event not found', Response::HTTP_NOT_FOUND);
        }
    }
}
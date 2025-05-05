<?php

namespace App\Http\Controllers;

use App\Mail\EventCreatedConfirmation;
use App\Models\Event;
use App\Models\User;
use App\Notifications\EventNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::all();
        return view('events/event', compact('events'));
    }

    // Create an Event and Notify Users
    public function createEvent(Request $request)
    {
        $validated = $request->validate([
            'user_id'=>'exists:users,id',
            'eventClass' => 'required',
            'level' => 'required',
            'category' => 'required',
            'subject' => 'nullable',
            'name' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'description' => 'required|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'hosts' => 'required|string',
            'sponsors' => 'nullable|string',
            'capacity' => 'required|integer|min:1'
        ]);

        try {
            $authUser = Auth::user();

            Log::info('Auth ID when creating event: ', ['user_id' => $authUser->id]);

           // Create the event and link it to the authenticated user
            $event = Event::create(array_merge($validated, ['user_id' => $authUser->id]));

            // Notify all users
            $users = User::all();

            foreach ($users as $user) {
                $user->notify(new EventNotification($event));
            }

           Mail::to($authUser->email)->send(new EventCreatedConfirmation($event));

            return response()->json(['message' => 'Event created and notifications sent!'], 201);
        } catch (\Exception $e) {
            Log::error('Event creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to create event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getEvents()
    {
        $events = Event::all();
        if ($events) {
            return response()->json($events);
        } else {
            return response("No event was found");
        }
    }

    public function getEvent($id)
    {
        try {
            $event = Event::findOrFail($id);
            return response()->json($event);
        } catch (\Exception $e) {
            return response()->json([
                "error" => "Event was not found with id: " . $id
            ], 404);
        }
    }

    public function editEvent(Request $request, $id)
    {
        $request->validate([
            'user_id'=>'required|exists:users,id',
            'eventClass' => 'required',
            'level' => 'required',
            'category' => 'required',
            'subject' => 'nullable',
            'name' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'description' => 'required|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'hosts' => 'required|string',
            'sponsors' => 'nullable|string',
            'capacity' => 'required|integer',
        ]);

        try {
            $existingEvent = Event::findOrFail($id);

            // Update event details
            $existingEvent->update($request->all());

            return response()->json($existingEvent);
        } catch (\Exception $e) {
            return response()->json([
                "error" => "Event could not be updated"
            ], 404);
        }
    }

    public function deleteEvent($id)
    {
        try {
            $existingEvent = Event::findOrFail($id);
            $existingEvent->delete();

            return response()->json([
                "deleted" => $existingEvent
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "error" => "Event could not be deleted!"
            ], 403);
        }
    }
}

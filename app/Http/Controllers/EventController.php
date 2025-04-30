<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Notifications\EventNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


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
            'user_id'=>'required|exists:users,id',
            'class' => 'required',
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
            Log::info('Auth ID when creating event: ', ['user_id' => Auth::id()]);

           // Create the event and link it to the authenticated user
            $event = Event::create(array_merge($validated, ['user_id' => Auth::id()]));

            // Find users who are interested in this event's category (no longer using classifications table)
            $users = User::all();

            // whereHas('preferences', function ($query) use ($event) {
            //     $query->where('category', $event->category);  // Adjust according to the correct attribute
            // })->get();

            // // // Send notifications to the relevant users
            foreach ($users as $user) {
                $user->notify(new EventNotification($event));
            }

            return response()->json(['message' => 'Event created and notifications sent!'], 201);
        } catch (\Exception $e) {
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
            'class' => 'required',
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

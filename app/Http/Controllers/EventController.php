<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventResource;
use App\Mail\EventCreatedConfirmation;
use App\Models\Event;
use App\Models\User;
use App\Notifications\EventNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EventController extends Controller
{
    public function index()
    {
        return EventResource::collection(Event::all());
    }

    // Create an Event and Notify Users
    public function createEvent(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'exists:users,id',
            'eventClass' => 'required',
            'level' => 'required',
            'category' => 'required',
            'subject' => 'nullable',
            'name' => 'required|string|max:255',
            'poster' => 'image|mimes:jpeg,png,jpg|max:2048',
            'participation_mode' => 'required|string|max:255',
            'link' => 'nullable|string|max:255',
            'venue' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
            'description' => 'required|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'hosts' => 'required|string',
            'sponsors' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'registration_fee' => 'required|integer',
            'currency' => 'required',
            'contact_number' => 'required|integer',
        ]);

        try {
            $authUser = Auth::user();
            if ($request->hasFile('poster')) {
                $filename = $request->file('poster')->store('events', 'public');
            } else {
                $filename = Null;
            }
            $validated['poster'] = $filename;

            // Create the event and link it to the authenticated user
            $event = Event::create(array_merge($validated, ['user_id' => $authUser->id]));

            // Notify all users
            $users = User::all();

            foreach ($users as $user) {
                $user->notify(new EventNotification($event));
            }

            //Email for new event
            foreach ($users as $user) {
                Mail::to($user->email)->send(new EventCreatedConfirmation($event));
            }

            return response()->json(['message' => 'Event created and notifications sent!'], 201);
        } catch (\Exception $e) {
            Log::error('Event creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to create event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getEvent($id)
    {
        try {
            $event = Event::findOrFail($id);
            return new EventResource($event);
        } catch (\Exception $e) {
            return response()->json([
                "error" => "Event was not found with id: " . $id
            ], 404);
        }
    }

    public function getUpcomingPaidEvents()
    {
        $user = Auth::user();

        $events = $user->paidEvents()
            ->where('startDate', '>', now())
            ->get();

        return EventResource::collection($events);
    }

    public function getPaidEvents()
    {
        $user = Auth::user();

        $events = $user->paidEvents()->get();

        return EventResource::collection($events);
    }

    public function editEvent(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'exists:users,id',
            'eventClass' => 'required',
            'level' => 'required',
            'category' => 'required',
            'subject' => 'nullable',
            'name' => 'required|string|max:255',
            'poster' => 'image|mimes:jpeg,png,jpg|max:2048',
            'participation_mode' => 'required|string|max:255',
            'link' => 'nullable|string|max:255',
            'venue' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
            'description' => 'required|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'hosts' => 'required|string',
            'sponsors' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'registration_fee' => 'required|integer',
            'currency' => 'required',
            'contact_number' => 'required|integer',
        ]);

        try {
            $existingEvent = Event::findOrFail($id);

            if ($request->hasFile('poster')) {
                $filename = $request->file('poster')->store('events', 'public');
            } else {
                $filename = Null;
            }
            $existingEvent->poster = $filename;

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
        $eventToDelete = Event::findorFail($id);

        if ($eventToDelete) {
            try {
                $eventToDelete = Event::destroy($id);
                return "Event deleted successfully";
            } catch (Exception $e) {
                return response()->json([
                    "Error" => "Failed to delete event",
                    "Message" => $e->getMessage()
                ], 500);
            }
        } else {
            return "Event not found";
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    public function likeEvent($id)
    {
        try {
            $user = Auth::user();

            $event = Event::findOrFail($id);

            if($user->likedEvents()->where('event_id', $event->id)->exists()){
                return response()->json([
                    'message'=>'Already liked'
                ], 409);
            };

            $user->likedEvents()->attach($event->id);

            Log::info('Liking event ID: ' . $event->id . ' for user ID: ' . $user->id);


            return response()->json([
                'message' => 'Event liked successfully',
            ], 201);

        } catch (\Exception $e) {
            print_r($e);
            return response()->json([
                'error' => 'Could not like the event'
            ], 500);
        }
    }

    public function getLikedEvents(){
        $user = Auth::user();

        try {
            $likedEvents = $user->likedEvents()->get()->map(function ($event){
                $event -> isLiked = true;
                return $event;
            });
            return response()->json($likedEvents);
        } catch(Exception $e) {
            print_r($e);
            return response()->json([
                'error'=>'No available liked events'.$e
            ]);
        }

    }

    public function unLikeEvent($id){
        try{
            $user = Auth::user();

            $event = Event::findOrFail($id);

            $user->likedEvents()->detach($event->id);
            return response()->json([
                'message'=>'Event unliked successfully',
            ]);
        }
        catch (Exception $e){
            return response()->json([
                'error'=>'Error unliking event: ' . $e,
            ]);
        }
    }
}

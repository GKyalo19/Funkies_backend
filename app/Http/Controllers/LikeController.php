<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Like;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function likeEvent(Event $event)
    {
        try {
            $user = Auth::user();

            if($user->likedEvents()->where('event_id', $event->id)->exists()){
                return response()->json([
                    'message'=>'Already liked'
                ], 409);
            };

            $user->likedEvents()->attach($event->id);

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
            $likedEvents = $user->likedEvents()->get();
            return response()->json($likedEvents);
        } catch(Exception $e) {
            print_r($e);
            return response()->json([
                'error'=>'No available liked events'.$e
            ]);
        }

    }

    public function unLikeEvent(Event $event){
        try{
            $user = Auth::user();

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

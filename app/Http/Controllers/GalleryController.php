<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function createGallery(Request $request)
    {
        $request->validate([
            'event_id'=>'required|exists:events,id',
            'photo'=>'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'video'=>'nullable|mimetypes:video/mp4,video/quicktime,video/x-msvideo|max:102400',
        ]);

        $gallery = new Gallery();
        $gallery->event_id = $request->event_id;
        $gallery->photo = $request->photo;
        $gallery->video = $request->video;

        if($request->hasFile('photo')){
            $filename = $request->file('photo')->store('photos', 'public');
        }
        else {
            $filename = null;
        }

        $gallery->photo =$filename;

        if($request->hasFile('video')){
            $videoPath = $request->file('video')->store('videos', 'public');
        }
        else {
            $videoPath = null;
        }

        $gallery->video = $videoPath;

        $gallery->save();

        $galleryCheck = Gallery::find($gallery->id);

        return response()->json($galleryCheck);
    }

    public function getGalleries()
    {
        $gallery = Gallery::all();
        if($gallery) {
            return response()->json($gallery);
        }
        else {
            return response("No gallery was found");
        }
    }

    public function getGallery($id)
    {
        try {
            $gallery = Gallery::findOrFail($id);
            return response()->json($gallery);
        }
        catch(\Exception $e){
            return response()->json([
                "error"=>"Gallery not found with id: ", $id
            ], 404);
        }
    }

    public function getEventGallery($event_id)
    {
        try {
            // Fetch galleries for the specified event_id
            $galleries = Gallery::where('event_id', $event_id)->get();

            if ($galleries->isEmpty()) {
                return response()->json([
                    "message" => "No galleries found for event_id: $event_id"
                ], 404);
            }

            return response()->json($galleries);
        } catch (\Exception $e) {
            return response()->json([
                "error" => "An error occurred while fetching galleries"
            ], 500);
        }
    }

    public function updateGallery(Request $request, $id)
    {
        $request->validate([
            'event_id'=>'required|exists:events,id',
            'photo'=>'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'video'=>'nullable|mimetypes:video/mp4,video/quicktime,video/x-msvideo|max:102400',
        ]);

        try {
            $existingGallery = Gallery::findOrFail($id);

            if($existingGallery){
                $existingGallery->event_id = $request->event_id;
                $existingGallery->photo = $request->photo;
                $existingGallery->video = $request->video;

                if($request->hasFile('photo')){
                    $filename = $request->file('photo')->store('photos', 'public');
                }
                else {
                    $filename = null;
                }

                $existingGallery->photo =$filename;

                if($request->hasFile('video')){
                    $videoPath = $request->file('video')->store('videos', 'public');
                }
                else {
                    $videoPath = null;
                }

                $existingGallery->video = $videoPath;

                $existingGallery->save();

                return response()->json($existingGallery);
            }
            else{
                return response()->json("No record found with id: ", $id);
            }
        }
        catch(\Exception $id){
            return response()->json([
                "error"=>"Gallery could not be updated"
            ], 404);
        }
    }

    public function deleteGallery($id)
    {
        try {
            $existingGallery = Gallery::findOrFail($id);
            if($existingGallery) {
                $existingGallery->delete();
                return response()->json([
                    "deleted"=>$existingGallery
                ]);
            }
            else {
                return response ("Gallery not found with id: ",$id);
            }
        }
        catch(\Exception $e) {
            return response()->json([
                "error"=>"Gallery could not be deleted"
            ], 403);
        }
    }
}

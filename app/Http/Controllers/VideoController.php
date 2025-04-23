<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Implementation goes here
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    
    {
        
       $user_id = 2;
    
        $validate = $request->validate([
            'title' => 'required|string',
            'video_url' => 'required|file',
            'description' => 'required|string',
            'thumbnail_url' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);
     
        if($request->hasFile('video_url')) {
            $validate['video_url'] = $request->file('video_url')->store('videos', 'public');
        }

        $validate['user_id'] = $user_id;
        if($request->hasFile('thumbnail_url')) {
            $validate['thumbnail_url'] = $request->file('thumbnail_url')->store('thumbnails', 'public');
        }
        $validate['credibility_score'] = 50;
        $validate['duration_seconds'] = 2;
        $validate['user_id']= $user_id;
        $video = Video::create($validate);
        
        return response()->json($video, 201); // or redirect somewhere
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
    $videos = Video::all();
      $videos->transform(function($video){
         $video->video_url = asset('storage/'.$video->video_url);
         $video->thumbnail_url = asset('storage/thumbnails/'.$video->thumbnail_url);
         return $video;
      });
    return$videos;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Video $video)
    {
        // Implementation goes here
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Video $video)
    {
        // Implementation goes here
    }

    public function insert(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $validated = $request->validate([
            'video' => 'required|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm,video/ogg|max:102400', // 100MB max
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 2MB max
        ]);

        try {
            $videoFile = $request->file('video');
            $videoName = time() . '_' . $videoFile->getClientOriginalName();
            $videoPath = $videoFile->storeAs('public/videos', $videoName);

            $thumbnailFile = $request->file('thumbnail');
            $thumbnailName = time() . '_' . $thumbnailFile->getClientOriginalName();
            $thumbnailPath = $thumbnailFile->storeAs('public/thumbnails', $thumbnailName);

            $video = Video::create([
                'user_id' => auth()->id, 
                'title' => $validated['title'],
                'description' => $validated['description'],
                'video_path' => str_replace('public/', '', $videoPath),
                'thumbnail_path' => str_replace('public/', '', $thumbnailPath),
                'original_name' => $videoFile->getClientOriginalName(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Video uploaded successfully',
                'data' => [
                    'id' => $video->id,
                    'title' => $video->title,
                    'video_url' => Storage::url($videoPath),
                    'thumbnail_url' => Storage::url($thumbnailPath),
                ]
            ], 201);

        } catch (\Exception $e) {
            // Delete any uploaded files if the operation failed
            if (isset($videoPath) && Storage::exists($videoPath)) {
                Storage::delete($videoPath);
            }
            if (isset($thumbnailPath) && Storage::exists($thumbnailPath)) {
                Storage::delete($thumbnailPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload video',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
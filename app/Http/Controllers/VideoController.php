<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $videos = Video::latest()->get();
        
        return response()->json([
            'videos' => $videos,
            'storage_url' => url('/storage/videos/') // Base URL for video access
        ]);
        // Implementation goes here
    }

    /**
     * Store a newly created resource in storage.
     */
    /* public function store(Request $request)
    {
        $user_id = Auth::id();
        
        $validate = $request->validate([
            'title' => 'required|string',
            'video_chunk' => 'required|file',
            'upload_id' => 'required|string',
            'chunk_index' => 'required|integer',
            'total_chunks' => 'required|integer',
            'is_last' => 'required|boolean',
            'description' => 'required|string',
            'thumbnail_url' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'allow_likes' => 'required|boolean',
            'allow_comments' => 'required|boolean',
            'allow_shares' => 'required|boolean',
        ]);
    
        if ($request->chunk_index === 0) {
            if ($request->hasFile('thumbnail_url')) {
                $validate['thumbnail_url'] = $request->file('thumbnail_url')->store('thumbnails', 'public');
            }
        }
    
        $uploadId = $validate['upload_id'];
        $chunkIndex = $validate['chunk_index'];
        $isLast = $validate['is_last'];
        
        if (!Storage::disk('local')->exists("temp_videos/{$uploadId}")) {
            Storage::disk('local')->makeDirectory("temp_videos/{$uploadId}");
        }
        
        $chunkPath = Storage::disk('local')->path(
            "temp_videos/{$uploadId}/{$chunkIndex}"
        );
        $request->file('video_chunk')->move(dirname($chunkPath), basename($chunkPath));
        
        if ($isLast) {
            $finalFileName = Str::random(40) . '.mp4'; // Generate random filename
            $finalPath = Storage::disk('public')->path("videos/{$finalFileName}");
            
            $finalFile = fopen($finalPath, 'wb');
            
            for ($i = 0; $i < $validate['total_chunks']; $i++) {
                $chunk = Storage::disk('local')->get("temp_videos/{$uploadId}/{$i}");
                fwrite($finalFile, $chunk);
            }
            
            fclose($finalFile);
            
            // Clean up chunks
            Storage::disk('local')->deleteDirectory("temp_videos/{$uploadId}");
            
            // Create video record
            $video = Video::create([
                'user_id' => $user_id,
                'title' => $validate['title'],
                'description' => $validate['description'],
                'video_url' => "videos/{$finalFileName}",
                'thumbnail_url' => $validate['thumbnail_url'],
                'credibility_score' => 50,
                'duration_seconds' => 0, // Set to 0 since we're not calculating duration
                'allow_likes' => $validate['allow_likes'],
                'allow_comments' => $validate['allow_comments'],
                'allow_shares' => $validate['allow_shares'],
            ]);
            
            return response()->json($video, 201);
        }
        
        return response()->json(['message' => 'Chunk uploaded successfully'], 200);
    } */
 
        public function deleteFile(Request $request)
        {
            $relativePath = $request->input('file_path');  // e.g., 'uploads/{location}/{filename}'
    
            $filePath = 'app/' . $relativePath;
    
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
                return response()->json(['message' => 'File deleted successfully!'], 200);
            }
    
            return response()->json(['message' => 'File not found.'], 404);
        }
    
        public function uploadChunk(Request $request)
        {
            // Validate required fields
            $request->validate([
                'video_chunk' => 'required|file',
                'title' => 'required|string',
                'chunk' => 'required|integer',
                'totalChunks' => 'required|integer',
                'location' => 'required|string',
            ]);
    
            // Check if file chunk exists
            if (!$request->hasFile('video_chunk')) {
                return response()->json(['message' => 'No video chunk uploaded'], 400);
            }
    
            $chunk = $request->file('video_chunk');
            $title = $request->input('title');
            $thumbnail = $request->file('thumbnail_url');
            $description = $request->input('description');
            $chunkIndex = $request->input('chunk');
            $totalChunks = $request->input('totalChunks');
            
            // Sanitize location
            $location = $request->input('location');
            $location = str_replace(['..', '/'], '', $location);
            
            $tempDir = storage_path('app/temp_chunks/' . $title);
            $finalDir = storage_path('app/public/uploads/' . $location);
            $finalPath = $finalDir . '/' . $title;
    
            // Handle thumbnail only for first chunk
            $thumbnailUrl = null;
            if ($thumbnail && $chunkIndex == 0) {
                $thumbnailPath = 'thumbnails/' . time() . '_' . $thumbnail->getClientOriginalName();
                $thumbnail->storeAs('public', $thumbnailPath);
                $thumbnailUrl = $thumbnailPath;
            }
    
            // Create temp directory if it doesn't exist
            if (!file_exists($tempDir)) {
                if (!mkdir($tempDir, 0777, true)) {
                    return response()->json(['message' => 'Failed to create temp directory'], 500);
                }
            }
    
            try {
                // Save the chunk
                $chunk->move($tempDir, $chunkIndex);
    
                // Check if all chunks have been uploaded
                $files = scandir($tempDir);
                if (count($files) - 2 === (int) $totalChunks) {
                    // Create final directory if it doesn't exist
                    if (!file_exists($finalDir)) {
                        if (!mkdir($finalDir, 0777, true)) {
                            return response()->json(['message' => 'Failed to create uploads directory'], 500);
                        }
                    }
    
                    // Combine chunks into final file
                    $output = fopen($finalPath, 'wb');
                    if (!$output) {
                        return response()->json(['message' => 'Failed to open final file path'], 500);
                    }
    
                    for ($i = 0; $i < $totalChunks; $i++) {
                        $chunkPath = $tempDir . '/' . $i;
                        if (!file_exists($chunkPath)) {
                            return response()->json(['message' => 'Missing chunk file'], 500);
                        }
                        
                        $chunkFile = fopen($chunkPath, 'rb');
                        if ($chunkFile) {
                            stream_copy_to_stream($chunkFile, $output);
                            fclose($chunkFile);
                        } else {
                            fclose($output);
                            return response()->json(['message' => 'Failed to open chunk file'], 500);
                        }
                    }
    
                    fclose($output);
    
                    // Create video record
                    $video = new Video();
                    $video->user_id = Auth::id();
                    $video->title = $title;
                    $video->description = $description ?? '';
                    $video->video_url = 'uploads/' . $location . '/' . $title;
                    
                    if ($thumbnailUrl) {
                        $video->thumbnail_url = $thumbnailUrl;
                    }
                    
                    $video->credibility_score = 50;
                    $video->duration_seconds = 0;
                    $video->allow_likes = $request->input('allow_likes', true);
                    $video->allow_comments = $request->input('allow_comments', true);
                    $video->allow_shares = $request->input('allow_shares', true);
                    $video->save();
    
                    // Clean up temp files
                    array_map('unlink', glob("$tempDir/*"));
                    rmdir($tempDir);
                    
                    return response()->json([
                        'message' => 'File uploaded successfully!',
                        'file_path' => 'uploads/' . $location . '/' . $title,
                        'video' => $video
                    ], 200);
                }
    
                return response()->json(['message' => 'Chunk uploaded successfully.']);
    
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Upload failed',
                    'error' => $e->getMessage()
                ], 500);
            }
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
        try {
            // Delete the physical file
            Storage::delete($video->path);
            
            // Delete the database record
            $video->delete();

            return response()->json([
                'message' => 'Video deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Video deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }    }

    public function insert(Request $request)
    {
     
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{

    private $supabaseUrl;
    private $apiKey;
    private $bucketName;

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->apiKey = env('SUPABASE_APIKEY');
        $this->bucketName = env('SUPABASE_BUCKET');
    }

    public function courseList() {

        try {
            $result = Course::orderBy(
                "id"
            )->select([
                'name',
                'description',
                'thumbnail',
                'lesson_length',
                'video_length',
                'price',
                'follow',
                'id'
            ])->get();

            return response()->json([
                'code' => 200,
                'msg' => "Successfully getting response",
                'data' => $result,
            ], 200);
        }
        catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'msg' => $th->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    public function courseListAdmin() {

        try {
            $result = Course::orderBy(
                "id"
            )->select([
                'id',
                'name',
                'type_id',
                'description',
                'thumbnail',
                'lesson_length',
                'video_length',
                'price',
                'follow',
                'score',
                'downloadable_resources',
                'created_at',
                'updated_at',
            ])->with('type')->get();

            return response()->json([
                'code' => 200,
                'msg' => "Successfully getting response",
                'data' => $result,
            ], 200);
        }
        catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'msg' => $th->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function courseCreateAdmin(Request $request) {

        try {
            $validateField = Validator::make($request->all(),
            [
                'name' => 'required',
                'thumbnail' => 'required',
                'type_id' => 'required',
                'price' => 'required',
                'lesson_length' => 'required',
                'video_length' => 'required',
            ]);

            if ($validateField->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateField->errors(),
                ], 401);
            }

            $thumbnail = $request->thumbnail;
            $imageName = uniqid().$thumbnail->getClientOriginalName();

            $responseImage = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
                ->attach('file', $thumbnail->get(), $imageName)
                ->post(
                    "{$this->supabaseUrl}/storage/v1/object/{$this->bucketName}/thumbnail/{$imageName}"
            );
            
            if (!$responseImage->successful()) {
                throw new \Exception("Something went wrong... Try again later!");
            }

            $video = $request->video;
            $videoName = uniqid().$video->getClientOriginalName();

            $responseVideo = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
                ->attach('file', $video->get(), $video->getClientOriginalName())
                ->post("{$this->supabaseUrl}/storage/v1/object/{$this->bucketName}/video/{$videoName}"
            );

            if (!$responseVideo->successful()) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])
                    ->delete(
                        "{$this->supabaseUrl}/storage/v1/object/{$this->bucketName}/thumbnail/{$imageName}"
                );
                throw new \Exception("Something went wrong... Try again later!");
            }

            $response = Course::create([
                'name' => $request->name,
                'type_id' => $request->type_id,
                'price' => $request->price,
                'lesson_length' => $request->lesson_length,
                'video_length' => $request->video_length,
                'thumbnail' => "{$this->supabaseUrl}/storage/v1/object/{$this->bucketName}/thumbnail/{$imageName}",
                'video' => "{$this->supabaseUrl}/storage/v1/object/{$this->bucketName}/video/{$videoName}",
                'downloadable_resources' => $request->downloadable_resources,
            ]);

            return response()->json([
                'code' => 200,
                'msg' => "Successfully adding new data",
                'data' => $response,
            ], 200);
        }
        catch (\Throwable $th) {
            Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                ])
                ->delete(
                    "{$this->supabaseUrl}/storage/v1/object/{$this->bucketName}/thumbnail/{$imageName}"
                );

            Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
                ->delete(
                    "{$this->supabaseUrl}/storage/v1/object/{$this->bucketName}/video/{$videoName}"
            );

            return response()->json([
                'code' => 500,
                'msg' => $th->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    
    public function courseDetail(Request $request) {
        $id = $request->id;
        try {
            $result = Course::where('id', '=', $id)->select([
                'id',
                'name',
                'description',
                'thumbnail',
                'lesson_length',
                'video_length',
                'downloadable_resources',
                'score',
                'price',
                'follow',
                'created_at',
            ])->get();

            return response()->json([
                'code' => 200,
                'msg' => "Successfully getting response",
                'data' => $result,
            ], 200);
        }
        catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'msg' => $th->getMessage(),
                'data' => null,
            ], 200);
        }


    }
}

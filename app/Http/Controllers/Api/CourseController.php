<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Models\CourseType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CourseController extends Controller
{
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

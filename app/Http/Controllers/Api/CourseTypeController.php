<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Models\CourseType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CourseTypeController extends Controller
{
    public function courseTypeListAdmin() {

        try {
            $result = CourseType::select([
                'id',
                'title',
                'description',
                'created_at',
                'updated_at',                
            ])->withCount([
                'courses as count',
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
}

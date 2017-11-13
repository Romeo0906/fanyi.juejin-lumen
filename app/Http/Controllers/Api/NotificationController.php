<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * 获取所有通知
     * @return \Illuminate\Http\JsonResponse
     */
    public function index ()
    {
        $applicants = DB::table("applicant")
                        ->select("id", "cdate")
                        ->where("status", 0)
                        ->get();

        $recommends = DB::table("recommend")
                        ->join("user", "recommend.recommender", "=", "user.id")
                        ->select("user.avatar", "recommend.id", "recommend.cdate")
            ->where("status", 0)
                        ->get();

        return response()->json([
            "applicants"    => $applicants,
            "recommends"    => $recommends,
            "total"         => count($applicants) + count($recommends)
        ], 200);
    }
}
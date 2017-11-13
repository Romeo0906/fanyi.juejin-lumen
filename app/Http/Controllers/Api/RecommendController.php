<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Service\Mail;
use App\Http\Controllers\Service\Tool;
use Illuminate\Support\Facades\DB;

class RecommendController extends Controller
{
    protected $status = array(
        "created"   => 0,
        "success"   => 1,
        "failure"   => -1
    );

    /**
     * 获取推荐文章列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function index ()
    {
        if ($this->request->has("status") && in_array(intval($this->request->input("status")), $this->status)) {
            $status = $this->request->input("status");
        } else {
            $status = 0;
        }

        return response()->json(
            DB::table("recommend")
                ->where("status", $status)
                ->orderBy("id", "ASC")
                ->skip($this->start)
                ->take($this->offset)
                ->get()
        , 200);
    }

    /**
     * 添加推荐文章
     * @return \Illuminate\Http\JsonResponse
     */
    public function store ()
    {
        if ($mandatory = $this->isNull(["category", "title", "url", "recommender"])) {
            return response()->json(["message" => $mandatory . " 为空！"], 400);
        }

        if ($unique = $this->isDuplicated("recommend", ["url"])) {
            return response()->json(["message" => $unique . " 重复！"], 400);
        }

        if (
        $id = DB::table("recommend")->insertGetId([
            "category" => $this->request->input("category"),
            "title" => $this->request->input("title"),
            "url" => $this->request->input("url"),
            "recommender" => $this->request->input("recommender"),
            "description" => $this->request->input("description"),
            "status" => $this->status["created"],
            "udate" => date("Y-m-d H:i:s"),
            "cdate" => date("Y-m-d H:i:s")
        ])
        ) {
            return $this->show($id);
        } else {
            return response("Not Implemented", 501);
        }
    }

    /**
     * 获取指定推荐文章详情
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show (int $id)
    {
        return response()->json(
            DB::table("recommend")->where("id", $id)->first()
        , 200);
    }

    /**
     * 更新推荐文章结果
     * @param Mail $mail
     * @param int $id
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function update (Mail $mail, int $id)
    {
        if ($this->request->has("result") && boolval($this->request->input("result"))) {
            $status = $this->status["success"];
        } else {
            $status = $this->status["failure"];
        }

        if (
            DB::table("recommend")->where("id", $id)->update([
                "comment"       => $this->request->input("comment"),
                "status"        => $status
            ])
        ) {
            $this->retrive($id);
            $mail->recommend($id);
            return response("OK", 200);
        } else {
            return response("Not Implemented", 501);
        }
    }

    /**
     * 抓取推荐文章
     * @param int $id
     */
    public function retrive(int $id)
    {
        $url = '127.0.0.1:' . env('GITHUB_MICRO_SERVER_PORT') . '/articles';
        Tool::sendRequest($url, 'POST',
            (array) DB::table('recommend')
            ->join('category', 'recommend.category', '=', 'category.id')
            ->select('recommend.id as rid', 'recommend.url', 'category.category')
            ->where('recommend.id', $id)
            ->first()
        );
    }
}
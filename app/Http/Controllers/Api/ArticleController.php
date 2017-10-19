<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ArticleController extends Controller
{
    /**
     * 获取试译文章列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function index ()
    {
        return response()->json(
                    DB::table("article")->skip($this->start)->take($this->offset)->get()
                , 200);
    }

    /**
     * 添加试译文章
     * @return \Illuminate\Http\JsonResponse
     */
    public function store ()
    {
        if ($mandatory = $this->isNull(["category", "title", "content"])) {
            return response()->json(["message" => $mandatory . " 为空！"], 400);
        }

        return $this->show(
                DB::table("article")->insertGetId([
                    "category"  => $this->request->input("category"),
                    "title"     => $this->request->input("title"),
                    "url"       => $this->request->input("url"),
                    "content"   => $this->request->input("content"),
                    "udate"     => date("Y-m-d H:i:s"),
                    "cdate"     => date("Y-m-d H:i:s")
                ])
            );
    }

    /**
     * 获取指定试译文章详情
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show (int $id)
    {
        return response()->json(
            DB::table("article")->where("id", $id)->first()
        , 200);
    }

    /**
     * 更新试译文章
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update (int $id)
    {
        if ($mandatory = $this->isNull(["category", "title", "content"])) {
            return response()->json(["message" => $mandatory . " 为空！"], 400);
        }

        return $this->show(
            DB::table("article")->where("id", $id)->update([
                "category"  => $this->request->input("category"),
                "title"     => $this->request->input("title"),
                "url"       => $this->request->input("url"),
                "content"   => $this->request->input("content")
            ])
        );
    }

    /**
     * 删除试译文章
     * @param int $id
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function destory (int $id)
    {
        if (DB::table("article")->where("id", $id)->delete()) {
            return response("OK", 200);
        } else {
            return response("Internal Server Error", 500);
        }
    }

    /**
     * 随机获取指定类别的试译文章
     * @param int $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function random (int $category)
    {
        return response()->json(
            DB::table("article")->where("category", $category)->inRandomOrder()->first()
        , 200);
    }
}
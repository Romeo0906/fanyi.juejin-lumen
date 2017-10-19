<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    /**
     * 获取文章类别列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function index ()
    {
        return response()->json(
                    DB::table("category")->get()
                , 200);
    }

    /**
     * 添加文章类别
     * @return \Illuminate\Http\JsonResponse
     */
    public function store ()
    {
        if ($mandatory = $this->isNull(["category"])) {
            return response()->json(["message" => $mandatory . " 为空！"], 400);
        }

        if ($unique = $this->isDuplicated("category", ["category"])) {
            return response()->json(["message" => $unique . " 重复！"], 400);
        }

        return $this->show(
                DB::table("category")->insertGetId([
                    "category"  => $this->request->input("category"),
                    "udate"     => date("Y-m-d H:i:s"),
                    "cdate"     => date("Y-m-d H:i:s")
                ])
            );
    }

    /**
     * 获取指定文章类别详情
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show (int $id)
    {
        return response()->json(
            DB::table("category")->where("id", $id)->first()
        , 200);
    }

    /**
     * 更新文章类别
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update (int $id)
    {
        if ($mandatory = $this->isNull(["category"])) {
            return response()->json(["message" => $mandatory . " 为空！"], 400);
        }

        if ($unique = $this->isDuplicated("category", ["category"], $id)) {
            return response()->json(["message" => $unique . " 重复！"], 400);
        }

        DB::table("category")->where("id", $id)->update([
            "category"  => $this->request->input("category")
        ]);

        return $this->show($id);
    }

    /**
     * 删除文章类别
     * @param int $id
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function destory (int $id)
    {
        if (DB::table("category")->where("id", $id)->delete()) {
            return response("OK", 200);
        } else {
            return response("Internal Server Error", 500);
        }
    }
}
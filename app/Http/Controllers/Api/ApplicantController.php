<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Service\Mail;
use Illuminate\Support\Facades\DB;

/**
 * Class ApplicantController
 * @package App\Http\Controllers\Api
 */
class ApplicantController extends Controller
{
    //
    protected $status = array(
        "created" => 0,
        "success" => 1,
        "failure" => -1
    );

    /**
     * 获取申请人列表
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
                    DB::table("applicant")
                    ->join("category", "applicant.major", "=", "category.id")
                    ->select("applicant.*", "category.category as major")
                    ->where("applicant.status", $status)
                    ->orderBy("applicant.id", "ASC")
                    ->skip($this->start)
                    ->take($this->offset)
                    ->get()
                , 200);
    }

    /**
     * 添加申请记录
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function store ()
    {
        if ($mandatory = $this->isNull(["email", "major", "article", "translation"])) {
            return response()->json(["message" => $mandatory . " 为空！"], 400);
        }

        if ($unique = $this->isDuplicated("applicant", ["email"])) {
            return response()->json(["message" => $unique . " 重复！"], 400);
        }

        if (
            DB::table("applicant")->insert([
                "email"         => $this->request->input("email"),
                "major"         => $this->request->input("major"),
                "description"   => $this->request->input("description"),
                "translation"   => $this->request->input("translation"),
                "article"       => $this->request->input("article"),
                "status"        => $this->status["created"],
                "udate"         => date("Y-m-d H:i:s"),
                "cdate"         => date("Y-m-d H:i:s")
            ])
        ) {
            return response("OK", 200);
        } else {
            return response("Not Implemented", 501);
        }
    }

    /**
     * 更新申请结果
     * @param Mail $mail
     * @param int $id
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function update (Mail $mail, int $id)
    {
        if ($this->request->has("result") && boolval($this->request->input("result"))) {
            $status = $this->status["success"];
            $invitation = md5(time() + rand(1000, 9999));
        } else {
            $status = $this->status["failure"];
            $invitation = "";
        }

        if (
            DB::table("applicant")->where("id", $id)->update([
                "comment"       => $this->request->input("comment"),
                "status"        => $status,
                "invitation"    => $invitation
            ])
        ) {
            $mail->applicant($id);
            return response("OK", 200);
        } else {
            return response("Not Implemented", 501);
        }
    }

    /**
     * 激活账户
     * @param int $user
     * @param string $invitation
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function activate (int $user, string $invitation)
    {
        if ($this->checkInvitation($invitation) === null) {
            return response()->json(["message" => "邀请码有误！"], 400);
        }

        try {
            DB::transaction(function () use ($user, $invitation) {
                DB::table("user")->where("id", $user)->update(["translator" => 1]);
                DB::table("applicant")->where("invitation", $invitation)->update(["invitation" => md5($invitation)]);
            });
            return response("OK", 200);
        } catch (\PDOException $e) {
            return response("Not Implemented", 501);
        }

    }

    /**
     * 验证邀请码
     * @param string $invitation
     * @return mixed
     */
    public function checkInvitation(string $invitation)
    {
        return DB::table("applicant")->where("invitation", $invitation)->first();
    }
}
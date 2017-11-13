<?php
/**
 * Created by PhpStorm.
 * User: romeo
 * Date: 17/10/18
 * Time: 上午6:27
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\SettingController as Setting;
use App\Http\Controllers\Api\StatisticController as Statistic;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Service\Task;
use App\Http\Controllers\Service\Tool;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{
    /**
     * 用户登录
     * @return \Illuminate\View\View
     */
    public function index ()
    {
        $data = json_decode(
            Tool::sendRequest(
                "https://github.com/login/oauth/access_token",
                "POST",
                array(
                    "code" => $this->request->input("code"),
                    "client_id" => env("GITHUB_APP_ID"),
                    "client_secret" => env("GITHUB_APP_KEY")
                ),
                array(
                    "Accept: application/json"
                )
            )
        );

        $userData = json_decode(
            Tool::sendRequest("https://api.github.com/user", "GET", ["access_token" => $data->access_token ?? ""])
        );

        if ($this->isRegistered($userData->id)) {
            $login = $this->update($userData);
        } else {
            $login = $this->store($userData);
        }

        if ($login) {
            $userData->token = Tool::generateToken($userData->id);
            $data = array();
        } else {
            $data = array();
        }

        return view("index", $data);
    }

    /**
     * 添加新用户
     * @param \stdClass $userData
     * @return bool
     */
    public function store(\stdClass $userData)
    {
        try {
            DB::transaction(function () use ($userData) {
                DB::table("user")->insert([
                    "id" => $userData->id,
                    "name" => $userData->login,
                    "email" => $userData->email,
                    "avatar" => $userData->avatar_url,
                    "status" => 1,
                    "advance" => 0,
                    "admin" => 0,
                    "translator" => 0,
                    "udate" => date("Y-m-d H:i:s"),
                    "cdate" => date("Y-m-d H:i:s")
                ]);
                DB::table("userDetail")->insert([
                    "user" => $userData->id,
                    "translated" => 0,
                    "reviewed" => 0,
                    "recommended" => 0,
                    "point" => 0,
                    "redeemed" => 0,
                    "bio" => $userData->bio,
                    "udate" => date("Y-m-d H:i:s"),
                    "cdate" => date("Y-m-d H:i:s")
                ]);
            });
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * 获取指定用户信息
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show (int $id)
    {
        return response()->json(
            DB::table("user")
                ->join("userDetail", "userDetail.user", "=", "user.id")
                ->select("user.name", "user.email", "user.avatar", "userDetail.*")
                ->where("user.id", $id)
                ->first()
            , 200);
    }

    /**
     * 更新用户信息
     * @param \stdClass $userData
     * @return bool
     */
    public function update(\stdClass $userData)
    {
        if (
        DB::table("user")->where("id", $userData->id)->update([
            "name" => $userData->login,
            "email" => $userData->email,
            "avatar" => $userData->avatar_url
        ])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取当前用户信息
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function pull (int $id)
    {
        return response()->json(
            DB::table("user")
                ->join("userDetail", "userDetail.user", "=", "user.id")
                ->select("user.*", "userDetail.bio")
                ->where("user.id", $id)
                ->first()
            , 200);
    }

    /**
     * 初始化用户信息（用户设置，用户统计）
     * @param int $id
     * @param SettingController $setting
     * @param StatisticController $statistic
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function init(int $id, Setting $setting, Statistic $statistic, Task $task)
    {
        return response()->json([
            "setting"   => $setting->init($id),
            "statistic" => $statistic->init($id),
            "task" => $task->init($id)
        ], 200);
    }

    /**
     * 查询用户是否已注册过
     * @param int $id
     * @return mixed
     */
    public function isRegistered (int $id)
    {
        return DB::table("user")->where("id", $id)->first();
    }
}
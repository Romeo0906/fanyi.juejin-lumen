<?php
/**
 * Created by PhpStorm.
 * User: romeo
 * Date: 17/10/18
 * Time: 上午6:27
 */

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Service\Log;
use App\Http\Controllers\Service\Tool;
use App\Http\Controllers\Api\SettingController as Setting;
use App\Http\Controllers\Api\StatisticController as Statistic;


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
                        "code"          => $this->request->input("code"),
                        "client_id"     => env("GITHUB_APP_ID"),
                        "client_secret" => env("GITHUB_APP_KEY")
                    ),
                    array(
                        "Accept"        => "application/json"
                    )
            )
        );

        Log::write(json_encode($data));

        $userData = json_decode(
            Tool::sendRequest("https://api.github.com/user?access_token=" . $data["access_token"])
        );

        Log::write(json_encode($userData));

        if ($this->isRegistered($userData->id)) {
            $this->update($userData);
        } else {
            $this->store($userData);
        }

        $userData->token = Tool::generateToken($userData->id);

        log::write(json_encode($userData));

        return view("index", $userData);
    }

    /**
     * 添加新用户
     * @param object $userData
     * @return mixed
     */
    public function store (object $userData)
    {
        return DB::transaction(function () use ($userData) {
             DB::table("user")->insert([
                "id"        => $userData->id,
                "name"          => $userData->login,
                "email"         => $userData->email,
                "avatar"        => $userData->avatar_url,
                "status"        => 1,
                "advance"       => 0,
                "admin"         => 0,
                "translator"    => 0,
                "udate"         => date("Y-m-d H:i:s"),
                "cdate"         => date("Y-m-d H:i:s")
            ]);
            DB::table("userDetail")->insert([
                "user"          => $userData->id,
                "translated"    => 0,
                "reviewed"      => 0,
                "recommended"   => 0,
                "point"         => 0,
                "redeemed"      => 0,
                "translating"   => null,
                "reviewing"     => null,
                "bio"           => $userData->bio,
                "udate"         => date("Y-m-d H:i:s"),
                "cdate"         => date("Y-m-d H:i:s")
            ]);
        });
    }

    /**
     * 获取指定用户信息
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show (int $id)
    {
        return response()->json(DB::table("user")->join("userDetail")->select(
            "user.name", "user.email", "user.avatar", "userDetail.*"
        )->where("id", $id)->first(), 200);
    }

    /**
     * 更新用户信息
     * @param object $userData
     * @return mixed
     */
    public function update (object $userData)
    {
        return DB::table("user")->where("id", $userData->id)->update([
            "name"      => $userData->login,
            "email"     => $userData->email,
            "avatar"    => $userData->avatar_url
        ]);
    }

    /**
     * 获取当前用户信息
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function pull (int $id)
    {
        return response()->json(DB::table("user")->join("userDetail")->select(
            "user.*", "userDetail.bio", "userDetail.major"
        )->where("id", $id)->first(), 200);
    }

    /**
     * 初始化用户信息（用户设置，用户统计）
     * @param int $id
     * @param SettingController $setting
     * @param StatisticController $statistic
     * @return \Illuminate\Http\JsonResponse
     */
    public function init (int $id, Setting $setting, Statistic $statistic)
    {
        return response()->json([
            "setting"   => $setting->init($id),
            "statistic" => $statistic->init($id)
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
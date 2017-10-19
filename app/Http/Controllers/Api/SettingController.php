<?php
/**
 * Created by PhpStorm.
 * User: romeo
 * Date: 17/10/19
 * Time: 下午9:12
 */

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class SettingController extends Controller
{
    public function init (int $user)
    {
        return DB::table("userSetting")->insert([
            "user"          => $user,
            "tranInform"    => 0,
            "reviInform"    => 0,
            "postInform"    => 0,
            "resuInform"    => 0,
            "udate"         => date("Y-m-d H:i:s"),
            "cdate"         => date("Y-m-d H:i:s")
        ]);
    }

    public function pull (int $user)
    {
        return response()->json(
            DB::table("userSetting")->where("user", $user)->first()
        , 200);
    }

    public function push (int $user)
    {
        return response()->json(
            DB::table("userSetting")->where("user", $user)->update([
                "tranInform"    => $this->request->input("tranInform"),
                "reviInform"    => $this->request->input("reviInform"),
                "postInform"    => $this->request->input("postInform"),
                "resuInform"    => $this->request->input("resuInform")
            ])
        , 200);
    }
}
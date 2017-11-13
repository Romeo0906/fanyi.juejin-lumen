<?php
/**
 * Created by PhpStorm.
 * User: romeo
 * Date: 17/10/19
 * Time: 下午9:12
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


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

    public function show(int $user)
    {
        return response()->json(
            DB::table("userSetting")->where("user", $user)->first()
        , 200);
    }

    public function update(int $user)
    {
        if (
            DB::table("userSetting")->where("user", $user)->update([
                "tranInform" => $this->request->input("newtranslation"),
                "reviInform" => $this->request->input("newreview"),
                "postInform" => $this->request->input("newarticle"),
                "resuInform" => $this->request->input("newresult")
            ])
        ) {
            return response("OK", 200);
        } else {
            return response("Not Implemented", 501);
        }
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: romeo
 * Date: 17/10/19
 * Time: 下午10:30
 */

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class StatisticController extends Controller
{
    public function init (int $user)
    {
        return DB::table("statistics")->insert([
            [
                "user"              => $user,
                "type"              => 1,
                "monthlyTotal"      => 0,
                "yearlyTotal"       => 0,
                "total"             => 0,
                "monthlyRanking"    => 0,
                "yearlyRanking"     => 0,
                "ranking"           => 0,
                "udate"             => date("Y-m-d H:i:s"),
                "cdate"             => date("Y-m-d H:i:s")
            ],
            [
                "user"              => $user,
                "type"              => 2,
                "monthlyTotal"      => 0,
                "yearlyTotal"       => 0,
                "total"             => 0,
                "monthlyRanking"    => 0,
                "yearlyRanking"     => 0,
                "ranking"           => 0,
                "udate"             => date("Y-m-d H:i:s"),
                "cdate"             => date("Y-m-d H:i:s")
            ],
            [
                "user"              => $user,
                "type"              => 3,
                "monthlyTotal"      => 0,
                "yearlyTotal"       => 0,
                "total"             => 0,
                "monthlyRanking"    => 0,
                "yearlyRanking"     => 0,
                "ranking"           => 0,
                "udate"             => date("Y-m-d H:i:s"),
                "cdate"             => date("Y-m-d H:i:s")
            ]
        ]);
    }
}
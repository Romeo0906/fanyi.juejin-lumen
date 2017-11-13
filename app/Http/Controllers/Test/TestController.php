<?php
/**
 * Created by PhpStorm.
 * User: romeo
 * Date: 17/11/1
 * Time: 下午10:20
 */

namespace App\Http\Controllers\Test;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Service\Timeline;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function transaction(Timeline $timeline)
    {
        try {
            DB::transaction(function () use ($timeline) {
                $timeline->write(1, 1, "测试事务");
                DB::table("category")->insert([
                    "id" => -1,
                    "name" => "test"
                ]);
                return "1";
            });
        } catch (\Throwable $e) {
            var_dump($e->getMessage());
        }
    }

    public function test()
    {
        try {
            $arr = array('a', 'b', 'c');
            echo $arr[5];
        } catch (\Throwable $e) {
            var_dump($e->getMessage());
        }
    }
}
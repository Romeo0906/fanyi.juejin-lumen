<?php

namespace App\Http\Controllers\Service;

use Illuminate\Support\Facades\DB;

class Task
{
    /**
     * 添加新用户
     * @param $user
     * @return mixed
     */
    public function init($user)
    {
        return DB::table("task")->insert([
            "user" => $user,
            "translating" => 0,
            "reviewing" => 0,
            "udate" => date("Y-m-d H:i:s"),
            "cdate" => date("Y-m-d H:i:s")
        ]);
    }

    /**
     * 释放任务
     * @param int $translation
     * @param string $task
     * @return mixed
     */
    public function flush(int $translation, string $task)
    {
        return DB::table("task")->where($task, $translation)->update([$task => 0]);
    }

    /**
     * 添加新任务
     * @param int $user
     * @param string $task
     * @param int $translation
     * @return bool
     */
    public function store(int $user, int $translation, string $task)
    {
        return DB::table("task")->where("user", $user)->update([$task => $translation]);
    }

    /**
     * 任务计数
     * @param int $translation
     * @param string $task
     * @return mixed
     */
    public function count(int $translation, string $task)
    {
        return DB::table("task")->where($task, $translation)->count();
    }
}
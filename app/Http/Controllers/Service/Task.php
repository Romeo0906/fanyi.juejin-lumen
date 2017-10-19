<?php

namespace App\Http\Controllers\Service;

use Illuminate\Support\Facades\DB;

class Task
{
    /**
     * 清空用户任务
     * @param int $user
     * @return mixed
     */
    public function flash (int $user)
    {
        return DB::table("task")->where("user", $user)->update([
            "translating"   => null,
            "reviewing"     => null
        ]);
    }

    /**
     * 添加新任务
     * @param int $user
     * @param string $task
     * @param int $translation
     * @param bool $force
     * @return bool
     */
    public function store (int $user, string $task, int $translation, bool $force = false)
    {
        if (!$force && $this->search($user, $task)) {
            return false;
        }

        return DB::table("userDetail")->where("user", $user)->update([$task => $translation]);
    }

    /**
     * 查找任务
     * @param int $user
     * @param string $task
     * @return mixed
     */
    public function search (int $user, string $task)
    {
        return DB::table("userDetail")->where("user", $user)->value($task);
    }
}
<?php

namespace App\Http\Controllers\Service;

use Illuminate\Support\Facades\DB;

/**
 * 时间线相关服务
 *
 * @author Romeo
 */
class Timeline
{
    /**
     * 添加时间线
     * @param int $translation
     * @param int $user
     * @param string $operation
     * @return mixed
     */
    public function write (int $translation, int $user, string $operation)
    {
        return DB::table("timeline")->insert(array(
            'translation' => $translation,
            'user' => $user,
            'operation' => $operation,
            'cdate' => date("Y-m-d H:i:s")
        ));
    }

    /**
     * 读取时间线
     * @param int $translation
     * @return mixed
     */
    public function read (int $translation)
    {
    	return DB::table('timeline')
            ->leftJoin('user', 'timeline.user', '=', 'user.id')
            ->select('timeline.operation', 'timeline.cdate', 'user.name')
            ->where('timeline.translation', $translation)
    			->get();
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * 分页数据偏移
     *
     * @var integer
     * @author Romeo
     */
    protected $start = 0;

    /**
     * 分页数据长度
     *
     * @var integer
     * @author Romeo
     */
    protected $offset = 10;

    /**
     * 当前请求对象
     *
     * @var object
     * @author Romeo
     */
    protected $request;

    /**
     * 根据分页参数设置数据获取偏移及数据长度，并保存当前请求对象
     *
     * @author Romeo
     */
    public function __construct (Request $request) {
        $this->request = $request;

        if ( $request->has('per_page') ) {
            $this->offset = $request->input('per_page');
        }

        if ( $request->has('page') ) {
            $this->start = ($request->input('page') - 1) * $this->offset;
        }
    }

    /**
     * 检查参数是否为空
     *
     * @param array $mandatory
     * @return boolean
     * @author Romeo
     */
    public function isNull(array $mandatory)
    {
        foreach ($mandatory as $value) {
            if ($this->request->has($value) === false || $this->request->input($value) === "") {
                return $value;
            }
        }

        return false;
    }

    /**
     * 检查字段值在表中是否重复
     *
     * @param string $table
     * @param array $unique
     * @param int $except
     * @return boolean
     * @author Romeo
     */
    public function isDuplicated(string $table, array $unique, int $except = 0)
    {
        foreach ($unique as $value) {
            if (
                DB::table($table)
                    ->where($value, $this->request->input($value))
                    ->when(($except > 0), function ($query) use ($except) {
                            return $query->where("id", "<>", $except);
                        })
                    ->first()
            ) {
                return $value;
            }
        }

        return false;
    }
}
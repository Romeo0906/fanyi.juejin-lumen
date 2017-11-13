<?php
/**
 * Created by PhpStorm.
 * User: romeo
 * Date: 17/10/23
 * Time: 上午5:44
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Rank extends Command
{
    protected $signature = "rank:make {action} {--option}";

    protected $description = "Refresh or flush ranking";

    public function handle()
    {
        if ($this->argument("action") === "flush") {
            $this->flush();
        } else {
            $this->refresh();
        }
    }

    public function flush()
    {
        $field = "monthlyTotal";

        if ($this->argument("--option") == "year") {
            $field = "yearlyTotal";
        }

        return DB::table("statistics")->update([$field => 0]);
    }

    public function refresh()
    {
    }
}
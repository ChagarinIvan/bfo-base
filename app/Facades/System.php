<?php

namespace App\Facades;

use App\Models\SystemFlag;
use Illuminate\Support\Facades\Facade;

class System extends Facade
{
    private const NEED_RECHECK_KEY = 'need_recheck';

    public static function needRecheck(): bool
    {
        $flags = SystemFlag::where('key', self::NEED_RECHECK_KEY)->get();
        /** @var SystemFlag $flag */
        $flag = $flags->first();
        return $flag ? (bool)$flag->volume : false;
    }

    public static function setNeedRecheck(bool $needRecheck = true): void
    {
        $flags = SystemFlag::where('key', self::NEED_RECHECK_KEY)->get();
        /** @var SystemFlag $flag */
        $flag = $flags->first();
        if (!$flag) {
            $flag = new SystemFlag();
            $flag->key = self::NEED_RECHECK_KEY;
        }
        $flag->volume = (string)((int)$needRecheck);
        $flag->save();
    }
}

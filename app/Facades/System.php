<?php

namespace App\Facades;

use App\Models\SystemFlag;
use Illuminate\Support\Facades\Facade;

class System extends Facade
{
    private const NEED_RECHECK_KEY = 'need_recheck';
    private const IDENT_RUNNING_KEY = 'ident_running';

    public static function isNeedRecheck(): bool
    {
        $flags = SystemFlag::where('key', self::NEED_RECHECK_KEY)->get();
        /** @var SystemFlag $flag */
        $flag = $flags->first();
        return $flag ? (bool)$flag->volume : false;
    }

    public static function isIdentRunning(): bool
    {
        $flags = SystemFlag::where('key', self::IDENT_RUNNING_KEY)->get();
        /** @var SystemFlag $flag */
        $flag = $flags->first();
        return $flag ? (bool)$flag->volume : false;
    }

    public static function setNeedRecheck(bool $needRecheck = true): void
    {
        $flag = self::extractFlag(self::NEED_RECHECK_KEY);
        $flag->volume = (string)((int)$needRecheck);
        $flag->save();
    }

    public static function startIdent(): void
    {
        $flag = self::extractFlag(self::IDENT_RUNNING_KEY);
        $flag->volume = 1;
        $flag->save();
    }

    public static function stopIdent(): void
    {
        $flag = self::extractFlag(self::IDENT_RUNNING_KEY);
        $flag->volume = 0;
        $flag->save();
    }

    private static function extractFlag(string $key): SystemFlag
    {
        $flags = SystemFlag::where('key', $key)->get();
        /** @var SystemFlag $flag */
        $flag = $flags->first();
        if (!$flag) {
            $flag = new SystemFlag();
            $flag->key = $key;
        }
        return $flag;
    }
}

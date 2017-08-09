<?php

namespace App\Extend;
class Thumb
{
    public static function getThumb($imgPath, $style='',$def='')
    {

        if(strstr($imgPath,'http')){
            return $imgPath;
        }

        $def = empty($def)?config('filesystems.default_thumb'):$def;
        $style = empty($style)?"":"!".$style;
        $imgPath = empty($imgPath)?$def:$imgPath;
        return config('filesystems.disks.qiniu.domains.custom') . "/" . $imgPath . $style;
    }
}
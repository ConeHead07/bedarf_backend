<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 03.04.2020
 * Time: 14:01
 */

namespace App\Utils\Helper;


class ArrayHelper
{
    static function elements(array $a, $aElementNames) {
        return array_filter($a, function($v, $k) use ($aElementNames) {
            return in_array($k, $aElementNames);
        }, ARRAY_FILTER_USE_BOTH);
    }

    static function elementsMapped(array $a, $aMappedElementNames) {
        $aMapped = [];
        foreach($aMappedElementNames as $oldKey => $newKey) {
            if (is_numeric($oldKey)) {
                $oldKey = $newKey;
            }
            if (isset($a[$oldKey])) {
                $aMapped[$newKey] = $a[$oldKey];
            }
        }
        return $aMapped;
    }
}

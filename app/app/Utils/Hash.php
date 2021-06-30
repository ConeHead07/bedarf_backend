<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 01.04.2020
 * Time: 17:13
 */

namespace App\Utils;


class Hash
{
    public static $aDebug = [];
    public static function fromTupel(array $aRow, bool $onlyFilledFields = true, array $aExcept = []): string {
        $tmp = $aRow;
        $str = '';

        ksort($tmp, SORT_NATURAL | SORT_FLAG_CASE);
        foreach($tmp as $k => $v) {
            if (in_array($k, $aExcept)) {
                continue;
            }
            if ($onlyFilledFields && (is_null($v) || trim($v) === '')) {
                continue;
            }

            $str.= preg_replace('#\s+#', ' ', mb_strtolower(trim($k) . ':' . trim($v)) . ';') ;
        }

        $hash = md5( $str );
        if (1 || isset($aRow['Zustand']) ) {
            if (empty(self::$aDebug)) {
                self::$aDebug = compact('aRow', 'onlyFilledFields', 'aExcept', 'tmp', 'str', 'hash');
            } elseif (strlen(self::$aDebug['str']) < strlen($str)) {
                self::$aDebug = compact('aRow', 'onlyFilledFields', 'aExcept', 'tmp', 'str', 'hash');
            }
        }
        return $hash;
    }
}

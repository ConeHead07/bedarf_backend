<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 01.04.2020
 * Time: 17:02
 */

namespace App\Utils;


class GUID
{
    static function V4(): string {
        $charid = self::uniqid();
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);

        return $uuid;
    }

    static function V4Prefixed(string $prefix = ''): string {
        return $prefix . self::V4();
    }

    static function uniqid(int $length = 23): string {
        $id = '';
        while(strlen($id) < $length) {
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $id.= strtoupper(md5(uniqid(rand(), true)));
        }
        return substr($id, 0, $length);
    }

    static function create(): string {
        return self::V4();
    }

    static function namespace(string $namespace, int $length = 32): string {
        return $namespace . self::uniqid($length);
    }


    static function NS16(string $namespace): string {
        return $namespace . self::uniqid(16);
    }
}

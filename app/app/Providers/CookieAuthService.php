<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 16.09.2020
 * Time: 00:59
 */

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\JWTAuth;

class CookieAuthService
{
    private static $user = null;
    private static $token = '';
    private static $tokenType = 'bearer';

    static public function setAuth(array $user) {
        self::$user = $user;
    }

    static public function setToken(string $token, string $type = null) {
        if (is_null($type)) {
            $type = 'bearer';
        }
        self::$token = $token;
        self::$tokenType = $type;
    }

    static public function getAuth(): array {
        return self::$user;
    }

    static public function getToken(): string {
        return self::$token;
    }

    static public function getTokenType(): string {
        return self::$tokenType;
    }

    static public function createToken(): string {
        return bin2hex(random_bytes(64));
    }

    private static function tokenDir(string $token): string {
        $tmpDir = sys_get_temp_dir();
        $sessDir = $tmpDir . '/sessions';

        if (! is_dir($sessDir) && ! mkdir($sessDir) && ! is_dir($sessDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $sessDir));
        }

        return $sessDir;
    }


    private static function tokenFilePath(string $token, string $dir = ''): string {
        if (!$dir) {
            $dir = self::tokenDir($token);
        }

        return $dir . '/' . substr($token, 0, 30) . '.txt';
    }

    public static function deleteByToken(string $token) {
        $sessFile = self::tokenFilePath($token);
        unlink($sessFile);
        return true;
    }

    public static function authByCredentials(string $user, string $password, int $lifetime = 1800): array
    {
        $dbUserField = (false !== strpos($user, '@')) ? 'email' : 'user';
        $jwtUserField = $dbUserField === 'email' ? $dbUserField : 'username';
        $success = false;

        $credentials = [
            $jwtUserField => $user,
            'password' => $password,
        ];

        $token = Auth::attempt($credentials);
        $user = Auth::user();
        $success = !!$token;
        if (!$success) {
            return compact( 'success');
        }

        $sessFile = self::tokenFilePath($token);
        $expire_in = time() + $lifetime;

        file_put_contents($sessFile, json_encode([
            'id' => $user['id'],
            'time' => time(),
            'expire' => $expire_in,
            'lifetime' => $lifetime,
        ]));
        return compact('success', 'token', 'user', 'expire_in');
    }

    static public function authBytoken($token) {
        $success = false;

        $sessFile = self::tokenFilePath($token);
        $sessFileExists = is_file($sessFile);
        if (is_file($sessFile)) {
            $json = file_get_contents($sessFile);
            $data = json_decode($json);

            $now = time();

            try {
                if (property_exists($data, 'expire') && !empty($data->expire) && $data->expire > $now) {
                    $success = true;
                    if (empty($data->lifetime)) {
                        $data->lifetime = 1800;
                    }
                    $data->expire = time() + $data->lifetime;
                    file_put_contents($sessFile, json_encode($data));
                    return compact( 'success', 'token', 'data');
                }
            } catch(\Exception $e) {
                $errMsg = $e->getMessage();
                echo json_encode(
                    compact( 'errMsg','success', 'sessFile', 'sessFileExists', 'json', 'data', 'now')
                );
                exit;
            }
            unlink($sessFile);
            return compact( 'success', 'sessFile', 'sessFileExists', 'json', 'data', 'now');
        }

        return compact('success', 'sessFile', 'sessFileExists');
    }
}

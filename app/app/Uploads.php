<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 28.08.2020
 * Time: 14:59
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

/**
 * Uploads
 *
 * @mixin Eloquent
 */
class Uploads extends Model
{

    protected $table = 'uploads';
    protected $primaryKey = 'jobid';
    protected static $lastQuery = '';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function listUploads() {
        $db = $this->getConnection()->getPdo();
        $stmt = $db->query(
            'SELECT jobid, mid, importkey, filename, filesize, checksum, '
            . 'stat, created_at, created_uid, modified_at '
            . '  FROM '
            . $this->table);
        return $stmt->fetchAll();
    }

}

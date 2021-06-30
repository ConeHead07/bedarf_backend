<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 04.02.2020
 * Time: 00:31
 */

namespace App;

use Faker\Provider\Base;
use Illuminate\Database\Eloquent\Model;

/**
 * Devices
 *
 * @package App
 * @mixin Eloquent
 */
class Devices extends BaseModel
{
    use HasCreatedModUid;

    const UPDATED_AT = 'modified_at';

    protected $table = 'Devices';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Mandant'
    ];

    protected $attributes = [

    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /*
     * By default, Eloquent expects created_at and updated_at columns to exist on your tables.
     * If you do not wish to have these columns automatically managed by Eloquent,
     * set the $timestamps property on your model to false:
     */

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    public function store() {
        $this->getKey();
    }

    public static function createClientIdentity(string $name, string $userAgent) {
        $dev = new Devices;
        $dev->name = $name;
        $dev->user_agent = $userAgent;
        $dev->save();

        if (!$name) {
            $dev->name = 'Gerät ' . $dev->id;
        }

        return $dev->id;
    }

}

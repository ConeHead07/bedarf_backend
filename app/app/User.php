<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use mysql_xdevapi\Exception;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * User
 *
 * @mixin Eloquent
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasRoles;


    protected $primaryKey = 'id';
    protected $table = 'users'; // eleoquent default: lowercase and ending with multiple s

    /**
     * Validation rules.
     *
     * @var array
     */
    public static $rules = [
        'email'   => 'required|unique:user,name',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'IstAdmin'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function auth(string $user, string $password)
    {
        $pdo = $this->getConnection()->getPdo();
        $userField = (false !== strpos($user, '@')) ? 'email' : 'user';

        $sql = 'SELECT * FROM ' . $this->table
            . ' WHERE ' . $userField . ' LIKE :user '
            . ' AND password = :password LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam('user', $user);
        $stmt->bindParam('password', $password);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row;
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }



    public function list() {
        $db = $this->getConnection()->getPdo();
        $stmt = $db->query(
            'SELECT * FROM users' . $this->table);
        return $stmt->fetchAll();
    }

    public function inventuren()
    {

        return $this
            ->belongsToMany('App\Inventuren', 'InventurenUser', 'uid', 'jobid')
            ->with();

    }

    public function createdInventuren() {
        $this->hasMany('App\Inventuren', 'created_uid', 'id');
    }

    public function getActiveInventurenUserByUID($uid)
    {
        // $this->belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null,
        // $parentKey = null, $relatedKey = null, $relation = null);

        return
            $this
                ->join('InventurenUser', 'InventurenUser.uid', '=', 'users.id')
                ->join('Inventuren', 'Inventuren.jobid', '=', 'InventurenUser.jobid')
                ->where('users.id', '=', $uid)
                ->where('Inventuren.aktiviert', '=', 1)
                ->select([
                        'InventurenUser.jobid',
                        'InventurenUser.uid']
                )
                ->get();

    }

    public function getActiveClientsByUID($uid) {
        return
            $this
                ->join('InventurenUser', 'InventurenUser.uid', '=', 'users.id')
                ->join('Inventuren', 'Inventuren.jobid', '=', 'InventurenUser.jobid')
                ->join('Mandanten', 'Mandanten.mid', '=', 'Inventuren.mid')
                ->where('users.id', '=', $uid)
                ->where('Inventuren.aktiviert', '=', 1)
                ->select([
                        'Mandanten.mid',
                        'Mandanten.Mandant',
                        'Mandanten.created_at',
                        'Mandanten.created_uid',
                        'Mandanten.modified_at',
                        'Mandanten.modified_uid'
                    ]
                )
                ->groupBy(
                    'Mandanten.mid', 'Mandanten.Mandant',
                    'Mandanten.created_at', 'Mandanten.created_uid',
                    'Mandanten.modified_at', 'Mandanten.modified_uid')
                ->get();
    }

    public function getActiveGebaeudeByUID($uid) {
        return
            $this
                ->join('InventurenUser', 'InventurenUser.uid', '=', 'users.id')
                ->join('Inventuren', 'Inventuren.jobid', '=', 'InventurenUser.jobid')
                ->join('InventurenGebaeude', 'InventurenGebaeude.jobid', '=', 'Inventuren.jobid')
                ->join('Gebaeude', 'Gebaeude.gid', '=', 'InventurenGebaeude.gid')
                ->where('users.id', '=', $uid)
                ->where('Inventuren.aktiviert', '=', 1)
                ->select([
                        'Gebaeude.gid',
                        'Gebaeude.mid',
                        'Gebaeude.mandanten_id',
                        'Gebaeude.Gebaeude',
                        'Gebaeude.Adresse',
                        'Gebaeude.created_at',
                        'Gebaeude.created_uid',
                        'Gebaeude.modified_at',
                        'Gebaeude.modified_uid'
                    ]
                )
                ->groupBy(
                    'Gebaeude.gid',
                    'Gebaeude.mid',
                    'Gebaeude.mandanten_id',
                    'Gebaeude.Gebaeude',
                    'Gebaeude.Adresse',
                    'Gebaeude.created_at',
                    'Gebaeude.created_uid',
                    'Gebaeude.modified_at',
                    'Gebaeude.modified_uid')
                ->get();
    }

    public function getActiveInventurenGebaeudeByUID($uid) {
        return
            $this
                ->join('InventurenUser', 'InventurenUser.uid', '=', 'users.id')
                ->join('Inventuren', 'Inventuren.jobid', '=', 'InventurenUser.jobid')
                ->join('InventurenGebaeude', 'InventurenGebaeude.jobid', '=', 'Inventuren.jobid')
                ->join('Gebaeude', 'Gebaeude.gid', '=', 'InventurenGebaeude.gid')
                ->where('users.id', '=', $uid)
                ->where('Inventuren.aktiviert', '=', 1)
                ->select([
                        'InventurenGebaeude.jobid',
                        'InventurenGebaeude.gid',
                        \DB::raw("{$uid} AS uid")
                    ]
                )
                ->groupBy(
                    'InventurenGebaeude.jobid',
                    'InventurenGebaeude.gid')
                ->get();
    }

    public function getActiveInventurenStatusByUID($uid) {
        return
            $this
                ->join('InventurenUser', 'InventurenUser.uid', '=', 'users.id')
                ->join('Inventuren', 'Inventuren.jobid', '=', 'InventurenUser.jobid')
                ->join('InventurenUserStatus', 'InventurenUserStatus.jobid', '=', 'Inventuren.jobid')
                ->where('users.id', '=', $uid)
                ->where('Inventuren.aktiviert', '=', 1)
                ->select([
                        'InventurenUserStatus.*'
                    ]
                )
                ->get();
    }

    public function getActiveInventurenByUID($uid) // $uid
    {
        // $this->belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null,
        // $parentKey = null, $relatedKey = null, $relation = null);

        return
            $this
            ->join('InventurenUser', 'InventurenUser.uid', '=', 'users.id')
            ->join('Inventuren', 'Inventuren.jobid', '=', 'InventurenUser.jobid')
            ->join('Mandanten', 'Mandanten.mid', '=', 'Inventuren.mid')
            ->where('users.id', '=', $uid)
            ->where('Inventuren.aktiviert', '=', 1)
            ->select([
                'Inventuren.jobid',
                'Inventuren.mid',
                'Inventuren.gid',
                'Inventuren.Titel',
                'Inventuren.Start',
                'Inventuren.aktiviert',
                'Inventuren.AbgeschlossenAm',
                'Mandanten.Mandant']
            )
            ->get();

    }

    public function checkAddUserEmail(string $name, string $email, int $uid = 0) {
        $re = (object)[
            'success' => false,
            'params' => compact('name', 'email', 'uid'),
            'errorFields' => [],
            'matchedRow' => [],
            'sql' => ''
        ];
        $re->success = true;
        return $re;

        if (!trim($name) && !trim($email)) {
            $re->errorFields['name'] = 'Name: fehlende Angabe';
            $re->errorFields['email'] = 'Email: fehlende Angabe';
            return $re;
        }

        // $row = User::where('name', $name)->orWhere('email', $email)->first();
        $qUser = User::where( function($query) use($name, $email) {
            $query->where('name', 'LIKE', $name)
                ->orWhere('email', 'LIKE ', $email);
        });
        if ($uid) {
            $qUser->where('id', '!=', $uid);
        }
        $row = $qUser->first();

        if (!$row) {
            $re->success = true;
            return $re;
        }

        if ( 0 === strcasecmp($row['name'], $name) ) {
            $re->errorFields['name'] = 'Der Name ist bereits vergeben';
        }

        if ( 0 === strcasecmp($row['email'], $email) ) {
            $re->errorFields['email'] = 'Die Email-Adresse ist bereits vergeben';
        }

        return $re;
    }

    public function createUser($userData) {
        $re = (object)[
            'success' => false,
            'error' => '',
            'errorFields' => [],
            'row' => null,
        ];

        if (empty($userData['name'])) {
            $re->errorFields['name'] = 'Fehlende Angabe Benutzername.';
        }

        if (empty($userData['IstAdmin'])) {
            $userData['IstAdmin'] = 0;
        }

        if (empty($userData['email'])) {
            $re->errorFields['email'] = 'Fehlende Angabe Email.';
        }

        if (count($re->errorFields)) {
            $re->error = implode("\n", array_values($re->errorFields));
            return $re;
        }

        $validation = $this->checkAddUserEmail($userData['name'], $userData['email']);

        if (!$validation->success) {
            return $validation;
        }

        if (empty($userData['password'])) {
            $re->errorFields['password'] = 'Fehlende Angabe Passwort.';
        }
        if (strlen($userData['password']) < 6) {
            $re->errorFields['password'] = 'Passwort muss mind. 6 Zeichen lang sein.';
        }
        if (!preg_match('#[^@]+@[^@.,!?_(){}].*\.[^@,!?_(){}]#', $userData['email'])) {
            $re->errorFields['email'] = 'Ungültige E-Mail-Adresse';
        }

        if (count($re->errorFields)) {
            $re->error = implode("\n", array_values($re->errorFields));
            return $re;
        }

        $created = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'IstAdmin' => (int)$userData['IstAdmin'] ,
            'password' => app('hash')->make($userData['password'], ['rounds' => 12]),
        ]);

        if ($created) {
            return (object)[
                'success' => true,
                'row' => $created,
            ];
        }

        return $created;
    }

    public function updateUser(int $id, $userData) {
        $user = User::find($id);
        $re = (object)[
            'success' => false,
            'error' => '',
            'errorFields' => [],
            'row' => null,
        ];

        if (!$user) {
            $re->errorFields['id'] = 'Benutzer mit ID ' . $id . ' wurde nicht gefunden!';
            return $re;
        }

        if (count($re->errorFields)) {
            $re->error = implode("\n", array_values($re->errorFields));
            return $re;
        }

        if (empty($userData['IstAdmin'])) {
            $userData['IstAdmin'] = 0;
        }

        if (isset($userData['password'])) {

            if (strlen($userData['password']) < 6) {
                $re->errorFields['password'] = 'Passwort muss mind. 6 Zeichen lang sein.';
            }
            if (!preg_match('#[^@]+@[^@.,!?_(){}].*\.[^@,!?_(){}]#', $userData['email'])) {
                $re->errorFields['email'] = 'Ungültige E-Mail-Adresse';
            }
        }

        if (count($re->errorFields)) {
            $re->error = implode("\n", array_values($re->errorFields));
            return $re;
        }

        if (!empty($userData['password'])) {
            $userData['password'] = app('hash')->make($userData['password'], ['rounds' => 12]);
        }

        $updated = $user->update($userData);

        if ($updated) {
            return (object)[
                'success' => true,
                'row' => $updated,
            ];
        }

        return $updated;
    }

    public function deleteUser(int $id) {
        $re = (object)[
            'success' => false,
            'error' => '',
        ];
        $user = User::find($id);

        if ($user) {
            InventurenUser::where('uid', '=', $id)->delete();
            $user->delete();
            $re->success = true;
            return $re;
        }
        $re->error = 'Der benutzer mit der ID ' . $id . ' wurde nicht gefunden!';
        return $re;
    }

    public function getList() {
        return User::all()->toArray();
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 25.06.2021
 * Time: 15:33
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class MobilesArbeiten extends Model
{
    protected $table = 'View_MobilesArbeiten';
    protected $primaryKey = 'bid';

    protected $fillable = [
        'kid',
        'aid',
        'Fachbereich',
        'Senior_Director_VP',
        'Senior_Dep_Head',
        'Teammanager',
        'MA_Innendienst',
        'MA_Aussendienst',
        'Azubis',
        'Werkstudenten_Praktikanten',
        'Externe',
        'DS_Ratio_Intern',
        'DS_Ratio_Temp',
        'created_uid',
        'modified_uid',
    ];

    public function getAll(): array {

        $pdo = \DB::connection()->getPdo();
        $sth = $pdo->query(
            'SELECT t.* '
            . ''
            . ' FROM ' . $this->table . ' AS t '
            . ' LIMIT 0, 100');
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAllByKid(int $id): array {

        $pdo = \DB::connection()->getPdo();
        $sth = $pdo->query(
            'SELECT t.* '
            . ''
            . ' FROM ' . $this->table . ' AS t '
            . ' WHERE kid = ' . $id
            . ' LIMIT 0, 100');
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function get(int $id): array {
        $pdo = \DB::connection()->getPdo();
        $sth = $pdo->prepare('SELECT * FROM ' . $this->table . ' WHERE ' . $this->primaryKey . ' = :id');
        $sth->execute([':id' => $id]);
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

}

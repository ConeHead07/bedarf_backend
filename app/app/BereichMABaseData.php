<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 23.06.2021
 * Time: 16:43
 */

namespace App;


class BereichMABaseData extends BaseModel
{
    protected $table = 'BereichsMitarbeiter';
    protected $primaryKey = 'bid';
    protected static $_lastQuery = '';

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
            'SELECT t.*, '
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

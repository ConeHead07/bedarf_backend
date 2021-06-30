<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Variablen
 *
 * @mixin Eloquent
 */
class Variables extends BaseModel
{
    protected $table = 'variables';
    protected $primaryKey = 'id';
    public const VAR_LASTID_MALFORMED_INV_BARCODE = 'ClientChangeLog/LastIdOfInvCodeCheck';

    protected $fillable = [
        'name', 'json_value'
    ];

    public function getVar(string $sVarName, $default = null) {
        $db = $this->db;
        $sth = $db->prepare(
            'SELECT * FROM `' . $this->table . '` WHERE `name` = :varName'
        );
        $sth->execute([':varName' => $sVarName]);
        $varRow = $sth->fetch(\PDO::FETCH_ASSOC);
        if ($varRow) {
            return json_decode($varRow['json_value']);
        }
        return $default;
    }

    public function setVar(string $sVarName, $value)
    {
        $db = $this->db;

        $sql = 'SELECT id FROM `' . $this->table . '` WHERE `name` = :name';
        $sth = $this->db->prepare($sql);
        $sth->execute([':name' => $sVarName]);
        $id = $sth->fetchColumn();

        if ($id) {
            $sthUp = $db->prepare('UPDATE `' . $this->table . '` SET json_value = :value WHERE id = :id');
            $sthUp->execute([
                ':value' => json_encode($value),
                ':id' => $id
            ]);
        } else {
            $sthUp = $db->prepare('INSERT `' . $this->table . '`(name, json_value) VALUES(:name, :value)');
            $sthUp->execute([
                ':name' => $sVarName,
                ':value' => json_encode($value),
            ]);
        }
    }
}

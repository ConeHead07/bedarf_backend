<?php

namespace App;


use Barryvdh\LaravelIdeHelper\Eloquent;
use Faker\Provider\Base;
use Illuminate\Database\Eloquent\Model;

use Closure;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

trait HasCreatedModUid {
    public static function boot()
    {
        parent::boot();
        static::creating(function($model)
        {
            $user = Auth::user();
            $model->created_uid = $user->id;
            $model->modified_uid = $user->id;
        });

        static::updating(function($model)
        {
            $user = Auth::user();
            $model->modified_uid = $user->id;
        });
    }
}

class SqlExpr {
    private $expression = '';

    public function __construct(string $expr)
    {
        $this->expression = $expr;
    }

    public function __toString()
    {
        return $this->get();
    }

    public function get(): string {
        return $this->expression;
    }
}
class SqlQuery {
    private $_with = [];
    private $_select = [];
    private $_from = '';
    private $_fromAs = '';
    private $_findIdentifiers = [];
    private $_join = [];
    private $_where = [];
    private $_group = [];
    private $_having = [];
    private $_order = [];
    private $_offset = 0;
    private $_limit = null;

    private $aQuerySegements = [
        'with' => 'array',
        'select' => 'array',
        'from' => 'string',
        'join' => 'array',
        'where' => 'array',
        'group' => 'array',
        'having' => 'array',
        'order' => 'array',
        'offset' => 'integer',
        'limit' => 'integer',
    ];

    private $aQueryProperties = [
        'fromAs' => 'string',
    ];

    private $aAvailableProperties = [];

    private $aQueryParams = [];

    private $aJoinProperties = [
        'with' => ', ',
        'select' => ', ',
        'join' => "\n ",
        'where' => ' AND ',
        'group' => ', ',
        'having' => ' AND ',
        'order' => ', ',
    ];

    /** @var \PDO null  */
    private $db = null;

    public function __construct($fromOpt = []) {
        $this->db = \DB::connection()->getPdo();

        $this->aAvailableProperties = array_merge($this->aQueryProperties, $this->aQuerySegements);

        if (!empty($fromOpt)) {
            if (is_string($fromOpt)) {
                $this->_from = $fromOpt;
            } elseif (is_array($fromOpt)) {
                $this->setProperties($fromOpt);
            }
        }
    }

    public function setProperties(array $props) {
        foreach($props as $_name => $_val) {
            $this->set($_name, $_val);
        }
    }

    public function set(string $sName, $val) {
        if (isset($this->aAvailableProperties[$sName])) {
            if (gettype($val) === $this->aAvailableProperties[$sName]) {
                $this->{"_{$sName}"} = $val;
            } elseif ('array' === $this->aAvailableProperties[$sName] && is_string($val)) {
                $this->{"_{$sName}"}[] = $val;
            }
        }
        return $this;
    }

    public function add(string $sName, $val) {
        if (isset($this->aAvailableProperties[$sName]) && 'array' === $this->aAvailableProperties[$sName]) {
            if (is_array($val)) {
                $key = key($val);
                $val = current($val);
                $this->{"_{$sName}"}[$key] = $val;
            } elseif ('array' === $this->aQuerySegements[$sName] && is_string($val)) {
                $this->{"_{$sName}"}[] = $val;
            }
        }
        return $this;
    }

    public function get(string $sName, bool $bFull = false) {
        if (!$bFull || $sName === 'offset' || $sName === 'fromAlias') {
            if (isset($this->{"_{$sName}"}) && isset($this->aQuerySegements[$sName])) {
                return $this->{"_{$sName}"};
            } else {
                return null;
            }
        }

        if (!isset($this->{"_{$sName}"})
            || is_null($this->{"_{$sName}"} )
            || $this->{"_{$sName}"} === ''
            || !isset($this->aQuerySegements[$sName])) {
            return null;
        }

        if (is_array($this->{"_{$sName}"}) && 'array' === $this->aQuerySegements[$sName]  ) {
            $aParts = [];
            foreach($this->{"_{$sName}"} as $key => $val) {
                if (is_numeric($key)) {
                    $aParts[] = $val;
                } else {
                    if ($sName === 'with') {
                        $aParts[] = "$key AS $val";
                    } elseif($sName === 'join') {
                        $aParts[] = $val;
                    } elseif (in_array($sName, ['select', 'from'])) {
                        $aParts[] = "$val AS $key";
                    }
                }
            }
            $val = implode($this->aJoinProperties[$sName], $aParts);
        } else {
            $val = $this->{"_{$sName}"};
        }

        if (trim($val) === '' && $sName !== 'select') {
            return null;
        }

        if ($sName === 'from' && $this->_fromAs) {
            return 'FROM ' . $this->_from . ' AS ' . $this->_fromAs;
        }

        if ($sName === 'limit' && $bFull && $this->_offset > 0 && $this->_limit) {
            return 'LIMIT ' . $this->_offset . ', ' . $this->_limit;
        }

        if ($sName === 'select' && $bFull && empty($val)) {
            return 'SELECT * ';
        }

        switch($sName) {
            case 'with':
            case 'select':
            case 'from':
            case 'where':
            case 'having':
            case 'limit':
                return strtoupper($sName) . ' ' . $val;

            case 'group':
                return 'GROUP BY ' . $val;

            case 'order':
                return 'ORDER BY ' . $val;

            case 'join':
                return $val;
        }
    }

    public function asSql(array $aParams = []): string {
        $sql = '';
        foreach($this->aQuerySegements as $key => $typ) {
            $segment = $this->get($key, true);
            if (!trim($segment)) {
                continue;
            }
            $sql .= (strlen($sql) > 0 ? ' ' : '') . $segment;
        }
        return $this->getRenderedSql($sql, empty($aParams) ? $this->aQueryParams : $aParams);
    }

    public function asCountSql(array $aParams = []) {
        return 'SELECT COUNT(1) FROM (' . $this->asSql($aParams) . ') AS t';
    }

    public function with($val) {
        return $this->set('with', $val);
    }

    public function from(string $val, string $alias = '') {
        $this->_fromAs = $alias;
        return $this->set('from', $val);
    }

    public function fromAlias(string $val) {
        $this->_fromAs = $val;
        return $this;
    }

    public function join($val) {
        return $this->set('join', $val);
    }

    public function select($val, string $table = '') {
        if ($table) {
            if (is_array($val)) {
                $aParts = [];
                foreach($val as $alias => $exp) {
                    if (strpos($exp, '(') === false && strpos($exp, '.') === false) {
                        $aParts[$alias] = "$table.$exp";
                    } else {
                        $aParts[$alias] = $exp;
                    }
                }
                $val = $aParts;
            } else {
                $val = "$table.$val";
            }
        }
        return $this->set('select', $val);
    }

    /**
     * where($query) || where($fld, $val) || where($fld, $cmpOp, $val)
     * @param $qry
     * @param string ...$args
     * @return SqlQuery
     */
    public function where($qry, ...$args) {
        if (is_string($qry)) {
            return $this->addWhere($qry, ...$args);
        } elseif (is_array($qry)) {
            foreach($qry as $k => $q) {
                if (is_string($k)) {
                    $this->addWhere($k, $q);
                } elseif (is_string($q)) {
                    $this->addWhere($q);
                } elseif (is_array($q)) {
                    call_user_func_array([$this, 'addWhere'], $q);
                }
            }
        }
        return $this;
    }

    public function addWhere(string $qry, ...$opTermArgs) {
        if (is_null($qry) || (is_string($qry) && !trim($qry)) ) {
            return $this;
        }
        $params = $opTermArgs;
        $num = count($params);
        $term = array_pop($params);
        switch($num) {
            case 0:
                $this->_where[] = $qry;
                return $this;
                break;

            case 1:
                if (!is_array($term)) {
                    $op = ' = ';
                    if (!is_numeric($term)) {
                        $term = $this->quote($term);
                    }
                } else {
                    $op = ' IN';
                    $term = '(' . $this->quote($term) . ')';
                }
                break;

            case 2:
                $op = array_pop($params);
                if (strcasecmp($op, 'IN') === 0) {
                    $term = '(' . $this->quote($term) . ')';
                } else {
                    $term = $this->quote($term);
                }
                break;

            default:
                throw new \InvalidArgumentException('Exceeding max 3 arguments for method addWhere');
        }
        $this->_where[] = "$qry $op $term";
        return $this;
    }

    public function group($term) {
        return $this->set('group', $term);
    }

    public function having($term) {
        return $this->set('having', $term);
    }

    public function order($term) {
        return $this->set('order', $term);
    }

    public function limit(int $iOffsetLimit, int $iLimit = 0) {
        if ($iLimit > 0) {
            $this->_offset = $iOffsetLimit;
            $term = $iLimit;
        } else {
            $term = $iOffsetLimit;
        }
        return $this->set('limit', $term);
    }

    public function getWith(bool $bFull = false) {
        return $this->get('with', $bFull);
    }

    public function getSelect(bool $bFull = false) {
        return $this->get('select', $bFull);
    }

    public function getFrom(bool $bFull = false) {
        return $this->get('from', $bFull);
    }

    public function getWhere(bool $bFull = false) {
        return $this->get('where', $bFull);
    }

    public function getGroup(bool $bFull = false) {
        return $this->get('group', $bFull);
    }

    public function getHaving(bool $bFull = false) {
        return $this->get('having', $bFull);
    }

    public function getOrder(bool $bFull = false) {
        return $this->get('order', $bFull);
    }

    public function getLimit(bool $bFull = false) {
        return $this->get('limit', $bFull);
    }

    public function getOffset() {
        return $this->_offset;
    }

    public function getFromAs() {
        return $this->fromAlias;
    }


    public function params(array $aParams) {
        $this->aQueryParams = $aParams;
        return $this;
    }

    public function param(string $sName, $term) {
        $this->aQueryParams[$sName] = $term;
        return $this;
    }

    public function query(array $aParams = []): \PDOStatement {

        return $this->db->query( $this->asSql($aParams) );
    }

    public function fetchRows($fetchStyle = \PDO::FETCH_ASSOC, array $aParams = []) {
        $sth = $this->query($aParams);
        return $sth->fetchAll($fetchStyle);
    }

    public function fetchRow($fetchStyle = \PDO::FETCH_ASSOC, array $aParams = []) {
        $sth = $this->query($aParams);
        return $sth->fetch($fetchStyle);
    }

    public function fetchCount(array $aParams = []) {
        $sth = $this->db->query( $this->asCountSql( $aParams ) );
        return $sth->fetchColumn(0);
    }

    public function getRenderedSql(string $sql, array $aParams = []) {
        foreach($aParams as $k => $v) {
            if (!is_array($v)) {
                $sql = preg_replace('#:' . ltrim($k, ':') . '\b#', $this->quote($v), $sql);
            }
        }
        return $sql;
    }

    private function quote($term) {
        $self = $this;

        if (is_object($term) && $term instanceof SqlExpr) {
            return (string)$term;
        }

        if (!is_array($term)) {
            if (is_numeric($term)) {
                return $term;
            }
            return $this->db->quote($term);
        } else {
            return implode( ', ', array_map(function($v) use($self) { return $self->db->quote($v); }, $term));
        }
    }

    public function __toString() {
        return $this->asSql();
    }

}

/**
 * BaseModel
 *
 * @package App
 * @mixin Eloquent
 * @mixin Illuminate\Database\Eloquent\Model
 * @mixin Illuminate\Database\Eloquent\Builder
 */
abstract class BaseModel extends Model
{
    /** @var \mysqli|null  */
    protected $mysqli = null;
    /** @var \mysqli|null  */
    private static $_mysqlInstance = null;
    private $authUser = null;
    private $aColumnNames = [];
    protected $authUserId = 0;
    /** @var \PDO  */
    protected $db = null;
    protected static $_instances = [];
    protected $lastInsertSql = '';
    protected $lastUpdateSql = '';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'modified_at';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->db = \DB::connection()->getPdo();
        $this->mysqli = $this->getMysqlInstance();

        $this->authUser = Auth::user();
        if (!is_null($this->authUser)) {
            $this->authUserId = $this->authUser->getAuthIdentifier();
        }
    }

    public function getColumnNames(): array {
        if (empty($this->aColumnNames)) {
            $sql = 'show fields from ' . $this->table;
            $sth = $this->db->query('show fields from ' . $this->table);
            $aColNames = [];
            while ($colName = $sth->fetchColumn()) {
                $aColNames[$colName] = $colName;
            }
            $this->aColumnNames = $aColNames;

            if (empty($this->aColumnNames)) {
                throw new \Exception(json_encode(compact('sql', 'aColNames'), JSON_PRETTY_PRINT));
            }
        }
        return $this->aColumnNames;
    }

    public function filterFillableData(array $data): array {
        $aColKeys = $this->getColumnNames();
        return array_intersect_key($data, $aColKeys);
    }

    public static function getInst() {
        $staticClass = static::class;

        if (empty(self::$_instances[$staticClass])) {
            self::$_instances[$staticClass] = new $staticClass();
        }

        return self::$_instances[$staticClass];
    }

    /**
     * @param $id
     * @return array|null
     */
    public function getById($id): ?array
    {
        if (!is_array($this->primaryKey) && !is_array($id)) {
            $row = $this->getQuery()->where($this->primaryKey, $id)->fetchRow();
        } else {
            $aKeys = (array)$this->primaryKey;
            $aIds = (array)$id;
            if (count($aKeys) === count($aIds)) {
                $aWhere = array_combine($aKeys, $aIds);
                $row = $this->getQuery()->where($aWhere)->fetchRow();
            }
        }
        return $row ?: null;
    }

    /**
     * @param $id
     * @return array|null
     */
    public function itemExists($id): bool
    {
        if (!is_array($this->primaryKey) && !is_array($id)) {
            return 0 < $this->getQuery()->where($this->primaryKey, $id)->fetchCount();
        } else {
            $aKeys = (array)$this->primaryKey;
            $aIds = (array)$id;
            if (count($aKeys) === count($aIds)) {
                $aWhere = array_combine($aKeys, $aIds);
                return 0 < $this->getQuery()->where($aWhere)->fetchCount();
            }
        }
        return false;
    }

    public function insertEntity(array $data, bool $getInsertId = true) {
        $cols = [];
        $vals = [];
        $aValidCols = $this->getColumnNames();
        foreach($data as $k => $v) {
            if (!isset($aValidCols[$k])) {
                continue;
            }
            $cols[] = "`$k`";
            $vals[] = $this->quote($v);
        }
        $sql = 'INSERT INTO ' . $this->table . '(' . implode(',', $cols) . ') ';
        $sql.= 'VALUES(' . implode(', ', $vals) . ')';
        $this->lastInsertSql = $sql;
        $rslt = $this->db->exec($sql);

        if ($getInsertId) {
            return $this->db->lastInsertId();
        }
        return $rslt;
    }

    public function updateEntity($id, array $data) {
        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sets = [];
        $aValidCols = $this->getColumnNames();
        foreach($data as $k => $v) {
            if (!isset($aValidCols[$k])) {
                continue;
            }
            $sets[] = " `$k` = " . $this->quote($v);
        }
        $sql.= implode(', ', $sets);

        if (!is_array($this->primaryKey) && !is_array($id)) {
            $sql.= ' WHERE ' . $this->primaryKey . ' = ' . $this->quote($id);
        } else {
            $aKeys = (array)$this->primaryKey;
            $aIds = (array)$id;
            if (count($aKeys) === count($aIds)) {
                $aWhere = array_combine($aKeys, $aIds);
                $whereIds = [];
                foreach($aWhere as $k => $v) {
                    $whereIds[] = " $k = " . $this->quote($v);
                }
                $sql.= ' WHERE ' . implode(' AND ', $whereIds);
            }
        }
        $this->lastUpdateSql = $sql;
        return $this->db->exec($sql);
    }

    public function getLastInsertSql() {
        return $this->lastInsertSql;
    }

    public function getLastUpdateSql() {
        return $this->lastUpdateSql;
    }



    public function getByUuid(string $uuid): ?array {
        $row = $this->getQuery()->where('uuid', $uuid)->fetchRow();

        return $row ?: null;
    }

    public function getQuery(array $options = []): SqlQuery {
        $options['from'] = $this->table;
        return new SqlQuery($options);
    }

    public function getTable(): string {
        return $this->table;
    }

    /**
     * @param array|string| $term
     * @param int $pdoParamType
     * @return string
     */
    public function quote($term, int $pdoParamType = 0) {
        if (is_null($term)) {
            return 'NULL';
        }
        if (is_object($term) && $term instanceof SqlExpr) {
            return (string)$term;
        }

        if ($term instanceof \Date) {
            return $this->db->quote( $term->format('Y-m-d H:i:s') );
        }

        if (is_array($term)) {
            $terms = array_filter($term, function($t) {
                return (is_string($t) || is_numeric($t) || ($t instanceof \DateTime) || ($t instanceof SqlExpr));
            });
            $terms = array_map(function($t) {
                if (is_numeric($t)) {
                    return $t;
                }
                if (is_string($t)) {
                    return $this->db->quote($t);
                }
                if (is_null($t)) {
                    return 'NULL';
                }
                if ($t instanceof SqlExpr) {
                    return (string)$t;
                }
                if ($t instanceof \DateTime) {
                    return $this->db->quote( $t->format('Y-m-d H:i:s') );
                }
            }, $terms);

            return implode(', ', $terms);
        }

        switch ($pdoParamType) {
            case 0:
                return $this->db->quote($term);
                break;

            default:
                return $this->db->quote($term, $pdoParamType);
        }
    }

    /*
    public static function boot()
    {
        parent::boot();
        static::creating(function($model)
        {
            $user = Auth::user();
            $model->created_uid = $user->id;
            $model->modified_uid = $user->id;
        });

        static::updating(function($model)
        {
            $user = Auth::user();
            $model->modified_uid = $user->id;
        });
    }
    */

    private static function getMysqlInstance(): \mysqli {
        $dbConn= env('DB_CONNCECTION');
        $dbHost = env('DB_HOST');
        $dbPort = env('DB_PORT');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');

        if (self::$_mysqlInstance === null
            || !(self::$_mysqlInstance instanceof \mysqli)
            || !self::$_mysqlInstance->ping()
        ) {
            self::$_mysqlInstance = new \mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
        }
        return self::$_mysqlInstance;
    }

}

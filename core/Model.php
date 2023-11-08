<?php
abstract class Model
{
    public static $_conection = null;
    public static $_query;
    public static $_table = '';
    public static $_id = 'id';
    public static $_idType = 'i';

    private $_dataType = [];
    private $__query = [
        'where' => "1",
        'joins' => "",
        "values" => [],
        "valueTypes" => "",
        "limitStart" => 0,
        "limitCount" => 0,
        "groupBy" => [],
        "orderBy" => []
    ];


    public function __construct()
    {
        self::createConection();
        $query = "SELECT `COLUMN_NAME` AS name, `data_type` AS type FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA` = ? AND `TABLE_NAME` = ?";
        foreach (self::selectQuery($query, "ss", [DB_NAME, static::$_table]) as $value) {
            $this->_dataType[TienIch::snakeToCamel($value["name"])] = DATA_TYPE_MAPPINGS[$value["type"]];
            $this->{TienIch::snakeToCamel($value["name"])} = null;
        }
    }

    private static function baseWhere($col, $syntax, $value, $type)
    {
        if (static::$_query == null) {
            static::$_query = new static;
        }

        if (static::$_query->__query['where'] == "1") {
            static::$_query->__query['where'] = "";
        }
        if (strlen(static::$_query->__query['where'])) {
            static::$_query->__query['where'] .= $type;
        }
        static::$_query->__query['where'] .= "$col $syntax ?";
        array_push(static::$_query->__query["values"], $value);
        static::$_query->__query["valueTypes"] .= static::$_query->_dataType[TienIch::camelToSnake($col)];
        return static::$_query;
    }

    public static function limit($limitStart, $limitCount)
    {
        if (static::$_query == null) {
            static::$_query = new static;
        }
        static::$_query->__query["limitStart"] = $limitStart;
        static::$_query->__query["limitCount"] = $limitCount;
       
        return static::$_query;
    }

    public static function orWhere($col, $syntax, $value)
    {
        return self::baseWhere($col, $syntax, $value, "OR ");
    }

    public static function where($col, $syntax, $value)
    {
        return self::baseWhere($col, $syntax, $value, "AND ");
    }

    public static function join($table, $onTable, $onThis = null, $thisTable = null)
    {
        if (static::$_query == null) {
            static::$_query = new static;
        }
        $onThis = $onThis == null ? $onThis : static::$_id;
        $thisTable = $thisTable  ? $thisTable  : static::$_table;
        static::$_query->__query['joins'] .= " JOIN $table ON $table.$onTable = $thisTable.$onThis";
        var_dump(" JOIN $table ON $table.$onTable = $thisTable.$onThis", $onTable);
        return static::$_query;
    }

    public static function groupBy($col)
    {
        if (static::$_query == null) {
            static::$_query = new static;
        }
        array_push(static::$_query->__query["groupBy"], $col);
        return static::$_query;
    }

    public static function orderBy($col, $type = "DESC")
    {
        if (static::$_query == null) {
            static::$_query = new static;
        }
        array_push(static::$_query->__query["orderBy"], "$col $type");
        return static::$_query;
    }

    public static function get($params = ["*"])
    {
        if (static::$_query == null) {
            static::$_query = new static;
        }
        self::createConection();
        $result = [];
        $select = implode(", ", $params);
        $joins = static::$_query->__query["joins"];
        $where = static::$_query->__query["where"];

        $groupBy = "";
        if (count(static::$_query->__query["groupBy"]) > 0) {
            $groupBy = static::$_query->__query["groupBy"];
            $groupBy = implode(", ", $groupBy);
            $groupBy .= "GROUP BY $groupBy";
        }

        $orderBy = "";
        if (count(static::$_query->__query["orderBy"]) > 0) {
            $orderBy = static::$_query->__query["orderBy"];
            $orderBy = implode(", ", $orderBy);
            $orderBy .= "ORDER BY $orderBy";
        }

        $table = static::$_table;
        $query = "SELECT $select FROM $table $joins WHERE $where $groupBy $orderBy";
        if (static::$_query->__query["limitCount"] > 0) {
            $query .= " LIMIT ?, ?";
            array_push(static::$_query->__query["values"], static::$_query->__query["limitStart"]);
            array_push(static::$_query->__query["values"], static::$_query->__query["limitCount"]);
            static::$_query->__query["valueTypes"] .= "ii";
        }

        $data = self::selectQuery($query, static::$_query->__query["valueTypes"], static::$_query->__query["values"]);
        foreach ($data as $value) {
            array_push($result, self::createModel($value));
        }

        static::$_query->__query = [
            'where' => "1",
            'joins' => "",
            "values" => [],
            "valueTypes" => "",
            "limitStart" => 0,
            "limitCount" => 0,
            "groupBy" => [],
            "orderBy" => ""
        ];;

        return $result;
    }

    public static function first($params = ["*"])
    {
        if (static::$_query == null) {
            static::$_query = new static;
        }
        return static::$_query->get($params)[0];
    }

    public function save()
    {
        self::createConection();
        $table = static::$_table;
        $update = [];
        $data = [];

        foreach ($this->_dataType  as $key => $value) {
            array_push($update, "$key = ?");
            array_push($data, $this->{$key});
        }

        $type = implode("", $this->_dataType);
        $update = implode(",", $update);
        $query = "REPLACE $table SET $update";

        $sql = self::$_conection->prepare($query);
        $sql->bind_param($type, ...$data);
        $sql->execute();
    }

    public static function findById($id)
    {
        $table = static::$_table;
        $typeId = static::$_idType;
        $_id = static::$_id;
        $data = self::selectQuery("SELECT * FROM $table where $_id = ?", $typeId, [$id])[0];

        return self::createModel($data);
    }

    public static function all($params = ["*"])
    {
        $result = [];
        $table = static::$_table;
        self::createConection();
        $params = implode(",", $params);
        $sql = self::$_conection->prepare("SELECT $params FROM $table");
        $data = self::select($sql);

        foreach ($data as $value) {
            array_push($result, self::createModel($value));
        }

        return $result;
    }

    private static function createModel($data, $class = null)
    {
        $class = $class == null ? static::class : $class;
        $result = new $class();
        foreach ($data  as $key => $value) {
            $result->{TienIch::snakeToCamel($key)} = $value;
        }

        return $result;
    }

    private static function selectQuery($query, $type = null, $data = [])
    {
        self::createConection();
        $sql = self::$_conection->prepare($query);
        !empty($type) && $sql->bind_param($type, ...$data);
        return self::select($sql);
    }

    public static function raw($query, $class, $type = null, $data = [])
    {
        $dataQuery = self::selectQuery($query, $type, $data);
        $result = [];
        foreach ($dataQuery as $value) {
            array_push($result, self::createModel($value, $class));
        }
        return $result;
    }

    private static function select($sql)
    {
        $items = [];
        $sql->execute();
        $items = $sql->get_result()->fetch_all(MYSQLI_ASSOC);
        return $items;
    }

    protected static function belongToMany($tableClass, $relationTable , $where, $relationTableId = null, $relationThisTableId = null)
    {
        self::createConection();
        $relationTableId =  $relationTableId == null ? $tableClass::$_id : $relationTableId;
        $relationThisTableId = $relationThisTableId == null ? static::$_id : $relationThisTableId;
        $table = $tableClass::$_table;
        $thisTable = static::$_table;
        $thisId = static::$_id;
        $tableId = $tableClass::$_id;
        $data = [];
        $query = "SELECT $table.* FROM $thisTable JOIN $relationTable ON $relationTable.$relationThisTableId =  $thisTable.$thisId JOIN $table ON $table.$tableId = $relationTable.$relationTableId WHERE $thisTable.$thisId = ?";
        foreach (self::selectQuery($query, static::$_idType, [$where]) as $key => $value) {
            array_push($data, self::createModel($value, $tableClass));
        }
        return $data;
    }

    protected static function hasMany($tableClass, $onTable, $where, $onThis = null)
    {
        self::createConection();
        $onThis = $onThis == null ? static::$_id : $onThis;
        $table = $tableClass::$_table;
        $thisTable = static::$_table;
        $data = [];
        $query = "SELECT $table.* FROM $thisTable JOIN $table ON $table.$onTable =  $thisTable.$onThis WHERE $thisTable.$onThis = ?";

        foreach (self::selectQuery($query, static::$_idType, [$where]) as $key => $value) {
            array_push($data, self::createModel($value, $tableClass));
        }
        return $data;
    }

    protected static function hasOne($tableClass, $onThis, $where, $onTable = null)
    {
        self::createConection();
        $onTable = $onTable == null ? $tableClass::$_id : $onTable;
        $table = $tableClass::$_table;
        $thisTable = static::$_table;
        $query = "SELECT $table.* FROM $thisTable JOIN $table ON $table.$onTable =  $thisTable.$onThis WHERE $thisTable.$onThis = ?";
        return self::createModel(self::selectQuery($query, static::$_idType, [$where])[0], $tableClass);
    }

    private static function createConection()
    {
        if (!self::$_conection) {
            self::$_conection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            self::$_conection->set_charset('utf8mb4');
        }
    }
}
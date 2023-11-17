<?php
abstract class Model
{
    public static $__table = '';
    public static $__id = 'id';
    public static $__idType = 'i';
    private static $__conn = null;
    private static $__self = null;
    private static $__query = [
        "WHERE" => "1",
        "PARAMS" => [],
        "PARAMS_TYPE" => "",
        "LIMIT" => [],
        "ORDER_BY" => [],
        "GROUP_BY" => [],
    ];
    public static $__col = [];

    public function __construct(...$agrs)
    {
        if (static::$__conn == null) {
            static::createConnection();
        }
        $database = DB_NAME;
        $table = static::$__table;
        $sql = "SELECT `COLUMN_NAME` as col,`DATA_TYPE` as type FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='$database' AND `TABLE_NAME`='$table'";
        $query = static::$__conn->prepare($sql);
        $query->execute();
        $result = $query->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                static::$__col[$row["col"]] = DATA_TYPE_MAPPING[$row["type"]];
            }
        }
        $query->close();
        foreach ($agrs as $key => $value) {
            $this->{snakeToCamel($key)} = $value;
        }
    }

    public static function all($params = ["*"]){
        $params = implode(",", $params);
        $table = static::$__table;
        $sql = "SELECT $params FROM $table";
        return static::raw($sql);
    }

    public static function first($params = ["*"]){
        return static::get($params)[0];
    }

    public static function findById($id, $params = ["*"])
    {
        $params = implode(",", $params);
        $table = static::$__table;
        $tableId = static::$__id;
        $sql = "SELECT $params FROM $table WHERE $tableId = ?";
        $params = [$id];
        $paramsType = static::$__idType;
        return static::raw($sql, $params, $paramsType)[0];
    }

    public static function where($col, $pattern, $value)
    {
        static::baseWhere($col, $pattern, $value);
        return static::$__self;
    }

    public static function orWhere($col, $pattern, $value)
    {
        static::baseWhere($col, $pattern, $value, " OR ");
        return static::$__self;
    }

    private static function baseWhere($col, $pattern, $value, $option = " AND")
    {
        static::createSelf();
        static::$__query["WHERE"] .= "$option $col $pattern ?";
        array_push(static::$__query["PARAMS"], $value);
        static::$__query["PARAMS_TYPE"] .= static::$__col[$col];
    }

    public static function limit($start, $count)
    {
        static::createSelf();
        static::$__query["LIMIT"]["start"] = $start;
        static::$__query["LIMIT"]["count"] = $count;
        return static::$__self;
    }

    public static function orderBy($col, $option = "DESC")
    {
        static::createSelf();
        array_push(static::$__query["ORDER_BY"], "$col $option");
        return static::$__self;
    }

    public static function groupBy($col)
    {
        static::createSelf();
        array_push(static::$__query["GROUP_BY"], $col);
        return static::$__self;
    }

    public static function get($params = ["*"])
    {
        static::createSelf();
        $limit = "";
        $orderBy = "";
        $groupBy = "";
        if (static::$__query["LIMIT"] != []) {
            $limit = "LIMIT " . implode(",", static::$__query["LIMIT"]);
        }
        if (static::$__query["ORDER_BY"] != []) {
            $orderBy = "ORDER BY " . implode(",", static::$__query["ORDER_BY"]);
        }
        if (static::$__query["GROUP_BY"] != []) {
            $groupBy = "GROUP BY " . implode(",", static::$__query["GROUP_BY"]);
        }
        $table = static::$__table;
        $params = implode(",", $params);
        $where = static::$__query["WHERE"];
        $values = static::$__query["PARAMS"];
        $paramsType = static::$__query["PARAMS_TYPE"];
        $query = "SELECT $params FROM $table WHERE $where $groupBy $orderBy $limit";
        static::$__query = [
            "WHERE" => "1",
            "PARAMS" => [],
            "PARAMS_TYPE" => "",
            "LIMIT" => [],
            "ORDER_BY" => [],
            "GROUP_BY" => [],
        ];
        return static::raw($query, $values, $paramsType);
    }

    public static function raw($sql, $params = null, $paramsType = null)
    {
        if (static::$__conn == null) {
            static::createConnection();
        }
        $query = static::$__conn->prepare($sql);
        !(empty($params) & empty($paramsType)) && $query->bind_param($paramsType, ...$params);
        $query->execute();
        $data = array();
        $result = $query->get_result();
        $class = static::class;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = new $class(...$row);
            }
        }
        $query->close();
        return $data;
    }

    private static function createSelf()
    {
        if (static::$__self == null) {
            $class = static::class;
            static::$__self = new $class();
        }
    }

    private static function createConnection()
    {
        static::$__conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    }

    public static function delete()
    {
        $table = static::$__table;
        $where = static::$__query["WHERE"];
        $values = static::$__query["PARAMS"];
        $paramsType = static::$__query["PARAMS_TYPE"];
        $sql = "DELETE FROM $table WHERE $where";
        static::$__query = [
            "WHERE" => "1",
            "PARAMS" => [],
            "PARAMS_TYPE" => "",
            "LIMIT" => [],
            "ORDER_BY" => [],
            "GROUP_BY" => [],
        ];
        $query = static::$__conn->prepare($sql);
        !(empty($params) & empty($paramsType)) && $query->bind_param($paramsType, ...$values);
        $query->execute();
    }

    public function save()
    {
        $table = static::$__table;
        $col = [];
        $params = [];
        $data = [];
        $paramsType = [];

        foreach (static::$__col as $key => $value) {
            array_push($col, $key);
            array_push($paramsType, $value);
            array_push($params, "?");
            array_push($data, $this->{snakeToCamel($key)});
        }
        $col = implode(",", $col);
        $params = implode(",", $params);
        $paramsType = implode("", $paramsType);

        $sql = "REPLACE INTO $table($col) VALUES ($params)";

        if (static::$__conn == null) {
            static::createConnection();
        }
        $query = static::$__conn->prepare($sql);
        $query->bind_param($paramsType, ...$data);
        $query->execute();
    }

    public function hasOne($class, $classId, $tableId)
    {
        return $this->hasMany($class, $classId, $tableId)[0];
    }

    public function hasMany($class, $classId, $tableId)
    {
        $id = static::$__id;
        $classTable = $class::$__table;
        $table = static::$__table;
        $sql = "SELECT $classTable.* FROM $table JOIN $classTable ON $table.$tableId = $classTable.$classId WHERE $table.$id = ?";
        $query = static::$__conn->prepare($sql);
        $query->bind_param(static::$__idType, $this->{snakeToCamel($tableId)});
        $query->execute();
        $data = [];
        $result = $query->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = new $class(...$row);
            }
        }
        $query->close();
        return $data;
    }

    public function belongsToMany($class, $joinTable, $classIdJoin, $tableIdJoin, $classId, $tableId)
    {
        $classTable = $class::$__table;
        $table = static::$__table;
        $id = static::$__id;
        $sql = "SELECT $classTable.* FROM $table JOIN $joinTable ON $joinTable.$tableIdJoin = $table.$tableId JOIN $classTable ON $classTable.$classId = $joinTable.$classIdJoin WHERE $table.$id = ? ";
        $query = static::$__conn->prepare($sql);
        $query->bind_param(static::$__idType, $this->{snakeToCamel($tableId)});
        $query->execute();
        $data = [];
        $result = $query->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = new $class(...$row);
            }
        }
        $query->close();
        return $data;
    }
}
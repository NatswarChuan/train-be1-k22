<?php
abstract class Model
{
    public static $conection = null;
    public static $_query;
    public static $_table = '';
    public static $_id = 'id';
    public static $idType = 'i';

    private $dataType = [];
    private $query = [
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
            $this->dataType[TienIch::snakeToCamel($value["name"])] = DATA_TYPE_MAPPINGS[$value["type"]];
            $this->{TienIch::snakeToCamel($value["name"])} = null;
        }
    }

    public static function baseWhere($col, $syntax, $value,$type)
    {
        if(static::$_query == null){
            static::$_query = new static;
        }

        if (static::$_query->query['where'] == "1") {
            static::$_query->query['where'] = "";
        }
        if (strlen(static::$_query->query['where'])) {
            static::$_query->query['where'] .= $type;
        }
        static::$_query->query['where'] .= "$col $syntax ?";
        array_push(static::$_query->query["values"], $value);
        static::$_query->query["valueTypes"] .= static::$_query->dataType[TienIch::camelToSnake($col)];
        return static::$_query;
    }

    public static function orWhere($col, $syntax, $value)
    {
        return self::baseWhere($col, $syntax, $value,"OR ");
    }

    public static function where($col, $syntax, $value)
    {
        return self::baseWhere($col, $syntax, $value,"AND ");
    }

    public static function join($table, $onTable, $onThis = null,$thisTable = null)
    {
        if(static::$_query == null){
            static::$_query = new static;
        }
        $onThis = $onThis == null ? $onThis : static::$_id;
        $thisTable = $thisTable  ? $thisTable  : static::$_table;
        static::$_query->query['joins'] .= " JOIN $table ON $table.$onTable = $thisTable.$onThis";
        var_dump(" JOIN $table ON $table.$onTable = $thisTable.$onThis",$onTable);
        return static::$_query;
    }

    public static function groupBy($col)
    {
        if(static::$_query == null){
            static::$_query = new static;
        }
        array_push(static::$_query->query["groupBy"], $col);
        return static::$_query;
    }

    public static function orderBy($col, $type = "DESC")
    {
        if(static::$_query == null){
            static::$_query = new static;
        }
        array_push(static::$_query->query["orderBy"], "$col $type");
        return static::$_query;
    }

    public static function get($params = ["*"])
    {
        if(static::$_query == null){
            static::$_query = new static;
        }
        self::createConection();
        $result = [];
        $select = implode(", ", $params);
        $joins = static::$_query->query["joins"];
        $where = static::$_query->query["where"];

        $groupBy = "";
        if (count(static::$_query->query["groupBy"]) > 0) {
            $groupBy = static::$_query->query["groupBy"];
            $groupBy = implode(", ", $groupBy);
            $groupBy .= "GROUP BY $groupBy";
        }

        $orderBy = "";
        if (count(static::$_query->query["orderBy"]) > 0) {
            $orderBy = static::$_query->query["orderBy"];
            $orderBy = implode(", ", $orderBy);
            $orderBy .= "ORDER BY $orderBy";
        }

        $table = static::$_table;
        $query = "SELECT $select FROM $table $joins WHERE $where $groupBy $orderBy";
        if (static::$_query->query["limitCount"] > 0) {
            $query .= " LIMIT ?, ?";
            array_push(static::$_query->query["values"], static::$_query->query["limitStart"]);
            array_push(static::$_query->query["values"], static::$_query->query["limitCount"]);
            static::$_query->query["valueTypes"] .= "ii";
        }
      
        $data = self::selectQuery($query, static::$_query->query["valueTypes"],static::$_query->query["values"]);
        foreach ($data as $value) {
            array_push($result, self::createModel( $value));
        }

        static::$_query->query = [
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

    public static function first($params = ["*"]){
        if(static::$_query == null){
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

        foreach ($this->dataType  as $key => $value) {
            array_push($update, "$key = ?");
            array_push($data, $this->{$key});
        }

        $type = implode("", $this->dataType);
        $update = implode(",", $update);
        $query = "REPLACE $table SET $update";

        $sql = self::$conection->prepare($query);
        $sql->bind_param($type, ...$data);
        $sql->execute();
    }

    public static function findById($id)
    {
        $table = static::$_table;
        $typeId = static::$idType;
        $_id = static::$_id;
        $data = self::selectQuery("SELECT * FROM $table where $_id = ?", $typeId, [$id])[0];

        return self::createModel($data);
    }

    public static function all($params = ["*"])
    {
        $result = [];
        $table = static::$_table;
        self::createConection();
        $params = implode(",",$params);
        $sql = self::$conection->prepare("SELECT $params FROM $table");
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
        $sql = self::$conection->prepare($query);
        !empty($type) && $sql->bind_param($type, ...$data);
        return self::select($sql);
    }

    public static function select($sql)
    {
        $items = [];
        $sql->execute();
        $items = $sql->get_result()->fetch_all(MYSQLI_ASSOC);
        return $items;
    }

    protected static function belongToMany($tableClass, $relationTable, $relationTableId = null, $relationThisTableId = null){
        self::createConection();
        $relationTableId =  $relationTableId == null ? $tableClass::$_id : $relationTableId;
        $relationThisTableId = $relationThisTableId == null ? static::$_id : $relationThisTableId;
        $table = $tableClass::$_table;
        $thisTable = static::$_table;
        $thisId = static::$_id;
        $tableId = $tableClass::$_id;
        $data = [];
        $query = "SELECT $table.* FROM $thisTable JOIN $relationTable ON $relationTable.$relationThisTableId =  $thisTable.$thisId JOIN $table ON $table.$tableId = $relationTable.$relationTableId";
        foreach (self::selectQuery($query) as $key => $value) {
            array_push($data, self::createModel($value, $tableClass));
        }
        return $data;
    }

    protected static function hasMany($tableClass, $onTable , $onThis = null){
        self::createConection();
        $onThis = $onThis == null ? static::$_id : $onThis;
        $table = $tableClass::$_table;
        $thisTable = static::$_table;
        $data = [];
        $query = "SELECT $table.* FROM $thisTable JOIN $table ON $table.$onTable =  $thisTable.$onThis";

        foreach (self::selectQuery($query) as $key => $value) {
            array_push($data, self::createModel($value, $tableClass));
        }
        return $data;
    }

    protected static function hasOne($tableClass, $onThis , $onTable = null){
        self::createConection();
        $onTable = $onTable == null ? $tableClass::$_id : $onTable;
        $table = $tableClass::$_table;
        $thisTable = static::$_table;
        $data = [];
        $query = "SELECT $table.* FROM $thisTable JOIN $table ON $table.$onTable =  $thisTable.$onThis";
        return self::createModel(self::selectQuery($query)[0], $tableClass);
    }

    private static function createConection()
    {
        if (!self::$conection) {
            self::$conection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            self::$conection->set_charset('utf8mb4');
        }
    }
}

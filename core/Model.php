<?php
class Model
{
    public static $conection = null;
    public static $_table = '';
    public static $_id = 'id';
    public static $idType = 'i';
    public static $_class;

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
            $this->dataType[$value["name"]] = DATA_TYPE_MAPPINGS[$value["type"]];
            $this->{$value["name"]} = null;
        }
    }

    public function where($col, $syntax, $value, $valueType)
    {
        if ($this->query['where'] == "1") {
            $this->query['where'] = "";
        }
        if (strlen($this->query['where'])) {
            $this->query['where'] .= "AND ";
        }
        $this->query['where'] .= "$col $syntax ?";
        array_push($this->query["values"], $value);
        $this->query["valueTypes"] .= $valueType;
        return $this;
    }

    public function orWhere($col, $syntax, $value)
    {
        if ($this->query['where'] == "1") {
            $this->query['where'] = "";
        }
        $this->query['where'] .= "OR $col $syntax $value";
        return $this;
    }

    public function join($table, $onTable, $onThis = null)
    {
        $onThis = $onThis ? $onThis : $this->_id;
        $thisTable = static::$_table;
        $this->query['joins'] .= "JOIN $table ON $table.$onTable = $thisTable.$onThis";
        return $this;
    }

    public function groupBy($col)
    {
        array_push($this->query["groupBy"], $col);
        return $this;
    }

    public function orderBy($col, $type = "DESC")
    {
        array_push($this->query["orderBy"], "$col $type");
        return $this;
    }

    public function get($params = ["*"])
    {
        self::createConection();
        $result = [];
        $select = implode(", ", $params);
        $joins = $this->query["joins"];
        $where = $this->query["where"];

        $groupBy = "";
        if (count($this->query["groupBy"]) > 0) {
            $groupBy = $this->query["groupBy"];
            $groupBy = implode(", ", $groupBy);
            $groupBy .= "GROUP BY $groupBy";
        }

        $orderBy = "";
        if (count($this->query["orderBy"]) > 0) {
            $orderBy = $this->query["orderBy"];
            $orderBy = implode(", ", $orderBy);
            $orderBy .= "ORDER BY $orderBy";
        }

        $table = static::$_table;
        $query = "SELECT $select FROM $table $joins WHERE $where $groupBy $orderBy";
        if ($this->query["limitCount"] > 0) {
            $query .= " LIMIT ?, ?";
            array_push($this->query["values"], $this->query["limitStart"]);
            array_push($this->query["values"], $this->query["limitCount"]);
            $this->query["valueTypes"] .= "ii";
        }
        $sql = $this::$conection->prepare($query);
        $sql->bind_param($this->query["valueTypes"], ...$this->query["values"]);
        $data = $this::select($sql);
        foreach ($data as $value) {
            array_push($result, self::createModel( $value));
        }

        $this->query = [
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

    public function first($params = ["*"]){
        return $this->get($params)[0];
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

    public static function findById($id, $class)
    {
        $table = static::$_table;
        $typeId = static::$idType;
        $_id = static::$_id;
        $data = self::selectQuery("SELECT * FROM $table where $_id = ?", $typeId, [$id])[0];

        return self::createModel($data);
    }

    public static function all($class)
    {
        $result = [];
        $table = static::$_table;
        self::createConection();
        $sql = self::$conection->prepare("SELECT * FROM $table");
        $data = self::select($sql);

        foreach ($data as $value) {
            array_push($result, self::createModel($value));
        }

        return $result;
    }

    private static function createModel($data)
    {
        $result = new static::$_class();
        foreach ($data  as $key => $value) {
            $result->{$key} = $value;
        }

        return $result;
    }

    private static function selectQuery($query, $type = null, $data = [])
    {
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

    private static function createConection()
    {
        if (!self::$conection) {
            self::$conection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            self::$conection->set_charset('utf8mb4');
        }
    }

    public static function test(){
        return new static::$_class();
    }
}

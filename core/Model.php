<?php
class Model
{
    public static $conection = null;
    public $table = '';
    public $id = 'id';
    public $idType = 'i';
    public $schema = '';
    private $query = [
        'where' => "1",
        'joins' => "",
        "values" => [],
        "valueTypes" => "",
        "limitStart" => 0,
        "limitCount" => 0
    ];

    public function findById($id){
        $query = "SELECT * FROM $this->table WHERE $this->id = ?";
        $sql = $this::$conection->prepare($query);
        $sql->bind_param($this->idType, $id);
        return json_decode(json_encode($this::select($sql)));
    }

    public function where($col, $syntax, $value, $valueType)
    {
        if($this->query['where'] == "1"){
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
        if($this->query['where'] == "1"){
            $this->query['where'] = "";
        }
        $this->query['where'] .= "OR $col $syntax $value";
        return $this;
    }

    public function join($table, $onTable, $onThis = null)
    {
        $onThis = $onThis ? $onThis : $this->id;
        $this->query['joins'] .= "JOIN $table ON $table.$onTable = $this->table.$onThis";
        return $this;
    }

    public function get($params = ["*"])
    {
        $select = implode(",", $params);
        $joins = $this->query["joins"];
        $where = $this->query["where"];
        $query = "SELECT $select FROM $this->table $joins WHERE $where";
        if($this->query["limitCount"] > 0){
            $query .= " LIMIT ?, ?";
            array_push($this->query["values"],$this->query["limitStart"]);
            array_push($this->query["values"],$this->query["limitCount"]);
            $this->query["valueTypes"] .= "ii";
        }
        $sql = $this::$conection->prepare($query);
        $sql->bind_param($this->query["valueTypes"], ...$this->query["values"]);
        $this->query = [
            'where' => "",
            'joins' => "",
            "values" => [],
            "valueTypes" => "",
        ];
        return json_decode(json_encode($this::select($sql)));
    }

    public function save($params)
    {
        return $this->genQueryString($params, "REPLACE");
    }

    public function insert($params){
        $this->genQueryString($params, "INSERT");
        return self::$conection->insert_id;
    }

    public function update($params){
        $col = [];
        $data = [];
        $type = [];
        $where = $this->query["where"];
        foreach ($params as $key => $value) {
            array_push($col, "$key = ?");
            array_push($data, $value[0]);
            array_push($type, $value[1]);
        }
        $col = implode(",", $col);
        $type = implode("", $type);
        $query = "UPDATE $this->table SET $col WHERE $where";
        $sql = self::$conection->prepare($query);
        $type .= $this->query["valueTypes"];
        $data = array_merge($data,$this->query["values"]);
        $sql->bind_param($type, ...$data);
        $this->query = [
            'where' => "",
            'joins' => "",
            "values" => [],
            "valueTypes" => "",
        ];
        return $sql->execute();
    }

    public function delete(){
        $where = $this->query["where"];
        $query = "DELETE FROM $this->table WHERE $where";
        $sql = self::$conection->prepare($query);
        $type = $this->query["valueTypes"];
        $sql->bind_param($type, ...$this->query["values"]);
        $this->query = [
            'where' => "",
            'joins' => "",
            "values" => [],
            "valueTypes" => "",
        ];
        return $sql->execute();
    }

    public function limit($start,$count){
        $this->query["limitStart"] = $start;
        $this->query["limitCount"] = $count;
        return $this;
    }

    private function genQueryString($params, $query){
        $col = [];
        $syntax = [];
        $data = [];
        $type = [];
        foreach ($params as $key => $value) {
            array_push($col, $key);
            array_push($syntax, "?");
            array_push($data, $value[0]);
            array_push($type, $value[1]);
        }

        $col = implode(",", $col);
        $syntax = implode(",", $syntax);
        $type = implode("", $type);
        $query = "$query INTO $this->table($col) VALUES ($syntax)";
        $sql = self::$conection->prepare($query);
        $sql->bind_param($type, ...$data);
        return $sql->execute();
    }

    public function __construct()
    {
        if (!self::$conection) {
            self::$conection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            self::$conection->set_charset('utf8mb4');
        }
        return self::$conection;
    }

    public static function select($sql)
    {
        $items = [];
        $sql->execute();
        $items = $sql->get_result()->fetch_all(MYSQLI_ASSOC);
        return $items;
    }
}

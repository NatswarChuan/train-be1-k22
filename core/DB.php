<?php
class DB
{
    public static $conection = null;

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

<?php
class Router
{
    public static $route = [
        "GET" => [],
        "POST" => []
    ];

    public static function get($uri, $func)
    {
        self::$route["GET"][$uri] = $func;
    }

    public static function post($uri, $func)
    {
        self::$route["POST"][$uri] = $func;
    }

    private static function checkUri($requestUri, $reuqestMethod)
    {
        $requestUri = explode("/", $requestUri);
        $resultRequestUri = "";
        $check = false;
        $data = [];

        foreach (self::$route[$reuqestMethod] as $key => $value) {
            if ($data != [] &&  $check) {
                break;
            }
            $routeUri = explode("/", $key);
            if (count($requestUri) != count($routeUri)) {
                continue;
            }
            else{
                $check = true;
            }
            $data = [];
            $resultRequestUri = $key;
            foreach ($requestUri as $requestUriKey => $requestUriValue) {
                if (preg_match('/\{.*\}/', $routeUri[$requestUriKey])) {
                    $dataKey = str_replace(["{", "}"], "", $routeUri[$requestUriKey]);
                    $data[$dataKey] = $requestUri[$requestUriKey];
                    $check = true;
                } else if ($requestUri[$requestUriKey] != $routeUri[$requestUriKey]) {
                    $check = false;
                    $resultRequestUri = "";
                    continue 2;
                }
            }
        }
        return ["data" => $data, "check" => $check, "requestUri" => $resultRequestUri];
    }

    public static function run($requestUri)
    {
        $reuqestMethod = $_SERVER["REQUEST_METHOD"];
        $checkUri = self::checkUri($requestUri, $reuqestMethod);
        if (!$checkUri["check"]) {
            return notFound();
        }
        $params = json_decode(json_encode($checkUri["data"]));
        $query = json_decode(json_encode($_GET));
        $body = json_decode(json_encode($_POST));
        $session = json_decode(json_encode($_SESSION));
        $cookie = json_decode(json_encode($_COOKIE));
        $request = (object)["params" => $params, "query" => $query, "body" => $body, "cookie" => $cookie, "session" => $session];
        $requestUri = $checkUri["requestUri"];
        $func = self::$route[$reuqestMethod][$requestUri];
        $func = explode("@", $func);
        $class = $func[0];
        $func = $func[1];

        $controller = new $class($request);
        $controller->$func();
    }
}
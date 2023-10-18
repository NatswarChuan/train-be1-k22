<?php
class Router
{
    private $route;
    private $params;
    private $request;
    private $body;
    private $notFound = "";

    function __construct()
    {
        $this->route = ['GET' => [], 'POST' => []];
    }

    function setNotFound($notFound)
    {
        $this->notFound = $notFound;
    }

    function get($url, $class, $function)
    {
        $this->genRoute($url, $class, $function, 'GET');
    }

    function post($url, $class, $function)
    {
        $this->genRoute($url, $class, $function, 'POST');
    }

    private function genRoute($url, $class, $function, $method)
    {
        $url = explode("/", $url);
        if ($url[1] != '') {
            array_splice($url, 0, 1);
        } else {
            array_splice($url, 0, 2);
        }
        $url = array_merge(['/'], $url);
        $this->route[$method] = array_merge($this->route[$method], [['url' => $url, 'class' => $class, 'function' => $function]]);
    }

    function action()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $checkRouter = $this->checkArray($this->route[$requestMethod]);
        if ($checkRouter['check']) {
            $this->params = $checkRouter['params'];
            $this->request = $_GET;
            $this->body = $_POST;
            $cookie = $_COOKIE;
            $session = $_SESSION;
            $files = $_FILES;
            foreach ($cookie as $key => $value) {
                $cookie[$key] = json_decode($value, true);
            }
            foreach ($session as $key => $value) {
                $session[$key] = json_decode($value, true);
            }
            foreach ($files as $key => $value) {
                $files[$key] = (object)$value;
            }
            $files = (object)$files;
            $cookie = json_decode(json_encode($cookie));
            $session = json_decode(json_encode($session));
            $request = (object) ["files" => $files, "cookie" => $cookie, "session" => $session, "params" => (object) $this->params, "query" => (object) $this->request, "body" => (object) $this->body];
            $class = new $checkRouter['class']($request);
            call_user_func([$class, $checkRouter['function']]);
        } else {
            if ($this->notFound == "") {
                $this->gen404();
            } else {

                include_once ROOT_DIR . $this->notFound;
            }
        }
    }

    private function checkArray($routeMethod)
    {
        $check = false;
        $params = array();
        $function = "";
        $class = "";
        foreach ($routeMethod as $temp) {
            if (count(URL) == count($temp['url']) && !$check) {
                $params = array();
                foreach (URL as $key => $value) {
                    if ((substr($temp['url'][$key], 0, 1) == "{"
                            && $temp['url'][$key][strlen($temp['url'][$key]) - 1] == "}")
                        && strpos($temp['url'][$key], "{") !== false
                        && strpos($temp['url'][$key], "}") !== false
                    ) {
                        $tempKey = str_replace("{", "", $temp['url'][$key]);
                        $tempKey = str_replace("}", "", $tempKey);
                        $params = array_merge($params, [$tempKey => URL[$key]]);
                        $function = $temp['function'];
                        $class = $temp['class'];
                        $check = true;
                    } else if ($value == $temp['url'][$key]) {
                        $check = true;
                        $function = $temp['function'];
                        $class = $temp['class'];
                    } else {
                        $check = false;
                        continue 2;
                    }
                }
            }
        }
        $result = [];
        if ($check) {
            $result =  ['check' => $check, 'params' => $params, 'class' => $class, 'function' => $function];
        } else {
            $result = ['check' => $check, 'params' => $params];
        }
        return $result;
    }

    private function gen404()
    {
        echo 404;
    }
}

<?php
class Controller
{
    protected $request;
    protected $user;
    public function __construct($request)
    {
        $this->request = $request;
        $this->user = isset($this->request->session->user) ?
        $this->request->session->user : (isset($this->request->cookie->user) ?
        $this->request->cookie->user :
        null);
    }

    protected function setCookie($cookiename, $cookievalue, $time)
    {
        setcookie($cookiename, json_encode($cookievalue), time() + $time, "/");
    }

    protected function setSession($sessionName, $sessionValue)
    {
        $_SESSION[$sessionName] = json_encode($sessionValue);
    }

    protected function removeCookie($cookiename)
    {
        setcookie($cookiename, null, -1, "/");
    }
    
    protected function removeSession($sessionName)
    {
        if (isset($_SESSION[$sessionName])) {
            unset($_SESSION[$sessionName]);
        }
    }
}

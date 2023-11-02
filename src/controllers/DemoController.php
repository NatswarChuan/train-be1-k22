<?php
class DemoController extends Controller
{
    public  function doGet()
    { 
        return view("form");
    }

    public  function doPost()
    {
        return view("form-send", [
            "name" => $this->request->body->name,
            "email" => $this->request->body->email,
            "website" => $this->request->body->website,
            "comment" => $this->request->body->comment,
            "gender" => $this->request->body->gender,
        ]);
    }
}

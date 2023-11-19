<?php
class CategoryController extends Controller
{

    public function id()
    {
        $id = $this->request->params->id;
        $category  = Category::findById($id);
        return view("home", ["products" => $category->products()]);
    }
}

<?php
class CategoryController extends Controller
{
    public function id()
    {
        $id = $this->request->params->id;
        $products = Category::findById($id)->products();
        return view("blocks/products_list", ["products" => $products]);
    }
}
<?php
class HomeController extends Controller
{

    public function index()
    {
        $count = 4;
        $page = isset($this->request->query->page) ? $this->request->query->page : 1;
        $start = ($page - 1) * $count;
        $products = Product::limit($start, $count)->get();
        return view("home", ["products" => $products]);
    }

    public function id()
    {
        $id = $this->request->params->id;
        $product  = Product::findById($id);
        $product->shop();
        $product->categories();
        return view("product", ["product" => $product]);
    }
}

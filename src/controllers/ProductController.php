<?php
class ProductController extends Controller
{
    public function index()
    {
        $page = isset($this->request->query->page) ? $this->request->query->page : 1;
        $page = ($page - 1) * 4;
        $products = Product::limit($page, 4)->get();
        return view("blocks/products_list", ["products" => $products]);
    }

    public function id()
    {
        $id = $this->request->params->id;
        $product = Product::findById($id);
        $product->categories();
        return view('blocks/product_detail', ["product" => $product]);
    }
}

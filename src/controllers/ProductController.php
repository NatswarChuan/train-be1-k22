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

    public function slug()
    {
        $slug = $this->request->params->slug;
        $product = Product::where("slug",'=',$slug)->first();
        $product->categories();
        return view('blocks/product_detail', ["product" => $product]);
    }
}

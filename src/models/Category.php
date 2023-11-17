<?php
class Category extends Model{
    public static $__table = 'categories';
    public $id;
    public $name;
    public $products;

    function products(){
        $this->products = $this->belongsToMany(Product::class,"category_product","product_id","category_id","id","id");
        return $this->products;
    }
}
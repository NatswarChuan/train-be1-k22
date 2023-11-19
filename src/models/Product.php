<?php
class Product extends Model{
    public static $__table = 'products';
    public static $__id = 'id';
    public static $__idType = 'i';

    public $shop;
    public $categories;

    function shop(){
        $this->shop = $this->hasOne(Shop::class,"shop_id","shop_id");
        return $this->shop;
    }

    function categories(){
        $this->categories = $this->belongsToMany(Category::class,"category_product","category_id","product_id","id","id");
        return $this->categories;
    }
}
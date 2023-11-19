<?php
class Shop extends Model{
    public static $__table = 'shops';
    public static $__id = 'shop_id';
    public static $__idType = 'i';

    public $products;

    function products(){
        $this->products = $this->hasMany(Product::class,"shop_id","shop_id");
        return $this->products;
    }
}
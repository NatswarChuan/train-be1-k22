<?php
class Category extends Model{
    public static $_table = 'categories';

    function products(){
        $this->{"products"} = $this->belongToMany(Product::class,"category_product",$this->id,"product_id","category_id");
        return $this->products;
    }
}
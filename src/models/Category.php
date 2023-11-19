<?php
class Category extends Model
{
    public static $__table = 'categories';
    public static $__id = 'id';
    public static $__idType = 'i';

    public $products;

    function products()
    {
        $this->products = $this->belongsToMany(Product::class, "category_product", "product_id", "category_id", "id", "id");
        return $this->products;
    }
}

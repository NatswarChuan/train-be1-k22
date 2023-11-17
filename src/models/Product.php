<?php
class Product extends Model
{
    public static $__table = 'products';

    public $id;
    public $slug;
    public $name;
    public $price;
    public $description;
    public $image;
    public $shopId;
    public $categories;

    public function categories()
    {
        $this->categories = $this->belongsToMany(Category::class,"category_product","category_id","product_id","id","id");
        return $this->categories;
    }
}

<?php
class Product extends Model
{
    public static $_table = 'products';

    public function categories()
    {
        $this->{"categories"} = $this->belongToMany(Category::class, "category_product", $this->id, "category_id", "product_id");
        return $this->categories;
    }
}

<div class="container">
    <div class="row">
        <div class="col-md">
            <img src="<?php echo BASE_URL . "/public/images/" . $product->image ?>" class="img-fluid" alt="...">
            <p>
                <?php
                foreach ($product->categories as $category) :
                ?>
                    <span class="badge text-bg-warning">
                        <a href="<?php route("/category/" . $category->id) ?>"><?php echo $category->name ?></a>
                    </span>
                <?php
                endforeach
                ?>
            </p>
        </div>
        <div class="col-md">
            <h1><?php echo $product->name ?></h1>
            <h2><?php echo $product->price ?></h2>
            <?php echo $product->description ?>
        </div>
        <div class="col-md mt-5">
            <div class="d-grid gap-2">
                <a href="#" class="btn btn-danger">Mua ngay</a>
                <a href="#" class="btn btn-outline-primary">Thêm vào giỏ</a>
            </div>
        </div>
    </div>
</div>
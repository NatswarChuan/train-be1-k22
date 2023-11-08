<div class="container">
    <div class="row row-cols-1 row-cols-md-4 g-4">
        <?php
        foreach ($products as $product) :
        ?>

            <div class="col">
                <div class="card">
                    <img src="<?php echo BASE_URL . "/public/images/" . $product->image ?>" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title"><a href="<?php route("/product/".$product->id) ?>" class=""><?php echo $product->name ?></a></h5>
                        <p><?php echo $product->price ?></p>
                        <?php
                        $description = trim(strip_tags($product->description));
                        $description = mb_substr($description, 0, mb_strpos($description, ' ', 100));
                        ?>
                        <?php echo $description ?> ...
                        <p>
                            <?php
                            foreach ($product->categories() as $category) :
                            ?>
                                <span class="badge text-bg-warning">
                                    <a href="<?php route("/category/" . $category->id) ?>"><?php echo $category->name ?></a>
                                </span>
                            <?php
                            endforeach
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php
        endforeach
        ?>
    </div>
</div>
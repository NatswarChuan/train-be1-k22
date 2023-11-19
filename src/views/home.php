<?php
foreach ($products as $product) :
?>
    <a class="card" style="width: 18rem;" href="<?php route("/san-pham/" .  $product->id) ?>">
        <div class="card-body">
            <h5 class="card-title"><?php echo  $product->name ?></h5>
        </div>
    </a>
<?php
endforeach
?>
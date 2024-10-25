<?php

function calculateProductPrice($product, $increased_product, $date)
{
    $qty = $product->qty($date);
    if ($qty < 0) {
        $qty = 0;
    }
    $old_price = $qty * $product->price;
    $new_price = $increased_product['qty'] * $increased_product['price'];
    $total_qty = $increased_product['qty'] + $qty;
    $total_price = $old_price + $new_price;
    $price = $total_price / $total_qty;

    return $price;
}

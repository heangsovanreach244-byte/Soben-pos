
<?php

session_start();

$product_id = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;

if(
    isset($_SESSION['cart'][$product_id])
){

    unset($_SESSION['cart'][$product_id]);

}

header("Location: pos.php");
exit();

?>


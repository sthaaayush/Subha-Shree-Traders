<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:../user_login.php');
}

if (isset($_POST['add_product'])) {

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $details = $_POST['details'];
   $details = filter_var($details, FILTER_SANITIZE_STRING);
   $category_id = $_POST['category_id'];
   $category_id = filter_var($category_id, FILTER_SANITIZE_NUMBER_INT);
   $quantity = $_POST['quantity'];
   $quantity = filter_var($quantity, FILTER_SANITIZE_NUMBER_INT);

   $image_01 = $_FILES['image_01']['name'];
   $image_01 = filter_var($image_01, FILTER_SANITIZE_STRING);
   $image_size_01 = $_FILES['image_01']['size'];
   $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
   $image_folder_01 = '../uploaded_img/' . $image_01;

   $image_02 = $_FILES['image_02']['name'];
   $image_02 = filter_var($image_02, FILTER_SANITIZE_STRING);
   $image_size_02 = $_FILES['image_02']['size'];
   $image_tmp_name_02 = $_FILES['image_02']['tmp_name'];
   $image_folder_02 = '../uploaded_img/' . $image_02;

   $image_03 = $_FILES['image_03']['name'];
   $image_03 = filter_var($image_03, FILTER_SANITIZE_STRING);
   $image_size_03 = $_FILES['image_03']['size'];
   $image_tmp_name_03 = $_FILES['image_03']['tmp_name'];
   $image_folder_03 = '../uploaded_img/' . $image_03;

   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

   if ($select_products->rowCount() > 0) {
      $message = 'product name already exist!';
   } else {

      $insert_products = $conn->prepare("INSERT INTO `products`(name, details, price, image_01, image_02, image_03, category_id, quantity) VALUES(?,?,?,?,?,?,?,?)");
      $insert_products->execute([$name, $details, $price, $image_01, $image_02, $image_03, $category_id, $quantity]);

      if ($insert_products) {
         if ($image_size_01 > 2000000 or $image_size_02 > 2000000 or $image_size_03 > 2000000) {
            $message = 'image size is too large!';
         } else {
            move_uploaded_file($image_tmp_name_01, $image_folder_01);
            move_uploaded_file($image_tmp_name_02, $image_folder_02);
            move_uploaded_file($image_tmp_name_03, $image_folder_03);
            $message = 'new product added!';
         }
      }
   }
}

if (isset($_GET['delete'])) {

   $delete_id = $_GET['delete'];
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $delete_product_image->execute([$delete_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   unlink('../uploaded_img/' . $fetch_delete_image['image_01']);
   unlink('../uploaded_img/' . $fetch_delete_image['image_02']);
   unlink('../uploaded_img/' . $fetch_delete_image['image_03']);
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_product->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
   $delete_wishlist->execute([$delete_id]);
   header('location:products.php');
}

// Fetch categories
$select_categories = $conn->prepare("SELECT * FROM `category`");
$select_categories->execute();

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="icon" href="../images/logo1.png" type="image/png">
   <title>Products</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>

<body>

   <?php include '../components/admin_header.php'; ?>
   <section class="product-management">
      <section class="add-products">

         <h1 class="heading">Add Product</h1>

         <form action="" method="post" enctype="multipart/form-data">
            <div class="flex">
               <div class="inputBox">
                  <span>Product Name </span>
                  <input type="text" class="box" required maxlength="100" placeholder="enter product name" name="name">
               </div>
               <div class="inputBox">
                  <span>Product Price </span>
                  <input type="number" min="0" class="box" required max="9999999999" placeholder="enter product price"
                     onkeypress="if(this.value.length == 10) return false;" name="price">
               </div>
               <div class="inputBox">
                  <span>Category </span>
                  <select name="category_id" class="box" required>
                     <option value="" disabled selected>Select Category</option>
                     <?php
                     while ($fetch_categories = $select_categories->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . $fetch_categories['id'] . '">' . $fetch_categories['name'] . '</option>';
                     }
                     ?>
                  </select>
               </div>
               <div class="inputBox">
                  <span>Quantity </span>
                  <input type="number" min="1" class="box" required placeholder="enter product quantity"
                     name="quantity">
               </div>
               <div class="inputBox">
                  <span>Image 01 </span>
                  <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box"
                     required>
               </div>
               <div class="inputBox">
                  <span>Image 02 </span>
                  <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
               </div>
               <div class="inputBox">
                  <span>Image 03 </span>
                  <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
               </div>
               <div class="inputBox">
                  <span>Product description </span>
                  <textarea name="details" placeholder="enter product details" class="box" required maxlength="500"
                     cols="30" rows="10"></textarea>
               </div>
            </div>
            <input type="submit" value="add product" class="btn" name="add_product">
         </form>

      </section>

      <section class="show-products">

         <h1 class="heading">Products Added</h1>

         <div class="box-container">

            <?php
            $select_products = $conn->prepare("SELECT * FROM `products`");
            $select_products->execute();
            if ($select_products->rowCount() > 0) {
               while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
                  $is_out_of_stock = $fetch_products['quantity'] == 0;
                  ?>
                  <div class="box <?= $is_out_of_stock ? 'out-of-stock' : '' ?>">
                     <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">
                     <div class="name"><?= $fetch_products['name']; ?></div>
                     <div class="price">Nrs.<span><?= $fetch_products['price']; ?></span>/-</div>
                     <div class="quantity">Quantity: <span><?= $fetch_products['quantity']; ?></span></div>
                     <?php if ($is_out_of_stock): ?>
                        <div class="out-of-stock-message">Out of Stock</div>
                     <?php endif; ?>
                     <div class="details"><span><?= $fetch_products['details']; ?></span></div>
                     <div class="flex-btn">
                        <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
                        <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn"
                           onclick="return confirm('delete this product?');">delete</a>
                     </div>
                  </div>
                  <?php
               }
            } else {
               echo '<p class="empty">no products added yet!</p>';
            }
            ?>

         </div>

      </section>
   </section>

   <script src="../js/admin_script.js"></script>

</body>

</html>

<style>
   .out-of-stock {
      border: 2px solid red;
   }

   .out-of-stock-message {
      color: red;
      font-weight: bold;
      margin-top: 10px;
   }
</style>
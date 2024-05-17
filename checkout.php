<?php

include 'components/connect.php';

session_start();

if (!isset($_SESSION['user_id'])) {
   header('location:user_login.php');
   exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['order'])) {
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
   $address = 'flat no. ' . filter_var($_POST['flat'], FILTER_SANITIZE_STRING) . ', ' . filter_var($_POST['street'], FILTER_SANITIZE_STRING) . ', ' . filter_var($_POST['city'], FILTER_SANITIZE_STRING) . ', ' . filter_var($_POST['state'], FILTER_SANITIZE_STRING) . ', ' . filter_var($_POST['country'], FILTER_SANITIZE_STRING) . ' - ' . filter_var($_POST['pin_code'], FILTER_SANITIZE_STRING);
   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];

   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if ($check_cart->rowCount() > 0) {
      // Start transaction
      $conn->beginTransaction();

      try {
         // Insert order
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price]);

         // Fetch all cart items
         $cart_items = $check_cart->fetchAll(PDO::FETCH_ASSOC);

         foreach ($cart_items as $item) {
            // Update product quantity
            $update_product_qty = $conn->prepare("UPDATE `products` SET quantity = quantity - ? WHERE id = ?");
            $update_product_qty->execute([$item['quantity'], $item['pid']]);
         }

         // Delete cart items
         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);

         // Commit transaction
         $conn->commit();

         $message[] = 'Order placed successfully!';
      } catch (Exception $e) {
         // Rollback transaction if something goes wrong
         $conn->rollBack();
         $message[] = 'Failed to place order. Please try again.';
      }
   } else {
      $message[] = 'Your cart is empty';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="icon" href="images/logo1.png" type="image/png">

   <title>Checkout</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="checkout-orders">

   <form action="" method="POST">

   <h3>Your Orders</h3>

      <div class="display-orders">
      <?php
         $grand_total = 0;
         $cart_items = [];
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if ($select_cart->rowCount() > 0) {
            while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
               $cart_items[] = $fetch_cart['name'] . ' (' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ')';
               $total_products = implode(', ', $cart_items);
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
      ?>
         <p> <?= $fetch_cart['name']; ?> <span>(<?= 'Nrs.' . $fetch_cart['price'] . '/- x ' . $fetch_cart['quantity']; ?>)</span> </p>
      <?php
            }
         } else {
            echo '<p class="empty">Your cart is empty!</p>';
         }
      ?>
         <input type="hidden" name="total_products" value="<?= $total_products; ?>">
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>">
         <div class="grand-total">Grand Total : <span>Nrs.<?= $grand_total; ?>/-</span></div>
      </div>

      <h3>Place your orders</h3>

      <div class="flex">
         <div class="inputBox">
            <span>Your Name :</span>
            <input type="text" name="name" placeholder="Enter your name" class="box" maxlength="20" required>
         </div>
         <div class="inputBox">
            <span>Your Number :</span>
            <input type="number" name="number" placeholder="Enter your number" class="box" min="0" max="10" onkeypress="if(this.value.length == 10) return false;" required>
         </div>
         <div class="inputBox">
            <span>Your Email :</span>
            <input type="email" name="email" placeholder="Enter your email" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Payment Method :</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">Cash On Delivery</option>
               <option value="esewa">eSewa</option>
               <option value="khalti">Khalti</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Address Line 01 :</span>
            <input type="text" name="flat" placeholder="e.g. Flat number" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Address Line 02 :</span>
            <input type="text" name="street" placeholder="Street name" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>City :</span>
            <input type="text" name="city" placeholder="Kathmandu" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Province :</span>
            <input type="text" name="state" placeholder="Bagmati" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Country :</span>
            <input type="text" name="country" placeholder="Nepal" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>ZIP Code :</span>
            <input type="number" min="0" name="pin_code" placeholder="e.g. 56400" onkeypress="if(this.value.length == 6) return false;" class="box" required>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>" value="Place Order">

   </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>

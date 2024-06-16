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
   $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING); // Updated
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

         $message = 'Order Placed Successfully!';
      } catch (Exception $e) {
         // Rollback transaction if something goes wrong
         $conn->rollBack();
         $message = 'Failed to place order. Please try again.';
      }
   } else {
      $message = 'Your cart is empty';
   }
}

// Fetch user details from the database
$select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_user->execute([$user_id]);
$user_details = $select_user->fetch(PDO::FETCH_ASSOC);

// Fetch cart items and calculate total price
$grand_total = 0;
$cart_items = [];
$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
$select_cart->execute([$user_id]);

if ($select_cart->rowCount() > 0) {
   while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
      $cart_items[] = $fetch_cart['name'] . ' (' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ')';
      // Calculate total price for each item and add it to grand total
      $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
   }
   // Implode all cart items into a single string
   $total_products = implode(', ', $cart_items);
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

      <form action="" method="POST" onsubmit="toggleOnlinePayment()">

         <h3>Your Orders</h3>

         <div class="display-orders">
            <?php
            if ($select_cart->rowCount() > 0) {
               foreach ($cart_items as $item) {
                  ?>
                  <p> <?= $item; ?> </p>
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
               <input type="text" name="name" placeholder="Enter your name" class="box" maxlength="20" required
                  value="<?= isset($user_details['name']) ? $user_details['name'] : ''; ?>">
            </div>
            <div class="inputBox">
               <span>Your Number :</span>
               <input type="number" name="number" placeholder="Enter your number" class="box" min="9" maxlength="10"
                  onkeypress="if(this.value.length == 10) return false;" required
                  value="<?= isset($user_details['number']) ? (int) $user_details['number'] : 0; ?>">
            </div>
            <div class="inputBox">
               <span>Your Email :</span>
               <input type="email" name="email" placeholder="Enter your email" class="box" maxlength="50" required
                  value="<?= isset($user_details['email']) ? $user_details['email'] : ''; ?>">
            </div>
            <div class="inputBox">
               <span>Payment Method :</span>
               <select name="method" class="box" required>
                  <option value="cash on delivery">Cash On Delivery</option>
                  <option value="online">Online Payment</option>
               </select>
            </div>
            <div id="onlinePaymentContainer" style="display: none;">
               <img src="online_payment_image.png" alt="Online Payment Image">
               <button id="cancelOnlinePayment">Cancel</button>
            </div>
            <div class="inputBox">
               <span>Address :</span>
               <input type="text" name="address" placeholder="Address" class="box" maxlength="50" required
                  value="<?= isset($user_details['address']) ? $user_details['address'] : ''; ?>">
            </div>
            <div class="inputBox">
               <span>Landmark:</span>
               <input type="text" name="street" placeholder="e.g. Flat number, Street, House number" class="box"
                  maxlength="50" required>
            </div>
         </div>

         <input type="submit" name="order" class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>" value="Place Order">
         </div>
         </div>

      </form>
   </section>

   <?php include 'components/footer.php'; ?>

   <script src="js/script.js"></script>

</body>

</html>
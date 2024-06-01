<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:../user_login.php');
}

if (isset($_POST['update_payment']) && isset($_POST['payment_status'])) {
   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];
   $payment_status = filter_var($payment_status, FILTER_SANITIZE_STRING);
   $update_payment = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_payment->execute([$payment_status, $order_id]);
   $message = 'payment status updated!';
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];

   // Select the order to be deleted
   $select_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ?");
   $select_order->execute([$delete_id]);
   $deleted_order = $select_order->fetch(PDO::FETCH_ASSOC);

   // Parse the "total products" data into separate entries
   $products_data = explode(',', $deleted_order['total_products']);

   // Prepare the INSERT query
   $insert_sales_history = $conn->prepare("INSERT INTO `sales_history` (user_id, placed_on, product_name, quantity, price, total_price, method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

   foreach ($products_data as $product) {
      // Extract product name and quantity
      $product_info = explode(' (', $product, 2); // Limit to 2 parts to avoid splitting the quantity string
      $product_name = trim($product_info[0]);
      
      // Extract quantity from the second part
      $quantity_data = explode(' x ', $product_info[1], 2); // Limit to 2 parts to extract only the quantity
      $product_quantity = (int) $quantity_data[1]; // Extract the quantity from the second part

      // Extract price from the second part
      $price_data = explode(' ', $quantity_data[0], 2); // Limit to 2 parts to extract only the price
      $product_price = (float) $price_data[0]; // Convert the extracted price to float

      // Calculate total price for the product
      $product_total_price = $product_price * $product_quantity;

      // Execute the query for each product
      $insert_sales_history->execute([$deleted_order['user_id'], $deleted_order['placed_on'], $product_name, $product_quantity, $product_price, $product_total_price, $deleted_order['method'], $deleted_order['payment_status']]);
   }

   // Delete the order from the orders table
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:placed_orders.php');
}






?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="icon" href="../images/logo1.png" type="image/png">
   <title>Placed Orders</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">

</head>

<body>

   <?php include '../components/admin_header.php'; ?>

   <section class="orders">
      <h1 class="heading">Orders</h1>

      <div class="table-container">
         <table>
            <thead>
               <tr>
                  <th>Placed On</th>
                  <th>Name</th>
                  <th>Number</th>
                  <th>Address</th>
                  <th>Total Products</th>
                  <th>Total Price</th>
                  <th>Payment Method</th>
                  <th>Payment Status</th>
                  <th>Actions</th>
               </tr>
            </thead>
            <tbody>
               <?php
               $select_orders = $conn->prepare("SELECT * FROM `orders`");
               $select_orders->execute();
               if ($select_orders->rowCount() > 0) {
                  while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                     ?>
                     <tr>
                        <td><?= $fetch_orders['placed_on']; ?></td>
                        <td><?= $fetch_orders['name']; ?></td>
                        <td><?= $fetch_orders['number']; ?></td>
                        <td><?= $fetch_orders['address']; ?></td>
                        <td><?= $fetch_orders['total_products']; ?></td>
                        <td>Nrs.<?= $fetch_orders['total_price']; ?>/-</td>
                        <td><?= $fetch_orders['method']; ?></td>
                        <td>
                           <form action="" method="post">
                              <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                              <select name="payment_status" class="select">
                                 <option selected disabled><?= $fetch_orders['payment_status']; ?></option>
                                 <option value="Pending" requried>Pending</option>
                                 <option value="Completed" requried>Completed</option>
                              </select>
                        </td>
                        <td>
                           <div class="flex-btn">
                              <input type="submit" value="update" class="option-btn" name="update_payment">
                              <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn"
                                 onclick="return confirm('delete this order?');">Save</a>
                           </div>
                           </form>
                        </td>
                     </tr>
                     <?php
                  }
               } else {
                  echo '<tr><td colspan="9" class="empty">No orders placed yet!</td></tr>';
               }
               ?>
            </tbody>
         </table>
      </div>
   </section>

   <script src="../js/admin_script.js"></script>
</body>

</html>
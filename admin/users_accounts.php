<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:../user_login.php');
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_user = $conn->prepare("DELETE FROM `users` WHERE id = ?");
   $delete_user->execute([$delete_id]);
   $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE user_id = ?");
   $delete_orders->execute([$delete_id]);
   $delete_messages = $conn->prepare("DELETE FROM `messages` WHERE user_id = ?");
   $delete_messages->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart->execute([$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist->execute([$delete_id]);
   header('location:users_accounts.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="icon" href="../images/logo1.png" type="image/png">
   <title>Users Accounts</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="accounts">
   <h1 class="heading">User Accounts</h1>

   <div class="table-container">
      <table>
         <thead>
            <tr>
               <th>User ID</th>
               <th>Username</th>
               <th>Email</th>
               <th>Actions</th>
            </tr>
         </thead>
         <tbody>
            <?php
               $select_accounts = $conn->prepare("SELECT * FROM `users`");
               $select_accounts->execute();
               if($select_accounts->rowCount() > 0){
                  while($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)){   
            ?>
            <tr>
               <td><?= $fetch_accounts['id']; ?></td>
               <td><?= $fetch_accounts['name']; ?></td>
               <td><?= $fetch_accounts['email']; ?></td>
               <td>
                  <a href="users_accounts.php?delete=<?= $fetch_accounts['id']; ?>" onclick="return confirm('delete this account? The user-related information will also be deleted!')" class="delete-btn">Delete</a>
               </td>
            </tr>
            <?php
                  }
               } else {
                  echo '<tr><td colspan="4" class="empty">No accounts available!</td></tr>';
               }
            ?>
         </tbody>
      </table>
   </div>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>

<?php
   if(isset($message)){
         echo '
         <div class="message">
            <span>'.ucfirst($message).'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      
   }
?>

<header class="header">

   <section class="flex">

      <a href="../admin/dashboard.php" class="logo">Admin<span>Panel</span></a>

      <nav class="navbar">
         <a href="../admin/dashboard.php">Home</a>
         <a href="../admin/products.php">Products</a>
         <a href="../admin/category.php">Category</a>
         <a href="../admin/placed_orders.php">Orders</a>
         <a href="../admin/admin_accounts.php">Admins</a>
         <a href="../admin/users_accounts.php">Users</a>
         <a href="../admin/messages.php">Messages</a>
         <a href="../admin/sales_report.php">Report</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
            $select_profile->execute([$admin_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p><?= $fetch_profile['name']; ?></p>
         <div class="flex-btn">
            <a href="../admin/update_profile.php" class="option-btn">Update Profile</a>
            <a href="../components/admin_logout.php" class="delete-btn" onclick="return confirm('logout from the website?');">logout</a> 
         </div>
      </div>

   </section>

</header>
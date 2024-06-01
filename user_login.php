<?php

include 'components/connect.php';

session_start();
if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
}

if (isset($_POST['submit_user'])) {
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
   $select_user->execute([$email, $pass]);
   $user_row = $select_user->fetch(PDO::FETCH_ASSOC);

   if ($select_user->rowCount() > 0) {
      $_SESSION['user_id'] = $user_row['id'];
      header('location:home.php');
   } else {
      $user_message = 'Incorrect username or password!';
   }
}

if (isset($_POST['submit_admin'])) {
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ? AND password = ?");
   $select_admin->execute([$name, $pass]);
   $admin_row = $select_admin->fetch(PDO::FETCH_ASSOC);

   if ($select_admin->rowCount() > 0) {
      $_SESSION['admin_id'] = $admin_row['id'];
      header('location:admin/dashboard.php');
   } else {
      $admin_message = 'Incorrect username or password!';
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
   <title>Login</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <section class="form-container">

      <form action="" method="post" id="user_login_form">
         <h3>User Login</h3>
         <?php if (isset($user_message)) {
            echo "<p class='message'>$user_message</p>";
         } ?>
         <input type="email" name="email" required placeholder="Enter your email" maxlength="50" class="box"
            oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="pass" required placeholder="Enter your password" maxlength="20" class="box"
            oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="submit" value="Login" class="btn" name="submit_user">
         <p>Didn't have an account?</p>
         <a href="user_register.php" class="option-btn">Register Now.</a>
      </form>

      <form action="" method="post" id="admin_login_form" style="display:none;">
         <h3>Admin Login</h3>
         <?php if (isset($admin_message)) {
            echo "<p class='message'>$admin_message</p>";
         } ?>
         <input type="text" name="name" required placeholder="Enter your username" class="box"
            oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="pass" required placeholder="Enter your password" class="box"
            oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="submit" value="Login" class="btn" name="submit_admin">
      </form>

      <div class="toggle-type">
         <span id="user_toggle" class="active">User</span>
         <span id="admin_toggle">Admin</span>
      </div>

   </section>

   <?php include 'components/footer.php'; ?>
   <script src="js/script.js"></script>

   <script>
      document.getElementById('user_toggle').addEventListener('click', function () {
         document.getElementById('user_login_form').style.display = 'block';
         document.getElementById('admin_login_form').style.display = 'none';
         this.classList.add('active');
         document.getElementById('admin_toggle').classList.remove('active');
      });

      document.getElementById('admin_toggle').addEventListener('click', function () {
         document.getElementById('user_login_form').style.display = 'none';
         document.getElementById('admin_login_form').style.display = 'block';
         this.classList.add('active');
         document.getElementById('user_toggle').classList.remove('active');
      });
   </script>

</body>

</html>
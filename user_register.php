<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
}

$message = ''; // Initialize $message to avoid undefined variable notice

// Initialize variables for form fields
$name = $email = $pass = $cpass = $number = $address = '';

if (isset($_POST['submit'])) {
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $address = $_POST['address'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);

   // Use regex to validate email format
   if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,6}$/', $email) || preg_match('/\.com\.[a-zA-Z]{2,}$/', $email)) {
      $message = 'Invalid email format!';
   } else {
      // Validate password length
      if (strlen($_POST['pass']) < 8) {
         $message = 'Password must be at least 8 characters long!';
      } else {
         // Validate phone number type
         if (!ctype_digit($number)) {
            $message = 'Phone number must contain only numbers!';
         } else {
            // Validate phone number length
            if (strlen($number) !== 10) {
               $message = 'Phone number must be 10 digits long!';
            } else {
               $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
               $select_user->execute([$email]);
               $row = $select_user->fetch(PDO::FETCH_ASSOC);

               if ($select_user->rowCount() > 0) {
                  $message = 'Email already exists!';
                  // Clear the form values upon successful registration
                  $name = $email = $number = $address = '';
               } else {
                  if ($pass != $cpass) {
                     $message = 'Confirm password not matched!';
                  } else {
                     $insert_user = $conn->prepare("INSERT INTO `users`(name, email, password, number, address) VALUES(?,?,?,?,?)");
                     $insert_user->execute([$name, $email, $pass, $number, $address]);
                     $message = 'Registered successfully, login now please!';

                     // Clear the form values upon successful registration
                     $name = $email = $number = $address = '';
                  }
               }
            }
         }
      }
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
   <title>Register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">


</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <section class="form-container">
      <form action="" method="post">
         <h3>Register Now.</h3>
         <?php
         if (!empty($message)) {
            echo '<p class="error-message" style="color: orangered; font-weight: bold;">' . $message . '</p>';
         }
         ?>
         <input type="text" name="name" required placeholder="Enter your Username" maxlength="20" class="box"
            value="<?= htmlspecialchars($name); ?>" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="email" name="email" required placeholder="Enter your Email" maxlength="50" class="box"
            value="<?= htmlspecialchars($email); ?>" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="pass" required placeholder="Enter your Password (min. 8 characters)" minlength="8"
            maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="cpass" required placeholder="Confirm your Password" maxlength="20" class="box"
            oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="text" name="number" required placeholder="Enter your Phone Number" maxlength="10" class="box"
            value="<?= htmlspecialchars($number); ?>" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="text" name="address" required placeholder="Enter your Address" maxlength="80" class="box"
            value="<?= htmlspecialchars($address); ?>" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="submit" value="register now" class="btn" name="submit">
         <p>Already have an account?</p>
         <a href="user_login.php" class="option-btn">Login Now.</a>
      </form>
   </section>

   <?php include 'components/footer.php'; ?>

   <script src="js/script.js"></script>

</body>

</html>
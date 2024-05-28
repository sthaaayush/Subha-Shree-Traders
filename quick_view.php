<?php
include 'components/connect.php';
session_start();
if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
}
;
include 'components/wishlist_cart.php';

// Function to fetch product ratings and reviews
function getProductReviews($conn, $pid)
{
   $select_reviews = $conn->prepare("SELECT reviews.*, users.name AS username FROM `reviews` INNER JOIN `users` ON reviews.user_id = users.id WHERE product_id = ?");
   $select_reviews->execute([$pid]);
   return $select_reviews->fetchAll(PDO::FETCH_ASSOC);
}

// Function to calculate average rating
function calculateAverageRating($reviews)
{
   $totalRatings = count($reviews);
   if ($totalRatings === 0)
      return 0;

   $total = 0;
   foreach ($reviews as $review) {
      $total += $review['rating'];
   }
   return round($total / $totalRatings, 1);
}

// Function to check if the user has already submitted a review for the product
function hasUserReviewed($conn, $user_id, $pid)
{
   $select_review = $conn->prepare("SELECT * FROM `reviews` WHERE user_id = ? AND product_id = ?");
   $select_review->execute([$user_id, $pid]);
   return $select_review->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   if (isset($_POST['submit_review'])) {
      $pid = $_POST['pid'];
      $rating = $_POST['rating'];
      $comment = $_POST['comment'];

      // Check if the user has already reviewed the product
      $existing_review = hasUserReviewed($conn, $user_id, $pid);

      if (!$existing_review) {
         // Insert review into database
         $insert_review = $conn->prepare("INSERT INTO `reviews` (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
         $insert_review->execute([$user_id, $pid, $rating, $comment]);
         $message="Review Submitted succesfully";
      }
   } elseif (isset($_POST['update_review'])) {
      $pid = $_POST['pid'];
      $rating = $_POST['rating'];
      $comment = $_POST['comment'];

      // Update the user's review in the database
      $update_review = $conn->prepare("UPDATE `reviews` SET rating = ?, comment = ? WHERE user_id = ? AND product_id = ?");
      $update_review->execute([$rating, $comment, $user_id, $pid]);
      $message="Review Updated succesfully";
   } elseif (isset($_POST['delete_review'])) {
      $pid = $_POST['pid'];

      // Delete the user's review from the database
      $delete_review = $conn->prepare("DELETE FROM `reviews` WHERE user_id = ? AND product_id = ?");
      $delete_review->execute([$user_id, $pid]);
      $message="Review Deleted succesfully";
   }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <link rel="icon" href="images/logo1.png" type="image/png">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Quick view</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <section class="quick-view">

      <h1 class="heading">Quick view</h1>

      <?php
      $pid = $_GET['pid'];
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
      $select_products->execute([$pid]);
      if ($select_products->rowCount() > 0) {
         while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
            ?>
            <form action="" method="post" class="box">
               <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
               <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
               <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
               <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
               <div class="row">
                  <div class="image-container">
                     <div class="main-image">
                        <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
                     </div>
                     <div class="sub-image">
                        <?php
                        // Display each sub-image if it's not empty
                        for ($i = 1; $i <= 3; $i++) {
                           if (!empty($fetch_product["image_0$i"])) {
                              echo '<img src="uploaded_img/' . $fetch_product["image_0$i"] . '" alt="product' . $i . '">';
                           }
                        }
                        ?>
                     </div>
                  </div>
                  <div class="content">
                     <div class="name" style="font-weight: 1000;"><?= $fetch_product['name']; ?></div>
                     <div class="flex">
                        <div class="price"><span>Nrs.</span><?= $fetch_product['price']; ?><span>/-</span></div>
                        <input type="number" name="qty" class="qty" min="1" max="<?= $fetch_product['quantity']; ?>"
                           onkeypress="if(this.value.length == 2) return false;" value="1">
                     </div>
                     <div class="details"><?= $fetch_product['details']; ?></div>
                     <!-- Average Rating Section -->
                     <div style="font-size: 2rem;
                     color: var(--orange);">Average Rating:<br>
                        <?php
                        $reviews = getProductReviews($conn, $pid);
                        $averageRating = calculateAverageRating($reviews);
                        for ($i = 1; $i <= 5; $i++) {
                           if ($i <= $averageRating) {
                              echo '<i class="fas fa-star"></i>'; // Full star
                           } else {
                              echo '<i class="far fa-star"></i>'; // Empty star
                           }
                        }
                        echo ($averageRating);
                        ?>
                     </div>
                     <div class="flex-btn">
                        <input type="submit" value="add to cart" class="btn" name="add_to_cart">
                        <input class="option-btn" type="submit" name="add_to_wishlist" value="add to wishlist">
                     </div>
                  </div>
               </div>
            </form>

            <!-- Form to add or update review -->
            <?php
            // Check if the user has already reviewed the product
            $existing_review = hasUserReviewed($conn, $user_id, $pid);
            ?>
            <form action="" method="post" class="box" <?php if ($existing_review)
               echo 'disabled'; ?>>
               <input type="hidden" name="pid" value="<?= $pid; ?>">
               <div class="row">
                  <div class="content">
                     <div class="name"><?= $existing_review ? 'Update Your Review' : 'Add Your Review'; ?></div>
                     <div class="flex">
                        <div class="rating">
                           <label for="rating" class="ratingSub">Your Rating:</label>
                           <!-- Stars for rating -->
                           <div class="stars">
                              <?php
                              // Initialize rating value
                              $ratingValue = 0;
                              if ($existing_review) {
                                 $ratingValue = $existing_review['rating'];
                              }
                              // Render stars
                              for ($i = 5; $i >= 1; $i--) {
                                 echo '<input type="radio" id="star' . $i . '" name="rating" value="' . $i . '"';
                                 if ($i === $ratingValue) {
                                    echo ' checked';
                                 }
                                 if ($i === 1) {
                                    echo ' checked';
                                 }
                                 echo '><label for="star' . $i . '"><i class="fas fa-star"></i></label>';
                              }
                              ?>
                           </div>
                        </div>
                        <textarea name="comment" placeholder="Write your review here"
                           required><?= $existing_review ? $existing_review['comment'] : ''; ?></textarea>
                     </div>
                     <?php if ($existing_review): ?>
                        <input type="submit" value="Update Review" class="btn" name="update_review">
                        <input type="submit" value="Delete Review" class="btn" name="delete_review">
                     <?php else: ?>
                        <input type="submit" value="Submit Review" class="btn" name="submit_review">
                     <?php endif; ?>
                  </div>
               </div>
            </form>
            <!-- Display existing reviews -->
            <div class="reviews-section">
               <?php if (!empty($reviews)): ?>
                  <h2>Product Reviews</h2>
                  <div class="reviews">
                     <?php foreach ($reviews as $review): ?>
                        <div class="review">
                           <div class="user"><?= $review['username'] ?></div> <!-- Displaying username -->
                           <div class="rating">Rating:
                              <?php
                              for ($i = 1; $i <= 5; $i++) {
                                 if ($i <= $review['rating']) {
                                    echo '<i class="fas fa-star"></i>'; // Full star
                                 } else {
                                    echo '<i class="far fa-star"></i>'; // Empty star
                                 }
                              }
                              ?>
                           </div>
                           <div class="comment"><?= $review['comment'] ?></div>
                        </div>
                     <?php endforeach; ?>
                  </div>
               <?php else: ?>
                  <p class="empty">No reviews yet. Be the first to review this product!</p>
               <?php endif; ?>
            </div>
            <?php
         }
      } else {
         echo '<p class="empty">No Products Added Yet!</p>';
      }
      ?>

   </section>

   <?php include 'components/footer.php'; ?>

   <script src="js/script.js"></script>

</body>

</html>
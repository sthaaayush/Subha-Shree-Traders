<?php
// Include the connection file and start the session
include '../components/connect.php';
session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

// Process add category request
if (isset($_POST['add_category'])) {
    $name = $_POST['name'];
    // Add the category to the database
    $add_category = $conn->prepare("INSERT INTO `category` (name) VALUES (?)");
    $add_category->execute([$name]);
    // Redirect back to the same page after adding category
    header('Location: category.php');
    exit;
}

// Process delete request if delete button is clicked
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    // Delete the category from the database
    $delete_category = $conn->prepare("DELETE FROM `category` WHERE id = ?");
    $delete_category->execute([$delete_id]);
    // Redirect back to the same page after deletion
    header('Location: category.php');
    exit;
}

// Process update request if update button is clicked
if (isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $new_name = $_POST['new_name'];
    // Update the category name in the database
    $update_category = $conn->prepare("UPDATE `category` SET name = ? WHERE id = ?");
    $update_category->execute([$new_name, $category_id]);
    // Redirect back to the same page after update
    header('Location: category.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="icon" href="../images/logo1.png" type="image/png">
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
        /* Style for the update form */
        .update-form {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
    </style>
</head>

<body>

    <?php include '../components/admin_header.php'; ?>

    <section class="add-products">

        <h1 class="heading">Add Category</h1>

        <form action="category.php" method="post" enctype="multipart/form-data">
            <div class="flex">
                <div class="inputBox">
                    <span>Category Name </span>
                    <input type="text" class="box" required maxlength="100" placeholder="Enter category name"
                        name="name">
                </div>
            </div>

            <input type="submit" value="Add Category" class="btn" name="add_category">
        </form>

    </section>

    <section class="show-products">

        <h1 class="heading">Category Added.</h1>

        <div class="box-container">

            <?php
            // Retrieve categories from the database
            $select_categories = $conn->prepare("SELECT * FROM `category`");
            $select_categories->execute();
            if ($select_categories->rowCount() > 0) {
                while ($fetch_category = $select_categories->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <div class="box">
                        <div class="name"><?= $fetch_category['name']; ?></div>
                        <div class="flex-btn">
                            <!-- Update Button -->
                            <button class="option-btn"
                                onclick="openUpdateForm(<?= $fetch_category['id']; ?>, '<?= $fetch_category['name']; ?>')">Update</button>
                            <!-- Delete Button -->
                            <a href="?delete=<?= $fetch_category['id']; ?>" class="delete-btn"
                                onclick="return confirm('Delete this category?');">Delete</a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="empty">No categories added yet!</p>';
            }
            ?>

        </div>

        <!-- Update Category Form (Initially Hidden) -->
        <section class="add-products" id="updateForm"
            style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
            <h1 class="heading">Update Category</h1>

            <form action="update_category.php" method="post">
                <input type="hidden" id="categoryId" name="id">
                <div class="flex">
                    <div class="inputBox">
                        <span>New Category Name </span>
                        <input type="text" class="box" required maxlength="100" placeholder="Enter new category name"
                            id="newName" name="name">
                    </div>
                </div>
                <div class="flex">
                    <input type="submit" value="Update" class="btn" name="update_category">
                    <button type="button" class="btn"
                        onclick="document.getElementById('updateForm').style.display = 'none';">Cancel</button>
                </div>
            </form>
        </section>

    </section>

    <script src="js/script.js"></script>

    <script>
        // Function to open update form with category id and current name
        function openUpdateForm(id, name) {
            document.getElementById('categoryId').value = id;
            document.getElementById('newName').value = name;

            var updateWindow = document.getElementById('updateForm').style.display;
            if (updateWindow == "none") {
                document.getElementById('updateForm').style.display = 'block';
            } else {
                document.getElementById('updateForm').style.display = 'none';
            }
        }
    </script>

</body>

</html>
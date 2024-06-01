<?php

include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];

// Function to get total sales for a specific period
function getTotalSales($start_date, $end_date)
{
    global $conn;

    $total_sales = 0;

    $select_orders = $conn->prepare("SELECT SUM(total_price) AS total_sales FROM `sales_history` WHERE placed_on BETWEEN ? AND ?");
    $select_orders->execute([$start_date, $end_date]);
    $result = $select_orders->fetch(PDO::FETCH_ASSOC);

    if ($result['total_sales']) {
        $total_sales = $result['total_sales'];
    }

    return $total_sales;
}

// Function to get the most purchased product for a specific period
function getMostPurchasedProduct($start_date, $end_date)
{
    global $conn;

    $most_purchased_product = "";

    $select_product = $conn->prepare("SELECT product_name, SUM(quantity) AS total_quantity FROM `sales_history` WHERE placed_on BETWEEN ? AND ? GROUP BY product_name ORDER BY total_quantity DESC LIMIT 1");
    $select_product->execute([$start_date, $end_date]);
    $result = $select_product->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $most_purchased_product = $result['product_name'];
    }

    return $most_purchased_product;
}

// Process form submission and display report
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Retrieve selected year, month, and day from form
    $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
    $month = isset($_GET['month']) ? $_GET['month'] : null;
    $day = isset($_GET['day']) ? $_GET['day'] : null;

    // Calculate start and end dates based on selected filters
    $start_date = "$year-01-01";
    $end_date = "$year-12-31";
    if ($month) {
        $start_date = "$year-$month-01";
        $end_date = date('Y-m-t', strtotime($start_date));
    }
    if ($day) {
        $start_date = "$year-$month-$day";
        $end_date = $start_date;
    }

    // Get total sales and most purchased product for the specified period
    $total_sales = getTotalSales($start_date, $end_date);
    $most_purchased_product = getMostPurchasedProduct($start_date, $end_date);
}

?>
<?php include '../components/admin_header.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/logo1.png" type="image/png">
    <link rel="stylesheet" href="../css/admin_style.css">
    <title>Sales Report</title>
    <style>
        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        .filter-form {
            margin-bottom: 20px;
        }

        .filter-form select {
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <h1>Sales Report</h1>

    <form class="filter-form" action="" method="get">
        <label for="year">Year:</label>
        <select name="year" id="year">
            <option value="<?= date('Y') ?>"><?= date('Y') ?></option>
        </select>
        <label for="month">Month:</label>
        <select name="month" id="month">
            <option value="">All</option>
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>"><?= date('F', mktime(0, 0, 0, $i, 1)) ?></option>
            <?php endfor; ?>
        </select>
        <label for="day">Day:</label>
        <select name="day" id="day">
            <option value="">All</option>
            <?php for ($i = 1; $i <= 31; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
            <?php endfor; ?>
        </select>
        <input type="submit" value="Generate Report">
    </form>
    <table>
        <tr>
            <th>Total Sales</th>
            <td>Nrs.<?= $total_sales ?>/-</td>
        </tr>
        <tr>
            <th>Most Purchased Product</th>
            <td><?= $most_purchased_product ?></td>
        </tr>
    </table>
</body>

</html>
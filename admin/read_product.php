<?php
include '../components/connection.php';
session_start();
$admin_id = $_SESSION['user_id'];

if (!isset($admin_id)) 
{
    header("location:../login.php");
    exit;
}

if (isset($_GET['product_id'])) 
    $product_id = $_GET['product_id'];

// delete product
if (isset($_POST['delete'])) 
{
    $p_id = $_GET['product_id'];
    $delete_image_query = "SELECT image FROM `products` WHERE id = ?";
    $delete_image_stmt = $conn->prepare($delete_image_query);

    if ($delete_image_stmt) {
        $delete_image_stmt->bind_param("i", $p_id); // Bind the product ID as an integer
        $delete_image_stmt->execute();
        $result = $delete_image_stmt->get_result();

        if ($result->num_rows > 0) {
            $fetch_delete_image = $result->fetch_assoc();

            // Step 2: Delete the image from the server
            if (!empty($fetch_delete_image['image'])) {
                $image_path = '../image/product/' . $fetch_delete_image['image'];
                if (file_exists($image_path)) {
                    unlink($image_path); // Remove the image
                }
            }
        }

        $delete_image_stmt->close(); // Close the statement
    }

    // Step 3: Delete the product from the database
    $delete_product_query = "DELETE FROM `products` WHERE id = ?";
    $delete_product_stmt = $conn->prepare($delete_product_query);

    if ($delete_product_stmt) {
        $delete_product_stmt->bind_param("i", $p_id); // Bind the product ID
        $delete_product_stmt->execute();
        $delete_product_stmt->close();
    }
    
    header("location:../admin/view_product.php");
}

if (isset($_POST['edit'])) 
{
    $p_id = $_POST['product_id'];
    $p_id = filter_var($p_id, FILTER_SANITIZE_STRING);
    header("location:../admin/edit_product.php");
}

if (isset($_POST['back'])) 
    header("location:../admin/view_product.php");

?>
<!DOCTYPE html>
<html lang = "en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href = 'https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel = 'stylesheet'>
    <link rel="stylesheet" href="admin_style.css?v=<?php echo time(); ?>">

    <title>Green Coffee - Shop Page</title>
</head>
<body>
    <?php 
        include '../admin/components/admin_header.php'; 
        include '../image_manager.php';
    ?>
    <div class="main">
        <div class="banner">
            <h1>Read Product</h1>
        </div>
        <div class="title2">
            <a href="../admin/dashboard.php">Dashboard</a><span> / Read Product</span>
        </div>
        <section class="read-post">
            <h1 class="heading">Read Product</h1>
            <?php
                $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
                $select_product->bind_param("i", $product_id); // Bind the product ID as an integer
                $select_product->execute();
                $result = $select_product->get_result(); // Fetch the result set
            
                if ($result->num_rows > 0) 
                {
                    while ($fetch_product = $result->fetch_assoc()) 
                    {
                        $productId = $fetch_product['id'];
                        $imageExtension = pathinfo($fetch_product['id'], PATHINFO_EXTENSION);
                        $imagePath = "../image/product/{$productId}.{$imageExtension}";
            ?>
                        <form action="" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="product_id" value="<?= ($fetch_product['id']); ?>">
                            <div class="status" style="color:<?php if ($fetch_product['status'] == "active") { echo "green"; } else { echo "red"; } ?>">
                                <?= ($fetch_product['status']); ?>
                            </div>
            <?php 
                        if (!empty($fetch_product['image'])) 
                        { 
            ?>
                            <img src="<?= $imagePath ?>" class="image" alt="Product Image">
            <?php 
                        } 
            ?>
                            <div class="title"><?= $fetch_product['name']; ?></div>
                            <div class="price">$<?= $fetch_product['price']; ?>/-</div>
                            <div class="content"><?= $fetch_product['product_details']; ?></div>
                            <div class="flex-btn">
                                <button type="submit" name="edit" class="btn">Edit</button>
                                <button type="submit" name="delete" class="btn" onclick="return confirm('Delete this product?');">Delete</button>
                                <button type="submit" name="back" class="btn">Go Back</button>
                            </div>
                        </form>
            <?php
                    }
                } 
            
            else 
            {
                echo '<div class="empty">
                         <p>No product added yet. <br><a href="add_product.php" style="margin-top:1.5rem" class="btn">Add Product</a></p>
                      </div>';
            }
            ?>
        </section>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src = "https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalerts.min.js"></script>
    <script src = "script.js"></script>

</body>

</html>
<!-- 
Author: Gabriel Zayas
Date: 10/30/2024
Assignment: Exercise 10-1 to 10-3 | Checkout.php
*
* This page handles the final step of the shopping process. It retrieves the stored OnlineStore object from the session, validates the store ID, and executes the checkout logic which typically involves recording the order in the database and notifying the user.
*
* It is accessed via a GET request from the main store page, carrying the
* session ID and the store ID to be checked out.
-->

<?php
    // Start a new session or resume the existing one to access the stored shopping cart.
    session_start();
    
    // Include the definition of the core OnlineStore class.
    require_once("class_OnlineStore.php");
    
    // Retrieve the store ID from the GET parameters. This ID is used to load the correct store information.
    $storeID = $_GET['CheckOut'];
    $storeInfo = array();
    
    // Check if the OnlineStore class is defined.
    if (class_exists("OnlineStore")) {
    
        /**
         * Store object retrieval:
         * Unserialize the OnlineStore object stored in the session to restore its state, including the current contents of the shopping cart.
         */
        if (isset($_SESSION['currentStore']))
            $Store = unserialize($_SESSION['currentStore']);
        
        else {
            // Fallback: If no store is in the session, create a new one.
            // NOTE: In a production environment, you might redirect to an error or main page.
            $Store = new OnlineStore();
        }
        // Set the store ID to ensure the correct information is loaded from the database.
        $Store->setStoreID($storeID);
        $storeInfo = $Store->getStoreInformation();
    }
    else {
        // Handle the case where the class file was not properly included or defined.
        $ErrorMsgs[] = "The OnlineStore class is not available!";
        $Store = NULL;
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"
    lang="en">
<head>
    <meta http-equiv="Content-Type"
        content="text/html; charset=iso-8859-1" />
<title><?php echo $storeInfo['name']; ?> Checkout</title>
<link rel="stylesheet" type="text/css" href="<?php echo $storeInfo['css_file']; ?>"/>
</head>
    <body>

        <h1><?php echo htmlentities($storeInfo['name']);?></h1>
        <h2>Checkout</h2>

        <?php
        /**
         * Checkout Execution
         * Call the core checkout method on the OnlineStore object.
         * This method handles the actual order processing, such as inserting the order details into the 'orders' database table.
         */
        $Store->checkout();
        
        // NOTE: After a successful checkout, the session variable should typically be destroyed or the cart emptied to prevent re-submitting the order.
        // Since the $Store object is not serialized back to the session here, subsequent requests will start with an empty cart (unless the session is actively used later).
        ?>
    </body>
</html>
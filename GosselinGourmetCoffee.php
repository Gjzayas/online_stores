<!-- 
Author: Gabriel Zayas
Date: 10/30/2024
Assignment: Exercise 10-1 to 10-3 | GosselinGourmetCoffee.php 
 * GosselinGourmetCoffee.php
 *
 * This is the main front-end page for the Gosselin Gourmet Coffee Online Store.
 * It initializes the session, sets up the OnlineStore object, processes user input
 * for adding/removing items (via GET requests), and displays the product list and shopping cart.
-->

    <?php

    // Start a new session or resume the existing one. This is crucial for maintaining the shopping cart state.
    session_start();

    // Initialize an array to hold any error messages encountered during execution.
    $ErrorMsgs = array();

    // Include the database connection script. This file should define the $DBConnect object.
    require_once("inc_OnlineStoreDB.php");

    // Include the definition of the core OnlineStore class.
    require_once("class_OnlineStore.php");

    // Define the unique identifier for this specific store instance (Coffee Store).
    $storeID = "COFFEE";
    
    // Initialize an array to hold the store's information retrieved from the database.
    $storeInfo = array();

    // Check if the OnlineStore class is defined before proceeding.
    if (class_exists("OnlineStore")) {
        
        /**
         * Store object initialization:
         * Check if a serialized OnlineStore object exists in the session. If so, unserialize it.
         * If not, create a new OnlineStore object.
         */
        if (isset($_SESSION['currentStore']))
            $Store = unserialize($_SESSION['currentStore']);
        
        else
            $Store = new OnlineStore();
            
            // Set the unique ID for the store and fetch its information (name, description, CSS file, etc.).
            $Store->setStoreID($storeID);
            $storeInfo = $Store->getStoreInformation();

            // Process any user input from the GET array (e.g., ItemToAdd, ItemToRemove, EmptyCart).
            $Store->processUserInput();
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
    <title><?php echo $storeInfo['name']; ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo $storeInfo['css_file']; ?>"/>
</head>
    <body>

        <h1><?php echo htmlentities($storeInfo['name']); ?></h1>
        <h2><?php echo htmlentities($storeInfo['description']);?></h2>
        <p><?php echo htmlentities($storeInfo['welcome']); ?></p>

        <?php

        // Call the method to display the list of products and the shopping cart contents.
        $Store->getProductList();

        // Serialize the updated OnlineStore object and save it back to the session
        // so the shopping cart state is maintained across requests.
        $_SESSION['currentStore'] = serialize($Store);

        ?>
    </body>
</html>

<?php
/**
 * Database Connection Cleanup
 * Close the database connection established in inc_OnlineStoreDB.php
 * if it was successfully connected.
 */
if (!$DBConnect->connect_error)
     $DBConnect->close();
?>
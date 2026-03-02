<!-- 
Author: Gabriel Zayas
Date: 10/30/2024
Assignment: Exercise 10-1 to 10-3 | ElectronicsBoutique.php
*
 * This is the main front-end page for the Electronics Boutique Online Store.
 * It initializes the session, sets up the OnlineStore object specific to this store ID, processes user input (add/remove items), and displays the current product catalog and shopping cart contents.
-->

    <?php

    // Start a new session or resume the existing one to manage user state (shopping cart).
    session_start();

    // Initialize an array to hold any error messages encountered.
    $ErrorMsgs = array();

    // Include the database connection script, which defines the $DBConnect object.
    require_once("inc_OnlineStoreDB.php");

    // Include the definition of the core OnlineStore class.
    require_once("class_OnlineStore.php");

    // Define the unique identifier for this specific store instance (Electronics Boutique).
    $storeID = "ELECBOUT";
    
    // Initialize an array to hold the store's information retrieved from the database.
    $storeInfo = array();

    // Check if the necessary OnlineStore class is available.
    if (class_exists("OnlineStore")) {
        
        /**
         * Store object initialization:
         * Attempts to retrieve the serialized OnlineStore object from the session.
         * If the session object exists, it is unserialized (restoring the cart state).
         * Otherwise, a new OnlineStore object is created.
         */
        if (isset($_SESSION['currentStore']))
            $Store = unserialize($_SESSION['currentStore']);
        
        else
            $Store = new OnlineStore();
            
            // Set the unique ID for the store, loading the specific inventory and store details.
            $Store->setStoreID($storeID);
            $storeInfo = $Store->getStoreInformation();

            // Process any user input from the GET array (e.g., adding or removing items).
            $Store->processUserInput();
        }
        else {
            // Handle the critical case where the OnlineStore class is missing.
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

        // Call the method to display the list of products and the shopping cart interface.
        $Store->getProductList();
        
        // Serialize the updated OnlineStore object (including the current cart) and save it back to the session for persistence across page requests.
        $_SESSION['currentStore'] = serialize($Store);

        ?>
    </body>
    </html>

    <?php
    /**
     * Database Connection Cleanup
     * Close the database connection established in inc_OnlineStoreDB.php
     * if it was successfully connected and is still open.
     */
    if (!$DBConnect->connect_error)
    $DBConnect->close();
    ?>
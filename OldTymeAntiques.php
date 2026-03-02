<!-- 
Author: Gabriel Zayas
Date: 10/30/2024
Assignment: Exercise 10-1 to 10-3 | OldTymeAntiques.php 
*
 * This is the main front-end page for the Old Tyme Antiques Online Store.
 * It follows the standard pattern for a store front: initializes the session, loads the database connection and core class, manages the store object state via session serialization, processes user shopping actions, and displays the catalog and shopping cart interface.
-->

    <?php

    // Start a new session or resume the existing one to maintain the shopping cart and user state.
    session_start();

    // Initialize an array to hold any error messages encountered during execution.
    $ErrorMsgs = array();

    // Include the database connection script, which defines the global $DBConnect object.
    require_once("inc_OnlineStoreDB.php");

    // Include the definition of the core business logic class.
    require_once("class_OnlineStore.php");

    // Define the unique identifier for this specific store instance (Antiques).
    $storeID = "ANTIQUE";
    
    // Initialize an array to hold the store's detailed information fetched from the database.
    $storeInfo = array();

    // Check for the availability of the core class before proceeding.
    if (class_exists("OnlineStore")) {
        
        /**
         * Store object state management:
         * Check if a serialized OnlineStore object exists in the session ('currentStore').
         * If found, unserialize it to restore the cart and inventory state.
         * Otherwise, create a new OnlineStore object.
         */
        if (isset($_SESSION['currentStore']))
            $Store = unserialize($_SESSION['currentStore']);
        
        else
            $Store = new OnlineStore();
            
            // Set the specific store ID to load the correct inventory and store details.
            $Store->setStoreID($storeID);
            $storeInfo = $Store->getStoreInformation();

            // Process any user input received via GET request (Add Item, Remove Item, Empty Cart).
            $Store->processUserInput();
        }
        else {
            // Handle the failure case if the core class is not available.
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

        // Call the method to display the product catalog, cart contents, and action links.
        $Store->getProductList();

        // Serialize the updated OnlineStore object (with the latest cart state) and save it back to the session for the next page load.
        $_SESSION['currentStore'] = serialize($Store);

        ?>
    </body>
    </html>

    <?php
    /**
     * Database Connection Cleanup
     * Close the database connection established in inc_OnlineStoreDB.php
     * if the connection was successful and is still open.
     */
    if (!$DBConnect->connect_error)
    $DBConnect->close();
    ?>
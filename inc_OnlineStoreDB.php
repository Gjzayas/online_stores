<!-- 
Author: Gabriel Zayas
Date: 10/30/2024
Assignment: Exercise 10-1 to 10-3 | inc_OnlineStoreDB.php
*
 * This configuration file is responsible for establishing the connection to the MySQL database for the online store application. It uses the mysqli extension for object-oriented database interaction.
 *
 * This file is included by both the main application script (GosselinGourmetCoffee.php) and the core class (class_OnlineStore.php).
-->

<?php

    // Initialize an array to store any error messages, specifically database connection errors.
    $ErrorMsgs = array();
    
    /**
     * Establish Database Connection
     * Attempts to create a new mysqli object to connect to the database.
     * The '@' symbol suppresses PHP warnings/errors, allowing the connection error to be handled gracefully via the connect_errno property.
     *
     * @var mysqli $DBConnect The mysqli object representing the database connection.
     * @param string "localhost" The database server host.
     * @param string "admin"     The database username.
     * @param string "5567"      The database password.
     * @param string "online_stores" The name of the database to select.
     */
    $DBConnect = @new mysqli("localhost", "admin", "5567","online_stores");
    
    // Check if the connection failed.
    if ($DBConnect->connect_errno)
        
        // If connection failed, populate the error message array with details.
        $ErrorMsgs[] = "Unable to connect to the database server." . " Error code " . $DBConnect->connect_errno . ": " . $DBConnect->connect_error;

    // Note: If the connection fails, the $DBConnect object will still exist but its methods will typically throw exceptions or return FALSE. The calling script must check $DBConnect->connect_error (or $DBConnect->connect_errno) before proceeding with queries.
?>
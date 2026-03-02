<!-- 
Author: Gabriel Zayas
Date: 10/30/2024
Assignment: Exercise 10-1 to 10-3 | class_OnlineStore.php
*
* Defines the core logic for the online store application. The OnlineStore class manages the connection to the database, handles the inventory, maintains the state of the user's shopping cart, and processes user interactions like adding/removing items and final checkout.
 *
 * This class uses object serialization to persist the shopping cart state across multiple page requests via the PHP session.
-->    
    
    <?php

    class OnlineStore {
        /**
         * @var mysqli|null $DBConnect The database connection object.
         */
        private $DBConnect = NULL;
        
        /**
         * @var string $storeID The unique identifier for the current store instance (e.g., "COFFEE").
         */
        private $storeID = "";
        
        /**
         * @var array $inventory Stores product details fetched from the 'inventory' database table.
         * Structure: [productID => ['name', 'description', 'price'], ...]
         */
        private $inventory = array();
        
        /**
         * @var array $shoppingCart Stores the current quantity of each product in the user's cart.
         * Structure: [productID => quantity, ...]
         */
        private $shoppingCart = array();

        /**
         * __construct
         * Establishes the database connection upon object creation.
         */
        function __construct() {
            // Include the database connection script, which initializes $DBConnect.
            include("inc_OnlineStoreDB.php");
            $this->DBConnect = $DBConnect;
        }

        /**
         * __destruct
         * Closes the database connection when the object is destroyed.
         */
        function __destruct() {
            if (!$this->DBConnect->connect_error)
                $this->DBConnect->close();
        }

        /**
         * setStoreID
         * Sets the unique store ID and populates the inventory and shopping cart arrays.
         * This method runs database queries to fetch all products associated with the store ID.
         *
         * @param string $storeID The unique identifier of the store to load.
         * @return void
         */
        public function setStoreID($storeID) {
            if ($this->storeID != $storeID) {
                $this->storeID = $storeID;
                $SQLString = "SELECT * FROM inventory " . " where storeID = '" . $this->storeID . "'";
                
                $QueryResult = @$this->DBConnect->query($SQLString);
                
                if ($QueryResult === FALSE) {
                // Reset storeID if the query fails (e.g., store does not exist).
                $this->storeID = "";
            }
            else {
                // Initialize/reset inventory and shopping cart arrays.
                $this->inventory = array();
                $this->shoppingCart = array();
            
                // Populate inventory and initialize cart quantities to zero.
                while (($Row = $QueryResult->fetch_assoc()) !== NULL) {
                    $this->inventory[$Row['productID']] = array();
                    $this->inventory[$Row['productID']]['name'] = $Row['name'];
                    $this->inventory[$Row['productID']]['description'] = $Row['description'];
                    $this->inventory[$Row['productID']]['price'] = $Row['price'];
                    $this->shoppingCart[$Row['productID']] = 0;
                }
            }
        }
    }
    
    /**
     * getStoreInformation
     * Retrieves the display information for the current store from the database.
     *
     * @return array|bool An associative array of store details (name, description, etc.) or FALSE on failure.
     */
    public function getStoreInformation() {
        $retval = FALSE;
            
            if ($this->storeID != "") {
                $SQLString = "SELECT * FROM store_info " . " where storeID = '" . $this->storeID . "'";
                $QueryResult = @$this->DBConnect->query($SQLString);
        
                if ($QueryResult !== FALSE) {
                    $retval = $QueryResult->fetch_assoc();
                }
            }
        return($retval);
        }

        /**
         * getProductList
         * Generates and displays the HTML table containing all products, their details, current cart quantity, total price per item, and action links (Add/Remove/Empty Cart/Checkout).
         *
         * @return bool TRUE if products were displayed, FALSE otherwise.
         */
        public function getProductList() {
            $retval = FALSE;
            $subtotal = 0;
            
                if (count($this->inventory) > 0) {
                    echo "<table width='100%'>\n";
                    
                    // Table header row
                    echo "<tr><th>Product</th><th>Description</th>" ."<th>Price Each</th><th># in Cart</th>" . "<th>Total Price</th><th>&nbsp;</th></tr>\n";
            
                    // Loop through each item in the inventory to display it
                    foreach ($this->inventory as $ID => $Info) {
                        echo "<tr><td>" . htmlentities($Info['name']) . "</td>\n";
                        
                        echo "<td>" . htmlentities($Info['description']) . "</td>\n";
                        
                        // Display price formatted as currency
                        printf("<td class='currency'>$%.2f</td>\n", $Info['price']);
                        
                        // Display quantity in cart
                        echo "<td class='currency'>" . $this->shoppingCart[$ID] . "</td>\n";
                        
                        // Display total price for this product in the cart
                        printf("<td class='currency'>$%.2f</td>\n", $Info['price'] * $this->shoppingCart[$ID]);
                        
                        // Action links for Add and Remove Item
                        echo "<td><a href='" . $_SERVER['SCRIPT_NAME'] ."?PHPSESSID=" . session_id() . "&ItemToAdd=$ID'>Add " . " Item</a><br />\n";

                        echo "<a href='" . $_SERVER['SCRIPT_NAME'] . "?PHPSESSID=" . session_id() . "&ItemToRemove=$ID'>Remove " . " Item</a></td>\n";
                        
                        // Calculate subtotal
                        $subtotal += ($Info['price'] * $this->shoppingCart[$ID]);
                    }
                    // Subtotal and Empty Cart link row
                    echo "<tr><td colspan='4'>Subtotal</td>\n";
                    
                    printf("<td class='currency'>$%.2f</td>\n", $subtotal);
                    
                    echo "<td><a href='" . $_SERVER['SCRIPT_NAME'] . "?PHPSESSID=" . session_id() . "&EmptyCart=TRUE'>Empty " . " Cart</a></td></tr>\n";
                    
                    echo "</table>";

                    // Checkout link
                    echo "<p><a href='Checkout.php?PHPSESSID=" .session_id() . "&CheckOut=$this->storeID'>Checkout</a></p>\n";
                    $retval = TRUE;
                }
            return($retval);
        }

        /**
         * addItem
         * Increments the quantity of a specific item in the shopping cart by 1.
         * The item ID is retrieved from the 'ItemToAdd' GET parameter.
         *
         * @return void
         */
        private function addItem() {
            $ProdID = $_GET['ItemToAdd'];
            
            if (array_key_exists($ProdID, $this->shoppingCart))
                $this->shoppingCart[$ProdID] += 1;
            }

        /**
         * __wakeup
         * Re-establishes the database connection after the object is retrieved from the session (unserialized).
         * This is necessary because the mysqli object ($DBConnect) cannot be serialized.
         *
         * @return void
         */
            function __wakeup() {
            include("inc_OnlineStoreDB.php");
            $this->DBConnect = $DBConnect;
        }

        /**
         * removeItem
         * Decrements the quantity of a specific item in the shopping cart by 1, ensuring the quantity does not drop below zero.
         * The item ID is retrieved from the 'ItemToRemove' GET parameter.
         *
         * @return void
         */
        private function removeItem() {
            $ProdID = $_GET['ItemToRemove'];
            
                if (array_key_exists($ProdID, $this->shoppingCart))
                    if ($this->shoppingCart[$ProdID] > 0)
                    $this->shoppingCart[$ProdID] -= 1;
            }

        /**
         * emptyCart
         * Resets the quantity of all items in the shopping cart to zero.
         *
         * @return void
         */
            private function emptyCart() {
            foreach ($this->shoppingCart as $key => $value)
                $this->shoppingCart[$key] = 0;
            }

        /**
         * processUserInput
         * Checks the GET superglobal for specific parameters (ItemToAdd, ItemToRemove, EmptyCart) and calls the corresponding private helper method.
         *
         * @return void
         */
            public function processUserInput() {
            if (!empty($_GET['ItemToAdd']))
                $this->addItem();
                
                if (!empty($_GET['ItemToRemove']))
                    $this->removeItem();
                
                    if (!empty($_GET['EmptyCart']))
                    $this->emptyCart();
        }

        /**
         * checkout
         * Processes the final order by inserting the cart contents into the 'orders' database table.
         * It uses the current session ID as the order ID.
         *
         * @return void
         */
        public function checkout() {
            $ProductsOrdered = 0;
            
            foreach($this->shoppingCart as $productID => $quantity) {
            
                if ($quantity > 0) {
                    ++$ProductsOrdered;
                    $SQLstring = "INSERT INTO orders " . " (orderID, productID, quantity) " . " VALUES('" . session_id() . "', " . "'$productID', $quantity)";
                    
                    // NOTE: Using the session ID directly in SQL without proper sanitation (like prepared statements)is a security vulnerability. This should be addressed in production code.
                    $QueryResult = $this->DBConnect->query($SQLstring);
                }
            }
            echo "<p><strong>Your order has been " .
            "recorded.</strong></p>\n";
        }
}

    ?>
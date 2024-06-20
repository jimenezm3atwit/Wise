<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (class_exists('mysqli')) {
    echo "mysqli class exists!";
} else {
    echo "mysqli class does not exist!";
}
?>

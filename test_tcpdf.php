<?php
require_once 'C:\xampp\htdocs\easeestate\tcpdf\tcpdf\tcpdf.php';

if (class_exists('TCPDF')) {
    echo "TCPDF library is loaded successfully!";
} else {
    echo "TCPDF library is NOT loaded. Please check your installation and path.";
}
?>
<?php
try {
    $conn = new PDO('mysql:host=191.252.181.161;dbname=sispro', 'sispro', 'sispro');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
?>
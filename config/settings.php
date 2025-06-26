<?php

include 'config/connection.php';

function GetASF($conn) {
    $query = "SELECT asf_percent FROM ref_asf_percentage WHERE default_asf = 1";
    $result = $conn->query($query);

    if ($result) {
        return $result->fetch_assoc();
    } else {
        return ['asf_percent' => 0.00];
    }
}
<<<<<<< HEAD
=======

function GetVAT($conn) {
    $query = "SELECT vat_percent FROM ref_vat_percentage WHERE default_vat = 1";
    $result = $conn->query($query);

    if ($result) {
        return $result->fetch_assoc();
    } else {
        return ['vat_percent' => 0.00];
    }
}
>>>>>>> 06b95ea615e0c6633d114500752a4081b56d7ba6
?>

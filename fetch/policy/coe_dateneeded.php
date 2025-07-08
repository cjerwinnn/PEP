<?php
include '../../config/connection.php';

// Example query: fetch global lead_days setting from a config/settings table
$result = $conn->query("SELECT policy_days FROM policy_coe_dateneeded WHERE coe_type = 'TRAVEL' LIMIT 1");

if ($row = $result->fetch_assoc()) {
    echo $row['policy_days'];
} else {
    echo 8; // default fallback
}
?>

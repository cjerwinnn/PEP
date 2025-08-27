<?php
include '../config/connection.php';

/**
 * Convert VB BGR color to HTML hex
 * Example: &H00FF00 => #00FF00
 */
function vbColorToHtml($vbColor) {
    if (!$vbColor) return '';
    // Remove &H prefix and convert to integer
    $vbColor = str_replace('&H', '', $vbColor);
    $intColor = hexdec($vbColor);

    // Extract B, G, R
    $b = $intColor & 0xFF;
    $g = ($intColor >> 8) & 0xFF;
    $r = ($intColor >> 16) & 0xFF;

    // Return HTML hex
    return sprintf("#%02X%02X%02X", $r, $g, $b);
}

$sql = "CALL WEB_SHIFTSCHEDULE_LIST(?)";
if ($result = $conn2->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $color = vbColorToHtml($row['shiftcolor']);
        $colorStyle = $color ? "style='background-color: {$color}; color:#fff;'" : "";
        echo "<tr class='schedule-row small' 
                    data-code='{$row['shiftcode']}' 
                    data-start='{$row['shiftstart']}' 
                    data-end='{$row['shiftend']}' 
                    data-desc='{$row['shiftdescription']}' 
                    $colorStyle>
                <td>{$row['shiftcode']}</td>
                <td>{$row['shiftstart']}</td>
                <td>{$row['shiftend']}</td>
                <td>{$row['shiftdescription']}</td>
              </tr>";
    }
    $result->close();
    $conn2->next_result();
}
?>

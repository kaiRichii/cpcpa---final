<?php
include "db.php";

$result = mysqli_query($conn, "SELECT * FROM deleted_table_data ORDER BY id ASC");

$output = "";
while ($row = mysqli_fetch_assoc($result)) {
    $output .= "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['ClientID']}</td>
                    <td>{$row['ClientName']}</td>
                    <td>{$row['TransactionDate']}</td>
                    <td>{$row['QueuePosition']}</td>
                    <td>{$row['RequirementsStatus']}</td>
                    <td>{$row['PaymentStatus']}</td>
                    <td><button onclick='restoreRecord({$row['id']})'>Restore</button></td>
                </tr>";
}

echo $output;
?>

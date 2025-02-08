<?php
include "db.php";
$result = mysqli_query($conn, "SELECT * FROM data_table ORDER BY id ASC");

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['ClientID']}</td>
            <td>{$row['ClientName']}</td>
            <td>{$row['TransactionDate']}</td>
            <td>{$row['QueuePosition']}</td>
            <td>{$row['RequirementsStatus']}</td>
            <td>{$row['PaymentStatus']}</td>
            <td>
                <button onclick=\"openEditModal('{$row['id']}', '{$row['ClientID']}', '{$row['ClientName']}', '{$row['TransactionDate']}', '{$row['QueuePosition']}', '{$row['RequirementsStatus']}', '{$row['PaymentStatus']}')\">Edit</button>
                <a href='javascript:void(0);' onclick='deleteData({$row['id']})'>Delete</a>
            </td>
          </tr>";
}
?>
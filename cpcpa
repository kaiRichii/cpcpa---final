<?php
include "db.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Google Sheets to MySQL - Auto Sync</title>
    <script>
        function fetchData() {
            let xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_data.php", true);
            xhr.onload = function () {
                if (xhr.status == 200) {
                    document.getElementById("dataTable").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        function autoSync() {
            let xhr = new XMLHttpRequest();
            xhr.open("GET", "sync.php", true);
            xhr.send();
        }

        // Auto-sync every 5 seconds
        setInterval(() => {
            autoSync();
            fetchData();
        }, 3000);

        window.onload = fetchData;
        
        function restoreData(id) {
    if (confirm("Are you sure you want to restore this record?")) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "restore.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status == 200) {
                let response = JSON.parse(xhr.responseText); // Parse the JSON response
                alert(response.message); // Show the response message

                // If successful, refresh the table
                if (response.status === "success") {
                    fetchData(); // Refresh the table after restoration
                }
            } else {
                alert("Error occurred while restoring the record");
            }
        };
        xhr.send("restore_id=" + id);
    }
}

        // Function to delete the record
        function deleteData(id) {
        if (confirm("Are you sure you want to delete this record?")) {
            let xhr = new XMLHttpRequest();
            xhr.open("GET", "delete.php?id=" + id, true);
            xhr.onload = function () {
                if (xhr.status == 200) {
                    let response = JSON.parse(xhr.responseText);  // Parse the JSON response
                    alert(response.message); // Show the response message

                    // If successful, refresh the table
                    if (response.status === "success") {
                        fetchData(); // Refresh the table after deletion
                    }
                } else {
                    alert('Error occurred while deleting the record');
                }
            };
            xhr.send();
        }
    }
    </script>
</head>
<body>
    <h2>Live Data from MySQL (Auto-Synced with Google Sheets)</h2>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>ClientID</th>
                <th>ClientName</th>
                <th>TransactionDate</th>
                <th>QueuePosition</th>
                <th>RequirementsStatus</th>
                <th>PaymentStatus</th>
                <th>Actions</th> <!-- Add this line to include the Actions column header -->
            </tr>
        </thead>
        <tbody id="dataTable">
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
                        <a href='edit.php?id={$row['id']}'>Edit</a> | 
                        <a href='javascript:void(0);' onclick='deleteData({$row['id']})'>Delete</a>
                    </td>
                  </tr>";
        }
        ?>
        </tbody>
    </table>

    <h2>Deleted Records</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>ClientID</th>
                <th>ClientName</th>
                <th>TransactionDate</th>
                <th>QueuePosition</th>
                <th>RequirementsStatus</th>
                <th>PaymentStatus</th>
                <th>Deleted At</th>
                <th>Restore</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $result = mysqli_query($conn, "SELECT * FROM deleted_table_data ORDER BY id ASC");
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['ClientID']}</td>
                    <td>{$row['ClientName']}</td>
                    <td>{$row['TransactionDate']}</td>
                    <td>{$row['QueuePosition']}</td>
                    <td>{$row['RequirementsStatus']}</td>
                    <td>{$row['PaymentStatus']}</td>
                    <td>{$row['deleted_at']}</td>

                    <td>
                        <a href='javascript:void(0);' onclick='restoreData({$row['id']})'>Restore</a>

                    </td>
                  </tr>";
        }
        ?>
        </tbody>
    </table>
</body>
</html>
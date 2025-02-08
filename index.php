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
        }, 1000);

        window.onload = fetchData;
        function restoreRecord(restoreId) {
            if (!confirm("Are you sure you want to restore this record?")) return;

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "restore.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function () {
                if (xhr.status === 200) {
                    alert(xhr.responseText);

                    // Remove restored record from the deleted records table
                    let row = document.getElementById("deleted-row-" + restoreId);
                    if (row) row.remove(); 
                }
            };

            xhr.send("restore_id=" + restoreId);
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

    function openEditModal(id, clientID, clientName, transactionDate, queuePosition, requirementsStatus, paymentStatus) {
            document.getElementById("edit_id").value = id;
            document.getElementById("edit_ClientID").value = clientID;
            document.getElementById("edit_ClientName").value = clientName;
            document.getElementById("edit_TransactionDate").value = transactionDate;
            document.getElementById("edit_QueuePosition").value = queuePosition;
            document.getElementById("edit_RequirementsStatus").value = requirementsStatus;
            document.getElementById("edit_PaymentStatus").value = paymentStatus;

            document.getElementById("editModal").style.display = "block";
        }

        function closeEditModal() {
            document.getElementById("editModal").style.display = "none";
        }

        function saveEdit() {
    let id = document.getElementById("edit_id").value;
    let clientID = document.getElementById("edit_ClientID").value;
    let clientName = document.getElementById("edit_ClientName").value;
    let transactionDate = document.getElementById("edit_TransactionDate").value;
    let queuePosition = document.getElementById("edit_QueuePosition").value;
    let requirementsStatus = document.getElementById("edit_RequirementsStatus").value;
    let paymentStatus = document.getElementById("edit_PaymentStatus").value;

    let saveBtn = document.getElementById("saveBtn");
    saveBtn.innerText = "Saving...";
    saveBtn.disabled = true;

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "edit.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function () {
        saveBtn.innerText = "Save";
        saveBtn.disabled = false;

        if (xhr.status == 200) {
            alert(xhr.responseText);
            fetchData(); // Refresh table
            closeEditModal(); // Close modal
        }
    };

    xhr.send(`id=${id}&ClientID=${clientID}&ClientName=${clientName}&TransactionDate=${transactionDate}&QueuePosition=${queuePosition}&RequirementsStatus=${requirementsStatus}&PaymentStatus=${paymentStatus}`);
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
                         <button onclick=\"openEditModal('{$row['id']}', '{$row['ClientID']}', '{$row['ClientName']}', '{$row['TransactionDate']}', '{$row['QueuePosition']}', '{$row['RequirementsStatus']}', '{$row['PaymentStatus']}')\">Edit</button>
                        <a href='javascript:void(0);' onclick='deleteData({$row['id']})'>Delete</a>
                    </td>
                  </tr>";
        }
        ?>
        </tbody>
    </table>
        <!-- Edit Modal -->
<div id="editModal" style="display: none;">
    <h2>Edit Record</h2>
    <form id="editForm">
        <input type="hidden" id="edit_id"> <!-- ID remains hidden -->
        <label>ClientID:</label><input type="text" id="edit_ClientID" disabled><br>
        <label>ClientName:</label><input type="text" id="edit_ClientName" disabled><br>
        <label>TransactionDate:</label><input type="date" id="edit_TransactionDate" disabled><br>
        <label>QueuePosition:</label><input type="text" id="edit_QueuePosition" disabled><br>
       <!-- Dropdown for RequirementsStatus -->
       <label>RequirementsStatus:</label>
        <select id="edit_RequirementsStatus">
            <option value="Pending">Pending</option>
            <option value="Completed">Completed</option>
            <option value="In Progress">In Progress</option>
        </select><br>

        <!-- Dropdown for PaymentStatus -->
        <label>PaymentStatus:</label>
        <select id="edit_PaymentStatus">
            <option value="Pending">Pending</option>
            <option value="Paid">Paid</option>
            <option value="Overdue">Overdue</option>
        </select><br>
        <button id="saveBtn" onclick="saveEdit()">Save</button>
        <button type="button" onclick="closeEditModal()">Cancel</button>
    </form>
</div>
    
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
            echo "<tr id='deleted-row-{$row['id']}'>
            <td>{$row['id']}</td>
            <td>{$row['ClientID']}</td>
            <td>{$row['ClientName']}</td>
            <td>{$row['TransactionDate']}</td>
            <td>{$row['QueuePosition']}</td>
            <td>{$row['RequirementsStatus']}</td>
            <td>{$row['PaymentStatus']}</td>
            <td>{$row['deleted_at']}</td>
            <td>
                <button onclick='restoreRecord({$row['id']})'>Restore</button>
            </td>
          </tr>";    
        }
        ?>
    </tbody>
</table>
</body>
</html>

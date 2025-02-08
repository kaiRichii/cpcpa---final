<?php
include "db.php";

// Google Sheets API details
$spreadsheetId = "1O7MpTiQnRbp65ndnpdlRk6lQqNx4V7S5CuQSRfxs6PA";
$apiKey = "AIzaSyB-5TcVzb2FEjKw0E3iRCDg4eKqKlzWj8w";
$range = "Sheet1!A2:G";

$url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/$range?key=$apiKey";
$response = file_get_contents($url);
$data = json_decode($response, true);

if (!isset($data['values'])) {
    die("No data found in Google Sheets.");
}

// Step 1: Get IDs from Google Sheets
$googleSheetIds = [];
foreach ($data['values'] as $row) {
    if (!empty($row[0])) {
        $googleSheetIds[] = (int)$row[0];  // Store only valid IDs
    }
}

// Step 2: Fetch all existing IDs from MySQL
$existingIds = [];
$result = mysqli_query($conn, "SELECT id FROM data_table");
while ($row = mysqli_fetch_assoc($result)) {
    $existingIds[] = (int)$row['id'];
}

// Step 3: Find missing records (records in MySQL but not in Google Sheets)
$idsToDelete = array_diff($existingIds, $googleSheetIds);

// Step 4: Move missing records to deleted_table_data instead of deleting
if (!empty($idsToDelete)) {
    foreach ($idsToDelete as $id) {
        $record = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM data_table WHERE id = $id"));
        if ($record) {
            // Check if already in deleted table
            $checkDeleted = mysqli_query($conn, "SELECT id FROM deleted_table_data WHERE id = $id");
            if (mysqli_num_rows($checkDeleted) == 0) {
                $query = "INSERT INTO deleted_table_data (id, ClientID, ClientName, TransactionDate, QueuePosition, RequirementsStatus, PaymentStatus, deleted_at)
                          VALUES ('{$record['id']}', '{$record['ClientID']}', '{$record['ClientName']}', '{$record['TransactionDate']}', 
                                  '{$record['QueuePosition']}', '{$record['RequirementsStatus']}', '{$record['PaymentStatus']}', NOW())";
                mysqli_query($conn, $query);
            }
            // Delete from main table
            mysqli_query($conn, "DELETE FROM data_table WHERE id = $id");
        }
    }
}

// Step 5: Insert or update records from Google Sheets
foreach ($data['values'] as $row) {
    // Ensure the row has exactly 7 elements, filling missing ones with empty values
    $row = array_pad($row, 7, "");

    $id = (int)$row[0];
    if ($id === 0) continue; // Skip invalid rows

    $clientID = mysqli_real_escape_string($conn, $row[1] ?? '');
    $clientName = mysqli_real_escape_string($conn, $row[2] ?? '');
    $transactionDate = mysqli_real_escape_string($conn, $row[3] ?? '');
    $queuePosition = (int)($row[4] ?? 0);
    $requirementsStatus = mysqli_real_escape_string($conn, $row[5] ?? '');
    $paymentStatus = mysqli_real_escape_string($conn, $row[6] ?? '');

    // Check if the record exists
    $check = mysqli_query($conn, "SELECT id FROM data_table WHERE id = $id");

    if (mysqli_num_rows($check) > 0) {
        // Update existing record
        $query = "UPDATE data_table 
                  SET ClientID = '$clientID', ClientName = '$clientName', TransactionDate = '$transactionDate', 
                      QueuePosition = '$queuePosition', RequirementsStatus = '$requirementsStatus', PaymentStatus = '$paymentStatus'
                  WHERE id = $id";
    } else {
        // Insert new record
        $query = "INSERT INTO data_table (id, ClientID, ClientName, TransactionDate, QueuePosition, RequirementsStatus, PaymentStatus) 
                  VALUES ($id, '$clientID', '$clientName', '$transactionDate', '$queuePosition', '$requirementsStatus', '$paymentStatus')";
    }

    mysqli_query($conn, $query);
}

// Log sync time
file_put_contents("sync_log.txt", "Last Sync: " . date("Y-m-d H:i:s") . "\n", FILE_APPEND);

echo "Auto-Sync Completed at " . date("Y-m-d H:i:s");
?>

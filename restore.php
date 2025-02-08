<?php
require 'vendor/autoload.php'; // Load Google API Client
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['restore_id'])) {
    $restore_id = (int)$_POST['restore_id'];

    // Fetch record from deleted_table_data
    $result = mysqli_query($conn, "SELECT * FROM deleted_table_data WHERE id = $restore_id");
    $record = mysqli_fetch_assoc($result);

    if ($record) {
        // Insert back into data_table
        $query = "INSERT INTO data_table (id, ClientID, ClientName, TransactionDate, QueuePosition, RequirementsStatus, PaymentStatus)
                  VALUES ('{$record['id']}', '{$record['ClientID']}', '{$record['ClientName']}', '{$record['TransactionDate']}', 
                          '{$record['QueuePosition']}', '{$record['RequirementsStatus']}', '{$record['PaymentStatus']}')";
        mysqli_query($conn, $query);

        // Update Google Sheets
        $googleUpdateSuccess = updateGoogleSheet($record);

        // If successfully added to Google Sheets, remove from deleted_table_data
        if ($googleUpdateSuccess) {
            mysqli_query($conn, "DELETE FROM deleted_table_data WHERE id = $restore_id");
            echo "✅ Record restored successfully and removed from deleted records!";
        } else {
            echo "⚠️ Record restored to database but Google Sheets update failed!";
        }
    } else {
        echo "❌ Record not found!";
    }
}

// Function to update Google Sheets with OAuth
function updateGoogleSheet($record) {
    $spreadsheetId = "1O7MpTiQnRbp65ndnpdlRk6lQqNx4V7S5CuQSRfxs6PA";
    $range = "Sheet1!A2:G";

    $client = new Google_Client();
    $client->setAuthConfig('firm-vortex-449506-e2-8fb3b303f46b.json');
    $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);

    $service = new Google_Service_Sheets($client);

    $values = [[
        $record['id'], $record['ClientID'], $record['ClientName'], 
        $record['TransactionDate'], $record['QueuePosition'], 
        $record['RequirementsStatus'], $record['PaymentStatus']
    ]];

    $body = new Google_Service_Sheets_ValueRange([
        'values' => $values
    ]);

    $params = ['valueInputOption' => 'RAW'];

    try {
        $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
        return true; // Successfully updated Google Sheets
    } catch (Exception $e) {
        return false; // Google Sheets update failed
    }
}

?>

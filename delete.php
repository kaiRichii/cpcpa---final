<?php
require 'vendor/autoload.php'; // Load Google API Client
include "db.php";

header("Content-Type: application/json"); // Ensure JSON response

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $delete_id = (int)$_GET['id'];

    // Check if record exists
    $result = mysqli_query($conn, "SELECT * FROM data_table WHERE id = $delete_id");
    $record = mysqli_fetch_assoc($result);

    if (!$record) {
        echo json_encode(["status" => "error", "message" => "❌ Record not found!"]);
        exit;
    }

    // Move record to deleted_table_data
    $query = "INSERT INTO deleted_table_data (id, ClientID, ClientName, TransactionDate, QueuePosition, RequirementsStatus, PaymentStatus, deleted_at)
              VALUES ('{$record['id']}', '{$record['ClientID']}', '{$record['ClientName']}', '{$record['TransactionDate']}',
                      '{$record['QueuePosition']}', '{$record['RequirementsStatus']}', '{$record['PaymentStatus']}', NOW())";
    
    if (!mysqli_query($conn, $query)) {
        echo json_encode(["status" => "error", "message" => "❌ Failed to move record to deleted records!"]);
        exit;
    }

    // Attempt Google Sheets deletion
    $googleDeleteSuccess = deleteFromGoogleSheet($record['id']);

    // If deleted in Google Sheets, remove from MySQL
    if ($googleDeleteSuccess) {
        $deleteQuery = "DELETE FROM data_table WHERE id = $delete_id";
        
        if (mysqli_query($conn, $deleteQuery)) {
            echo json_encode(["status" => "success", "message" => "✅ Record deleted successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "❌ Database deletion failed: " . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "⚠️ Record moved to deleted records but Google Sheets update failed!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "❌ Invalid request!"]);
}

// Function to delete record from Google Sheets
function deleteFromGoogleSheet($recordId) {
    $spreadsheetId = "1O7MpTiQnRbp65ndnpdlRk6lQqNx4V7S5CuQSRfxs6PA";
    $range = "Sheet1!A2:G"; 

    $client = new Google_Client();
    $client->setAuthConfig('firm-vortex-449506-e2-8fb3b303f46b.json');
    $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);

    $service = new Google_Service_Sheets($client);
    
    try {
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();
        
        if (!empty($values)) {
            foreach ($values as $index => $row) {
                if ($row[0] == $recordId) { 
                    $deleteRange = "Sheet1!A" . ($index + 2) . ":G" . ($index + 2);
                    $clearRequest = new Google_Service_Sheets_ClearValuesRequest();
                    $service->spreadsheets_values->clear($spreadsheetId, $deleteRange, $clearRequest);
                    return true;
                }
            }
        }
    } catch (Exception $e) {
        error_log("Google Sheets API Error: " . $e->getMessage());
        return false;
    }
    return false;
}
?>

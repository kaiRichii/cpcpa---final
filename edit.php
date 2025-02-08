<?php
require 'vendor/autoload.php';
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST["id"];
    $requirementsStatus = mysqli_real_escape_string($conn, $_POST["RequirementsStatus"]);
    $paymentStatus = mysqli_real_escape_string($conn, $_POST["PaymentStatus"]);

    // ✅ Update only the editable fields
    $query = "UPDATE data_table 
              SET RequirementsStatus = '$requirementsStatus', PaymentStatus = '$paymentStatus' 
              WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        // ✅ Optionally Update Google Sheets
        $googleUpdateSuccess = updateGoogleSheet($id, $requirementsStatus, $paymentStatus);

        if ($googleUpdateSuccess) {
            echo "✅ Record updated successfully!";
        } else {
            echo "⚠️ Record updated in database but Google Sheets update failed!";
        }
    } else {
        echo "❌ Database update failed!";
    }
}

// ✅ Function to Update Google Sheets
function updateGoogleSheet($id, $requirementsStatus, $paymentStatus) {
    $spreadsheetId = "1O7MpTiQnRbp65ndnpdlRk6lQqNx4V7S5CuQSRfxs6PA";
    $range = "Sheet1!A2:G"; 

    $client = new Google_Client();
    $client->setAuthConfig('firm-vortex-449506-e2-8fb3b303f46b.json');
    $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);

    $service = new Google_Service_Sheets($client);

    try {
        // ✅ Get existing Google Sheets Data
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        if (!empty($values)) {
            foreach ($values as $index => $row) {
                // ✅ Safeguard against missing array keys
                if (isset($row[0]) && $row[0] == $id) { // Match ID in the first column
                    $updateRange = "Sheet1!F" . ($index + 2) . ":G" . ($index + 2); // Adjust range for editable columns

                    // ✅ Prepare Updated Data
                    $updatedValues = [[
                        $requirementsStatus, $paymentStatus
                    ]];

                    // ✅ Send Update Request
                    $body = new Google_Service_Sheets_ValueRange(['values' => $updatedValues]);
                    $params = ['valueInputOption' => 'RAW'];
                    $service->spreadsheets_values->update($spreadsheetId, $updateRange, $body, $params);

                    return true; // Successfully updated Google Sheets
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

<?php
include "db.php";  // Connect to MySQL

// // Google Sheets API details
// $spreadsheetId = "1O7MpTiQnRbp65ndnpdlRk6lQqNx4V7S5CuQSRfxs6PA";  // Your Google Sheet ID
// $apiKey = "AIzaSyB-5TcVzb2FEjKw0E3iRCDg4eKqKlzWj8w";  // Your Google API Key
// $range = "Sheet1!A2:G";  // Fetch from A2 to last row in column G

// // Fetch data from Google Sheets
// $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/$range?key=$apiKey";
// $response = file_get_contents($url);
// $data = json_decode($response, true);

// if (!isset($data['values'])) {
//     die("No data found in Google Sheets.");
// }

// // Loop through each row from Google Sheets and insert/update in MySQL
// foreach ($data['values'] as $row) {
//     $id = (int)$row[0];
//     $clientID = mysqli_real_escape_string($conn, $row[1]);
//     $clientName = mysqli_real_escape_string($conn, $row[2]);
//     $transactionDate = mysqli_real_escape_string($conn, $row[3]);
//     $queuePosition = (int)$row[4];
//     $requirementsStatus = mysqli_real_escape_string($conn, $row[5]);
//     $paymentStatus = mysqli_real_escape_string($conn, $row[6]);

//     // Check if the record exists
//     $check = mysqli_query($conn, "SELECT id FROM data_table WHERE id = $id");
    
//     if (mysqli_num_rows($check) > 0) {
//         // Update existing record
//         $query = "UPDATE data_table 
//                   SET ClientID = '$clientID', ClientName = '$clientName', TransactionDate = '$transactionDate', 
//                       QueuePosition = '$queuePosition', RequirementsStatus = '$requirementsStatus', PaymentStatus = '$paymentStatus'
//                   WHERE id = $id";
//     } else {
//         // Insert new record
//         $query = "INSERT INTO data_table (id, ClientID, ClientName, TransactionDate, QueuePosition, RequirementsStatus, PaymentStatus) 
//                   VALUES ($id, '$clientID', '$clientName', '$transactionDate', '$queuePosition', '$requirementsStatus', '$paymentStatus')";
//     }

//     mysqli_query($conn, $query);
// }

// echo "Data successfully fetched from Google Sheets and stored in MySQL.";
?>

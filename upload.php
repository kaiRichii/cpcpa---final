<?php
include "db.php";
require "vendor/autoload.php";  // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST["upload"])) {
    $file = $_FILES["excel_file"]["tmp_name"];
    if ($file) {
        $spreadsheet = IOFactory::load($file);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();

        foreach ($sheetData as $index => $row) {
            if ($index == 0) continue; // Skip header row
            
            $ClientID = $row[1];
            $ClientName = $row[2];
            $TransactionDate = date('Y-m-d', strtotime($row[3]));
            $QueuePosition = (int)$row[4];
            $RequirementsStatus = $row[5];
            $PaymentStatus = $row[6];

            $query = "INSERT INTO data_table (ClientID, ClientName, TransactionDate, QueuePosition, RequirementsStatus, PaymentStatus) 
                      VALUES ('$ClientID', '$ClientName', '$TransactionDate', '$QueuePosition', '$RequirementsStatus', '$PaymentStatus')";
            mysqli_query($conn, $query);
        }
        echo "Data imported successfully!";
    } else {
        echo "File upload failed!";
    }
}
header("Location: index.php");
?>

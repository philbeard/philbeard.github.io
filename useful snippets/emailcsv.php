<?php
// array of email addresses
$to = array();

function array2csv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   $df = fopen("/tmp/report_".date('Y-m-d', strtotime("-1 days")).".csv", 'w');
   fputcsv($df, array_keys(reset($array)));
   foreach ($array as $row) {
      fputcsv($df, $row);
   }
   fclose($df);
}

function getData(){
        $return = array();
        $dbDetails = readConfig();
        $table = '';
        $query = "SELECT ";
        $query .= "FROM ";
        $conn = new mysqli($dbDetails[server], $dbDetails[user], $dbDetails[pass], $dbDetails[database]);
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()){
                $return[] = $row;
        }
		$conn->close();
        return $return;
}

function emailCsv($csv_path, $body, $to = 'phil@email.co.uk', $subject = 'CSV Report data', $from = 'support@email.co.uk') {
        $logopt = '';
        $subject = escapeshellarg($subject);
        $attachment_path = escapeshellarg($csv_path);
        file_put_contents("/tmp/email.txt", $body);
        $txtfile = escapeshellarg("/tmp/email.txt");
        $from = escapeshellarg($from);
        foreach($to as $singleEmail){
                $emailto = escapeshellarg($singleEmail);
                `/usr/local/bin/sendEmail $logopt -q -f $from -t $emailto -u $subject -a $attachment_path -o "message-file="$txtfile`;
        }
}

$data = getData();

$body = "Please see attached CSV for " . date('Y-m-d', strtotime("-1 days"));
array2csv($data);

emailCsv("/tmp/report_".date('Y-m-d', strtotime("-1 days")).".csv", $body, $to);


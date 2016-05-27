<?php
/***** LICENSE GOES HERE *****/
include_once 'functions_atl.php';

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
        $start = date('Y-m-d',strtotime("-1 days")) . ' 00:00:00';
        $end = date('Y-m-d',strtotime("-1 days")) . ' 23:59:59';
        $return = array();
        $dbDetails = readConfig();
        $table = 'vicidial_closer_log';
        $query = "SELECT closecallid, lead_id, list_id, campaign_id, call_date, start_epoch, end_epoch, length_in_sec, `status`, phone_code, phone_number, user, comments, processed, queue_seconds, user_group, xfercallid, term_reason, uniqueid, agent_only, queue_position, called_count ";
        $query .= " FROM vicidial_closer_log WHERE call_date > '$start' AND call_date < '$end'";
        $conn = new mysqli($dbDetails[VARDB_server], $dbDetails[VARDB_user], $dbDetails[VARDB_pass], $dbDetails[VARDB_database]);
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()){
                $return[] = $row;
        }
		$conn->close();
        return $return;
}

function emailCsv($csv_path, $body, $to = 'phil@antheus.co.uk', $subject = 'CSV Report data', $from = 'support@antheus.co.uk') {
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

$body = "Please see attached CSV for inbound data for " . date('Y-m-d', strtotime("-1 days"));
array2csv($data);

emailCsv("/tmp/report_".date('Y-m-d', strtotime("-1 days")).".csv", $body, $to);


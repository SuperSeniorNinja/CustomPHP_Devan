<?php

require_once("./config/config.php");

$action = $_POST['action'];
if($action == "" || $action == NULL){
    echo "Action died";
    exit;
}

if($action == "read_export_table") {

    $shift = str_replace("shift", "", $_POST['shift']);
    $date = convert_date_string($_POST['date']);
    $week = date('N', strtotime($date));

    //GET SHIFT SETTING
    $query = "SELECT * FROM {$tblShiftSetting} WHERE date = '{$week}'";
    $result = $db->query($query);
    $shift_row = mysqli_fetch_object($result);
    if($shift_row) {
        $shift_setting = json_decode($shift_row->timeset, true);
        $start = $date. " ". $shift_setting[$shift]['start'].":00";
        $end = $date. " ". $shift_setting[$shift]['end'].":00";
    } else{
        $start = $date." 00:00:00";
        $end = $date." 23:59:59";
    }

    if(strtotime($start) > strtotime($end)) {
        $end = date('Y-m-d H:i:s', strtotime("+1 days", strtotime($end)));
    }



    echo '<table class="table table-bordered">
                <thead>
                <tr>
                    <th>DATE</th>
                    <th>TIME</th>
                    <th>MBR</th>
                    <th>TOOL</th>
                    <th>SHIFT TOTAL</th>
                </tr>
                </thead>
                <tbody>
                ';

    $query = "SELECT * FROM {$tblWipShiftSummary} WHERE StatusTime >= '{$start}' AND StatusTime <= '{$end}'";
    $result = $db->query($query);

    $count = mysqli_num_rows($result);

    if($count > 0 ) {
        while($row= mysqli_fetch_object($result)) {


            $sql = "SELECT * FROM {$tblExportScanData} WHERE WIPShiftIndex = '{$row->WIPShiftIndex}' AND Bookin = 1";
            $res = $db->query($sql);
            $shift_total = mysqli_num_rows($res);

            while($tool= mysqli_fetch_object($res)) {
                echo '<tr>';
                $datetime = explode(" ", $tool->DateTimeStamp);
                echo '<td>'.convert_date_string($datetime[0]).'</td>';
                echo '<td>'.$datetime[1].'</td>';
                echo '<td>'.$tool->Barcode.'</td>';

                $q = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$tool->Barcode}'";
                $r = $db->query($q);
                $barcode = mysqli_fetch_object($r);


                echo '<td>'.$barcode->tool_number.'</td>';
                echo '<td>'.$shift_total.'</td>';
                echo '</tr>';
            }
        }
    } else {
        echo '<tr><td colspan="5" style="text-align: center;"> NO Tool Data</td></tr>';
    }



    echo '
                </tbody>
            </table>';

}



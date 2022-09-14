<?php
require_once("./config/config.php");

if (empty($_SESSION['username'])) {
    echo "Action died";
    exit;
}

$action = $_POST['action'];
if($action == "" || $action == NULL){
    echo "Action died";
    exit;
}

if($action == "change_shift") {
    $change = $_POST['change'];
    $id = $_POST['id'];
    $old_shift = $_POST['old_shift'];

    if($old_shift != "maintool") {
        $wip_query = "SELECT * FROM {$tblWipShiftSummary} ORDER BY StatusTime DESC limit 1";
        $wip_result = $db->query($wip_query);
        $wip = mysqli_fetch_object($wip_result);
        $current_shift = $wip->WIPShiftIndex;

        $differ = $current_shift - $change;
        if($differ < 0) {
            echo "fail";
        } else {
            $query = "SELECT * FROM {$tblWipShiftSummary} WHERE WIPShiftIndex <= '{$differ}' ORDER BY WIPShiftIndex DESC limit 1";
            $result = $db->query($query);
            $change_wip = mysqli_fetch_object($result);
            $change_shift = $change_wip->WIPShiftIndex;
            $shift_time = $change_wip->StatusTime;

            //update export data
            $sql = "UPDATE {$tblExportScanData} SET WIPShiftIndex = {$change_shift}, DateTimeStamp = '{$shift_time}' WHERE id = {$id}";
            $db->query($sql);

            //update shift summary
            $sql = "UPDATE {$tblWipShiftSummary} SET Qty = Qty + 1, BI = BI + 1 WHERE WIPShiftIndex = {$change_shift}";
            $db->query($sql);

            $sql = "UPDATE {$tblWipShiftSummary} SET Qty = Qty - 1, BI = BI - 1 WHERE WIPShiftIndex = {$old_shift}";
            $db->query($sql);

            echo $current_shift;
        }
    } else {
        $sql = "UPDATE {$tblToolMainData} SET priority = '{$change}' WHERE id = {$id}";
        $res = $db->query($sql);
        if($res)
            echo "ok";
        else
            echo "fail";
    }

}

else if($action == "read_barcode") {
    $request_barcode = explode(",", $_POST['barcode']);
    $locations = explode(",", $_POST['tool_location']);
    $shift_id = $_POST['shift_id'];

    $user_id = $_SESSION['userId'];

    foreach($request_barcode as $index => $barcode) {
        //$pre_fix = substr($barcode, 0,1);

        //Check exist same barcode
        $query = "SELECT * FROM {$tblExportScanData} WHERE Barcode='{$barcode}' AND Bookin=1 limit 1";
        $result = $db->query($query);
        $row = mysqli_fetch_object($result);
        if($row) {
            //If same barcode, scan out
            $qty = 1;
            $update = "UPDATE {$tblWipShiftSummary} SET Qty = Qty - {$qty}, BO = BO + {$qty} WHERE WIPShiftIndex = {$shift_id}";
            $update_result = $db->query($update);

            $update = "UPDATE {$tblExportScanData} SET Bookin = 0, left_time = 0, updated_left_time = '{$current}', booked_out_user = {$user_id} WHERE id = {$row->id}";
            $result = $db->query($update);

        } else {
            $query = "SELECT * FROM {$tblToolMainData} WHERE machine_number='{$barcode}' limit 1";
            $result = $db->query($query);
            $row = mysqli_fetch_object($result);

            if(count($row)>0) {

                $checked_query = "SELECT DateTimeStamp, Bookin FROM {$tblExportScanData} WHERE Barcode='{$barcode}' ORDER BY DateTimeStamp DESC Limit 1";
                $checked_result = $db->query($checked_query);
                $checked_row = mysqli_fetch_object($checked_result);

                //$location = $row->tool_location;
                if(isset($locations[$index])) {
                    $location = $locations[$index];
                }

                //$boxes = $row->Boxes;
                //$toolNo = $row->PartNo;
                $qty = 1;

                if(count($checked_row)==0 || $checked_row->Bookin == 0){
                    //Get Hr
                    if(count($checked_row)>0) {
                        $old_time = $checked_row->DateTimeStamp;
                        $hrs = round((strtotime($current) - strtotime($old_time))/3600, 0);
                    }
                    else {
                        $hrs = 0;
                    }

                    //insert export scan data table
                    $current_date = date('Y-m-d');

                    //get left time
                    $left_time = 0;
                    if($row->override_time !="" && $row->override_time != null && $row->override_time != 0) {
                        $left_time = $row->override_time * 60;
                    } else {
                        if($row->priority == 0) {
                            $left_time = 480 * 60;
                        }

                        if($row->priority==2) {
                            $left_time = 960 * 60;
                        }

                        if($row->priority==3) {
                            $left_time = 1440 * 60;
                        }

                        if($row->priority==4) {
                            $left_time = 2880 * 60;
                        }

                        if($row->priority==6) {
                            $left_time = 5760 * 60;
                        }

                        if($row->priority==12) {
                            $left_time = 6640 * 60 ;
                        }
                    }


                    //$left_mins = floor($left_mins / 60);
                    $insert = "INSERT INTO {$tblExportScanData} (Location, Barcode, DateTimeStamp, Date, WIPShiftIndex, Hrs, Bookin, Qty, booked_in_user, booked_out_user, left_time, updated_left_time) VALUES ('{$location}', '{$barcode}', '{$current}', '{$current_date}', '{$shift_id}', '{$hrs}',1, {$qty}, {$user_id}, 0, $left_time, '{$current}')";
                    $insert_result = $db->query($insert);
                    $export_id = $db->insert_id;

                    //update shift table
                    $update = "UPDATE {$tblWipShiftSummary} SET Qty = Qty + 1, WIP_8 = WIP_8 +1, BI = BI + 1 WHERE WIPShiftIndex = {$shift_id}";

                    $update_result = $db->query($update);
                }
            }
        }


    }

    $hr_query = "SELECT * FROM {$tblWipShiftSummary} WHERE WIPShiftIndex = {$shift_id}";
    $hr_result = $db->query($hr_query);
    $shift = mysqli_fetch_object($hr_result);

    echo json_encode($shift);
    mysqli_close($db);
}

else if($action == "book_out") {
    $id = $_POST['entered_id'];
    $shift_id = $_POST['shift_id'];
    $barcode = $_POST['barcode'];

    $user_id = $_SESSION['userId'];

    $query = "SELECT * FROM {$tblToolMainData} WHERE machine_number='{$barcode}' limit 1";
    $result = $db->query($query);
    $row = mysqli_fetch_object($result);
    $qty = 1;

    $update = "UPDATE {$tblWipShiftSummary} SET Qty = Qty - {$qty}, BO = BO + {$qty} WHERE WIPShiftIndex = {$shift_id}";

    $update_result = $db->query($update);

    $update = "UPDATE {$tblExportScanData} SET Bookin = 0, left_time = 0, updated_left_time = '{$current}', booked_out_user = '{$user_id}' WHERE id = {$id}";
    $result = $db->query($update);
    if($result){
        echo "ok_".$hr;
    }

    else
        echo "fail";
    mysqli_close($db);
}

else if($action == "read_priority_table") {

    $shift_id = $_POST['shift_id'];
    $shift_end_time = $_POST['end_time'];

    $wip_query = "SELECT * FROM {$tblWipShiftSummary} WHERE WIPShiftIndex = '{$shift_id}' ";
    $wip_result = $db->query($wip_query);
    $wip = mysqli_fetch_object($wip_result);

    $this_shift_id = $wip->WIPShiftIndex;
    $this_shift_time = $wip->StatusTime;

    $query = "SELECT a.id, a.DateTimeStamp, a.Date, a.WIPShiftIndex, a.Barcode, a.left_time, a.updated_left_time, b.machine, b.machine_number, b.tool_number, a.Location as tool_location, a.Bookin, b.priority 
                FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.machine_number 
                WHERE a.Bookin = 1 ORDER BY b.priority ASC";
    $result = $db->query($query);

    echo "<table class='table table-striped'  id='p_table'>
            <thead>
            <tr>
                <th style='width: 30%; text-align: left;'>Machine Number</th>
                <th style='width: 25%; text-align: left;'>Tool Location</th>
                <th style='width: 15%; text-align: left;'>Checked In</th>
                <th style='width: 15%; text-align: left;'>Time Left</th>
                <th style='width: 15%; text-align: center'>Tool Priority</th>
            </tr>
            </thead>
            <tbody>";

    $scanned_barcode = array();
    $left_mins_array = array();


    while($row=mysqli_fetch_array($result)){
        $scanned_barcode[$row['Barcode']]['id'] = $row['id'];
        $scanned_barcode[$row['Barcode']]['barcode'] = $row['Barcode'];
        $scanned_barcode[$row['Barcode']]['tool_location'] = $row['tool_location'];
        $scanned_barcode[$row['Barcode']]['DateTimeStamp'] = $row['DateTimeStamp'];

        $left_mins = $row['left_time'];
        $color = "#ECECEC";

        if(isset($row['priority']) && $row['priority']==0) {
            $color = "#ff0000";
        }

        if($row['priority']==2) {
            $color = "#da029a";
        }

        if($row['priority']==3) {
            $color = "#004eff";
        }

        if($row['priority']==4) {
            $color = "#ff8400";
        }

        if($row['priority']==6) {
            $color = "#fff000";
        }

        if($row['priority']==12) {
            $color = "#00ff0c";
        }

        $weekend_times = 0;

        if($count_down == 0) {
            $c_data = get_shift_date_number($current);
            $u_data = get_shift_date_number($row['updated_left_time']);

            $c_date = $c_data['date'];
            $u_date = $u_data['date'];

            $c_shift = $c_data['shift'];
            $u_shift = $u_data['shift'];

            if($c_date == $u_date) {
                if($c_shift == $u_shift && $c_shift != 0) {
                    $left_mins -= (strtotime($current) - strtotime($row['updated_left_time']));
                } else {
                    if($u_shift != 0) {
                        $start_shift = $u_shift;
                        $last_time = $row['updated_left_time'];
                        while($start_shift <= $c_shift) {
                            if($start_shift == $c_shift) {
                                $left_mins -= (strtotime($current) - strtotime($last_time));
                            } else {
                                $timeset = get_start_end_time($u_date, "shift".$start_shift);
                                $start = explode(" ", $timeset['start']);
                                $end = explode(" ", $timeset['end']);
                                if($start[1] != "00:00:00" || $end[1] != "00:00:00") {
                                    $left_mins -= (strtotime($timeset['end']) - strtotime($last_time));
                                    $last_time = $timeset['end'];
                                }
                            }
                            $start_shift ++;
                        }
                    }
                }
            } else {
                if($c_date > $u_date){
                    $start_date = $u_date;
                    $last_time = $row['updated_left_time'];
                    $end_shift = get_end_shift($start_date);
                    while($start_date <= $c_date){
                        if($start_date == $u_date && $u_shift != 0)
                            $start_shift = $u_shift;
                        else
                            $start_shift = 1;

                        if($start_date == $c_date)
                            $end_shift = $c_shift;

                        while($start_shift <= $end_shift) {
                            if($start_shift == $end_shift && $start_date == $c_date) {
                                $left_mins -= (strtotime($current) - strtotime($last_time));
                            } else {
                                $timeset = get_start_end_time($u_date, "shift".$start_shift);
                                $start = explode(" ", $timeset['start']);
                                $end = explode(" ", $timeset['end']);
                                if($start[1] != "00:00:00" || $end[1] != "00:00:00") {
                                    $left_mins -= (strtotime($timeset['end']) - strtotime($last_time));
                                    $last_time = $timeset['end'];
                                }
                            }

                            $start_shift ++;
                        }

                        $start_date = date('Y-m-d', strtotime("+1 days", strtotime($start_date)));
                    }
                }
            }
        }

        $u_id =  $row['id'];

        if($weekToday < 6) {
            $update_sql = "UPDATE {$tblExportScanData} SET left_time = '{$left_mins}', updated_left_time = '{$current}' WHERE id = $u_id";
            $db->query($update_sql);
            $left_mins += $weekend_times;
        }

        if($left_mins < 0 ){
            $update_sql = "UPDATE {$tblExportScanData} SET OldDateTime = '{$current}' WHERE id = $u_id";
            $db->query($update_sql);
            $left_mins = 0;
        }

        $left_mins = floor($left_mins / 60);

        if($left_mins < 0) {
            $left_mins = 0;
        }

        $scanned_barcode[$row['Barcode']]['left_mins'] = $left_mins;
        $scanned_barcode[$row['Barcode']]['color'] = $color;
        $left_mins_array[$row['Barcode']] = $left_mins;
    }

    asort($left_mins_array);

    $barcodes = array();

    $i = 0;
    foreach ($left_mins_array as $key => $data) {
        if($i < 3)
            echo"<tr id='barcode".$scanned_barcode[$key]['id']."' style='background-color: #ff0000; color:#000;' >";
        else
            echo"<tr id='barcode".$scanned_barcode[$key]['id']."' >";

        echo"<td class='barcode'>".$scanned_barcode[$key]['barcode']."</td>";
        echo"<td class='tool-location'>".$scanned_barcode[$key]['tool_location']."</td>";
        echo"<td style='vertical-align: middle;'>".date("d/m/Y, H:i:s", strtotime($scanned_barcode[$key]['DateTimeStamp']))."</td>";

        echo"<td style='vertical-align: middle;'><span class='left-time'>".$data."</span> MINS</td>";
        echo"<td style='vertical-align: middle;'><div class='priority' style='background-color: ".$scanned_barcode[$key]['color']."; height: 20px; width: 70px; margin: 0 auto;'></div></td>";
        echo"</tr>";
        $i++;

        array_push($barcodes, $row['Barcode']);
    }

    echo"</tbody></table>";
    echo "<input type='hidden' name='tmp_scanned_barcode' id='tmp_scanned_barcode' value='".implode($barcodes,",")."'>";


} else if($action == "read_export_table") {

    $shift_id = $_POST['shift_id'];

    $wip_query = "SELECT * FROM {$tblWipShiftSummary} WHERE WIPShiftIndex = '{$shift_id}' ";
    $wip_result = $db->query($wip_query);
    $wip = mysqli_fetch_object($wip_result);

    $this_shift_id = $wip->WIPShiftIndex;
    $this_shift_time = $wip->StatusTime;

    $query = "SELECT a.id, a.DateTimeStamp, a.Barcode, b.machine, b.machine_number, b.tool_number, a.Location as tool_location, a.WIPShiftIndex, a.Hrs, a.Bookin, b.priority 
                FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.machine_number 
                WHERE a.Bookin = 1 ORDER BY b.priority ASC";

    $result = $db->query($query);
    $scanned_barcode = [];
    while($row=mysqli_fetch_array($result)){
        echo"<tr id='barcode".$row['id']."'>";
        echo"<td style='text-align: left;'>".$row['Barcode']."</td>";
        echo"<td style='text-align: left;'>".$row['tool_location']."</td>";
        echo"<td style='text-align: left;'>".$row['DateTimeStamp']."</td>";

        $color = "#ECECEC";

        if(isset($row['priority']) && $row['priority']==0) {
            $color = "#ff0000";
        }

        if($row['priority']==2) {
            $color = "#da029a";
        }

        if($row['priority']==3) {
            $color = "#004eff";
        }

        if($row['priority']==4) {
            $color = "#ff8400";
        }

        if($row['priority']==6) {
            $color = "#fff000";
        }

        if($row['priority']==12) {
            $color = "#00ff0c";
        }

        echo"<td><div style='background-color: ".$color."; height: 20px; width: 80px; margin: 0 auto;'></div></td>";
        echo"<td><a class='book-out' style='cursor: pointer' id='book".$row['id']."' data-shift='".$row['WIPShiftIndex']."'>Book Out</a></td>";
        echo"</tr>";
        array_push($scanned_barcode, $row['Barcode']);
    }
    echo "<input type='hidden' name='tmp_scanned_barcode' id='tmp_scanned_barcode' value='".implode($scanned_barcode,",")."'>";

} else if($action == "get_scanned_barcode") {
    $query = "SELECT Barcode FROM {$tblExportScanData} WHERE Bookin = 1";
    $result = $db->query($query);
    $barcode = [];
    while ($row = mysqli_fetch_array($result)){
        array_push($barcode, $row['Barcode']);
    }

    echo json_encode($barcode);
} else if($action == "create_next_shift") {
    $wip_query = "SELECT * FROM {$tblWipShiftSummary} ORDER BY WIPShiftIndex DESC limit 1";
    $wip_result = $db->query($wip_query);
    $wip = mysqli_fetch_object($wip_result);

    //$carried_forward = $wip->BI;

    $q1 = "SELECT * FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.machine_number WHERE a.Bookin = 1 AND b.priority = 0";
    $r1 = $db->query($q1);
    $this_shift = mysqli_num_rows($r1);

    $q2 = "SELECT * FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.machine_number WHERE a.Bookin = 1 AND b.priority = 2";
    $r2 = $db->query($q2);
    $two_shift = mysqli_num_rows($r2);

    $q3 = "SELECT * FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.machine_number WHERE a.Bookin = 1 AND b.priority = 3";
    $r3 = $db->query($q3);
    $three_shift = mysqli_num_rows($r3);

    $q4 = "SELECT * FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.machine_number WHERE a.Bookin = 1 AND b.priority = 4";
    $r4 = $db->query($q4);
    $four_six_shift = mysqli_num_rows($r4);

    $q12 = "SELECT * FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.machine_number WHERE a.Bookin = 1 AND b.priority = 6";
    $r12 = $db->query($q12);
    $six_twelve_shift = mysqli_num_rows($r12);

    $q5 = "SELECT * FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.machine_number WHERE a.Bookin = 1 AND b.priority = 12";
    $r5 = $db->query($q5);
    $five_days_shift = mysqli_num_rows($r5);

    $carried_forward = $this_shift + $two_shift + $three_shift + $four_six_shift + $six_twelve_shift + $five_days_shift;

    $insert = "INSERT INTO {$tblWipShiftSummary} (StatusTime, Qty, WIP_8, WIP_8_12, WIP_12, BI, BO, Shift, Carried_forward) 
                                          VALUES ('{$current}',0,0,0,0,0,0,'','{$carried_forward}')";
    $result = $db->query($insert);

    if($result) {
        echo "ok";
    }

} else if($action == "read_change_frequency") {

    $q1 = "SELECT * FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.machine_number WHERE a.Bookin = 1 AND b.priority = 0";
    $r1 = $db->query($q1);
    $data['this_shift'] = mysqli_num_rows($r1);

    $q2 = "SELECT * FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.machine_number WHERE a.Bookin = 1 AND b.priority = 2";
    $r2 = $db->query($q2);
    $data['two_shift'] = mysqli_num_rows($r2);

    $q3 = "SELECT * FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.machine_number WHERE a.Bookin = 1 AND b.priority = 3";
    $r3 = $db->query($q3);
    $data['three_shift'] = mysqli_num_rows($r3);

    $q4 = "SELECT * FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.machine_number WHERE a.Bookin = 1 AND b.priority = 4";
    $r4 = $db->query($q4);
    $data['four_six_shift'] = mysqli_num_rows($r4);

    $q12 = "SELECT * FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.machine_number WHERE a.Bookin = 1 AND b.priority = 6";
    $r12 = $db->query($q12);
    $data['six_twelve_shift'] = mysqli_num_rows($r12);

    $q5 = "SELECT * FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.machine_number WHERE a.Bookin = 1 AND b.priority = 12";
    $r5 = $db->query($q5);
    $data['five_days_shift'] = mysqli_num_rows($r5);

    echo json_encode($data, true);
} else if($action == "shift_setting") {

    $start1 = $_POST['start1'];
    $end1 = $_POST['end1'];

    $start2 = $_POST['start2'];
    $end2 = $_POST['end2'];

    $start3 = $_POST['start3'];
    $end3 = $_POST['end3'];
    $res = true;
    for($i=0; $i<7; $i++) {

        if(strlen($start1[$i]) < 5)
            $start1[$i] = "0".$start1[$i];

        if(strlen($start2[$i]) < 5)
            $start2[$i] = "0".$start2[$i];

        if(strlen($start3[$i]) < 5)
            $start3[$i] = "0".$start3[$i];

        if(strlen($end1[$i]) < 5)
            $end1[$i] = "0".$end1[$i];

        if(strlen($end2[$i]) < 5)
            $end2[$i] = "0".$end2[$i];

        if(strlen($end3[$i]) < 5)
            $end3[$i] = "0".$end3[$i];


        $data[1]['start'] = $start1[$i];
        $data[1]['end'] = $end1[$i];

        $data[2]['start'] = $start2[$i];
        $data[2]['end'] = $end2[$i];

        $data[3]['start'] = $start3[$i];
        $data[3]['end'] = $end3[$i];

        $timeset = json_encode($data, true);
        $date = $i+1;
        $sql = "UPDATE {$tblShiftSetting} SET timeset = '{$timeset}' WHERE date = {$date}";
        $res = $db->query($sql);
    }

    if($res) {
        echo "ok";
    } else {
        echo "fail";
    }
}

else if($action == "update_tool") {
    $tool_id = $_POST['tool_id'];
    $machine = $_POST['machine'];
    $machine_number = $_POST['machine_number'];
    $tool_number = $_POST['tool_number'];
    $old_machine_number = $_POST['old_machine_number'];
    $tool_location = $_POST['tool_location'];
    $override_time = $_POST['override_time'];

    if($tool_id != 0) {
        $sql = "SELECT * FROM {$tblToolMainData} WHERE id !={$tool_id} AND machine_number = '{$machine_number}'";
        $res = $db->query($sql);
        $count = mysqli_num_rows($res);

        if($count == 0) {
            $update = "UPDATE {$tblToolMainData} SET machine = '{$machine}', machine_number = '{$machine_number}', tool_number = '{$tool_number}', tool_location = '{$tool_location}', override_time = '{$override_time}' WHERE id = {$tool_id}";
            $update_result = $db->query($update);
            if(!$update_result) {
                echo "fail";
            } else {
                $query = "SELECT * FROM {$tblExportScanData} WHERE Barcode = '{$old_machine_number}'";
                $res = $db->query($query);
                $tool = mysqli_fetch_object($res);
                if($tool) {
                    $update = "UPDATE {$tblToolMainData} SET tool_location = '{$tool_location}', machine_number = '{$machine_number}' WHERE id = {$tool->id}";
                    $update_result = $db->query($update);
                }
                echo "ok";
            }
        } else{
            echo "same";
        }

    } else {
        $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$machine_number}'";
        $res = $db->query($sql);
        $count = mysqli_num_rows($res);
        if($count == 0) {
            $insert = "INSERT INTO {$tblToolMainData} (machine, machine_number, tool_number, tool_location, override_time) VALUES ('{$machine}','{$machine_number}','{$tool_number}','{$tool_location}', {$override_time})";
            $result = $db->query($insert);
            if(!$result) {
                echo "fail";
            } else {
                echo "ok";
            }
        } else{
            echo "same";
        }
    }
}

else if($action == "read_report_table") {

    $shift = str_replace("shift", "", $_POST['shift']);
    $date = convert_date_string($_POST['date']);
    $week = date('N', strtotime($date));

    $scan_filter = $_POST['scan_filter'];

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
                    <th>SCANNED IN/OUT</th>
                </tr>
                </thead>
                <tbody>
                ';

    $query = "SELECT * FROM {$tblWipShiftSummary} WHERE StatusTime >= '{$start}' AND StatusTime <= '{$end}'";
    $result = $db->query($query);

    $count = mysqli_num_rows($result);

    if($count > 0 ) {
        while($row= mysqli_fetch_object($result)) {

            if($scan_filter == "all") {
                $sql = "SELECT * FROM {$tblExportScanData} WHERE WIPShiftIndex = '{$row->WIPShiftIndex}'";
            } else if( $scan_filter == "in") {
                $sql = "SELECT * FROM {$tblExportScanData} WHERE WIPShiftIndex = '{$row->WIPShiftIndex}' AND Bookin = 1";
            } else {
                $sql = "SELECT * FROM {$tblExportScanData} WHERE WIPShiftIndex = '{$row->WIPShiftIndex}' AND Bookin = 0";
            }


            $res = $db->query($sql);
            $shift_total = mysqli_num_rows($res);

            while($tool= mysqli_fetch_object($res)) {
                echo '<tr>';
                $datetime = explode(" ", $tool->DateTimeStamp);
                echo '<td style="text-align: center">'.convert_date_string($datetime[0]).'</td>';
                echo '<td style="text-align: center">'.$datetime[1].'</td>';

                $q = "SELECT * FROM {$tblUsers} WHERE id = '{$tool->booked_in_user}' OR id = '{$tool->booked_out_user}'";
                $r = $db->query($q);
                $user = mysqli_fetch_object($r);

                if($user)
                    echo '<td>'.$user->username.'</td>';
                else
                    echo '<td></td>';

                echo '<td>'.$tool->Barcode.'</td>';
                echo '<td style="text-align: center">'.$shift_total.'</td>';
                if($tool->Bookin == 1)
                    echo '<td style="text-align: center">In</td>';
                else
                    echo '<td style="text-align: center">Out</td>';
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

else if($action == "update_count_down") {
    $value = $_POST['kind'];
    $update = "UPDATE {$tblCountDown} SET count_down = $value";
    $result = $db->query($update);
    if($result)
        echo "ok";
    else
        echo "fail";
}

else if($action == "save_report") {
    $report_name = $_POST['report_name'];
    $report_type = $_POST['type_graph_data'];

    //Sections
    $sections = "";
    if(isset($_POST['all_sections']) && $_POST['all_sections'] == 1) {
        $sections = "all";
    } else {
        if(isset($_POST['section1']) && $_POST['section1'] == 1) {
            $sections = "section1";
        }

        if(isset($_POST['section2']) && $_POST['section2'] == 1) {
            $sections .= ",section2";
        }

        if(isset($_POST['section3']) && $_POST['section3'] == 1) {
            $sections .= ",section3";
        }

        if(isset($_POST['section4']) && $_POST['section4'] == 1) {
            $sections .= ",section4";
        }

        if(isset($_POST['section5']) && $_POST['section5'] == 1) {
            $sections .= ",section5";
        }

        if(isset($_POST['section6']) && $_POST['section6'] == 1) {
            $sections .= ",section6";
        }

        if(isset($_POST['section7']) && $_POST['section7'] == 1) {
            $sections .= ",section7";
        }
    }

    //Members
    $include_members = $_POST['include_members'];
    if($include_members == "custom_members") {
        $members = implode(",", $_POST['select_members']);
    } else {
        $members ="all";
    }


    //Tools
    $tools = "";
    if(isset($_POST['all_tools']) && $_POST['all_tools'] == 1) {
        $tools = "all";
    } else {
        if(isset($_POST['red_tool']) && $_POST['red_tool'] == 1) {
            $tools = "red,";
        }

        if(isset($_POST['purple_tool']) && $_POST['purple_tool'] == 1) {
            $tools .= "purple,";
        }

        if(isset($_POST['blue_tool']) && $_POST['blue_tool'] == 1) {
            $tools .= "blue,";
        }

        if(isset($_POST['orange_tool']) && $_POST['orange_tool'] == 1) {
            $tools .= "orange,";
        }

        if(isset($_POST['yellow_tool']) && $_POST['yellow_tool'] == 1) {
            $tools .= "yellow,";
        }

        if(isset($_POST['green_tool']) && $_POST['green_tool'] == 1) {
            $tools .= "green,";
        }

        if(isset($_POST['custom_tool']) && $_POST['custom_tool'] == 1) {
            $tools = implode(",", $_POST['select_tools']);
        }
    }

    //Section2 Options
    if(isset($_POST['booked_in_2']) && $_POST['booked_in_2'] == 1) {
        $booked_in_2 = 1;
    } else {
        $booked_in_2 = 0;
    }

    if(isset($_POST['booked_out_2']) && $_POST['booked_out_2'] == 1) {
        $booked_out_2 = 1;
    } else {
        $booked_out_2 = 0;
    }

    //Section3 Options
    if(isset($_POST['hide_graph_3']) && $_POST['hide_graph_3'] == 1) {
        $hide_graph_3 = 1;
    } else {
        $hide_graph_3 = 0;
    }

    if(isset($_POST['hide_list_3']) && $_POST['hide_list_3'] == 1) {
        $hide_list_3 = 1;
    } else {
        $hide_list_3 = 0;
    }

    //Section4 Options
    if(isset($_POST['hide_list_4']) && $_POST['hide_list_4'] == 1) {
        $hide_list_4 = 1;
    } else {
        $hide_list_4 = 0;
    }

    //Section5 Options
    if(isset($_POST['booked_in_5']) && $_POST['booked_in_5'] == 1) {
        $booked_in_5 = 1;
    } else {
        $booked_in_5 = 0;
    }

    if(isset($_POST['booked_out_5']) && $_POST['booked_out_5'] == 1) {
        $booked_out_5 = 1;
    } else {
        $booked_out_5 = 0;
    }


    $report_id = $_POST['report_id'];

    if($report_id != 0) {
        //Update Report
        $query = "UPDATE {$tblReports} SET report_name = '{$report_name}', report_type = '{$report_type}', sections = '{$sections}', members = '{$members}', tools = '{$tools}', booked_in_2 = '{$booked_in_2}', 
                  booked_out_2 = '{$booked_out_2}', hide_graph_3 = '{$hide_graph_3}', hide_list_3 = '{$hide_list_3}', hide_list_4 = '{$hide_list_4}', booked_in_5 = '{$booked_in_5}', booked_out_5 = '{$booked_out_5}' WHERE id = {$report_id}";
    } else {
        //Insert Report
        $query = "INSERT INTO {$tblReports} (report_name, report_type, sections, members, tools, booked_in_2, booked_out_2, hide_graph_3, hide_list_3, hide_list_4, booked_in_5, booked_out_5) 
                  VALUES ('{$report_name}', '{$report_type}', '{$sections}', '{$members}', '{$tools}', '{$booked_in_2}', '{$booked_out_2}', '{$hide_graph_3}', '{$hide_list_3}', '{$hide_list_4}', '{$booked_in_5}', '{$booked_out_5}')";
    }

    $result = $db->query($query);

    if($result) {
        echo "ok";
    } else {
        echo "fail";
    }

}

else if($action == "read_all_reports") {
    $query = "SELECT * FROM {$tblReports}";
    $result = $db->query($query);
    echo "<table class='table table-striped'>";
    while($row = mysqli_fetch_object($result)) {
        echo "<tr>";
        echo "<td style='border-top: 0px; text-align: left'>".$row->report_name."</td>";
        echo "<td style='border-top: 0px;'>";
        echo "<button class='btn btn-primary report-select' id='select_".$row->id."' 
                data-report='".$row->report_name."' 
                data-report_type='".$row->report_type."' 
                data-sections='".$row->sections."'
                data-members='".$row->members."'
                data-tools='".$row->tools."'
                data-booked_in_2='".$row->booked_in_2."'
                data-booked_out_2='".$row->booked_out_2."'
                data-hide_graph_3='".$row->hide_graph_3."'
                data-hide_list_3='".$row->hide_list_3."'
                data-hide_list_4='".$row->hide_list_4."'
                data-booked_in_5='".$row->booked_in_5."'
                data-booked_out_5='".$row->booked_out_5."'>SELECT</button>";
        echo "&nbsp;&nbsp;&nbsp;<button class='btn btn-danger report-delete' id='delete_".$row->id."'>DELETE</button></td>";
        echo "</tr>";

    }
    echo "</table>";
}

else if($action == "delete_report") {
    if($_POST['report_id']) {
        $report_id = $_POST['report_id'];
        $query = "DELETE FROM {$tblReports} WHERE id = {$report_id}";
        $result = $db->query($query);
        if($result) {
            echo "ok";
        } else{
            echo $query;
        }
    } else{
        echo "fail";
    }
}

else if($action == "get_report_section2") {
    $report_start_date = $_POST['start_date'];
    $report_end_date = $_POST['end_date'];
    $shift = $_POST['shift'];
    $data = get_report_section2_data($report_start_date, $report_end_date, $shift);
    $g_data = json_encode($data, true);
    echo $g_data;
}

else if($action == "get_report_section3")
{

    $report_start_date = $_POST['start_date'];
    $report_end_date = $_POST['end_date'];
    $shift = $_POST['shift'];

    $data = array();

    if($report_start_date == $report_end_date) {
        if($shift != 'all_shift') {
            $time_set = get_start_end_time($report_start_date, $shift);
            $start_time = $time_set['start'];
            $end_time = $time_set['end'];
        } else {
            $time_set0 = get_start_end_time($report_start_date, "shift1");
            $time_set1 = get_start_end_time($report_end_date, "shift3");
            $start_time = $time_set0['start'];
            $end_time = $time_set1['end'];
        }

        $query = "SELECT * FROM {$tblToolMainData}";
        $result = $db->query($query);
        while($row = mysqli_fetch_object($result)) {
            $tool = $row->machine_number;
            $sql = "SELECT * FROM {$tblExportScanData} WHERE Barcode = '{$tool}' AND OldDateTime >= '{$start_time}' AND OldDateTime <= '{$end_time}' ORDER BY DateTimeStamp DESC limit 1";
            $res = $db->query($sql);
            $item = mysqli_fetch_object($res);
            if($item) {
                switch ($row->priority) {
                    case '0': $color = "#ff0000"; break;
                    case '2': $color = "#da029a"; break;
                    case '3': $color = "#004eff"; break;
                    case '4': $color = "#ff8400"; break;
                    case '6': $color = "#fff000"; break;
                    case '12': $color = "#00ff0c"; break;
                    default: $color = ""; break;
                }

                if($item->booked_out_user > 0) {
                    array_push($data, [
                        "tool" => $tool,
                        "color" => $color,
                        "booked_in" => date("d/m/Y H:i", strtotime($item->DateTimeStamp)),
                        "booked_out" => date("d/m/Y H:i", strtotime($item->updated_left_time)),
                        "duration" => ceil(abs(strtotime($item->DateTimeStamp) - strtotime($item->updated_left_time)) / 3600),
                    ]);
                } else {
                    array_push($data, [
                        "tool" => $tool,
                        "color" => $color,
                        "booked_in" => date("d/m/Y H:i", strtotime($item->DateTimeStamp)),
                        "booked_out" => '',
                        "duration" => ceil(abs(strtotime($item->DateTimeStamp) - strtotime($item->updated_left_time)) / 3600),
                    ]);
                }
            }
        }

    } else {
        $end_date = $report_start_date;

        while(strtotime($end_date) <= strtotime($report_end_date)) {

            if($shift != 'all_shift') {
                $time_set = get_start_end_time($end_date, $shift);
                $start_time = $time_set['start'];
                $end_time = $time_set['end'];
            } else {
                $time_set0 = get_start_end_time($end_date, "shift1");
                $time_set1 = get_start_end_time($end_date, "shift3");
                $start_time = $time_set0['start'];
                $end_time = $time_set1['end'];
            }

            $query = "SELECT * FROM {$tblToolMainData}";
            $result = $db->query($query);
            while($row = mysqli_fetch_object($result)) {
                $tool = $row->machine_number;
                $sql = "SELECT * FROM {$tblExportScanData} WHERE Barcode = '{$tool}' AND OldDateTime >= '{$start_time}' AND OldDateTime <= '{$end_time}' ORDER BY DateTimeStamp DESC limit 1";
                $res = $db->query($sql);
                $item = mysqli_fetch_object($res);
                if($item) {
                    switch ($row->priority) {
                        case '0': $color = "#ff0000"; break;
                        case '2': $color = "#da029a"; break;
                        case '3': $color = "#004eff"; break;
                        case '4': $color = "#ff8400"; break;
                        case '6': $color = "#fff000"; break;
                        case '12': $color = "#00ff0c"; break;
                        default: $color = ""; break;
                    }

                    if($item->booked_out_user > 0) {
                        array_push($data, [
                            "tool" => $tool,
                            "color" => $color,
                            "booked_in" => date("d/m/Y H:i", strtotime($item->DateTimeStamp)),
                            "booked_out" => date("d/m/Y H:i", strtotime($item->updated_left_time)),
                            "duration" => ceil(abs(strtotime($item->DateTimeStamp) - strtotime($item->updated_left_time)) / 3600),
                        ]);
                    } else {
                        array_push($data, [
                            "tool" => $tool,
                            "color" => $color,
                            "booked_in" => date("d/m/Y H:i", strtotime($item->DateTimeStamp)),
                            "booked_out" => '',
                            "duration" => ceil(abs(strtotime($item->DateTimeStamp) - strtotime($item->updated_left_time)) / 3600),
                        ]);
                    }

                }

            }

            $end_date = date('Y-m-d', strtotime("+1 days", strtotime($end_date)));
        }
    }

    echo json_encode($data, true);
}

else if($action == "delete_user") {
    $user = $_POST['user'];
    $query = "DELETE FROM {$tblUsers} WHERE id = '{$user}'";
    $result = $db->query($query);
    if($result)
        echo "ok";
    else
        echo "fail";
}
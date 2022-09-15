<?php
//Login check
function login_check()
{
    if(!isset($_SESSION['user']))
        header('Location: login.php');
}

function convert_seconds_to_minutes($input_seconds)
{
    $seconds = $input_seconds % 60;
    $minutes = ($input_seconds - $seconds) / 60;
    if($minutes < 10)
        $minutes = '0'.$minutes;
    if($seconds < 10)
        $seconds = '0'.$seconds;
    return $minutes.":".$seconds;
}

function make_time_string($time)
{
    if (strlen($time) < 5) {
        $time = "0" . $time;
    }
    return $time;
}

function convert_date_string($date)
{
    $string = explode("/", $date);
    return $string[2] . '-' . $string[1] . '-' . $string[0];
}

/*
 * Settings
 */

function get_setting($set_type)
{
    global $db, $tblSettings;
    $query = "SELECT * FROM {$tblSettings} WHERE set_type = '{$set_type}' limit 1";
    $result = $db->query($query);
    if($result && mysqli_num_rows($result) > 0) {
        $setting = mysqli_fetch_object($result);
        return $setting->set_value;
    } else {
        return '';
    }
}

function update_setting($set_type, $set_value)
{
    global $db, $tblSettings;
    $old_setting = get_setting($set_type);
    if($old_setting != '')
        $sql = "UPDATE {$tblSettings} SET set_value = '{$set_value}' WHERE set_type = '{$set_type}'";
    else
        $sql = "INSERT INTO {$tblSettings} (set_value, set_type) VALUES ('{$set_value}', '{$set_type}')";

    return $db->query($sql);
}

function save_setting($post_data)
{
    $set_type = $post_data['set_type'];
    $set_value = $post_data['set_value'];
    $result = update_setting($set_type, $set_value);
    if($result)
        echo 'Ok';
    else
        echo 'Failed';
}


/*
 * Shift Setting & get shift information
 */
function shift_setting($post_data)
{
    $settings['shift1']['start'] = make_time_string($post_data['shift1_start']).":00";
    $settings['shift1']['end'] = make_time_string($post_data['shift1_end']).":00";

    $settings['shift2']['start'] = make_time_string($post_data['shift2_start']).":00";
    $settings['shift2']['end'] = make_time_string($post_data['shift2_end']).":00";

    $settings['shift3']['start'] = make_time_string($post_data['shift3_start']).":00";
    $settings['shift3']['end'] = make_time_string($post_data['shift3_end']).":00";

    for($i=0; $i<3; $i++) {
        $index = $i + 1;
        $settings['shift1']['breaks']['start'.$index] = make_time_string($post_data['shift1_break_start'][$i]).":00";
        $settings['shift1']['breaks']['end'.$index] = make_time_string($post_data['shift1_break_end'][$i]).":00";

        $settings['shift2']['breaks']['start'.$index] = make_time_string($post_data['shift2_break_start'][$i]).":00";
        $settings['shift2']['breaks']['end'.$index] = make_time_string($post_data['shift2_break_end'][$i]).":00";

        $settings['shift3']['breaks']['start'.$index] = make_time_string($post_data['shift3_break_start'][$i]).":00";
        $settings['shift3']['breaks']['end'.$index] = make_time_string($post_data['shift3_break_end'][$i]).":00";
    }

    $shift_setting = json_encode($settings, true);

    $set_type = $post_data['set_type'];
    $result = update_setting($set_type, $shift_setting);

    if($result)
        echo "ok";
    else
        echo "fail";
    exit;
}

function get_current_shift()
{
    global $current;
    $datetime = $current;
    $shift_pattern = get_setting('Shift Pattern');
    $datetime_arr = explode(" ", $datetime);
    $date = $datetime_arr[0];
    $week_day = date('N', strtotime($date));
    $next_date = date("Y-m-d", strtotime("+1 days", strtotime($date)));
    $pre_date = date("Y-m-d", strtotime("-1 days", strtotime($date)));

    $shift_settings = get_setting($shift_pattern);

    if($shift_settings != '')
        $shifts = json_decode($shift_settings, true);
    else{
        $string = file_get_contents("./shift.json");
        $shifts = json_decode($string, true);
    }

    if($shift_pattern == '2 shifts') {
        if($week_day == 5) { //Friday
            if ($datetime < $date . " " . $shifts['shift1']['start']) {
                $shift['shift'] = "shift2";
                $shift['date'] = $pre_date;
                $shift['start'] = $pre_date. " ". $shifts['shift3']['start'];
                $shift['end'] = $date. " ". $shifts['shift3']['end'];
            } else if ($datetime >= $date . " " . $shifts['shift1']['start'] && $datetime < $date . " " . $shifts['shift3']['start']) {
                $shift['shift'] = "shift1";
                $shift['date'] = $date;
                $shift['start'] = $date. " ". $shifts['shift1']['start'];
                $shift['end'] = $date. " ". $shifts['shift1']['end'];
            } else {
                $shift['shift'] = "shift2";
                $shift['date'] = $date;
                $shift['start'] = $date. " ". $shifts['shift3']['start'];
                $shift['end'] = $next_date. " ". $shifts['shift3']['end'];
            }
        } else {
            if ($datetime < $date . " " . $shifts['shift1']['start']) {
                $shift['shift'] = "shift2";
                $shift['date'] = $pre_date;
                $shift['start'] = $pre_date. " ". $shifts['shift2']['start'];
                $shift['end'] = $date. " ". $shifts['shift2']['end'];
            } else if ($datetime >= $date . " " . $shifts['shift1']['start'] && $datetime < $date . " " . $shifts['shift2']['start']) {
                $shift['shift'] = "shift1";
                $shift['date'] = $date;
                $shift['start'] = $date. " ". $shifts['shift1']['start'];
                $shift['end'] = $date. " ". $shifts['shift1']['end'];
            } else {
                $shift['shift'] = "shift2";
                $shift['date'] = $date;
                $shift['start'] = $date. " ". $shifts['shift2']['start'];
                $shift['end'] = $next_date. " ". $shifts['shift2']['end'];
            }
        }
    } else {
        if ($datetime < $date . " " . $shifts['shift1']['start']) {
            $shift['shift'] = "shift3";
            $shift['date'] = $pre_date;
            $shift['start'] = $pre_date. " ". $shifts['shift3']['start'];
            $shift['end'] = $date. " ". $shifts['shift3']['end'];
        } else if ($datetime >= $date . " " . $shifts['shift1']['start'] && $datetime < $date . " " . $shifts['shift2']['start']) {
            $shift['shift'] = "shift1";
            $shift['date'] = $date;
            $shift['start'] = $date. " ". $shifts['shift1']['start'];
            $shift['end'] = $date. " ". $shifts['shift1']['end'];
        } else if ($datetime >= $date . " " . $shifts['shift2']['start'] && $datetime < $date . " " . $shifts['shift3']['start']) {
            $shift['shift'] = "shift2";
            $shift['date'] = $date;
            $shift['start'] = $date. " ". $shifts['shift2']['start'];
            $shift['end'] = $date. " ". $shifts['shift2']['end'];
        } else {
            $shift['shift'] = "shift3";
            $shift['date'] = $date;
            $shift['start'] = $date. " ". $shifts['shift3']['start'];
            $shift['end'] = $next_date. " ". $shifts['shift3']['end'];
        }
    }

    return $shift;
}

function get_star_end_by_date_shift($date, $shift, $area) {
    $shift_pattern = get_setting($area.' Shift Pattern');
    $week_day = date('N', strtotime($date));
    $shift_settings = get_setting($shift_pattern);
    if($shift_settings != '')
        $shifts = json_decode($shift_settings, true);
    else{
        $string = file_get_contents("./shift.json");
        $shifts = json_decode($string, true);
    }

    if($week_day == 5) { //Friday
        if($shift == 'shift2') {
            $start = $shifts['shift3']['start'];
            $end = $shifts['shift3']['end'];
        } else {
            $start = $shifts[$shift]['start'];
            $end = $shifts[$shift]['end'];
        }
    } else {
        $start = $shifts[$shift]['start'];
        $end = $shifts[$shift]['end'];
    }

    $data['start'] = $start;
    $data['end'] = $end;
    return $data;

}

/*
 * User
 */
function get_all_users()
{
    global $db, $tblUsers;
    $query = "SELECT * FROM {$tblUsers}";
    $result = $db->query($query);
    $users = array();
    while($user = mysqli_fetch_object($result)){
        array_push($users, array(
            'user_id' => $user->ID,
            'username' => $user->username,
            'staff' => $user->staff,
            'type' => $user->type,
            'last_login' => $user->last_login,
        ));
    }

    return $users;
}

function read_users()
{
    $users = get_all_users();
    foreach ($users as $user) {
        echo '<tr>';
        echo '<td>'.$user['username'].'</td>';
        echo '<td>'.$user['staff'].'</td>';
        if($user['type'] == 1)
            echo '<td><span style="color: red;">Administrator</span></td>';
        else
            echo '<td><span style="color: green;">User</span></td>';

        if(!empty($user['last_login']))
            echo '<td>'.date('d/m/Y H:i:s', strtotime($user['last_login'])).'</td>';
        else
            echo '<td></td>';
        echo '<td style="text-align: center;">';
        echo '<button class="btn btn-primary btn-sm edit-user" value="'.$user['user_id'].'" type="button"><i class="fas fa-edit"></i></button>&nbsp;';
        echo '<button class="btn btn-danger btn-sm delete-user" type="button" value="'.$user['user_id'].'"><i class="fas fa-trash"></i></button>';
        echo '</td>';
        echo '</tr>';
    }
}

function get_user_info($user_id) {
    global $db, $tblUsers;
    $query = "SELECT * FROM {$tblUsers} WHERE `ID` = {$user_id}";
    $result = $db->query($query);
    return mysqli_fetch_object($result);
}

function read_user($post_data)
{
    $user_id = $post_data['user_id'];
    $user = get_user_info($user_id);
    echo json_encode($user, true);
}

function save_user($post_data)
{
    global $db, $tblUsers;
    $user_id = $post_data['user_id'];
    $username = $post_data['username'];
    $staff = $post_data['staff'];
    $user_type = $post_data['user_type'];
    if($user_id == 0) {
        $query = "INSERT INTO {$tblUsers}  (`username`, `staff`, `type`) value ('{$username}', '{$staff}', '{$user_type}')";
    } else {
        $query = "UPDATE {$tblUsers} SET `username` = '{$username}', `staff` = '{$staff}', `type` = '{$user_type}' WHERE `ID` = {$user_id}";
    }

    $result = $db->query($query);
    if($result)
        echo 'Ok';
    else
        echo 'Fail';
}

function delete_user($post_data)
{
    global $db, $tblUsers;
    $user_id = $post_data['user_id'];
    $query = "DELETE FROM {$tblUsers} WHERE `ID` = {$user_id}";
    $result = $db->query($query);
    if($result)
        echo 'Ok';
    else
        echo 'Fail';
}

function get_user_names($user_ids)
{
    $user_names = array();
    if(!empty($user_ids)){
        $user_ids = explode(",", $user_ids);
        foreach ($user_ids as $user_id) {
            $user = get_user_info($user_id);
            array_push($user_names, $user->username);
        }
    }
    return implode(", ", $user_names);
}

/*
 * Stocking Lane Management
 */

function save_lane($post_data)
{
    global $db, $tblStocking, $STOCKING_AREAS;
    $area_index = $post_data['area_index'];
    $lane_id = $post_data['lane_id'];
    $lane_no = $post_data['lane_no'];
    $barcode_in = $post_data['barcode_in'];
    $barcode_out = $post_data['barcode_out'];
    $allocation = $post_data['allocation'];
    $height = $post_data['height'];
    $area = $STOCKING_AREAS[$area_index];
    if($lane_id == 0) {
        $query = "INSERT INTO {$tblStocking}  (`lane_no`, `barcode_in`, `barcode_out`, `allocation`, `height`, `area`) 
                    value ('{$lane_no}', '{$barcode_in}', '{$barcode_out}', '{$allocation}', '{$height}', '{$area}')";
    } else {
        $query = "UPDATE {$tblStocking} SET `lane_no` = '{$lane_no}', `barcode_in` = '{$barcode_in}', `barcode_out` = '{$barcode_out}',
                    `allocation` = '{$allocation}', `height` = '{$height}' WHERE `id` = {$lane_id}";
    }

    $result = $db->query($query);
    if($result)
        echo 'Ok';
    else
        echo 'Fail';
}

function get_all_lanes($area, $order = null)
{
    global $db, $tblStocking;
    if($order == null)
        $query = "SELECT * FROM {$tblStocking} WHERE `area` = '{$area}'";
    else
        $query = "SELECT * FROM {$tblStocking} WHERE `area` = '{$area}' ".$order;
    $result = $db->query($query);
    $lanes = array();
    while($lane = mysqli_fetch_object($result)){
        array_push($lanes, $lane);
    }

    return $lanes;
}

function read_lanes($post_data)
{
    global $STOCKING_AREAS;
    $area_index = $post_data['area_index'];
    $area = $STOCKING_AREAS[$area_index];
    $lanes = get_all_lanes($area);
    echo '<table class="table table-bordered table-striped">';
    echo '<thead>';
    echo '<tr><th>Lane No.</th><th>Barcode IN</th><th>Barcode OUT</th><th>Allocation</th><th>Height</th><th></th></tr>';
    echo '</thead>';
    if(count($lanes) > 0) {
        foreach ($lanes as $lane) {
            echo '<tr>';
            echo '<td style="text-align: center;">'.$lane->lane_no.'</td>';
            echo '<td style="text-align: center;">'.$lane->barcode_in.'</td>';
            echo '<td style="text-align: center;">'.$lane->barcode_out.'</td>';
            echo '<td style="text-align: center;">'.$lane->allocation.'</td>';
            echo '<td style="text-align: center;">'.$lane->height.'</td>';
            echo '<td style="text-align: center;">';
            echo '<button class="btn btn-primary btn-sm edit-lane" value="'.$lane->id.'" type="button"><i class="fas fa-edit"></i></button>&nbsp;';
            echo '<button class="btn btn-danger btn-sm delete-lane" type="button" value="'.$lane->id.'"><i class="fas fa-trash"></i></button>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6" style="text-align: center;">There is no lane yet.</td></tr>';
    }
}

function get_lane_by_id($lane_id)
{
    global $db, $tblStocking;
    $query = "SELECT * FROM {$tblStocking} WHERE `id` = {$lane_id}";
    $result = $db->query($query);
    return mysqli_fetch_object($result);
}

function get_lane($post_data)
{
    $lane_id = $post_data['lane_id'];
    $lane = get_lane_by_id($lane_id);
    echo json_encode($lane, true);
}

function delete_lane($post_data)
{
    global $db, $tblStocking;
    $lane_id = $post_data['lane_id'];
    $query = "DELETE FROM {$tblStocking} WHERE `id` = {$lane_id}";
    $result = $db->query($query);
    if($result)
        echo 'Ok';
    else
        echo 'Fail';
}

/*
 * Part Management
 */
function get_all_parts()
{
    global $db, $tblParts;
    $query = "SELECT * FROM {$tblParts} ORDER BY `part_no`";
    $result = $db->query($query);
    $parts = array();
    while($part = mysqli_fetch_object($result)){
        array_push($parts, $part);
    }
    return $parts;
}

function read_parts()
{
    $parts = get_all_parts();
    echo '<table class="table table-bordered table-striped">';
    echo '<thead>';
    echo '<tr><th>Part No.</th><th>Part Name</th><th>Amount</th><th></th></tr>';
    echo '</thead>';
    if(count($parts) > 0) {
        foreach ($parts as $part) {
            echo '<tr>';
            echo '<td style="text-align: center;">'.$part->part_no.'</td>';
            echo '<td style="text-align: center;">'.$part->part_name.'</td>';
            echo '<td style="text-align: center;">'.$part->amount.'</td>';
            echo '<td style="text-align: center;">';
            echo '<button class="btn btn-primary btn-sm edit-part" value="'.$part->id.'" type="button"><i class="fas fa-edit"></i></button>&nbsp;';
            echo '<button class="btn btn-danger btn-sm delete-part" type="button" value="'.$part->id.'"><i class="fas fa-trash"></i></button>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6" style="text-align: center;">There is no part yet.</td></tr>';
    }
}

function save_part($post_data)
{
    global $db, $tblParts;
    $part_id = $post_data['part_id'];
    $part_no = $post_data['part_no'];
    $part_name = $post_data['part_name'];
    $amount = $post_data['amount'];
    if($part_id == 0) {
        $query = "INSERT INTO {$tblParts}  (`part_no`, `part_name`, `amount`) 
                    value ('{$part_no}', '{$part_name}', '{$amount}')";
    } else {
        $query = "UPDATE {$tblParts} SET `part_no` = '{$part_no}', `part_name` = '{$part_name}', `amount` = '{$amount}' WHERE `id` = {$part_id}";
    }

    $result = $db->query($query);
    if($result)
        echo 'Ok';
    else
        echo 'Fail';
}

function get_part_by_id($id)
{
    global $db, $tblParts;
    $query = "SELECT * FROM {$tblParts} WHERE `id` = {$id}";
    $result = $db->query($query);
    return mysqli_fetch_object($result);
}

function get_part_by_no($no)
{
    global $db, $tblParts;
    $query = "SELECT * FROM {$tblParts} WHERE `part_no` = '{$no}' LIMIT 1";
    $result = $db->query($query);
    return mysqli_fetch_object($result);
}

function get_part($post_data)
{
    $part_id = $post_data['part_id'];
    $part = get_part_by_id($part_id);
    echo json_encode($part, true);
}

function delete_part($post_data)
{
    global $db, $tblParts;
    $part_id = $post_data['part_id'];
    $query = "DELETE FROM {$tblParts} WHERE `id` = {$part_id}";
    $result = $db->query($query);
    if($result)
        echo 'Ok';
    else
        echo 'Fail';
}

/*
 * Stocking Input
 */
function read_barcode($post_data)
{
    global $db, $tblStocking, $tblScanLog, $_SESSION;

    $shift_id = $post_data['shift_id'];
    $shift_date = $post_data['shift_date'];
    $page = $post_data['page'];
    $user_id = $_SESSION['user']['user_id'];

    $part_barcodes = explode(",", $_POST['part']);
    $lane_barcodes = explode(",", $_POST['lane']);
    $data['error'] = '';
    $data['success'] = '';

    foreach($part_barcodes as $index => $part_barcode) {
        $lane_barcode = $lane_barcodes[$index];
        $part = get_part_by_no($part_barcode);
        if($part) {
            //Get Lane Information
            $query = "SELECT * FROM {$tblStocking} WHERE `barcode_in` = '{$lane_barcode}' OR `barcode_out` = '{$lane_barcode}' LIMIT 1";
            $result = $db->query($query);
            if(mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_object($result);
                $lane_id = $row->id;
                $allocation = $row->allocation;
                //Get left allocation
                $query = "SELECT * FROM {$tblScanLog} WHERE `lane_id` = '{$lane_id}' AND `booked_in` = 1 AND `booked_out` = 0";
                $result = $db->query($query);
                $allocated = mysqli_num_rows($result);
                $left_allocation = $allocation - $allocated;
                if($row->barcode_in == $lane_barcode) {
                    if($left_allocation > 0 ) {
                        $query = "INSERT INTO {$tblScanLog}  (`part`, `lane_id`, `booked_in`, `booked_out`, `page`, `shift`, `shift_date`, `user_id`, `booked_in_time`) 
                                value ('{$part_barcode}', '{$lane_id}', 1, 0, '{$page}', '{$shift_id}', '{$shift_date}', {$user_id}, NOW())";
                        $db->query($query);
                    } else {
                        $data['error'] = 'Lane allocation already was 0.';
                    }
                } else {
                    $query = "SELECT * FROM {$tblScanLog} WHERE `part` = '{$part_barcode}' AND `lane_id` = '{$lane_id}' AND `booked_in` = 1 AND `booked_out` = 0";
                    $result = $db->query($query);
                    $chk = mysqli_num_rows($result);
                    if($chk > 0) {
                        $scanned = mysqli_fetch_object($result);
                        $update = "UPDATE {$tblScanLog} SET `booked_out` = 1, `out_user_id` = {$user_id}, `booked_out_time` = NOW() WHERE id = {$scanned->id}";
                        $db->query($update);
                    } else {
                        $data['error'] = 'There is no scanned in lane';
                    }
                }
            } else {
                $data['error'] = 'Location barcode is incorrect.';
            }
        } else {
            $data['error'] = 'Part No is incorrect.';
        }
        if($data['error'] == '')
            $data['success'] = 'Part : '.$part->part_no.'('.$part->part_no.') has been scanned to '.$row->area.', Lane'.$row->lane_no;
    }

    $booked_in_out = get_booked_in_out($page, $shift_id, $shift_date);
    $data['booked_in'] = $booked_in_out['booked_in'];
    $data['booked_out'] = $booked_in_out['booked_out'];
    echo json_encode($data, true);
}

function get_booked_in_out($page, $shift_id, $shift_date)
{
    global $db, $tblScanLog, $_SESSION;
    $query = "SELECT * FROM {$tblScanLog} WHERE `page` = '{$page}' AND `shift` = '{$shift_id}' AND `shift_date` = '{$shift_date}' AND `booked_in` = 1";
    $result = $db->query($query);
    $data['booked_in'] = mysqli_num_rows($result);
    $query = "SELECT * FROM {$tblScanLog} WHERE `page` = '{$page}' AND `shift` = '{$shift_id}' AND `shift_date` = '{$shift_date}' AND `booked_out` = 1";
    $result = $db->query($query);
    $data['booked_out'] = mysqli_num_rows($result);
    return $data;
}

function get_filled_lane($lane_id)
{
    global $db, $tblScanLog;
    $query = "SELECT * FROM {$tblScanLog} WHERE `lane_id` = {$lane_id} AND `booked_in` = 1 AND `booked_out` = 0";
    $result = $db->query($query);
    $filled = array();
    while($row = mysqli_fetch_object($result)) {
        $date_in = date('d/m/Y H:i:s', strtotime($row->booked_in_time));
        $user = get_user_info($row->user_id);
        array_push($filled, array(
            'part_no' => $row->part,
            'date_in' => $date_in,
            'member' => $user->username
        ));
    }
    return $filled;
}

function read_scan_table($post_data)
{
    global $db, $tblScanLog;
    $shift_id = $post_data['shift_id'];
    $shift_date = $post_data['shift_date'];
    $page = $post_data['page'];
    $query = "SELECT * FROM {$tblScanLog} WHERE `page` = '{$page}' AND `shift` = '{$shift_id}' AND `shift_date` = '{$shift_date}' ORDER BY `booked_in_time` ASC";
    $result = $db->query($query);
    while ($row = mysqli_fetch_object($result)) {
        echo '<tr>';
        echo '<td>'.$row->part.'</td>';
        $lane = get_lane_by_id($row->lane_id);
        echo '<td>'.$lane->area.'</td>';
        echo '<td>Lane '.$lane->lane_no.'</td>';
        echo '<td style="color: green;">IN</td>';
        echo '<td>'.date('d/m/Y H:i:s', strtotime($row->booked_in_time)).'</td>';
        $user = get_user_info($row->user_id);
        echo '<td>'.$user->username.'</td>';
        echo '</tr>';

        if($row->booked_out == 1) {
            echo '<tr>';
            echo '<td>'.$row->part.'</td>';
            echo '<td>'.$lane->area.'</td>';
            echo '<td>Lane '.$lane->lane_no.'</td>';
            echo '<td style="color: red;">OUT</td>';
            echo '<td>'.date('d/m/Y H:i:s', strtotime($row->booked_out_time)).'</td>';
            $user = get_user_info($row->out_user_id);
            echo '<td>'.$user->username.'</td>';
            echo '</tr>';
        }
    }
}

function set_help_alarm($post_data)
{
    global $db, $tblHelpAlarm, $_SESSION;
    $page = $post_data['page'];
    $user_id = $_SESSION['user']['user_id'];
    $query = "SELECT * FROM {$tblHelpAlarm} WHERE `is_confirm` = 0 AND `page` = '{$page}' LIMIT 1";
    $result = $db->query($query);
    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_object($result);
        $sql = "UPDATE {$tblHelpAlarm} SET `clicked_time` = NOW() WHERE id = {$row->id}";
        $db->query($sql);
        $help_id = $row->id;
    } else {
        $sql = "INSERT INTO {$tblHelpAlarm}  (`user_id`, `clicked_time`, `is_confirm`, `page`) value ('{$user_id}', NOW(), 0, '{$page}') ";
        $db->query($sql);
        $help_id = $db->insert_id;
    }

    if($page == 'Container Devan') {
        $username = $_SESSION['user']['username'];
        echo '<h3>MEMBER: '.$username.'</h3>';
        echo '<h3>TIME/DATE: '.date('H:i d/m/y').'</h3>';
        echo '<input type="hidden" id="help_alarm_id" value="'.$help_id.'">';
    }
}

function get_help_alarm($post_data)
{
    global $db, $tblHelpAlarm;
    $page = $post_data['page'];
    $query = "SELECT * FROM {$tblHelpAlarm} WHERE `is_confirm` = 0 AND page ='{$page}' LIMIT 1";
    $result = $db->query($query);
    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_object($result);
        $user = get_user_info($row->user_id);
        echo '<h3>ANDON HELP</h3>';
        echo '<h3>MEMBER: '.$user->username.'</h3>';
        echo '<h3>TIME: '.date('d/m/Y H:i:s', strtotime($row->clicked_time)).'</h3>';
        echo '<input type="hidden" id="confirm_help_alarm_id" value="'.$row->id.'">';
    } else {
        echo 'NO HELP';
    }
}

function confirm_help_alarm($post_data)
{
    global $db, $tblHelpAlarm, $_SESSION;
    $alarm_id = $post_data['alarm_id'];
    if(isset($post_data['confirm_user_id']))
        $user_id = $post_data['confirm_user_id'];
    else
        $user_id = $_SESSION['user']['user_id'];

    $query = "UPDATE {$tblHelpAlarm} SET is_confirm = 1, confirm_user_id = '{$user_id}', confirm_time = NOW() WHERE id = {$alarm_id}";
    $db->query($query);
}

function get_overview_screen()
{
    global $STOCKING_AREAS;
    foreach ($STOCKING_AREAS as $index => $area) {
        echo '<div class="row">';
        echo '<div class="col-md-12">';
        if($index == 0) {
            echo '<div class="card card-primary">';
            $cell_bg = '#007bff';
        }
        else if($index == 1) {
            echo '<div class="card card-success">';
            $cell_bg = '#28a745';
        }
        else {
            echo '<div class="card card-warning">';
            $cell_bg = '#ffc107';
        }

        echo '<div class="card-header">';
        echo '<h3 class="card-title">'.$area.'</h3>';
        echo '</div>';

        echo '<div class="card-body">';
        echo '<div style="overflow-x: auto; white-space: nowrap;">';
        $lanes = get_all_lanes($area);
        foreach ($lanes as $lane) {
            $allocation = $lane->allocation;
            $height = $lane->height;

            if($allocation % $height != 0) {
                $remainder = $height - ($allocation % $height);
                $rows = (int) ($allocation / $height) + 1;
            }
            else {
                $remainder = 0;
                $rows = (int) ($allocation / $height);
            }
            $height_px = (int) 140 / $height;
            $total_width = $rows * $height_px;

            $filled = get_filled_lane($lane->id);
            echo '<div style="width: auto; display: inline-block;">';
            echo '<h5 style="font-size:16px; text-align: center;">Lane'.$lane->lane_no.' Filled. '.count($filled).'/'.$lane->allocation.'</h5>';
            echo '<table style="width: auto; margin: 10px;" class="float-left">';
            for($i = 1; $i <= $height; $i++ ) {
                echo '<tr>';
                for($c = 1; $c <= $rows; $c ++){
                    $td_index = $height * ($c - 1) + ($height - $i);
                    if($remainder >= $i && $c == $rows) {
                        echo '<td style="background-color: #FFFFFF; border: 0; width: 50px; height: 50px;">&nbsp;</td>';
                    }
                    else {
                        if(isset($filled[$td_index])) {
                            echo '<td class="has-details" style="background-color: '.$cell_bg.'; border: 1px solid #a5a3a3; width: 50px; height: 50px;">';
                            echo '<span>&nbsp;</span>';
                            echo '<span class="details" style="width: 300px;">';
                            echo 'Part No: '. $filled[$td_index]['part_no'].'<br/>';
                            echo 'Location: '. $lane->barcode_in.'<br/>';
                            echo 'Date IN: '. $filled[$td_index]['date_in'].'<br/>';
                            echo 'Member: '. $filled[$td_index]['member'];
                            echo '</span>';
                        } else {
                            echo '<td style="background-color: #FFFFFF; border: 1px solid #a5a3a3; width: 50px; height: 50px;">';
                            echo '&nbsp;';
                            echo '</td>';
                        }
                    }
                }
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>'; // card body

        echo '</div>'; // card
        echo '</div>'; // col-md-12
        echo '</div>'; // row
    }
}

function read_history($post_data)
{
    global $db, $tblScanLog;
    $from_date = convert_date_string($post_data['from_date']);
    $start = $from_date." 00:00:00";
    $to_date = convert_date_string($post_data['to_date']);
    $end = $to_date." 23:59:59";

    $query = "SELECT * FROM {$tblScanLog} WHERE booked_in_time BETWEEN '{$start}' AND  '{$end}' ORDER BY `booked_in_time` ASC";
    $result = $db->query($query);
    echo '<table id="history_table" class="table table-bordered table-striped dataTable dtr-inline">';
    echo '<thead>';
    echo '<th>Location</th>';
    echo '<th>Lane</th>';
    echo '<th>Part number</th>';
    echo '<th>Timestamp</th>';
    echo '<th>Member</th>';
    echo '</thead>';
    echo '<tbody>';
    while ($row = mysqli_fetch_object($result)) {
        $lane = get_lane_by_id($row->lane_id);
        $user = get_user_info($row->user_id);
        echo '<tr>';
        echo '<td>'.$lane->barcode_in.'</td>';
        echo '<td>Lane '.$lane->lane_no.'</td>';
        echo '<td>'.$row->part.'</td>';
        echo '<td><span style="display: none;">'.$row->booked_in_time.'</span>'.date('d/m/Y H:i:s', strtotime($row->booked_in_time)).'</td>';
        echo '<td>'.$user->username.'</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}

/*
 * Container Devan
 */

function get_container_devan($start, $end)
{
    global $db, $tblContainerDevan;
    $query = "SELECT * FROM {$tblContainerDevan} WHERE `date` BETWEEN  '{$start}' AND '{$end}' ORDER BY `date` ASC";
    $result = $db->query($query);
    $items = array();
    while($row = mysqli_fetch_array($result))
    {
        array_push($items, $row);
    }
    return $items;
}

function update_container_devan($post_data)
{
    global $db, $tblContainerDevan;
    $value = $post_data['value'];
    $field = $post_data['field'];
    $row_id = $post_data['row_id'];
    $update_query = "UPDATE {$tblContainerDevan} SET `{$field}` = '{$value}' WHERE id = {$row_id}";
    $result = $db->query($update_query);
    if($result)
        echo 'Success';
    else
        echo 'Failed';
}

function complete_container_devan($post_data)
{
    global $db, $tblContainerDevan, $_SESSION;
    $row_id = $post_data['row_id'];
    $user_id = $_SESSION['user']['user_id'];
    $revan_state = $post_data['renban'];
    if($revan_state == 'revan')
        $update_query = "UPDATE {$tblContainerDevan} SET `revan_state` = '{$revan_state}' WHERE id = {$row_id}";
    else
        $update_query = "UPDATE {$tblContainerDevan} SET `is_completed` = 1, `completed_at` = NOW(), `completed_by` = '{$user_id}', `revan_state` = 'completed' WHERE id = {$row_id}";
    $result = $db->query($update_query);
    if($result)
        echo 'Success';
    else
        echo 'Failed';
}

function read_container_devan($post_data)
{
    $year_month = explode("/", $post_data['year_month']);
    $this_month_start = $year_month[1].'-'.$year_month[0].'-01';
    $start_date = date('Y-m-d', strtotime('previous sunday', strtotime($this_month_start)));
    $this_month_end = date("Y-m-t", strtotime($this_month_start));
    $end_date = date('Y-m-d', strtotime('next monday', strtotime($this_month_end)));

    $container_devan = get_container_devan($start_date, $end_date);
    $pre_date = '';
    if(count($container_devan) > 0) {
        foreach ($container_devan as $index => $row){
            $date = $row['date'];
            $week_day = date('l', strtotime($date));
            if($pre_date != $date) {
                if($week_day == 'Monday') {
                    echo '<tr>';
                    echo '<td colspan="38" style="height: 20px; background-color: #d5d5d5;"></td>';
                    echo '</tr>';
                } else {
                    echo '<tr>';
                    echo '<td colspan="38" style="height: 20px; background-color: white;"></td>';
                    echo '</tr>';
                }
            }

            echo '<tr class="devan-row" data-row="'.$row['id'].'" data-container="'.$row['in_house_container_number'].'" data-schedule_date="'.$date.'">';
            if($row['revan_state'] == 'scheduled')
                $style = 'background-color:red;color:white;';
            else if($row['revan_state'] == 'revan')
                $style = 'background-color:#CCFFCC;';
            else
                $style = '';
            echo '<!--------Delivery Management------->';
            echo '<th style="font-weight: bold;">'.date('d-M D', strtotime($date)).'</th>';
            echo '<th style="font-weight: bold;">'.$row['shift'].'</th>';
            echo '<th style="font-weight: bold; border-right: 1px solid #878787;">'.$row['time'].'</th>';

            echo '<td style="'.$style.'"><input type="text" name="inbound_renban_air_freight_case_number" class="form-control input-value" value="'.$row['inbound_renban_air_freight_case_number'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="shipping_line" class="form-control input-value" value="'.$row['shipping_line'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="number_of_zr_modules" class="form-control input-value" value="'.$row['number_of_zr_modules'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="container_number" class="form-control input-value" style="width: 180px;" value="'.$row['container_number'].'"></td>';
            echo '<td style="'.$style.'">'.$row['pentalver_instructions'].'</td>';

            echo '<td style="'.$style.'"><input type="text" name="departure_inbound_renban" class="form-control input-value" value="'.$row['departure_inbound_renban'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="departure_export_load_reference" class="form-control input-value" value="'.$row['departure_export_load_reference'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="departure_shipping_line" class="form-control input-value" value="'.$row['departure_shipping_line'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="departure_container_number" class="form-control input-value" style="width: 180px;" value="'.$row['departure_container_number'].'"></td>';

            echo '<td style="'.$style.'"><input type="text" name="on_dock_inbound_renban" class="form-control input-value" value="'.$row['on_dock_inbound_renban'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="on_dock_shipping_line" class="form-control input-value" value="'.$row['on_dock_shipping_line'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="on_doc_container_number" class="form-control input-value" style="width: 180px;" value="'.$row['on_doc_container_number'].'"></td>';

            echo '<!--------In House Management-------->';
            echo '<td style="'.$style.'"></td>';
            echo '<td style="'.$style.'">'.$row['in_house_instructions'].'</td>';
            echo '<td style="'.$style.'"><input type="text" name="confirm_gl_tl_instructions_print_name" class="form-control input-value" value="'.$row['confirm_gl_tl_instructions_print_name'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="confirm_gl_customs_check_print_name" class="form-control input-value" value="'.$row['confirm_gl_customs_check_print_name'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="confirm_module_condition_quantity" class="form-control input-value" value="'.$row['confirm_module_condition_quantity'].'"></td>';

            echo '<td style="'.$style.'"><input type="text" name="devan_inbound_renban_no_1" class="form-control input-value" value="'.$row['devan_inbound_renban_no_1'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="devan_export_renban" class="form-control input-value" value="'.$row['devan_export_renban'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="devan_shipping_line" class="form-control input-value" value="'.$row['devan_shipping_line'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="devan_zr" class="form-control input-value" style="width: 120px;" value="'.$row['devan_zr'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="pipcont_pipseal" class="form-control input-value" value="'.$row['pipcont_pipseal'].'"></td>';

            echo '<td style="'.$style.'"><input type="text" name="in_house_container_number" class="form-control input-value" style="width: 180px;" value="'.$row['in_house_container_number'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="expected_seal_no" class="form-control input-value" value="'.$row['expected_seal_no'].'"></td>';

            echo '<td style="'.$style.'"></td>';

            echo '<td style="'.$style.'"><input type="text" name="deeside_yard_inbound_renban_no_1" class="form-control input-value" value="'.$row['deeside_yard_inbound_renban_no_1'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="deeside_yard_tapped_modules_no_1" class="form-control input-value" value="'.$row['deeside_yard_tapped_modules_no_1'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="deeside_yard_container_number_no_1" class="form-control input-value" style="width: 180px;" value="'.$row['deeside_yard_container_number_no_1'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="deeside_yard_inbound_renban_no_2" class="form-control input-value" value="'.$row['deeside_yard_inbound_renban_no_2'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="deeside_yard_tapped_modules_no_2" class="form-control input-value" value="'.$row['deeside_yard_tapped_modules_no_2'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="deeside_yard_container_number_no_2" class="form-control input-value" style="width: 180px;" value="'.$row['deeside_yard_container_number_no_2'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="deeside_yard_inbound_renban_no_3" class="form-control input-value" value="'.$row['deeside_yard_inbound_renban_no_3'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="deeside_yard_tapped_modules_no_3" class="form-control input-value" value="'.$row['deeside_yard_tapped_modules_no_3'].'"></td>';
            echo '<td style="'.$style.'"><input type="text" name="deeside_yard_container_number_no_3" class="form-control input-value" style="width: 180px;" value="'.$row['deeside_yard_container_number_no_3'].'"></td>';
            echo '</tr>';
            $pre_date = $date;
        }
    } else {
        echo 'there is no data yet';
    }

}

function read_container_devan_member_screen($post_data)
{
    global $db, $tblContainerDevan;
    if($post_data['date'] == 'today') {
        $query = "SELECT * FROM {$tblContainerDevan} WHERE `revan_state` = 'scheduled' ORDER BY `date` ASC LIMIT 1";
    } else {
        $date = convert_date_string($post_data['date']);
        $query = "SELECT * FROM {$tblContainerDevan} WHERE `revan_state` = 'scheduled' AND `date` = '{$date}' ORDER BY `date` ASC LIMIT 1";
    }

    $result = $db->query($query);
    if(mysqli_num_rows($result) > 0) {
        $devan = mysqli_fetch_array($result);
        //Update Renban No
        $renban_no = get_setting('renban_no_prefix');
        //$renban_no = update_renban_no($devan['id']);

        echo '<div class="row" style="background-color: #1797FF; color: #FFF;">';
        echo '<div class="offset-md-2 col-md-7" style="padding: 50px 10px; min-width: 650px;">';
        if($devan['shift'] == 'D')
            $shift = 'Days';
        else
            $shift = 'Night';
        //Date, Shift and Time
        echo '<h1 style="font-size: 48px;"><span style="margin-right: 148px;">'.date('d/m/Y', strtotime($devan['date'])).'</span><span style="margin-right: 60px;">'.$shift.'</span><span>'.$devan['time'].'</span></h1>';

        //Container Renban
        echo '<label style="font-size: 48px; font-weight: normal">Container Renban:</label>';
        echo '<input type="text" id="container_renban" name="container_renban" class="form-control" style="width: 420px; display: inline-block; height: 60px; font-size: 48px;">';
        echo '<button class="btn btn-primary" id="btn_chk_container_renban" style="height: 60px; margin-left: 20px; width: 160px; margin-top: -20px; font-size: 32px;" value="'.$devan['devan_inbound_renban_no_1'].'">CHECK</button>';

        //Container No
        echo '<h1 style="font-size: 48px;">Container No: <span style="color: #000;">'.$devan['in_house_container_number'].'</span></h1>';

        //Reban
        echo '<h1 style="font-size: 48px;">';
        echo '<span>Renban No: '.$renban_no.'</span>';
        echo '</h1>';

        //Reban
        echo '<div style="width: 100%; text-align: center;" >';
        echo '<button class="btn btn-success" id="btn_complete" style="width: 240px; font-size:36px; margin:0;" disabled value="'.$devan['id'].'" data-renban="check">Complete</button>';
        echo '</div>';
        echo '</div>';
        echo '<div class="col-md-3" style="display: flex; align-items: center;">';
        echo '<button class="btn bg-yellow devan-help" style="font-size: 36px; border-radius: 100px; width: 200px; height: 200px;">Help <br/>Andon</button>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<p style="text-align: center; padding: 30px; font-size: 30px;">There is no scheduled job yet</p>';
    }
}

function update_renban_no($devan_id)
{
    global $db, $tblContainerDevan;
    $pre_fix = get_setting('renban_no_prefix');
    //$query = "SELECT * FROM {$tblContainerDevan} WHERE id < '{$devan_id}' AND is_completed = 1 ORDER BY `departure_inbound_renban` DESC LIMIT 1";
    $query = "SELECT * FROM {$tblContainerDevan} WHERE is_completed = 1 ORDER BY `departure_inbound_renban` DESC LIMIT 1";
    $result = $db->query($query);
    if(mysqli_num_rows($result) > 0) {
        $devan = mysqli_fetch_array($result);
        $old_inbound_renban = $devan['departure_inbound_renban'];
        $new_reban_no = (int) $old_inbound_renban + 1;
        if($new_reban_no < 10)
            $renban_no = $pre_fix.'0000'.$new_reban_no;
        else if($new_reban_no >=10 && $new_reban_no < 100)
            $renban_no = $pre_fix.'000'.$new_reban_no;
        else if($new_reban_no >=100 && $new_reban_no < 1000)
            $renban_no = $pre_fix.'00'.$new_reban_no;
        else if($new_reban_no >=1000 && $new_reban_no < 10000)
            $renban_no = $pre_fix.'0'.$new_reban_no;
        else
            $renban_no = $pre_fix.$new_reban_no;
        $update_query = "UPDATE {$tblContainerDevan} SET `departure_inbound_renban` = '{$new_reban_no}' WHERE id = {$devan_id}";

    } else {
        $update_query = "UPDATE {$tblContainerDevan} SET `departure_inbound_renban` = '1' WHERE id = {$devan_id}";
        $renban_no = $pre_fix.'00001';
    }
    $result = $db->query($update_query);
    return $renban_no;
}

function update_revan_state($post_data)
{
    global $db, $tblContainerDevan;
    $container_number = $post_data['container_number'];
    $today = date('Y-m-d');
    $query = "UPDATE {$tblContainerDevan} SET revan_state = 'scheduled' WHERE in_house_container_number = '{$container_number}' AND `date` >= '{$today}'";
    $result = $db->query($query);
    if($result)
        echo $today;
    else
        echo 'Failed';
}

/*
 * Stocking Page
 */

function read_area_lane_status($post_data)
{
    global $db, $tblScanLog, $STOCKING_AREAS;
    $part_no = $post_data['part_no'];
    $page = $post_data['page'];

    if(empty($part_no))
        $query = "SELECT * FROM {$tblScanLog} WHERE `page` = '{$page}' AND `booked_in` = 1 AND `booked_out` = 0";
    else
        $query = "SELECT * FROM {$tblScanLog} WHERE `page` = '{$page}' AND `booked_in` = 1 AND `booked_out` = 0 AND `part` = '{$part_no}'";
    $result = $db->query($query);
    $rows = mysqli_num_rows($result);
    $filled_lanes = array();
    if($rows > 0) {
        while($row = mysqli_fetch_object($result)){
            if(!in_array($row->lane_id, $filled_lanes))
                array_push($filled_lanes, $row->lane_id);
        }
    } else {
        $part_no = '';
    }

    $areas = array();
    foreach ($STOCKING_AREAS as $index => $area) {
        $lanes = get_all_lanes($area);
        foreach ($lanes as $lane){
            $allocation = $lane->allocation;
            $query = "SELECT * FROM {$tblScanLog} WHERE `page` = '{$page}' AND `booked_in` = 1 AND `booked_out` = 0 AND `lane_id` = {$lane->id}";
            $result = $db->query($query);
            $filled_allocations = mysqli_num_rows($result);
            $left_allocations = $allocation - $filled_allocations;
            if(!empty($part_no)) {
                if($filled_allocations == 0 || in_array($lane->id, $filled_lanes)){
                    $areas[$area][] = array(
                        'lane' => 'Lane '.$lane->lane_no,
                        'allocation' => $allocation,
                        'filled_allocation' => $filled_allocations,
                    );
                }
            } else {
                if($filled_allocations == 0){
                    $areas[$area][] = array(
                        'lane' => 'Lane '.$lane->lane_no,
                        'allocation' => $allocation,
                        'filled_allocation' => $filled_allocations,
                    );
                }
            }
        }
    }

    echo '<div class="row">';
    foreach ($areas as $area => $lanes){
        echo '<div class="col-sm-4">';
        echo '<h1>'.$area.'</h1>';
        echo '<table class="table table-bordered table-striped">';
        if(!empty($part_no)){
            echo '<tr><th>Part</th><th>'.$part_no.'</th></tr>';
        }
        echo '<tr><td colspan="2" style="text-align: left;">Lanes Available</td></tr>';
        foreach ($lanes as $lane) {
            echo '<tr>';
            echo '<td>'.$lane['lane'].'</td>';
            echo '<td>'.$lane['filled_allocation'].'/'.$lane['allocation'].'</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    }
    echo '</div>';
}

function get_filled_lanes_by_part($post_data)
{
    global $db, $tblScanLog;
    $part_no = $post_data['part_no'];
    $page = $post_data['page'];
    $query = "SELECT * FROM {$tblScanLog} WHERE `page` = '{$page}' AND `booked_in` = 1 AND `booked_out` = 0 AND `part` = '{$part_no}'";
    $result = $db->query($query);
    $rows = mysqli_num_rows($result);
    $filled_lanes = array();
    //$lanes = array();
    if($rows > 0) {
        while($row = mysqli_fetch_object($result)){
            if(!in_array($row->lane_id, $filled_lanes)) {
                array_push($filled_lanes, $row->lane_id);
                //array_push($lanes, get_lane_by_id($row->lane_id));
            }
        }
    }

    $part = get_part_by_no($part_no);
    $data['part'] = $part;
    $amount = 0;
    $lanes = array();
    foreach ($filled_lanes as $lane_id) {
        $query = "SELECT * FROM {$tblScanLog} WHERE `page` = '{$page}' AND `booked_in` = 1 AND `booked_out` = 0 AND `lane_id` = {$lane_id}";
        $result = $db->query($query);
        $lane_inf = get_lane_by_id($lane_id);
        $location_index = $lane_inf->allocation;
        $locations = array();
        while($row=mysqli_fetch_object($result)) {
            if($row->part == $part->part_no) {
                array_push($locations, $location_index);
                $amount += $part->amount;
            }
            $location_index --;
        }
        array_push($lanes, array(
            'lane_id' => $lane_inf->id,
            'lane_no' => $lane_inf->lane_no,
            'area' => $lane_inf->area,
            'locations' => implode(", ", $locations)
        ));
    }
    $data['lanes'] = $lanes;
    $data['amount'] = $amount;
    echo json_encode($data, true);
}

function load_overview_screen($post_data)
{
    global $db, $tblScanLog, $STOCKING_AREAS;
    $page = $post_data['page'];
    $td_data = array();
    foreach ($STOCKING_AREAS as $index => $area) {
        $lanes = get_all_lanes($area);
        foreach ($lanes as $lane){
            $query = "SELECT * FROM {$tblScanLog} WHERE `page` = '{$page}' AND `booked_in` = 1 AND `booked_out` = 0 AND `lane_id` = {$lane->id}";
            $result = $db->query($query);
            $filled_allocations = mysqli_num_rows($result);
            $allocations = $lane->allocation;
            $height = $lane->height;
            if($area == 'Free Location') {
                for($i=$allocations; $i>0; $i--){
                    $td_class = '';
                    if($filled_allocations > ($allocations - $i))
                        $td_class = 'full-td';

                    if($td_class != '')
                        array_push($td_data, array(
                            'id' => 'td_'.$lane->id.'_'.$i,
                            'td_class' => $td_class
                        ));
                }
            }

            if($area == 'Part Stocking') {
                $index = ceil($allocations / $height);
                if($allocations % $height != 0)
                    $index ++;

                for($i=$height; $i <= $allocations; $i+=$height){
                    $td_class = '';
                    $box_size = $index * $height;
                    if($filled_allocations >= $box_size)
                        $td_class = 'full-td';
                    else {
                        $diff = $box_size - $filled_allocations;
                        $reminder = $filled_allocations % $height;
                        if($diff < $height) {
                            if($reminder < 2)
                                $td_class = 'l-full-td';
                            else
                                $td_class = 'm-full-td';
                        }
                    }

                    if($td_class != '')
                        array_push($td_data, array(
                            'id' => 'td_'.$lane->id.'_'.$i,
                            'td_class' => $td_class
                        ));

                    $index--;
                }

                if($allocations < $i && $allocations > ($i - $height)){
                    if($filled_allocations >= $height)
                        $td_class = 'full-td';
                    else if($filled_allocations <= $height - 1)
                        $td_class = 'm-full-td';
                    else if($filled_allocations <= $height - 1)
                        $td_class = 'l-full-td';
                    else
                        $td_class = '';

                    if($td_class != '')
                        array_push($td_data, array(
                            'id' => 'td_'.$lane->id.'_'.$i,
                            'td_class' => $td_class
                        ));
                }
            }

            if($area == 'System Fill') {
                if($allocations % $height == 0)
                    $start = $allocations;
                else
                    $start = $allocations + ($height - $allocations % $height);
                $index = 1;
                for($i=$start; $i>=$height; $i-=$height){
                    $td_class = '';
                    $box_size = $index * $height;
                    if($filled_allocations >= $box_size)
                        $td_class = 'full-td';
                    else {
                        $diff = $box_size - $filled_allocations;
                        $reminder = $filled_allocations % $height;
                        if($diff < $height) {
                            if($reminder < 2)
                                $td_class = 'l-full-td';
                            else
                                $td_class = 'm-full-td';
                        }
                    }
                    if($td_class != '')
                        array_push($td_data, array(
                            'id' => 'td_'.$lane->id.'_'.$i,
                            'td_class' => $td_class
                        ));
                    $index++;
                }
            }
        }
    }

    echo json_encode($td_data, true);
}

function get_box_data($post_data)
{
    global $db, $tblScanLog;
    $lane_id = $post_data['lane_id'];
    $page = $post_data['page'];
    $box_index = $post_data['box_index'];
    $lane = get_lane_by_id($lane_id);
    $area = $lane->area;
    $height = $lane->height;
    $allocations = $lane->allocation;
    if($area == 'Free Location')
        $height = 1;

    $start = $height * $box_index;
    $end = $height;

    echo '<table class="table">';
    echo '<tr>';
    if($area == 'Free Location')
        echo '<td>Area: '.$area.'</td>';
    else
        echo '<td colspan="2">Area: '.$area.'</td>';
    $colspan = $height - 1;
    echo '<td colspan="'.$colspan.'">Lane: '.$lane->lane_no.'</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td style="">Location: </td>';
    if($area == 'Free Location') {
        $location = $allocations - $box_index;
        echo '<td style="">'.$location.'</td>';
    } else {
        $s = $allocations - $box_index * $height;
        $e = $s - $height;
        for($i = $s; $i > $e;  $i--) {
            echo '<td style="">'.$i.'</td>';
        }
    }
    echo '</tr>';

    $query = "SELECT * FROM {$tblScanLog} WHERE `page` = '{$page}' AND `booked_in` = 1 AND `booked_out` = 0 AND `lane_id` = {$lane->id} 
                ORDER BY `booked_in_time` ASC LIMIT {$start}, {$end}";
    $result = $db->query($query);
    $data = array();
    while($row = mysqli_fetch_object($result)) {
        if(!empty($row->user_id)) {
            $user = get_user_info($row->user_id);
            $user_name = $user->username;
        } else{
            $user_name = '';
        }

        $part = get_part_by_no($row->part);
        if($part)
            $amount = $part->amount;
        else
            $amount = '';

        array_push($data, array(
            'date' => date('d/m/Y', strtotime($row->booked_in_time)),
            'member' => $user_name,
            'part_no' => $row->part,
            'amount' => $amount,
        ));

    }


    echo '<tr>';
    echo '<td>Date IN: </td>';
    $cols = 1;
    foreach ($data as $item) {
        echo '<td>'.$item['date'].'</td>';
        $cols ++;
    }
    if($height > 1 )
        for($i = 0; $i< $height - $cols + 1; $i++){
            echo '<td></td>';
        }
    echo '</tr>';

    //Member
    echo '<tr>';
    echo '<td>Member: </td>';
    $cols = 1;
    foreach ($data as $item) {
        echo '<td>'.$item['member'].'</td>';
        $cols ++;
    }
    if($height > 1 )
        for($i = 0; $i< $height - $cols + 1; $i++){
            echo '<td></td>';
        }
    echo '</tr>';

    echo '<tr>';
    echo '<td>Part No: </td>';
    $cols = 1;
    foreach ($data as $item) {
        echo '<td>'.$item['part_no'].'</td>';
        $cols ++;
    }
    if($height > 1 )
        for($i = 0; $i< $height - $cols + 1; $i++){
            echo '<td></td>';
        }
    echo '</tr>';

    echo '<tr>';
    echo '<td>Amount: </td>';
    $cols = 1;
    foreach ($data as $item) {
        echo '<td>'.$item['amount'].'</td>';
        $cols ++;
    }
    if($height > 1 )
        for($i = 0; $i< $height - $cols + 1; $i++){
            echo '<td></td>';
        }
    echo '</tr>';
    echo '</tr>';


    echo '</table>';
}
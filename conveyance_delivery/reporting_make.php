<?php
require_once("./config/config.php");

function date_compare($element1, $element2) {
    $datetime1 = strtotime($element1['datetime']);
    $datetime2 = strtotime($element2['datetime']);
    return $datetime1 - $datetime2;
}

if(!isset($_POST['select_report'])) {
    exit;
}

$t_colors = [
    [
        'bg' => '#fff',
        'color' => '#000'
    ],
    [
        'bg' => '#d0021b',
        'color' => '#fff'
    ],
    [
        'bg' => '#bd10e0',
        'color' => '#fff'
    ],
    [
        'bg' => '#0b98fb',
        'color' => '#fff'
    ],
    [
        'bg' => '#f5a623',
        'color' => '#fff'
    ],
    [
        'bg' => '#f1e01b',
        'color' => '#fff'
    ],
    [
        'bg' => '#7ed321',
        'color' => '#fff'
    ],

];

$priority = ['This shift in', 'Due in 2 shifts', 'Due in 3 shifts', 'Next day 4~6 shifts', '3~4 days 6~12 shifts', 'Due in 5 days+'];

//Report Name and Options
$select_report = $_POST['select_report'];
$query = "SELECT * FROM {$tblReports} WHERE id={$select_report}";
$result = $db->query($query);
$report = mysqli_fetch_object($result);
$report_name = $report->report_name;

//Sections
$sections = $report->sections;
$all_sections = true;
$sections_array = array();
if($sections != 'all') {
    $sections_array = explode(",", $sections);
    $all_sections = false;
}

//Graph and Data
$report_type = $report->report_type;


//Members
$members_array = array();
$members = $report->members;
if($members != 'all') {
    $all_members = false;
    $members_array = explode(",", $members);
} else {
    $query = "SELECT * FROM {$tblUsers}";
    $result = $db->query($query);
    while($user = mysqli_fetch_object($result)){
        array_push($members_array, $user->ID);
    }
}


//Tools
$tools_array = array();
$tools = $report->tools;
if($tools != 'all') {
    $tools_array = explode(",", $tools);
} else {
    $query = "SELECT * FROM {$tblToolMainData}";
    $result = $db->query($query);
    while($tool = mysqli_fetch_object($result)){
        array_push($tools_array, $tool->machine_number);
    }
}

//Section2 booked in and out
$section2_booked_in = $report->booked_in_2;
$section2_booked_out = $report->booked_out_2;

//Section3 hide and list
$section3_hide_graph = $report->hide_graph_3;
$section3_hide_list = $report->hide_list_3;

//Section4 hide list
$section4_hide_list = $report->hide_list_4;

//Section5 booked in and out
$section5_booked_in = $report->booked_in_5;
$section5_booked_out = $report->booked_out_5;


//Selected Date
$selected_date = $_POST['select_date'];

switch ($selected_date) {
    case 'current_shift' :
        $report_date_string = date("d-m-Y");
        $report_start_date = convert_date_string($report_date_string);
        $report_end_date = convert_date_string($report_date_string);
        break;
    case 'day':
        $report_date_string = $_POST['report_date'];
        $report_start_date = convert_date_string($report_date_string);
        $report_end_date = convert_date_string($report_date_string);
        break;
    case 'week':
        $report_date_string = $_POST['week_report_picker'];
        $dates = explode(" to ", $report_date_string);
        $report_start_date = convert_date_string($dates[0]);
        $report_end_date = convert_date_string($dates[1]);
        break;
    case 'month':
        $report_date_string = $_POST['month_report_picker'];
        $dates = explode("-", $report_date_string);
        $report_start_date = $dates[1]."-".$dates[0]."-01";
        $report_end_date = date("Y-m-t", strtotime($dates[1]."-".$dates[0]."-01"));
        break;
    case 'custom_date':
        if(isset($_POST['report_start_date']))
            $start_date = $_POST['report_start_date'];
        else
            $start_date = date("d-m-Y");

        if(isset($_POST['report_end_date']))
            $end_date = $_POST['report_end_date'];
        else
            $end_date = date("d-m-Y");

        $report_date_string = $start_date. " to ". $end_date;

        $report_start_date = convert_date_string($start_date);
        $report_end_date = convert_date_string($end_date);
        break;
    default:
        $report_date_string = date("d-m-Y");
        $report_start_date = convert_date_string($report_date_string);
        $report_end_date = convert_date_string($report_date_string);
        break;
}

//SHIFT
if($selected_date != 'current_shift')
    $shift = $_POST['select_shift'];
else{
    $week = date('N', strtotime($today));

    //GET SHIFT SETTING
    $query = "SELECT * FROM {$tblShiftSetting} WHERE date = '{$week}'";
    $result = $db->query($query);
    $shift_row = mysqli_fetch_object($result);
    $shift_setting = json_decode($shift_row->timeset, true);

    if($current >= $today. " ". $shift_setting[1]['start'].":00" && $current <= $today. " ". $shift_setting[1]['end'].":00") {
        $shift = "shift1";
    } else if($current >= $today. " ". $shift_setting[2]['start'].":00" && $current <= $today. " ". $shift_setting[2]['end'].":00") {
        $shift = "shift2";
    } else {
        $shift = "shift3";
        if($report_start_date == $report_end_date) {
            $time_set = get_start_end_time($report_start_date, $shift);
            if($time_set['start'] > $current)
                $report_start_date = $report_end_date = date('Y-m-d', strtotime("-1 days", strtotime($report_start_date)));
        }

    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<meta http-equiv="refresh" content="300">-->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Report Tooling</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-datepicker3.min.css" rel="stylesheet">
    <link href="css/tableexport.css" rel="stylesheet" type="text/css">
    <link href="css/style.css" rel="stylesheet" />
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js')}}"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js')}}"></script>
    <![endif]-->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/moment.min.js"></script>

    <script src="js/amcharts/amcharts.js"></script>
    <script src="js/amcharts/serial.js"></script>
    <script src="js/amcharts/plugins/export/export.min.js"></script>
    <link rel="stylesheet" href="js/amcharts/plugins/export/export.css" type="text/css" media="all"/>

    <script src="js/FileSaver.min.js"></script>
    <script src="js/Blob.min.js"></script>
    <script src="js/xls.core.min.js"></script>
    <script src="js/tableexport.min.js"></script>

    <script src="js/printThis.js"></script>
    <style>
        hr{
            border: 1px solid #4189e3;
        }

        .header-td {
            background-color: #3f415a;
            color : #fff;
            text-align: center;
            border: 1px solid #e2e2e2;
            text-transform: uppercase;
            font-weight: bold;
        }

        .value-td{
            border: 1px solid #bdbdbd;
            font-weight: bold;
            text-align: center;
        }

        .title-td{
            text-align: center;
            font-weight: bold;
            color: #0e83cd;
            font-size: 30px;
        }

        #report_table{
            width: 100%;
        }
    </style>
</head>
<body>
<div class="container" style="width: 100%; margin-bottom: 20px;">

    <input type="hidden" id="report_start_date" name="report_start_date" value="<?php echo $report_start_date;?>">
    <input type="hidden" id="report_end_date" name="report_end_date" value="<?php echo $report_end_date;?>">
    <input type="hidden" id="shift" name="shift" value="<?php echo $shift;?>">

    <div class="row" style="padding-top: 10px;">
        <div class="col-md-4">
            <img src="images/Tooling-Logo2.png" width="426" height="43" style="text-align:center; margin-left: 50px;">
        </div>
        <div class="col-md-4" style="text-align: center">
            <form id="export_data_form" action="reporting_just_data.php" method="post" target="_blank">
                <input type="hidden" id="section_report" name="select_report" value="<?php echo (isset($_POST['select_report']))?$_POST['select_report']:''; ?>" >
                <input type="hidden" id="select_date" name="select_date" value="<?php echo (isset($_POST['select_date']))?$_POST['select_date']:''; ?>" >
                <input type="hidden" id="report_date" name="report_date" value="<?php echo (isset($_POST['report_date']))?$_POST['report_date']:''; ?>" >
                <input type="hidden" id="week_report_picker" name="week_report_picker" value="<?php echo (isset($_POST['week_report_picker']))?$_POST['week_report_picker']:''; ?>" >
                <input type="hidden" id="month_report_picker" name="month_report_picker" value="<?php echo (isset($_POST['month_report_picker']))?$_POST['month_report_picker']:''; ?>" >
                <input type="hidden" id="report_start_date" name="report_start_date" value="<?php echo (isset($_POST['report_start_date']))?$_POST['report_start_date']:''; ?>" >
                <input type="hidden" id="report_end_date" name="report_end_date" value="<?php echo (isset($_POST['report_end_date']))?$_POST['report_end_date']:''; ?>" >
                <input type="hidden" id="select_shift" name="select_shift" value="<?php echo (isset($_POST['select_shift']))?$_POST['select_shift']:''; ?>" >
                <button type="button" class="btn btn-primary" id="print_pdf">EXPORT PDF</button>
                <button type="submit" class="btn btn-primary">EXPORT CSV</button>
            </form>

        </div>
        <div class="col-md-4">
            <?php
            echo '<h3 style="color: #0d548b">' .$report_name.'</h3>';
            echo '<h3 style="color: #56545a; text-transform: lowercase;">' .$report_date_string.', '.$shift.'</h3>';
            ?>
        </div>
    </div>

    <hr>

    <div class="row" id="report_div">
        <div class="col-md-12">
            <?php

            if($all_sections == true || in_array("section1", $sections_array)) {
                echo '<div class="row">';
                echo '<div class="col-md-12">';
                $data = get_report_section1_data($report_start_date, $report_end_date, $shift);

                echo '<table class="table table-responsive">';
                foreach($data as $item){
                    echo '<tr>';
                    foreach($item as $index=>$value){
                        echo '<td style="padding: 10px; border: 0px;">';
                        echo '<table class="table">';
                        echo '<tr>';
                        echo '<td class="header-td">';
                        echo $value['key'];
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<td class="value-td" style="background-color:'.$t_colors[$index]['bg'].';color: '.$t_colors[$index]['color'].';">';
                        echo $value['value'];
                        echo '</td>';
                        echo '</tr>';
                        echo '</table>';

                        echo '</td>';
                    }
                    echo '</tr>';
                }

                echo '</table>';
                echo '</div>';
                echo '</div>';
                echo '<hr>';
            }

            if($all_sections == true || in_array("section2", $sections_array)) {
                echo '<div class="row">';
                echo '<div class="col-md-12">';

                if($section2_booked_in == 1) {
                    echo '<div class="col-md-12">
                    <h2 style="text-align: center">TOTAL IN</h2>
                    <div id="section2_in" style="height: 540px;"></div>
                </div>';
                }

                if($section2_booked_out == 1) {
                    echo '<hr>';
                    echo '<div class="col-md-12">
                    <h2 style="text-align: center">TOTAL OUT</h2>
                    <div id="section2_out" style="height: 540px;"></div>
                </div>';
                }

                echo '</div>';
                echo '</div>';
                echo '<hr>';
            }

            if($all_sections == true || in_array("section3", $sections_array)) {
                echo '<div class="row">';
                echo '<div class="col-md-12">';

                if($section3_hide_graph == 0) {
                    echo '<div class="col-md-12">
                    <h2 style="text-align: center">Tools Overdue</h2>
                    <div id="section3_graph" style="height: 540px;"></div>
                </div>';
                }

                if($section3_hide_list == 0) {
                    echo '<div class="col-md-12">
                    <div id="section3_list" style="min-height: 100px;"></div>
                </div>';
                }

                echo '</div>';
                echo '</div>';
                echo '<hr>';
            }

            if($all_sections == true || in_array("section4", $sections_array)) { //Tool Activity
                echo '<div class="row">';
                echo '<div class="col-md-12">';
                echo '<h2 style="text-align: center">Tools Activity</h2>';
                if($section4_hide_list == 0) {
                    echo '<div id="section4_list" style="min-height: 100px;">';

                    //$tools_array
                    $tools_data = get_tools_data($report_start_date, $report_end_date, $tools_array, $shift);
                    foreach ($tools_data as $key=>$data) {
                        echo '<div class="col-md-12">';
                        echo '<table class="table">';
                        echo '<tr style=" background-color: #f4f4f3;">';
                        echo '<td style="font-size: 20px; font-weight: bold; width: 360px;">'.$key.'</td>';
                        echo '<td style="font-size: 20px; font-weight: bold;">';
                        echo 'TOOL COLOUR:';
                        echo '<div class="" style="border-radius:5px;background-color: '.$data['color'].'; width: 60px; height: 30px; display: inline-block"></div>';
                        echo '</td>';
                        echo '<td style="padding: 10px; width: 300px;">';
                        echo '<div style="border-radius:15px;width: 100%; padding: 10px; text-align: center; font-size: 20px; font-weight: bold; background-color: #4a90e2; color: #fff;">';
                        echo 'TOTAL IN : '.$data['total_in'];
                        echo '</div>';
                        echo '</td>';
                        echo '<td style="padding: 10px; width: 300px;">';
                        echo '<div style="border-radius:15px;width: 100%; padding: 10px; text-align: center; font-size: 20px; font-weight: bold; background-color: #4a90e2; color: #fff;">';
                        echo 'TOTAL OUT : '.$data['total_out'];
                        echo '</div>';
                        echo '</td>';
                        echo '</tr>';

                        $records = $data['data'];

                        if(count($records) == 0) {
                            echo '<tr><td colspan="4" style="text-align: center"> NO DATA </td></tr>';
                        } else {
                            echo '<tr>
                        <td style="font-weight: bold; font-size: 20px;text-align: center;">BOOKED IN TIME</td>
                        <td colspan="2" style="font-weight: bold; font-size: 20px;text-align: center;">BOOKED OUT TIME</td>
                        <td style="font-weight: bold; font-size: 20px;text-align: center;">DURATION</td>
                    </tr>';

                            foreach ($records as $record) {
                                echo '<tr>';
                                if(!isset($record['in'])){
                                    $in = '';
                                    echo '<td style="text-align: center"></td>';
                                }
                                else{
                                    $in = $record['in'];
                                    echo '<td style="text-align: center">'.date('d/m/Y H:i',strtotime($record['in'])).'</td>';
                                }

                                if(!isset($record['out']))
                                    $out = '';
                                else
                                    $out = date('d/m/Y H:i',strtotime($record['out']));

                                echo '<td colspan="2" style="text-align: center">'.$out.'</td>';

                                if($out != '' && $in != '') {
                                    $duration = strtotime($record['out']) - strtotime($record['in']);

                                    $hours = floor($duration / 3600);
                                    $minutes = floor(($duration / 60) % 60);
                                    $seconds = $duration % 60;

                                    $duration_string = "";
                                    if($hours > 0) {
                                        if($hours < 10)
                                            $duration_string .= "0".$hours.":";
                                        else
                                            $duration_string .= $hours.":";
                                    } else
                                        $duration_string .= "00:";

                                    if($minutes < 10)
                                        $duration_string .= "0".$minutes.":";
                                    else
                                        $duration_string .= $minutes.":";

                                    if($seconds < 10)
                                        $duration_string .= "0".$seconds;
                                    else
                                        $duration_string .= $seconds;

                                    echo '<td style="text-align: center">'.$duration_string.'</td>';
                                } else{
                                    $duration = '';
                                    echo '<td style="text-align: center"></td>';
                                }


                                echo '</tr>';
                            }
                        }

                        echo '</table>';
                        echo '</div>';
                        echo '<div class="col-md-12" style="height:20px;"></div>';
                    }

                    echo '</div>';
                }

                echo '</div>';
                echo '</div>';
                echo '<hr>';
            }

            if($all_sections == true || in_array("section5", $sections_array)) { //Member Activity
                echo '<div class="row">';
                echo '<div class="col-md-12">';
                echo '<h2 style="text-align: center">Members Tool Report</h2>';

                $users_data = get_member_data($report_start_date, $report_end_date, $members_array, $shift);
                $g_data = json_encode($users_data, true);

                echo '<div id="section5_div" style="min-height: 100px;">';

                foreach ($users_data as $key=>$data) {
                    echo '<div class="row">';
                    echo '<div class="col-md-12" style="padding: 10px; font-size: 20px; font-weight: bold">';

                    $q = "SELECT * FROM {$tblUsers} WHERE ID = $key";
                    $r = $db->query($q);
                    $u = mysqli_fetch_object($r);
                    echo $u->username;

                    echo '</div>';
                    echo '<div class="col-md-12">';
                    echo '<h3 style="text-align: center">TOTAL IN</h3>';
                    echo '<div style="display:none;" class="section5-data" id="member_data_'.$key.'">'.json_encode($data['graph']).'</div>';
                    echo '<div id="member_in_'.$key.'" style="height: 300px;"></div>';
                    echo '</div>';

                    echo '<div class="col-md-12">';
                    echo '<h3 style="text-align: center">TOTAL OUT</h3>';
                    echo '<div id="member_out_'.$key.'" style="height: 300px;"></div>';
                    echo '</div>';

                    echo '<div class="col-md-12" style="text-align: center;">';
                    echo '<table align="center">';
                    echo '<tr>';
                    echo '<td style="padding: 10px; width: 160px;">';
                    echo '<div class="total">';
                    echo 'TOTAL IN: <br>';
                    echo $data['total_in'];
                    echo '</div>';
                    echo '</td>';

                    echo '<td style="padding: 10px; width: 160px;">';
                    echo '<div class="total">';
                    echo 'TOTAL OUT: <br>';
                    echo $data['total_out'];
                    echo '</div>';
                    echo '</td>';

                    echo '<td style="padding: 10px; width: 180px;">';
                    echo '<div class="left-in" style="background-color: #ff0500;">';
                    echo 'IN : <br>';
                    echo $data['shift0_total_in'];
                    echo '</div>';
                    echo '<div class="right-out" style="background-color: #ff0500;">';
                    echo 'OUT : <br>';
                    echo $data['shift0_total_out'];
                    echo '</div>';
                    echo '</td>';

                    echo '<td style="padding: 10px; width: 180px;">';
                    echo '<div class="left-in" style="background-color: #df02a4;">';
                    echo 'IN : <br>';
                    echo $data['shift2_total_in'];
                    echo '</div>';
                    echo '<div class="right-out" style="background-color: #df02a4;">';
                    echo 'OUT : <br>';
                    echo $data['shift2_total_out'];
                    echo '</div>';
                    echo '</td>';

                    echo '<td style="padding: 10px; width: 180px;">';
                    echo '<div class="left-in" style="background-color: #0557ff;">';
                    echo 'IN : <br>';
                    echo $data['shift3_total_in'];
                    echo '</div>';
                    echo '<div class="right-out" style="background-color: #0557ff;">';
                    echo 'OUT : <br>';
                    echo $data['shift3_total_out'];
                    echo '</div>';
                    echo '</td>';

                    echo '<td style="padding: 10px; width: 180px;">';
                    echo '<div class="left-in" style="background-color: #ff8f00;">';
                    echo 'IN : <br>';
                    echo $data['shift4_total_in'];
                    echo '</div>';
                    echo '<div class="right-out" style="background-color: #ff8f00;">';
                    echo 'OUT : <br>';
                    echo $data['shift4_total_out'];
                    echo '</div>';
                    echo '</td>';

                    echo '<td style="padding: 10px; width: 180px;">';
                    echo '<div class="left-in" style="background-color: #ede104;">';
                    echo 'IN : <br>';
                    echo $data['shift6_total_in'];
                    echo '</div>';
                    echo '<div class="right-out" style="background-color: #ede104;">';
                    echo 'OUT : <br>';
                    echo $data['shift6_total_out'];
                    echo '</div>';
                    echo '</td>';

                    echo '<td style="padding: 10px; width: 180px;">';
                    echo '<div class="left-in" style="background-color: #02f009;">';
                    echo 'IN : <br>';
                    echo $data['shift12_total_in'];
                    echo '</div>';
                    echo '<div class="right-out" style="background-color: #02f009;">';
                    echo 'OUT : <br>';
                    echo $data['shift12_total_out'];
                    echo '</div>';
                    echo '</td>';

                    echo '</tr>';
                    echo '</table>';
                    echo '</div>';
                    echo '</div>';
                }

                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '<hr>';
            }

            if($all_sections == true || in_array("section6", $sections_array)) {
                echo '<div class="row">';
                echo '<div class="col-md-12">';

                $section6_data = get_report_section6_data($report_start_date, $report_end_date, $members_array, $shift);

                foreach($section6_data as $data) {
                    echo "<div class='row' style='padding: 20px;'>";
                    echo "<h3>".$data['user']."</h3>";
                    echo "<div class='col-md-3' style='font-weight: bold;'>Tool in: ".$data['booked_in']."</div>";
                    echo "<div class='col-md-3' style='font-weight: bold;'>Tool out: ".$data['booked_out']."</div>";
                    echo "<div class='col-md-3'>";
                    $logged_in_count = count($data['login']);
                    echo "<h4 style='font-weight: bold;'>Logged in Total: ".$logged_in_count."</h4>";
                    foreach ($data['login'] as $datum) {
                        echo date('d/m/y H:i', strtotime($datum));
                        echo "<br/>";
                    }
                    echo "</div>";

                    $logged_out_count = count($data['logout']);
                    echo "<div class='col-md-3'>";
                    echo "<h4 style='font-weight: bold;'>Logged out Total: ".$logged_out_count."</h4>";
                    foreach ($data['logout'] as $datum) {
                        echo date('d/m/y H:i', strtotime($datum));
                        echo "<br/>";
                    }
                    echo "</div>";
                    echo "</div>";

                }

                echo '</div>';
                echo '</div>';
                echo '<hr>';
            }

            if($all_sections == true || in_array("section7", $sections_array)) {
                echo '<div class="row">';
                echo '<div class="col-md-12">';

                echo '<h2 style="text-align: center">Activity</h2>';

                echo '<table class="table table-bordered" id="activity_table">
                <thead>
                <tr>
                    <th>DATE</th>
                    <th>TIME</th>
                    <th>MBR</th>
                    <th>TOOL</th>
                    <th>SCANNED IN/OUT</th>
                </tr>
                </thead>
                <tbody>
                ';

                $activities = array();
                if($report_start_date == $report_end_date) {
                    if ($shift != 'all_shift') {
                        $time_set = get_start_end_time($report_start_date, $shift);
                        $start = $time_set['start'];
                        $end = $time_set['end'];
                    } else {
                        $time_set0 = get_start_end_time($report_start_date, "shift1");
                        $time_set1 = get_start_end_time($report_start_date, "shift3");
                        $start = $time_set0['start'];
                        $end = $time_set1['end'];
                    }

                    //Booked in
                    $query = "SELECT * FROM {$tblExportScanData} WHERE DateTimeStamp >= '{$start}' AND DateTimeStamp <= '{$end}'";
                    $result = $db->query($query);

                    while($row= mysqli_fetch_object($result)) {

                        $datetime = explode(" ", $row->DateTimeStamp);
                        $date = convert_date_string($datetime[0]);
                        $time = $datetime[1];

                        $q = "SELECT * FROM {$tblUsers} WHERE id = '{$row->booked_in_user}'";
                        $r = $db->query($q);
                        $user = mysqli_fetch_object($r);
                        $user_name = "";
                        if($user)
                            $user_name = $user->username;

                        $barcode = $row->Barcode;
                        $booked = "In";

                        array_push($activities, [
                            "datetime" => $row->DateTimeStamp,
                            "date" => $date,
                            "time" => $time,
                            "user" => $user_name,
                            "barcode" => $barcode,
                            "booked" => $booked
                        ]);
                    }

                    //Booked out
                    $query = "SELECT * FROM {$tblExportScanData} WHERE updated_left_time >= '{$start}' AND updated_left_time <= '{$end}' AND booked_out_user > 0 ";
                    $result = $db->query($query);

                    while($row= mysqli_fetch_object($result)) {
                        $datetime = explode(" ", $row->updated_left_time);
                        $date = convert_date_string($datetime[0]);
                        $time = $datetime[1];

                        $q = "SELECT * FROM {$tblUsers} WHERE id = '{$row->booked_out_user}'";
                        $r = $db->query($q);
                        $user = mysqli_fetch_object($r);
                        $user_name = "";
                        if($user)
                            $user_name = $user->username;

                        $barcode = $row->Barcode;
                        $booked = "Out";

                        array_push($activities, [
                            "datetime" => $row->updated_left_time,
                            "date" => $date,
                            "time" => $time,
                            "user" => $user_name,
                            "barcode" => $barcode,
                            "booked" => $booked
                        ]);
                    }

                } else {
                    $start_date = $report_start_date;
                    while($start_date <= $report_end_date){
                        if ($shift != 'all_shift') {
                            $time_set = get_start_end_time($start_date, $shift);
                            $start = $time_set['start'];
                            $end = $time_set['end'];
                        } else {
                            $time_set0 = get_start_end_time($start_date, "shift1");
                            $time_set1 = get_start_end_time($start_date, "shift3");
                            $start = $time_set0['start'];
                            $end = $time_set1['end'];

                        }

                        //Booked in
                        $query = "SELECT * FROM {$tblExportScanData} WHERE DateTimeStamp >= '{$start}' AND DateTimeStamp <= '{$end}'";
                        $result = $db->query($query);

                        while($row= mysqli_fetch_object($result)) {
                            $datetime = explode(" ", $row->DateTimeStamp);
                            $date = convert_date_string($datetime[0]);
                            $time = $datetime[1];

                            $q = "SELECT * FROM {$tblUsers} WHERE id = '{$row->booked_in_user}'";
                            $r = $db->query($q);
                            $user = mysqli_fetch_object($r);
                            $user_name = "";
                            if($user)
                                $user_name = $user->username;

                            $barcode = $row->Barcode;
                            $booked = "In";

                            array_push($activities, [
                                "datetime" => $row->DateTimeStamp,
                                "date" => $date,
                                "time" => $time,
                                "user" => $user_name,
                                "barcode" => $barcode,
                                "booked" => $booked
                            ]);
                        }

                        //Booked out
                        $query = "SELECT * FROM {$tblExportScanData} WHERE updated_left_time >= '{$start}' AND updated_left_time <= '{$end}' AND booked_out_user > 0 ";
                        $result = $db->query($query);

                        while($row= mysqli_fetch_object($result)) {
                            $datetime = explode(" ", $row->updated_left_time);
                            $date = convert_date_string($datetime[0]);
                            $time = $datetime[1];

                            $q = "SELECT * FROM {$tblUsers} WHERE id = '{$row->booked_out_user}'";
                            $r = $db->query($q);
                            $user = mysqli_fetch_object($r);
                            $user_name = "";
                            if($user)
                                $user_name = $user->username;

                            $barcode = $row->Barcode;
                            $booked = "Out";

                            array_push($activities, [
                                "datetime" => $row->updated_left_time,
                                "date" => $date,
                                "time" => $time,
                                "user" => $user_name,
                                "barcode" => $barcode,
                                "booked" => $booked
                            ]);
                        }
                        $start_date = date('Y-m-d', strtotime("+1 days", strtotime($start_date)));
                    }
                }

                if(count($activities) > 0) {
                    usort($activities, 'date_compare');
                    foreach ($activities as $activity) {
                        echo '<tr>';
                        echo '<td style="text-align: center">'.$activity['date'].'</td>';
                        echo '<td style="text-align: center">'.$activity['time'].'</td>';
                        echo '<td>'.$activity['user'].'</td>';
                        echo '<td>'.$activity['barcode'].'</td>';
                        echo '<td style="text-align: center;">'.$activity['booked'].'</td>';
                        echo '</tr>';
                    }
                } else {
                    echo "<tr><td style='text-align: center;' colspan='5'> No Data</td></tr>";
                }

                echo '</tbody></table>';
                echo '</div>';
                echo '</div>';
            }

            ?>
        </div>
    </div>
</div>
</body>
</html>
<script>
    $(document).ready(function () {
        var start_date = $("#report_start_date").val();
        var end_date = $("#report_end_date").val();
        var shift = $("#shift").val();

        if($("#section2_in").length > 0 || $("#section2_out").length > 0) {
            $.ajax({
                url: "actions.php",
                method: "post",
                data: {
                    start_date:start_date,
                    end_date:end_date,
                    shift:shift,
                    action:"get_report_section2"
                }
            }).done(function (data) {
                var section2_data = JSON.parse(data);

                if($("#section2_in").length > 0) {
                    var chartData_in = section2_data.in;
                    AmCharts.makeChart( "section2_in", {
                        "type": "serial",
                        "theme": "light",
                        //"depth3D": 20,
                        //"angle": 30,
                        "dataDateFormat": "DD-MM-YYYY JJ:NN",
                        "legend": {
                            "horizontalGap": 10,
                            "useGraphSettings": true,
                            "markerSize": 10
                        },
                        "dataProvider": chartData_in,
                        "valueAxes": [{
                            "stackType": "regular",
                            "axisAlpha": 0,
                            "gridAlpha": 0
                        }],
                        "graphs": [ {
                            "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                            "fillAlphas": 0.8,
                            "labelText": "[[value]]",
                            "lineAlpha": 0.3,
                            "title": "MUST BE THIS SHIFT",
                            "type": "column",
                            "color": "#000000",
                            "lineColor": "#ff0500",
                            "valueField": "0shift"
                        }, {
                            "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                            "fillAlphas": 0.8,
                            "labelText": "[[value]]",
                            "lineAlpha": 0.3,
                            "title": "DUE IN 2 SHIFTS",
                            "type": "column",
                            "color": "#000000",
                            "lineColor": "#df02a4",
                            "valueField": "2shift"
                        }, {
                            "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                            "fillAlphas": 0.8,
                            "labelText": "[[value]]",
                            "lineAlpha": 0.3,
                            "title": "DUE IN 3 SHIFTS",
                            "type": "column",
                            "newStack": true,
                            "color": "#000000",
                            "lineColor": "#0557ff",
                            "valueField": "3shift"
                        }, {
                            "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                            "fillAlphas": 0.8,
                            "labelText": "[[value]]",
                            "lineAlpha": 0.3,
                            "title": "NEXT DAY 4~6 SHIFTS",
                            "type": "column",
                            "color": "#000000",
                            "lineColor": "#ff8f00",
                            "valueField": "4shift"
                        }, {
                            "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                            "fillAlphas": 0.8,
                            "labelText": "[[value]]",
                            "lineAlpha": 0.3,
                            "title": "3~4 DAYS 6~12 SHIFTS",
                            "type": "column",
                            "color": "#000000",
                            "lineColor": "#fff200",
                            "valueField": "6shift"
                        }, {
                            "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                            "fillAlphas": 0.8,
                            "labelText": "[[value]]",
                            "lineAlpha": 0.3,
                            "title": "DUE IN 5 DAYS +",
                            "type": "column",
                            "color": "#000000",
                            "lineColor": "#00ff08",
                            "valueField": "12shift"
                        } ],
                        "categoryField": "time",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "gridAlpha": 0,
                            "position": "left"
                        },
                        "export": {
                            "enabled": true
                        }

                    });

                }

                if($("#section2_out").length > 0) {
                    var chartData_out = section2_data.out;
                    AmCharts.makeChart( "section2_out", {
                        "type": "serial",
                        "theme": "light",
                        //"depth3D": 20,
                        //"angle": 30,
                        "legend": {
                            "horizontalGap": 10,
                            "useGraphSettings": true,
                            "markerSize": 10
                        },
                        "dataProvider": chartData_out,
                        "valueAxes": [ {
                            "stackType": "regular",
                            "axisAlpha": 0,
                            "gridAlpha": 0
                        } ],
                        "graphs": [ {
                            "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                            "fillAlphas": 0.8,
                            "labelText": "[[value]]",
                            "lineAlpha": 0.3,
                            "title": "MUST BE THIS SHIFT",
                            "type": "column",
                            "color": "#000000",
                            "lineColor": "#ff0500",
                            "valueField": "0shift"
                        }, {
                            "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                            "fillAlphas": 0.8,
                            "labelText": "[[value]]",
                            "lineAlpha": 0.3,
                            "title": "DUE IN 2 SHIFTS",
                            "type": "column",
                            "color": "#000000",
                            "lineColor": "#df02a4",
                            "valueField": "2shift"
                        }, {
                            "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                            "fillAlphas": 0.8,
                            "labelText": "[[value]]",
                            "lineAlpha": 0.3,
                            "title": "DUE IN 3 SHIFTS",
                            "type": "column",
                            "newStack": true,
                            "color": "#000000",
                            "lineColor": "#0557ff",
                            "valueField": "3shift"
                        }, {
                            "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                            "fillAlphas": 0.8,
                            "labelText": "[[value]]",
                            "lineAlpha": 0.3,
                            "title": "NEXT DAY 4~6 SHIFTS",
                            "type": "column",
                            "color": "#000000",
                            "lineColor": "#ff8f00",
                            "valueField": "4shift"
                        }, {
                            "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                            "fillAlphas": 0.8,
                            "labelText": "[[value]]",
                            "lineAlpha": 0.3,
                            "title": "3~4 DAYS 6~12 SHIFTS",
                            "type": "column",
                            "color": "#000000",
                            "lineColor": "#fff200",
                            "valueField": "6shift"
                        }, {
                            "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                            "fillAlphas": 0.8,
                            "labelText": "[[value]]",
                            "lineAlpha": 0.3,
                            "title": "DUE IN 5 DAYS +",
                            "type": "column",
                            "color": "#000000",
                            "lineColor": "#00ff08",
                            "valueField": "12shift"
                        } ],
                        "categoryField": "time",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "gridAlpha": 0,
                            "position": "left"
                        },
                        "export": {
                            "enabled": true
                        }

                    } );
                }
            });
        }

        if($("#section3_graph").length > 0 || $("#section3_list").length > 0) {
            $.ajax({
                url: "actions.php",
                method: "post",
                data: {
                    start_date:start_date,
                    end_date:end_date,
                    shift:shift,
                    action:"get_report_section3"
                }
            }).done(function (data) {
                var section3_data = JSON.parse(data);

                if($("#section3_graph").length > 0) {

                    AmCharts.makeChart("section3_graph", {
                        "type": "serial",
                        "theme": "none",
                        "categoryField": "tool",
                        "rotate": true,
                        "startDuration": 1,
                        "categoryAxis": {
                            "gridPosition": "start",
                            "position": "left"
                        },
                        "trendLines": [],
                        "graphs": [
                            {
                                "balloonText": "Overdue:[[value]]hour",
                                "fillAlphas": 0.8,
                                "id": "AmGraph-1",
                                "lineAlpha": 0.2,
                                "title": "Overdue",
                                "type": "column",
                                "valueField": "duration",
                                "colorField": "color"
                            }
                        ],
                        "guides": [],
                        "valueAxes": [
                            {
                                "id": "ValueAxis-1",
                                "position": "top",
                                "axisAlpha": 0
                            }
                        ],
                        "allLabels": [],
                        "balloon": {},
                        "titles": [],
                        "dataProvider": section3_data,
                        "export": {
                            "enabled": true
                        }
                    });
                }

                if($("#section3_list").length > 0) {
                    var table = '<table class="table table-bordered"><thead><tr><th>Tool</th><th>Colour</th><th>Booked in</th><th>Booked out</th><th>Duration</th></tr></thead>';
                    table += '<tbody>';

                    for(var key in section3_data) {
                        table += '<tr>';
                        table += '<td style="text-align: center">' + section3_data[key]['tool'] + '</td>';
                        table += '<td style="text-align: center; padding: 10px;"><div style="width: 80%; height: 20px; background-color: ' + section3_data[key]['color'] +'"></div></td>';
                        table += '<td style="text-align: center">' + section3_data[key]['booked_in'] + '</td>';
                        table += '<td style="text-align: center">' + section3_data[key]['booked_out'] + '</td>';
                        table += '<td style="text-align: center">' + section3_data[key]['duration'] + '</td>';
                        table += '</tr>';
                    }
                    table += '</tbody>';
                    table += '</table>';
                    $("#section3_list").html(table);
                }
            });
        }

        $(".section5-data").each(function () {
            var g5_data = $(this).text();
            var id = $(this).attr('id').replace("member_data_", "");
            var section5_g_data = JSON.parse(g5_data);
            var in_data = section5_g_data.in;
            var out_data = section5_g_data.out;

            var graph_in_div = "member_in_" + id;
            var graph_out_div = "member_out_" + id;

            if($("#"+graph_in_div).length > 0) {
                AmCharts.makeChart( graph_in_div, {
                    "type": "serial",
                    "theme": "light",
                    //"depth3D": 20,
                    //"angle": 30,
                    "dataDateFormat": "DD-MM-YYYY JJ:NN",
                    "legend": {
                        "horizontalGap": 10,
                        "useGraphSettings": true,
                        "markerSize": 10
                    },
                    "dataProvider": in_data,
                    "valueAxes": [ {
                        "stackType": "regular",
                        "axisAlpha": 0,
                        "gridAlpha": 0
                    } ],
                    "graphs": [ {
                        "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                        "fillAlphas": 0.8,
                        "labelText": "[[value]]",
                        "lineAlpha": 0.3,
                        "title": "MUST BE THIS SHIFT",
                        "type": "column",
                        "color": "#000000",
                        "lineColor": "#ff0500",
                        "valueField": "0shift"
                    }, {
                        "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                        "fillAlphas": 0.8,
                        "labelText": "[[value]]",
                        "lineAlpha": 0.3,
                        "title": "DUE IN 2 SHIFTS",
                        "type": "column",
                        "color": "#000000",
                        "lineColor": "#df02a4",
                        "valueField": "2shift"
                    }, {
                        "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                        "fillAlphas": 0.8,
                        "labelText": "[[value]]",
                        "lineAlpha": 0.3,
                        "title": "DUE IN 3 SHIFTS",
                        "type": "column",
                        "newStack": true,
                        "color": "#000000",
                        "lineColor": "#0557ff",
                        "valueField": "3shift"
                    }, {
                        "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                        "fillAlphas": 0.8,
                        "labelText": "[[value]]",
                        "lineAlpha": 0.3,
                        "title": "NEXT DAY 4~6 SHIFTS",
                        "type": "column",
                        "color": "#000000",
                        "lineColor": "#ff8f00",
                        "valueField": "4shift"
                    }, {
                        "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                        "fillAlphas": 0.8,
                        "labelText": "[[value]]",
                        "lineAlpha": 0.3,
                        "title": "3~4 DAYS 6~12 SHIFTS",
                        "type": "column",
                        "color": "#000000",
                        "lineColor": "#fff200",
                        "valueField": "6shift"
                    }, {
                        "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                        "fillAlphas": 0.8,
                        "labelText": "[[value]]",
                        "lineAlpha": 0.3,
                        "title": "DUE IN 5 DAYS +",
                        "type": "column",
                        "color": "#000000",
                        "lineColor": "#00ff08",
                        "valueField": "12shift"
                    } ],
                    "categoryField": "time",
                    "categoryAxis": {
                        "gridPosition": "start",
                        "axisAlpha": 0,
                        "gridAlpha": 0,
                        "position": "left"
                    },
                    "export": {
                        "enabled": true
                    }
                });
            }

            if($("#"+graph_out_div).length > 0) {
                AmCharts.makeChart( graph_out_div, {
                    "type": "serial",
                    "theme": "light",
                    //"depth3D": 20,
                    //"angle": 30,
                    "dataDateFormat": "DD-MM-YYYY JJ:NN",
                    "legend": {
                        "horizontalGap": 10,
                        "useGraphSettings": true,
                        "markerSize": 10
                    },
                    "dataProvider": out_data,
                    "valueAxes": [ {
                        "stackType": "regular",
                        "axisAlpha": 0,
                        "gridAlpha": 0
                    } ],
                    "graphs": [ {
                        "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                        "fillAlphas": 0.8,
                        "labelText": "[[value]]",
                        "lineAlpha": 0.3,
                        "title": "MUST BE THIS SHIFT",
                        "type": "column",
                        "color": "#000000",
                        "lineColor": "#ff0500",
                        "valueField": "0shift"
                    }, {
                        "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                        "fillAlphas": 0.8,
                        "labelText": "[[value]]",
                        "lineAlpha": 0.3,
                        "title": "DUE IN 2 SHIFTS",
                        "type": "column",
                        "color": "#000000",
                        "lineColor": "#df02a4",
                        "valueField": "2shift"
                    }, {
                        "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                        "fillAlphas": 0.8,
                        "labelText": "[[value]]",
                        "lineAlpha": 0.3,
                        "title": "DUE IN 3 SHIFTS",
                        "type": "column",
                        "newStack": true,
                        "color": "#000000",
                        "lineColor": "#0557ff",
                        "valueField": "3shift"
                    }, {
                        "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                        "fillAlphas": 0.8,
                        "labelText": "[[value]]",
                        "lineAlpha": 0.3,
                        "title": "NEXT DAY 4~6 SHIFTS",
                        "type": "column",
                        "color": "#000000",
                        "lineColor": "#ff8f00",
                        "valueField": "4shift"
                    }, {
                        "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                        "fillAlphas": 0.8,
                        "labelText": "[[value]]",
                        "lineAlpha": 0.3,
                        "title": "3~4 DAYS 6~12 SHIFTS",
                        "type": "column",
                        "color": "#000000",
                        "lineColor": "#fff200",
                        "valueField": "6shift"
                    }, {
                        "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                        "fillAlphas": 0.8,
                        "labelText": "[[value]]",
                        "lineAlpha": 0.3,
                        "title": "DUE IN 5 DAYS +",
                        "type": "column",
                        "color": "#000000",
                        "lineColor": "#00ff08",
                        "valueField": "12shift"
                    } ],
                    "categoryField": "time",
                    "categoryAxis": {
                        "gridPosition": "start",
                        "axisAlpha": 0,
                        "gridAlpha": 0,
                        "position": "left"
                    },
                    "export": {
                        "enabled": true
                    }
                });
            }
        });

        $("#activity_table").tableExport({
            formats: ["xlsx"],
            position: "top",
            bootstrap: true
        });

        $(".xlsx").addClass("pull-right");

        $("#print_pdf").on('click', function () {
            $("#report_div").printThis({
                importCSS: false,
                loadCSS: "tooling/css/report_pdf.css",
                printContainer: true
            });
        });

    });
</script>
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
        <div class="col-md-4" style="text-align: center"></div>
        <div class="col-md-4">
            <?php
            echo '<h3 style="color: #0d548b">' .$report_name.'</h3>';
            echo '<h3 style="color: #56545a; text-transform: lowercase;">' .$report_date_string.'</h3>';
            ?>
        </div>
    </div>

    <hr>

    <?php

    echo '<div class="row">';
    echo '<div class="col-md-12">';
    echo '<table id="report_table" class="table table-bordered table-responsive">';

    if($all_sections == true || in_array("section1", $sections_array)) {
        echo '<tr>';
        echo '<td class="title-td" colspan="7">Tool Report</td>';
        echo '</tr>';
        $data = get_report_section1_data($report_start_date, $report_end_date, $shift);

        foreach($data as $item){
            echo '<tr>';
            foreach($item as $index=>$value){
                echo '<td style="padding: 10px;text-align: center; font-weight: bold">';
                echo $value['key'];
                echo '</td>';
            }
            echo '</tr>';
            echo '<tr>';
            foreach($item as $index=>$value){
                echo '<td style="padding: 10px;text-align: center">';
                echo $value['value'];
                echo '</td>';
            }
            echo '</tr>';
        }
    }

    if($all_sections == true || in_array("section2", $sections_array)) {

        $data = get_report_section2_data($report_start_date, $report_end_date, $shift);

        if($section2_booked_in == 1) {
            $in_data = $data['in'];
            echo '<tr>';
            echo '<td class="title-td" colspan="8">Tool In</td>';
            echo '</tr>';

            echo "<tr>";
            for($i=0;$i<7;$i++){
                if($i==0)
                    echo "<td style='text-align: center; font-weight: bold;'>Time</td>";
                else
                    echo "<td style='text-align: center; font-weight: bold;'>".$priority[$i-1]."</td>";
            }
            echo "</tr>";

            foreach($in_data as $datum) {
                echo "<tr>";
                foreach ($datum as $item) {
                    echo "<td style='text-align: center;'>".$item."</td>";
                }
                echo "<tr>";
            }
        }

        if($section2_booked_out == 1) {
            $out_data = $data['out'];
            echo '<tr>';
            echo '<td class="title-td" colspan="8">Tool Out</td>';
            echo '</tr>';

            echo "<tr>";
            for($i=0;$i<7;$i++){
                if($i==0)
                    echo "<td style='text-align: center; font-weight: bold;'>Time</td>";
                else
                    echo "<td style='text-align: center; font-weight: bold;'>".$priority[$i-1]."</td>";
            }
            echo "</tr>";

            foreach($out_data as $datum) {
                echo "<tr>";
                foreach ($datum as $item) {
                    echo "<td style='text-align: center;'>".$item."</td>";
                }
                echo "<tr>";
            }
        }

    }

    if($all_sections == true || in_array("section3", $sections_array)) {

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

        echo '<tr>';
        echo '<td class="title-td" colspan="7">Tool Overdue</td>';
        echo '</tr>';

        echo "<tr>";
        echo "<td style='text-align: center; font-weight: bold;' colspan='3'>Tool</td>";
        echo "<td style='text-align: center; font-weight: bold;'>Colour</td>";
        echo "<td style='text-align: center; font-weight: bold;'>Booked in</td>";
        echo "<td style='text-align: center; font-weight: bold;'>Booked out</td>";
        echo "<td style='text-align: center; font-weight: bold;'>Duration</td>";
        echo "</tr>";

        if(count($data) > 0) {
            foreach ($data as $item) {
                echo "<tr>";
                echo "<td colspan='3'>".$item['tool']."</td>";
                echo "<td style='background-color: ".$item['color']."'> </td>";
                echo "<td style='text-align: center;'>".$item['booked_in']."</td>";
                echo "<td style='text-align: center;'>".$item['booked_out']."</td>";
                echo "<td style='text-align: center;'>".$item['duration']." hours</td>";
                echo "</tr>";
            }
        }

        else {
            echo '<tr>';
            echo '<td colspan="7" style="text-align: center;">No Tool Overdue Data</td>';
            echo '</tr>';
        }
    }

    if(($all_sections == true || in_array("section4", $sections_array)) && $section4_hide_list == 0) {

        echo '<tr>';
        echo '<td class="title-td" colspan="7">Tool Activity</td>';
        echo '</tr>';

        //$tools_array
        $tools_data = $tools_data = get_tools_data($report_start_date, $report_end_date, $tools_array, $shift);
        foreach ($tools_data as $key=>$data) {
            echo '<tr style=" background-color: #f4f4f3;">';
            echo '<td style="font-size: 20px; font-weight: bold; width: 360px;" colspan="2">'.$key.'</td>';
            echo '<td style="font-size: 20px; font-weight: bold;">';
            echo 'TOOL COLOUR:';
            echo '<div class="" style="border-radius:5px;background-color: '.$data['color'].'; width: 60px; height: 30px; display: inline-block"></div>';
            echo '</td>';
            echo '<td style="padding: 10px; width: 300px;" colspan="2">';
            echo 'TOTAL IN : '.$data['total_in'];
            echo '</td>';
            echo '<td style="padding: 10px; width: 300px;" colspan="2">';
            echo 'TOTAL OUT : '.$data['total_out'];
            echo '</td>';
            echo '</tr>';

            $records = $data['data'];

            if(count($records) == 0) {
                echo '<tr><td colspan="7" style="text-align: center"> NO DATA </td></tr>';
            } else {
                echo '<tr>
                            <td style="font-weight: bold; font-size: 20px;text-align: center;" colspan="3">BOOKED IN TIME</td>
                            <td colspan="3" style="font-weight: bold; font-size: 20px;text-align: center;">BOOKED OUT TIME</td>
                            <td style="font-weight: bold; font-size: 20px;text-align: center;">DURATION</td>
                        </tr>';

                foreach ($records as $record) {
                    echo '<tr>';
                    if(!isset($record['in'])){
                        $in = '';
                        echo '<td style="text-align: center" colspan="3"></td>';
                    }
                    else{
                        $in = $record['in'];
                        echo '<td style="text-align: center" colspan="3">'.date('d/m/Y H:i',strtotime($record['in'])).'</td>';
                    }

                    if(!isset($record['out']))
                        $out = '';
                    else
                        $out = date('d/m/Y H:i',strtotime($record['out']));

                    echo '<td colspan="3" style="text-align: center">'.$out.'</td>';

                    if($out != '' && $in != '') {
                        $duration = strtotime($out) - strtotime($in);
                        echo '<td style="text-align: center">'.gmdate("H:i:s", $duration).'</td>';
                    } else{
                        $duration = '';
                        echo '<td style="text-align: center"></td>';
                    }
                    echo '</tr>';
                }
            }
        }
    }

    if($all_sections == true || in_array("section5", $sections_array)) {
        echo '<tr>';
        echo '<td class="title-td" colspan="7">Members Tool Report</td>';
        echo '</tr>';

        $users_data = get_member_data($report_start_date, $report_end_date, $members_array, $shift);

        foreach ($users_data as $key=>$data) {

            $q = "SELECT * FROM {$tblUsers} WHERE ID = $key";
            $r = $db->query($q);
            $u = mysqli_fetch_object($r);

            echo '<tr>';
            echo '<td style="font-weight: bold;" colspan="7">'.$u->username.'</td>';
            echo '</tr>';

            echo "<tr>";
            echo "<td style='text-transform: uppercase;text-align: center;'>Total In</td>";
            foreach ($priority as $item) {
                echo "<td style='text-transform: uppercase;text-align: center;'>".$item." IN</td>";
            }
            echo "</tr>";

            echo "<tr>";
            echo "<td style='text-align: center;'>".$data['total_in']."</td>";
            echo "<td style='text-align: center;'>".$data['shift0_total_in']."</td>";
            echo "<td style='text-align: center;'>".$data['shift2_total_in']."</td>";
            echo "<td style='text-align: center;'>".$data['shift3_total_in']."</td>";
            echo "<td style='text-align: center;'>".$data['shift4_total_in']."</td>";
            echo "<td style='text-align: center;'>".$data['shift6_total_in']."</td>";
            echo "<td style='text-align: center;'>".$data['shift12_total_in']."</td>";
            echo "</tr>";


            echo "<tr>";
            echo "<td style='text-transform: uppercase;text-align: center;'>Total Out</td>";
            foreach ($priority as $item) {
                echo "<td style='text-transform: uppercase;text-align: center;'>".$item." IN</td>";
            }
            echo "</tr>";

            echo "<tr>";
            echo "<td style='text-align: center;'>".$data['total_in']."</td>";
            echo "<td style='text-align: center;'>".$data['shift0_total_out']."</td>";
            echo "<td style='text-align: center;'>".$data['shift2_total_out']."</td>";
            echo "<td style='text-align: center;'>".$data['shift3_total_out']."</td>";
            echo "<td style='text-align: center;'>".$data['shift4_total_out']."</td>";
            echo "<td style='text-align: center;'>".$data['shift6_total_out']."</td>";
            echo "<td style='text-align: center;'>".$data['shift12_total_out']."</td>";
            echo "</tr>";
        }

    }

    if($all_sections == true || in_array("section6", $sections_array)) {
        echo '<tr>';
        echo '<td class="title-td" colspan="7">Members Log</td>';
        echo '</tr>';

        $section6_data = get_report_section6_data($report_start_date, $report_end_date, $members_array, $shift);

        foreach($section6_data as $data) {
            echo '<tr>';
            echo '<td style="font-weight: bold;" colspan="7">'.$data['user'].'</td>';
            echo '</tr>';
            echo "<tr>";
            echo "<td>Tool in: ".$data['booked_in']."</td>";
            echo "<td>Tool out: ".$data['booked_out']."</td>";
            $logged_in_count = count($data['login']);
            echo "<td>Logged in Total: ".$logged_in_count."<br/>";
            foreach ($data['logout'] as $datum) {
                echo date('d/m/y H:i', strtotime($datum));
                echo "<br/>";
            }
            echo "</td>";

            $logged_out_count = count($data['logout']);
            echo "<td>Logged in Total: ".$logged_in_count."<br/>";
            foreach ($data['logout'] as $datum) {
                echo date('d/m/y H:i', strtotime($datum));
                echo "<br/>";
            }
            echo "</td>";
            echo "</tr>";
        }
    }

    if($all_sections == true || in_array("section7", $sections_array)) {

        echo '<tr>';
        echo '<td class="title-td" colspan="7">Activity</td>';
        echo '</tr>';

        echo '<tr>
                    <th>DATE</th>
                    <th>TIME</th>
                    <th colspan="2">MBR</th>
                    <th colspan="2">TOOL</th>
                    <th>SCANNED IN/OUT</th>
                </tr>';

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
                echo '<td colspan="2">'.$activity['user'].'</td>';
                echo '<td colspan="2">'.$activity['barcode'].'</td>';
                echo '<td style="text-align: center;">'.$activity['booked'].'</td>';
                echo '</tr>';
            }
        } else {
            echo "<tr><td style='text-align: center;' colspan='7'> No Activity Data</td></tr>";
        }

    }

    echo '</table>';
    echo "</div>";
    echo "</div>";






?>


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
                                "valueField": "overdue",
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
                    var table = '<table class="table table-bordered"><thead><tr><th>Tool</th><th>Colour</th><th>Booked in</th><th>Booked out</th><th>Duration</th><th>Overdue</th></tr></thead>';
                    table += '<tbody>';

                    for(var key in section3_data) {
                        table += '<tr>';
                        table += '<td style="text-align: center">' + section3_data[key]['tool'] + '</td>';
                        table += '<td style="text-align: center; padding: 10px;"><div style="width: 80%; height: 20px; background-color: ' + section3_data[key]['color'] +'"></div></td>';
                        table += '<td style="text-align: center">' + section3_data[key]['booked_in'] + '</td>';
                        table += '<td style="text-align: center">' + section3_data[key]['booked_out'] + '</td>';
                        table += '<td style="text-align: center">' + section3_data[key]['duration'] + '</td>';
                        table += '<td style="text-align: center">' + section3_data[key]['overdue'] + '</td>';
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



        $("#report_table").tableExport({
            formats: ["csv"],
            position: "top",
            bootstrap: true
        });

        $(".xlsx").addClass("pull-left");

    });
</script>
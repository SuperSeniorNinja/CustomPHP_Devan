<?php
require_once("./config/config.php");
require_once('users.php');

if (empty($_SESSION['username'])) {
    header('Location: index.php');
}

//Initialize all data
$wip_query = "SELECT * FROM {$tblWipShiftSummary} ORDER BY WIPShiftIndex DESC limit 1";
$wip_result = $db->query($wip_query);
$wip = mysqli_fetch_object($wip_result);

//Get time setting data
$time_query = "SELECT * FROM {$tblShiftSetting} WHERE date = {$weekToday}";
$time_result = $db->query($time_query);
$time_set = mysqli_fetch_object($time_result);

$time_setting = json_decode($time_set->timeset, true);

if($time_setting[1]['start'] != "00:00" || $time_setting[1]['end'] != "00:00") {
    $today_start1 = $today . " " . $time_setting[1]['start'] . ":00";
    $today_end1 = $today . " " . $time_setting[1]['end'] . ":59";

    if(strtotime($today_start1) > strtotime($today_end1)) {
        $today_end1 = date('Y-m-d H:i:s', strtotime("+1 days", strtotime($today_end1)));
    }

    $last_shift_start = $today_start1;
    $last_shift_end = $today_end1;
}

if($time_setting[2]['start'] != "00:00" || $time_setting[2]['end'] != "00:00") {

    $today_start2 = $today . " " . $time_setting[2]['start'] . ":00";
    $today_end2 = $today . " " . $time_setting[2]['end'] . ":59";

    if(strtotime($today_start2) > strtotime($today_end2)) {
        $today_end2 = date('Y-m-d H:i:s', strtotime("+1 days", strtotime($today_end2)));
    }

    /*if ($current > $today_start2 && $current < $today_end2) {

    } else {
        $today_start2 = date('Y-m-d H:i:s', strtotime("-1 days", strtotime($today_start2)));
        $today_end2 = date('Y-m-d H:i:s', strtotime("-1 days", strtotime($today_end2)));
    }*/

    $last_shift_start = $today_start2;
    $last_shift_end = $today_end2;
}

if($time_setting[3]['start'] != "00:00" || $time_setting[3]['end'] != "00:00") {
    if ($current > $today . " " . $time_setting[3]['start'] . ":00" && $current < $tomorrow . " " . $time_setting[3]['end'] . ":59") {
        $today_start3 = $today . " " . $time_setting[3]['start'] . ":00";
        $today_end3 = $tomorrow . " " . $time_setting[3]['end'] . ":59";
    } else {
        $today_start3 = $yesterday . " " . $time_setting[3]['start'] . ":00";
        $today_end3 = $today . " " . $time_setting[3]['end'] . ":59";
    }

    $last_shift_start = $today_start3;
    $last_shift_end = $today_end3;
}

$check_create_new = 0;

if ($wip) {

    ////GET past time from start
    $from_start = strtotime($current) - strtotime($wip->StatusTime);

    //Get Previous Shift
    $shift_id = $wip->WIPShiftIndex;
    //$carried_forward = $wip->BI;
    $last_shift_time = $wip->StatusTime;

    $view_shift_id = 1;
    //$left_time = 480*60;

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


    //if is not started first shift of today
    if(isset($today_start1)) {
        if ($last_shift_time < $today_start1 && $last_shift_time < $last_shift_start) {
            $carried_forward = $this_shift + $two_shift + $three_shift + $four_six_shift + $six_twelve_shift + $five_days_shift;

            $insert = "INSERT INTO {$tblWipShiftSummary} (StatusTime, Qty, WIP_8, WIP_8_12, WIP_12, BI, BO, Shift, Carried_forward) 
                                          VALUES ('{$current}',0,0,0,0,0,0,'',{$carried_forward})";

            $result = $db->query($insert);
            $shift_id = $db->insert_id;

            $check_create_new = 1;

            if ($current > $today_start1 && $current < $today_end1) {
                if ($time_setting[1]['end'] != "0:00")
                    $end_time = $today . " " . $time_setting[1]['end'] . ":00";
                else
                    $end_time = $today . " 08:00:00";

                $view_shift_id = 1;
            } else if ($current > $today_start2 && $current < $today_end2) {
                $end_time = $today . " " . $time_setting[2]['end'] . ":00";
                $view_shift_id = 2;
            } else {// if($current > $today_start3 && $current < $today_end3) {
                if ($current > $today . " " . $time_setting[3]['start'] . ":00" && $current < $tomorrow . " " . $time_setting[3]['end'] . ":59") {
                    $end_time = $tomorrow . " " . $time_setting[3]['end'] . ":59";
                } else {
                    $end_time = $today . " " . $time_setting[3]['end'] . ":59";
                }

                $view_shift_id = 3;
            }
            $left_time = (strtotime($end_time) - strtotime($current));
        } else {
            if ($last_shift_time >= $today_start1 && $last_shift_time < $today_end1) {
                $end_time = $today_end1;
                $left_time = (strtotime($end_time) - strtotime($current));
                $view_shift_id = 2;
            }
        }
    }

    if (isset($today_start2) && $last_shift_time >= $today_start2 && $last_shift_time < $today_end2) {
        /*if ($time_setting[2]['end'] != "0:00")
            $end_time = $today . " " . $time_setting[2]['end'] . ":59";
        else
            $end_time = $today . " 15:59:59";*/
        $end_time = $today_end2;
        $left_time = (strtotime($end_time) - strtotime($current));
        $view_shift_id = 2;
    }

    if (isset($today_start3) && $last_shift_time >= $today_start3 && $last_shift_time < $today_end3) {
        /*if ($current > $today . " " . $time_setting[3]['start'] . ":00" && $current < $tomorrow . " " . $time_setting[3]['end'] . ":59") {
            $end_time = $tomorrow . " " . $time_setting[3]['end'] . ":59";
        } else {
            $end_time = $today . " " . $time_setting[3]['end'] . ":59";
        }*/
        $end_time = $today_end3;
        $left_time = (strtotime($end_time) - strtotime($current));
        $view_shift_id = 3;
    }

    $current_shift_no = $view_shift_id;

    //update_in_process($shift_id);

    $wip_query = "SELECT * FROM {$tblWipShiftSummary} WHERE WIPShiftIndex = '{$shift_id}' ";
    $wip_result = $db->query($wip_query);
    $wip = mysqli_fetch_object($wip_result);

    $carried_forward = $wip->Carried_forward;

    $booked_in = $wip->BI;
    $booked_out = $wip->BO;

    if(!isset($left_time)) {
        if($current > $today_end1 && $current < $today_start2) {
            $left_time = (strtotime($today_start2) - strtotime($current));
        }

        if($current < $today_start1) {
            $left_time = (strtotime($today_start1) - strtotime($current));
        }

        $next_start = date('Y-m-d H:i:s', strtotime("+1 days", strtotime($today_start1)));
        if(isset($today_start3)) {
            if($current > $today_end2 && $current < $today_start3) {
                $left_time = (strtotime($today_start3) - strtotime($current));
            } else {
                $left_time = (strtotime($next_start) - strtotime($current));
            }
        } else {
            if($current > $today_end2 && $current < $next_start){
                $left_time = (strtotime($next_start) - strtotime($current));
            }
        }


    }
} else {
    $shifted_time = date("m/d/Y H:i:s");

    //Get In Process
    $qty = 0;
    $wip_8 = 0;
    $wip_8_12 = 0;
    $wip_12 = 0;
    $total_wip = 0;

    //Get Current Shift
    $shift_id = 1;
    $carried_forward = 0;
    $booked_in = 0;
    $booked_out = 0;

    if(isset($today_start1)) {
        $insert = "INSERT INTO {$tblWipShiftSummary} (StatusTime, Qty, WIP_8, WIP_8_12, WIP_12, BI, BO, Shift, Carried_forward) 
                                          VALUES ('{$current}','{$qty}','{$wip_8}','{$wip_8_12}','{$wip_12}',0,0,'Start',0)";
        $result = $db->query($insert);
    }

    $this_shift = 0;
    $two_shift = 0;
    $three_shift = 0;
    $four_six_shift = 0;
    $six_twelve_shift = 0;
    $five_days_shift = 0;
    $view_shift_id = 1;

    if(isset($today_start1)) {

    }
    if (isset($today_start1) && $current >= $today_start1 && $current < $today_end1) {
        if ($time_setting[1]['end'] != "0:00")
            $end_time = $today . " " . $time_setting[1]['end'] . ":59";
        else
            $end_time = $today . " 08:00:00";
        $left_time = (strtotime($end_time) - strtotime($current));
        $view_shift_id = 1;
    }

    if (isset($today_start2) && $current >= $today_start2 && $current < $today_end2) {
        if ($time_setting[2]['end'] != "0:00")
            $end_time = $today . " " . $time_setting[2]['end'] . ":59";
        else
            $end_time = $today . " 15:59:59";
        $left_time = (strtotime($end_time) - strtotime($current));
        $view_shift_id = 2;
    }

    if (isset($today_start3) && $current >= $today_start3 && $current < $today_end3) {
        if ($current > $today . " " . $time_setting[3]['start'] . ":00" && $current < $tomorrow . " " . $time_setting[3]['end'] . ":59") {
            $end_time = $tomorrow . " " . $time_setting[3]['end'] . ":59";
        } else {
            $end_time = $today . " " . $time_setting[3]['end'] . ":59";
        }

        $left_time = (strtotime($end_time) - strtotime($current));
        $view_shift_id = 3;
    }

    $current_shift_no = $view_shift_id;

    $from_start = 0;

    $check_create_new = 1;
}

$day_no = date('w')+1;
$current_time = date('g:i:s A');
$hours = date('G');

/*if($carried_forward < 0 ) {
    $carried_forward = 0;
}*/

$carried_forward = $this_shift + $two_shift + $three_shift + $four_six_shift + $six_twelve_shift + $five_days_shift;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<meta http-equiv="refresh" content="300">-->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barcode Input</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
    <title>@yield('title')</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link type="text/css" href="css/bootstrap-timepicker.min.css" />
    <link rel="stylesheet" type="text/css" href="css/datatables.min.css"/>
    <link href="css/style.css" rel="stylesheet" />

    <link rel="stylesheet" href="css/font-awesome.css">
    <link rel="stylesheet" href="assets/css/jquery.mCustomScrollbar.min.css" />
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/custom-themes.css">

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js')}}"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js')}}"></script>
    <![endif]-->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/moment.min.js"></script>
</head>
<body onload="startTime()">
<div class="page-wrapper chiller-theme">
    <?php
    include ('menu.php');
    ?>
    <!-- sidebar-wrapper  -->
    <main class="page-content">
        <div class="container-fluid">
            <div class="row">
                <?php
                require_once ('header.php');
                ?>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6" style="text-align: center">
                    <h5>Change Frequency</h5>
                    <div class="row" style="text-transform: uppercase">
                        <div class="col-md-2 change-frequency this-shift">
                            Must Be <br/> This Shift
                        </div>
                        <div class="col-md-2 change-frequency two-shift">
                            Due In <br/> 2 Shifts
                        </div>
                        <div class="col-md-2 change-frequency three-shift">
                            Due In <br/> 3 Shifts
                        </div>
                        <div class="col-md-2 change-frequency four-six-shift">
                            Next Day <br/> 4~6 Shifts
                        </div>
                        <div class="col-md-2 change-frequency six-twelve-shift">
                            3~4 Days <br/> 6~12 Shifts
                        </div>
                        <div class="col-md-2 change-frequency five-days-shift">
                            Due In <br/> 5 Days +
                        </div>
                        <div class="col-md-12" style="height: 10px;"></div>
                        <div class="col-md-2 change-frequency this-shift number">
                            <span id="this_shift"><?php echo $this_shift; ?></span>
                        </div>
                        <div class="col-md-2 change-frequency two-shift number">
                            <span id="two_shift"><?php echo $two_shift; ?></span>
                        </div>
                        <div class="col-md-2 change-frequency three-shift number">
                            <span id="three_shift"><?php echo $three_shift; ?></span>
                        </div>
                        <div class="col-md-2 change-frequency four-six-shift number">
                            <span id="four_six_shift"><?php echo $four_six_shift; ?></span>
                        </div>
                        <div class="col-md-2 change-frequency six-twelve-shift number">
                            <span id="six_twelve_shift"><?php echo $six_twelve_shift; ?></span>
                        </div>
                        <div class="col-md-2 change-frequency five-days-shift number">
                            <span id="five_days_shift"><?php echo $five_days_shift; ?></span>
                        </div>
                    </div>

                    <div class="row" style="margin-top: 0px;">
                        <div class="col-md-6 work-space" >
                            <div class="panel panel-primary">
                                <div class="panel-heading">Barcode</div>
                                <div class="panel-body" style="padding-top: 16px;">
                                    <div><label id="input_title">barcode input</label></div>
                                    <div id="barcode_div" onclick="LockTarget();">
                                        <input type="text" name="barcode" id="barcode" class="form-control in" style="text-align: left;">
                                        <input type="hidden" name="shift_id" id="shift_id" value="<?php echo $shift_id; ?>">
                                        <input type="hidden" name="request_scan" id="request_scan">
                                        <input type="hidden" name="request_location" id="request_location">
                                        <input type="hidden" name="input_kind" id="input_kind" value="tool">

                                    </div>
                                    <div id="alert_div" style="padding-top: 10px;"></div>

                                    <div id="locktarget" style="display: none">Target</div>

                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 work-space">
                            <div class="panel panel-primary">
                                <div class="panel-heading">Current Shift</div>
                                <div class="panel-body" style="padding: 0px;">
                                    <table class="table current-shift-table" align="center">
                                        <thead>
                                        <tr>
                                            <th width="33%">Carried <br/> Forward</th>
                                            <th width="33%">Booked <br/> In</th>
                                            <th width="33%">Booked <br/> Out</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td id="carried_forward" class="number2"><?php echo $carried_forward; ?></td>
                                            <td id="booked_in" class="number2"><?php echo $booked_in; ?></td>
                                            <td id="booked_out" class="number2"><?php echo $booked_out; ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="panel panel-primary">
                            <div class="panel-heading">Barcode Booked In</div>
                            <div class="panel-body" style="padding: 5px; min-height: 400px;">
                                <table class="table table-striped data-table" id="barcode_table">
                                    <thead>
                                    <tr>
                                        <th style="width: 20%;">Machine Number</th>
                                        <th style="width: 25%;">Tool Location</th>
                                        <th style="width: 20%;">Checked In</th>
                                        <!--th style="width: 10%;text-align: center">Hrs</th-->
                                        <th style="width: 15%;text-align: center">Tool Priority</th>
                                        <th style="width: 10%;"></th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="row" style="padding-top: 20px;">
                        <div class="col-md-5">
                            <h3 style="display: inline-block">Current Shift:&nbsp;&nbsp;<span id="current_shift_no"><?php echo $current_shift_no; ?></span></h3>
                            <a style="cursor: pointer;" id="shift_setting" href="shift_setting.php"><img src="./images/settings.png" style="width: 25px; height: 25px;margin-top: -10px;"></a>
                        </div>
                        <div class="col-md-7"><h3>Time Left of Shift:&nbsp;&nbsp; <span id="left_time"></span></h3><input type="hidden" value="<?php echo $left_time?>" id="start_left_time"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-primary">
                                <div class="panel-heading">PRIORITY</div>
                                <div class="panel-body" style="padding: 5px 15px; min-height: 704px;" id="priority_table">
                                    <table class="table barcode-table table-striped data-table" id="p_table">
                                        <thead>
                                        <tr>
                                            <th style="width: 30%;text-align: center">Machine Number</th>
                                            <th style="width: 30%;text-align: center">Tool Location</th>
                                            <th style="width: 20%;text-align: center">Checked In</th>
                                            <!--th style="width: 10%;text-align: center">Hrs</th-->
                                            <th style="width: 20%;text-align: center">Tool Priority</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<input type='hidden' name='scanned_barcode' id='scanned_barcode' value=''>
<input type='hidden' name='end_time' id='end_time' value='<?php echo $end_time; ?>'>
<?php
mysqli_close($db);
?>
</body>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-timepicker.min.js"></script>
<script src="js/datatables.min.js"></script>
<script src="js/custom.js"></script>

<script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
<script src="assets/js/custom.js"></script>
<script>
    var locktarget = document.querySelector('#locktarget'),
        lock_log = document.querySelector('#basic-log');

    var pointerlockchangeIsFiredonRequest = false;
    var posX = posY = 0;
    var event_counter = 0;
    var request_counter = 0;


    document.addEventListener("pointerlockchange", function () {
        event_counter++;

        if (event_counter === 1) {
            pointerlockchangeIsFiredonRequest = true;
            runRequestPointerLockTest();
        } else if (event_counter === 2) {
            runExitPointerLockTest();
        } else if (event_counter === 3) {
            runReEnterPointerLockTest()
        } else if (event_counter > 104) {
            runRepeatLockPointerTest();
        }
    });

    function runRequestPointerLockTest() {
        posX = window.screenX;
        posY = window.screenY;

        /*requestPointerLockTest.step(function() {
         assert_true(pointerlockchangeIsFiredonRequest === true, "pointerlockchange is fired when requesting pointerlock");
         assert_true(document.pointerLockElement === locktarget, "pointer is locked at the target element");
         });*/

        //lock_log.innerHTML = "Pointer is locked on the target element;";

        //requestPointerLockTest.done();
    }

    function runExitPointerLockTest() {
        locktarget.requestPointerLock(); // To re-enter pointer lock

        /*exitPointerLockTest.step(function() {
         assert_true(document.pointerLockElement === null, "pointer is unlocked");
         assert_equals(posX, window.screenX, "mouse cursor X is at the same location that it was when pointer lock was entered");
         assert_equals(posY, window.screenY, "mouse cursor Y is at the same location that it was when pointer lock was entered");
         });

         lock_log.innerHTML = "Status: Exited pointer lock; Please click the 'Re-enter Lock' button and exit the lock.";

         exitPointerLockTest.done();*/
    }

    function runReEnterPointerLockTest() {
        /*reenterPointerLockTest.step(function() {
         assert_true(document.pointerLockElement === locktarget, "Pointer is locked again without engagement gesture");
         });

         lock_log.innerHTML = "Status: Exited pointer lock; Please click the 'Repeat Lock' button and exit the lock.";

         reenterPointerLockTest.done();*/
    }

    function runRepeatLockPointerTest() {
        repeatLockPointerTest.step(function () {
            assert_equals(request_counter + 5, event_counter, "Each requestPointerLock() will fire a pointerlockchange event");
        });

        lock_log.innerHTML = "Status: Test over.";

        repeatLockPointerTest.done();
    }

    function LockTarget() {
        locktarget.requestPointerLock();
    }

    function startTime() {
        var today = new Date();

        var h = today.getHours();
        var m = today.getMinutes();
        var s = today.getSeconds();

        m = checkTime(m);
        s = checkTime(s);

        var am_pm = today.getHours() >= 12 ? "PM" : "AM";

        $('#current_time').text(h + ":" + m + ":" + s + ' ' + am_pm);

        var t = setTimeout(startTime, 500);
    }
    function checkTime(i) {
        if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
        return i;
    }
</script>
</html>

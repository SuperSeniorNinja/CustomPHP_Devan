<?php
require_once("./config/config.php");
require_once('users.php');

if (empty($_SESSION['username'])) {
    header('Location: index.php');
}

$query = "SELECT timeset FROM {$tblShiftSetting}";
$result = $db->query($query);
$rows = mysqli_fetch_all($result, MYSQLI_BOTH);

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
<?php
$week = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
?>
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
            <div class="row" style="margin-top: 20px;">
            <form method="post" id="shift_setting_form">
                <table class="table ">
                    <thead>
                    <tr>
                        <th style="width: 100px;"></th>
                        <?php
                        for($i=0; $i<count($week); $i++ ) {
                            ?>
                            <th colspan="2"><?php echo $week[$i];?></th>
                            <?php
                        }
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td></td>
                        <?php
                        for($i=0; $i<count($week); $i++ ) {
                            echo '<td>Start</td>';
                            echo '<td>End</td>';
                        }
                        ?>
                    </tr>
                    <?php
                    for($k = 1; $k<4; $k++) {
                        echo "<tr>";
                        echo "<td>Shift ".$k."</td>";
                        for($i=0; $i<7; $i++ ) {

                            if(isset($rows[$i])) {
                                $timeset = $rows[$i]['timeset'];
                                $times = json_decode($timeset, true);

                            }

                            if(isset($times) && $times[$k]['start']) {
                                $start = $times[$k]['start'];
                            } else {
                                $start = "00:00";
                            }

                            if(isset($times) && $times[$k]['start']) {
                                $end = $times[$k]['end'];
                            } else {
                                $end = "00:00";
                            }

                            echo '<td>';
                            echo '<div class="input-group bootstrap-timepicker timepicker">';
                            echo '<input name="start'.$k.'[]" type="text" class="time-picker form-control input-small" value="'.$start.'" style="min-width:70px;">';
                            echo '</div>';
                            echo '</td>';

                            echo '<td>';
                            echo '<div class="input-group bootstrap-timepicker timepicker">';
                            echo '<input type="text" class="time-picker form-control input-small" name="end'.$k.'[]" value="'.$end.'" style="min-width:70px;">';
                            echo '</div>';
                            echo '</td>';
                        }
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
                <div style="padding: 20px; text-align: right; border-top: 1px solid #dadada">
                    <button type="button" class="btn btn-primary" id="save_shift_time">Save changes</button>
                </div>
                <input type="hidden" name="action" value="shift_setting">
            </form>
            </div>
        </div>
    </main>
</div>
<?php
mysqli_close($db);
?>
</body>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-timepicker.min.js"></script>
<script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
<script src="assets/js/custom.js"></script>
<script>
    $(document).ready(function() {
        $(document).on('click', '#save_shift_time', function () {
            var form = $("#shift_setting_form")
            $.ajax({
                url: "actions.php",
                method: "post",
                data: form.serialize()
            }).done(function (res) {
                if(res =="ok") {
                    alert("Saved successfully");
                } else {
                    alert("Save failed");
                }
            });
        });
    });

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
        if (i < 10) {
            i = "0" + i
        }
        ;  // add zero in front of numbers < 10
        return i;
    }

</script>

</html>
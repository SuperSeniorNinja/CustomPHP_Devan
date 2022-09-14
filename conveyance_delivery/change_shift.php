<?php
require_once("./config/config.php");

if (empty($_SESSION['username'])) {
    header('Location: index.php');
}

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
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js')}}"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js')}}"></script>
    <![endif]-->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/moment.min.js"></script>
</head>
<body>

<nav class="navbar navbar-default">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Barcode Tooling System</a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
                <li style="padding: 15px;">
                    <span style="color: #88898a"><?php echo date('l jS F Y | g:i A'); ?></span>
                </li>
                <li></li>
                <li class="active" style="padding: 0 50px;"><a style="cursor: pointer;">Change Shift</a></li>
                <li><a href="./main.php">Admin<span class="sr-only">(current)</span></a></li>
                <li><a href="./output.php">Display</a></li>
                <li><a href="./logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container" style="padding-top: 20px;">
    <?php

    $wip_query = "SELECT * FROM {$tblWipShiftSummary} ORDER BY StatusTime DESC limit 1";
    $wip_result = $db->query($wip_query);
    $wip = mysqli_fetch_object($wip_result);

    $this_shift_id = $wip->WIPShiftIndex;
    $this_shift_time = $wip->StatusTime;

    $query = "SELECT a.id, a.DateTimeStamp, a.WIPShiftIndex, a.Barcode, b.machine, b.machine_number, b.tool_number, b.tool_location, a.Hrs, a.Bookin 
                FROM {$tblExportScanData} as a INNER JOIN {$tblToolMainData} as b ON a.Barcode = b.tool_number 
                WHERE a.Bookin = 1 ORDER BY a.DateTimeStamp DESC";
    $result = $db->query($query);

    echo "<table class=\"table barcode-table table-striped\">
            <thead>
            <tr>
                <th style=\"width: 20%;text-align: center\">Tool Number</th>
                <th style=\"width: 24%;text-align: center\">Tool Location</th>
                <th style=\"width: 14%;text-align: center\">Checked In</th>
                <th style=\"width: 10%; text-align: center\">Hrs</th>
                <th style=\"width: 10%; text-align: center\">Tool Priority</th>
                <th style=\"width: 12%; text-align: center\">Change Priority</th>
            </tr>
            </thead>
            <tbody>";

    while($row=mysqli_fetch_array($result)){
        echo"<tr id='barcode".$row['id']."'>";
        echo"<td class='barcode' style='text-align: center'>".$row['Barcode']."</td>";
        echo"<td style='text-align: center'>".$row['tool_location']."</td>";
        echo"<td style='text-align: center'>".$row['DateTimeStamp']."</td>";
        echo"<td align='center'>".$row['Hrs']."</td>";
        $color = "#ff0000";

        if($row['WIPShiftIndex'] < $this_shift_id  && $row['WIPShiftIndex'] > $this_shift_id - 3) {
            $color = "#da029a";
        }

        if($row['WIPShiftIndex'] <= $this_shift_id - 3 && $row['WIPShiftIndex'] > $this_shift_id - 4) {
            $color = "#004eff";
        }

        if($row['WIPShiftIndex'] <= $this_shift_id - 4 && $row['WIPShiftIndex'] > $this_shift_id - 6) {
            $color = "#ff8400";
        }

        if($row['WIPShiftIndex'] <= $this_shift_id - 6 && $row['WIPShiftIndex'] > $this_shift_id - 12) {
            $color = "#fff000";
        }

        if($row['WIPShiftIndex'] <= $this_shift_id - 12) {
            $color = "#00ff0c";
        }

        echo"<td style='text-align: center'><div class='priority' style='background-color: ".$color."; height: 20px; width: 100%;' id='tool_".$row['id']."' data-shift='".$row['WIPShiftIndex']."' ></div></td>";
        echo"<td style='text-align: center'>";
        echo "<div id='select".$row['id']."' data-old='".$row['WIPShiftIndex']."' class=\"btn-group my-select\" style='width: 120px;'>
                  <button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                    <span id='selected".$row['id']."'>Change</span> <span class=\"caret\"></span>
                  </button>
                  <ul class=\"dropdown-menu\">
                    <li data-shift='0'><a href=\"#\"><div style='width: 30px; height: 10px; background-color: red; display: inline-block'></div>&nbsp;&nbsp;THIS SHIFT </a></li>
                    <li data-shift='2'><a href=\"#\"><div style='width: 30px; height: 10px; background-color: #da029a; display: inline-block'></div>&nbsp;&nbsp;2 SHIFTS</a></li>
                    <li data-shift='3'><a href=\"#\"><div style='width: 30px; height: 10px; background-color: #004eff; display: inline-block'></div>&nbsp;&nbsp;3 SHIFTS</a></li>
                    <li data-shift='4'><a href=\"#\"><div style='width: 30px; height: 10px; background-color: #ff8400; display: inline-block'></div>&nbsp;&nbsp;4~6 SHIFTS</a></li>
                    <li data-shift='6'><a href=\"#\"><div style='width: 30px; height: 10px; background-color: #fff000; display: inline-block'></div>&nbsp;&nbsp;6~12 SHIFTS</a></li>
                    <li data-shift='12'><a href=\"#\"><div style='width: 30px; height: 10px; background-color: #00ff0c; display: inline-block'></div>&nbsp;&nbsp;5 DAYS +</a></li>
                  </ul>
            </div>";

        echo "</td>";
        echo"</tr>";
    }
    echo"</tbody></table>";
    ?>

</div>

<?php
mysqli_close($db);
?>
</body>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-timepicker.min.js"></script>
<script src="js/custom.js"></script>
<script>
    $(function() {
        $('.my-select').find('li').click(function() {
            var id = $(this).closest('div').attr('id').replace("select","");
            var change = $(this).data('shift');
            var old_shift = $(this).closest('div').data('old');
            //console.log($(this).data('shift'));
            $('#selected'+id).html($(this).html());

            $.ajax({
                url: "actions.php",
                method: "post",
                data: {action:"change_shift", change:change, id:id, old_shift:old_shift},
            }).done(function (res) {
                if(res == "fail") {
                    alert("Can't change shift");
                    $('#selected'+id).html('Change');
                } else {
                    console.log(res);
                    location.reload();
                }

            });
        });
    });
</script>
</html>
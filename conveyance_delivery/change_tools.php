<?php
require_once("./config/config.php");
require_once('users.php');

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
    <title>Tool Admin</title>
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
<style>
    .tools {
        cursor: pointer;
    }

    .tools:hover{
        color: #1b74bf;
    }
</style>

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
            <div class="row" style="margin-bottom: 20px; margin-top: 20px;">
                <div class="col-md-12">
                    <a class="btn btn-primary" id="add_new_button">Add new tool</a> &nbsp;&nbsp;&nbsp;
                    <?php
                    if($count_down == 0) {
                        echo '<a class="btn btn-danger count-down pause_count_down" data-kind="pause_count_down" style="width: 300px;">Pause Count Down</a>';
                        echo '<a class="btn btn-success count-down resume_count_down" data-kind="btn_resume_count_down" style="display: none; width: 300px;">Resume Count Down</a>';
                    } else{
                        echo '<a class="btn btn-danger count-down pause_count_down" id="btn_pause_count_down" style="display: none;width: 300px;">Pause Count Down</a>';
                        echo '<a class="btn btn-success count-down resume_count_down" id="btn_resume_count_down" style="width: 300px;">Resume Count Down</a>';
                    }
                    ?>
                </div>
            </div>

            <?php

            $tool_query = "SELECT * FROM {$tblToolMainData}";
            $tool_result = $db->query($tool_query);

            echo "<table class=\"table table-striped\" id='tools_table'>
            <thead>
            <tr>
                <th style=\"width: 15%;text-align: center\">Machine</th>
                <th style=\"width: 20%;text-align: center\">Machine Number</th>
                <th style=\"width: 20%;text-align: center\">Tool Number</th>
                <th style=\"width: 15%;text-align: center\">Tool Location</th>
                <th style=\"width: 13%;text-align: center\">Checked In</th>
                <!--th style=\"width: 10%; text-align: center\">Hrs</th-->
                <th style=\"width: 10%; text-align: center\">Tool Priority</th>
                <th style=\"width: 7%; text-align: center\">Change Priority</th>
                <th style=\"width: 7%; text-align: center\">Override(mins)</th>
            </tr>
            </thead>
            <tbody>";

            $wip_query = "SELECT * FROM {$tblWipShiftSummary} ORDER BY StatusTime DESC limit 1";
            $wip_result = $db->query($wip_query);
            $wip = mysqli_fetch_object($wip_result);

            $this_shift_id = $wip->WIPShiftIndex;
            $this_shift_time = $wip->StatusTime;

            while($row=mysqli_fetch_array($tool_result)){
                echo"<tr id='barcode".$row['id']."' data-machine='".$row['machine']."' data-machine_number='".$row['machine_number']."' data-tool_number='".$row['tool_number']."' data-tool_location='".$row['tool_location']."' data-override='".$row['override_time']."'>";
                echo"<td class='tools' style='padding-top: 20px;'>".$row['machine']."</td>";
                echo"<td class='tools' style='padding-top: 20px;'>".$row['machine_number']."</td>";
                echo"<td class='tools' style='padding-top: 20px;'>".$row['tool_number']."</td>";
                echo"<td class='tools' style='padding-top: 20px;'>".$row['tool_location']."</td>";


                echo"<td style='text-align: center'>No Scanned</td>";
                /*echo"<td></td><td></td>";*/

                $color = "#ECECEC";

                if($row['priority'] == "0") {
                    $color = "#FF0000";
                }

                if($row['priority'] == "2") {
                    $color = "#da029a";
                }

                if($row['priority'] == "3") {
                    $color = "#004eff";
                }

                if($row['priority'] == "4") {
                    $color = "#ff8400";
                }

                if($row['priority'] == "6") {
                    $color = "#fff000";
                }

                if($row['priority'] == "12") {
                    $color = "#00ff0c";
                }

                echo"<td style='text-align: center; padding-top: 20px;'><div class='priority' style='background-color: ".$color."; height: 20px; width: 100px; margin:0 auto' id='tool_".$row['id']."' data-shift='".$row['priority']."' ></div></td>";
                echo"<td style='text-align: center;'>";
                echo "<div id='select".$row['id']."' data-old='maintool' class=\"btn-group my-select\" style='width: 120px;'>
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
                if($row['override_time'] !="" && $row['override_time'] != null)
                    echo "<td class=\"tools\" style='text-align: center; padding-top: 20px;'>".$row['override_time']."</td>";
                else
                    echo '<td class="tools"></td>';

                echo"</tr>";
            }
            echo"</tbody></table>";
            ?>
        </div>
    </main>
</div>

<div class="modal fade" id="tool_modal" tabindex="-1" role="dialog" aria-labelledby="toolModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="toolModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" action="">
                    <div class="form-group">
                        <label class="control-label col-sm-4" for="machine">Machine:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="machine" placeholder="Enter Machine">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-4" for="machine_number">Machine Number:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="machine_number" placeholder="Enter Machine">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-4" for="tool_number">Tool Number:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="tool_number" placeholder="Enter Machine">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-4" for="tool_location">Tool Location:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="tool_location" placeholder="Enter Machine">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-4" for="tool_location">Override Time:</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="override_time" placeholder="Override Time">
                        </div>
                    </div>
                    <input type="hidden" id="tool_id" name="tool_id" value="0">
                    <input type="hidden" id="old_tool_number" name="old_tool_number" value="0">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="save_tool">Save changes</button>
            </div>
        </div>
    </div>
</div>

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

        $("#tools_table").DataTable({
            "paging":   true,
            "ordering": true,
            "info":     true,
            "searching":true,
            "columnDefs": [{
                "targets": [5,6],
                "orderable": false
            }]
        });

        $(document).on('click', '.tools', function () {
            var tr = $(this).closest('tr');
            var id = tr.attr('id').replace('barcode','');
            var machine = tr.data('machine');
            var machine_number = tr.data('machine_number');
            var tool_number = tr.data('tool_number');
            var tool_location = tr.data('tool_location');
            var override_time = tr.data('override');

            $("#toolModalLabel").text("Edit Tool: " + tool_number);

            $("#tool_id").val(id);
            $("#machine").val(machine);
            $("#machine_number").val(machine_number);
            $("#tool_number").val(tool_number);
            $("#old_tool_number").val(tool_number);
            $("#tool_location").val(tool_location);
            $("#override_time").val(override_time);

            $("#tool_modal").modal('show');
        });

        $(document).on('click', '#add_new_button', function () {
            $("#toolModalLabel").text("New Tool");
            $("#tool_id").val('0');
            $("#machine").val('');
            $("#machine_number").val('');
            $("#tool_number").val('');
            $("#tool_location").val('');
            $("#tool_modal").modal('show');
        });


        $(document).on('click', '#save_tool', function () {
            var tool_id = $("#tool_id").val();
            var machine = $("#machine").val();
            console.log(machine);
            var machine_number = $("#machine_number").val();
            var tool_number = $("#tool_number").val();
            var old_tool_number = $("#old_tool_number").val();
            var tool_location = $("#tool_location").val();
            var override_time = $("#override_time").val();

            if(machine == "") {
                $("#machine").focus();
                return;
            }

            if(machine_number == "") {
                $("#machine_number").focus();
                return;
            }

            if(tool_number == "") {
                $("#tool_number").focus();
                return;
            }

            if(tool_location == "") {
                $("#tool_location").focus();
                return;
            }

            $.ajax({
                url: "actions.php",
                method: "post",
                data: {
                    action:"update_tool",
                    tool_id:tool_id,
                    machine:machine,
                    machine_number:machine_number,
                    tool_number:tool_number,
                    old_tool_number:old_tool_number,
                    tool_location:tool_location,
                    override_time:override_time}
            }).done(function (res) {
                if(res == "same") {
                    alert("Same tool already is existed.");
                    return;
                } if(res == "faile") {
                    alert("Tool information save failed.");
                    return;
                } else {
                    $("#tool_modal").modal('hide');
                    location.reload();
                }
            });
        });

        $(".count-down").on('click', function () {
            var id = $(this).data('kind');
            var kind;

            if(id == 'pause_count_down') {
                kind = 1;
                $(".pause_count_down").hide();
                $(".resume_count_down").show();
            } else{
                kind = 0;
                $(".resume_count_down").hide();
                $(".pause_count_down").show();
            }


            $.ajax({
                url: "actions.php",
                method: "post",
                data: {action:'update_count_down', kind:kind}
            }).done(function (res) {
                console.log(res);
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
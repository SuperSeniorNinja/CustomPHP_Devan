<?php
require_once("./config/config.php");

$graph_date = date('Y-m-d');

if(isset($_POST['graph_date']))
    $graph_date = convert_date_string($_POST['graph_date']);

$shift = "shift1";

if(isset($_POST['graph_shift']))
    $shift = $_POST['graph_shift'];

$scan_filter = "all";

if(isset($_POST['scan_filter']))
    $scan_filter = $_POST['scan_filter'];



$tools = array();

if(isset($_POST['tools'])){
    $tools = $_POST['tools'];
}

$from_date = $graph_date;
if(isset($_POST['from_date'])) {
    $from_date = convert_date_string($_POST['from_date']);
}

$to_date = $graph_date;
if(isset($_POST['to_date'])) {
    $to_date = convert_date_string($_POST['to_date']);
}

$tools_data = get_tools_data($from_date, $to_date, $tools);

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
    <link href="css/select2.min.css" rel="stylesheet">
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
        .btn {
            font-size: 20px;
        }
    </style>
</head>
<body onload="startTime()">
<?php
include ("header.php");
?>

<div class="container">
    <form method="post" action="" id="graph_form" name="graph_form" class="form-inline">
        <div class="row" style="min-height: 40px; background-color: #373950; color: #fff; padding: 10px; margin-top: 10px;">

            <div class="col-md-4">
                <strong>SELECT DATE : </strong>&nbsp;&nbsp;
                <input style="width: 150px; display: inline-block" class="form-control datepicker" name="graph_date"
                       id="graph_date" value="<?php echo convert_date_string($graph_date); ?>" data-provide="datepicker"
                       data-date-end-date="0d">
                <input type="hidden" name="graph_shift" id="graph_shift" value="<?php echo $shift; ?>">
                <input type="hidden" name="scan_filter" id="scan_filter" value="<?php echo $scan_filter; ?>">
                <button type="submit" class="btn btn-primary" id="load_date">Load Date</button>
            </div>


            <div class="col-md-8">
                <strong>SELECT TOOL(S) : </strong>&nbsp;&nbsp;
                <select class="form-control select2" id="tools" name="tools[]" multiple style="width: 100%;">
                    <?php
                    $m_query = "SELECT * FROM {$tblToolMainData}";
                    $m_result = $db->query($m_query);
                    while($tool = mysqli_fetch_object($m_result)){
                        if(in_array($tool->machine_number, $tools)) {
                            echo '<option value="'.$tool->machine_number.'" selected>'.$tool->machine_number.'</option>';
                        }
                        else
                            echo '<option value="'.$tool->machine_number.'">'.$tool->machine_number.'</option>';
                    }
                    ?>
                </select>
            </div>

        </div>

        <div class="row">
            <div class="col-md-12" style="text-align: center;padding: 10px;">

                <div class="form-group">
                    <label for="from_date">FROM:</label>
                    <input type="text" id="from_date" name="from_date" class="form-control datepicker" value="<?php echo convert_date_string($from_date); ?>">
                </div>
                <div class="form-group">
                    <label for="from_date">TO:</label>
                    <input type="text" id="to_date" name="to_date" class="form-control datepicker" value="<?php echo convert_date_string($to_date); ?>">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">GO</button>
                </div>


            </div>
        </div>
    </form>


    <div class="row">
        <?php
        foreach ($tools_data as $key=>$data) {
        ?>
            <div class="col-md-12">
                <table class="table">
                    <tr style=" background-color: #f4f4f3;">
                        <td style="font-size: 20px; font-weight: bold; width: 360px;"><?php echo $key?></td>
                        <td style="font-size: 20px; font-weight: bold;">
                            TOOL COLOUR:
                            <div class="" style="border-radius:5px;background-color: <?php echo $data['color']; ?>; width: 60px; height: 30px; display: inline-block"></div>
                        </td>
                        <td style="padding: 10px; width: 300px;">
                            <div style="border-radius:15px;width: 100%; padding: 10px; text-align: center; font-size: 20px; font-weight: bold; background-color: #4a90e2; color: #fff;">
                                TOTAL IN : <?php echo $data['total_in']; ?>
                            </div>
                        </td>
                        <td style="padding: 10px; width: 300px;">
                            <div style="border-radius:15px;width: 100%; padding: 10px; text-align: center; font-size: 20px; font-weight: bold; background-color: #4a90e2; color: #fff;">
                                TOTAL OUT : <?php echo $data['total_out']; ?>
                            </div>
                        </td>
                    </tr>

                    <?php
                    $records = $data['data'];

                    if(count($records) == 0) {
                        echo '<tr><td colspan="4" style="text-align: center"> NO DATA </td></tr>';
                    } else {
                    ?>
                        <tr>
                            <td style="font-weight: bold; font-size: 20px;text-align: center;">BOOKED IN TIME</td>
                            <td colspan="2" style="font-weight: bold; font-size: 20px;text-align: center;">BOOKED OUT TIME</td>
                            <td style="font-weight: bold; font-size: 20px;text-align: center;">DURATION</td>
                        </tr>
                    <?php

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
                                $out = $record['out'];

                            echo '<td colspan="2" style="text-align: center">'.$out.'</td>';

                            if($out != '' && $in != '') {
                                $duration = strtotime($out) - strtotime($in);
                            } else{
                                $duration = '';
                            }

                            echo '<td style="text-align: center">'.gmdate("H:i:s", $duration).'</td>';
                            echo '</tr>';
                        }

                    }

                    ?>
                </table>
            </div>
            <div class="col-md-12" style="height:20px;"></div>
        <?php
        }
        ?>

    </div>

</div>
<?php
mysqli_close($db);
?>
</body>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/select2.min.js"></script>
<script>
    $(document).ready(function () {

        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
        });

        $(".select2").select2();

        $(".select2").on('change', function () {
            $("#graph_form").submit();

        });

        $('.shift-select').find('li').click(function () {
            $('#selected_shift').html($(this).html());
            var shift = $(this).data('shift');
            $('#select_shift').attr('data-shift', shift);
            $("#graph_shift").val(shift);
            $("#graph_form").submit();
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

        $(document).find('#current_time').text(h + ":" + m + ":" + s + ' ' + am_pm);

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
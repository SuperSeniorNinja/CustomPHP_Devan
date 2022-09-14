<?php
require_once("./config/config.php");
$live_date = date('Y-m-d');
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
<style>
    #week-picker-wrapper .datepicker .datepicker-days tr td.active ~ td, #week-picker-wrapper .datepicker .datepicker-days tr td.active {
        color: #fff;
        background-color: #04c;
        border-radius: 0;
    }

    #week-picker-wrapper .datepicker .datepicker-days tr:hover td, #week-picker-wrapper .datepicker table tr td.day:hover, #week-picker-wrapper .datepicker table tr td.focused {
        color: #000 !important;
        background: #e5e2e3 !important;
        border-radius: 0 !important;
    }
</style>
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
            <div class="row" style="margin-top: 30px;">
                <h3 style="text-align: left; padding-left: 20px; color:#264e84">Tooling Report</h3>
            </div>
            <div class="row">
                <form id="report_build_form" method="post" action="reporting_make.php" target="_blank">
                    <div class="col-md-12" style="margin-top: 20px;">
                        <label style="font-size: 20px; color: #0e83cd">1, Select Report</label><br/>
                        <select id="select_report" name="select_report" class="form-control" style="width: auto; min-width: 350px;" required>
                            <option></option>
                            <?php
                            $query = "SELECT * FROM {$tblReports}";
                            $result = $db->query($query);
                            while ($row = mysqli_fetch_object($result)) {
                                echo "<option value='" . $row->id . "' data-report='".$row->report_type."'>" . $row->report_name . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-12" style="margin-top: 20px;">
                        <label style="font-size: 20px; color: #0e83cd">2, Select Date / Shift</label><br/>
                        <label style="margin-right: 20px;" id="current_shift"><input type="radio" id="radio_current_shift" name="select_date" value="current_shift" checked> Current Shift</label>
                        <label style="margin-right: 20px;" id="day_date"><input type="radio" id="radio_day" name="select_date" value="day"> Day</label>
                        <label style="margin-right: 20px;" id="week_date"><input type="radio" id="radio_week" name="select_date" value="week"> Week</label>
                        <label style="margin-right: 20px;" id="month_date"><input type="radio" id="radio_month" name="select_date" value="month"> Month</label>
                        <label style="margin-right: 20px;" id="custom_date"><input type="radio" id="radio_custom_date" name="select_date" value="custom_date"> Custom Date Range</label>
                    </div>

                    <div class="col-md-12" style="padding-top: 10px;">
                        <input class="form-control datepicker" type="text" name="report_date" id="report_date" style="width: 250px;margin-top: 5px; display: none;" placeholder="Click Here To Select Date" autocomplete="off">
                        <div id="week-picker-wrapper" style="width: 300px; display: none">
                            <div class="input-group">
                                <span class="input-group-btn"><button type="button" class="btn btn-rm week-prev">&laquo;</button></span>
                                <input class="form-control week-picker" name="week_report_picker" id="week_report_picker" value="" data-provide="datepicker" data-date-end-date="0d" style="width: 300px;" placeholder="Click Here To Select Week" autocomplete="off">
                                <span class="input-group-btn"><button type="button" class="btn btn-rm week-next">&raquo;</button></span>
                            </div>
                        </div>
                        <input class="form-control monthpicker" name="month_report_picker" id="month_report_picker" value="" data-provide="datepicker" data-date-end-date="0d" style="width: 300px; display:none" placeholder="Click Here To Select Month" autocomplete="off">
                        <input class="form-control datepicker" type="text" name="report_start_date" id="report_start_date" style="width: 300px;margin-top: 5px; display: none;" placeholder="Click Here To Select Start Date" autocomplete="off">
                        <input class="form-control datepicker" type="text" name="report_end_date" id="report_end_date" style="width: 300px;margin-top: 5px; display: none;" placeholder="Click Here To Select End Date" autocomplete="off">
                    </div>

                    <div class="col-md-12" style="padding-top: 20px; display: none;" id="shift_select">
                        <label style="margin-right: 20px;" id="both_shift"><input type="radio" name="select_shift" id="select_both_shift" value="all_shift" checked> All Shifts</label>
                        <label style="margin-right: 20px;" id="shift1"><input type="radio" name="select_shift" id="select_shift1" value="shift1" > Shift1</label>
                        <label style="margin-right: 20px;" id="shift2"><input type="radio" name="select_shift" id="select_shift2" value="shift2"> Shift2</label>
                        <label style="margin-right: 20px;" id="shift3"><input type="radio" name="select_shift" id="select_shift3" value="shift3"> Shift3</label>
                    </div>
                    <div class="col-md-12" style="padding-top: 20px;">
                        <button type="submit" class="btn btn-primary" id="run_report">Run Report</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>



</body>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/select2.min.js"></script>

<script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
<script src="assets/js/custom.js"></script>

<script>

    var weekpicker, start_date, end_date;

    function set_week_picker(date) {
        start_date = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay() + 1);
        $("#start_date").val(start_date);

        end_date = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay() + 7);
        weekpicker.datepicker('update', start_date);
        weekpicker.val(start_date.getDate() + '-' + (start_date.getMonth() + 1) + '-' + start_date.getFullYear() + ' to ' + end_date.getDate() + '-' + (end_date.getMonth() + 1) + '-' + end_date.getFullYear());
    }

    $(document).ready(function () {
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy'
        });

        $("#select_report").on('change', function () {
            var report_type = $(this).find(':selected').data('report');
            if(report_type == "just_data") {
                $("#report_build_form").attr('action', 'reporting_just_data.php');
            } else {
                $("#report_build_form").attr('action', 'reporting_make.php');
            }

        });

        $("#current_shift").on('click', function () {
            $("#report_date").hide();
            $("#week-picker-wrapper").hide();
            $("#month_report_picker").hide();
            $("#report_start_date").hide();
            $("#report_end_date").hide();

            $("#shift_select").hide();
        });

        $("#day_date").on('click', function () {
            $("#report_date").show();
            $("#week-picker-wrapper").hide();
            $("#month_report_picker").hide();
            $("#report_start_date").hide();
            $("#report_end_date").hide();

            $("#shift_select").show();

        });

        $("#week_date").on('click', function () {
            $("#report_date").hide();
            $("#week-picker-wrapper").show();
            $("#month_report_picker").hide();
            $("#report_start_date").hide();
            $("#report_end_date").hide();

            $("#shift_select").show();
        });

        $("#month_date").on('click', function () {
            $("#report_date").hide();
            $("#week-picker-wrapper").hide();
            $("#month_report_picker").show();
            $("#report_start_date").hide();
            $("#report_end_date").hide();

            $("#shift_select").show();
        });


        $("#custom_date").on('click', function () {
            $("#report_date").hide();
            $("#week-picker-wrapper").hide();
            $("#month_report_picker").hide();
            $("#report_start_date").show();
            $("#report_end_date").show();

            $("#shift_select").show();
        });

        $('#month_report_picker').datepicker({
            autoclose: true,
            forceParse: false,
            minViewMode: 1,
            format: 'mm-yyyy'
        });

        weekpicker = $('.week-picker');

        //console.log(weekpicker);
        weekpicker.datepicker({
            autoclose: true,
            forceParse: false,
            format: 'dd-mm-yyyy',
            weekStart: 1,
            container: '#week-picker-wrapper',
        }).on("changeDate", function (e) {
            set_week_picker(e.date);
        });

        $('.week-prev').on('click', function () {
            var prev = new Date(start_date.getTime());
            prev.setDate(prev.getDate() - 2);
            set_week_picker(prev);
        });
        $('.week-next').on('click', function () {
            var next = new Date(end_date.getTime());
            next.setDate(next.getDate() + 1);
            set_week_picker(next);
        });

        set_week_picker(new Date('<?php echo $live_date;?>'));

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
<?php
require_once("./config/config.php");

$graph_date = date('Y-m-d');

if(isset($_POST['graph_date']))
    $graph_date = convert_date_string($_POST['graph_date']);

$shift = "shift1";

if(isset($_POST['graph_shift']))
    $shift = $_POST['graph_shift'];

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

    <script src="js/FileSaver.min.js"></script>
    <script src="js/Blob.min.js"></script>
    <script src="js/xls.core.min.js"></script>
    <script src="js/tableexport.min.js"></script>
</head>
<body onload="startTime()">
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
            <a class="navbar-brand" href="#">Export Tooling</a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
                <li style="padding: 15px;">
                    <span style="color: #88898a; font-weight: bold; font-size: 16px;"><?php echo date('d / m / Y'); ?></span>
                    <span id="current_time" style="margin-left: 10px; color: #88898a; font-weight: bold;font-size: 16px;"><?php echo date('G:i:s A'); ?></span>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row" style="min-height: 40px; background-color: #373950; color: #fff; padding: 10px; margin-top: 10px;">
        <form method="post" action="" id="graph_form" name="graph_form">
            <div class="col-md-6">
                <strong>SELECT DATE : </strong>&nbsp;&nbsp;
                <input style="width: 100px; display: inline-block" class="form-control datepicker" name="graph_date"
                       id="graph_date" value="<?php echo convert_date_string($graph_date); ?>" data-provide="datepicker"
                       data-date-end-date="0d">
                <input type="hidden" name="graph_shift" id="graph_shift" value="<?php echo $shift; ?>">
                <button type="submit" class="btn btn-primary" id="load_date">Load Date</button>
            </div>
        </form>

        <div class="col-md-6">
            <strong>SELECT SHIFT : </strong>&nbsp;&nbsp;
            <div id='select_shift' data-shift='<?php echo $shift; ?>' class="btn-group shift-select"
                 style='width: 120px;'>
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false" style='width: 120px;'>
                    <span id='selected_shift' style="text-transform: uppercase"><?php echo $shift; ?></span>&nbsp;&nbsp;<span
                            class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li data-shift="shift1"><a href="#">SHIFT 1</a></li>
                    <li data-shift="shift2"><a href="#">SHIFT 2</a></li>
                    <li data-shift="shift3"><a href="#">SHIFT 3</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12" id="export_table">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>DATE</th>
                    <th>TIME</th>
                    <th>MBR</th>
                    <th>TOOL</th>
                    <th>SHIFT TOTAL</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                </tbody>
            </table>


        </div>

    </div>
</div>
<?php
mysqli_close($db);
?>
</body>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-datepicker.min.js"></script>
<script>
    $(document).ready(function () {

        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
        });

        $('.shift-select').find('li').click(function () {
            $('#selected_shift').html($(this).html());
            var shift = $(this).data('shift');
            $('#select_shift').attr('data-shift', shift);
            $("#graph_shift").val(shift);
            $("#graph_form").submit();
        });

        $(".table").tableExport({
            formats: ["xlsx"],
            position: "top",
            bootstrap: true,
        });

        $(".xlsx").addClass("pull-right");

        read_table();

    });

    function read_table()
    {
        var date = $("#graph_date").val();
        var shift = $("#graph_shift").val();
        $.ajax({
            url: "actions.php",
            method: "post",
            data: {
                date:date,
                shift:shift,
                action:"read_export_table"
            },
            dataType: "HTML"
        }).done(function (html) {
            $("#export_table").html(html);

            $(".table").tableExport({
                formats: ["xlsx"],
                position: "top",
                bootstrap: true,
            });

            $(".xlsx").addClass("pull-right");



        });
    }


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
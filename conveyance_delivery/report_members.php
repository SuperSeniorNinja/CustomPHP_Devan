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

$users = array();

if(!isset($_POST['members'])){
    array_push($users, $_SESSION['userId']);
} else{
    $users = $_POST['members'];
}

$from_date = $graph_date;
if(isset($_POST['from_date'])) {
    $from_date = convert_date_string($_POST['from_date']);
}

$to_date = $graph_date;
if(isset($_POST['to_date'])) {
    $to_date = convert_date_string($_POST['to_date']);
}


$users_data = get_member_data($from_date, $to_date, $users);

$g_data = json_encode($users_data, true);


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
                <strong>SELECT MEMBER(S) : </strong>&nbsp;&nbsp;
                <select class="form-control select2" id="members" name="members[]" multiple style="width: 100%;">
                    <?php
                    $m_query = "SELECT * FROM {$tblUsers}";
                    $m_result = $db->query($m_query);
                    while($member = mysqli_fetch_object($m_result)){
                        if(in_array($member->ID, $users)) {
                            echo '<option value="'.$member->ID.'" selected>'.$member->username.'</option>';
                        }
                        else
                            echo '<option value="'.$member->ID.'">'.$member->username.'</option>';
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


    <?php
    foreach ($users_data as $key=>$data) {
    ?>
        <div class="row">
            <div class="col-md-12" style="padding: 10px; font-size: 20px; font-weight: bold">
                <?php
                $q = "SELECT * FROM {$tblUsers} WHERE ID = $key";
                $r = $db->query($q);
                $u = mysqli_fetch_object($r);
                echo $u->username;
                ?>
            </div>
            <div class="col-md-12">
                <h1 style="text-align: center">TOTAL IN</h1>
                <div id="chartdiv<?php echo $key;?>" style="height: 300px;"></div>
            </div>

            <div class="col-md-12">
                <h1 style="text-align: center">TOTAL OUT</h1>
                <div id="chartdiv2<?php echo $key;?>" style="height: 300px;"></div>
            </div>

            <div class="col-md-12" style="text-align: center;">
                <table align="center">
                    <tr>
                        <td style="padding: 10px; width: 160px;">
                            <div class="total">
                                TOTAL IN: <br>
                                <?php echo $data['total_in'];?>
                            </div>
                        </td>

                        <td style="padding: 10px; width: 160px;">
                            <div class="total">
                                TOTAL IN: <br>
                                <?php echo $data['total_out'];?>
                            </div>
                        </td>

                        <td style="padding: 10px; width: 180px;">
                            <div class="left-in" style="background-color: #ff0500;">
                                IN : <br>
                                <?php echo $data['shift0_total_in'];?>
                            </div>
                            <div class="right-out" style="background-color: #ff0500;">
                                OUT : <br>
                                <?php echo $data['shift0_total_out'];?>
                            </div>
                        </td>

                        <td style="padding: 10px; width: 180px;">
                            <div class="left-in" style="background-color: #df02a4;">
                                IN : <br>
                                <?php echo $data['shift2_total_in'];?>
                            </div>
                            <div class="right-out" style="background-color: #df02a4;">
                                OUT : <br>
                                <?php echo $data['shift2_total_out'];?>
                            </div>
                        </td>

                        <td style="padding: 10px; width: 180px;">
                            <div class="left-in" style="background-color: #0557ff;">
                                IN : <br>
                                <?php echo $data['shift3_total_in'];?>
                            </div>
                            <div class="right-out" style="background-color: #0557ff;">
                                OUT : <br>
                                <?php echo $data['shift3_total_out'];?>
                            </div>
                        </td>

                        <td style="padding: 10px; width: 180px;">
                            <div class="left-in" style="background-color: #ff8f00;">
                                IN : <br>
                                <?php echo $data['shift4_total_in'];?>
                            </div>
                            <div class="right-out" style="background-color: #ff8f00;">
                                OUT : <br>
                                <?php echo $data['shift4_total_out'];?>
                            </div>
                        </td>

                        <td style="padding: 10px; width: 180px;">
                            <div class="left-in" style="background-color: #ede104;">
                                IN : <br>
                                <?php echo $data['shift6_total_in'];?>
                            </div>
                            <div class="right-out" style="background-color: #ede104;">
                                OUT : <br>
                                <?php echo $data['shift6_total_out'];?>
                            </div>
                        </td>

                        <td style="padding: 10px; width: 180px;">
                            <div class="left-in" style="background-color: #02f009;">
                                IN : <br>
                                <?php echo $data['shift12_total_in'];?>
                            </div>
                            <div class="right-out" style="background-color: #02f009;">
                                OUT : <br>
                                <?php echo $data['shift12_total_out'];?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    <?php
    }
    ?>
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

        $('.filter-select').find('li').click(function () {

            $('#selected_filter').html($(this).html());
            var filter = $(this).data('filter');
            $('#select_filter').attr('data-filter', filter);
            $("#scan_filter").val(filter);
            $("#graph_form").submit();
        });


        //read_report_table();
        var g_data = JSON.parse('<?php echo $g_data;?>');

        var chartData_in;
        var chartData_out;

        for(var k in g_data) {

            chartData_in = g_data[k].graph.in;
            chartData_out = g_data[k].graph.out;

            console.log(chartData_in);

            var chart1 = AmCharts.makeChart( "chartdiv"+k, {
                "type": "serial",
                "theme": "light",
                "depth3D": 20,
                "angle": 30,
                "dataDateFormat": "DD-MM-YYYY JJ:NN",
                "legend": {
                    "horizontalGap": 10,
                    "useGraphSettings": true,
                    "markerSize": 10
                },
                "dataProvider": chartData_in,
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

            var chart2 = AmCharts.makeChart( "chartdiv2"+k, {
                "type": "serial",
                "theme": "light",
                "depth3D": 20,
                "angle": 30,
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



        var chart = AmCharts.makeChart( "chartdiv", {
            "type": "serial",
            "theme": "light",
            "depth3D": 20,
            "angle": 30,
            "legend": {
                "horizontalGap": 10,
                "useGraphSettings": true,
                "markerSize": 10
            },
            "dataProvider": [ {
                "year": 2003,
                "europe": 2.5,
                "namerica": 2.5,
                "asia": 2.1,
                "lamerica": 1.2,
                "meast": 0.2,
                "africa": 0.1
            }, {
                "year": 2004,
                "europe": 2.6,
                "namerica": 2.7,
                "asia": 2.2,
                "lamerica": 1.3,
                "meast": 0.3,
                "africa": 0.1
            }, {
                "year": 2005,
                "europe": 2.8,
                "namerica": 2.9,
                "asia": 2.4,
                "lamerica": 1.4,
                "meast": 0.3,
                "africa": 0.1
            } ],
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
                "title": "Europe",
                "type": "column",
                "color": "#000000",
                "valueField": "europe"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.8,
                "labelText": "[[value]]",
                "lineAlpha": 0.3,
                "title": "North America",
                "type": "column",
                "color": "#000000",
                "valueField": "namerica"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.8,
                "labelText": "[[value]]",
                "lineAlpha": 0.3,
                "title": "Asia-Pacific",
                "type": "column",
                "newStack": true,
                "color": "#000000",
                "valueField": "asia"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.8,
                "labelText": "[[value]]",
                "lineAlpha": 0.3,
                "title": "Latin America",
                "type": "column",
                "color": "#000000",
                "valueField": "lamerica"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.8,
                "labelText": "[[value]]",
                "lineAlpha": 0.3,
                "title": "Middle-East",
                "type": "column",
                "color": "#000000",
                "valueField": "meast"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.8,
                "labelText": "[[value]]",
                "lineAlpha": 0.3,
                "title": "Africa",
                "type": "column",
                "color": "#000000",
                "valueField": "africa"
            } ],
            "categoryField": "year",
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

    });

    function read_report_table()
    {
        var date = $("#graph_date").val();
        var shift = $("#graph_shift").val();
        var scan_filter = $("#scan_filter").val();
        $.ajax({
            url: "actions.php",
            method: "post",
            data: {
                date:date,
                shift:shift,
                scan_filter:scan_filter,
                action:"read_report_table"
            },
            dataType: "HTML"
        }).done(function (html) {
            $("#report_table_div").html(html);

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
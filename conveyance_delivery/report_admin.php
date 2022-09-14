<?php
require_once("./config/config.php");
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
            <div class="row">
                <h3 style="text-align: left; padding-left: 20px; color:#264e84">Custom Report Builder</h3>
            </div>
            <div class="row">
                <form id="reporting_form">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="report_name" style="color: #2f65a5;">Report Name</label>
                            <input type="text" class="form-control" id="report_name" name="report_name" placeholder="Report Name">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="report_type" class="col-md-12" style="color: #2f65a5;">Report Type Options</label>
                            <label style="color: #0f0f0f; margin-right: 20px;"><input type="radio" id="type_graph_data" name="type_graph_data" checked value="graph_data"> Full Graph and Data</label>
                            <label style="color: #0f0f0f"><input type="radio" id="type_just_data" name="type_graph_data" value="just_data"> Just Data</label>
                        </div>
                    </div>

                    <div class="col-lg-12"><hr></div>

                    <div class="col-lg-4" style="margin-top: 20px;">
                        <div class="form-group">
                            <label for="all_sections" style="color: #2f65a5;">Sections to include</label>
                            <br/>
                            <label><input type="checkbox" name="all_sections" id="all_sections" checked value="1"> All</label>
                            <br/>
                            <label><input type="checkbox" class="select-section" name="section1" id="section1" checked value="1"> 1. Tools</label>
                            <br/>
                            <label><input type="checkbox" class="select-section" name="section2" id="section2" checked value="1"> 2. Total Tools In & Out</label>
                            <br/>
                            <label><input type="checkbox" class="select-section" name="section3" id="section3" checked value="1"> 3. Tools Overdue</label>
                            <br/>
                            <label><input type="checkbox" class="select-section" name="section4" id="section4" checked value="1"> 4. Tool Activity</label>
                            <br/>
                            <label><input type="checkbox" class="select-section" name="section5" id="section5" checked value="1"> 5. Members Tool Report</label>
                            <br/>
                            <label><input type="checkbox" class="select-section" name="section6" id="section6" checked value="1"> 6. Members Activity</label>
                            <br/>
                            <label><input type="checkbox" class="select-section" name="section7" id="section7" checked value="1"> 7. Activity</label>
                            <br/>
                        </div>
                    </div>

                    <div class="col-lg-4" style="margin-top: 20px;">
                        <div class="form-group">
                            <label for="report_name" style="color: #2f65a5;">Include Members</label>
                            <br/>
                            <label><input type="radio" class="" name="include_members" id="all_members" checked value="all_member"> All Members</label>
                            <br/>
                            <label><input type="radio" class="" name="include_members" id="custom_members" value="custom_members"> Custom</label>
                            <br/>
                            <select class="form-control select2" id="select_members" name="select_members[]" multiple>
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

                    <div class="col-lg-4" style="margin-top: 20px;">
                        <div class="form-group">
                            <label for="report_name" style="color: #2f65a5;">Include Tools</label>
                            <br/>
                            <label><input type="checkbox" class="" name="all_tools" id="all_tools" checked value="1"> All Tools</label>
                            <br/>
                            <label><input type="checkbox" class="select-tool" name="red_tool" id="red_tool" checked value="1"> Red(Current Shift)</label>
                            <br/>
                            <label><input type="checkbox" class="select-tool" name="purple_tool" id="purple_tool" checked value="1"> Purple(2 Shifts)</label>
                            <br/>
                            <label><input type="checkbox" class="select-tool" name="blue_tool" id="blue_tool" checked value="1"> Blue(3 Shifts)</label>
                            <br/>
                            <label><input type="checkbox" class="select-tool" name="orange_tool" id="orange_tool" checked value="1"> Orange(4/6 Shift)</label>
                            <br/>
                            <label><input type="checkbox" class="select-tool" name="yellow_tool" id="yellow_tool" checked value="1"> Yellow(3/4 Days)</label>
                            <br/>
                            <label><input type="checkbox" class="select-tool" name="green_tool" id="green_tool" checked value="1"> Green(5 Days+)</label>
                            <br/>
                            <label><input type="checkbox" class="" name="custom_tool" id="custom_tool" value="1"> Custom</label>
                            <br/>

                            <select class="form-control select2" id="select_tools" name="select_tools[]" multiple>
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

                    <div class="col-lg-12"><hr></div>

                    <div class="col-lg-3" style="margin-top: 20px;">
                        <div class="form-group">
                            <label for="report_name" style="color: #2f65a5;">Section2 Options</label>
                            <br/>
                            <label><input type="checkbox" name="booked_in_2" id="booked_in_2" checked value="1"> Just Booked In</label>
                            <br/>
                            <label><input type="checkbox" name="booked_out_2" id="booked_out_2" checked value="1"> Just Booked Out</label>
                        </div>
                    </div>

                    <div class="col-lg-3" style="margin-top: 20px;">
                        <div class="form-group">
                            <label for="report_name" style="color: #2f65a5;">Section3 Options</label>
                            <br/>
                            <label><input type="checkbox" name="hide_graph_3" id="hide_graph_3" value="1"> Hide Graph</label>
                            <br/>
                            <label><input type="checkbox" name="hide_list_3" id="hide_list_3" value="1"> Hide List</label>
                        </div>
                    </div>

                    <div class="col-lg-3" style="margin-top: 20px;">
                        <div class="form-group">
                            <label for="report_name" style="color: #2f65a5;">Section4 Options</label>
                            <br/>
                            <label><input type="checkbox" name="hide_list_4" id="hide_list_4" value="1"> Hide List</label>
                        </div>
                    </div>

                    <div class="col-lg-3" style="margin-top: 20px;">
                        <div class="form-group">
                            <label for="report_name" style="color: #2f65a5;">Section5 Options</label>
                            <br/>
                            <label><input type="checkbox" name="booked_in_5" id="booked_in_5" checked value="1"> Just Booked In</label>
                            <br/>
                            <label><input type="checkbox" name="booked_out_5" id="booked_out_5" checked value="1"> Just Booked Out</label>
                        </div>
                    </div>

                    <div class="col-lg-12"><hr> <button type="button" class="btn btn-primary" id="save_report">Save Report</button></div>

                    <div class="col-lg-12"><hr></div>

                    <div class="col-lg-12" id="current_reports"></div>

                    <input type="hidden" id="report_id" name="report_id" value="0">
                    <input type="hidden" id="action" name="action" value="save_report">
                </form>
            </div>

            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-12" id="reports_list">

                </div>
            </div>
        </div>

    </main>
</div>

<div class="my-alert alert alert-success" id="success-alert" style="display: none">
    <button type="button" class="close" data-dismiss="alert">x</button>
    <strong id="alert_title">Success! </strong>
    <span id="alert_message">Saved successfully.</span>
</div>

<div class="my-alert alert alert-danger" id="fault-alert" style="display: none">
    <button type="button" class="close" data-dismiss="alert">x</button>
    <strong id="fault_title">Fail! </strong>
    <span id="fault_message">Saved failed.</span>
</div>

</body>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/select2.min.js"></script>

<script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
<script src="assets/js/custom.js"></script>

<script>
    $(document).ready(function () {
        read_reports();

        $(".select2").select2();

        $("#all_sections").on('click', function () {
            if($(this).is(':checked')){
                $(".select-section").prop('checked', 'checked');
            } else {
                $(".select-section").prop('checked', false);
            }
        });

        $(".select-section").on('click', function () {
            if(!$(this).is(':checked')){
                $("#all_sections").prop('checked', false);
            } else{
                if($("input[class=select-section]:checked").length == 7){
                    $("#all_sections").prop('checked', true);
                }
            }
        });


        $("#all_tools").on('click', function () {
            if($(this).is(':checked')){
                $(".select-tool").prop('checked', 'checked');
                $("#custom_tool").prop('checked', false);
            } else {
                $(".select-tool").prop('checked', false);
            }
        });

        $(".select-tool").on('click', function () {
            if(!$(this).is(':checked')){
                $("#all_tools").prop('checked', false);
            } else{
                if($("input[class=select-tool]:checked").length == 6){
                    $("#all_tools").prop('checked', true);
                }
            }
        });

        $("#custom_tool").on('click', function () {
            $("#all_tools").prop('checked', false);
            $(".select-tool").prop('checked', false);
        });

        $("#save_report").on('click', function () {
            var report_name = $("#report_name").val();
            if(report_name == "") {
                $("#report_name").focus();
                return false;
            }

            var form = $("#reporting_form");

            $.ajax({
                url: "actions.php",
                method: "post",
                data: form.serialize()
            }).done(function (res) {
                if(res =="ok") {
                    $("#select_members").val([]).trigger('change');
                    $("#select_tools").val([]).trigger('change');
                    $("#report_name").val('');
                    $('input:checkbox').prop('checked', true);
                    $('#hide_graph_3').prop('checked', false);
                    $('#hide_list_3').prop('checked', false);
                    $('#hide_list_4').prop('checked', false);
                    $("#custom_members").prop('checked', false);
                    $("#custom_tool").prop('checked', false);
                    $("#all_members").prop('checked', true);
                    read_reports();
                    $("#success-alert").fadeTo(2000, 500).slideUp(500, function(){
                        $("#success-alert").slideUp(500);
                    });
                } else {
                    $("#fault-alert").fadeTo(2000, 500).slideUp(500, function(){
                        $("#fault-alert").slideUp(500);
                    });
                }
            });

        });

        $(document).on('click', '.report-delete', function(){
            var report_id = $(this).attr('id').replace("delete_", "");

            if(confirm("Are you sure?")) {
                $.ajax({
                    url: "actions.php",
                    method: "post",
                    data: {report_id:report_id, action:"delete_report"}
                }).done(function (res) {
                    if(res == "fail") {
                        $("#fault-alert").fadeTo(2000, 500).slideUp(500, function(){
                            $("#fault-alert").slideUp(500);
                        });
                    } else {
                        $("#report_name").val('');
                        $("#report_id").val(0);
                        read_reports();
                    }
                });
            }
        });


        $(document).on('click', '.report-select', function(){
            var report_name = $(this).data('report');
            var report_type = $(this).data('report_type');
            var sections = $(this).data('sections');
            var members = $(this).data('members');
            var tools = $(this).data('tools');
            var booked_in_2 = $(this).data('booked_in_2');
            var booked_out_2 = $(this).data('booked_out_2');
            var hide_graph_3 = $(this).data('hide_graph_3');
            var hide_list_3 = $(this).data('hide_list_3');
            var hide_list_4 = $(this).data('hide_list_4');
            var booked_in_5 = $(this).data('booked_in_5');
            var booked_out_5 = $(this).data('booked_out_5');

            var report_id = $(this).attr('id').replace("select_", "");

            $("#report_id").val(report_id);
            $("#report_name").val(report_name);

            if(report_type == "graph_data") {
                $("#type_graph_data").prop('checked', true);
                $("#type_just_data").prop('checked', false);
            } else {
                $("#type_graph_data").prop('checked', false);
                $("#type_just_data").prop('checked', true);
            }

            if(sections == "all") {
                $("#all_sections").prop('checked', true);
                $(".select-section").prop('checked', true);
            } else {
                $("#all_sections").prop('checked', false);
                if(sections.includes(",") == true) {
                    var sections_array = sections.split(",");
                    for(var section in sections_array) {
                        $("#"+sections_array[section]).prop('checked', true);
                    }
                } else {
                    $(".select-section").prop('checked', false);
                    $("#"+sections).prop('checked', true);
                }
            }

            if(members == "all") {
                $("#all_members").prop('checked', true);
            } else {
                $("#custom_members").prop('checked', true);
                if(members.includes(",") == true) {
                    var members_array = members.split(",");
                    $("#select_members").val(members_array).trigger('change');
                } else {
                    $("#select_members").val(members).trigger('change');
                }
            }

            //Tools
            if(tools == "all") {
                $("#all_tools").prop('checked', true);
                $(".select-tool").prop('checked', true);
                $("#custom_tool").prop('checked', false);
            } else {
                $("#all_tools").prop('checked', false);
                $(".select-tool").prop('checked', false);
                if(tools.includes(",") == true) {
                    var tools_array = tools.split(",");
                    for(var tool in tools_array) {
                        $("#"+tools_array[tool]+'_tool').prop('checked', true);
                    }
                    if($("input[class=select-tool]:checked").length == 0) {
                        $("#custom_tool").prop('checked', true);
                        $("#select_tools").val(tools_array).trigger('change');
                    } else{
                        $("#custom_tool").prop('checked', false);
                    }

                } else {
                    $("#"+tools+'_tool').prop('checked', true);
                    if($("input[class=select-tool]:checked").length == 0) {
                        $("#custom_tool").prop('checked', true);
                        $("#select_tools").val(tools).trigger('change');
                    } else{
                        $("#custom_tool").prop('checked', false);
                    }
                }
            }


            //OTHERS
            if(booked_in_2 == 1) {
                $("#booked_in_2").prop('checked', true);
            } else {
                $("#booked_in_2").prop('checked', false);
            }

            if(booked_out_2 == 1) {
                $("#booked_out_2").prop('checked', true);
            } else {
                $("#booked_out_2").prop('checked', false);
            }

            if(hide_graph_3 == 1) {
                $("#hide_graph_3").prop('checked', true);
            } else {
                $("#hide_graph_3").prop('checked', false);
            }

            if(hide_list_3 == 1) {
                $("#hide_list_3").prop('checked', true);
            } else {
                $("#hide_list_3").prop('checked', false);
            }

            if(hide_list_4 == 1) {
                $("#hide_list_4").prop('checked', true);
            } else {
                $("#hide_list_4").prop('checked', false);
            }

            if(booked_in_5 == 1) {
                $("#booked_in_5").prop('checked', true);
            } else {
                $("#booked_in_5").prop('checked', false);
            }

            if(booked_out_5 == 1) {
                $("#booked_out_5").prop('checked', true);
            } else {
                $("#booked_out_5").prop('checked', false);
            }



        });

    });

    function read_reports()
    {
        $.ajax({
            url: "actions.php",
            method: "post",
            data: {action:'read_all_reports'},
            dataType:"HTML"
        }).done(function (html) {
            $("#reports_list").html(html)
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
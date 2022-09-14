
$(document).ready(function(){

    read_priority_table();
    read_export_table();

    var leftTime = $("#start_left_time").val();
    var display = $('#left_time');

    if(leftTime != "")
        startTimer(leftTime);


    $('.time-picker').timepicker({
        minuteStep: 1,
        template: 'modal',
        appendWidgetTo: 'body',
        showSeconds: false,
        showMeridian: false,
        defaultTime: false
    });

    $("#barcode").on('keyup', function (e) {
        if (e.keyCode == 13) {
            if($(this).hasClass('in')) {

                var input_kind = $("#input_kind").val();
                var input = $(this);
                var shift_id = $("#shift_id").val();

                if(input_kind == "tool") {
                    var barcode = $(this).val();

                    if(barcode == "999999") {
                        location.href = 'logout.php';
                        return false;
                    }

                    var scanned_barcode = $("#scanned_barcode").val();

                    var request_scan = $("#request_scan").val();
                    if(request_scan == ""){
                        request_scan += barcode;
                    } else {
                        request_scan += "," + barcode;
                    }

                    scanned_barcode += "," + barcode;

                    $("#scanned_barcode").val(scanned_barcode);
                    $("#request_scan").val(request_scan);


                    $("#input_kind").val('location');
                    $("#input_title").text('location input');
                    $("#input_title").css('color', 'blue');

                    input.val('');

                } else {

                    var tool_location = $(this).val();

                    if(tool_location == "999999") {
                        location.href = 'logout.php';
                        return false;
                    }

                    var request_location = $("#request_location").val();
                    if(request_location == ""){
                        request_location += tool_location;
                    } else {
                        request_location += "," + tool_location;
                    }

                    $("#request_location").val(request_location);


                    $("#input_kind").val('tool');
                    $("#input_title").text('barcode input');
                    $("#input_title").css('color', '#0e0e0e');

                    input.val('');
                    barcode_scan();
                }
            } else{
                return;
            }
        }
    });


    $(document).on('click', ".book-out", function () {
        var id = $(this).attr('id').replace("book","");
        var shift_id = $(this).data('shift');
        var current_shift_id = $('#shift_id').val();
        var tr = $(this).closest("tr");
        var barcode = tr.find(".barcode").text();
        $.ajax({
            url: "actions.php",
            method: "post",
            data: {entered_id:id, shift_id:shift_id, action:"book_out", barcode:barcode}
        }).done(function (res) {
            if(res.includes("ok")){

                if(shift_id == current_shift_id) {
                    $("#booked_out").text(parseInt($("#booked_out").text()) + 1);
                }
                tr.remove();
                read_change_frequency();
                read_priority_table();
                get_scanned_barcode();
            } else{
                alert("Book out failed!")
            }
        });
    });
});

function read_priority_table() {
    var shift_id = $("#shift_id").val();
    var end_time = $("#end_time").val();
    $.ajax({
        url: "actions.php",
        method: "post",
        data: {action:"read_priority_table", shift_id:shift_id, end_time:end_time},
        dataType: "HTML"
    }).done(function (html) {
        $("#priority_table").html(html);

        var scanned_barcodes = $("#tmp_scanned_barcode").val();

        $("#tmp_scanned_barcode").remove();
        $("#scanned_barcode").val(scanned_barcodes);

    });
}

function read_export_table(text) {
    var shift_id = $("#shift_id").val();
    $.ajax({
        url: "actions.php",
        method: "post",
        data: {action:"read_export_table", shift_id:shift_id},
        dataType: "HTML"
    }).done(function (html) {
        $("#barcode_table").find("tbody").html(html);

        var scanned_barcodes = $("#tmp_scanned_barcode").val();
        //console.log(scanned_barcodes);
        $("#tmp_scanned_barcode").remove();
        $("#scanned_barcode").val(scanned_barcodes);

    });
}

function barcode_scan() {
    var barcode = $("#request_scan").val();
    var tool_location = $("#request_location").val();
    var shift_id = $("#shift_id").val();

    $.ajax({
        url: "actions.php",
        method: "post",
        data: {barcode:barcode, tool_location:tool_location, action:"read_barcode", shift_id:shift_id}
    }).done(function (res) {
        var data = JSON.parse(res);
        var bookin = data.BI;
        var bookout = data.BO;

        $("#booked_in").text(bookin);
        $("#booked_out").text(bookout);
        $("#request_scan").val('');
        $("#request_location").val('');

        read_change_frequency();
        read_priority_table();
        read_export_table('scan');
    });
}

function get_scanned_barcode() {
    $.ajax({
        url: "actions.php",
        method: "post",
        data: {action:"get_scanned_barcode"}
    }).done(function (res) {
        var data = JSON.parse(res);
        $("#scanned_barcode").val(data.join());
    });
}

function startTimer(duration) {
    var timer = duration, hours, minutes, seconds;

    var timeInterval = setInterval(function () {
        hours = parseInt(timer / 3600, 10);
        minutes = parseInt((timer - hours * 3600)  / 60, 10)
        seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        $('#left_time').text(hours + ":" + minutes + ":" + seconds);

        var past_time = $('#past_time').val();
        past_time = parseInt(past_time) + 1;

        $('#past_time').val(past_time);

        var from_start = $("#from_start").val();
        var check_create = $("#check_create").val();

        if(past_time == 60 && from_start <= 60 && check_create != 1) {
            location.href = './logout.php';
        }

        if (--timer < 1) {

            clearInterval(timeInterval);

            $.ajax({
                url: "actions.php",
                method: "post",
                data: {action:"create_next_shift"}
            }).done(function (res) {
                if(res == "ok") {
                    location.reload();
                }
            });
        }
    }, 1000);
}

function read_change_frequency() {
    var shift_id = $("#shift_id").val();
    $.ajax({
        url: "actions.php",
        method: "post",
        data: {action:"read_change_frequency", shift_id:shift_id}
    }).done(function (res) {
        var data = JSON.parse(res);
        //console.log(data);
        $("#this_shift").text(data.this_shift);
        $("#two_shift").text(data.two_shift);
        $("#three_shift").text(data.three_shift);
        $("#four_six_shift").text(data.four_six_shift);
        $("#six_twelve_shift").text(data.six_twelve_shift);
        $("#five_days_shift").text(data.five_days_shift);

        /*var carried_forward = parseInt(data.this_shift) + parseInt(data.two_shift) + parseInt(data.three_shift) + parseInt(data.four_six_shift) + parseInt(data.six_twelve_shift) + parseInt(data.five_days_shift);

        $("#carried_forward").text(carried_forward);*/
    });
}

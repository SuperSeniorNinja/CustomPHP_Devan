
$(document).ready(function(){

    read_priority_table();
    read_export_table();

    $(".datepicker").datetimepicker({
        format: 'M/D/YYYY'
    });

    $("#btn_change_shift").on('click', function () {
        $("#shift_modal").modal({
            backdrop: 'static',
            keyboard: false
        });
    });

    $("#change_shift_ok").on('click', function () {
        var new_shift = $("#new_shift").val();
        if(new_shift == ""){
            $("#new_shift").focus();
            return false;
        }

        var carried_forword = parseInt($("#total_wip").text());
        var qty = carried_forword;
        var wip_8 = parseInt($("#wip_8").text());
        var wip_8_12 = parseInt($("#wip_8_12").text());
        var wip_12 = parseInt($("#wip_12").text());

        $.ajax({
            url: "actions.php",
            method: "post",
            data: {
                shift:new_shift,
                action:'change_shift',
                carried:carried_forword,
                qty:qty,
                wip_8:wip_8,
                wip_8_12:wip_8_12,
                wip_12:wip_12

            }
        }).done(function (data) {
            //console.log(data);

            data = JSON.parse(data);

            $("#shift_changed_at").val(data.shift_changed_at);
            $("#hours").val(data.hours);
            $("#shift_id").val(data.shift_id);
            $("#new_shift").val('');
            $("#shift_modal").modal('hide');

            $("#carried_forward").text(carried_forword);
            $("#booked_in").text(0);
            $("#booked_out").text(0);
        });
    });

    $("#btn_shift_log").on('click', function () {
        $("#action_kind").val('shift_log');
        $("#date_modal").modal({
            backdrop: 'static',
            keyboard: false
        });
    });

    $("#date_select_ok").on('click', function () {

        var start_date = $("#start_date").val();
        if(start_date == ""){
            $("#start_date").focus();
            return false;
        }

        var end_date = $("#end_date").val();
        if(end_date == ""){
            $("#end_date").focus();
            return false;
        }

        var action_kind = $("#action_kind").val();
        var modal_title = '';
        if(action_kind == "shift_log"){
            modal_title = "SHIFT LOG";
        } else if(action_kind == "scanning_data"){
            modal_title = "SCANNING DATA";
        }

        $.ajax({
            url: "actions.php",
            method: "post",
            data: {start_date:start_date, end_date:end_date, action:action_kind},
            dataType: "HTML"
        }).done(function (html) {
            $("#date_modal").modal('hide');
            $("#start_date").val('')
            $("#end_date").val('')
            $("#data_modal").find('.modal-title').html(modal_title);
            $("#data_modal").find('.modal-body').html(html);
            $("#data_modal").modal({
                backdrop: 'static',
                keyboard: false
            });
        });
    });

    $("#btn_scanning_data").on('click', function () {
        $("#action_kind").val('scanning_data');
        $("#date_modal").modal();
    });

    $("#btn_update_screen").on('click', function () {
        location.reload();
    });

    $("#btn_output_xl").on('click', function () {

    });

    $("#btn_move_saturday").on('click', function () {

    });

    $("#barcode").on('change', function () {
        var barcode = $(this).val();

        var shift_id = $("#shift_id").val();
        var input = $(this);
        var scanned_barcode = $("#scanned_barcode").val();
        var scanned_barcode_array = scanned_barcode.split(',');
        if(scanned_barcode_array.includes(barcode)){
            $("#alert_th").css('color', 'red');
            $("#alert_th").text("Same barcode already scanned!");
            input.val('');
            setTimeout(function () {
                $("#alert_th").text('');
            }, 3000);
            return false;
        } else{
            input.val('');
            var request_scan = $("#request_scan").val();
            if(request_scan == ""){
                request_scan += barcode;
            } else {
                request_scan += "," + barcode;
            }

            scanned_barcode += "," + barcode;

            $("#scanned_barcode").val(scanned_barcode);
            $("#request_scan").val(request_scan);
        }
    });

    $(document).on('click', "#input_barcode", function () {
        barcode_scan();
    });


    $(document).on('click', ".book-out", function () {
        var id = $(this).attr('id').replace("book","");
        var shift_id = $("#shift_id").val();
        var tr = $(this).closest("tr");
        var barcode = tr.find(".barcode").text();


        $.ajax({
            url: "actions.php",
            method: "post",
            data: {entered_id:id, shift_id:shift_id, action:"book_out", barcode:barcode}
        }).done(function (res) {
            if(res.includes("ok")){


                var hr = parseInt(res.replace("ok_", ""));
                var qty = parseInt(tr.find('.qty').text());
                console.log(qty);

                if(parseInt($("#total_wip").text()) - qty < 0)
                    $("#total_wip").text('0');
                else
                    $("#total_wip").text(parseInt($("#total_wip").text()) - qty);

                if(hr < 8){
                    if(parseInt($("#wip_8").text())- qty < 0)
                        $("#wip_8").text('0');
                    else
                        $("#wip_8").text(parseInt($("#wip_8").text()) - qty);
                } else if(hr >=8 && hr <12){
                    if(parseInt($("#wip_8_12").text()) - qty < 0)
                        $("#wip_8_12").text(0);
                    else
                        $("#wip_8_12").text(parseInt($("#wip_8_12").text()) - qty);
                } else if(hr >= 12) {
                    if(parseInt($("#wip_12").text()) - qty < 0)
                        $("#wip_12").text(0);
                    else
                        $("#wip_12").text(parseInt($("#wip_12").text()) - qty);
                }

                $("#booked_out").text(parseInt($("#booked_out").text()) + qty);

                $("#c_booked_out").text(parseInt($("#c_booked_out").text())+1);


                // if(parseInt($("#booked_in").text()) - qty < 0)
                //     $("#booked_in").text(0);
                // else
                //     $("#booked_in").text(parseInt($("#booked_in").text()) - qty);
                //
                // if(parseInt($("#c_booked_in").text())-1 < 0)
                //     $("#c_booked_in").text(0);
                // else
                //     $("#c_booked_in").text(parseInt($("#c_booked_in").text())-1);

                tr.remove();
                read_priority_table();
                get_scanned_barcode();


            } else{
                alert("Book out failed!")
            }
        });
    });

    $(document).on('click', ".book-in", function () {
        var id = $(this).attr('id').replace("book","");
        var shift_id = $("#shift_id").val();
        $.ajax({
            url: "actions.php",
            method: "post",
            data: {entered_id:id, shift_id:shift_id, action:"book_in"}
        }).done(function (res) {
            if(res == "ok"){
                $("#book"+id).text('Book Out');
                $("#book"+id).removeClass('book-in');
                $("#book"+id).addClass('book-out');
                $("#booked_out").text(parseInt($("#booked_out").text())-1);
                $("#booked_in").text(parseInt($("#booked_in").text())+1);
            } else{
                alert("Book in failed!")
            }
        });

    });

    $("#day_no").on('click', function () {
        var shift_id = $("#shift_id").val();
        $.ajax({
            url: "actions.php",
            method: "post",
            data: {action:"get_barcode_list", shift_id:shift_id},
            dataType: "HTML"
        }).done(function (html) {
            $("#barcode_list").html(html);
            $("#remove_list_modal").modal();
        });
    });

    $("#remove_barcode_list").on('click', function () {
        var shift_id = $("#shift_id").val();
        var barcode = $("#barcode_list").val();
        var table = $("#barcode_table");
        $.ajax({
            url: "actions.php",
            method: "post",
            data: {action:"remove_barcode_list", shift_id:shift_id, barcode:barcode},
        }).done(function (res) {
            if(res == "ok"){
                $("#remove_list_modal").modal('hide');
                location.reload();
            } else{
                alert("Remove list failed!")
            }
        });
    });

    $(".checked_book_out").on('click', function () {
        var checked_barcodes = [];
        $(".checked-barcode").each(function () {
            if($(this).is(":checked")) {
                checked_barcodes.push($(this).val());
            }
        });

        var shift_id = $("#shift_id").val();

        $.ajax({
            url: "actions.php",
            method: "post",
            data: {shift_id:shift_id, action:"checked_book_out", barcode:checked_barcodes}
        }).done(function (res) {
            if(res.includes("ok")){
                location.reload();
            } else{
                alert("Book out failed!")
            }
        });
    });

});

function read_priority_table() {
    $.ajax({
        url: "actions.php",
        method: "post",
        data: {action:"read_priority_table"},
        dataType: "HTML"
    }).done(function (html) {
        $("#priority_table").html(html);
    });
}

function read_export_table() {
    $.ajax({
        url: "actions.php",
        method: "post",
        data: {action:"read_export_table"},
        dataType: "HTML"
    }).done(function (html) {
        $("#barcode_table").find("tbody").html(html);

    });
}

function barcode_scan() {
    var barcode = $("#request_scan").val();
    var shift_id = $("#shift_id").val();

    $.ajax({
        url: "actions.php",
        method: "post",
        data: {barcode:barcode, action:"read_barcode", shift_id:shift_id}
    }).done(function (res) {

        var data = JSON.parse(res);

        if(parseInt(data.Qty) > 0)
            $("#total_wip").text(parseInt(data.Qty));
        else
            $("#total_wip").text('0');

        if(parseInt(data.WIP_8) > 0)
            $("#wip_8").text(parseInt(data.WIP_8));
        else
            $("#wip_8").text('0');

        if(parseInt(data.WIP_8_12) > 0)
            $("#wip_8_12").text(parseInt(data.WIP_8_12));
        else
            $("#wip_8_12").text('0');

        if(parseInt(data.WIP_12) > 0)
            $("#wip_12").text(parseInt(data.WIP_12));
        else
            $("#wip_12").text('0');

        if(parseInt(data.BO) > 0)
            $("#booked_out").text(parseInt(data.BO));
        else
            $("#booked_out").text('0');

        if(parseInt(data.BI) > 0)
            $("#booked_in").text(parseInt(data.BI));
        else
            $("#booked_in").text('0');

        if(parseInt(data.c_bi) > 0)
            $("#c_booked_in").text(parseInt(data.c_bi));
        else
            $("#c_booked_in").text('0');

        if(parseInt(data.c_bo) > 0)
            $("#c_booked_out").text(parseInt(data.c_bo));
        else
            $("#c_booked_out").text('0');

        $("#request_scan").val('');
        read_priority_table();
        read_export_table();
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
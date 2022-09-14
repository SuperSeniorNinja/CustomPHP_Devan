<?php
require_once("./config/config.php");
require_once('users.php');

if(empty($_SESSION['username'])) {
    header('Location: index.php');
}

date_default_timezone_set('Europe/London');

$username = $_SESSION['username'];
if(isset($_SESSION['userId']))
    $userId = $_SESSION['userId'];
else
    $userId = 0;

if(isset($_SESSION['staff']))
    $userStaff = $_SESSION['staff'];
else
    $userStaff = "";

$melts = $_SESSION['melt'];
$selections = $_SESSION['selection'];
$meltInputs = $_SESSION['meltInput'];
$totalWeight = $_SESSION['totalWeight'];
$lastTime = $_SESSION['lastTime'];

$date = date('Y-m-d H:i:s');
$currentTime = strtotime($date);
$balance = $currentTime - $lastTime;
$hour = intval($balance / 60 / 24);
$min = intval($balance / 60) - ($hour * 24);
$sec = $balance - ($hour * 24 * 60) - $min * 60;
$lastUpdate = "";

if ($hour < 10)
    $lastUpdate = "0" . $hour;
else
    $lastUpdate = "" . $hour;
if ($min < 10)
    $lastUpdate .= ":0" . $min;
else
    $lastUpdate .= ":" . $min;
if ($sec < 10)
    $lastUpdate .= ":0" . $sec;
else
    $lastUpdate .= ":" . $sec;

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="css/main.css">

    <link rel="stylesheet" href="css/font-awesome.css">
    <link rel="stylesheet" href="assets/css/jquery.mCustomScrollbar.min.css" />
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/custom-themes.css">

    <script>
        var staff = [<?php echo $staff;?>];
        var editUserId;
        var lastTime = <?php echo $lastTime;?>;

        function selectMenu(index) {
            var menus = document.getElementsByName('menu');
            var contents = document.getElementsByName('content');
            for (i = 0; i < menus.length; i++) {
                menus[i].className = "";
                contents[i].style.display = 'none';
                //contents[i].css('display', 'none');
                if (index == i) {
                    menus[i].className = "active";
                    contents[i].style.display = 'block';
                    //contents[i].css('display', 'block');
                }
            }
        }

        function showCurrentDate() {
            var date = new Date();
            var day = date.getDate();
            if (day < 10)
                day = "0" + day;

            var month = date.getMonth() + 1;
            if (month < 10)
                month = "0" + month;

            var hour = date.getHours();
            if (hour < 10)
                hour = "0" + hour;

            var mins = date.getMinutes();
            if (mins < 10)
                mins = "0" + mins;

            var secs = date.getSeconds();
            if (secs < 10)
                secs = "0" + secs;

            document.getElementById('currentTime').innerHTML = day + " / " + month + " / " + date.getFullYear() + " " + hour + " : " + mins + " : " + secs;
            document.getElementById('input_current_time').innerHTML = hour + ":" + mins;
            document.getElementById('edit_input_time').innerHTML = hour + ":" + mins;


            var balance = parseInt(date.getTime() / 1000) - lastTime;
            var hour = parseInt(balance / 60 / 24);
            var min = parseInt(balance / 60) - (hour * 24);
            var sec = balance - (hour * 24 * 60) - min * 60;
            var lastUpdate = "";

            if (hour < 10)
                lastUpdate = "0" + hour;
            else
                lastUpdate = "" + hour;
            if (min < 10)
                lastUpdate += ":0" + min;
            else
                lastUpdate += ":" + min;
            if (sec < 10)
                lastUpdate += ":0" + sec;
            else
                lastUpdate += ":" + sec;

            document.getElementById('lastChange').innerHTML = lastUpdate;

            setTimeout(showCurrentDate, 1000);
        }

        function selectUsertype(value) {
            var btn = document.getElementById('userTypeId');
            if (value == 1) {
                btn.innerHTML = 'Administrator&nbsp;&nbsp;&nbsp;<span class="caret"></span>';
            } else {
                btn.innerHTML = 'User&nbsp;&nbsp;&nbsp;<span class="caret"></span>';
            }
            document.getElementById('userType').value = value;
        }

        function selectUsertypeEdit(value) {
            var btn = document.getElementById('userTypeIdEdit');
            if (value == 1) {
                btn.innerHTML = 'Administrator&nbsp;&nbsp;&nbsp;<span class="caret"></span>';
            } else {
                btn.innerHTML = 'User&nbsp;&nbsp;&nbsp;<span class="caret"></span>';
            }
            document.getElementById('userTypeEdit').value = value;
        }

        function selectSelection(value, name) {
            var btn = document.getElementById('btnSelection');
            btn.innerHTML = name + '&nbsp;&nbsp;&nbsp;<span class="caret"></span>';
            document.getElementById('userSelection').value = value;
            document.getElementById('userSelectionName').value = name;
        }

        function selectMelt(value, name) {
            var btn = document.getElementById('btnMelt');
            btn.innerHTML = name + '&nbsp;&nbsp;&nbsp;<span class="caret"></span>';
            document.getElementById('userMelt').value = value;
            document.getElementById('userMeltName').value = name;
        }

        function selectSelectionEdit(value, name) {
            var btn = document.getElementById('btnSelectionEdit');
            btn.innerHTML = name + '&nbsp;&nbsp;&nbsp;<span class="caret"></span>';
            document.getElementById('userSelectionEdit').value = value;
            document.getElementById('userSelectionNameEdit').value = name;
        }

        function selectMeltEdit(value, name) {
            var btn = document.getElementById('btnMeltEdit');
            btn.innerHTML = name + '&nbsp;&nbsp;&nbsp;<span class="caret"></span>';
            document.getElementById('userMeltEdit').value = value;
            document.getElementById('userMeltNameEdit').value = name;
        }

        function addNewUser() {
            var username = document.getElementById('username').value;
            var usertype = document.getElementById('userType').value;
            var staffID = document.getElementById('staff').value;

            if (username == "") {
                alert("Please type username");
                return;
            }
            if (usertype == "") {
                alert("Please type usertype");
                return;
            }

            for (var i = 0; i < staff.length; i++) {
                if (staff[i] == staffID) {
                    alert("Staff ID needs to be unique value");
                    return;
                }
            }

            $.ajax({
                url: "add_user.php",
                method: "post",
                data: {action:"add_user", username:username, usertype:usertype, staffID:staffID},
            }).done(function (res) {
                if(res == "ok")
                    location.reload();
                else
                    alert(res);
            });
        }

        function editNewUser() {
            var username = document.getElementById('usernameEdit').value;
            var usertype = document.getElementById('userTypeEdit').value;
            var staffID = document.getElementById('staffEdit').value;

            var xmlhttp;
            if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
                xmlhttp = new XMLHttpRequest();
            }
            else {// code for IE6, IE5
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.onreadystatechange = function () {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    $('#myModal2').modal('hide');
                    document.location.href = "main.php";
                }
            }

            xmlhttp.open("POST", "edit_user.php?username=" + username + "&type=" + usertype + "&staff=" + staffID + "&id=" + editUserId, true);
            xmlhttp.send();
        }



        function openEditUser(id, name, staff, type) {
            editUserId = id;
            document.getElementById("usernameEdit").value = name;
            document.getElementById("staffEdit").value = staff;
            document.getElementById("userTypeEdit").value = type;
            var btn = document.getElementById('userTypeIdEdit');
            if (type == 1)
                btn.innerHTML = 'Administrator&nbsp;&nbsp;&nbsp;<span class="caret"></span>';
            else
                btn.innerHTML = 'User&nbsp;&nbsp;&nbsp;<span class="caret"></span>';

            $('#myModal2').modal('show');
        }

    </script>
</head>
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

            <div class="row profile">

                <div class="col-md-12">
                    <div class="profile-content row">
                        <h2>User Admin</h2>
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#myModal">Add User</button>
                        <div class="col-md-12">
                            <table class="table table-striped" id="userTable">
                                <thead>
                                <tr>
                                    <th style="text-align: left">Name</th>
                                    <th style="text-align: left">Staff ID</th>
                                    <th style="text-align: left">Role</th>
                                    <th style="text-align: left">Last Login</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                for ($i = 0; $i < sizeof($users); $i++) {
                                    echo "<tr>";
                                    echo "<td>" . $users[$i]['username'] . "</td>";
                                    echo "<td>" . $users[$i]['staff'] . "</td>";
                                    echo "<td>" . $users[$i]['type'] . "</td>";
                                    echo "<td>" . date("d/m/Y H:i:s", strtotime($users[$i]['last_login'])) . "</td>";
                                    echo '<td><button type="button" class="btn btn-primary btn-sm" onclick="javascript:openEditUser(' . $users[$i]['id'] . ', \'' . $users[$i]['username'] . '\', \'' . $users[$i]['staff'] . '\', ' . $users[$i]['typeId'] . ');">Edit</button>';
                                    echo '<button class="btn btn-danger delete-user" data-user="'.$users[$i]['id'].'" style="margin-left: 10px;">DELETE</button>';
                                    echo '</td>';
                                    echo "</tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="profile-content" name="content" style="display: none;">
                        Tools Admin
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add New User</h4>
            </div>
            <div class="modal-body">
                <div class="col-md-6">
                    <input type="text" name="username" id="username" tabindex="1"
                           class="form-control" placeholder="Username" value="">
                </div>
                <div class="col-md-6">
                    <input type="text" name="staff" id="staff" tabindex="1" class="form-control"
                           placeholder="Staff ID" value="">
                </div>
                <div class="dropdown col-md-12" style="margin-top: 20px;">
                    <button class="btn btn-primary dropdown-toggle" type="button"
                            data-toggle="dropdown" id="userTypeId">Select User Type
                        <span class="caret"></span></button>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:selectUsertype(1);">Administrator</a></li>
                        <li><a href="javascript:selectUsertype(0);">User</a></li>
                    </ul>
                    <input type="hidden" id="userType"/>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="addNewUser();">Add</button>
            </div>
        </div>

    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal2" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit User</h4>
            </div>
            <div class="modal-body">
                <div class="col-md-6">
                    <input type="text" name="username" id="usernameEdit" tabindex="1"
                           class="form-control" placeholder="Username" value="">
                </div>
                <div class="col-md-6">
                    <input type="text" name="staff" id="staffEdit" tabindex="1" class="form-control"
                           placeholder="Staff ID" value="">
                </div>
                <div class="dropdown col-md-12" style="margin-top: 20px;">
                    <button class="btn btn-primary dropdown-toggle" type="button"
                            data-toggle="dropdown" id="userTypeIdEdit">Select User Type
                        <span class="caret"></span></button>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:selectUsertypeEdit(1);">Administrator</a></li>
                        <li><a href="javascript:selectUsertypeEdit(0);">User</a></li>
                    </ul>
                    <input type="hidden" id="userTypeEdit"/>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="editNewUser();">Save</button>
            </div>
        </div>
    </div>
</div>
<script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
<script src="assets/js/custom.js"></script>
<script>
    $(document).ready(function(){
        $(".delete-user").on('click', function () {
            var user = $(this).data('user');
            if(confirm("Are you sure?")) {
                $.ajax({
                    url: "actions.php",
                    method: "post",
                    data: {action:"delete_user", user:user}
                }).done(function (res) {
                    if(res =="ok") {
                        location.reload();
                    } else {
                        alert("Save failed");
                    }
                });
            }


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
</body>
</html>

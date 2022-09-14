<?php
require_once('users.php');

if(isset($_SESSION['username'])) {
    header('Location: input.php');
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tooling</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="css/login.css">
    <script>
        function selectUser(username) {
            document.getElementById("username").value = username;
        }

        function ff() {
            var focusval1 = document.getElementById("aa1").value;
            var focusval2 = document.getElementById("aa2").value;
            document.getElementById("aa").innerHTML = focusval1;
            document.getElementById("bb").innerHTML = focusval2;
        }
    </script>
</head>
<body>
<div class="logo" id="logo"></div>
<div class="container">
        <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-login">
          <div class="panel-heading">
            <div class="row">
              <div class="col-xs-12">
                <p><img src="" width="534" height="68"></p>
                
                  <p><a href="#" class="active" id="login-form-link">Welcome</a><br><div class="su" id="su"> Please select a user to begin
                  </p>
                </div>
              </div>
            </div>
            <hr>
          </div>
          <div class="panel-body">
            <div class="row">
              <div class="col-lg-12">
                <form id="login-form" action="user_login.php" method="post" role="form" style="display: block;">
                  <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">Select Username
                    <span class="caret"></span></button>
                    <ul class="dropdown-menu">
                      <?php
                        for ($i = 0; $i < sizeof($users); $i++) {
                          echo "<li><a href='javascript:selectUser(\"" . $users[$i]['username'] . "\");'>" . $users[$i]['username'] . "</a></li>";
                        }
                      ?>
                    </ul>
                  </div>
                  <br/>
                  <div class="form-group">
                    <input type="text" name="username" autofocus="autofocus"  id="username" tabindex="1" class="form-control" placeholder="Enter Scan Id" value="" required autofocus>
                    <div id="locktarget" style="display: none">Target</div>
                  </div>
                  <div class="form-group">
                    <div class="row">
                      <div class="col-sm-6 col-sm-offset-3">
                        <input type="submit" name="login-submit" id="login-submit" tabindex="4" class="form-control btn btn-login" value="Log In">
                      </div>
                      
                    </div>
                  </div>
                </form>
                <div class="inspired" id="inspired"><img src="images/Inspired-Powered-by-Logo.png" width="256" height="50" alt="inspired" longdesc="http://www.inspiredonline.co.uk"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

<script>
    $(document).ready(function(){
        $("#username").focus();
        $("#username").click();

        $("#username").on('click', function () {
            var locktarget = document.querySelector('#locktarget');
            console.log('mbmbm');
            locktarget.requestPointerLock();
        });
    });

</script>
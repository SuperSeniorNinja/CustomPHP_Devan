<nav id="sidebar" class="sidebar-wrapper">
    <div class="sidebar-content">
        <div id="toggle-sidebar">
            <div></div>
            <div></div>
            <div></div>
        </div>
        <div class="sidebar-brand">
            <a href="#">TOOLING</a>
        </div>
        <div class="sidebar-header">
            <!-- sidebar-header  --><!-- sidebar-search  -->
            <div class="sidebar-menu">
                <ul>
                    <li class="header-menu">
                        <span>MENU</span>
                    </li>
                    <li>
                        <a href="index.php">
                            <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="input.php">
                            <span>Main Display</span>
                        </a>
                    </li>
                    <li>
                        <a href="output.php">
                            <span>User Display</span>
                        </a>
                    </li>
                    <li class="sidebar-dropdown">
                        <a href="#">
                            <span>Reporting</span>
                        </a>
                        <div class="sidebar-submenu">
                            <ul>
                                <li>
                                    <a href="report_build.php">Report Generator</a>
                                </li>
                                <li>
                                    <a href="report_admin.php">Report Admin</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="sidebar-dropdown">
                        <a href="#">
                            <span>Admin</span>
                        </a>
                        <div class="sidebar-submenu">
                            <ul>
                                <li>
                                    <a href="change_tools.php">Tool Admin</a>
                                </li>
                                <li>
                                    <a href="main.php">User Admin</a>
                                </li>
                                <li>
                                    <a href="shift_setting.php">Shift Setting</a>
                                </li>

                            </ul>
                        </div>
                    </li>

                    <?php
                    if($count_down == 0) {
                        echo '<li class="pause_count_down" style=" font-size: 16px;"><a class="count-down" data-kind="pause_count_down" href="#"><span>Pause Count Down</span></a></li>';
                        echo '<li class="resume_count_down" style="display: none; font-size: 16px;"><a  href="#" class="count-down" data-kind="resume_count_down"><span>Resume Count Down</span></a></li>';
                    } else{
                        echo '<li class="pause_count_down" style="display: none; font-size: 16px;"><a  href="#" class="count-down" data-kind="pause_count_down"><span>Pause Count Down</span></a></li>';
                        echo '<li class="resume_count_down" style=" font-size: 16px;"><a  href="#" class="count-down" data-kind="resume_count_down"><span>Resume Count Down</span></a></li>';
                    }
                    ?>
                    <li>
                        <a href="logout.php">
                            <span style="font-size: 26px;">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- sidebar-menu  -->
        </div>
        <!-- sidebar-content  -->
        <div class="sidebar-footer">
            INSPIRED
        </div>
    </div>
</nav>
<script>
    $(document).ready(function () {
        $(".count-down").on('click', function () {
            var id = $(this).data('kind');
            var kind;

            if(id == 'pause_count_down') {
                kind = 1;
                $(".pause_count_down").hide();
                $(".resume_count_down").show();
            } else{
                kind = 0;
                $(".pause_count_down").show();
                $(".resume_count_down").hide();
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

</script>
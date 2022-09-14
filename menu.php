<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a class="brand-link" style="padding: 13px 20px; cursor: pointer;">
        <span class="brand-text font-weight-light"><?php echo $APP_TITLE;?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?php echo ($page_name == 'Home')?'active':''; ?>">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Overview</p>
                    </a>
                </li>
                <li class="nav-item <?php echo (strpos($page_name, 'Container') > -1)?'menu-open':''; ?>">
                    <a href="#" class="nav-link <?php echo (strpos($page_name, 'Container') > -1)?'active':''; ?>">
                        <i class="nav-icon fas fa-bookmark"></i>
                        <p>
                            Container Devan
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="container_devan_main.php" class="nav-link <?php echo ($page_name == 'Container Devan Main')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Devan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="container_devan_member.php" class="nav-link <?php echo ($page_name == 'Container Devan Member')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Member Devan Input</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item <?php echo (strpos($page_name, 'Stocking') > -1)?'menu-open':''; ?>">
                    <a href="#" class="nav-link <?php echo (strpos($page_name, 'Stocking') > -1)?'active':''; ?>">
                        <i class="nav-icon fas fa-bookmark"></i>
                        <p>
                            Stocking
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="stocking_input.php" class="nav-link <?php echo ($page_name == 'Stocking Input')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Input</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="stocking_overview.php" class="nav-link <?php echo ($page_name == 'Stocking Overview')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Overview</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="stocking_admin.php" class="nav-link <?php echo ($page_name == 'Stocking Admin')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Admin</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="stocking_history.php" class="nav-link <?php echo ($page_name == 'Stocking History')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>History</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item <?php echo (strpos($page_name, 'System Fill') > -1)?'menu-open':''; ?>">
                    <a href="#" class="nav-link <?php echo (strpos($page_name, 'System Fill') > -1)?'active':''; ?>">
                        <i class="nav-icon fas fa-bookmark"></i>
                        <p>
                            System Fill
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="system_fill_main.php" class="nav-link <?php echo ($page_name == 'System Fill Main')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Main</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="system_fill_live_status.php" class="nav-link <?php echo ($page_name == 'System Fill Live Status')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Live Status</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="system_fill_andon.php" class="nav-link <?php echo ($page_name == 'System Fill Andon')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Andon</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item <?php echo (strpos($page_name, 'Sequence Pick') > -1)?'menu-open':''; ?>">
                    <a href="#" class="nav-link <?php echo (strpos($page_name, 'Sequence Pick') > -1)?'active':''; ?>">
                        <i class="nav-icon fas fa-bookmark"></i>
                        <p>
                            Sequence Pick
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="sequence_pick_list.php" class="nav-link <?php echo ($page_name == 'Sequence Pick List')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pick List</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="sequence_pick_andon.php" class="nav-link <?php echo ($page_name == 'Sequence Pick Andon')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Sequence Andon</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item <?php echo (strpos($page_name, 'Conveyance') > -1)?'menu-open':''; ?>">
                    <a href="#" class="nav-link <?php echo (strpos($page_name, 'Conveyance Delivery') > -1)?'active':''; ?>">
                        <i class="nav-icon fas fa-bookmark"></i>
                        <p>
                            Conveyance
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="conveyance_pick.php" class="nav-link <?php echo ($page_name == 'Conveyance Pick')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pick</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="conveyance_delivery.php" class="nav-link <?php echo ($page_name == 'Conveyance Delivery')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Delivery</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item <?php echo (strpos($page_name, 'Part Delivery') > -1)?'menu-open':''; ?>">
                    <a href="#" class="nav-link <?php echo (strpos($page_name, 'Part Delivery') > -1)?'active':''; ?>">
                        <i class="nav-icon fas fa-bookmark"></i>
                        <p>
                            Part Delivery
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="part_delivery_input.php" class="nav-link <?php echo ($page_name == 'Part Delivery Input')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Input</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="part_delivery_overview.php" class="nav-link <?php echo ($page_name == 'Part Delivery Overview')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Overview</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item <?php echo (strpos($page_name, 'Setting'))?'menu-open':''; ?>">
                    <a href="#" class="nav-link <?php echo (strpos($page_name, 'Setting'))?'active':''; ?>">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            Administration
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="admin_user_setting.php" class="nav-link <?php echo ($page_name == 'User Setting')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>User Setting</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="admin_shift_setting.php" class="nav-link <?php echo ($page_name == 'Shift Setting')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Shift Setting</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="admin_system_setting.php" class="nav-link <?php echo ($page_name == 'System Setting')?'active':''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>System Setting</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="barcode_generator.php" class="nav-link <?php echo ($page_name == 'Barcode Generator')?'active':''; ?>">
                        <i class="nav-icon fas fa-bookmark"></i>
                        <p>Barcode Generator</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
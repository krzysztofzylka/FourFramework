<?php
ob_start();
session_start();
include('../core/core.php');
core::init();
core::$debug['showCoreError'] = false;
$config = ['type' => 'sqlite', 'path' => 'database/adminpanel.sqlite3'];
core::$library->database->connect($config);
$account = core::loadModule('account');
if(!$account->checkUser())
	header('location: login.php');
$_GLOBALS['wersja'] = '1.0';
?>
<!DOCTYPE html>
<html lang="pl">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<title>Admin Panel - FourFramework</title>
		<link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
		<link rel="stylesheet" href="dist/css/adminlte.min.css">
	</head>
	<body class="hold-transition sidebar-mini">
		<div class="wrapper">
			<!-- Navbar -->
			<nav class="main-header navbar navbar-expand navbar-white navbar-light">
				<!-- Left navbar links -->
				<ul class="navbar-nav">
					<li class="nav-item">
						<a class="nav-link" data-widget="pushmenu" href="index.php"><i class="fas fa-bars"></i></a>
					</li>
					<li class="nav-item d-none d-sm-inline-block">
						<a href="index.php" class="nav-link">Strona główna</a>
					</li>
				</ul>
			</nav>
			<!-- /.navbar -->
			<!-- Main Sidebar Container -->
			<aside class="main-sidebar sidebar-dark-primary elevation-4">
				<!-- Brand Logo -->
				<a href="index.php" class="brand-link">
					<img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
					<span class="brand-text font-weight-light">AdminPanel</span>
				</a>
				<!-- Sidebar -->
				<div class="sidebar">
					<!-- Sidebar Menu -->
					<nav class="mt-2">
						<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
							<?php include('add/menu.php'); ?>
						</ul>
					</nav>
				<!-- /.sidebar-menu -->
				</div>
			<!-- /.sidebar -->
			</aside>
			<!-- Content Wrapper. Contains page content -->
			<div class="content-wrapper">
			<?php
			if(isset($_GET['p'])){
				$page = 'page2/'.htmlspecialchars(basename($_GET['p'])).'.php';
				if(!file_exists($page))
					include('page/404.php');
				else
					include($page);
			}elseif(isset($_GET['page'])){
				$page = 'page/'.htmlspecialchars(basename($_GET['page'])).'.php';
				if(!file_exists($page))
					include('page/404.php');
				else
					include($page);
			}else
				include('page/panel.php');
			?>
			<!-- /.content -->
			</div>
			
			<!-- /.content-wrapper -->
			<!-- Main Footer -->
			<footer class="main-footer">
				<!-- To the right -->
				<div class="float-right d-none d-sm-inline">
					Panel administracyjny dla <a href='https://programista.vxm.pl/fourframework/'>FourFramework</a>
				</div>
				<!-- Default to the left -->
				<strong>Copyright &copy; 2014-2019 <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights reserved.
			</footer>
		</div>
		<!-- ./wrapper -->
		<!-- REQUIRED SCRIPTS -->
		<!-- jQuery -->
		<script src="plugins/jquery/jquery.min.js"></script>
		<!-- Bootstrap 4 -->
		<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
		<!-- AdminLTE App -->
		<script src="dist/js/adminlte.min.js"></script>
	</body>
	<script>
	$(function () {
	  $('[data-toggle="tooltip"]').tooltip()
	})
	</script>
</html>
<?php ob_end_flush() ?>
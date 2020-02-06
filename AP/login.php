<?php
session_start();
include('../core/core.php');
core::init();
core::$debug['showCoreError'] = false;
$config = ['type' => 'sqlite', 'path' => 'database/adminpanel.sqlite3'];
core::$library->database->connect($config);
$account = core::loadModule('account');
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
	<body class="hold-transition login-page">
		<div class="login-box">
			<div class="login-logo">
				<a href="index.php">Admin Panel</a>
			</div>
			<!-- /.login-logo -->
			<div class="card">
				<div class="card-body login-card-body">
					<?php
					if(isset($_POST['login']) and isset($_POST['haslo'])){
						if($account->loginUser($_POST['login'], $_POST['haslo'])){
							header('location: index.php');
						}else
							echo '<div class="alert alert-danger" role="alert">Błędne dane logowania</div>';
					}
					?>
					<p class="login-box-msg">Zaloguj się do panelu administracyjnego FourFramework</p>
					<form action="login.php" method="post">
						<div class="input-group mb-3">
							<input type="text" name="login" class="form-control" placeholder="Login">
							<div class="input-group-append">
								<div class="input-group-text">
									<span class="fas fa-envelope"></span>
								</div>
							</div>
						</div>
						<div class="input-group mb-3">
							<input type="password" name="haslo" class="form-control" placeholder="Hasło">
							<div class="input-group-append">
								<div class="input-group-text">
									<span class="fas fa-lock"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-8"></div>
							<div class="col-4">
								<button type="submit" class="btn btn-primary btn-block">Zaloguj</button>
							</div>
						</div>
					</form>
				</div>
			<!-- /.login-card-body -->
			</div>
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
</html>

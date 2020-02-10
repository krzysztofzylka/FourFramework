<!DOCTYPE html>
<html lang="pl">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<title>{$title}</title>
		<link rel="stylesheet" href="script/plugins/fontawesome-free/css/all.min.css">
		<link rel="stylesheet" href="script/dist/css/adminlte.min.css">
		<script src='https://code.jquery.com/jquery-3.4.1.min.js'></script>
	</head>
	<body class="hold-transition sidebar-mini">
		<div class="wrapper">
			<nav class="main-header navbar navbar-expand navbar-white navbar-light">
				<ul class="navbar-nav">
					<li class="nav-item">
						<a class="nav-link" data-widget="pushmenu" href="index.php"><i class="fas fa-bars"></i></a>
					</li>
					<li class="nav-item d-none d-sm-inline-block">
						<a href="index.php" class="nav-link">Strona główna</a>
					</li>
				</ul>
			</nav>
			<aside class="main-sidebar sidebar-dark-primary elevation-4">
				<a href="index.php" class="brand-link">
					<img src="script/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
					<span class="brand-text font-weight-light">AdminPanel</span>
				</a>
				<div class="sidebar">
					<nav class="mt-2">
						<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
							{if isset($menu)}
								{$menu}
							{/if}
						</ul>
					</nav>
				</div>
			</aside>
			<div class="content-wrapper">
			{if isset($data)}
				{$data}
			{/if}
			</div>
			<footer class="main-footer">
				<div class="float-right d-none d-sm-inline">
					Panel administracyjny dla <a href='https://programista.vxm.pl/fourframework/'>FourFramework</a>
				</div>
				<strong>Copyright &copy; 2014-2019 <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights reserved.
			</footer>
		</div>
		<script src="script/plugins/jquery/jquery.min.js"></script>
		<script src="script/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="script/dist/js/adminlte.min.js"></script>
	</body>
	<script>
	$(function () {
	  $('[data-toggle="tooltip"]').tooltip()
	})
	</script>
</html>
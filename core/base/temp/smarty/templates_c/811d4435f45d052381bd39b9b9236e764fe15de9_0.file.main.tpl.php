<?php
/* Smarty version 3.1.33, created on 2020-02-10 20:47:47
  from 'C:\Users\user\Documents\GitHub\FourFramework\AP\template\main.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_5e41b363e48ad9_24632182',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '811d4435f45d052381bd39b9b9236e764fe15de9' => 
    array (
      0 => 'C:\\Users\\user\\Documents\\GitHub\\FourFramework\\AP\\template\\main.tpl',
      1 => 1581314505,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5e41b363e48ad9_24632182 (Smarty_Internal_Template $_smarty_tpl) {
?><!DOCTYPE html>
<html lang="pl">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<title><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</title>
		<link rel="stylesheet" href="script/plugins/fontawesome-free/css/all.min.css">
		<link rel="stylesheet" href="script/dist/css/adminlte.min.css">
		<?php echo '<script'; ?>
 src='https://code.jquery.com/jquery-3.4.1.min.js'><?php echo '</script'; ?>
>
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
							<?php if (isset($_smarty_tpl->tpl_vars['menu']->value)) {?>
								<?php echo $_smarty_tpl->tpl_vars['menu']->value;?>

							<?php }?>
						</ul>
					</nav>
				</div>
			</aside>
			<div class="content-wrapper">
			<?php if (isset($_smarty_tpl->tpl_vars['data']->value)) {?>
				<?php echo $_smarty_tpl->tpl_vars['data']->value;?>

			<?php }?>
			</div>
			<footer class="main-footer">
				<div class="float-right d-none d-sm-inline">
					Panel administracyjny dla <a href='https://programista.vxm.pl/fourframework/'>FourFramework</a>
				</div>
				<strong>Copyright &copy; 2014-2019 <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights reserved.
			</footer>
		</div>
		<?php echo '<script'; ?>
 src="script/plugins/jquery/jquery.min.js"><?php echo '</script'; ?>
>
		<?php echo '<script'; ?>
 src="script/plugins/bootstrap/js/bootstrap.bundle.min.js"><?php echo '</script'; ?>
>
		<?php echo '<script'; ?>
 src="script/dist/js/adminlte.min.js"><?php echo '</script'; ?>
>
	</body>
	<?php echo '<script'; ?>
>
	$(function () {
	  $('[data-toggle="tooltip"]').tooltip()
	})
	<?php echo '</script'; ?>
>
</html><?php }
}

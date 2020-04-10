<?php
$line = htmlspecialchars($_GET['debug']);
$file = htmlspecialchars($_GET['debug_file']);
$path = core::$path['log'].$file.'.log';
$readLine = file($path)[$line];
$debug = core::$library->string->between($readLine, '[', ']', 4);
?>
<div class="content-header">
	<div class="container-fluid">
		<h1 class="m-0 text-dark">Analiza logu rdzenia</h1>
	</div>
</div>

<div class="content-body">
	<div class="container-fluid table-responsive">
		<div class="card" style='overflow: auto;'>
		<?php
		core::$library->debug->print_r(json_decode(base64_decode($debug), true));
		?>
		</div>
	</div>
</div>
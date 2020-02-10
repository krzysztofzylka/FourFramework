<?php
$string = htmlspecialchars($_GET['debug']);
?>
<div class="content-header">
	<div class="container-fluid">
		<h1 class="m-0 text-dark">Analiza logu rdzenia</h1>
	</div>
</div>

<div class="content-header">
	<div class="container-fluid">
		<div class="card" style='overflow: auto;'>
		<?php
		core::$library->debug->print_r(json_decode(base64_decode($string), true));
		?>
		</div>
	</div>
</div>
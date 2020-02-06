<?php
if(!isset($_GET['string']))
	header('location: index.php?page=404');
$string = htmlspecialchars($_GET['string']);
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
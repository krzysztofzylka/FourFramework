<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 text-dark">PHPInfo</h1>
			</div>
		</div>
	</div>
</div>
<div class="content">
	<div class="container-fluid">
		<?php
		ob_start();
		phpinfo();
		$data = ob_get_contents();
		ob_end_clean();
		$data = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $data);
		echo '<style>body {background-color: #fff; color: #222; font-family: sans-serif;}
		pre {margin: 0; font-family: monospace;}
		table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}
		.center {text-align: center;}
		.center table {margin: 1em auto; text-align: left;}
		.center th {text-align: center !important;}
		td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
		h1 {font-size: 150%;}
		h2 {font-size: 125%;}
		.p {text-align: left;}
		.e {background-color: #ccf; width: 300px; font-weight: bold;}
		.h {background-color: #99c; font-weight: bold;}
		.v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
		.v i {color: #999;}
		img {float: right; border: 0;}
		hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}</style>';
		echo $data;
		?>
	</div>
</div>

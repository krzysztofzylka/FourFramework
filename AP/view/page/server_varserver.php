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
	<div class="container-fluid table-responsive">
		<?php
		core::$library->debug->print_r($_SERVER);
		?>
	</div>
</div>

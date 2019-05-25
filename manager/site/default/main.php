<h1>Informacje</h1>
<table class="title border twoData">
	<tr>
		<td>Nazwa</td>
		<td>Wartość</td>
	</tr>
	<tr>
		<td>Wersja rdzenia</td>
		<td><?php echo $core->version ?></td>
	</tr>
	<tr>
		<td>Data wydania</td>
		<td><?php echo $core->releaseDate ?></td>
	</tr>
	<tr>
		<td>Wersja menadżera</td>
		<td><?php echo $_version ?></td>
	</tr>
	<tr>
		<td>Domyślne kodowanie</td>
		<td><?php echo $core->crypt?'tak':'nie' ?></td>
	</tr>
	<tr>
		<td>URL API</td>
		<td><?php echo $core->API ?></td>
	</tr>
	<tr>
		<td>URL API Updater</td>
		<td><?php echo $core->APIUpdater ?></td>
	</tr>
</table>
<h1><?php echo $lang->get('info') ?></h1>
<table class="title border twoData">
	<tr>
		<td><?php echo $lang->get('name') ?></td>
		<td><?php echo $lang->get('value') ?></td>
	</tr>
	<tr>
		<td><?php echo $lang->get('coreversion') ?></td>
		<td><?php echo $core->version ?></td>
	</tr>
	<tr>
		<td><?php echo $lang->get('corereleasedate') ?></td>
		<td><?php echo $core->releaseDate ?></td>
	</tr>
	<tr>
		<td><?php echo $lang->get('managerversion') ?></td>
		<td><?php echo $_version ?></td>
	</tr>
	<tr>
		<td><?php echo $lang->get('defaultcrypt') ?></td>
		<td><?php echo $core->crypt?$lang->get('yes'):$lang->get('no') ?></td>
	</tr>
	<tr>
		<td><?php echo $lang->get('urlapi') ?></td>
		<td><?php echo $core->API ?></td>
	</tr>
	<tr>
		<td><?php echo $lang->get('urlupdaterapi') ?></td>
		<td><?php echo $core->APIUpdater ?></td>
	</tr>
</table>
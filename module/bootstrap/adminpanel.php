<table class="title border">
	<tr>
		<td>Nazwa</td>
		<td>Wartośc</td>
	</tr>
	<tr>
		<td>Wersja modułu</td>
		<td><?php echo $module->__debugInfo()['version'] ?></td>
	</tr>
	<tr>
		<td>Wersja bootstrap</td>
		<td><?php echo $module->__debugInfo()['bootstrap']['version'] ?></td>
	</tr>
</table>
<br />
<h1>Podgląd zmiennej adding</h1>
<pre><?php echo str_replace('	', '', str_replace('>', '&gt;', str_replace('<', '&lt;', $module->adding))) ?></pre>
<li class="nav-item">
	<a href="index.php" class="nav-link active">
		<i class="nav-icon fas fa-th"></i>
		<p>Panel główny</p>
	</a>
</li>
<?php
if(file_exists('add/menu_user.php'))
	include('add/menu_user.php');
?>
<li class="nav-item has-treeview">
	<a href="#" class="nav-link">
		<i class="nav-icon fas fa-globe"></i>
		<p>Framework</p>
		<i class="right fas fa-angle-left"></i>
	</a>
	<ul class="nav nav-treeview">
		<li class="nav-item">
			<a href="?page=framework_informacje" class="nav-link">
				<i class="far fa-circle nav-icon"></i>
				<p>Informacje</p>
			</a>
		</li>
		<li class="nav-item">
			<a href="?page=framework_biblioteki" class="nav-link">
				<i class="far fa-circle nav-icon"></i>
				<p>Biblioteki</p>
			</a>
		</li>
		<li class="nav-item">
			<a href="?page=framework_moduly" class="nav-link">
				<i class="far fa-circle nav-icon"></i>
				<p>Moduły</p>
			</a>
		</li>
		<li class="nav-item">
			<a href="?page=framework_logi" class="nav-link">
				<i class="fas fa-file nav-icon"></i>
				<p>Logi</p>
			</a>
		</li>
		<li class="nav-item">
			<a href="?page=fdbeditor" class="nav-link">
				<i class="fas fa-database nav-icon"></i>
				<p>FDB Editor</p>
			</a>
		</li>
	</ul>
</li>
<li class="nav-item has-treeview">
	<a href="#" class="nav-link">
		<i class="nav-icon fas fa-user"></i>
		<p>Użytkownik</p>
		<i class="right fas fa-angle-left"></i>
	</a>
	<ul class="nav nav-treeview">
		<li class="nav-item">
			<a href="?page=user_zmienHaslo" class="nav-link">
				<i class="fas fa-key nav-icon"></i>
				<p>Zmień hasło</p>
			</a>
		</li>
		<li class="nav-item">
			<a href="?page=user_wyloguj" class="nav-link">
				<i class="fas fa-sign-out-alt nav-icon"></i>
				<p>Wyloguj</p>
			</a>
		</li>
	</ul>
</li>
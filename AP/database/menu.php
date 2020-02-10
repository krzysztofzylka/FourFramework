<?php
return [
	[
		'href' => 'index.php',
		'icon' => 'fas fa-th',
		'name' => 'Panel główny',
		'class' => 'active'
	],
	[
		'href' => '#',
		'icon' => 'fas fa-plug',
		'name' => 'Moduły',
		'menu' => []
	],
	[
		'href' => '#',
		'icon' => 'fas fa-globe',
		'name' => 'Framework',
		'menu' => [
			[
				'href' => '?page=framework_informacje',
				'icon' => 'fas fa-info',
				'name' => 'Informacje',
			],
			[
				'href' => '?page=framework_biblioteki',
				'icon' => 'fas fa-puzzle-piece',
				'name' => 'Biblioteki'
			],
			[
				'href' => '?page=framework_moduly',
				'icon' => 'fas fa-plug',
				'name' => 'Moduły'
			],
			[
				'href' => '?page=framework_logi',
				'icon' => 'fas fa-file',
				'name' => 'Logi'
			],
			[
				'href' => '?page=fdbeditor',
				'icon' => 'fas fa-database',
				'name' => 'FDB Editor'
			]
		]
	],
	[
		'href' => '#',
		'icon' => 'fas fa-user',
		'name' => 'Użytkownik',
		'menu' => [
			[
				'href' => '?page=user_zmienHaslo',
				'icon' => 'fas fa-key',
				'name' => 'Zmień hasło',
			],
			[
				'href' => '?page=user_wyloguj',
				'icon' => 'fas fa-sign-out-alt',
				'name' => 'Wyloguj'
			]
		]
	]
];
?>
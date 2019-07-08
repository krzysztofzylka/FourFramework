<?php
$mod_path = $core->path['dir_module'];
$core->path['dir_module'] = 'module/';
$core->loadModule('language');
$core->path['dir_module'] = $mod_path;
?>
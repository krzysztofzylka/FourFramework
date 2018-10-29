<?php
require('core/core.php');

$core = new core();
$core->templateSet('title', 'Tytuł strony');
$core->Template('header');
$core->loadController('index');
$core->Template('footer');
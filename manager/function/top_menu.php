<?php
$menu = '';
$incmen = include('function/data_menu.php');
foreach($incmen as $item){
	$menu .= '<a href="'.(isset($item['url'])?$item['url']:(isset($item['type'])?'index.php?type='.$item['type'].(isset($item['page'])?'&page='.$item['page']:''):'#')).'" class="'.(isset($item['class'])?$item['class']:'').'">'.$item['name'].'</a>';
}
$core->templateSet('menu', $menu);
?>
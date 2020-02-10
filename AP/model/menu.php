<?php
return new class(){
	private $load = null;
	private $data = '';
	public function loadMenu(){
		$this->load = include(__dir__.'/../database/menu.php');
		$this->loadModuleMenu();
		$copy = $this->load[0];
		unset($this->load[0]);
		$menu_user = file_exists(__dir__.'/../database/menu_user.php')?include(__dir__.'/../database/menu_user.php'):null;
		if(is_array($menu_user))
			foreach($menu_user as $item)
				array_unshift($this->load, $item);
		array_unshift($this->load, $copy);
		$this->loadUserMenu();
		$this->loadTree($this->load);
		return $this->data;
	}
	private function loadTree($array){
		foreach($array as $item){
			$this->data .= '<li class="nav-item '.(isset($item['menu'])?'has-treeview':'').'">
				<a href="'.$item['href'].'" class="nav-link '.(isset($item['class'])?$item['class']:'').'">
					<i class="nav-icon '.(isset($item['icon'])?$item['icon']:'fas fa-circle').'"></i>
					<p>'.$item['name'].'</p>
					'.(isset($item['menu'])?'<i class="right fas fa-angle-left"></i>':'').'
				</a>';
				if(isset($item['menu'])){
					$this->data .= '<ul class="nav nav-treeview">';
					$this->loadTree($item['menu']);
					$this->data .= '</ul>';
				}
			$this->data .= '</li>';
		}
	}
	private function loadUserMenu(){
		
	}
	private function loadModuleMenu(){
		$search = core::$library->array->searchByKey($this->load, 'name', 'Moduły');
		$list = core::$library->module->moduleList(true);
		foreach($list as $item)
			if(isset($item['config']['adminPanel']))
				if(isset($item['config']['apMenu'])){
					$apMenu = $item['config']['apMenu'];
					array_push($this->load[$search]['menu'], [
						'href' => '?page=framework_moduly&type=adminpanel&modul='.$item['name'],
						'icon' => 'fas '.$apMenu['icon'],
						'name' => $apMenu['name']
					]);
				}
	}
}
?>
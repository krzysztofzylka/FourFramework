<?php
$this->path = [
	 'dir_core' => $this->reversion.'core/',
	'file_core' => $this->reversion.'core/core.php',
	 'dir_library' => $this->reversion.'core/library/',
	 'dir_controller' => $this->reversion.'controller/',
	 'dir_model' => $this->reversion.'model/',
	 'dir_log' => $this->reversion.'core/base/log/',
	 'dir_log_php' => $this->reversion.'core/base/log/',
	 'dir_module' => $this->reversion.'module/',
	 'dir_template' => $this->reversion.'template/',
	 'dir_view' => $this->reversion.'view/',
	 'dir_temp' => $this->reversion.'temp/',
	 'dir_base' => $this->reversion.'core/base/',
	 'dir_db' => $this->reversion.'core/base/db/',
];
$this->module = [];
$this->module_list = [];
$this->module_config = [];
$this->model = [];
$this->model_list = [];
$this->template_extension = '.inc.tpl';
$this->array_template_list = [];
$this->array_template = [];
$this->log_file = 'log_'.date('Y_m').'.log';
$this->log_save = true;
$this->log_hide_type = ['message', 'info'];
$this->error = true;
$this->php_error = true;
$this->php_error_file = 'log_php_'.date('Y_m').'.log';
$this->API_secure = false;
$this->API = ($this->API_secure==true?'https':'http').'://www.fourframework.hmcloud.pl/module/'; //API url
$this->library = null;
$this->lastError = null;
$this->crypt = true;
$this->showError = [
	'show' => false,
	'show_number' => true,
	'show_name' => true,
	'show_message' => true
];
$this->APIUpdater = ($this->API_secure==true?'https':'http').'://www.fourframework.hmcloud.pl/updater/'; //API url
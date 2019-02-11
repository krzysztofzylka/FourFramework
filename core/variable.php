<?php
//path list
$this->path = [
	 'dir_core' => $this->reversion.'core/',
	'file_core' => $this->reversion.'core/core.php',
	 'dir_extension' => $this->reversion.'core/extension/',
	 'dir_ext_db' => $this->reversion.'core/extension/db/',
	'file_ext_db_base' => $this->reversion.'core/extension/db/base.zip',
	 'dir_ext_moduleManager' => $this->reversion.'core/extension/moduleManager/',
	 'dir_ext_test' => $this->reversion.'core/extension/test/',
	 'dir_library' => $this->reversion.'core/library/',
	 'dir_controller' => $this->reversion.'controller/',
	 'dir_model' => $this->reversion.'model/',
	 'dir_log' => $this->reversion.'log/',
	 'dir_log_php' => $this->reversion.'log/',
	 'dir_module' => $this->reversion.'module/',
	 'dir_template' => $this->reversion.'template/',
	 'dir_view' => $this->reversion.'view/',
	 'dir_temp' => $this->reversion.'temp/',
];
//for module
$this->module = [];
$this->module_list = [];
$this->module_config = [];
//for model
$this->model = [];
$this->model_list = [];
//for template
$this->template_extension = '.inc.tpl';
$this->array_template_list = [];
$this->array_template = [];
//for log
$this->log_file = 'log_'.date('Y_m').'.log'; //file name
$this->log_save = true; //active
$this->log_hide_type = ['message', 'info']; //hidden log types
//for error
$this->error = true;
$this->php_error = true;
$this->php_error_file = 'log_php_'.date('Y_m').'.log';
//for extension
$this->db = null;
$this->moduleManager = null;
$this->test = null;
//fot API
$this->API_secure = false; //ssl
$this->API = ($this->API_secure==true?'https':'http').'://www.fourframework.hmcloud.pl'; //API url
//for library
$this->library = null;
//for error
$this->lastError = null;
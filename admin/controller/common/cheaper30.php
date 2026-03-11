<?php
class ControllerCommonCheaper30 extends Controller {
	public function index(){
		$this->load->language('common/cheaper30');
		
		$data['text_module_request'] = $this->language->get('text_module_request');
		$this->load->model('extension/module/cheaper30');
		$data['cheaperings'] = $this->model_extension_module_cheaper30->getCheaperingTotalStatus();
		
		$this->load->model('extension/module/cheaper30');
		$config = $this->model_extension_module_cheaper30->config_version();
		if ($config['version'] == '2.2' or $config['version'] == '2.1' or $config['version'] == '2.0'){
			if ($data['cheaperings']){
				return $this->load->view('common/cheaper30.tpl', $data);
			}
		} else {
			if ($data['cheaperings']){
				return $this->load->view('common/cheaper30', $data);
			}
		}
	}
}
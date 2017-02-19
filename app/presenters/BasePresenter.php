<?php
namespace FrontModule;

abstract class BasePresenter extends \Nette\Application\UI\Presenter{
	public $presentername;
	public $config = array();
	
	public function startup(){
		parent::startup();
		$this->setDefaultScripts();
		$this->loadConfig();
	}
	
	public function beforeRender(){
		parent::beforeRender();
		$this->template->today = new \DateTime();
		$this->template->working = $this->config['working']->value === 'true';
	}
	
	protected function loadConfig(){
		$data = $this->context->getService("dibi")->query("SELECT * FROM ski_config")->fetchAll();
		
		libxml_use_internal_errors(true);
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);

		foreach($data as $item){
			$isxml = simplexml_load_string($item->value);
			if($isxml){
				xml_parse_into_struct($parser, $item->value, $values, $tags);
				xml_parser_free($parser);
				$obj = new \stdClass();
				foreach($values as $val){
					if($val['level'] > 1){
						$obj->{$val['tag']} = !empty($val['value']) ? $val['value'] : null;
					}
				}
				$item->value = $obj;
			}
			$this->config[$item->name] = $item;
		}
		\Tracy\Debugger::barDump($this->config);
	}
	
	protected function setDefaultScripts(){
		$this->template->scripts = array();
		$this->presentername = substr($this->name, strrpos(':' . $this->name, ':'));
		
		$file1 = strtolower($this->presentername) . '.js';
		$file2 = strtolower($this->view) . '.js';

		foreach(\Nette\Utils\Finder::findFiles($file1)->in(WWW_DIR.'/js') as $name => $file){
			$this->template->scripts[$name] = array("dir" => null, "name" => $file1 .'?'. time());
		}
		try{
			foreach(\Nette\Utils\Finder::findFiles($file1, $file2)->in(WWW_DIR.'/js/'.$this->presentername) as $name => $file){
				$this->template->scripts[$name] = array("dir" => '/js/'.$this->presentername.'/', "name" => $file->getFilename() .'?'. time());
			}
		}
		catch(\Exception $e){}
	}

	protected function removeScript($name){
		if(isset($this->template->scripts[$name])){
	    	unset($this->template->scripts[$name]);
		}
	}

	protected function removeStyle($name){
		if(isset($this->template->cssStyles[$name])){
			unset($this->template->cssStyles[$name]);
		}
	}

	protected function addScript($name, $dir = ""){
		$cdnBasePath = "";
		
		if(empty($dir))
			$path = $cdnBasePath . "/js/";
		elseif(strpos($dir, "http") === false)
			$path = $cdnBasePath . $dir;
		else
			$path = $dir;
		
		$this->template->scripts[$name] = array("dir" => $dir, "name" => $name);
	}
}

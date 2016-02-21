<?php
namespace FrontModule;

abstract class BasePresenter extends \Nette\Application\UI\Presenter{
	public $presentername;
	
	public function startup(){
		parent::startup();
		$this->setDefaultScripts();
	}
	
	public function beforeRender(){
		parent::beforeRender();
		$this->template->today = new \DateTime();
		$this->template->working = $this->context->parameters['working'];
	}

	/**
	* Setting javascripts files common for all presenters
	*/
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
}

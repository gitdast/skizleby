<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


class ChangePasswordForm extends Nette\Application\UI\Form {
	
	/** @var App\Model\UserAuthentication */
	private $model;
	
	/** @var Nette\Security\User */
	private $user;

	public function __construct(\App\Model\UserAuthentication $model, Nette\Security\User $user){
		$this->model = $model;
		$this->user = $user;
	}
	
	protected function attached($presenter){
		parent::attached($presenter);
		$this->buildForm();
	}

	public function buildForm(){
		$this->addText('oldpassword', 'Staré heslo:')
			->setRequired('Zadejte původní heslo.');

		$this->addPassword('password', 'Nové heslo:')
			->setRequired('Zadejte nové heslo.');
		
		$this->addPassword('password2', 'Znovu nové heslo:')
			->setRequired('Zadejte znovu nové heslo.')
			->addConditionOn($this['password'], Form::VALID)
                ->addRule(Form::FILLED, 'Zadejte znovu nové heslo.')
                ->addRule(Form::EQUAL, 'Hesla se neshodují.', $this['password']);

		$this->addSubmit('send', 'Změnit');

		$this->onSuccess[] = array($this, 'formSucceeded');
	}

	public function formSucceeded($form){
		$values = $this->getValues();
		\Tracy\Debugger::barDump($values, "values");
		
		try {
			$this->model->changePassword($values, $this->user);
		}
		catch (\Exception $e) {
			$this->addError($e->getMessage());
			$this->addError("Vyskytla se chyba při ukládání.");
		}
		
		if(!$this->hasErrors()){
			$this->presenter->redirect('this');
		}
	}

}

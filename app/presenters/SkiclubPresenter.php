<?php
namespace FrontModule;

use \Nette\Application\UI\Form;

class SkiclubPresenter extends BasePresenter{

	public function renderDefault()	{
		
	}
	
	public function createComponentLoginForm() {
		$form = new Form;
		$form->addProtection();
		
        $form->addText('username', 'Uživatelské jméno')
			->addRule(Form::FILLED, 'Vyplňte uživatelské jméno.')
			->setAttribute('placeholder', "Uživatelské jméno");

		$form->addPassword('password', 'Heslo')
			->addRule(Form::FILLED, 'Vyplňte heslo.')
			->setAttribute('placeholder', "Heslo");

		$form->addSubmit('login', 'Přihlásit')->setAttribute("class", "pull-right");

		$form->onSuccess[] = array($this, 'loginFormSubmitted');
		return $form;
	}
	
	public function loginFormSubmitted(Form $form ){
		if($form->isValid()){
			$values = $form->getValues();
			$username = $values['username'];
			$password = $values['password'];
			try {
				$this->getUser()->login($username, $password, false);
			    $logoutTime = isset($this->context->parameters->logoutTime) ? $this->context->parameters->logoutTime : "+120 minutes";
				if($this->getUser()->isLoggedIn()) $this->getUser()->setExpiration($logoutTime, TRUE, TRUE);
				$this->flashMessage("Login OK", 'success');
			}
			catch (Nette\Security\AuthenticationException $e) {
				if($e->getCode()==1){
					$this->flashMessage("Neplatné uživatelské jméno", 'error');
				}
				else{
					$this->flashMessage("Neplatné heslo", 'error');
				}
				$this->redirect("this");
			}
			
			
			$this->redirect("this");
		}
	}
	
}
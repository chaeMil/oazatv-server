<?php

namespace App\AdminModule;

use Nette,
    Model,
    Nette\Utils\Html;

class SignPresenter extends BasePresenter
{
        public function renderDefault() {
            if ($this->getUser()->isLoggedIn()) {
                $this->redirect(':Admin:Main:');
            }
        }

	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new Nette\Application\UI\Form;
		$form->addText('username', 'Login')
			->setRequired('Please enter your username.')
                        ->setAttribute("class","form-control");

		$form->addPassword('password', 'Heslo')
			->setRequired('Please enter your password.')
                        ->setAttribute("class", "form-control");

		$form->addSubmit('send', 'Přihlásit')
                        ->setAttribute("class", "btn-lg btn-success btn-block");

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = $this->signInFormSucceeded;
                
                // setup Bootstrap form rendering
                $this->bootstrapFormRendering($form);
        
		return $form;
	}


	public function signInFormSucceeded($form)
	{
		$values = $form->getValues();

                $this->getUser()->setExpiration('20 minutes', TRUE);

		try {
			$this->getUser()->login($values->username, $values->password);
			$this->redirect(':Admin:Main:');

		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
                
	}


	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('You have been signed out.');
		$this->redirect('Sign:default');
	}

}

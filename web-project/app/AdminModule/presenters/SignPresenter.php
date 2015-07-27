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
		$form->addText('username', 'Username')
			->setRequired('Please enter your username.')
                        ->setAttribute("class","login-inp");

		$form->addPassword('password', 'Password')
			->setRequired('Please enter your password.')
                        ->setAttribute("class", "login-inp");

		$form->addCheckbox('remember', Html::el('span')->setHtml('<label for="login-check">Remember me</label>'))
                        ->setAttribute("class", "checkbox-size");

		$form->addSubmit('send', 'Sign in')
                        ->setAttribute("class", "submit-login");

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = $this->signInFormSucceeded;
		return $form;
	}


	public function signInFormSucceeded($form)
	{
		$values = $form->getValues();

		if ($values->remember) {
			$this->getUser()->setExpiration('14 days', FALSE);
		} else {
			$this->getUser()->setExpiration('20 minutes', TRUE);
		}

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

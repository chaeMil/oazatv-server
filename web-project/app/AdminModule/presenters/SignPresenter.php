<?php

namespace App\AdminModule;

use Nette,
    Model,
    Model\UserManager,
    App\EventLogger;

class SignPresenter extends BasePresenter {
    
    public $database;
    private $userManager;

    function __construct(UserManager $userManager, Nette\Database\Context $database) {
        $this->userManager = $userManager;
        $this->database = $database;
    }

    public function renderDefault() {
        $this->getUser()->getStorage()->setNamespace('admin');
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect(':Admin:Main:');
        }
    }

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm() {
        $form = new Nette\Application\UI\Form;
        $form->addText('username', 'Login')
                ->setRequired('Please enter your username.')
                ->setAttribute("class", "form-control");

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

    public function signInFormSucceeded($form) {
        $values = $form->getValues();

        $this->getUser()->setExpiration('20 minutes', TRUE);

        try {
            
            $user = $this->database->table("admin_users")->get($this->getUser()->getId());
            $user->update(Array(
                "lastlogin_time" => time(),
                "lastlogin_ip" => $_SERVER['REMOTE_ADDR']
                )
            );
            
            $this->getUser()->getStorage()->setNamespace('admin');
            $this->getUser()->login($values->username, $values->password);
            
            EventLogger::log('user '.$values->username.' logged in', EventLogger::AUTH_LOG);
            
            $this->redirect(':Admin:Main:');
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function actionOut() {
        $this->userManager->emptyUserTempFolder($this->getUser()->getId());
        EventLogger::log('user '.$this->getUser()->storage->identity->login.' logged out', EventLogger::AUTH_LOG);
        $this->getUser()->logout();
        $this->flashMessage('You have been signed out.');
        $this->redirect('Sign:in');
    }

}

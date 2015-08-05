<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
    Model,
    App\Model\UserManager,
    Nette\Forms\Form;

/**
 * Description of SettingsPresenter
 *
 * @author chaemil
 */
class SettingsPresenter extends BaseSecuredPresenter {

    private $model;
    public $database;
    private $userManager;

    function __construct(Nette\Database\Context $database, Model\AdminFacade $adminFacade, UserManager $userManager) {
        parent::__construct($database);
        $this->model = $adminFacade;
        $this->database = $database;
        $this->userManager = $userManager;
    }

    function renderDefault() {
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->users = $this->database->table(DB_ADMIN_PREFIX . "users")
                ->fetchAll();
    }

    function renderAddUser() {
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->users = $this->database->table(DB_ADMIN_PREFIX . "users")
                ->fetchAll();
    }
    
    function actionDeleteUser($user_id) {
        $user = $this->database->table(DB_ADMIN_PREFIX."users")->where("id", $user_id)
                ->fetch();
        $this->flashMessage("Uživatel '".$user->login."' byl smazán", "warning");
        $this->userManager->delete($user_id);
        $this->redirect("Settings:default");
    }

    function createComponentAddUser() {
        $form = new Nette\Application\UI\Form;

        $form->addText('userName', "Login:")
                ->setRequired()
                ->setAttribute("class", "form-control");

        $form->addPassword('pass', 'Heslo:')
                ->setRequired('Please enter your password.')
                ->setAttribute("class", "form-control")
                ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 8);

        $form->addPassword('rePass', 'Heslo znovu:')
                ->setRequired('Please retype your password.')
                ->setAttribute("class", "form-control")
                ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['pass']);

        $form->addSubmit('save', null)
                ->setAttribute("class", "btn btn-primary btn-xl");

        $form->onSuccess[] = $this->addUserSucceeded;

        $this->bootstrapFormRendering($form);

        return $form;
    }

    public function addUserSucceeded($form) {
        $vals = $form->getValues();
        if ($this->userManager->add($vals->userName, $vals->pass) == true) {
            $this->flashMessage("Uživatel '" . $vals->userName . "' úspěšně přidán", "info");
            $this->redirect("Settings:default");
        } else {
            $this->flashMessage("Uživatel '" . $vals->userName . "' už existuje", "danger");
            $this->redirect("Settings:default");
        }
    }

}

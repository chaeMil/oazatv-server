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
    Nette\Forms\Form,
    Model\ServerSettings;

/**
 * Description of SettingsPresenter
 *
 * @author chaemil
 */
class SettingsPresenter extends BaseSecuredPresenter {

    private $model;
    public $database;
    private $userManager;
    private $settings;

    function __construct(Nette\Database\Context $database, 
            UserManager $userManager, ServerSettings $settings) {
        parent::__construct($database);
        $this->database = $database;
        $this->userManager = $userManager;
        $this->settings = $settings;
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
    
    function createComponentServerSettings() {
        $keyValues = $this->settings->loadAllSettings();

        $form = new Nette\Application\UI\Form;
        
        foreach($keyValues as $keyValue) {
            $form->addText($keyValue['key'], $keyValue['key'])
                    ->setAttribute("class", "form-control")
                    ->setDefaultValue($keyValue['value']);
        }
        
        $form->addSubmit("submit", "uložit")
                ->setAttribute("class", "btn btn-success btn-lg");
        
        $this->bootstrapFormRendering($form);
        
        return $form;
    }
    
    function createComponentAddKeyValue() {
        $form = new Nette\Application\UI\Form;
        
        $form->addText("key", "klíč")
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addText("value", "hodnota")
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addSubmit("submit", "uložit")
                ->setAttribute("class", "btn btn-success btn-lg");
        
        $form->onSuccess[] = $this->addKeyValueSucceeded;
        
        $this->bootstrapFormRendering($form);
        
        return $form;
    }
    
    function createComponentDeleteKey() {
        $form = new Nette\Application\UI\Form;
        
        $keys = $this->settings->loadAllSettings();
        
        $keysArray = array();
        $keysArray[''] = "";
        
        foreach($keys as $key) {
            $keysArray[$key['key']] = $key['key']." = ".$key['value'];
        }
        
        $form->addSelect("delete", "smazat")
                ->setItems($keysArray)
                ->setAttribute("class", "form-control");
        
        $form->addSubmit("submit", "smazat")
                ->setAttribute("class", "btn btn-danger");
        
        $this->bootstrapFormRendering($form);
        
       return $form; 
    }
    
    function addKeyValueSucceeded($form) {
        $values = $form->getValues();
        
        $this->settings->saveValue($values->key, $values->value);
        
        $this->flashMessage("Hodnota uložena", "success");
        $this->redirect("Settings:");
    }
    
    function renderAddKeyValue() {
        $this->getTemplateVariables($this->getUser()->getId());
    }

}

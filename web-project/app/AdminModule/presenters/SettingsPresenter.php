<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
    Model,
    Model\UserManager,
    Nette\Forms\Form,
    Model\ServerSettings,
    App\EventLogger;

/**
 * Description of SettingsPresenter
 *
 * @author chaemil
 */
class SettingsPresenter extends BaseSecuredPresenter {

    public $database;
    private $userManager;
    private $settings;

    function __construct(Nette\DI\Container $container, Nette\Database\Context $database,
            UserManager $userManager, ServerSettings $settings) {
        parent::__construct($container, $database);
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
        
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' deleted user '. $user->login, 
                EventLogger::ACTIONS_LOG);
        
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

        $form->onSuccess[] = [$this, 'addUserSucceeded'];

        $this->bootstrapFormRendering($form);

        return $form;
    }

    public function addUserSucceeded($form) {
        $vals = $form->getValues();
        if ($this->userManager->add($vals->userName, $vals->pass) == true) {
            
            EventLogger::log('user '.$this->getUser()->getIdentity()->login.' added user '. $vals->userName, 
                EventLogger::ACTIONS_LOG);
            
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
        
        $form->onSuccess[] = [$this, 'saveServerSettingsSucceeded'];
        
        $this->bootstrapFormRendering($form);
        
        return $form;
    }
    
    public function saveServerSettingsSucceeded($form) {
        $values = $form->getValues();
        $values = (array) $values;
        
        foreach($values as $value) {
            $key = array_search ($value, $values);
            $this->settings->saveValue($key, $value);
            
            EventLogger::log('user '.$this->getUser()->getIdentity()->login.' set server variable '.$key.'='.$value, 
                EventLogger::ACTIONS_LOG);
        }
        
        $this->flashMessage("Nastavení uložena", "success");
        $this->redirect("Settings:");
    }
    
    function createComponentAddKeyValue() {
        $form = new Nette\Application\UI\Form;
        
        $form->addText("key", "klíč [a-zA-Z0-9]")
                ->setRequired()
                ->setAttribute("pattern", "[a-zA-Z0-9_]+")
                ->setAttribute("class", "form-control");
        
        $form->addText("value", "hodnota [a-zA-Z0-9]")
                ->setRequired()
                ->setAttribute("pattern", "[a-zA-Z0-9_]+")
                ->setAttribute("class", "form-control");
        
        $form->addSubmit("submit", "uložit")
                ->setAttribute("class", "btn btn-success btn-lg");
        
        $form->onSuccess[] = [$this, 'addKeyValueSucceeded'];
        
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
        
        $form->onSuccess[] = [$this, 'deleteKeySucceeded'];
        
        $this->bootstrapFormRendering($form);
        
       return $form; 
    }
    
    function addKeyValueSucceeded($form) {
        $values = $form->getValues();
        
        $this->settings->saveValue($values->key, $values->value);
        
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' set server variable '.$values->key.'='.$values->value, 
                EventLogger::ACTIONS_LOG);
        
        $this->flashMessage("Hodnota uložena", "success");
        $this->redirect("Settings:");
    }
    
    function deleteKeySucceeded($form) {
        $values = $form->getValues();
        
        if ($values->delete != "") {
            EventLogger::log('user '.$this->getUser()->getIdentity()->login.' deleted server variable '.$values->delete, 
                EventLogger::ACTIONS_LOG);
            
            $this->settings->deleteKey($values->delete);
            $this->flashMessage("Klíč smazán", "danger");
        }
        $this->redirect("Settings:");
    }
    
    function renderAddKeyValue() {
        $this->getTemplateVariables($this->getUser()->getId());
    }

}

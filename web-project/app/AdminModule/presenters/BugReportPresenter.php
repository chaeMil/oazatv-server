<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\UserManager,
 Model\BugReport;

/**
 * Description of BugReportPresenter
 *
 * @author chaemil
 */
class BugReportPresenter extends BaseSecuredPresenter {
    
    public $database;
    private $bugReport;
    private $userManager;
    
    function __construct(Nette\Database\Context $database,
                         BugReport $bugReport, UserManager $userManager) {
        parent::__construct($database);
        $this->database = $database;
        $this->bugReport = $bugReport;
        $this->userManager = $userManager;
    }
    
    function renderDefault() {       
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->userManager = $this->userManager;
        
        $this->template->unresolvedBugs = $this->bugReport->getBugsFromDB(2);
        $this->template->resolvedBugs = $this->bugReport->getBugsFromDB(1);
    }
    
    public function actionAdd() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    public function renderDetail($id) {
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->bug = $this->bugReport->getBugFromDB($id);
        $this->template->userManager = $this->userManager;
    }
    
    public function createComponentAddBugReport() {
        $form = new Nette\Application\UI\Form;
        
        $priority = array(0 => "malá", 1 => "normální", 2 => "velká", 3 => "urgentní");
        
        $form->addHidden("user_id")
                ->setValue($this->getUser()->getId());
        
        $form->addText("bug_name", "název:")
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addTextArea("bug_desc", "popis chyby:")
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addSelect("priority", "priorita:")
                ->setItems($priority)
                ->setAttribute("class", "form-control");
        
        $form->addSubmit("submit", "Nahlásit")
                ->setHtmlId("submit")
                ->setAttribute("class", "btn btn-primary btn-xl");
        
        $form->onSuccess[] = $this->addBugReportSucceeded;
        
        $this->bootstrapFormRendering($form);
        
        return $form;
    }
    
    public function addBugReportSucceeded($form) {
        $values = $form->getValues();
        
        $this->bugReport->reportBug($values);
        
        $this->flashMessage("Chyba byla nahlášena", "info");
        $this->redirect("BugReport:default");
    }
}

<?php

namespace App\AdminModule;

use Nette,
 Nette\Application\UI\Form,
 Model\LiveStreamManager,
 Model\AnalyticsManager;

/**
 * Presenter for all common sites in administration
 * 
 * @author Michal Mlejnek <chaemil72@gmail.com>
 */
class LiveStreamPresenter extends BaseSecuredPresenter {
    
    public $database;
    public $liveStreamManager;
    public $analyticsManager;

    function __construct(Nette\Database\Context $database, 
            LiveStreamManager $liveStreamManager,
            AnalyticsManager $analyticsManager) {
        $this->database = $database;
        $this->liveStreamManager = $liveStreamManager;
        $this->analyticsManager = $analyticsManager;
    }

    public function renderDefault() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    public function renderAliveUsers() {
        $request = $this->getHttpRequest();
        $adminUserOazaId = $request->getCookie('oaza_user-id');
        $aliveUsers = $this->analyticsManager
                ->getAliveUsersFromPage("live-stream", 1, $adminUserOazaId);
        $this->template->aliveUsers = $aliveUsers;
    }
    
    public function createComponentValuesForm() {        
        $form = new Nette\Application\UI\Form;
        
        $values = $this->liveStreamManager->loadValues();
        
        $translations = array(
            'on_air' => 'OnAir',
            'youtube_link' => 'Youtube ID',
            'bottom_text_cs' => 'Text pod přehrávačem (cz)',
            'bottom_text_en' => 'Text pod přehrávačem (en)',);
        
        $types = array(
            'on_air' => 'on_air',
            'youtube_link' => 'text',
            'bottom_text_cs' => 'textarea',
            'bottom_text_en' => 'textarea',);
        
        foreach($values as $key => $value) {
            
            switch ($types[$key]) {
                case 'text':   
                    $form->addText($key, $translations[$key])
                        ->setValue($value)
                        ->setAttribute("class", "form-control");
                        break;
                case 'textarea':
                    $form->addTextArea($key, $translations[$key])
                        ->setValue($value)
                        ->setAttribute("class", "form-control ckeditor");
                    break;
                case 'on_air':
                    $form->addSelect($key, $translations[$key])
                        ->setItems(array('online' => 'online', 'offline' => 'offline'))
                        ->setValue($value)
                        ->setAttribute("class", "form-control");
                    break;
            }
            
            
        }
        
        $form->addSubmit('send', 'Uložit')
                ->setAttribute("class", "btn-lg btn-success btn-block");
        
        // call method signInFormSucceeded() on success
        $form->onSuccess[] = [$this, 'valuesSucceeded'];

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }
    
    public function valuesSucceeded(Form $form) {
        $values = $form->getValues();
        $this->liveStreamManager->saveValues($values);
        
        $this->flashMessage("Úspěšně uloženo!", "success");
        $this->redirect("default");
    }
    
}
    
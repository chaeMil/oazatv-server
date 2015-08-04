<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;


use Nette,
 App\Constants,
 Model\VideoManager,
 App\StringUtils,
Nette\Utils\Strings;

/**
 * Description of UploadPresenter
 *
 * @author chaemil
 */
class UploadPresenter extends BaseSecuredPresenter {
    
    public $database;
    private $videoManager;
    
    const
            RESUMABLE_TEMP = 'uploaded/resumable-temp/';

    function __construct(Nette\Database\Context $database, VideoManager $videoManager) {
        $this->database = $database;
        $this->videoManager = $videoManager;
    }
    
    function renderPrepareVideo() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    function createComponentPrepareVideoInDB() {
        $form = new Nette\Application\UI\Form;
        
        $form->addText("year", "rok:")
                ->setType("number")
                ->setDefaultValue(date("Y"))
                ->setAttribute("min", "1950")
                ->setAttribute("max", "2050")
                ->setAttribute("step", "1")
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addSelect("month", "měsíc:")
                ->setItems(Constants::$months)
                ->setRequired()
                ->setDefaultValue(date("n"))
                ->setAttribute("class", "form-control");
        
        $form->addText("day", "den:")
                ->setType("number")
                ->setDefaultValue(date("j"))
                ->setAttribute("min", "1")
                ->setAttribute("max", "31")
                ->setAttribute("step", "1")
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addText("name_cs", "název česky:")
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addText("name_en", "název anglicky")
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addText("tags", "tagy:")
                ->setRequired()
                ->setHtmlId("tags")
                ->setAttribute("data-role", "tagsinput")
                ->setAttribute("class", "form-control");
        
        $form->addText("categories", "kategorie:")
                ->setHtmlId("categories")
                ->setAttribute("class", "form-control");
        
        $form->addTextArea("description_cs", "popis česky:")
                ->setAttribute("class", "form-control");
        
        $form->addTextArea("description_en", "popis anglicky:")
                ->setAttribute("class", "form-control");
        
        $form->addTextArea("note", "interní poznámka:")
                ->setAttribute("class", "form-control");
        
        $form->addSubmit("submit", "Připravit video")
                ->setHtmlId("submit")
                ->setAttribute("class", "btn btn-primary btn-xl");
        
        $form->onSuccess[] = $this->prepareVideoInDBSucceeded;
        
        $this->bootstrapFormRendering($form);
        
        return $form;
    }
    
    public function prepareVideoInDBSucceeded($form) {
       
        $values = $form->getValues();        
        
        $sqlDate = StringUtils::formatSQLDate($values['year'], $values['month'], $values['day']);
        unset($values['year']);
        unset($values['month']);
        unset($values['day']);
        $values['date'] = $sqlDate;
        
        $insertedId = $this->videoManager->saveVideoToDB($values);
        
        $this->flashMessage("Video uloženo v DB", "success");
        $this->flashMessage("Zbyvá nahrát soubory pro zpracování", "info");
        $this->redirect("Video:detail#files", array("id" => $insertedId));
    }
    
    public function actionUploadOriginalFileSucceeded() {
   
        $videoname = Strings::random(6,'0-9a-zA-Z');
        $videoId = Strings::webalize($_GET['id']);
        
        $files = glob(self::RESUMABLE_TEMP.'/*.*');
        
        foreach($files as $file) {
                $extension = StringUtils::getExtensionFromFileName($file);
                rename($file, VIDEOS_FOLDER.$videoId."/".$videoname.".".$extension);
                $this->videoManager->saveVideoToDB(
                        array("id" => $videoId, 
                    "original_file" => $videoname.".".$extension));
        }
        
        $this->flashMessage("Soubor byl úspěšně nahrán.", 'success');
        $this->redirect('Video:detail#files', $_GET['id']);
    }
}

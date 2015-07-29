<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;


use Nette,
 App\Constants;

/**
 * Description of UploadPresenter
 *
 * @author chaemil
 */
class UploadPresenter extends BaseSecuredPresenter {
    
    function renderUploadVideo() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    function createComponentUploadVideo() {
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
        
        $form->addButton("submit", "Nahrát")
                ->setHtmlId("submit")
                ->setAttribute("class", "btn btn-primary btn-xl");
        
        $this->bootstrapFormRendering($form);
        
        return $form;
    }
}

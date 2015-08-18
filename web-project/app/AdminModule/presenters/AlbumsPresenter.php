<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\PhotosManager;

/**
 * Description of AlbumsPresenter
 *
 * @author chaemil
 */
class AlbumsPresenter extends BaseSecuredPresenter {
    
    public $database;
    private $photosManager;

    function __construct(Nette\Database\Context $database, PhotosManager $photosManager) {
        $this->database = $database;
        $this->photosManager = $photosManager;
    }
    
    public function renderList() {
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($this->photosManager->countAlbums());
        $paginator->setItemsPerPage(30);
        $paginator->setPage(1);
        
        $this->getTemplateVariables($this->getUser()->getId());
        
        $this->template->albums = $this->photosManager
                ->getAlbumsFromDB($paginator->getLength(), $paginator->getOffset(), "id");
    }
    
    public function renderAlbumDetail($id) {
        $this->getTemplateVariables($this->getUser()->getId());
        $album = $this->photosManager->getAlbumFromDB($id);
        
        $this->template->album = $album;
    }
    
    public function renderCreateAlbum() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    public function createComponentCreateAlbumForm() {        
        $form = new Nette\Application\UI\Form;
        
        $published = array(
            '0' => 'Ne',
            '1' => 'Ano',
        );
        
        $form->addSelect("published", "zveřejneno")
                ->setItems($published)
                ->setAttribute("class", "form-control");
        
        $form->addText('name_cs', 'název česky')
                ->setRequired()
                ->setAttribute("class", "form-control");

        $form->addText('name_en', 'název anglicky')
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addText('date', 'datum')
                ->setRequired()
                ->setHtmlId("datepicker")
                ->setAttribute("class", "form-control");
        
        $form->addText('days', 'dny')
                ->setAttribute("type", "number")
                ->setAttribute("step", "1")
                ->setAttribute("class", "form-control");
        
        $form->addSubmit("submit", "uložit")
                ->setAttribute("class", "btn-lg btn-success btn-block");
        

        // call method signInFormSucceeded() on success
        $form->onSuccess[] = $this->albumInfoSucceeded;

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }
    
    public function albumInfoSucceeded($form) {
        $vals = $form->getValues();
        if(empty($vals['id'])) {
            $vals['id'] = 0;
        }
        
        $status = $this->photosManager->saveAlbumToDB($vals);
        
        if ($status) {
            $this->flashMessage("Změny úspěšně uloženy", "success");
        } else {
            $this->flashMessage("Nic nebylo změněno", "info");
        }
        
        $this->redirect('Albums:detail', $vals['id']);
    }
}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\PhotosManager,
 App\StringUtils,
 App\ImageUtils;

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
        $this->template->photos = $this->photosManager->getPhotosFromAlbum($id);
        $this->template->photosManager = $this->photosManager;
        $this['createAlbumForm']->setDefaults($album->toArray());
        $this['uploadPhotos']->setDefaults(array('album_id' => $id));
        
    }
    
    public function renderCreateAlbum() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    public function actionDeleteAlbum($id) {
        $this->photosManager->deleteAlbum($id);
        $this->flashMessage("Album smazáno", "danger");
        $this->redirect("Albums:list");
    }
    
    public function actionUpdateAlbum($id) {
        foreach($_POST as $key => $value) {
            if (strpos($key, 'cs') !== FALSE) {
                $photoId = substr($key, 0, strpos($key, '_'));
                $descCs = $value;
                $descEn = $_POST[str_replace('cs', 'en', $key)];
                $order = $_POST[$photoId.'_order'];
                $this->photosManager
                        ->updatePhotoInDB(array(PhotosManager::COLUMN_DESCRIPTION_CS => $descCs,
                            PhotosManager::COLUMN_DESCRIPTION_EN => $descEn,
                            PhotosManager::COLUMN_ID => $photoId,
                            PhotosManager::COLUMN_ORDER => $order));
            } else {
                continue;
            }
            
        }
        $this->flashMessage("Změny uloženy", "success");
        $this->redirect('Albums:albumDetail#files', $id);
    }
    
    public function actionAjaxDeletePhoto() {
        $id = Nette\Utils\Strings::webalize($_GET['id']);
        $this->photosManager->deletePhoto($id);
        exit;
    }
    
    public function createComponentUploadPhotos() {
        $form = new Nette\Application\UI\Form;
        
        $form->addHidden('album_id');
        
        $form->addMultiUpload("photos", "fotky")
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addSubmit("submit", "nahrát")
                ->setAttribute("class", "btn btn-success btn-lg");
        
        $this->bootstrapFormRendering($form);
        
        $form->onSuccess[] = $this->photosUploadSuceeded;
        
        return $form;
    }
    
    public function photosUploadSuceeded($form) {
        $vals = $form->getValues();
        
        $order = $this->photosManager->getAlbumMaxOrderNumber($vals['album_id']);
        
        foreach($vals['photos'] as $photo) {
            $extension = StringUtils::getExtensionFromFileName($photo->name);
            $filename = StringUtils::rand(8);
            $newName = ALBUMS_FOLDER.$vals['album_id']."/".$filename.".".$extension;
            rename($photo, $newName);
            $this->photosManager->savePhotoToDB(
                        array(PhotosManager::COLUMN_ALBUM_ID => $vals['album_id'],
                            PhotosManager::COLUMN_FILE => $filename.".".$extension,
                            PhotosManager::COLUMN_ORDER => $order));
            chmod($newName, 0777);
            $order++;
        }
        
        $this->flashMessage("Úspěšně nahráno", "success");
        $this->redirect("Albums:albumDetail#files", $vals['album_id']);
    }
    
    public function createComponentCreateAlbumForm() {        
        $form = new Nette\Application\UI\Form;
        
        $form->addHidden("id");
        
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
        
        $this->redirect('Albums:albumDetail', $status);
    }
}

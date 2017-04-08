<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\FrontModule;

use Model\PrivateLinksManager;
use Model\UserManager;
use Nette,
Nette\Database\Context,
Model\PhotosManager,
Model\TagsManager,
Model\CategoriesManager;

/**
 * Description of VideoPreseter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class AlbumPresenter extends BasePresenter {

    public function createComponentPrivateLinkForm() {
        $form = new Nette\Application\UI\Form;

        $form->addHidden(PrivateLinksManager::COLUMN_ITEM_HASH)
            ->setRequired()
            ->setDefaultValue('');

        $form->addText('pass', 'heslo')
            ->setRequired()
            ->setAttribute("class", "form-control");

        $form->addSubmit('send', 'Odeslat')
            ->setAttribute("class", "btn btn-success btn-block");

        // call method signInFormSucceeded() on success
        $form->onSubmit[] = $this->privateLinkSend;

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }

    public function privateLinkSend($form) {
        $vals = $form->getValues();
        $hash = $vals[PrivateLinksManager::COLUMN_ITEM_HASH];
        $pass = $vals[PrivateLinksManager::COLUMN_PASS];
        $valid = $this->privateLinksManager->validate($hash, $pass);

        if ($valid) {
            $httpResponse = $this->container->getByType('Nette\Http\Response');
            $httpResponse->setCookie($hash . '_private', $pass, '1 hour');
            $this->redirect("private", $hash);
        } else {
            $this->flashMessage("Zadali jste špatné heslo nebo odkaz již není platný.", "danger");
            $this['privateLinkForm']->setDefaults(array(PrivateLinksManager::COLUMN_ITEM_HASH => $hash));
        }
    }

    public function renderPrivate($id) {
        $hash = $id; //id only in router, actually its hash

        $this['privateLinkForm']->setDefaults(array(PrivateLinksManager::COLUMN_ITEM_HASH => $hash));

        $this->template->valid = false;

        $privateLinks = $this->privateLinksManager->getFromDBbyHash($hash);
        $privateCookie = $this->getHttpRequest()->getCookie($hash . '_private');

        if (isset($privateCookie) && $privateLinks != false) {
            foreach ($privateLinks as $privateLink) {
                if ($this->privateLinksManager->validate($privateLink[PrivateLinksManager::COLUMN_ITEM_HASH], $privateCookie)) {

                    $this->template->valid = true;
                    $this->createAlbumVariables($hash, 2);

                    break;
                }
            }
        }
    }

    public function renderView($id) {
        $this->createAlbumVariables($id);
    }

    private function createAlbumVariables($id, $published = 1) {
        if (is_numeric($id)) {
            $album = $this->photosManager->getAlbumFromDB($id, $published);
            $this->redirect("Album:view", $album['hash']);
        } else {
            $hash = $id; //id only in router, actualy its hash
            $album = $this->photosManager->getAlbumFromDBbyHash($hash, $published);
        }

        $tags = explode(",", $album['tags']);
        $tagsWithUsage = $this->tagsManager->tagsUsage($tags);
        $this->template->tags = $tagsWithUsage;

        $this->template->album = $this->photosManager
            ->createLocalizedAlbumThumbObject($this->lang, $album);

        $photos = $this->photosManager
            ->createLocalizedAlbumPhotosObject($this->lang, $album['id']);

        $this->template->photos = $photos;

        $this->template->categories = $this->categoriesManager
            ->getLocalizedCategories($this->lang);

        $this->template->albumRaw = $album;
    }

}

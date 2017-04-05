<?php
/**
 * Created by PhpStorm.
 * User: chaemil
 * Date: 25.1.17
 * Time: 14:08
 */

namespace App\AdminModule;

use App\EventLogger;
use Model\PhotosManager;
use Model\PrivateLinksManager;
use Model\VideoManager;
use Nette;

class PrivateLinksGeneratorPresenter extends BaseSecuredPresenter {

    public $database;
    private $videoManager;
    private $privateLinksManager;
    private $photosManager;

    function __construct(Nette\Database\Context $database, VideoManager $videoManager,
                        PhotosManager $photosManager,
                        PrivateLinksManager $privateLinksManager) {
        $this->database = $database;
        $this->videoManager = $videoManager;
        $this->photosManager = $photosManager;
        $this->privateLinksManager = $privateLinksManager;
    }

    public function createComponentPrivateLinkForm() {
        $form = new Nette\Application\UI\Form;

        $form->addHidden('id')
            ->setDefaultValue(0);

        $form->addHidden('item_hash', 'hash')
            ->setDefaultValue("");

        $form->addText('valid', 'přístupné do')
            ->setRequired()
            ->setHtmlId("datepicker")
            ->setAttribute("class", "form-control");

        $form->addText('pass', 'heslo')
            ->setRequired()
            ->setAttribute("class", "form-control");

        $form->addSubmit('send', 'Uložit')
            ->setAttribute("class", "btn-lg btn-success btn-block");

        // call method signInFormSucceeded() on success
        $form->onSuccess[] = [$this, 'privateLinkSucceeded'];

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }

    public function privateLinkSucceeded($form) {
        $vals = $form->getValues();

        $status = $this->privateLinksManager->saveToDB($vals);

        if ($status) {
            EventLogger::log('user '.$this->getUser()->getIdentity()
                    ->login.' created private link '.$vals->id,
                EventLogger::ACTIONS_LOG);

            $this->flashMessage("Změny úspěšně uloženy", "success");
        } else {
            $this->flashMessage("Nic nebylo změněno", "info");
        }
    }

    public function renderDefault() {
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->privateLinks = $this->privateLinksManager->getAll();
        $this->template->privateLinksManager = $this->privateLinksManager;
    }

    function renderCreate($hash) {
        $this->getTemplateVariables($this->getUser()->getId());

        $video = $this->videoManager->getVideoFromDBbyHash($hash, 2);
        $album = $this->photosManager->getAlbumFromDBbyHash($hash, 2);

        if ($video != false) {
            $this->template->video = $video;
            $this->template->privateLinks = $this->privateLinksManager
                ->getFromDBbyHash($video[VideoManager::COLUMN_HASH]);
        }

        if ($album != false) {
            $this->template->album = $album;
            $this->template->privateLinks = $this->privateLinksManager
                ->getFromDBbyHash($album[PhotosManager::COLUMN_HASH]);
        }

        $this->template->privateLinksManager = $this->privateLinksManager;

        if (isset($hash)) {
            $this['privateLinkForm']->setDefaults(array(PrivateLinksManager::COLUMN_ITEM_HASH => $hash));
        }
    }

    function actionDelete($hash, $id) {
        $this->privateLinksManager->delete($id);
        if ($hash != null) {
            $this->redirect("create", $hash);
        } else {
            $this->redirect("default");
        }
        $this->flashMessage("Smazáno", "danger");
    }

}
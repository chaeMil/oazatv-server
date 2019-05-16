<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\FrontModule;

use Model\PrivateLinksManager;
use Nette,
Nette\Database\Context,
Model\VideoManager,
Model\AnalyticsManager,
Model\SongsManager,
Model\PreachersManager,
Model\CategoriesManager;

/**
 * Description of VideoPreseter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class VideoPresenter extends BasePresenter {

    private $videoManager;
    private $analyticsManager;
    private $songsManager;
    private $preachersManager;
    private $categoriesManager;
    private $privateLinksManager;

    public function __construct(Nette\DI\Container $container,
            Context $database, VideoManager $videoManager,
            AnalyticsManager $analyticsManager, SongsManager $songsManager,
            PreachersManager $preachersManager,
            CategoriesManager $categoriesManager,
            PrivateLinksManager $privateLinksManager) {

        parent::__construct($container, $database);

        $this->videoManager = $videoManager;
        $this->analyticsManager = $analyticsManager;
        $this->songsManager = $songsManager;
        $this->preachersManager = $preachersManager;
        $this->categoriesManager = $categoriesManager;
        $this->privateLinksManager = $privateLinksManager;
    }
    
    private function countView($id, $hash) {
        $httpResponse = $this->container->getByType('Nette\Http\Response');
        $watchedCookie = $this->getHttpRequest()->getCookie($hash);
        if (!isset($watchedCookie)) {
            $this->videoManager->countView($id);
            $this->analyticsManager->countVideoView($id, AnalyticsManager::WEB);
            $this->analyticsManager->addVideoToPopular($id);
            $httpResponse->setCookie($hash, 'watched', '1 hour');
        }
    }

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
        $form->onSubmit[] = [$this, 'privateLinkSend'];

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
                    $this->createVideoVariables(false, $hash, 2);

                    break;
                }
            }
        }
    }

    public function renderWatch($id, $searched) {
        $hash = $id; //id only in router, actually its hash
        $this->createVideoVariables($searched, $hash);
    }

    private function createVideoVariables($searched, $hash, $published = 1) {
        $video = $this->videoManager->getVideoFromDBbyHash($hash, $published);
        if ($searched) {
            $this->analyticsManager->countVideoSearchClick($video['id'], AnalyticsManager::WEB);
        }

        $tags = explode(",", $video['tags']);
        $tagsWithSongs = $this->songsManager->parseTagsAndReplaceKnownSongs($tags);
        $this->template->tags = $tagsWithSongs;

        $preachers = array();
        foreach ($tags as $tag) {
            $findPreacher = $this->preachersManager->getPreacherFromDBByTag($tag);
            if ($findPreacher) {
                $preacher = $this->preachersManager->createLocalizedObject($this->lang, $findPreacher);
                $preachers[] = $preacher;
            }
        }

        $this->template->preachers = array_map("unserialize", array_unique(array_map("serialize", $preachers)));

        $this->countView($video->id, $hash);

        $this->template->categoriesManager = $this->categoriesManager;
        $this->template->categories = $this->categoriesManager
            ->getLocalizedCategories($this->lang);

        $this->template->serverUrl = "http://$_SERVER[HTTP_HOST]";
        $this->template->videoRaw = $video;
        $this->template->video = $this->videoManager
            ->createLocalizedVideoObject($this->lang, $video);

        $this->template->similarVideos = $this->videoManager->findSimilarVideos($video, $this->lang, 12);
    }

}

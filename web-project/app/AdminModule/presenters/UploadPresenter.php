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
    Nette\Utils\Strings,
    App\EventLogger,
    Model\TagsManager,
    Model\CategoriesManager;
use Nette\DI\Container;

/**
 * Description of UploadPresenter
 *
 * @author chaemil
 */
class UploadPresenter extends BaseSecuredPresenter
{

    public $database;
    private $videoManager;
    private $tagsManager;
    private $categoriesManager;
    public $container;

    const
        RESUMABLE_TEMP = 'uploaded/resumable-temp/';

    function __construct(Nette\Database\Context $database, VideoManager $videoManager,
                         TagsManager $tagsManager, CategoriesManager $categoriesManager, Container $container)
    {
        $this->database = $database;
        $this->videoManager = $videoManager;
        $this->tagsManager = $tagsManager;
        $this->categoriesManager = $categoriesManager;
        $this->container = $container;
    }

    function renderPrepareVideo()
    {
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->tagsArray = $this->tagsManager->tagCloud();
        $this->template->categories = $this->categoriesManager->getCategoriesFromDB();
    }

    function createComponentPrepareVideoInDB()
    {
        $form = new Nette\Application\UI\Form;

        $form->addText('date', 'datum')
            ->setRequired()
            ->setHtmlId("datepicker")
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

        $form->onSuccess[] = [$this, 'prepareVideoInDBSucceeded'];

        $this->bootstrapFormRendering($form);

        return $form;
    }

    public function prepareVideoInDBSucceeded($form)
    {

        $values = $form->getValues();

        $insertedId = $this->videoManager->saveVideoToDB($values);

        EventLogger::log('user ' . $this->getUser()->getIdentity()->login . ' added new video ' . $insertedId,
            EventLogger::ACTIONS_LOG);

        $this->flashMessage("Video uloženo v DB", "success");
        $this->flashMessage("Zbyvá nahrát soubory pro zpracování", "info");
        $this->redirect("Video:detail#files", array("id" => $insertedId));
    }

    public function actionUploadOriginalFileSucceeded()
    {

        $httpRequest = $this->container->getByType('Nette\Http\Request');

        $this->getTemplateVariables($this->getUser()->getId());

        $videoname = StringUtils::rand(6);
        $videoId = Strings::webalize($httpRequest->getQuery('id'));

        $files = glob(self::RESUMABLE_TEMP . '/*.*');

        foreach ($files as $file) {
            $extension = StringUtils::getExtensionFromFileName($file);
            rename($file, VIDEOS_FOLDER . $videoId . "/" . $videoname . "." . $extension);
            $this->videoManager->saveVideoToDB(
                array("id" => $videoId,
                    "original_file" => $videoname . "." . $extension));
        }

        EventLogger::log('user ' . $this->getUser()->getIdentity()->login . ' uploaded new original file to video ' . $videoId,
            EventLogger::ACTIONS_LOG);

        $this->flashMessage("Soubor byl úspěšně nahrán.", 'success');
        $this->redirect('Video:detail#files', $videoId);
    }
}

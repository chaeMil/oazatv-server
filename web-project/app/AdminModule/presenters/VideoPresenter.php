<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use App\StringUtils;
use Model\PrivateLinksManager;
use Nette,
 Model\VideoManager,
 Model\VideoConvertQueueManager,
 App\EventLogger,
 Model\TagsManager,
 Model\CategoriesManager,
 Model\ConversionProfilesManager,
 App\FileUtils,
 ADT\Datagrid\Datagrid;
use Nette\Application\Responses\JsonResponse;

/**
 * Description of VideoPresenter
 *
 * @author chaemil
 */
class VideoPresenter extends BaseSecuredPresenter {

    public $database;
    private $videoManager;
    private $convertQueueManager;
    private $tagsManager;
    private $categoriesManager;
    private $conversionProfilesManager;
    private $privateLinksManager;

    function __construct(Nette\Database\Context $database, VideoManager $videoManager,
     VideoConvertQueueManager $convertQueueManager, TagsManager $tagsManager,
            CategoriesManager $categoriesManager,
            ConversionProfilesManager $conversionProfilesManager,
            PrivateLinksManager $privateLinksManager) {
        $this->database = $database;
        $this->videoManager = $videoManager;
        $this->convertQueueManager = $convertQueueManager;
        $this->tagsManager = $tagsManager;
        $this->categoriesManager = $categoriesManager;
        $this->conversionProfilesManager = $conversionProfilesManager;
        $this->privateLinksManager = $privateLinksManager;
    }

    public function renderList() {
        $this->getTemplateVariables($this->getUser()->getId());

        $this->template->videos = $this->videoManager
                ->getVideosFromDB(0, 9999, 2, VideoManager::COLUMN_DATE." DESC");

        $this->template->videosFolder = VIDEOS_FOLDER;
        $this->template->conversionProfiles = $this->conversionProfilesManager->getProfilesFromDB();
    }

    public function getDataSource($filter, $order, Nette\Utils\Paginator $paginator = NULL){
        $selection = $this->prepareDataSource($filter, $order);
        if ($paginator) {
            $selection->limit($paginator->getItemsPerPage(), $paginator->getOffset());
        }
        return $selection;
    }

    public function getDataSourceSum($filter, $order) {
        return $this->prepareDataSource($filter, $order)->count('*');
    }

    private function prepareDataSource($filter, $order) {
        $filters = array();
        foreach ($filter as $k => $v) {
            if ($k == 'id' || is_array($v)) {
                $filters[$k] = $v;
            } else {
                $filters[$k. ' LIKE ?'] = "%$v%";
            }
        }

        $selection = $this->database->table(VideoManager::TABLE_NAME)->where($filters);
        if ($order[0]) {
            $selection->order(implode(' ', $order));
        }

        return $selection;
    }


    public function createComponentVideosGrid() {
        $grid = new Datagrid;
        $grid->setRowPrimaryKey('id');
        $grid->addColumn('hash', '#');
        $grid->addColumn('id', 'DB ID')
            ->enableSort(Datagrid::ORDER_ASC)
            ->enableSort(Datagrid::ORDER_DESC);
        $grid->addColumn('published', 'veřejné')
            ->enableSort(Datagrid::ORDER_DESC)
            ->enableSort(Datagrid::ORDER_ASC);
        $grid->addColumn('thumb_color', 'dominantní barva');
        $grid->addColumn('thumb_file', ' ');
        $grid->addColumn('name_cs', 'název česky')
            ->enableSort(Datagrid::ORDER_DESC)
            ->enableSort(Datagrid::ORDER_ASC);
        $grid->addColumn('name_en', 'název anglicky')
            ->enableSort(Datagrid::ORDER_DESC)
            ->enableSort(Datagrid::ORDER_ASC);
        $grid->addColumn('date', 'datum')
            ->enableSort(Datagrid::ORDER_ASC)
            ->enableSort(Datagrid::ORDER_DESC);
        $grid->addColumn('categories', 'kategorie');
        $grid->addColumn('note', 'poznámka');

        $grid->setFilterFormFactory(function() {
            $form = new Nette\Forms\Container;
            $form->addText('hash')
                ->setAttribute("class", "form-control");
            $form->addText('published')
                ->setAttribute("placeholder", "0 / 1")
                ->setAttribute("class", "form-control");
            $form->addText('name_cs')
                ->setAttribute("class", "form-control");
            $form->addText('name_en')
                ->setAttribute("class", "form-control");
            $form->addText('date')
                ->setAttribute("placeholder", "RRRR-MM-DD")
                ->setAttribute("class", "form-control");
            $form->addText('categories')
                ->setAttribute("class", "form-control");
            $form->addText('note')
                ->setAttribute("class", "form-control");

            // these buttons are not compulsory
            $form->addSubmit('filter', 'Filtrovat')->getControlPrototype()->class = 'btn btn-primary';
            $form->addSubmit('cancel', 'Zrušit')->getControlPrototype()->class = 'btn';

            return $form;
        });

        $grid->addCellsTemplate(__DIR__.'/../templates/Video/listCell.latte');

        $grid->setPagination(30, [$this, 'getDataSourceSum']);

        $grid->setDataSourceCallback([$this, 'getDataSource']);

        return $grid;
    }

    public function renderDetail($id) {
        $this->getTemplateVariables($this->getUser()->getId());
        $video = $this->videoManager->getVideoFromDB($id, 2);

        $this->template->tagsArray = $this->tagsManager->tagCloud();
        $this->template->categories = $this->categoriesManager->getCategoriesFromDB();
        $this->template->conversionProfiles = $this->conversionProfilesManager->getProfilesFromDB();

        if (!isset($video['id'])) {
            $this->error('Požadované video neexistuje!');
        }

        $this->template->video = $video;
        if (!file_exists(VIDEOS_FOLDER.$id.'/'.$video['mp4_file'])) {
            $this->template->mp4FileMissing = true;
        } else {
            $mp4FileSize = FileUtils::humanReadableFileSize(VIDEOS_FOLDER.$id.'/'.$video['mp4_file']);
            $this->template->mp4FileSize = $mp4FileSize;
        }

        if (!file_exists(VIDEOS_FOLDER.$id.'/'.$video['mp3_file'])) {
            $this->template->mp3FileMissing = true;
        } else {
            $mp3FileSize = FileUtils::humanReadableFileSize(VIDEOS_FOLDER.$id.'/'.$video['mp3_file']);
            $this->template->mp3FileSize = $mp3FileSize;
        }

        if (!file_exists(VIDEOS_FOLDER.$id.'/'.$video['webm_file'])) {
            $this->template->webmFileMissing = true;
        } else {
            $webmFileSize = FileUtils::humanReadableFileSize(VIDEOS_FOLDER.$id.'/'.$video['webm_file']);
            $this->template->webmFileSize = $webmFileSize;
        }

        if (!file_exists(VIDEOS_FOLDER.$id.'/'.$video['mp4_file_lowres'])) {
            $this->template->mp4FileLowresMissing = true;
        } else {
            $mp4FileLowresSize = FileUtils::humanReadableFileSize(VIDEOS_FOLDER.$id.'/'.$video['mp4_file_lowres']);
            $this->template->mp4FileLowresSize = $mp4FileLowresSize;
        }

        if (!file_exists(VIDEOS_FOLDER.$id.'/'.$video['subtitles_file'])) {
            $this->template->subtitlesFileMissing = true;
        } else {
            $subtitlesFileSize = FileUtils::humanReadableFileSize(VIDEOS_FOLDER.$id.'/'.$video['subtitles_file']);
            $this->template->$subtitlesFileSize = $subtitlesFileSize;
        }

        if (file_exists(VIDEOS_FOLDER.$id.'/logs/')) {
            if (!FileUtils::isDirEmpty(VIDEOS_FOLDER . $id . '/logs/')) {
                $logFiles = scandir(VIDEOS_FOLDER . $id . '/logs/');
                unset($logFiles[0]);
                unset($logFiles[1]);
                $this->template->logFiles = $logFiles;
            }
        }

        $this->template->originalFileInfo = $this->videoManager->getOriginalFileInfo($video->id);
        $this->template->originalFileSize = FileUtils::humanReadableFileSize(VIDEOS_FOLDER.$id.'/'.$video[VideoManager::COLUMN_ORIGINAL_FILE]);
        $this->template->originalFileDate = $this->videoManager->getOriginalFileDate($video->id);
        $this->template->originalFile = VideoManager::COLUMN_ORIGINAL_FILE;
        $this->template->mp4File = VideoManager::COLUMN_MP4_FILE;
        $this->template->mp4FileLowres = VideoManager::COLUMN_MP4_FILE_LOWRES;
        $this->template->mp3File = VideoManager::COLUMN_MP3_FILE;
        $this->template->webmFile = VideoManager::COLUMN_WEBM_FILE;
        $this->template->thumbFile = VideoManager::COLUMN_THUMB_FILE;
        $this->template->subtitlesFile = VideoManager::COLUMN_SUBTITLES_FILE;
        $this->template->thumbs = $this->videoManager->getThumbnails($id);
        $this->template->convertQueueManager = $this->convertQueueManager;
        $this->template->privateLinks = $this->privateLinksManager->getFromDBbyHash($video[VideoManager::COLUMN_HASH]);
        $this['videoBasicInfoForm']->setDefaults($video->toArray());

    }

    public function handleShowLogFile($videoId, $file) {
        $logFile = file_get_contents(VIDEOS_FOLDER . $videoId . '/logs/' . $file);
        $this->sendResponse(new JsonResponse(['file' => $logFile]));
        $this->redrawControl("logFile");
    }

    public function createComponentVideoBasicInfoForm() {
        $form = new Nette\Application\UI\Form;

        $form->addHidden('id')
                ->setRequired();

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

        $form->addText('tags', 'tagy')
                ->setRequired()
                ->setHtmlId("tags")
                ->setAttribute("class", "form-control")
                ->setAttribute("data-role", "tagsinput");

        $form->addText("categories", "kategorie:")
                ->setHtmlId("categories")
                ->setAttribute("data-role", "tagsinput")
                ->setAttribute("class", "form-control");

        $form->addTextArea('description_cs', 'popis česky')
                ->setAttribute("class", "form-control ckeditor");

        $form->addTextArea('description_en', 'popis anglicky')
                ->setAttribute("class", "form-control ckeditor");

        $form->addTextArea("note", "interní poznámka")
                ->setAttribute("class", "form-control");

        $form->addSubmit('send', 'Uložit')
                ->setAttribute("class", "btn-lg btn-success btn-block");


        // call method signInFormSucceeded() on success
        $form->onSuccess[] = [$this, 'videoBasicInfoSucceeded'];

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }

    public function videoBasicInfoSucceeded($form) {
        $vals = $form->getValues();

        $vals[VideoManager::COLUMN_DESCRIPTION_CS] = StringUtils::removeStyleTag($vals[VideoManager::COLUMN_DESCRIPTION_CS]);
        $vals[VideoManager::COLUMN_DESCRIPTION_EN] = StringUtils::removeStyleTag($vals[VideoManager::COLUMN_DESCRIPTION_EN]);

        $status = $this->videoManager->saveVideoToDB($vals);

        if ($status) {
            EventLogger::log('user '.$this->getUser()->getIdentity()
                    ->login.' updated video '.$vals->id,
                EventLogger::ACTIONS_LOG);

            $this->flashMessage("Změny úspěšně uloženy", "success");
        } else {
            $this->flashMessage("Nic nebylo změněno", "info");
        }
    }

    public function actionUseOriginalFileAs($id, $target) {
        $this->videoManager->useOriginalFileAs($id, $target);

        EventLogger::log('user '.$this->getUser()->getIdentity()
                ->login.' used original file as '.$target.' in video '.$id,
                EventLogger::ACTIONS_LOG);

        $this->flashMessage("Originání soubor použit jako: ".$target, "success");
        $this->redirect("Video:Detail#files", $id);
    }

    public function actionDeleteVideoFile($id, $file) {
        $this->videoManager->deleteVideoFile($id, $file);
        if ($file == VideoManager::COLUMN_THUMB_FILE) {
            $this->videoManager->deleteThumbnails($id);
        }
        EventLogger::log('user '.$this->getUser()->getIdentity()
                ->login.' deleted '.$file.' from video '.$id,
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Soubor ".$file." byl smazán", "danger");
        $this->redirect("Video:Detail#files", $id);
    }

    public function actionConvertFile($id, $input, $target, $profile) {
        $this->videoManager->addVideoToConvertQueue($id, $input, $target, $profile);
        EventLogger::log('user '.$this->getUser()
                ->getIdentity()->login.' added '.$input.' from video '.$id.
                    ' to conversion queue, target format is '.$target. ", profile: ".$profile,
                EventLogger::CONVERSION_LOG);
        $this->flashMessage("Soubor byl přidán do fronty", "info");
        $this->redirect("Video:Detail#files", $id);
    }

    public function actionDeleteVideo($id) {
        $this->videoManager->deleteVideo($id);
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' deleted video '.$id,
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Video bylo smazáno!", "danger");
        $this->redirect("Video:List");
    }

    public function actionSaveThumbDominantColor($id) {
        $this->videoManager->saveVideoThumbDominantColor($id);
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' saved video thumb dominant color: '.$id,
            EventLogger::ACTIONS_LOG);
        $this->flashMessage("Uloženo!", "success");
        $this->redirect("Video:Detail#files", $id);
    }
}

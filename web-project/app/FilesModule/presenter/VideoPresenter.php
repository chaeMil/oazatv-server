<?php
namespace App\FilesModule;

use Drahak\Restful\IResource;
use Drahak\Restful\Application\UI\ResourcePresenter;
use Model\ArchiveManager;
use Model\VideoManager;
use Nette\Application\UI\Presenter;
use Nette\Utils\Paginator;

class VideoPresenter extends Presenter {

    private $videoManager;

    public function __construct(VideoManager $videoManager) {
        $this->videoManager = $videoManager;
    }

    public function actionGetVideoFile($hash, $format) {
        $video = $this->videoManager->getVideoFromDBbyHash($hash);
        $file = VIDEOS_FOLDER . $video[VideoManager::COLUMN_ID] . "/" . $video[$format . "_file"];
        if (file_exists($file)) {
            readfile($file);
        }
    }

}
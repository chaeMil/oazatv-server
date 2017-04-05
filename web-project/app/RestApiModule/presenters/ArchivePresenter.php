<?php
namespace App\RestApiModule;

use Drahak\Restful\IResource;
use Drahak\Restful\Application\UI\ResourcePresenter;
use Model\ArchiveManager;
use Nette\Utils\Paginator;

class ArchivePresenter extends ResourcePresenter {

    private $archiveManager;

    public function __construct(ArchiveManager $archiveManager) {
        $this->archiveManager = $archiveManager;
    }


    public function actionPage($page, $lang) {
        $paginator = new Paginator;
        $paginator->setItemCount($this->archiveManager->countArchive());
        $paginator->setItemsPerPage(16);
        $paginator->setPage($page);

        $archive = $this->archiveManager->getVideosAndPhotoAlbumsFromDB(
            $paginator->getOffset(),
            16, $lang);



        $this->resource->archive = $archive;
        $this->sendResource(IResource::JSON);
    }

}
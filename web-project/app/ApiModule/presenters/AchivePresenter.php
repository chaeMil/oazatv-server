<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette;

/**
 * Description of MainPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class ArchivePresenter extends BasePresenter {
   
    public function renderDefault($id = 1) {
        $page = $id;
        
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($this->archiveManager->countArchive());
        $paginator->setItemsPerPage(16);
        $paginator->setPage($page);

        $db = $this->archiveManager
                ->getVideoAndPhotoAlbumsFromDBtoAPI(
                        $paginator->getOffset(), 
                        $paginator->getItemsPerPage());
        
        $archiveArray = array();
        
        foreach($db as $item) {
            
            $archiveArray[] = $this->createArchiveItem($item);
 
        }
        
        $jsonArray['archive'] = $archiveArray;
        
        $this->sendJson($jsonArray);
    }
}

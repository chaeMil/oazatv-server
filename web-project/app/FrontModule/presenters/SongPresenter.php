<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\SongsManager;

/**
 * Description of VideoPreseter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class SongPresenter extends BasePresenter {

    private $songsManager;

    public function __construct(Nette\DI\Container $container,
            Context $database, SongsManager $songsManager) {
        parent::__construct($container, $database);
        $this->songsManager =  $songsManager;
    }

    public function renderView($id) {
        $tag = $id; //id only in router, actualy its tag
        $song = $this->songsManager->getSongFromDBByTag($tag);
        $this->template->song = $song;
   }

}

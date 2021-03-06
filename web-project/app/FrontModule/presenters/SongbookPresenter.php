<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\SongsManager,
Model\VideoManager,
Model\CategoriesManager;

/**
 * Description of VideoPreseter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class SongbookPresenter extends BasePresenter {

    private $songsManager;
    private $videoManager;
    private $categoriesManager;

    public function __construct(Nette\DI\Container $container,
            Context $database, SongsManager $songsManager, 
            VideoManager $videoManager, 
            CategoriesManager $categoriesManager) {
        parent::__construct($container, $database);
        $this->songsManager = $songsManager;
        $this->videoManager = $videoManager;
        $this->categoriesManager = $categoriesManager;
    }

    public function renderView($id) {
        $tag = $id; //id only in router, actualy its tag
        $song = $this->songsManager->getSongFromDBByTag($tag)->toArray();
        
        $song['body'] = str_replace(array("[", "]"), array("<chord>[","]</chord>"), 
                htmlspecialchars_decode($song['body']));
        
        $song['body'] = \App\StringUtils::removeStyleTag($song['body']);
        
        $this->template->song = $song;
        $videos = $this->videoManager->getVideosFromDBbyTag($song['tag']);
        $videosArray = array();
        
        foreach($videos as $video) {
            $videosArray[] = $this->videoManager->createLocalizedVideoObject($this->lang, $video);
        }
        
        
        $this->template->categoriesManager = $this->categoriesManager;
        $this->template->categories = $this->categoriesManager
                ->getLocalizedCategories($this->lang);
        $this->template->videos = $videosArray;
    }
    
    public function renderAll() {
        $this->template->categoriesManager = $this->categoriesManager;
        $this->template->categories = $this->categoriesManager
                ->getLocalizedCategories($this->lang);
        $songs = $this->songsManager->getSongsFromDB();
        
        usort($songs, function($a, $b) {
            if ($a['tag'] == $b['tag']) {
                return 0;
            }
            return strnatcmp($a['tag'], $b['tag']);
        }); 
        
        $this->template->songs = $songs;
    }
    
    public function renderChord($id) {
        $this->template->chord = $id;
        $this->template->categoriesManager = $this->categoriesManager;
        $this->template->categories = $this->categoriesManager
                ->getLocalizedCategories($this->lang);  
    }
    
    

}

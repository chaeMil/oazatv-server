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
class SongsPresenter extends BasePresenter {
   
    public function renderDefault() {
        $songs = $this->songsManager->getSongsFromDB();

        usort($songs, function($a, $b) {
            if ($a['tag'] == $b['tag']) {
                return 0;
            }
            return strnatcmp($a['tag'], $b['tag']);
        });

        $groupedSongs = array();
        foreach($songs as $song) {
            $tag = preg_replace('/\d/', '', $song['tag']);;

            if(!isset($groupedSongs[$tag])) {
                $groupedSongs[$tag] = array();
            }

            $song = $song->toArray();
            unset($song['body']);

            $groupedSongs[$tag][] = $song;
        }

        $this->enableCORS();
        $this->sendJson($groupedSongs);
    }

    public function renderView($id) {
        $song = $this->songsManager->getSongFromDB($id);
        $song = $song->toArray();

        $this->enableCORS();
        $this->sendJson($song);
    }
}

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
        $group = array();
        $lastSong = NULL;
        $lastSongTag = NULL;

        foreach($songs as $song) {
            $tag = preg_replace('/\d/', '', $song['tag']);;

            $song = $song->toArray();
            unset($song['body']);

            if ($lastSong = NULL || $lastSongTag == $tag) {
                $group['tag'] = $tag;
                $group['songs'][] = $song;
            } else {

                if (!empty($group)) {
                    $groupedSongs[] = $group;
                }

                $group = array();
                $group['tag'] = $tag;
                $group['songs'][] = $song;
            }

            $lastSong = $song;
            $lastSongTag = preg_replace('/\d/', '', $lastSong['tag']);;
        }

        if (!empty($group)) {
            $groupedSongs[] = $group;
        }

        $jsonResponse = array();
        $jsonResponse['songs'] = $groupedSongs;

        $this->enableCORS();
        $this->sendJson($jsonResponse);
    }

    public function renderView($id) {
        $song = $this->songsManager->getSongFromDB($id);
        $song = $song->toArray();

        $song['body_decoded'] = html_entity_decode(html_entity_decode($song['body']));

        $this->enableCORS();
        $this->sendJson($song);
    }
}

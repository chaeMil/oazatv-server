<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CategoriesManager
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
namespace Model;

use Nette,
Model\TagsManager;
/**
 * Description of VideoManager
 *
 * @author chaemil
 */
class SongsManager extends BaseModel {

    const
            TABLE_NAME = 'db_songbook',
            COLUMN_ID = 'id',
            COLUMN_TAG = 'tag',
            COLUMN_NAME = 'name',
            COLUMN_AUTHOR = 'author',
            COLUMN_BODY = 'body';

    /** @var Nette\Database\Context */
    public static $database;
    private $tagsManager;

    public function __construct(Nette\Database\Context $database,
    TagsManager $tagsManager) {
        self::$database = $database;
        $this->tagsManager = $tagsManager;
    }

    private function checkIfSongExists($id) {
        return self::$database->table(self::TABLE_NAME)
                ->where(self::COLUMN_ID, $id)->count();
    }

    public function saveSongToDB($values) {

        if(isset($values['id'])) {
            $id = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $id = 0;
        }
        
        $values['body'] = \App\StringUtils::removeStyleTag($values['body']);

        if ($id != 0 && $this->checkIfSongExists($id) > 0) {
            $category = self::$database->table(self::TABLE_NAME)->get($id);
            $sql = $category->update($values);
            return $sql;
        } else {
            $sql = self::$database->table(self::TABLE_NAME)->insert($values);
        }

        return $sql->id;

    }


    public function getSongFromDB($id) {
        return self::$database->table(self::TABLE_NAME)
                ->select("*")
                ->where(array(self::COLUMN_ID => $id))
                ->fetch();
    }

    public function getSongsFromDB() {
        return self::$database->table(self::TABLE_NAME)
            ->select('*')
            ->order(self::COLUMN_TAG." COLLATE utf8_czech_ci")
            ->fetchAll();

    }
    
    public function getSongFromDBByTag($tag) {
        return self::$database->table(self::TABLE_NAME)
                ->select('*')
                ->where(array(self::COLUMN_TAG => $tag))
                ->fetch();
    }

    public function deleteSong($id) {
        $video = $this->getSongFromDB($id);
        $video->delete();
    }

    public function parseTagsAndReplaceKnownSongs(array $tags) {

        $knownSongs = $this->getSongsFromDB();
        $outputTags = array();

        foreach($tags as $tagIndex => $tag) {
            $newTag = array();
            $newTag['tag'] = '#'.trim($tag);
            $newTag['usage'] = $this->tagsManager->tagUsage($tag);
            $newTag['type'] = 'tag';
            foreach($knownSongs as $song) {
                if ($newTag['tag'] == '#'.trim($song['tag'])) {
                    $newTag['tag'] = '♫ ' . $song['name'] . ' ♫';
                    $newTag['type'] = 'song';
                    $newTag['song'] = $song['tag'];
                }
            }
            $outputTags[] = $newTag;
        }

        return $outputTags;
    }

}

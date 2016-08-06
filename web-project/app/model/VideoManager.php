<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 App\StringUtils,
 App\FileUtils,
 ColorThief\ColorThief;
use Utils\ColorUtils;

/**
 * Description of VideoManager
 *
 * @author chaemil
 */
class VideoManager extends BaseModel {

    const
            TABLE_NAME = 'db_video_files',
            COLUMN_ID = 'id',
            COLUMN_HASH = 'hash',
            COLUMN_PUBLISHED = 'published',
            COLUMN_ORIGINAL_FILE = 'original_file',
            COLUMN_MP4_FILE = 'mp4_file',
            COLUMN_MP4_FILE_LOWRES = 'mp4_file_lowres',
            COLUMN_WEBM_FILE = 'webm_file',
            COLUMN_MP3_FILE = 'mp3_file',
            COLUMN_THUMB_FILE = 'thumb_file',
            COLUMN_THUMB_FILE_LOWRES = 'thumb_file_lowres',
            COLUMN_SUBTITLES_FILE = 'subtitles_file',
            COLUMN_THUMB_COLOR = 'thumb_color',
            COLUMN_METADATA_DURATION_IN_SECONDS = "metadata_duration_in_seconds",
            COLUMN_DATE = 'date',
            COLUMN_NAME_CS = 'name_cs',
            COLUMN_NAME_EN = 'name_en',
            COLUMN_TAGS = 'tags',
            COLUMN_VIEWS = 'views',
            COLUMN_CATEGORIES = 'categories',
            COLUMN_DESCRIPTION_CS = 'description_cs',
            COLUMN_DESCRIPTION_EN = 'description_en',
            COLUMN_NOTE = 'note',
            THUMB_1024 = 1024,
            THUMB_512 = 512,
            THUMB_256 = 256,
            THUMB_128 = 128,
            API_METADATA = "metadata";

    /** @var Nette\Database\Context */
    public static $database;
    public static $queueManager;

    public function __construct(Nette\Database\Context $database, 
            VideoConvertQueueManager $queueManager) {
        self::$database = $database;
        self::$queueManager = $queueManager;
    }

    private function checkIfVideoExists($id) {
        return self::$database->table(self::TABLE_NAME)
                ->where(self::COLUMN_ID, $id)->count();
    }

    public function saveVideoToDB($values) {

        if(isset($values['id'])) {
            $videoId = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $videoId = 0;
        }

        if ($videoId != 0 && $this->checkIfVideoExists($videoId) > 0) {
            $video = self::$database->table(self::TABLE_NAME)->get($videoId);
            $sql = $video->update($values);
            return $sql;
        } else {
            $values['hash'] = StringUtils::rand(8);
            $sql = self::$database->table(self::TABLE_NAME)->insert($values);
            $newVideoDir = VIDEOS_FOLDER.$sql->id."/";
            $newVideoThumbsDir = $newVideoDir."thumbs/";
            $vewVideoLogsDir = $newVideoDir."logs/";
            mkdir($newVideoDir);
            mkdir($newVideoThumbsDir);
            mkdir($vewVideoLogsDir);
            chmod($newVideoDir, 0777);
            chmod($newVideoThumbsDir, 0777);
            chmod($vewVideoLogsDir, 0777);
        }

        return $sql->id;

    }
    
    public function getLatestVideoFromDB($published = 1) {
        if ($published != 2) {
            return self::$database->table(self::TABLE_NAME)
                    ->select("*")
                    ->limit(1, 0)
                    ->order(self::COLUMN_DATE." DESC". ", ".self::COLUMN_ID." DESC")
                    ->where(array(self::COLUMN_PUBLISHED => $published))
                    ->fetch();
        } else {
            return self::$database->table(self::TABLE_NAME)
                    ->select("*")
                    ->limit(1, 0)
                    ->order(self::COLUMN_DATE." DESC")
                    ->fetch();
        }
    }

    public function getVideoFromDB($id, $published = 1) {
        if ($published != 2) {
            $video = self::$database->table(self::TABLE_NAME)
                    ->select("*")
                    ->where(array(self::COLUMN_ID => $id,
                        self::COLUMN_PUBLISHED => $published))
                    ->fetch();
        } else {
            $video = self::$database->table(self::TABLE_NAME)
                    ->select("*")
                    ->where(array(self::COLUMN_ID => $id))
                    ->fetch();
        }
		
		$result = $video;
        return $result;
    }
  
    public function getVideoFileMetadata($file = "", $duration_in_seconds = 0) {
        if ($file != "") {
            $metadata = array();

            $time = exec(PATH_TO_FFMPEG . " -i $file 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//");
            $duration = explode(":", $time);
            if (isset($duration[0]) && isset($duration[1]) && isset($duration[2])) {
                $duration_in_seconds = $duration[0] * 3600 + $duration[1] * 60 + round($duration[2]);
            }
        }

        $metadata['duration_in_seconds'] = $duration_in_seconds;
        $duration_string = gmdate("H:i:s", $duration_in_seconds);
        if (StringUtils::startsWith($duration_string, "00:")) {
            $duration_string = str_replace("00:", "", $duration_string);
        }
        $metadata['duration_string'] = $duration_string;
		
		return $metadata;   
	}
    
    public function getVideoFromDBtoAPI($id) {
        
        $video = self::$database->table(self::TABLE_NAME)
                ->select('id')
                ->where(array(self::COLUMN_PUBLISHED => 1, self::COLUMN_ID => $id))
                ->fetch();
        
        $arrayItemFromDB = $this->getVideoFromDB($video['id'])->toArray();
        $arrayItemFromDB['type'] = 'video';
        return $arrayItemFromDB;
    }

    public function getVideoFromDBbyHash($hash, $published = 1) {

        if ($published != 2) {
            return self::$database->table(self::TABLE_NAME)
                    ->select("*")
                    ->where(array(self::COLUMN_HASH => $hash,
                        self::COLUMN_PUBLISHED => $published))
                    ->fetch();
        } else {
            return self::$database->table(self::TABLE_NAME)
                    ->select("*")
                    ->where(array(self::COLUMN_HASH => $hash))
                    ->fetch();
        }
    }
    
    public function getVideoFromDBbyTag($tag, $published = 1) {

        if ($published != 2) {
            return self::$database
                    ->query("select * from db_video_files where tags like'%"
                            .str_replace(array(' ', '.'), '', $tag)
                            ."%' and published = 1 order by rand() limit 1")
                    ->fetch();
        } else {
            return self::$database->table(self::TABLE_NAME)
                    ->query("select * from db_video_files where tags like'%"
                            .str_replace(array(' ', '.'), '', $tag)
                            ."%' order by rand() limit 1")
                    ->fetch();
        }
    }
    
    public function getVideosFromDBbyCategory($category, $from, $count, 
            $published = 1, $order = "date DESC") {

        if($published != 2) {
            return self::$database->table(self::TABLE_NAME)
                ->select('*')
                ->where(array(self::COLUMN_PUBLISHED => $published,
                    self::COLUMN_CATEGORIES." LIKE '%".$category."%'"))
                ->limit($count, $from)
                ->order($order);
        } else {
            return self::$database->table(self::TABLE_NAME)
                ->select('*')
                ->limit($count, $from)
                ->order($order);
        }
    }

    public function countVideos($published = 1) {

        if ($published != 2) {
            return self::$database->table(self::TABLE_NAME)
                    ->where(self::COLUMN_PUBLISHED, $published)->count("*");
        } else {
            return self::$database->table(self::TABLE_NAME)->count("*");
        }


    }

    public function getVideosFromDB($from, $count, $published = 1, $order = "date DESC, id DESC") {

        if($published != 2) {
            return self::$database->table(self::TABLE_NAME)
                ->select('*')
                ->where(array(self::COLUMN_PUBLISHED => $published))
                ->limit($count, $from)
                ->order($order);
        } else {
            return self::$database->table(self::TABLE_NAME)
                ->select('*')
                ->limit($count, $from)
                ->order($order);
        }

    }
    
    public function getVideosFromDBtoAPI($from, $count, $order = "date DESC, id DESC") {
        
        $videos = self::$database->table(self::TABLE_NAME)
                ->select('*')
                ->where(array(self::COLUMN_PUBLISHED => 1))
                ->limit($count, $from)
                ->order($order);
        
        $outputArray = array();
        
        foreach($videos as $video) {
            $arrayItemFromDB = $this->getVideoFromDB($video['id'])->toArray();
            $arrayItemFromDB['type'] = 'video';
            $outputArray[] = $arrayItemFromDB;
        }
        
        return $outputArray;
    }


    public function getVideosFromDBbyTag($tag, $limit = 10, $published = 1) {
        return self::$database->table(self::TABLE_NAME)
                ->select('*')
                ->where(array(self::COLUMN_TAGS." LIKE '%".$tag."%'",
                        self::COLUMN_PUBLISHED => $published))
                ->limit($limit)
                ->fetchAll();
    }
    
    public function getVideosFromDBbyTags($tags, $offset = 0, $limit = 10) {
        $tagsArray = explode(",", str_replace(" ", "", $tags));
        
        $tagsQuery = "";
        
        $i = 0;
        $len = count($tagsArray);
        foreach($tagsArray as $tag) {
            $tagsQuery .= self::COLUMN_TAGS." LIKE '%".$tag."%' ";
            
            if ($i != $len - 1) {
                $tagsQuery .= "AND ";
            }
            
            $i++;
        }
        
        $videos = self::$database->query("SELECT * FROM ".self::TABLE_NAME." WHERE ".$tagsQuery.
                " ORDER BY ".VideoManager::COLUMN_DATE. " DESC".
                " LIMIT ".$limit." OFFSET ".$offset)->fetchAll();
        
        return $videos;
        
    }

    public function getOriginalFileInfo($id) {
        $video = $this->getVideoFromDB($id, 2);
        $finfo = finfo_open();
        $file = VIDEOS_FOLDER . $id ."/". $video->original_file;
        if (file_exists($file)) {
            $fileinfo = finfo_file($finfo, $file, FILEINFO_MIME);
            finfo_close($finfo);
            return $fileinfo;
        } else {
            return false;
        }
    }

    public function deleteVideoFile($id, $file) {
        $video = $this->getVideoFromDB($id, 2);
        $fileToDelete = VIDEOS_FOLDER . $id ."/". $video->$file;
        if (!empty($video->$file)) {
            if (file_exists($fileToDelete)) {
                unlink($fileToDelete);
            }
        }
        $video->update(array($file => ""));
    }

    public function deleteVideo($id) {
        $video = $this->getVideoFromDB($id, 2);
        FileUtils::recursiveDelete(VIDEOS_FOLDER . $id ."/");
        $video->delete();
    }
    
    public function useOriginalFileAs($id, $target) {
        $video = $this->getVideoFromDB($id, 2);
        $video->update(array(self::COLUMN_ORIGINAL_FILE => "", $target => $video->original_file));

        if ($target == self::COLUMN_SUBTITLES_FILE) {
            $videoWithSubtitles = $this->getVideoFromDB($id, 2);
            $subtitlesFile = VIDEOS_FOLDER . $id . "/" . $videoWithSubtitles[self::COLUMN_SUBTITLES_FILE];
            self::fixSubtitlesFile($subtitlesFile);
        }

        if ($target == self::COLUMN_THUMB_FILE) {
            $newThumbColor = $this->getVideoThumbDominantColor($id);
            $video->update(array(self::COLUMN_THUMB_COLOR => $newThumbColor));
        }
    }

    public function fixSubtitlesFile($subtitlesFile) {

        $header = "[Script Info]
ScriptType: v4.00+
WrapStyle: 0
ScaledBorderAndShadow: yes
YCbCr Matrix: None
PlayResX: 640
PlayResY: 360

[V4+ Styles]
Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, OutlineColour, BackColour, Bold, Italic, Underline, StrikeOut, ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, Alignment, MarginL, MarginR, MarginV, Encoding
Style: Default,Roboto Slab,20,&H00FFFFFF,&H000000FF,&H00000000,&HFF000000,0,0,0,0,100,100,0,0,1,1.5,2,2,10,10,10,1

[Events]";

        $originalFile = file_get_contents($subtitlesFile);
        $justSubtitles = substr(strstr($originalFile, '[Events]'), strlen('[Events]'));
        $newContent = $header . $justSubtitles;

        $file = fopen($subtitlesFile, "w") or die("Unable to open file!");
        fwrite($file, $newContent);
        fclose($file);
    }

    public function useExternalFileAsThumb($id, $file) {
        $video = $this->getVideoFromDB($id, 2);
        $this->deleteVideoFile($id, self::COLUMN_THUMB_FILE);
        $this->deleteThumbnails($id);
        $newThumbName = StringUtils::rand(6).".jpg";
        copy($file, VIDEOS_FOLDER.$id."/".$newThumbName);

        $newThumbColor = $this->getVideoThumbDominantColor($id);
        $video->update(array(self::COLUMN_THUMB_FILE => $newThumbName,
            self::COLUMN_THUMB_COLOR => $newThumbColor));

    }

    public function addVideoToConvertQueue($id, $input, $target, $profile) {
        self::$queueManager->addVideoToQueue($id, $input, $target, $profile);
    }

    public function returnMissingThumbs() {
        return array(self::THUMB_1024 => "assets/img/missing-thumb.png",
                self::THUMB_512 => "assets/img/missing-thumb.png",
                self::THUMB_256 => "assets/img/missing-thumb.png",
                self::THUMB_128 => "assets/img/missing-thumb.png");
    }

    public function getThumbnails($id) {
        $video = $this->getVideoFromDB($id, 2);
        if ($video['thumb_file'] != null) {
            $thumb = VIDEOS_FOLDER.$video->id."/thumbs/".str_replace(".jpg", "_".self::THUMB_1024.".jpg", $video['thumb_file']);
            $thumbfile = VIDEOS_FOLDER.$video->id."/thumbs/".str_replace(".jpg", "", $video['thumb_file']);
            if (!file_exists($thumb)) {
                $this->generateThumbnails($id);
            }
            if (file_exists($thumb)) {
                return array(self::THUMB_1024 => $thumbfile."_".self::THUMB_1024.".jpg",
                    self::THUMB_512 => $thumbfile."_".self::THUMB_512.".jpg",
                    self::THUMB_256 => $thumbfile."_".self::THUMB_256.".jpg",
                    self::THUMB_128 => $thumbfile."_".self::THUMB_128.".jpg");
            } else {
                return $this->returnMissingThumbs();
            }
        } else {
            return null;
        }

    }

    public function deleteThumbnails($id) {
        $files = glob(VIDEOS_FOLDER.$id.'/thumbs/*');
        foreach($files as $file){
            if(is_file($file)) {
                 unlink($file);
            }
        }
    }

    private function generateThumbnails($videoId) {
        $video = $this->getVideoFromDB($videoId, 2);
        if (isset($video->thumb_file)) {
            $thumbFile = VIDEOS_FOLDER.$video->id."/".$video->thumb_file;
            if (file_exists($thumbFile)) {
                \App\ImageUtils::resizeImage(VIDEOS_FOLDER.$video->id, $video->thumb_file, self::THUMB_1024, self::THUMB_1024, VIDEOS_FOLDER.$video->id."/thumbs/");
                \App\ImageUtils::resizeImage(VIDEOS_FOLDER.$video->id, $video->thumb_file, self::THUMB_512, self::THUMB_512, VIDEOS_FOLDER.$video->id."/thumbs/");
                \App\ImageUtils::resizeImage(VIDEOS_FOLDER.$video->id, $video->thumb_file, self::THUMB_256, self::THUMB_256, VIDEOS_FOLDER.$video->id."/thumbs/");
                \App\ImageUtils::resizeImage(VIDEOS_FOLDER.$video->id, $video->thumb_file, self::THUMB_128, self::THUMB_128, VIDEOS_FOLDER.$video->id."/thumbs/");
            }
        }
    }

    public function countView($id) {
        $video = self::$database->table(self::TABLE_NAME)->get($id);
        $views = $video['views'];

        $video->update(array(self::COLUMN_VIEWS => $views + 1));
    }

    public function createLocalizedVideoObject($lang, $input) {
        $video = Array();

        switch($lang) {
            case 'cs':
                $day = date('d', strtotime($input[self::COLUMN_DATE]));
                $month = date('n', strtotime($input[self::COLUMN_DATE]));
                $year = date('Y', strtotime($input[self::COLUMN_DATE]));

                $video['name'] = $input[self::COLUMN_NAME_CS];
                $video['date'] = StringUtils::formatCzechDate($year, $month, $day);
                $video['desc'] = $input[self::COLUMN_DESCRIPTION_CS];
                break;
            case 'en':
                $day = date('d', strtotime($input[self::COLUMN_DATE]));
                $month = date('n', strtotime($input[self::COLUMN_DATE]));
                $year = date('Y', strtotime($input[self::COLUMN_DATE]));

                $video['name'] = $input[self::COLUMN_NAME_EN];
                $video['date'] = StringUtils::formatEnglishDate($year, $month, $day);
                $video['desc'] = $input[self::COLUMN_DESCRIPTION_EN];
                break;
        }


        $videoId = $input[self::COLUMN_ID];
        $video['id'] = $videoId;
        $video['hash'] = $input['hash'];
        $video['tags'] = $input[self::COLUMN_TAGS];
        if ($input[self::COLUMN_MP3_FILE] != '') {
            $video['mp3'] = VIDEOS_FOLDER.$videoId.'/'.$input[self::COLUMN_MP3_FILE];
        }
        if ($input[self::COLUMN_MP4_FILE] != '') {
            $video['mp4'] = VIDEOS_FOLDER.$videoId.'/'.$input[self::COLUMN_MP4_FILE];
        }
        if ($input[self::COLUMN_MP4_FILE_LOWRES] != '') {
            $video['mp4_lowres'] = VIDEOS_FOLDER.$videoId.'/'.$input[self::COLUMN_MP4_FILE_LOWRES];
        }
        if ($input[self::COLUMN_WEBM_FILE] != '') {
            $video['webm'] = VIDEOS_FOLDER.$videoId.'/'.$input[self::COLUMN_WEBM_FILE];
        }
        if ($input[self::COLUMN_SUBTITLES_FILE] != '') {
            $video['ass'] = VIDEOS_FOLDER.$videoId.'/'.$input[self::COLUMN_SUBTITLES_FILE];
        }
        $video['categories'] = $input[self::COLUMN_CATEGORIES];
        $video['views'] = $input[self::COLUMN_VIEWS];
        $video['thumbs'] = $this->getThumbnails($videoId);
        $video['thumb_color'] = $input[self::COLUMN_THUMB_COLOR];
        if ($input[self::COLUMN_METADATA_DURATION_IN_SECONDS] != '') {
            $video[self::COLUMN_METADATA_DURATION_IN_SECONDS] = $input[self::COLUMN_METADATA_DURATION_IN_SECONDS];
            $video['metadata'] = $this->getVideoFileMetadata("", $video[self::COLUMN_METADATA_DURATION_IN_SECONDS]);
        }
        $video['type'] = "video";

        return $video;
    }

    public function generateVideoTimeThumbs($id) {
        $this->deleteTimeThumbs($id);

        $video = $this->getVideoFromDB($id, 2);

        if($video['mp4_file'] != '') {
            $file = $video['mp4_file'];
        } else if ($video['webm_file'] != '') {
            $file = $video['webm_file'];
        } else if ($video['original_file'] != '') {
            $file = $video['original_file'];
        } else {
            $file = null;
        }

        if ($file != null) {

            $command = PATH_TO_FFMPEG." -i ".
                CONVERSION_FOLDER_ROOT.$id."/".$file.
                ' -threads 2 -an -sn -vsync 0 -vf fps=fps=1/30,scale=150:-1 '.CONVERSION_FOLDER_ROOT.$id."/time-thumbs/time-thumb-%04d.jpg".
                ' -y > /dev/null 2>/dev/null &';

            shell_exec($command);
        }
    }

    public function countTimeThumbs($id) {
        if (file_exists(VIDEOS_FOLDER.$id.'/time-thumbs/')) {
            $fi = new \FilesystemIterator(VIDEOS_FOLDER.$id.'/time-thumbs/', \FilesystemIterator::SKIP_DOTS);
            return iterator_count($fi);
        } else {
            return 0;
        }
    }

    public function deleteTimeThumbs($id) {
        $files = glob(VIDEOS_FOLDER.$id.'/time-thumbs/*');
        foreach($files as $file){
            if(is_file($file)) {
                 unlink($file);
            }
        }
    }
    
    public function findSimilarVideos($originalVideo, $lang, $numOfVideos = 8) {
        $originalTags = explode(',', str_replace(' ', '', $originalVideo['tags']));
        $tagsManager = new TagsManager(self::$database); 
        $hiddenTags = $tagsManager->getHiddenTagsFromDB();
        
        $hiddenTagsArray = array();
        foreach($hiddenTags as $hiddenTag) {
            $hiddenTagsArray[] = $hiddenTag['tag'];
        }
        
        $usableTags = array_diff($originalTags, $hiddenTagsArray);
        sort($usableTags);
        $similarVideos = array();
        
        if ($usableTags >= $numOfVideos) {
            $try = 0;
            while(sizeof($similarVideos) != $numOfVideos) {
                
                if ($try > $numOfVideos) {
                    break;
                }
                $randomTag = $usableTags[rand(0, count($usableTags)-1)];
                
                $similarVideo = $this->getVideoFromDBbyTag($randomTag);

                if($similarVideo != false) {
                    if ($similarVideo['id'] != $originalVideo['id']
                            || !in_array($similarVideo['id'], $similarVideos)) {
                        $similarVideos[] = $similarVideo['id'];
                    }
                }
                
                $try++;
            }
        }
        
        $localizedSimilarVideos = array();
        
        foreach($similarVideos as $id) {
            $localizedSimilarVideos[] = $this
                    ->createLocalizedVideoObject($lang, $this->getVideoFromDB($id));
        }
        
        return $localizedSimilarVideos;
    }

    public function getVideoThumbDominantColor($thumbFile, $quality = 30) {
        if ($thumbFile != null) {
            if (file_exists($thumbFile)) {
                return ColorUtils::rgb2hex(ColorThief::getColor($thumbFile, $quality));
            }
        }
        return null;
    }

    public function saveVideoThumbDominantColor($videoId) {
        $video = $this->getVideoFromDB($videoId, 2);
        if ($video[self::COLUMN_THUMB_COLOR] == NULL) {
            $thumbFile = VIDEOS_FOLDER . $videoId . "/thumbs/" . str_replace(".jpg", "_128.jpg", $video[self::COLUMN_THUMB_FILE]);
            $thumbColor = $this->getVideoThumbDominantColor($thumbFile);
            $video->update(array(self::COLUMN_THUMB_COLOR => $thumbColor));
        }
    }
}

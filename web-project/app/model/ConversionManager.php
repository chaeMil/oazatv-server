<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 Model\ServerSettings,
 Model\VideoConvertQueueManager,
 Model\VideoManager,
 App\EventLogger;

/**
 * Description of ConversionManager
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class ConversionManager {
    
    /** @var Nette\Database\Context */
    public static $database;
    private $serverSettings;
    private $queueManager;
    private $videoManager;
    private $profilesManager;

    public function __construct(Nette\Database\Context $database, \Model\ServerSettings $serverSettings,
     \Model\VideoConvertQueueManager $queueManager, \Model\VideoManager $videoManager, 
            \Model\ConversionProfilesManager $profilesManager) {
        $this::$database = $database;
        $this->serverSettings = $serverSettings;
        $this->queueManager = $queueManager;
        $this->videoManager = $videoManager;
        $this->profilesManager = $profilesManager;
    }
    
    public function startConversion($queueId) {
        $queueItem = $this->queueManager->getVideoFromQueueByQueueId($queueId);
        $video = $this->videoManager->getVideoFromDB($queueItem->video_id, 2);      
        
        //setup bitrate
        switch ($queueItem->target) {
            case VideoManager::COLUMN_MP3_FILE:
                if ($queueItem['profile'] == 0) {
                    $CONVaudioBitrate = $this->serverSettings->loadValue("mp3_audio_bitrate");
                    $CONVvideoBitrate = $this->serverSettings->loadValue("mp3_video_bitrate");
                }
                $CONVextension = ".mp3";
                $CONVcodecVideo = "";
                $CONVcodecAudio = "";
                $CONVextraParam = "-g 0";
                break;
            case VideoManager::COLUMN_MP4_FILE:
                if ($queueItem['profile'] == 0) {
                    $CONVaudioBitrate = $this->serverSettings->loadValue("mp4_audio_bitrate");
                    $CONVvideoBitrate = $this->serverSettings->loadValue("mp4_video_bitrate");
                }
                $CONVextension = ".mp4";
                $CONVcodecVideo = "libx264 -preset medium -profile:v baseline -level 3";
                $CONVcodecAudio = "aac -strict -2";
                $CONVextraParam = "-deinterlace -movflags faststart -async 1";
                break;
            case VideoManager::COLUMN_WEBM_FILE:
                if ($queueItem['profile'] == 0) {
                    $CONVaudioBitrate = $this->serverSettings->loadValue("webm_audio_bitrate");
                    $CONVvideoBitrate = $this->serverSettings->loadValue("webm_video_bitrate");
                }
                $CONVextension = ".webm";
                $CONVcodecVideo = "libvpx";
                $CONVcodecAudio = "libvorbis";
                $CONVextraParam = "-async 1";
                break;
            case VideoManager::COLUMN_MP4_FILE_LOWRES:
                if ($queueItem['profile'] == 0) {
                    $CONVaudioBitrate = $this->serverSettings->loadValue("mp4_lowres_audio_bitrate");
                    $CONVvideoBitrate = $this->serverSettings->loadValue("mp4_lowres_video_bitrate");
                }
                $CONVextension = ".mp4";
                $CONVcodecVideo = "libx264 -preset medium -profile:v baseline -level 3 -vf scale=-1:480";
                $CONVcodecAudio = "aac -strict -2";
                $CONVextraParam = "-deinterlace -movflags faststart -async 1";
        }
        
        if ($queueItem['profile'] != 0) {
            
            $profile = $this->profilesManager->getProfileFromDB($queueItem['profile']);
            
            $CONVaudioBitrate = $profile['audio_bitrate'];
            $CONVvideoBitrate = $profile['video_bitrate'];
            
        }
        
        
        //setup input file
        switch ($queueItem->input) {
            case VideoManager::COLUMN_MP3_FILE:
                $CONVinput = $video->mp3_file;
                break;
            case VideoManager::COLUMN_MP4_FILE:
                $CONVinput = $video->mp4_file;
                break;
            case VideoManager::COLUMN_WEBM_FILE:
                $CONVinput = $video->webm_file;
                break;
            case VideoManager::COLUMN_ORIGINAL_FILE:
                $CONVinput = $video->original_file;
                break;
        }
        
        $CONVthreads = $this->serverSettings->loadValue("conversion_threads");
        $CONVfolder = CONVERSION_FOLDER_ROOT . $video->id . "/";
        $CONVtarget = \App\StringUtils::rand(6).$CONVextension;
        $CONVlog = "logs/".date("Y-m-d_H-i-s").".log";
        
        $queueItem->update(array(VideoConvertQueueManager::COLUMN_STATUS => VideoConvertQueueManager::STATUS_CONVERTING,
                                VideoConvertQueueManager::COLUMN_STARTED_AT => date('Y-m-d H:i:s'),
                                VideoConvertQueueManager::COLUMN_TARGET_FILENAME => $CONVtarget));
        
        $this->videoManager->deleteVideoFile($video->id, $queueItem->target);
        $video->update(array($queueItem->target => ""));
        
        /*dump($CONVaudioBitrate); dump($CONVvideoBitrate); dump($CONVthreads); 
        dump($CONVfolder); dump($CONVinput); dump($CONVtarget);
        dump($CONVcodecAudio); dump($CONVcodecVideo); dump($CONVextraParam);*/
        
        if ($CONVaudioBitrate != 0 && $CONVcodecAudio != "") {
            $CONVaudio = " -c:a ".$CONVcodecAudio." -b:a ".$CONVaudioBitrate."k";
        } else {
            $CONVaudio = "";
        }
        
        if ($CONVvideoBitrate != 0 && $CONVcodecVideo != "") {
            $CONVvideo = " -c:v ".$CONVcodecVideo." -b:v ".$CONVvideoBitrate."k";
        } else {
            $CONVvideo = "";
        }
        
        $CONVcommand = PATH_TO_FFMPEG. " -i ".$CONVfolder.$CONVinput." -y -threads "
                .$CONVthreads." ".$CONVvideo." ".$CONVaudio." ".$CONVextraParam
                ." ".$CONVfolder.$CONVtarget." 1> ".$CONVfolder.$CONVlog
                ." 2>&1 &";
        
        //dump($CONVcommand);
        //echo $CONVcommand; exit;
        
        EventLogger::log('conversion of '.$CONVinput. ' to '.$CONVtarget.' started', 
                EventLogger::CONVERSION_LOG);
        shell_exec($CONVcommand);
    }
    
    public function getConversionStatus($queueId) {
        $queueItem = $this->queueManager->getVideoFromQueueByQueueId($queueId);
        $video = $this->videoManager->getVideoFromDB($queueItem->video_id, 2);
        
        $logfileDir = VIDEOS_FOLDER.$video->id."/logs/";
        $logfiles = scandir($logfileDir, SCANDIR_SORT_DESCENDING);
        $CONVlogfile = $logfiles[0];
        
        $CONVlogfileContent = file_get_contents($logfileDir.$CONVlogfile);
        
        $progress = 0;

        if ($CONVlogfileContent) {
            
            $content = $CONVlogfileContent;
            //get duration of source
            preg_match("/Duration: (.*?), start:/", $content, $matches);

            $rawDuration = $matches[1];

            //rawDuration is in 00:00:00.00 format. This converts it to seconds.
            $ar = array_reverse(explode(":", $rawDuration));
            $duration = floatval($ar[0]);
            if (!empty($ar[1]))
                $duration += intval($ar[1]) * 60;
            if (!empty($ar[2]))
                $duration += intval($ar[2]) * 60 * 60;

            //get the time in the file that is already encoded
            preg_match_all("/time=(.*?) bitrate/", $content, $matches);

            $rawTime = array_pop($matches);

            //this is needed if there is more than one match
            if (is_array($rawTime)) {
                $rawTime = array_pop($rawTime);
            }

            //rawTime is in 00:00:00.00 format. This converts it to seconds.
            $ar = array_reverse(explode(":", $rawTime));
            $time = floatval($ar[0]);
            if (!empty($ar[1]))
                $time += intval($ar[1]) * 60;
            if (!empty($ar[2]))
                $time += intval($ar[2]) * 60 * 60;

            //calculate the progress
            $progress = round(($time / $duration) * 100, 3);
        }
        
        return $progress;
    }
}

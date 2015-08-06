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
 Model\VideoManager;

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

    public function __construct(Nette\Database\Context $database, \Model\ServerSettings $serverSettings,
     \Model\VideoConvertQueueManager $queueManager, \Model\VideoManager $videoManager) {
        $this::$database = $database;
        $this->serverSettings = $serverSettings;
        $this->queueManager = $queueManager;
        $this->videoManager = $videoManager;
    }
    
    public function startConversion($queueId) {
        $queueItem = $this->queueManager->getVideoFromQueueByQueueId($queueId);
        $video = $this->videoManager->getVideoFromDB($queueItem->video_id);
        
        //setup bitrate
        switch ($queueItem->target) {
            case VideoManager::COLUMN_MP3_FILE:
                $CONVaudioBitrate = $this->serverSettings->loadValue("mp3_audio_bitrate");
                $CONVvideoBitrate = $this->serverSettings->loadValue("mp3_video_bitrate");
                $CONVextension = ".mp3";
                $CONVcodecVideo = "";
                $CONVcodecAudio = "";
                $CONVextraParam = "-g 0";
                break;
            case VideoManager::COLUMN_MP4_FILE:
                $CONVaudioBitrate = $this->serverSettings->loadValue("mp4_audio_bitrate");
                $CONVvideoBitrate = $this->serverSettings->loadValue("mp4_video_bitrate");
                $CONVextension = ".mp4";
                $CONVcodecVideo = "libx264 -preset medium -profile:v baseline -level 3";
                $CONVcodecAudio = "aac -strict -2";
                $CONVextraParam = "-deinterlace -movflags faststart -async 1";
                break;
            case VideoManager::COLUMN_WEBM_FILE:
                $CONVaudioBitrate = $this->serverSettings->loadValue("webm_audio_bitrate");
                $CONVvideoBitrate = $this->serverSettings->loadValue("webm_video_bitrate");
                $CONVextension = ".webm";
                $CONVcodecVideo = "libvpx";
                $CONVcodecAudio = "libvorbis";
                $CONVextraParam = "-async 1";
                break;
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
        //echo $CONVcommand;
        shell_exec($CONVcommand);
    }
}

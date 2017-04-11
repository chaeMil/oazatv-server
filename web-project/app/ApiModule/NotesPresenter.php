<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 11/04/2017
 * Time: 15:56
 */

namespace App\ApiModule;


use Model\MyOazaManager;
use Model\UserManager;
use Model\VideoManager;

class NotesPresenter extends BasePresenter {

    public function actionSave($id) {

        $videoHash = $this->request->getPost('videoHash');
        $token = $this->request->getHeaders('token');
        $note = $this->request->getPost('note');
        $time = $this->request->getPost('time');

        $tokenValid = $this->userManager->validateUserToken($token);

        if ($tokenValid) {
            $video = $this->videoManager->getVideoFromDBbyHash($videoHash);
            $user = $this->userManager->findByToken($token);

            if ($video) {
                if ($id != null) {
                    $existingNote = $this->myOazaManager->getNote($id);
                    if ($existingNote) {
                        $this->myOazaManager->updateNote($id, $note);
                        $this->sendJson(array("status" => "ok"));
                    } else {
                        $this->myOazaManager->addNote($user[UserManager::COLUMN_ID],
                            $video[VideoManager::COLUMN_ID],
                            $note, $time);
                        $this->sendJson(array("status" => "ok"));
                    }
                    $this->sendJson(array("status" => "error"));
                } else {
                    $this->myOazaManager->addNote($user[UserManager::COLUMN_ID],
                        $video[VideoManager::COLUMN_ID],
                        $note, $time);
                    $this->sendJson(array("status" => "ok"));
                }

            } else {
                $this->sendJson(array("status" => "error"));
            }
        }

        $this->sendJson(array("status" => "error", "token_valid" => $tokenValid));
    }

    public function actionGet() {

        $videoHash = $this->request->getQuery('videoHash');
        $token = $this->request->getHeader('token');

        $tokenValid = $this->userManager->validateUserToken($token);

        if ($tokenValid) {
            $video = $this->videoManager->getVideoFromDBbyHash($videoHash);
            $user = $this->userManager->findByToken($token);

            if ($video) {
                $notes = $this->myOazaManager
                    ->getNotesFromVideo($user[UserManager::COLUMN_ID],
                        $video[VideoManager::COLUMN_ID]);

                $notesJson = [];
                foreach($notes as $note) {
                    $notesJson[] = array("time" => $note[MyOazaManager::TIME],
                        "note" => $note[MyOazaManager::NOTE],
                        "edited" => $note[MyOazaManager::EDITED]);
                }

                $this->sendJson(array("status" => "ok", "notes" => $notesJson));
            } else {
                $this->sendJson(array("status" => "error"));
            }
        }

        $this->sendJson(array("status" => "error", "token_valid" => $tokenValid));
    }

}
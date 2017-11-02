<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 02/11/2017
 * Time: 10:51
 */

namespace App\AdminModule;

use Model\LiveChatManager;
use Nette;

class LiveChatPresenter extends BaseSecuredPresenter{
    public $database;
    public $liveChatManager;

    function __construct(Nette\Database\Context $database,
                         LiveChatManager $liveChatManager) {
        $this->database = $database;
        $this->liveChatManager = $liveChatManager;
    }

    public function renderDefault() {
        $this->getTemplateVariables($this->getUser()->getId());
        $messages = $this->liveChatManager->getAllMessages();
        $approvedMessages = $this->liveChatManager->getApprovedMessages();
        $nonApprovedMessages = $this->liveChatManager->getNonApprovedMessages();
        $this->template->messages = $messages;
        $this->template->approvedMessages = $approvedMessages;
        $this->template->nonApprovedMessages = $nonApprovedMessages;
    }

    public function actionDeleteMessage() {
        $id = $_POST['id'];
        $status = $this->liveChatManager->deleteMessage($id);
        if ($this->isAjax()) {
            $this->sendJson(array('status' => $status));
        } else {
            $this->redirect('default');
        }
    }

    public function actionApproveMessage() {
        $id = $_POST['id'];
        $status = $this->liveChatManager->approveMessage($id);
        if ($this->isAjax()) {
            $this->sendJson(array('status' => $status));
        } else {
            $this->redirect('default');
        }
    }

    public function actionDeleteAllMessages() {
        $status = $this->liveChatManager->deleteAllMessages();
        if ($this->isAjax()) {
            $this->sendJson(array('status' => $status));
        } else {
            $this->redirect('default');
        }
    }
}
<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use App\ImageUtils;
use Model\PhotosManager;
use Nette,
 Model\FrontPageManager,
 App\EventLogger,
 App\StringUtils;

/**
 * Description of VideoPresenter
 *
 * @author chaemil
 */
class FrontPageManagerPresenter extends BaseSecuredPresenter {

    public $database;
    public $frontPageManager;

    function __construct(Nette\Database\Context $database,
            FrontPageManager $frontPageManager) {
        $this->database = $database;
        $this->frontPageManager = $frontPageManager;
    }
    
    public function createComponentFeaturedForm() {
        $form = new Nette\Application\UI\Form;

        $form->addText('featured', 'Vybraná videa / alba')
                ->setRequired()
                ->setAttribute("class", "form-control")
                ->setHtmlId("featured")
                ->setAttribute("data-role", "tagsinput");
        
        $form->addSubmit('send', 'Uložit')
                ->setAttribute("class", "btn-lg btn-success btn-block");


        // call method signInFormSucceeded() on success
        $form->onSuccess[] = $this->featuredSucceeded;

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }
    
    public function featuredSucceeded($form) {
        $vals = $form->getValues();
        $featured = $vals['featured'];

        $this->frontPageManager->saveFeatured($featured);
        
        EventLogger::log('user '.$this->getUser()->getIdentity()
                ->login.' updated featured '.$featured,
            EventLogger::ACTIONS_LOG);

        $this->flashMessage("Změny úspěšně uloženy", "success");
        $this->redirect("FrontPageManager:rowsList");
    }

    public function renderRowsList() {
        $this->getTemplateVariables($this->getUser()->getId());

        $this->template->rows = $this->frontPageManager->getRowsFromDB();
        $this->template->frontPageManager = $this->frontPageManager;
        $this->template->blockDefinitions = $this->frontPageManager->getBlocksDefinitions();
        
        $featured = implode("," ,$this->frontPageManager->loadFeatured());
        $this['featuredForm']->setDefaults(array("featured" => $featured));
    }

    public function renderCreateRow() {
        $this->getTemplateVariables($this->getUser()->getId());
    }

    public function createComponentCreateRowForm() {
        $form = new Nette\Application\UI\Form;

        $form->addHidden('id');

        $form->addText('name', 'název')
                ->setRequired()
                ->setAttribute("class", "form-control");

        $classes = array("container" => "container", "container-fluid" => "container-fluid");
        $form->addSelect('class', 'třída', $classes)
                ->setRequired()
                ->setAttribute("class", "form-control");

        $form->addSubmit('send', 'Vytvořit novou pozici')
                ->setAttribute("class", "btn-lg btn-success btn-block");


        // call method signInFormSucceeded() on success
        $form->onSuccess[] = $this->rowSucceeded;

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }


    public function rowSucceeded($form) {
        $vals = $form->getValues();

        $status = $this->frontPageManager->saveRowToDB($vals);

        if ($status) {
            EventLogger::log('user '.$this->getUser()->getIdentity()
                    ->login.' updated frontpage > row '.$vals->id,
                EventLogger::ACTIONS_LOG);

            $this->flashMessage("Změny úspěšně uloženy", "success");
        } else {
            $this->flashMessage("Nic nebylo změněno", "info");
        }
    }

    public function actionDeleteRow($id) {
        $this->frontPageManager->deleteRow($id);
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' deleted frontpage > row '.$id,
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Pozice byla smazána!", "danger");
        $this->redirect("FrontPageManager:RowsList");
    }

    public function actionMoveRowUp($id) {
        $this->frontPageManager->moveRow($id, -1);
        $this->flashMessage("Změny úspěšně uloženy", "success");
        $this->redirect("RowsList");
    }

    public function actionMoveRowDown($id) {
        $this->frontPageManager->moveRow($id, 1);
        $this->flashMessage("Změny úspěšně uloženy", "success");
        $this->redirect("RowsList");
    }

    public function actionToggleRowPublished($id) {
        $this->frontPageManager->toggleRowPublished($id);
        $this->flashMessage("Změny úspěšně uloženy", "success");
        $this->redirect("RowsList");
    }

    public function actionCreateNewBlock($rowId, $type) {
        $values['type'] = $type;
        $blockId = $this->frontPageManager->saveBlockToDB($values);
        $this->frontPageManager->addBlockToRow($blockId, $rowId);

        EventLogger::log('user '.$this->getUser()->getIdentity()
                    ->login.' updated frontpage > add block '.$blockId,
                EventLogger::ACTIONS_LOG);

        $this->flashMessage("Změny úspěšně uloženy", "success");
        $this->redirect("RowsList");
    }

    public function actionDeleteBlock($id) {
        $this->frontPageManager->deleteBlock($id);
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' deleted frontpage > block '.$id,
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Blok byl smazán!", "danger");
        $this->redirect("RowsList");
    }

    public function actionEditBlock($id) {
        $this->getTemplateVariables($this->getUser()->getId());

        $definitions = $this->frontPageManager->getBlocksDefinitions();

        $blockImages = array();

        $block = $this->frontPageManager->getBlockFromDB($id);
        $blockJsonData = json_decode($block['json_data'], true);

        foreach($definitions[$block['type']]['inputs'] as $input) {

            if ($input['type'] == "image") {
                if (isset($input['mutations'])) {

                    $mutations = explode("|", $input['mutations']);
                    foreach ($mutations as $mutation) {
                        $blockImages[$input['name']][$mutation] = $blockJsonData['inputs'][$input['name']][$mutation];
                    }
                }
            }
        }

        $this->template->blockImages = $blockImages;

    }

    public function createComponentEditBlock() {

        $definitions = $this->frontPageManager->getBlocksDefinitions();
        $id = $this->getParameter('id');
        $block = $this->frontPageManager->getBlockFromDB($id);
        if($block[FrontPageManager::COLUMN_JSON_DATA] != "") {
            $savedData = $this->frontPageManager
                    ->parseJsonBlock($block[FrontPageManager::COLUMN_JSON_DATA]);
        }

        $form = new Nette\Application\UI\Form;
        $form->addHidden('id')->setValue($id);

        if(isset($block) && sizeof($definitions) > 0) {

            foreach($definitions as $definition) {
                if ($definition['name'] == $block['type']) {

                    $form->addHidden('definition')
                            ->setValue($definition['name']);

                    if(isset($definition['inputs'])) {
                        foreach($definition['inputs'] as $input) {
                            switch($input['type']) {
                                case 'text':
                                    if (isset($input['mutations'])) {
                                        $form->addGroup($input['name']);
                                        foreach(explode('|', $input['mutations']) as $mutation) {

                                            if (isset($savedData)) {
                                                $savedInput = $savedData['inputs'][$input['name']][$mutation];
                                            } else {
                                                $savedInput = "";
                                            }
                                            $form->addText($input['name'].'_'.$mutation, $mutation)
                                                    ->setValue($savedInput)
                                                    ->setAttribute("class", "form-control");
                                        }
                                    } else {
                                        $form->addGroup($input['name']);

                                        if (isset($savedData)) {
                                                $savedInput = $savedData['inputs'][$input['name']];
                                            } else {
                                                $savedInput = "";
                                            }
                                        $form->addText($input['name'], $input['name'])
                                                ->setValue($savedInput)
                                                    ->setAttribute("class", "form-control");
                                    }
                                    break;
                                case 'select':
                                    $form->addGroup($input['name']);

                                    $definitionOptions = explode("|", $definition['inputs'][$input['type']]['options']);
                                    if (isset($savedData)) {
                                        $savedValue = $savedData['inputs'][$input['name']];
                                        $savedInput = array_search($savedValue, $definitionOptions);
                                    } else {
                                        $savedInput = 0;
                                    }

                                    $form->addSelect($input['name'], $input['name'])
                                            ->setItems($definitionOptions)
                                            ->setValue($savedInput)
                                            ->setAttribute("class", "form-control");

                                    break;

                                case 'image':
                                    if (isset($input['mutations'])) {
                                        $form->addGroup($input['name']);
                                        foreach(explode('|', $input['mutations']) as $mutation) {

                                            if (isset($savedData)) {
                                                $savedInput = $savedData['inputs'][$input['name']][$mutation];
                                            } else {
                                                $savedInput = "";
                                            }
                                            $form->addUpload($input['name'].'_'.$mutation, $mutation)
                                                //->setValue($savedInput)
                                                ->setAttribute("class", "form-control");
                                        }
                                    } else {
                                        $form->addGroup($input['name']);

                                        if (isset($savedData)) {
                                            $savedInput = $savedData['inputs'][$input['name']];
                                        } else {
                                            $savedInput = "";
                                        }
                                        $form->addUpload($input['name'], $input['name'])
                                            //->setValue($savedInput)
                                            ->setAttribute("class", "form-control");
                                    }
                                    break;
                            }
                        }
                    }
                }
            }

            $form->addGroup("");
            $form->addSubmit('send', 'Uložit')
                ->setAttribute("class", "btn-lg btn-success btn-block");
        }

        $form->onSuccess[] = $this->editBlockSucceeded;

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;

    }

    public function editBlockSucceeded($form) {
        $vals = $form->getValues();

        foreach($vals as $input => $val) {

            if (gettype($val) == "object" && $val instanceof Nette\Http\FileUpload) {

                $fileName = "";

                if ($val->error == 0 && $val->size > 0) {

                    $fileName = "block_" . $vals['id'] . "_" . $input. ".jpg";

                    if (file_exists($fileName)) {
                        unlink($fileName);
                    }

                    $val->move(FRONTPAGE_IMAGES_FOLDER . $fileName);

                    ImageUtils::resizeImage(FRONTPAGE_IMAGES_FOLDER,
                        $fileName,
                        FrontPageManager::THUMB_256,
                        FrontPageManager::THUMB_256,
                        FRONTPAGE_IMAGES_FOLDER);

                    ImageUtils::resizeImage(FRONTPAGE_IMAGES_FOLDER,
                        $fileName,
                        FrontPageManager::THUMB_512,
                        FrontPageManager::THUMB_512,
                        FRONTPAGE_IMAGES_FOLDER);

                    ImageUtils::resizeImage(FRONTPAGE_IMAGES_FOLDER,
                        $fileName,
                        FrontPageManager::THUMB_1024,
                        FrontPageManager::THUMB_1024,
                        FRONTPAGE_IMAGES_FOLDER);

                    ImageUtils::resizeImage(FRONTPAGE_IMAGES_FOLDER,
                        $fileName,
                        FrontPageManager::THUMB_2048,
                        FrontPageManager::THUMB_2048,
                        FRONTPAGE_IMAGES_FOLDER);
                }

                $vals[$input] = $fileName;
            }
        }

        $jsonData = $this->frontPageManager->createJsonBlock($vals);
        $dbValues = array(
            FrontPageManager::COLUMN_ID => $vals['id'],
            FrontPageManager::COLUMN_TYPE => $vals['definition'],
            FrontPageManager::COLUMN_JSON_DATA => $jsonData);

        $this->frontPageManager->saveBlockToDB($dbValues);

        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' edited frontpage > block '.$vals['id'],
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Blok upraven!", "success");
        $this->redirect("RowsList");
    }

}

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
 Model\VideoManager,
 Model\PhotosManager;
/**
 * Description of VideoManager
 *
 * @author chaemil
 */
class FrontPageManager extends BaseModel {

    const
            FEATURED_FILE = '/../config/home_featured.json',
            TABLE_NAME_ROWS = 'frontpage_rows',
            TABLE_NAME_BLOCKS = 'frontpage_blocks',
            COLUMN_ID = 'id',
            COLUMN_SORT = 'sort',
            COLUMN_CLASS = 'class',
            COLUMN_PUBLISHED = 'published',
            COLUMN_NAME = 'name',
            COLUMN_BLOCKS = 'blocks',
            COLUMN_TYPE = 'type',
            COLUMN_JSON_DATA = 'json_data',
            THUMB_2048 = 2048,
            THUMB_1024 = 1024,
            THUMB_512 = 512,
            THUMB_256 = 256,
            THUMB_128 = 128;

    /** @var Nette\Database\Context */
    public static $database;
    private $neonAdapter;
    private $photosManager;
    private $videoManager;

    public function __construct(Nette\Database\Context $database,
            VideoManager $videoManager, 
            PhotosManager $photosManager) {
        
        self::$database = $database;
        $this->videoManager = $videoManager;
        $this->photosManager = $photosManager;
        $this->neonAdapter = new Nette\DI\Config\Adapters\NeonAdapter();
    }
    
    public function loadFeatured() {
        return json_decode(file_get_contents(__DIR__.self::FEATURED_FILE), true)['featured'];
    }
    
    public function saveFeatured($values) {
        $jsonValues = json_encode(array('featured' => explode(',', $values)));
        file_put_contents(__DIR__.self::FEATURED_FILE, $jsonValues);
    }
    
    public function getFeaturedItems() {
        $featuredJson = $this->loadFeatured();
        
        $featuredOutput = array();
        
        foreach($featuredJson as $hash) {
            $featuredVideo = $this->videoManager->getVideoFromDBbyHash($hash);
            
            if ($featuredVideo) {
                $featuredVideoOut = $featuredVideo->toArray();
                $featuredVideoOut['type'] = "video";
                $featuredOutput[] = $featuredVideoOut;
            }
        }
        
        foreach($featuredJson as $hash) {
            $featuredAlbum = $this->photosManager->getAlbumFromDBbyHash($hash);
            
            if ($featuredAlbum) {
                $featuredAlbumOut = $featuredAlbum->toArray();
                $featuredAlbumOut['type'] = "album";
                $featuredOutput[] = $featuredAlbumOut;
            }
        }
        
        return $featuredOutput;
    }
    
    private function checkIfRowExists($id) {
        return self::$database->table(self::TABLE_NAME_ROWS)
                ->where(self::COLUMN_ID, $id)->count();
    }
    
    private function getRowMaxSort() {
        return self::$database->table(self::TABLE_NAME_ROWS)
                ->max(self::COLUMN_SORT);
    }
    
    public function saveRowToDB($values) {

        if(isset($values['id'])) {
            $id = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $id = 0;
        }

        if ($id != 0 && $this->checkIfRowExists($id) > 0) {
            $row = self::$database->table(self::TABLE_NAME_ROWS)->get($id);
            $sql = $row->update($values);
            return $sql;
        } else {
            $maxSort = $this->getRowMaxSort();
            if (isset($maxSort)) {
                $values['sort'] = intval($maxSort) + 1;
            }
            unset($values['id']);
            $sql = self::$database->table(self::TABLE_NAME_ROWS)->insert($values);
        }

    }
    
    
    public function getRowFromDB($id) {
        return self::$database->table(self::TABLE_NAME_ROWS)->get($id);
    }
    
    public function getRowsFromDB() {
        return self::$database->table(self::TABLE_NAME_ROWS)
            ->select('*')
            ->order(self::COLUMN_SORT." ASC")
            ->fetchAll();

    }
    
    public function deleteRow($id) {
        $row = $this->getRowFromDB($id);
        $row->delete();
    }
    
    private function checkIfBlockExists($id) {
        return self::$database->table(self::TABLE_NAME_BLOCKS)
                ->where(self::COLUMN_ID, $id)->count();
    }
    
    public function saveBlockToDB($values) {

        if(isset($values['id'])) {
            $id = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $id = 0;
        }

        if ($id != 0 && $this->checkIfBlockExists($id) > 0) {
            $block = self::$database->table(self::TABLE_NAME_BLOCKS)->get($id);
            $sql = $block->update($values);
            return $sql;
        } else {
            $sql = self::$database->table(self::TABLE_NAME_BLOCKS)->insert($values);
        }

        return $sql->id;

    }
   
    public function getBlockFromDB($id) {
        return self::$database->table(self::TABLE_NAME_BLOCKS)
                ->select('*')
                ->where(self::COLUMN_ID, $id)
                ->fetch();
    }
    
    public function getBlocksFromDB() {
        return self::$database->table(self::TABLE_NAME_BLOCKS)
            ->select('*')
            ->order(self::COLUMN_SORT." ASC")
            ->fetchAll();

    }
    
    public function deleteBlock($id) {
        $row = $this->getBlockFromDB($id);
        $row->delete();
    }
    
    public function getBlocksFromRow($rowId) {
        $row = $this->getRowFromDB($rowId);
        $blocksIdsArray = explode(",", str_replace(" ", "", trim($row[self::COLUMN_BLOCKS])));
           
        $blocks = array();
        foreach($blocksIdsArray as $blockId) {
            if ($blockId != "") {
                $blocks[] = $this->getBlockFromDB($blockId);
            }
        }
        
        return $blocks;
    }
    
    public function isBlockInRow($blockId, $rowId) {
        $row = $this->getRowFromDB($rowId);
        $blockIdsArray = $blocksIdsArray = explode(",", str_replace(" ", "", trim($row)));
        return in_array($blockId, $blockIdsArray);
    }
    
    private function getNextSortRow($sort) {
        return self::$database->table(self::TABLE_NAME_ROWS)
                ->select('*')
                ->order(self::COLUMN_SORT." ASC")
                ->where(self::COLUMN_SORT." > ?", $sort)
                ->limit(1, 0)
                ->fetch();
    }
    
    private function getPrevSortRow($sort) {
        return self::$database->table(self::TABLE_NAME_ROWS)
                ->select('*')
                ->order(self::COLUMN_SORT." DESC")
                ->where(self::COLUMN_SORT." < ?", $sort)
                ->limit(1, 0)
                ->fetch();
    }
    
    public function moveRow($id, $amount) {
        
        $originalRow = $this->getRowFromDB($id);
        
        if ($amount < 0) {
            $otherRow = $this->getPrevSortRow($originalRow['sort']);
        } else {
            $otherRow = $this->getNextSortRow($originalRow['sort']);
        }
        
        if ($otherRow) {
            $originalRowSort = $originalRow['sort'];
            $otherRowSort = $otherRow['sort'];

            $originalRow->update(array(self::COLUMN_SORT => $otherRowSort));
            $otherRow->update(array(self::COLUMN_SORT => $originalRowSort));
        }
    }

    public function toggleRowPublished($id) {
        $row = $this->getRowFromDB($id);
        if ($row[self::COLUMN_PUBLISHED] == 1) {
            $published = 0;
        } else {
            $published = 1;
        }
        $row->update(array(self::COLUMN_PUBLISHED => $published));
    }
    
    public function addBlockToRow($blockId, $rowId) {
        $row = $this->getRowFromDB($rowId);
        $rowBlocks = $row[self::COLUMN_BLOCKS];
        $row->update(array(self::COLUMN_BLOCKS => $rowBlocks .= $blockId.","));
    }
    
    public function getBlocksDefinitions() {
        $definitions = $this->neonAdapter->load(__DIR__ . '/../config/frontpage_block_definitions.neon');
        return $definitions['frontpage_blocks'];
    }
    
    public function parseJsonBlock($json) {
        return json_decode($json, true);
    }

    public function createJsonBlock($vals) {
        $output = array();
        $output['inputs'] = array();
        
        $definitions = $this->getBlocksDefinitions();
        foreach($definitions as $definition) {
            if ($definition['name'] == $vals['definition']) {
                foreach($definition['inputs'] as $input) {
                    switch($input['type']) {
                        case 'text':
                            if (isset($input['mutations'])) {
                                $output['inputs'][$input['name']] = array();
                                foreach(explode("|", $input['mutations']) as $mutation) {
                                    $output['inputs'][$input['name']][$mutation] = $vals[$input['name'].'_'.$mutation];
                                }
                            } else {
                                $output['inputs'][$input['name']] = $vals[$input['name']];
                            }
                            break;
                        
                        case 'select':
                            $definitionOptions = explode("|",$input['options']);
                            $inputVal = $vals[$input['name']];
                            $outputVal = $definitionOptions[$inputVal];
                            
                            $output['inputs'][$input['name']] = $outputVal;
                            break;
                        
                        case 'image':
                            if (isset($input['mutations'])) {
                                $output['inputs'][$input['name']] = array();
                                foreach(explode("|", $input['mutations']) as $mutation) {
                                    $output['inputs'][$input['name']][$mutation] = $vals[$input['name'].'_'.$mutation];
                                }
                            } else {
                                $output['inputs'][$input['name']] = $vals[$input['name']];
                            }
                            break;
                            
                    }
                }
            }
        }
        
        return json_encode($output);
    }

}
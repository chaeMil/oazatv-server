<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette,
 Nette\Database\Context,
 Model\FrontPageManager;

/**
 * Description of BlocksPresenter
 *
 * @author chaemil
 */
class BlocksPresenter extends BasePresenter {
    
    public $frontPageManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, 
            FrontPageManager $frontPageManager) {
        
        parent::__construct($container, $database);
        $this->frontPageManager = $frontPageManager;
    }
    
    private function loadBlockDefinition($name) {
        return file_get_contents(__DIR__ . '/../blocks/'.$name.'.json');
    }
    
    public function renderDefault() {
        
        $definitions = $this->frontPageManager->getBlocksDefinitions();
        $definitionsArray = array();        
        
        foreach($definitions as $definition) {
            
            $json2view = $this->loadBlockDefinition($definition['name']);
            $definitionsArray[] = array('name' => $definition['name'],
                                'definition' => $json2view);
            
        }
        
        $response = array();
        $response['definitions'] = $definitionsArray;
        
        $this->sendJson($response);
        
    }
}

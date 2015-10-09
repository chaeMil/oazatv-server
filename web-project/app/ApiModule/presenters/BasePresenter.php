<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette,
 App\ApiModule\JsonApi,
 Model\VideoManager;

/**
 * Description of MainPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class BasePresenter extends \Nette\Application\UI\Presenter {
    
    public $container;
    public $database;
    public $videoManager;
    public $lang;
    
    public function __construct(Nette\DI\Container $container,
            Nette\Database\Context $database,
            VideoManager $videoManager) {
        
        parent::__construct();
        
        $this->database = $database;
        $this->videoManager = $videoManager;
        $this->container = $container;
        
        $routerLang = $this->getParameter('locale');
        $this->setupLanguage($this->container, $routerLang);
    }
    
    public function setupLanguage($container, $lang = null) {
        if ($lang != null) {
            $this->lang = $lang;
        } else {
            $langs = array('cs', 'en'); // app supported languages
            $httpRequest = $container->getByType('Nette\Http\Request');
            $this->lang = $httpRequest->detectLanguage($langs);
        }
    }
}

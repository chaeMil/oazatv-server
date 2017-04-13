<?php

namespace App\FrontModule;

use App\AdminModule\ArchiveMenuPresenter;
use App\AdminModule\TagsManagerPresenter;
use App\StringUtils;
use Exception;
use Less_Parser;
use Model\AnalyticsManager;
use Model\ArchiveManager;
use Model\ArchiveMenuManager;
use Model\CategoriesManager;
use Model\FrontPageManager;
use Model\LiveStreamManager;
use Model\MyOazaManager;
use Model\PhotosManager;
use Model\PreachersManager;
use Model\SearchManager;
use Model\SongsManager;
use Model\TagsManager;
use Model\UserManager;
use Model\VideoManager;
use Nette,
App\Services\WebDir,
CssMin,
WebLoader,
Mexitek\PHPColors\Color;
use WebLoader\Nette\LoaderFactory;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {    
    
    public $database;
    public $lang;
    public $container;
    public $userManager;
    public $videoManager;
    public $photosManager;
    public $analyticsManager;
    public $categoriesManager;
    public $frontPageManager;
    public $liveStreamManager;
    public $songsManager;
    public $preachersManager;
    public $archiveManager;
    public $archiveMenuManager;
    public $webDir;
    public $neonAdapter;
    public $facebook;
    public $google;
    public $tagsManager;
    public $searchManager;
    public $myOazaManager;
    
    /** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;
    
    public function __construct(Nette\DI\Container $container, 
            Nette\Database\Context $database, UserManager $userManager,
            \Kdyby\Facebook\Facebook $facebook,
            \Kdyby\Google\Google $google,
            LoaderFactory $webLoader,
            LiveStreamManager $liveStreamManager,
            VideoManager $videoManager,
            SongsManager $songsManager,
            PhotosManager $photosManager,
            AnalyticsManager $analyticsManager,
            CategoriesManager $categoriesManager,
            FrontPageManager $frontPageManager,
            PreachersManager $preachersManager,
            ArchiveManager $archiveManager,
            ArchiveMenuManager $archiveMenuManager,
            TagsManager $tagsManager,
            SearchManager $searchManager,
            MyOazaManager $myOazaManager) {

        parent::__construct();
        $this->database = $database;
        $this->container = $container;
        $this->userManager = $userManager;
        $this->facebook = $facebook;
        $this->google = $google;
        $this->videoManager = $videoManager;
        $this->photosManager = $photosManager;
        $this->analyticsManager = $analyticsManager;
        $this->categoriesManager = $categoriesManager;
        $this->frontPageManager = $frontPageManager;
        $this->liveStreamManager = $liveStreamManager;
        $this->songsManager = $songsManager;
        $this->archiveManager = $archiveManager;
        $this->preachersManager = $preachersManager;
        $this->archiveMenuManager = $archiveMenuManager;
        $this->tagsManager =$tagsManager;
        $this->searchManager = $searchManager;
        $this->myOazaManager = $myOazaManager;
        $this->neonAdapter = new Nette\DI\Config\Adapters\NeonAdapter();
        
    }
    
    public function beforeRender() {
        parent::beforeRender();
        $routerLang = $this->getParameter('locale');
        $this->setupLanguage($this->container, $this->translator->getLocale());
        
        $this->registerCustomHelpers($this->template);
      
        $this->template->isCrOS = $this->detectChromeOS();

        if ($this->getUser()->isLoggedIn()) {
            $user = $this->getUser();
            $fronUser = [];

            $userFromDB = $this->userManager->getFrontUserFromDB($user->id);
            $frontUser['fromDB'] = $userFromDB;

            if ($userFromDB[UserManager::COLUMN_FB_TOKEN]) {
                $userFromFbAPI = $this->facebook->api('/me/',
                    null,
                    array("access_token" => $userFromDB[UserManager::COLUMN_FB_TOKEN],
                        'fields' => ['id',
                            'first_name',
                            'last_name',
                            'picture',
                            'email']));
                $frontUser['fromFB'] = $userFromFbAPI;
            }

            $this->template->frontUser = $frontUser;
        }
        
    }
    
    private function registerCustomHelpers($template) {
        //make HEX color from string
        $template->getLatte()->addFilter('makeColor', function ($s) {
            return '#'.substr(md5($s), 0, 6);
        });
        
        //lighten HEX color
        $template->getLatte()->addFilter('lighten', function ($s,
                $amount = Color::DEFAULT_ADJUST) {
            $color = new Color($s);
            return '#'.$color->lighten($amount);
        });
        
        //darken HEX color
        $template->getLatte()->addFilter('darken', function ($s,
                $amount = Color::DEFAULT_ADJUST) {
            $color = new Color($s);
            return '#'.$color->darken($amount);
        });
    }
    
    public function setupLanguage($container, $lang = null) {
        if ($lang != null) {
            $this->lang = $lang;
        } else {
            $langs = array('cs', 'en'); // app supported languages
            $httpRequest = $container->getByType('Nette\Http\Request');
            $this->lang = $httpRequest->detectLanguage($langs);
        }

        $this->template->lang = $this->lang;
    }
    
    public function injectWebDir(WebDir $webDir) {
        $this->webDir = $webDir;
    }
    
    private function getCssFilesToCompile() {
        $neon = $this->neonAdapter->load(__DIR__ . '/../../config/assets.neon');
        return $neon['assets']['css'];
    }
    
    private function getJsFilesToCompile() {
        $neon = $this->neonAdapter->load(__DIR__ . '/../../config/assets.neon');
        return $neon['assets']['js'];
    }
    
    protected function createComponentCss() {
        $basePath = $this->template->basePath;
        $filesToCompile = $this->getCssFilesToCompile();
        $recompile = false;

        $sourcesPath = 'webtemp/sources/';
        $sources = $this->webDir->getPath($basePath . '/' . $sourcesPath);
        if (!file_exists($sourcesPath)) {
            mkdir($sourcesPath);
            $recompile = true;
        }
        $files = new WebLoader\FileCollection($sources);

        foreach ($filesToCompile as $file) {
            if (StringUtils::endsWith($file, '.less')) {
                try {
                    $parser = new Less_Parser();
                    $inputFile = $this->webDir->getPath('') . $file;
                    $outputFile = $sourcesPath . basename($file, '.less') . '.css';
                    if ($recompile || DEV_MODE) {
                        $parser->parseFile($inputFile, $basePath);
                        $fileContent = $parser->getCss();
                        file_put_contents($outputFile, $fileContent);
                    }
                    $files->addFile($outputFile);
                } catch (Exception $e) {
                    $error_message = $e->getMessage();
                    dump($error_message);
                }
            }
            if (StringUtils::endsWith($file, '.css')) {
                $files->addFile($this->webDir->getPath('') . $file);
            }
        }

        $compiler = WebLoader\Compiler::createCssCompiler($files, $this->webDir->getPath('') . '/webtemp');

        $compiler->addFilter(function ($code) {
            return cssmin::minify($code);
        });

        $control = new WebLoader\Nette\CssLoader($compiler, $basePath . '/webtemp');
        $control->setMedia('screen');

        return $control;
    }
    
     protected function createComponentJs() {
        $dir = $this->webDir->getPath('');
        $jsFiles = $this->getJsFilesToCompile();
        $files = new WebLoader\FileCollection($dir);
        $files->addFiles($jsFiles);

        $compiler = WebLoader\Compiler::createJsCompiler($files, $dir . '/webtemp');

        $control = new WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath. '/webtemp');

        return $control;
    }
    
    public function bootstrapFormRendering($form) {
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-9';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
        // make form and controls compatible with Twitter Bootstrap
        $form->getElementPrototype()->class('form-horizontal');
        foreach ($form->getControls() as $control) {
                if ($control instanceof Controls\Button) {
                        $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                        $usedPrimary = TRUE;
                } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
                        $control->getControlPrototype()->addClass('form-control');
                } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
                        $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
                }
        }
    }
  
  public function detectChromeOS() {
    return strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false;
  }

}

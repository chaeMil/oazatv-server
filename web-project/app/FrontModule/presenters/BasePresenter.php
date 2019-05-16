<?php

namespace App\FrontModule;

use App\StringUtils;
use Exception;
use Less_Parser;
use Nette,
App\Services\WebDir,
CssMin,
WebLoader,
Mexitek\PHPColors\Color;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {    
    
    public $database;
    public $lang;
    public $container;
    private $webDir;
    private $neonAdapter;
    
    /** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;
    
    public function __construct(Nette\DI\Container $container, 
            Nette\Database\Context $database) {
        parent::__construct();
        $this->database = $database;
        $this->container = $container;
        $this->neonAdapter = new Nette\DI\Config\Adapters\NeonAdapter();
        
    }
    
    public function beforeRender() {
        parent::beforeRender();
        $routerLang = $this->getParameter('locale');
        $this->setupLanguage($this->container, $this->translator->getLocale());
        
        $this->registerCustomHelpers($this->template);
      
        $this->template->isCrOS = $this->detectChromeOS();
        
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

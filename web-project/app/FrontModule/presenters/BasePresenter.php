<?php

namespace App\FrontModule;

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
        
    }
    
    private function registerCustomHelpers($template) {
        //make HEX color from string
        $template->registerHelper('makeColor', function ($s) {
            return '#'.substr(md5($s), 0, 6);
        });
        
        //lighten HEX color
        $template->registerHelper('lighten', function ($s, 
                $amount = Color::DEFAULT_ADJUST) {
            $color = new Color($s);
            return '#'.$color->lighten($amount);
        });
        
        //darken HEX color
        $template->registerHelper('darken', function ($s, 
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
        $dir = $this->webDir->getPath('');
        $cssFiles = $this->getCssFilesToCompile();
        $files = new WebLoader\FileCollection($dir);
        $files->addFiles($cssFiles);

        $compiler = WebLoader\Compiler::createCssCompiler($files, $dir . '/webtemp');

        $compiler->addFilter(function ($code) {
            return cssmin::minify($code);
        });

        $control = new WebLoader\Nette\CssLoader($compiler, $this->template->basePath. '/webtemp');
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

}

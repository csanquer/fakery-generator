<?php

use Assetic\Asset\AssetCache;
use Assetic\Cache\ConfigCache;
use Assetic\Cache\FilesystemCache;
use Assetic\Extension\Twig\TwigFormulaLoader;
use Assetic\Factory\LazyAssetManager;
use Assetic\Factory\Loader\CachedFormulaLoader;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter\CssRewriteFilter;
use Assetic\Filter\JSMinFilter;
use Assetic\Filter\LessphpFilter;
use Assetic\FilterManager;
use CSanquer\Silex\Tools\Provider\SkeletonProvider;
use Herrera\Wise\WiseServiceProvider;
use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;
use SilexAssetic\AsseticServiceProvider;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Loader\MoFileLoader;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Validator\Mapping\Cache\ApcCache;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\YamlFilesLoader;

//// get environment constants or set default
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('SILEX_ENV')) {
    define('SILEX_ENV', 'dev');
}

if (!defined('SILEX_DEBUG')) {
    define('SILEX_DEBUG', true);
}

if (!defined('UMASK')) {
    define('UMASK', 0002);
}

/**
 * @var Application new Silex application
 */
$app = new Application();

$fs = new Filesystem();

$app->register(
    new SkeletonProvider(),
    array(
        'app_dir' => __DIR__,
        'umask' => UMASK, 
        'env' => SILEX_ENV,
        'debug' => SILEX_DEBUG,
        'cached_dirs' => array(),
    )
);

// get configs
$configFiles = array(
    'parameters',
    'parameters_'.$app['env'],
);

$configFormats = array(
    'php',
//    'ini',
//    'json',
    'yml',
//    'xml',
);

$app->register(
    new WiseServiceProvider(),
    array(
        'wise.path' => $app['config_dir'],
        'wise.cache_dir' => $app['cache_dir'].DS.'config',
        'wise.options' => array(
            'parameters' => $app
        )
    )
);

$rawConfig = array();
foreach ($configFiles as $configFile) {
    $conf = array();
    foreach ($configFormats as $configFormat) {
        if ($fs->exists($app['config_dir'].DS.$configFile.'.'.$configFormat)) {
            $conf = $app['wise']->load($configFile.'.'.$configFormat);
        }
    }
    
    if (!empty($conf)) {
        $rawConfig = array_replace_recursive($rawConfig, $conf);
    }
}

$config = $rawConfig['parameters'];

/*
 * add service providers
 */

//add symfony2 translation (needed for twig + forms)
$app->register(new TranslationServiceProvider(), array(
    'locale_fallback' => empty($config['i18n.locale_fallback']) ? 'en' : $config['i18n.locale_fallback'],
));

// add translation files
$app['translator'] = $app->share($app->extend('translator', function($translator, $app) use ($config, $fs) {
    $usedExt = array();
    
    $finder = new Finder();
    $finder->files()->in($app['translations_dir']);
    
    foreach ($finder as $file) {
        if (preg_match('/^(.+)\.([^\.]+)\.$/', $file->getBasename($file->getExtension()), $matches)) {
            if (!in_array($file->getExtension(), $usedExt)) {
                $usedExt[] = $file->getExtension();
                $loader = null;
                
                switch ($file->getExtension()) {
                    case 'yml':
                        $loader = new YamlFileLoader();
                        break;
                    case 'php':
                        $loader = new PhpFileLoader();
                        break;
                    case 'mo':
                        $loader = new MoFileLoader();
                        break;
                    case 'po':
                        $loader = new PoFileLoader();
                        break;
                    case 'xliff':
                    default:
                        $loader = new XliffFileLoader();
                        break;
                }

                if (isset($loader)) {
                    $translator->addLoader($file->getExtension(), $loader);
                }
            }

            $translator->addResource($file->getExtension(), $file->getRealPath(), $matches[2], $matches[1]);
        }
    }
    
    return $translator;
}));

//add http cache
//$app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
//    'http_cache.cache_dir' => $app['cache_dir'].DS.'http',
//));

//add url generator
$app->register(new UrlGeneratorServiceProvider());

//add symfony2 sessions
$app->register(new SessionServiceProvider());

//add symfony2 forms and validators
$app->register(new ValidatorServiceProvider());

$app['validator.mapping.class_metadata_factory'] = new ClassMetadataFactory(
    new YamlFilesLoader([$app['root_dir'].'/src/CSanquer/FakeryGenerator/Resources/Config/validation.yml']),
    new ApcCache('fakery_generator')
);

//add service controller provider
$app->register(new ServiceControllerServiceProvider());

//symfony2 form provider, must be registered before twig
$app->register(new FormServiceProvider(), array(
    'form.secret' => $config['form_secret'],
));

// add twig templating
$app->register(new TwigServiceProvider(), array(
    'twig.path' => array(
        __DIR__.'/views',
    ),
//        'twig.templates' => array(),
    'twig.options' => array(
        'debug' => $app['debug'],
        'cache' => $app['cache_dir'].DS.'twig',
        'auto_reload' => $app['env'] !== 'prod',
    ),
    'twig.form.templates' => array(
        'form_div_layout.html.twig', // Twig SF2 original form theme
        // add your custom form theme
    ),
));

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) use ($config) {
    // add twig custom globals, filters, tags, ...
    if (!empty($config['twig.variables'])) {
        foreach ($config['twig.variables'] as $name => $value) {
            $twig->addGlobal($name, $value);
        }
    }
    
    // add twig extensions
    $twig->addExtension(new Twig_Extensions_Extension_Text());
    
    if (extension_loaded('intl')) {
        $twig->addExtension(new Twig_Extensions_Extension_Intl());
    }
    
    return $twig;
}));

// add Assetic
$app->register(new AsseticServiceProvider(), array(
    'assetic.path_to_web' => $app['web_dir'],
    'assetic.options' => array(
    'debug' => $app['debug'],
    'auto_dump_assets' => true,
        'formulae_cache_dir' => $app['cache_dir'].DS.'assetic'.DS.'formulae',
                    ),
    'assetic.filters' => $app->protect(function(FilterManager $fm) {
        $fm->set('lessphp', new LessphpFilter());
        $fm->set('cssrewrite', new CssRewriteFilter());
        $fm->set('cssmin', new CssMinFilter());
        $fm->set('jsmin', new JSMinFilter());
    }),
        ));

$app['assetic.lazy_asset_manager'] = $app->share(
    $app->extend('assetic.lazy_asset_manager', function (LazyAssetManager $am, $app) {
        $am->setLoader('twig', new CachedFormulaLoader(
            new TwigFormulaLoader($app['twig']), 
            new ConfigCache($app['cache_dir'].DS.'assetic'.DS.'twig')
        ));
        
        if ($app['assetic.options']['formulae_cache_dir'] !== null && $app['assetic.options']['debug'] !== true) {
            foreach ($am->getNames() as $name) {
                $am->set($name, new AssetCache(
                    $am->get($name),
                    new FilesystemCache($app['assetic.options']['formulae_cache_dir'])
                ));
            }
        }
        
        return $am;
    })
);  
    
if ('dev' == SILEX_ENV) {
    // logs
    $app->register(new MonologServiceProvider(), array(
        'monolog.logfile' => $app['log_dir'].DS.'silex_dev.log',
    ));

    // symfony2 web profier
    $app->register($p = new WebProfilerServiceProvider(), array(
        'profiler.cache_dir' => $app['cache_dir'].DS.'profiler',
    ));

    $app->mount('/_profiler', $p);
}

//custom providers and services
$app->register(new \CSanquer\FakeryGenerator\Provider\FakeryGeneratorProvider());

return $app;
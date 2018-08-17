<?php
namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Common\Uri;
use Grav\Common\Inflector;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;
use Grav\Framework\Cache\Adapter\SessionCache;

require __DIR__ . '/vendor/autoload.php';
use Leafo\ScssPhp\Compiler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

require 'Data.php';
use Grav\Plugin\AdminStylesPlugin\Data;

/**
 * Adds multiple custom styles for the Admin-plugin interface
 *
 * Class AdminStylesPlugin
 * 
 * @package Grav\Plugin
 * 
 * @return mixed Style-replacements in Admin-plugin
 * 
 * @license MIT License by Ole Vik
 */
class AdminStylesPlugin extends Plugin
{
    /**
     * Route for Ajax-Endpoint
     * @var string
     */
    protected $compileRoute = '/adminstyles-compile';

    protected $returnRoute = '/plugins/adminstyles';

    protected $messages = 'AdminStylesPluginMessages.txt';

    /**
     * Path for preview-page
     * @var string
     */
    protected $route = 'themepreview';

    /**
     * Initialize plugin and subsequent events
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Register events and route with Grav
     * 
     * @return void
     */
    public function onPluginsInitialized()
    {
        if (!$this->isAdmin()) {
            return;
        }

        $config = $this->config();
        $uri = $this->grav['uri'];
        if ($config['enabled']) {
            $cache = new SessionCache();
            if (!empty($cache->doGet($this->messages, false))) {
                $messages = $this->grav['messages'];
                $messages->add($cache->doGet($this->messages, false), 'note');
                $cache->doDelete($this->messages);
            }
            $this->enable(
                [
                    'onAdminMenu' => ['onAdminMenu', 0],
                    'onTwigExtensions' => ['onTwigExtensions', 0],
                    'onPageInitialized' => ['onPageInitialized', 0],
                    'onPagesInitialized' => ['pluginEndpoint', 0],
                    'onAdminTwigTemplatePaths' => ['onAdminTwigTemplatePaths', -1]
                ]
            );
            if (strpos($uri->path(), $this->config->get('plugins.admin.route') . '/' . $this->route) === false) {
                return;
            }
        }
    }

    /**
     * Declare config from plugin-config
     * 
     * @return array Plugin configuration
     */
    public function config()
    {
        $pluginsobject = (array) $this->config->get('plugins');
        if (isset($pluginsobject) && $pluginsobject['adminstyles']['enabled']) {
            $config = $pluginsobject['adminstyles'];
        } else {
            return false;
        }
        return $config;
    }

    /**
     * Get type-configuration from plugin
     * 
     * @return string Type-field from config
     */
    public static function getConfigType()
    {
        return Grav::instance()['config']->get('plugins.adminstyles.type');
    }

    /**
     * Get styles
     * 
     * @return array Associative array of styles
     */
    public static function getStyles()
    {
        $inflector = new Inflector();
        $config = Grav::instance()['config']->get('plugins.adminstyles');
        $styles = array();
        if (isset($config['custom_styles']) && !empty($config['custom_styles'])) {
            foreach ($config['custom_styles'] as $custom) {
                $key = $inflector->underscorize($custom['name']);
                $styles[$key] = $custom['name'];
            }
        }
        $defaults = $config['styles'];
        if (!empty($defaults)) {
            foreach ($defaults as $default) {
                $name = $inflector->titleize($default);
                $styles[$default] = $name;
            }
        }
        return $styles;
    }

    /**
     * Push styles to Admin-plugin via Assets Manager
     * 
     * @return void
     */
    public function onPageInitialized()
    {
        $uri = $this->grav['uri'];
        $assets = $this->grav['assets'];
        $config = $this->config();
        if ($config['current']) {
            $current = self::fileFinder(
                $config['current'],
                '.css',
                'user://data/adminstyles/styles/css',
                'plugin://adminstyles/styles/css'
            );
            $assets->addCss($current, 1);
            $assets->addCss('plugin://adminstyles/styles/adminstyles.css', 1);
        }
        if ($uri->path() == $this->config->get('plugins.admin.route') . '/' . $this->route) {
            $assets->AddJs('plugin://adminstyles/js/anchor.min.js', 10, false, 'async defer');
            $assets->AddJs('plugin://adminstyles/js/preview-anchors.js', 10, false, 'async defer');
        }
    }

    /**
     * Ajax-Endpoint to handle File-operations
     * 
     * @return string Prints state of operation
     * 
     * @throws \Exception
     */
    public function pluginEndpoint()
    {
        $time_start = microtime(true);
        $uri = $this->grav['uri'];
        $config = $this->grav['config'];
        $locator = $this->grav['locator'];
        $assets = $locator ->findResource('plugin://adminstyles/styles/scss', true) . '/';
        $dest = $locator->findResource('user://data', true);
        $fileSystem = new Filesystem();
        $inflector = new Inflector();
        $cache = new SessionCache();
        if (strpos($uri->path(), $this->compileRoute) === false or !isset($_GET['compile'])) {
            return;
        }
        $return = array($_GET['compile'] => '');
        $root = $uri->rootUrl(true);
        $adminRoute = $config->get('plugins.admin.route');
        $returnLocation = $root . $adminRoute . $this->returnRoute;
        foreach ($config->get('plugins.adminstyles.custom_styles') as $style) {
            $name = $inflector->underscorize($style['name']);
            $dest_css = $dest . '/adminstyles/styles/css/' . $name . '.css';
            $dest_map = $dest . '/adminstyles/styles/css/' . $name . '.css.map';
            if ($name == $_GET['compile']) {
                $data = Data::prepend() . Data::variables($style) . Data::append();
                // Currently not working
                /*$scss->setSourceMap(Compiler::SOURCE_MAP_FILE);
                $scss->setSourceMapOptions(
                    array(
                        'sourceMapWriteTo' => $dest_map,
                        'sourceMapURL' => $dest_map,
                        'sourceMapFilename' => $dest_css,
                        'sourceRoot' => '/'
                    )
                );*/
                try {
                    $scss = new Compiler();
                    $scss->setImportPaths($assets);
                    $scss->setFormatter('Leafo\ScssPhp\Formatter\Crunched');
                    $css = $scss->compile($data);
                } catch (\Exception $e) {
                    $message = $cache->doGet(
                        $this->messages,
                        $cache->doSet($this->messages, $e, 3600)
                    );
                    header('Location: ' . $returnLocation, true, 307);
                    exit();
                }
                try {
                    $fileSystem->dumpFile($dest_css, $css);
                } catch (IOExceptionInterface $e) {
                    $error = 'Error creating ' . $e->getPath();
                    $message = $cache->doGet(
                        $this->messages,
                        $cache->doSet($this->messages, $error, 3600)
                    );
                    header('Location: ' . $returnLocation, true, 307);
                    exit();
                }
            }
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            $success = 'Compiled ' . $inflector->titleize($_GET['compile']) . ' in ' . round($time, 2) . ' seconds';
            $message = $cache->doGet(
                $this->messages,
                $cache->doSet($this->messages, 'Compiled ' . $success, 3600)
            );

        }
        header('Location: ' . $returnLocation, true, 307);
        exit();
    }

    /**
     * Search for a file in multiple locations
     *
     * @param string $file         Filename.
     * @param string $ext          File extension.
     * @param array  ...$locations List of paths.
     * 
     * @return string
     */
    public static function fileFinder($file, $ext, ...$locations)
    {
        $return = false;
        foreach ($locations as $location) {
            if (file_exists($location . '/' . $file . $ext)) {
                $return = $location . '/' . $file . $ext;
                break;
            }
        }
        return $return;
    }

    /**
     * Register templates and page
     * 
     * @param RocketTheme\Toolbox\Event\Event $event
     * 
     * @return void
     */
    public function onAdminTwigTemplatePaths($event)
    {
        $event['paths'] = [__DIR__ . '/admin/themes/grav/templates'];
    }

    /**
     * Register link to page in admin menu
     * 
     * @return void
     */
    public function onAdminMenu()
    {
        $config = $this->config();
        if ($config['preview']) {
            $this->grav['twig']->plugins_hooked_nav['ADMINSTYLES.PREVIEW.TITLE'] = ['route' => $this->route, 'icon' => 'fa-th-list'];
        }
    }

    /**
     * Add Twig Extensions
     *
     * @return void
     */
    public function onTwigExtensions()
    {
        include_once __DIR__ . '/twig/CallStaticExtension.php';
        $this->grav['twig']->twig->addExtension(new CallStaticTwigExtension());
        include_once __DIR__ . '/twig/FileFinderExtension.php';
        $this->grav['twig']->twig->addExtension(new FileFinderTwigExtension());
        include_once __DIR__ . '/twig/UrlEncodeExtension.php';
        $this->grav['twig']->twig->addExtension(new UrlEncodeTwigExtension());
        include_once __DIR__ . '/twig/UrlDecodeExtension.php';
        $this->grav['twig']->twig->addExtension(new UrlDecodeTwigExtension());
    }
}

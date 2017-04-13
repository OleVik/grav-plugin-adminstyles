<?php
namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Common\Uri;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;
 
/**
 * Adds multiple custom styles for the Admin-plugin interface
 *
 * Class AdminStylesPlugin
 * @package Grav\Plugin
 * @return mixed Style-replacements in Admin-plugin
 * @license MIT License by Ole Vik
 */
class AdminStylesPlugin extends Plugin
{

    /**
     * Path for preview-page
     * @var string
     */
    protected $route = 'themepreview';

    /**
     * Initialize plugin and subsequent events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Declare config from plugin-config
     * @return array Plugin configuration
     */
    public function config()
    {
        $pluginsobject = (array) $this->config->get('plugins');
        if (isset($pluginsobject) && $pluginsobject['adminstyles']['enabled']) {
            $config = $pluginsobject['adminstyles'];
        } else {
            return;
        }
        return $config;
    }

    /**
     * Get type-configuration from plugin
     * @return string Type-field from config
     */
    public static function getConfigType()
    {
        return Grav::instance()['config']->get('plugins.adminstyles.type');
    }

    /**
     * Register events and route with Grav
     * @return void
     */
    public function onPluginsInitialized()
    {
        /* Check if Admin-interface */
        if (!$this->isAdmin()) {
            return;
        }

        $config = $this->config();
        if ($config['enabled']) {
            $uri = $this->grav['uri'];
            $this->enable([
                'onAdminMenu' => ['onAdminMenu', 0],
                'onPageInitialized' => ['onPageInitialized', 0],
                'onAdminTwigTemplatePaths' => ['onAdminTwigTemplatePaths', -1]
            ]);
            if (strpos($uri->path(), $this->config->get('plugins.admin.route') . '/' . $this->route) === false) {
                return;
            }
        }
    }

    /**
     * Push styles to Admin-plugin via Assets Manager
     * @return void
     */
    public function onPageInitialized()
    {
        $uri = $this->grav['uri'];
        $assets = $this->grav['assets'];
        $config = $this->config();
        if ($config['current']) {
            $assets->addCss('plugin://adminstyles/styles/css/' . $config['current'] . '.css', 1);
            $assets->addCss('plugin://adminstyles/styles/adminstyles.css', 1);
        }
        if ($uri->path() == $this->config->get('plugins.admin.route') . '/' . $this->route) {
            $assets->AddJs('plugin://adminstyles/js/anchor.min.js', 10, false, 'async defer');
            $assets->AddJs('plugin://adminstyles/js/preview-anchors.js', 10, false, 'async defer');
        }
    }

    /**
     * Register templates and page
     * @param RocketTheme\Toolbox\Event\Event $event
     * @return void
     */
    public function onAdminTwigTemplatePaths($event)
    {
        $event['paths'] = [__DIR__ . '/admin/themes/grav/templates'];
    }

    /**
     * Register link to page in admin menu
     * @return void
     */
    public function onAdminMenu()
    {
        $config = $this->config();
        if ($config['preview']) {
            $this->grav['twig']->plugins_hooked_nav['ADMINSTYLES.PREVIEW.TITLE'] = ['route' => $this->route, 'icon' => 'fa-th-list'];
        }
    }
}

<?php
namespace Grav\Plugin;

use Grav\Common\Data;
use Grav\Common\Plugin;
use Grav\Common\Grav;
use Grav\Common\Uri;
use Grav\Common\Taxonomy;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;

class AdminStylesPlugin extends Plugin
{
    protected $route = 'themepreview';

    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onAdminTwigTemplatePaths' => ['onAdminTwigTemplatePaths', 0]
        ];
    }
    public function onPluginsInitialized()
    {
        $config = (array) $this->config->get('plugins');
        if ($this->isAdmin() && $config['adminstyles']['enabled']) {
            $uri = $this->grav['uri'];
            $this->enable([
                'onAdminMenu' => ['onAdminMenu', 0],
                'onPageInitialized' => ['onPageInitialized', 0]
            ]);
            if (strpos($uri->path(), $this->config->get('plugins.admin.route') . '/' . $this->route) === false) {
                return;
            }
        }
    }
    public function onPageInitialized()
    {
        $uri = $this->grav['uri'];
        $assets = $this->grav['assets'];
        $config = (array) $this->config->get('plugins');
        if ($config['adminstyles']['current']) {
            $assets->addCss('plugin://adminstyles/styles/css/' . $config['adminstyles']['current'] . '.css', 1);
            $assets->addCss('plugin://adminstyles/styles/adminstyles.css', 1);
        }
        if ($uri->path() == $this->config->get('plugins.admin.route') . '/' . $this->route) {
            $assets->AddJs('plugin://adminstyles/js/anchor.min.js', 10, false, 'async defer');
            $assets->AddJs('plugin://adminstyles/js/preview-anchors.js', 10, false, 'async defer');
        }
    }
    public function onAdminTwigTemplatePaths($event)
    {
        $event['paths'] = [__DIR__ . '/admin/templates'];
    }
    public function onAdminMenu()
    {
        $config = (array) $this->config->get('plugins');
        if ($config['adminstyles']['preview']) {
            $this->grav['twig']->plugins_hooked_nav['ADMINSTYLES.PREVIEW.TITLE'] = ['route' => $this->route, 'icon' => 'fa-th-list'];
        }
    }
}

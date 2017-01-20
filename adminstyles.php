<?php
namespace Grav\Plugin;

use Grav\Common\Data;
use Grav\Common\Plugin;
use Grav\Common\Grav;
use Grav\Common\Uri;
use Grav\Common\Taxonomy;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;

class AdminStylesPlugin extends Plugin {
	
	protected $route = 'themepreview';
	
    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }
    public function onPluginsInitialized() {
        if ($this->isAdmin()) {
            $this->initializeAdmin();
        }
    }
    public function initializeAdmin() {
        $uri = $this->grav['uri'];
        $this->enable([
            'onTwigTemplatePaths' => ['onTwigAdminTemplatePaths', 0],
            'onAdminMenu' => ['onAdminMenu', 0],
            'onPageInitialized' => ['onPageInitialized', 0]
        ]);
        if (strpos($uri->path(), $this->config->get('plugins.admin.route') . '/' . $this->route) === false) {
            return;
        }
    }
    public function onPageInitialized(Event $event) {
		if ($this->isAdmin()) {
			$grav = $this->grav;
			$uri = $this->grav['uri'];
			$assets = $this->grav['assets'];
			$pluginsobject = (array) $this->config->get('plugins');
			if (isset($pluginsobject['adminstyles'])) {
				if ($pluginsobject['adminstyles']['enabled'] && $pluginsobject['adminstyles']['current']) {
					$assets->addCss('plugin://adminstyles/styles/css/' . $pluginsobject['adminstyles']['current'] . '.css', 1);
					$assets->addCss('plugin://adminstyles/styles/adminstyles.css', 1);
				}
				if ($pluginsobject['adminstyles']['enabled'] && $uri->path() == $this->config->get('plugins.admin.route') . '/' . $this->route) {
					$assets->AddJs('plugin://adminstyles/js/anchor.min.js', 10, false, 'async defer');
					$assets->AddJs('plugin://adminstyles/js/preview-anchors.js', 10, false, 'async defer');
				}
			}
		}
    }
	public function onTwigAdminTemplatePaths() {
		$pluginsobject = (array) $this->config->get('plugins');
		if (isset($pluginsobject['adminstyles'])) {
			if ($pluginsobject['adminstyles']['enabled'] && $pluginsobject['adminstyles']['preview']) {
				$this->grav['twig']->twig_paths[] = __DIR__ . '/admin/templates';
			}
		}
	}
	public function onAdminMenu() {
		$pluginsobject = (array) $this->config->get('plugins');
		if (isset($pluginsobject['adminstyles'])) {
			if ($pluginsobject['adminstyles']['enabled'] && $pluginsobject['adminstyles']['preview']) {
				$this->grav['twig']->plugins_hooked_nav['ADMINSTYLES.PREVIEW.TITLE'] = ['route' => $this->route, 'icon' => 'fa-th-list'];
			}
		}
	}
}
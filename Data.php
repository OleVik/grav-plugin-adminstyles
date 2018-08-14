<?php
namespace Grav\Plugin\AdminStylesPlugin;

require __DIR__ . '/vendor/autoload.php';
use MatthiasMullie\Minify;

/**
 * Compile SCSS
 */
class Data
{
    /**
     * Load Third Party Libraries
     *
     * @return string
     */
    public static function prepend()
    {
        $return = '@import "base/configuration/template/base";';
        $return .= '@import "base/template/modules/bourbon_essentials";';
        $return .= '@import "base/template/modules/buttons";';
        return $return;
    }

    /**
     * Resolve variables
     *
     * @param array $params Style scheme variables
     * @return string
     */
    public static function variables($params)
    {
        $return = '$theme-main: ' . $params['main'] . ';';
        $return .= '$theme-secondary: ' . $params['secondary'] . ';';
        $return .= '$theme-main-alt: ' . $params['main_alt'] . ';';
        $return .= '$theme-secondary-alt: ' . $params['secondary_alt'] . ';';
        $return .= '$theme-text: ' . $params['text'] . ';';
        $return .= '$theme-text-alt: ' . $params['text_alt'] . ';';
        $return .= '$theme-background: ' . $params['background'] . ';';
        if ($params['override']) {
            $minifier = new Minify\CSS();
            $minifier->add($params['override']);
            $return .= $minifier->minify();
        }

        $return .= '$logo-bg: darken($theme-main-alt, 8%) !default;';
        $return .= '$logo-link: $theme-text-alt !default;';
        $return .= '$nav-bg: $theme-main-alt !default;';
        $return .= '$nav-text: $theme-text-alt !default;';
        $return .= '$nav-link: darken($theme-text-alt, 8%) !default;';
        $return .= '$nav-selected-bg: darken($theme-main-alt, 8%) !default;';
        $return .= '$nav-selected-link: $theme-text-alt !default;';
        $return .= '$nav-hover-bg: $theme-main-alt !default;';
        $return .= '$nav-hover-link: $theme-text-alt !default;';
        $return .= '$toolbar-bg: $theme-main !default;';
        $return .= '$toolbar-text: $theme-text-alt !default;';
        $return .= '$page-bg: $theme-background !default;';
        $return .= '$page-text: $theme-secondary-alt !default;';
        $return .= '$page-link: darken($theme-secondary-alt, 15%) !default;';
        $return .= '$content-bg: $theme-text-alt !default;';
        $return .= '$content-text: $theme-secondary-alt !default;';
        $return .= '$content-link: darken($theme-secondary-alt, 15%) !default;';
        $return .= '$content-link2: darken(saturate($theme-main, 75%), 25%) !default;';
        $return .= '$content-header: $theme-secondary-alt !default;';
        $return .= '$content-tabs-bg: $theme-secondary-alt !default;';
        $return .= '$content-tabs-text: $theme-text-alt !default;';
        $return .= '$button-bg: $theme-secondary-alt !default;';
        $return .= '$button-text: $theme-text-alt !default;';
        $return .= '$notice-bg: $theme-secondary-alt !default;';
        $return .= '$notice-text: $theme-text-alt !default;';
        $return .= '$update-bg: $theme-main-alt !default;';
        $return .= '$update-text: $theme-text-alt !default;';
        $return .= '$critical-bg: darken(saturate($theme-main, 75%), 20%) !default;';
        $return .= '$critical-text: $theme-text-alt !default;';
        $return .= '$primary-accent: primary !default;';
        $return .= '$secondary-accent: secondary !default;';
        $return .= '$tertiary-accent: tertiary !default;';
        $return .= '$primary-accent-bg: $theme-main;';
        $return .= '$primary-accent-fg: $theme-text-alt;';
        $return .= '$secondary-accent-bg: $theme-secondary;';
        $return .= '$secondary-accent-fg: $theme-text-alt;';
        $return .= '$tertiary-accent-bg: $theme-main-alt;';
        $return .= '$tertiary-accent-fg: $theme-text-alt;';
        return $return;
    }

    /**
     * Customizations and variable-parsing
     *
     * @return string
     */
    public static function append()
    {
        $return = '.pages-list .page-icon {color: saturate($theme-main, 25%);}';
        $return .= '.pages-list .page-icon.not-routable {color: saturate($theme-secondary, 25%);}';
        $return .= '.pages-list .page-icon.not-visible {color: lighten($theme-main-alt, 15%);}';
        $return .= '.pages-list .page-icon.modular {color: adjust-hue(saturate($theme-main, 25%), 180deg);}';
        $return .= '@import "base/preset.scss";';
        return $return;
    }
}
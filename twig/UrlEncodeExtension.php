<?php
namespace Grav\Plugin;

class UrlEncodeTwigExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'UrlEncoder';
    }
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('url_encode', [$this, 'UrlEncoderFilter'])
        ];
    }
    public function UrlEncoderFilter( $url )
    {
        return urlencode($url);
    }
}
<?php

namespace Laraish\WpSupport;

class AdminPage
{

    private static function getClassInstance($function)
    {
        $class = strtok($function, '@');
        return new $class();
    }

    private static function getMethodName($function)
    {
        if (($pos = strpos($function, '@')) !== FALSE) {
            return substr($function, $pos + 1);
        }
        return 'index';
    }

    public static function register($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', int $position = null)
    {
        add_action('admin_menu', function () use ($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position) {
            $instance = AdminPage::getClassInstance($function);
            $method   = AdminPage::getMethodName($function);
            add_menu_page($page_title, $menu_title, $capability, $menu_slug, [$instance, $method], $icon_url, $position);
        });
    }

}

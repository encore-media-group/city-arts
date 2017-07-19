<?php

/* Horrible hack
* named something random so as not to conflict and can be accessed using the factory method at the
* bottom of this file
 *
 */
global $services_9490340238;
$services_9490340238 = new ResponsiveMenuPro\Container\Container;

$services_9490340238['database'] = function($c) {
    global $wpdb;
    return new ResponsiveMenuPro\Database\Database($wpdb);
};

$services_9490340238['option_manager'] = function($c) {
    return new ResponsiveMenuPro\Management\OptionManager(
        $c['database'],
        get_responsive_menu_pro_default_options()
    );
};

$services_9490340238['twig'] = function($c) {
    include_once dirname(__FILE__) . '/twig.php';
    return $twig;
};

$services_9490340238['view'] = function($c) {
    return new ResponsiveMenuPro\View\View($c['twig']);
};

$services_9490340238['admin_controller'] = function($c) {
    return new ResponsiveMenuPro\Controllers\AdminController($c['option_manager'], $c['view']);
};

$services_9490340238['front_controller'] = function($c) {
    return new ResponsiveMenuPro\Controllers\FrontController($c['option_manager'], $c['view']);
};

function get_responsive_menu_pro_service($service) {
    global $services_9490340238;
    return $services_9490340238[$service];
}
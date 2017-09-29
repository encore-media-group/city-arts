<?php

namespace ResponsiveMenuPro\Controllers;
use ResponsiveMenuPro\Collections\OptionsCollection;
use ResponsiveMenuPro\View\View;
use ResponsiveMenuPro\Management\OptionManager;
use ResponsiveMenuPro\Formatters\Minifier;

class FrontController {

    public function __construct(OptionManager $manager, View $view) {
        $this->manager = $manager;
        $this->view = $view;
    }

    public function index() {
        $options = $this->manager->all();
        $this->buildFrontEnd($options);
    }

    public function preview($options) {
        $options['external_files'] = 'off';
        $collection = $this->manager->buildFromArray($options);
        $this->buildFrontEnd($collection);
    }

    private function buildFrontEnd(OptionsCollection $options) {
        if($options['mobile_only'] == 'on' && !wp_is_mobile())
            return;

        $font_icons = $options->usesFontIcons();

        add_action('wp_enqueue_scripts', function() use ($font_icons, $options) {

            if($font_icons):
                if (in_array('font-awesome', $font_icons) && !$options['remove_fontawesome'])
                    wp_enqueue_script('responsive-menu-pro-font-awesome', 'https://use.fontawesome.com/b6bedb3084.js', null, null);

                if (in_array('glyphicon', $font_icons) && !$options['remove_bootstrap']):
                    wp_enqueue_script('responsive-menu-pro-bootstrap-js', plugin_dir_url(dirname(dirname(__FILE__))) . 'public/js/admin/bootstrap.js', null, null);
                    wp_enqueue_style('responsive-menu-pro-bootstrap-css', plugin_dir_url(dirname(dirname(__FILE__))) . 'public/css/admin/bootstrap.css', null, null);
                endif;
            endif;

            if($options['enable_touch_gestures'])
                wp_enqueue_script('responsive-menu-pro-jquery-touchswipe', plugin_dir_url(dirname(dirname(__FILE__))) . 'public/js/jquery.touchSwipe.min.js', null, null);

        });

        add_filter('body_class', function($classes) use($options) {
            $classes[] = 'responsive-menu-pro-' . $options['animation_type'] . '-' . $options['menu_appear_from'];
            return $classes;
        });

        if($options['show_menu_on_page_load']):
            add_filter('language_attributes', function($output) {
                return $output . ' class="responsive-menu-pro-open"';
            });
        endif;

        if($options['external_files'] == 'on'):
            add_action('wp_enqueue_scripts', function() use($options) {
                $css_file = plugins_url() . '/responsive-menu-pro-data/css/responsive-menu-pro-' . get_current_blog_id() . '.css';
                $js_file = plugins_url() . '/responsive-menu-pro-data/js/responsive-menu-pro-' . get_current_blog_id() . '.js';
                wp_enqueue_style('responsive-menu-pro', $css_file, null, false);
                wp_enqueue_script('responsive-menu-pro', $js_file, ['jquery'], false, $options['scripts_in_footer'] == 'on' ? true : false);
            });
        else:
            add_action('wp_head', function() use($options)  {
                $css_data = $this->view->render('css/app.css.twig', ['options' => $options]);
                if($options['minify_scripts'] == 'on')
                    $css_data = Minifier::minify($css_data);

                echo '<style>' . $css_data . '</style>';
            }, 100);

            add_action($options['scripts_in_footer'] == 'on' ? 'wp_footer' : 'wp_head', function() use($options) {
                $js_data = $this->view->render('js/app.js.twig', ['options' => $options]);
                if($options['minify_scripts'] == 'on')
                    $js_data = Minifier::minify($js_data);

                echo '<script>' . $js_data . '</script>';
            }, 100);
        endif;

        if($options['shortcode'] == 'on'):
            add_shortcode('responsive_menu_pro', function($atts) use($options) {
                if(is_array($atts))
                    $merged_options = array_merge($options->toArray(), $atts);
                else
                    $merged_options = $options->toArray();

                $new_collection = new OptionsCollection($merged_options);
                $html = '';
                if($options['use_header_bar'] == 'on'):
                    $html .= $this->view->render('app/header-bar.html.twig', ['options' => $new_collection]);
                endif;
                $html .= $this->view->render('app.html.twig', ['options' => $new_collection]);
                return $html;
            });
        else:
            add_action('wp_footer', function() use($options) {
                if($options['use_header_bar'] == 'on'):
                    echo $this->view->render('app/header-bar.html.twig', ['options' => $options]);
                endif;
                echo $this->view->render('app.html.twig', ['options' => $options]);
            });
        endif;
    }

}

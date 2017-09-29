<?php

if(is_admin()):
    add_action('plugins_loaded', function() {
        if(function_exists('pll_register_string')):
            $options = get_responsive_menu_pro_service('option_manager')->all();
            pll_register_string('menu_to_use', $options['menu_to_use'], 'Responsive Menu Pro');
            pll_register_string('button_title', $options['button_title'], 'Responsive Menu Pro');
            pll_register_string('button_title_open', $options['button_title_open'], 'Responsive Menu Pro');
            pll_register_string('menu_title', $options['menu_title'], 'Responsive Menu Pro');
            pll_register_string('menu_title_link', $options['menu_title_link'], 'Responsive Menu Pro');
            pll_register_string('menu_additional_content', $options['menu_additional_content'], 'Responsive Menu Pro');
            pll_register_string('menu_search_box_text', $options['menu_search_box_text'], 'Responsive Menu Pro');
            pll_register_string('header_bar_title', $options['header_bar_title'], 'Responsive Menu Pro');
            pll_register_string('header_bar_logo', $options['header_bar_logo'], 'Responsive Menu Pro');
            pll_register_string('header_bar_logo_link', $options['header_bar_logo_link'], 'Responsive Menu Pro');
            pll_register_string('header_bar_logo_alt', $options['header_bar_logo_alt'], 'Responsive Menu Pro');
            pll_register_string('header_bar_html_content', $options['header_bar_html_content'], 'Responsive Menu Pro');
        endif;
    });
endif;

<?php

if(is_admin()):
    add_action('admin_menu', function() {
        if(isset($_POST['responsive-menu-pro-export'])):
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename=responsive-menu-pro-settings.json');
            $controller = get_responsive_menu_pro_service('admin_controller');
            echo $controller->export();
            exit();
        elseif(isset($_POST['responsive-menu-pro-rebuild-db'])):
            update_option('responsive_menu_pro_version', '2.8.9');
        endif;
        add_menu_page(
            'Responsive Menu Pro',
            'Responsive Menu Pro',
            'manage_options',
            'responsive-menu-pro',
            function() {
                $controller = get_responsive_menu_pro_service('admin_controller');
                $menus_array = [];
                $location_menus = ['' => 'None'];
                foreach(get_terms('nav_menu') as $menu) $menus_array[$menu->slug] = $menu->name;
                foreach(get_registered_nav_menus() as $location => $menu) $location_menus[$location] = $menu;

                if(isset($_POST['responsive-menu-pro-current-page']))
                    update_option('responsive_menu_pro_current_page', $_POST['responsive-menu-pro-current-page']);

                if(isset($_POST['responsive-menu-pro-submit'])):
                    $valid_nonce = wp_verify_nonce($_POST['responsive-menu-pro-nonce'], 'update');
                    echo $controller->update($valid_nonce, wp_unslash($_POST['menu']), $menus_array, $location_menus);
                elseif(isset($_POST['responsive-menu-pro-reset'])):
                    echo $controller->reset(get_responsive_menu_pro_default_options(), $menus_array, $location_menus);
                elseif(isset($_POST['responsive-menu-pro-import'])):
                    $file = $_FILES['responsive-menu-pro-import-file'];
                    $file_options = isset($file['tmp_name']) ? (array) json_decode(file_get_contents($file['tmp_name'])) : null;
                    echo $controller->import($file_options, $menus_array, $location_menus);
                elseif(isset($_POST['responsive-menu-pro-add-license-key'])):
                    echo $controller->license($_POST['responsive-menu-pro-license-key'], $menus_array, $location_menus);
                elseif(isset($_POST['responsive-menu-pro-rebuild-db'])):
                    echo $controller->rebuild($menus_array, $location_menus);
                else:
                    echo $controller->index($menus_array, $location_menus);
                endif;
            },
            'dashicons-menu');
    });
else:
    add_action('template_redirect', function() {
        $controller = get_responsive_menu_pro_service('front_controller');
        if(isset($_GET['responsive-menu-pro-preview']) && isset($_POST['menu']))
            echo $controller->preview($_POST['menu']);
        else
            $controller->index();
    });
endif;

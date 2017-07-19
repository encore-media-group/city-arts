<?php

namespace ResponsiveMenuPro\Controllers;
use ResponsiveMenuPro\View\View;
use ResponsiveMenuPro\Management\OptionManager;
use ResponsiveMenuPro\Validation\Validator;
use ResponsiveMenuPro\Tasks\UpdateOptionsTask;
use ResponsiveMenuPro\Collections\OptionsCollection;

class AdminController {

    public function __construct(OptionManager $manager, View $view) {
        $this->manager = $manager;
        $this->view = $view;
    }

    public function index($nav_menus, $location_menus) {
        return $this->view->render(
            'admin/main.html.twig',
            [
                'options' => $this->manager->all(),
                'nav_menus' => $nav_menus,
                'location_menus' => $location_menus
            ]
        );
    }

    public function rebuild($nav_menus, $location_menus) {
        return $this->view->render(
            'admin/main.html.twig',
            [
                'options' => $this->manager->all(),
                'nav_menus' => $nav_menus,
                'location_menus' => $location_menus,
                'alert' => ['success' => 'Responsive Menu Pro Database Rebuilt Successfully.']
            ]
        );
    }

    public function update($valid_nonce, $new_options, $nav_menus, $location_menus) {
        $validator = new Validator();
        $errors = [];
        if(!$valid_nonce):
            $alert = ['danger' => 'CSRF token not valid'];
            $options = new OptionsCollection($new_options);
        elseif($validator->validate($new_options)):
            try {
                $options = $this->manager->updateOptions($new_options);
                $task = new UpdateOptionsTask;
                $task->run($options, $this->view);
                $alert = ['success' => 'Responsive Menu Pro Options Updated Successfully.'];
            } catch (\Exception $e) {
                $alert = ['danger' => $e->getMessage()];
            }
        else:
            $options = new OptionsCollection($new_options);
            $errors = $validator->getErrors();
            $alert = ['danger' => $errors];
        endif;

        return $this->view->render(
            'admin/main.html.twig',
            [
                'options' => $options,
                'alert' => $alert,
                'nav_menus' => $nav_menus,
                'location_menus' => $location_menus,
                'errors' => $errors
            ]
        );
    }

    public function reset($default_options, $nav_menus, $location_menus) {
        try {
            $options = $this->manager->updateOptions($default_options);
            $task = new UpdateOptionsTask;
            $task->run($options, $this->view);
            $alert = ['success' => 'Responsive Menu Pro Options Reset Successfully'];
        } catch(\Exception $e) {
            $options = new OptionsCollection($default_options);
            $alert = ['danger' => $e->getMessage()];
        }
        return $this->view->render(
            'admin/main.html.twig',
            [
                'options' => $options,
                'alert' => $alert,
                'nav_menus' => $nav_menus,
                'location_menus' => $location_menus
            ]
        );
    }

    public function import($imported_options, $nav_menus, $location_menus) {
        $errors = [];
        if(!empty($imported_options)):
            $validator = new Validator();
            if($validator->validate($imported_options)):
                try {
                    unset($imported_options['button_click_trigger']);
                    $options = $this->manager->updateOptions($imported_options);
                    $task = new UpdateOptionsTask;
                    $task->run($options, $this->view);
                    $alert = ['success' => 'Responsive Menu Pro Options Imported Successfully.'];
                } catch(\Exception $e) {
                    $options = $this->manager->all();
                    $alert = ['danger' => $e->getMessage()];
                }
            else:
                $options = new OptionsCollection($imported_options);
                $errors = $validator->getErrors();
                $alert = ['danger' => $errors];
            endif;
        else:
            $options = $this->manager->all();
            $alert = ['danger' => 'No import file selected'];
        endif;

        return $this->view->render(
            'admin/main.html.twig',
            [
                'options' => $options,
                'alert' => $alert,
                'nav_menus' => $nav_menus,
                'location_menus' => $location_menus,
                'errors' => $errors
            ]
        );
    }

    public function license($license_key, $nav_menus, $location_menus) {
        $license_key = trim($license_key);
        $alert = [];
        if(!$license_key):
            $alert = ['danger' => 'No license key added'];
            update_option('responsive_menu_pro_license_type', '');
            update_option('responsive_menu_pro_license_key', '');
        else:
            /*
            First Check The Single License */
            $response = wp_remote_get('https://responsive.menu?' . http_build_query(
                    [
                        'edd_action'=> 'activate_license',
                        'license' 	=> $license_key,
                        'item_name' => urlencode('Responsive Menu Pro - Single License'),
                        'url'       => home_url()
                    ]
                ), ['decompress' => false]);
            $license_type = 'Single License';

            if(is_wp_error($response))
                $alert = ['danger' => $response->get_error_message() . ' - Please <a href="https://responsive.menu/faq/license-activation-issues" target="_blank"> click here</a> for more information.'];
            else
                $response = json_decode($response['body']);

            /*
            Parse Result */
            if(!isset($response->success) || !$response->success):
                /*
                Now Check The Multi License */
                $response = wp_remote_get('https://responsive.menu?' . http_build_query(
                        [
                            'edd_action'=> 'activate_license',
                            'license' 	=> $license_key,
                            'item_name' => urlencode('Responsive Menu Pro - Multi License'),
                            'url'       => home_url()
                        ]
                    ), ['decompress' => false]);
                $license_type = 'Multi License';
                if(is_wp_error($response))
                    $alert = ['danger' => $response->get_error_message() . ' - Please <a href="https://responsive.menu/faq/license-activation-issues" target="_blank"> click here</a> for more information.'];
                else
                    $response = json_decode($response['body']);
            endif;

            if(isset($response->success) && $response->success):
                update_option('responsive_menu_pro_license_type', $license_type);
                $alert = ['success' => 'License key updated'];
            else:
                update_option('responsive_menu_pro_license_type', '');
                if(!is_wp_error($response))
                    $alert = ['danger' => 'License key invalid' . ' - Please <a href="https://responsive.menu/faq/license-activation-issues" target="_blank"> click here</a> for more information.'];
            endif;
            update_option('responsive_menu_pro_license_key', $license_key);
        endif;

        return $this->view->render(
            'admin/main.html.twig',
            [
                'alert' => $alert,
                'options' => $this->manager->all(),
                'nav_menus' => $nav_menus,
                'location_menus' => $location_menus
            ]
        );
    }

    public function export() {
        return json_encode(
            $this->manager->all()->toArray()
        );
    }

}

<?php

namespace ResponsiveMenuPro\Database\Migrations;
use ResponsiveMenuPro\Collections\OptionsCollection;

class Migrate_3_1_0_3_1_1 extends Migrate {

    protected $migrations = [
        'button_background_colour_active' => 'button_background_colour',
        'button_line_colour_hover' => 'button_line_colour',
        'button_line_colour_active' => 'button_line_colour',
        'menu_container_background_colour' => 'menu_background_colour',
    ];

    protected $migration_scripts = [
        'updateFontIcons'
    ];

    protected function updateFontIcons(OptionsCollection $options) {
        if($options['menu_font_icons']):
            $decoded = json_decode($options['menu_font_icons']);
            $types = [];

            foreach($decoded->id as $icon)
                $types[] = 'font-awesome';

            $decoded->type = $types;

            $options['menu_font_icons'] = json_encode($decoded);
        endif;

        return $options;
    }

}
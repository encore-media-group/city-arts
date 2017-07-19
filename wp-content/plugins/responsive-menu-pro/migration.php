<?php

add_action('init', function() {

    $options_manager = get_responsive_menu_pro_service('option_manager');
    $plugin_data = get_file_data(dirname(__FILE__) . '/responsive-menu-pro.php', ['version']);
    $new_version = $plugin_data[0];

    $migration = new ResponsiveMenuPro\Database\Migration(
        $options_manager,
        get_option('responsive_menu_pro_version'),
        $new_version,
        get_responsive_menu_pro_default_options()
    );

    if($migration->needsTable()) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        global $wpdb;
        maybe_create_table(
            $wpdb->prefix . 'responsive_menu_pro',
            "CREATE TABLE " . $wpdb->prefix . "responsive_menu_pro (
              name varchar(50) NOT NULL,
              value varchar(5000) DEFAULT NULL,
              PRIMARY KEY (name)
           ) " . $wpdb->get_charset_collate() . ";"
        );
    }

    if($migration->needsUpdate()) {

        $migration->addNewOptions();
        $migration->tidyUpOptions();

        if($migration->getMigrationClasses()):
            $updated_options = $options_manager->all();
            foreach($migration->getMigrationClasses() as $migration)
                $migrated_options = $migration->migrate($updated_options);
            $options_manager->updateOptions($migrated_options->toArray());
        endif;

        $task = new ResponsiveMenuPro\Tasks\UpdateOptionsTask();
        $task->run($options_manager->all(), get_responsive_menu_pro_service('view'));
        update_option('responsive_menu_pro_version', $new_version);

    }

});
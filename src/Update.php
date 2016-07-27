<?php namespace Caleano\Freifunk\MeetupRegister;

use wpdb;

defined('ABSPATH') or die('NOPE');

/**
 * Class Update
 */
class Update
{
    public function __construct()
    {
        $pluginFile = realpath(__DIR__ . '/../Meetup-Register.php');
        register_activation_hook($pluginFile, [$this, 'updateTables']);
    }

    /**
     * Update the database
     */
    public function updateTables()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        /** @var wpdb $wpdb */
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'meetup_registration';

        $sql = "
            CREATE TABLE $table_name (
                `id` bigint(20) NOT NULL UNIQUE AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `community` varchar(255) NOT NULL,
                `email` varchar(255) UNIQUE NOT NULL,
                `day` varchar(255) NOT NULL,
                `grill` varchar(255),
                `lunch` varchar(255),
                `other` text,
                `optInKey` varchar(50),
                `time` datetime DEFAULT NOW() NOT NULL,
                UNIQUE KEY `id` (`id`)
            ) $charset_collate;";

        dbDelta($sql);
    }
}

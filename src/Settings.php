<?php namespace Caleano\Freifunk\MeetupRegister;

defined('ABSPATH') or die('NOPE');

class Settings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    public static $options;

    /**
     * Get plugin settings
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        if (isset(self::$options[$name])) {
            return self::$options[$name];
        }

        return $default;
    }

    /**
     * Start up
     */
    public function __construct()
    {
        self::$options = array_merge(
            [
                'title' => 'Freifunk Meetup 2016.2',
            ],
            (array)get_option('meetup_registration')
        );

        if (is_admin()) {
            add_action('admin_menu', [$this, 'addPluginPage']);
            add_action('admin_init', [$this, 'pageInit']);
        }
    }

    /**
     * Add options page
     */
    public function addPluginPage()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'Meetup Registration',
            'manage_options',
            'meetup-registration-admin',
            [$this, 'createAdminPage']
        );
    }

    /**
     * Options page callback
     */
    public function createAdminPage()
    {
        ?>
        <div class="wrap">
            <h2>Meetup Registration</h2>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('main_settings_group');
                do_settings_sections('meetup-registration-admin');
                submit_button();
                ?>
            </form>
            <?php $this->printExport(); ?>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function pageInit()
    {
        register_setting(
            'main_settings_group',
            'meetup_registration',
            [$this, 'sanitize']
        );

        add_settings_section(
            'setting_section_id',
            'Meetup Registration settings',
            [$this, 'printSectionInfo'],
            'meetup-registration-admin'
        );

        add_settings_field(
            'title',
            'Title',
            [$this, 'titleCallback'],
            'meetup-registration-admin',
            'setting_section_id'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     * @return array
     */
    public function sanitize(array $input)
    {
        $new_input = [];
        if (isset($input['title'])) {
            $new_input['title'] = sanitize_text_field($input['title']);
        }

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function printSectionInfo()
    {
        echo 'Enter your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function titleCallback()
    {
        printf(
            '<input type="text" id="title" name="meetup_registration[title]" value="%s" />',
            self::$options['title']
        );
    }

    /**
     * Print the Section text
     */
    public function printExport()
    {
        $url = site_url('/meetup/export');
        echo '<a href="' . $url . '" class="button button-primary">Export Data</a>';
    }
}

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
                'title'  => 'Freifunk Meetup 2016.2',
                'active' => true,
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
            'primary_settings',
            'Meetup Registration settings',
            [$this, 'printSectionInfo'],
            'meetup-registration-admin'
        );

        add_settings_field(
            'title',
            'Title',
            [$this, 'titleCallback'],
            'meetup-registration-admin',
            'primary_settings'
        );

        add_settings_field(
            'active',
            'Active',
            [$this, 'activeCallback'],
            'meetup-registration-admin',
            'primary_settings'
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
        $sanitizedInput = [];
        if (isset($input['title'])) {
            $sanitizedInput['title'] = sanitize_text_field($input['title']);
        }

        $sanitizedInput['active'] = false;
        if (isset($input['active'])) {
            $sanitizedInput['active'] = (bool)$input['active'];
        }

        return $sanitizedInput;
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
     * Get the settings option array and print one of its values
     */
    public function activeCallback()
    {
        printf(
            '<input type="checkbox" id="active" name="meetup_registration[active]" %s />',
            self::$options['active'] ? 'checked' : ''
        );
    }

    /**
     * Print the Section text
     */
    public function printExport()
    {
        $url = site_url('/meetup/export');
        echo '<a href="' . $url . '" class="button button-default">Export Data</a>';
    }
}

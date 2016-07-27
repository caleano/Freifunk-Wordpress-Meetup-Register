<?php namespace Caleano\Freifunk\MeetupRegister;

defined('ABSPATH') or die('NOPE');

/**
 * Class Template
 */
class Template
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'registerStyles']);
    }

    /**
     * Register and enqueue style sheet.
     */
    public function registerStyles()
    {
        wp_enqueue_style('meetup-registration', plugins_url('Meetup-Register/css/plugin.css'));
    }
}

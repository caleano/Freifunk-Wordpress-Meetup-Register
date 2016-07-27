<?php namespace Caleano\Freifunk\MeetupRegister;

use Caleano\Freifunk\MeetupRegister\WordpressRouting as Route;
use WP_Post;

defined('ABSPATH') or die('NOPE');

/**
 * Class Form
 */
class RegistrationForm
{
    public function __construct()
    {
        Route::get('meetup/register', [$this, 'onGetForm']);
    }

    public function onGetForm(WP_Post $page)
    {
        $templateFile = __DIR__ . '/../templates/register.html';

        $page->post_title = 'Freifunk Meetup 2016.2 Anmeldung';
        $template = file_get_contents($templateFile);
        $template = $this->removeWhitespace($template);
        $page->post_content = $template;
        $page->filter = 'raw';

        return $page;
    }

    private function removeWhitespace($html)
    {
        $html = preg_replace('/>(\s)+</i', '><', $html);
        $html = preg_replace('/(\w)\s+</i', '$1<', $html);
        $html = preg_replace('/>\s+(\w)/i', '>$1', $html);
        $html = preg_replace('/"\s+(\w)/i', '"$1', $html);

        return $html;
    }
}

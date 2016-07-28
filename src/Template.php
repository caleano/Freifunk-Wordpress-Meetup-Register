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

    /**
     * Renders a template
     *
     * @param string   $name
     * @param string[] $replacements
     * @return string
     */
    public static function render($name, $replacements = [])
    {
        $template = self::getPart($name);

        $from = array_keys($replacements);
        $to = array_values($replacements);

        $template = str_replace($from, $to, $template);

        return $template;
    }

    /**
     * Returns the error message html code
     *
     * @param string[] $errors
     * @return string
     */
    public static function renderErrors(array $errors)
    {
        $errorTemplate = self::getPart('errorMessage');
        $template = '';

        foreach ($errors as $title => $error) {
            $template .= str_replace(
                ['%MESSAGE_TITLE%', '%MESSAGE%'],
                [
                    $title,
                    $error,
                ],
                $errorTemplate

            );
        }

        return $template;
    }

    /**
     * Load a template part
     *
     * @param string $name
     * @return string
     */
    public static function getPart($name)
    {
        $templateFile = __DIR__ . '/../templates/' . $name . '.html';
        if (!is_readable($templateFile)) {
            return '';
        }

        $template = file_get_contents($templateFile);
        return $template;
    }
}

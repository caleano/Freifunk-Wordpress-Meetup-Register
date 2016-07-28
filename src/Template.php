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
        add_action('wp_enqueue_scripts', [$this, 'registerScripts']);
    }

    /**
     * Register and enqueue style sheet.
     */
    public function registerStyles()
    {
        wp_enqueue_style('meetup-registration', $this->getPluginAssetPath('assets/css/plugin.css'));
    }

    /**
     * Register and enqueue scripts.
     */
    public function registerScripts()
    {
        wp_enqueue_script('meetup-registration-js', $this->getPluginAssetPath('assets/js/plugin.js'), ['jquery']);
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

    /**
     * Removes all whitespaces and line breaks
     *
     * @param string $html
     * @return string
     */
    public static function removeWhitespace($html)
    {
        $html = preg_replace('/>(\s)+</i', '><', $html);
        $html = preg_replace('/(\w)\s+</i', '$1<', $html);
        $html = preg_replace('/>\s+(\w)/i', '>$1', $html);
        $html = preg_replace('/"\s+(\w)/i', '"$1', $html);

        return $html;
    }

    /**
     * Get plugin asset path
     *
     * @param string $assetPath
     * @return string
     */
    protected function getPluginAssetPath($assetPath)
    {
        $dir = realpath(__DIR__ . '/../');
        $dir = explode('/', $dir);
        $dirName = array_pop($dir);

        return plugins_url($dirName . '/' . $assetPath);
    }
}

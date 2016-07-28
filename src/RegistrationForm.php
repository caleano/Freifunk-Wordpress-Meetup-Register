<?php namespace Caleano\Freifunk\MeetupRegister;

use Caleano\Freifunk\MeetupRegister\WordpressRouting as Route;
use WP_Post;
use wpdb;

defined('ABSPATH') or die('NOPE');

/**
 * Class Form
 */
class RegistrationForm
{
    public static $title = 'Freifunk Meetup 2016.2';

    public function __construct()
    {
        Route::get('meetup/register', [$this, 'onGetForm']);
        Route::post('meetup/register', [$this, 'onPostForm']);
        Route::get('meetup/register/optIn', [$this, 'onGetOptIn']);
    }

    /**
     * Show the registration form
     *
     * @param WP_Post $page
     * @param array   $errors
     * @return WP_Post
     */
    public function onGetForm(WP_Post $page, $errors = [])
    {
        $page->post_title = self::$title . ' Anmeldung';
        $template = $this->getTemplatePart('register');

        if (empty($errors)) {
            $template = str_replace('%ERRORS%', '', $template);
        } else {
            $errorList = '';
            foreach ($errors as $name => $error) {
                $errorList .= $this->getErrorTemplate([
                    ucfirst($name) => $error
                ]);
            }

            $template = str_replace('%ERRORS%', $errorList, $template);
        }

        $template = $this->removeWhitespace($template);
        $page->post_content = $template;

        return $page;
    }

    /**
     * Handle the form post
     *
     * @param WP_Post $page
     * @return WP_Post
     */
    public function onPostForm(WP_Post $page)
    {
        if (($errors = $this->validateRequest()) !== true) {
            return $this->onGetForm($page, $errors);
        }
        $page->post_title = self::$title;

        $data = [
            'name'      => $this->getPostData('name'),
            'community' => $this->getPostData('community'),
            'email'     => $this->getPostData('email'),
            'day'       => $this->getPostData('day'),
            'grill'     => $this->getPostData('grill', []),
            'lunch'     => $this->getPostData('lunch', []),
            'other'     => $this->getPostData('other', ''),
        ];

        if ((new DataStore())->store($data)) {
            $page->post_title .= ' - Angemeldet';
            $template = $this->getTemplatePart('success');
            $page->post_content = $template;
        } else {
            $template = $this->getErrorTemplate([
                'Es gab einen Fehler' => 'Entweder die Mail konnte nicht versendet werden '
                    . 'oder irgend etwas ist beim Speichern schief gelaufen...'
            ]);
            $page->post_title .= ' - Fehler';
            $page->post_content = $template;
        }

        return $page;
    }

    /**
     * Handle the opt in confirmation
     *
     * @param WP_Post $page
     * @return WP_Post
     */
    public function onGetOptIn(WP_Post $page)
    {
        $page->post_title = self::$title;

        if (
            !($id = $this->validateOptIn())
            || !$this->unsetOptIn($id)
        ) {
            $template = $this->getErrorTemplate([
                'Es gab einen Fehler' => 'Wahrscheinlich wurdest du bereits freigeschaltet'
            ]);
            $page->post_title .= ' - Fehler';
            $page->post_content = $template;
            return $page;
        }

        $page->post_title .= ' - OptIn';
        $page->post_content = 'Du wurdest erfolgreich freigeschaltet!';

        return $page;
    }

    /**
     * Returns the error message html code
     *
     * @param string[] $errors
     * @return string
     */
    protected function getErrorTemplate(array $errors)
    {
        $errorTemplate = $this->getTemplatePart('errorMessage');
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
     * @return int|null
     */
    protected function validateOptIn()
    {
        $email = $this->getGetData('email');
        $optInKey = $this->getGetData('key');
        /** @var wpdb $wpdb */
        global $wpdb;
        $table_name = $wpdb->prefix . 'meetup_registration';

        $data = $wpdb->get_row(
            $wpdb->prepare(
                "
                  SELECT *
                  FROM $table_name
                  WHERE `email` = %s
                  AND `optInKey` = %s
                 ",
                $email,
                $optInKey
            )
        );

        if (empty($data)) {
            return null;
        }

        return $data->id;
    }

    /**
     * @param string $email
     * @return bool
     */
    protected function isRegistered($email)
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $table_name = $wpdb->prefix . 'meetup_registration';

        $data = $wpdb->get_row(
            $wpdb->prepare(
                "
                  SELECT *
                  FROM $table_name
                  WHERE `email` = %s
                 ",
                $email
            )
        );

        return !empty($data);
    }

    protected function unsetOptIn($id)
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $table_name = $wpdb->prefix . 'meetup_registration';

        $data = $wpdb->update(
            $table_name,
            ['optInKey' => null],
            ['id' => $id]
        );

        return (bool)$data;
    }

    /**
     * Load a template part
     *
     * @param string $name
     * @return string
     */
    protected function getTemplatePart($name)
    {
        $templateFile = __DIR__ . '/../templates/' . $name . '.html';
        if (!is_readable($templateFile)) {
            return '';
        }

        $template = file_get_contents($templateFile);
        return $template;
    }

    /**
     * @return true|string[]
     */
    protected function validateRequest()
    {
        $errors = [];

        if (($captcha = $this->getPostData('iAmAHiddenCap1chaDontPutAnyDataInHere')) && !empty($captcha)) {
            $errors['iAmAHiddenCap1chaDontPutAnyDataInHere'] = 'You have been trapped...';
        }

        if (!$this->getPostData('name')) {
            $errors['name'] = 'Bitte gib deinen Namen an';
        }

        if (!$this->getPostData('community')) {
            $errors['community'] = 'Bitte gib deine Community an';
        }

        if (
            !($email = $this->getPostData('email'))
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            $errors['email'] = 'Bitte gib eine gültige E-Mail an';
        }

        if ($this->isRegistered($email)) {
            $errors['email'] = 'Du hast dich mit dieser E-Mail bereits angemeldet';
        }

        if (
            !($day = $this->getPostData('day'))
            || !is_array($day)
            || !$this->validateArrayValues($day, ['friday', 'saturday', 'sunday'])
        ) {
            $errors['day'] = 'Bite wähle einen Tag';
        }

        if (
            ($grill = $this->getPostData('grill'))
            && (
                !is_array($grill)
                || !$this->validateArrayValues($grill, ['saturday', 'sunday'])
            )
        ) {
            $errors['grill'] = 'Invalid "grill"-Value';
        }

        if (
            ($lunch = $this->getPostData('lunch'))
            && (
                !is_array($lunch)
                || !$this->validateArrayValues($lunch, ['saturday'])
            )
        ) {
            $errors['lunch'] = 'Invalid "lunch"-Value';
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * @param array $data
     * @param array $values
     * @return bool
     */
    protected function validateArrayValues(array $data, array $values)
    {
        foreach ($data as $value) {
            if (!in_array($value, $values)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    protected function getGetData($key, $default = null)
    {
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        return $default;
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    protected function getPostData($key, $default = null)
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        return $default;
    }

    /**
     * Removes all whitespaces and line breaks
     *
     * @param string $html
     * @return string
     */
    protected function removeWhitespace($html)
    {
        $html = preg_replace('/>(\s)+</i', '><', $html);
        $html = preg_replace('/(\w)\s+</i', '$1<', $html);
        $html = preg_replace('/>\s+(\w)/i', '>$1', $html);
        $html = preg_replace('/"\s+(\w)/i', '"$1', $html);

        return $html;
    }
}

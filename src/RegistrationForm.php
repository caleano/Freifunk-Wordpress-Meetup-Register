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
        if (!Settings::get('active')) {
            return $this->submissionDisabled($page);
        }

        $page->post_title = Settings::get('title') . ' - Anmeldung';
        $errorList = '';

        if (!empty($errors)) {
            $page->post_title = Settings::get('title') . ' - Fehler';
            foreach ($errors as $name => $error) {
                $errorList .= Template::renderErrors([
                    ucfirst($name) => $error
                ]);
            }
        }

        $template = Template::render('register', ['%ERRORS%' => $errorList]);
        $template = Template::removeWhitespace($template);
        $template = str_replace('%ABOUT_TEXT%', Settings::get('about-text'), $template);
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
        if (!Settings::get('active')) {
            return $this->submissionDisabled($page);
        }

        if (($errors = $this->validateRequest()) !== true) {
            return $this->onGetForm($page, $errors);
        }
        $page->post_title = Settings::get('title');

        $data = [
            'name'      => Request::post('name'),
            'community' => Request::post('community'),
            'email'     => Request::post('email'),
            'day'       => Request::post('day'),
            'grill'     => Request::post('grill', []),
            'lunch'     => Request::post('lunch', []),
            'other'     => Request::post('other', ''),
        ];

        if ((new DataStore())->store($data)) {
            $page->post_title .= ' - Angemeldet';
            $template = Template::render('success');
            $page->post_content = $template;
        } else {
            $template = Template::renderErrors([
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
        $page->post_title = Settings::get('title');

        if (
            !($data = (array)$this->validateOptIn())
            || empty($data)
            || !$this->unsetOptIn($data['id'])
        ) {
            $template = Template::renderErrors([
                'Es gab einen Fehler' => 'Wahrscheinlich wurdest du bereits freigeschaltet'
            ]);
            $page->post_title .= ' - Fehler';
            $page->post_content = $template;
            return $page;
        }

        $page->post_title .= ' - OptIn';
        $page->post_content = Template::render('optInSuccess');

        $this->notify($data);

        return $page;
    }

    /**
     * Render a form disabled page
     *
     * @param WP_Post $page
     * @return WP_Post
     */
    protected function submissionDisabled(WP_Post $page)
    {
        $page->post_title = Settings::get('title') . ' - Anmeldung beendet';
        $page->post_content = Template::render('disabled');

        return $page;
    }

    /**
     * @return string[]|null
     */
    protected function validateOptIn()
    {
        $email = Request::get('email');
        $optInKey = Request::get('key');
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

        return $data;
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
     * @return true|string[]
     */
    protected function validateRequest()
    {
        $errors = [];

        if (($captcha = Request::post('iAmAHiddenCap1chaDontPutAnyDataInHere')) && !empty($captcha)) {
            $errors['iAmAHiddenCap1chaDontPutAnyDataInHere'] = 'You have been trapped...';
        }

        if (!Request::post('name')) {
            $errors['name'] = 'Bitte gib deinen Namen an';
        }

        if (!Request::post('community')) {
            $errors['community'] = 'Bitte gib deine Community an';
        }

        if (
            !($email = Request::post('email'))
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            $errors['email'] = 'Bitte gib eine gültige E-Mail an';
        }

        if ($this->isRegistered($email)) {
            $errors['email'] = 'Du hast dich mit dieser E-Mail bereits angemeldet';
        }

        if (
            !($day = Request::post('day'))
            || !is_array($day)
            || !$this->validateArrayValues($day, ['friday', 'saturday', 'sunday'])
        ) {
            $errors['day'] = 'Bite wähle einen Tag';
        }

        if (
            ($grill = Request::post('grill'))
            && (
                !is_array($grill)
                || !$this->validateArrayValues($grill, ['saturday', 'sunday'])
            )
        ) {
            $errors['grill'] = 'Invalid "grill"-Value';
        }

        if (
            ($lunch = Request::post('lunch'))
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
     * @return bool
     */
    protected function notify(array $data)
    {
        $to = Settings::get('email');
        if (empty($to)) {
            return true;
        }

        $mail = Template::render(
            'notifyMail',
            [
                '%NAME%'      => $data['name'],
                '%COMMUNITY%' => $data['community'],
                '%EMAIL%'     => $data['email'],
                '%DAY%'       => str_replace('|', ', ', trim($data['day'], '|')),
                '%GRILL%'     => str_replace('|', ', ', trim($data['grill'], '|')),
                '%LUNCH%'     => str_replace('|', ', ', trim($data['lunch'], '|')),
                '%OTHER%'     => $data['other'],
                '%TIME%'      => $data['time'],
            ]
        );

        return wp_mail(
            $to,
            Settings::get('title') . ' - Anmeldung bestätigt',
            $mail
        );
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
}

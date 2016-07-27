<?php namespace Caleano\Freifunk\MeetupRegister;

defined('ABSPATH') or die('NOPE');

/**
 * Class DataStore
 */
class DataStore
{
    /**
     * @param array $data
     * @return bool
     */
    public function store(array $data)
    {
        if (!$this->saveData($data)) {
            return false;
        }

        if (!$this->sendEmail($data)) {
            return false;
        }

        return true;
    }

    /**
     * @TODO
     *
     * @param array $data
     * @return bool
     */
    protected function saveData($data)
    {
        /*
        $data = [
            'name'      => 'Foo Bar',
            'community' => 'Foobar',
            'email'     => 'foo.bar@batz.bar',
            'day'       => ['friday', 'saturday', 'sunday'],
            'grill'     => ['saturday', 'sunday'],
            'lunch'     => ['saturday'],
            'other'     => 'Blablabla'
        ];
        */

        return wp_mail(
            'admin@foo.bar',
            'Freifunk Meetup 2016.2 - Daten',
            json_encode($data)
        );
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function sendEmail(array $data)
    {
        $confirmationLink = $this->generateConfirmationLink($data);
        $message = sprintf(
            'Bitte klicke auf den folgenden Link um deine Anmeldung zu bestätigen' . PHP_EOL
            . '%s' . PHP_EOL . PHP_EOL
            . 'Das Orga-Team',
            $confirmationLink
        );

        return wp_mail(
            $data['email'],
            'Freifunk Meetup 2016.2 - Anmeldung',
            $message
        );
    }

    /**
     * @TODO: Implement
     *
     * @param string[] $data
     * @return string
     */
    protected function generateConfirmationLink($data)
    {
        return 'foo/bar/confirm';
    }
}

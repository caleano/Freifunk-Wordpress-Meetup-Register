<?php namespace Caleano\Freifunk\MeetupRegister;

use wpdb;

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
        $optInKey = substr(base64_encode(random_bytes(50)), 0, 50);

        if (!$this->saveData($data, $optInKey)) {
            return false;
        }

        if ($optInKey && !$this->sendEmail($data, $optInKey)) {
            return false;
        }

        return true;
    }

    /**
     * @param array  $data
     * @param string $optInKey
     * @return bool
     */
    protected function saveData($data, $optInKey)
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $table_name = $wpdb->prefix . 'meetup_registration';

        return $wpdb->insert(
            $table_name,
            [
                'name'      => $data['name'],
                'community' => $data['community'],
                'email'     => $data['email'],
                'day'       => $this->getArrayString($data['day']),
                'grill'     => $this->getArrayString($data['grill']),
                'lunch'     => $this->getArrayString($data['lunch']),
                'other'     => $data['other'],
                'optInKey'  => $optInKey,
                'time'      => date('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * @param array $array
     * @return string|null
     */
    protected function getArrayString(array $array)
    {
        if (empty($array)) {
            return null;
        }

        return sprintf('|%s|', implode('|', $array));
    }

    /**
     * @param array  $data
     * @param string $optInKey
     * @return bool
     */
    protected function sendEmail(array $data, $optInKey)
    {
        $confirmationLink = $this->generateConfirmationLink($data, $optInKey);

        return wp_mail(
            $data['email'],
            Settings::get('title') . ' - Anmeldung',
            Template::render('optInMail', ['%OPT_IN_URL%' => $confirmationLink])
        );
    }

    /**
     * @param string[] $data
     * @param string   $optInKey
     * @return string
     */
    protected function generateConfirmationLink($data, $optInKey)
    {
        $url = site_url('/meetup/register/optIn/');

        return sprintf(
            '%s?key=%s&email=%s',
            $url,
            urlencode($optInKey),
            urlencode($data['email'])
        );
    }
}

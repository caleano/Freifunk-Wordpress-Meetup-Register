<?php namespace Caleano\Freifunk\MeetupRegister;

defined('ABSPATH') or die('NOPE');

class Request
{
    /**
     * Get request data (POST or GET)
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public static function data($key, $default = null)
    {
        $randomDefaultValue = md5(rand(9999999, 999999999));
        if (($data = self::post($key, $randomDefaultValue)) != $randomDefaultValue) {
            return $data;
        }

        return self::get($key, $default);
    }

    /**
     * Get GET data
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        return $default;
    }

    /**
     * Get POST data
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public static function post($key, $default = null)
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        return $default;
    }
}

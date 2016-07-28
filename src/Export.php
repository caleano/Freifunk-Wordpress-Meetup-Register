<?php namespace Caleano\Freifunk\MeetupRegister;

use Caleano\Freifunk\MeetupRegister\WordpressRouting as Route;
use stdClass;
use wpdb;

defined('ABSPATH') or die('NOPE');

/**
 * Class Form
 */
class Export
{
    public function __construct()
    {
        Route::get('meetup/export', [$this, 'onGetExport']);
    }

    /**
     * Show the registration form
     */
    public function onGetExport()
    {
        if (!$this->hasPermissions()) {
            return;
        }

        /** @var stdClass[] $data */
        $data = $this->getData();
        if (empty($data)) {
            status_header(404);
            echo 'No data found';

            exit;
        }

        if (!headers_sent()) {
            $filename = Settings::get('title');
            $filename = preg_replace('/[^\w\.\-]+/i', '', $filename);

            status_header(200);
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '-export.csv"');
        }

        $header = $this->getHeader($data);
        $header = array_diff($header, ['optInKey']);

        $this->printData($header);

        foreach ($data as $row) {
            $row = (array)$row;
            unset($row['optInKey']);
            $this->printData($row);
        }

        exit;
    }

    /**
     * @param $data
     */
    public function printData(array $data)
    {
        echo implode(';', $data) . PHP_EOL;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getHeader(array $data)
    {
        $firstElement = (array)array_shift($data);
        reset($data);

        if (!is_array($firstElement)) {
            $firstElement = $data;
        }

        if (!is_array($firstElement)) {
            return [];
        }

        return array_keys($firstElement);
    }

    /**
     * @return stdClass[]
     */
    protected function getData()
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $table_name = $wpdb->prefix . 'meetup_registration';

        $data = $wpdb->get_results("
                  SELECT *
                  FROM $table_name
                  WHERE `optInKey` IS NULL
                 ");

        return $data;
    }

    /**
     * @return bool
     */
    protected function hasPermissions()
    {
        require_once(ABSPATH . 'wp-includes/pluggable.php');

        $user = wp_get_current_user();

        return $user->has_cap('export');
    }
}

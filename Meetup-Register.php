<?php defined('ABSPATH') or die('NOPE');

/*
Plugin Name: Meetup Registration
Plugin URI: https://github.com/caleano/Freifunk-Wordpress-Meetup-Register
Description: Ein Wordpress Plugin für Freifunk Frankfurt um sich fürs Meetup anzumelden
Version: 1.0.0
Author: Igor Scheller <igor.scheller@igorshp.de>
Author URI: https://igorshp.de
License: MIT
*/

use Caleano\Freifunk\MeetupRegister\Export;
use Caleano\Freifunk\MeetupRegister\RegistrationForm;
use Caleano\Freifunk\MeetupRegister\Settings;
use Caleano\Freifunk\MeetupRegister\Template;
use Caleano\Freifunk\MeetupRegister\Update;
use Caleano\Freifunk\MeetupRegister\WordpressRouting;

require_once __DIR__ . '/src/Settings.php';
require_once __DIR__ . '/src/Request.php';
require_once __DIR__ . '/src/WordpressRouting.php';
require_once __DIR__ . '/src/DataStore.php';
require_once __DIR__ . '/src/Template.php';
require_once __DIR__ . '/src/Update.php';
require_once __DIR__ . '/src/RegistrationForm.php';
require_once __DIR__ . '/src/Export.php';

$settings = new Settings();
$update = new Update();
$router = new WordpressRouting();
$template = new Template();
$form = new RegistrationForm();
$export = new Export();

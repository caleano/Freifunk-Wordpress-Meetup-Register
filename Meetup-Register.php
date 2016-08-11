<?php defined('ABSPATH') or die('NOPE');

/*
Plugin Name: Meetup Registration
Plugin URI: https://github.com/caleano/Freifunk-Wordpress-Meetup-Register
Description: Ein Wordpress Plugin für Freifunk Frankfurt um sich fürs Meetup anzumelden
Version: 1.0.1
Author: Igor Scheller <igor.scheller@igorshp.de>
Author URI: https://igorshp.de
License: MIT
*/
require_once __DIR__ . '/autoload.php';

use Caleano\Freifunk\MeetupRegister\Export;
use Caleano\Freifunk\MeetupRegister\RegistrationForm;
use Caleano\Freifunk\MeetupRegister\Settings;
use Caleano\Freifunk\MeetupRegister\Template;
use Caleano\Freifunk\MeetupRegister\Update;
use Caleano\Freifunk\MeetupRegister\WordpressRouting;

$settings = new Settings();
$update = new Update();
$router = new WordpressRouting();
$template = new Template();
$form = new RegistrationForm();
$export = new Export();

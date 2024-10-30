<?php

/**
 * Plugin Name: Crontrol Hours
 * Description: Take control of your CRON jobs by restricting them to your website's low traffic hours.
 * Version: 2.1.0
 * Author: AuRise Creative
 * Author URI: https://aurisecreative.com/
 * Plugin URI: https://aurisecreative.com/crontrol-hours/
 * License: GPL v3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.8
 * Requires PHP: 5.6.20
 * Text Domain: crontrol-hours
 *
 * @package AuRise\Plugin\CrontrolHours
 * @copyright Copyright (c) 2023 Tessa Watkins, AuRise Creative <tessa@aurisecreative.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

defined('ABSPATH') || exit; // Exit if accessed directly
defined('CRONTROLHOURS_FILE') || define('CRONTROLHOURS_FILE', __FILE__); // Define root file
defined('CRONTROLHOURS_VERSION') || define('CRONTROLHOURS_VERSION', '2.1.0'); // Define plugin version

require_once('includes/class-utilities.php'); // Load the utilities class
require_once('includes/class-settings.php'); // Load the settings class
require_once('includes/class-main.php'); // Load the main plugin class

/**
 * The global instance of the Main plugin class
 *
 * @var AuRise\Plugin\CrontrolHours\Main
 *
 * @since 3.0.0
 */
$au_init_plugin = str_replace('-', '_', sanitize_key(dirname(plugin_basename(CRONTROLHOURS_FILE)))); // E.g. `plugin_folder`
global ${$au_init_plugin}; // I.e. `$plugin_folder`
${$au_init_plugin} = AuRise\Plugin\CrontrolHours\Main::instance(); // Run once to init
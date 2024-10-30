<?php
/**
 * @link              http://pcis.com
 * @since             1.0.1
 * @package           LaiserTagInsights
 *
 * @wordpress-plugin
 * Plugin Name:       Laiser Tag Insights
 * Plugin URI:        http://pcis.com/laiser-tag
 * Description:       Extended analytics for Laiser Tag
 * Version:           1.1.0
 * Author:            PCIS
 * Author URI:        http://pcis.com
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define('LTI_PLUGIN_VERSION', '1.1.0');
define('LTI_PLUGIN_PATH', dirname(__FILE__));
define('LTI_TEMPLATES', dirname(__FILE__) . '/templates/');
define('LTI_PLUGIN_NAME', 'laiser-tag-insights');
define('LTI_ASSETS_URL', plugins_url(LTI_PLUGIN_NAME . '/assets/'));

require_once __DIR__ . '/include/LTInsight.php';

global $ltinsights_obj;
$ltinsights_obj = new \LaiserTag\LTInsight();

function lti_activate_plugin() {
    global $ltinsights_obj;
    \LaiserTag\LTInsight::activationCheck();
    $ltinsights_obj->setupMenus();
}

function lti_deactivate_plugin() {
    global $ltinsights_obj;
    \LaiserTag\LTInsight::deactivation();
    $ltinsights_obj = null;
}

register_activation_hook( __FILE__, 'lti_activate_plugin');
register_deactivation_hook( __FILE__, 'lti_deactivate_plugin');
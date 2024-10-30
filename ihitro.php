<?php
/**
 * Plugin Name: Ihitro Media
 * Plugin URI: http://borner.nl
 * Description: Plugin voor klanten van Ihitro. Login pagina word aangepast en men kan ook informatie over Ihitro Media vinden.
 * Version: 0.5
 * Author: Arvid de Jong
 * Author URI: http://borner.nl
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */


$ihitro_config			= get_option('ihitro_config');
$ihitro_config_users	= get_option('ihitro_config_users');
require_once('ihitro.class.php');
$ihitro = new ihitro();


?>
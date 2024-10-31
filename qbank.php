<?php
/*
Plugin Name: QBank Connector
Plugin URI: 
Description: Connector to QBank media.
Text Domain: qbank-wp-connector
Domain Path: /languages/
Version: 1.1.0
Author: Andy Karlsson
Author URI: http://luckycat.se/
License: 
*/


if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once(dirname(__FILE__) . '/vendor/autoload.php');
require_once(dirname(__FILE__) . '/functions.php');


function qbank_activate() {
	
  if( !get_option('qbank_host') ) update_option( 'qbank_host', 'sales.qbank.se' );
  if( !get_option('qbank_base_href') ) update_option( 'qbank_base_href', 'http://sales.qbank.se/connector' );
  if( !get_option('qbank_client_id') ) update_option( 'qbank_client_id', 'c34a9cdcaf83604dedc85b5609dc607b3e89c75e' );
  if( !get_option('qbank_username') ) update_option( 'qbank_username', 'wp_user' );
  if( !get_option('qbank_password') ) update_option( 'qbank_password', 'qbank2016' );
  if( !get_option('qbank_deployment_site_id') ) update_option( 'qbank_deployment_site_id', '3' );
  if( !get_option('qbank_session_source_id') ) update_option( 'qbank_session_source_id', '243' );
  if( !get_option('qbank_default_image_size') ) update_option( 'qbank_default_image_size', '1000' );
  if( !get_option('qbank_default_image_extension') ) update_option( 'qbank_default_image_extension', 'jpg' );
  if( !get_option('qbank_download_file') ) update_option( 'qbank_download_file', true );

}
register_activation_hook( __FILE__, 'qbank_activate' );

define( "QBANK_HOST", rtrim(get_option('qbank_host'), '/') );
define( "QBANK_BASE_HREF", rtrim(get_option('qbank_base_href'), '/') );
define( "QBANK_CLIENT_ID", get_option('qbank_client_id') );
define( "QBANK_USERNAME", get_option('qbank_username') );
define( "QBANK_PASSWORD", get_option('qbank_password') );
define( "QBANK_DEPLOYMENT_SITE_ID", get_option('qbank_deployment_site_id') );
define( "QBANK_SESSION_SOURCE_ID", get_option('qbank_session_source_id') );
define( "QBANK_DEFAULT_IMAGE_SIZE", get_option('qbank_default_image_size') );
define( "QBANK_DEFAULT_IMAGE_EXTENSION", get_option('qbank_default_image_extension') );
define( "QBANK_ALT_NAME", get_option('qbank_alt_name') );
define( "QBANK_CAPTION_NAME", get_option('qbank_caption_name') );
define( "QBANK_DESCRIPTION_NAME", get_option('qbank_description_name') );
define( "QBANK_ADDITIONAL_META_FIELDS", array_map('trim', explode(',', get_option('qbank_additional_meta_fields'))) );
define( "QBANK_DOWNLOAD_FILE", get_option('qbank_download_file') );
define( "QBANK_REMOTE_PREFIX", rtrim(get_option('qbank_remote_prefix'), '/').'/' );

$now = date("Y-m-d H:i:s");

if ( is_admin() ):


	add_action( 'init', 'qbank_load_textdomain' );

	// Hook onto dashboard and admin menu
	add_action( 'admin_menu', "qbank_admin_menu" );

endif;
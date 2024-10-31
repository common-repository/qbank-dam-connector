<?php
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function qbank_load_textdomain() {

	load_plugin_textdomain('qbank-wp-connector', false, basename( dirname( __FILE__ ) ) . '/languages' );

}

// Add items to the wp admin menu
function qbank_admin_menu() {

	$page_title = __("QBank Connector", 'qbank-wp-connector');
	$menu_title = __("QBank Connector", 'qbank-wp-connector');
	add_submenu_page( 'options-general.php', $page_title, $menu_title, "administrator", "qbank-settings", "qbank_settings_page");

}

function qbank_settings() {

	register_setting( 'qbank-settings-group', 'qbank_host' );
	register_setting( 'qbank-settings-group', 'qbank_base_href' );
	register_setting( 'qbank-settings-group', 'qbank_client_id' );
	register_setting( 'qbank-settings-group', 'qbank_username' );
	register_setting( 'qbank-settings-group', 'qbank_password' );
	register_setting( 'qbank-settings-group', 'qbank_deployment_site_id' );
	register_setting( 'qbank-settings-group', 'qbank_session_source_id' );
	register_setting( 'qbank-settings-group', 'qbank_disable_file_uploads' );
	register_setting( 'qbank-settings-group', 'qbank_download_file' );
	register_setting( 'qbank-settings-group', 'qbank_remote_prefix' );
	register_setting( 'qbank-settings-group', 'qbank_default_image_size' );
	register_setting( 'qbank-settings-group', 'qbank_default_image_extension' );
    register_setting( 'qbank-settings-group', 'qbank_alt_name' );
	register_setting( 'qbank-settings-group', 'qbank_caption_name' );
	register_setting( 'qbank-settings-group', 'qbank_description_name' );
	register_setting( 'qbank-settings-group', 'qbank_additional_meta_fields' );
}
add_action( 'admin_init', 'qbank_settings' );

/**
 * Output options page
 */
function qbank_settings_page() {


//must check that the user has the required capability 
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	?>
  <div class="wrap">
    <h2><?php _e("QBank Connector settings", 'qbank-wp-connector') ?></h2>


    <form method="post" action="options.php">
			<?php settings_fields( 'qbank-settings-group' ); ?>
			<?php do_settings_sections( 'qbank-settings-group' ); ?>
      <table class="form-table">

        <tr valign="top">
          <th scope="row"><?php _e("Host QBank", 'qbank-wp-connector') ?></th>
          <td><input type="text" name="qbank_host" value="<?php echo esc_attr( get_option('qbank_host') ); ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e("Base href", 'qbank-wp-connector') ?></th>
          <td><input type="text" name="qbank_base_href" value="<?php echo esc_attr( get_option('qbank_base_href') ); ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e("Client ID", 'qbank-wp-connector') ?></th>
          <td><input type="text" name="qbank_client_id" value="<?php echo esc_attr( get_option('qbank_client_id') ); ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e("Username", 'qbank-wp-connector') ?></th>
          <td><input type="text" name="qbank_username" value="<?php echo esc_attr( get_option('qbank_username') ); ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e("Password", 'qbank-wp-connector') ?></th>
          <td><input type="text" name="qbank_password" value="<?php echo esc_attr( get_option('qbank_password') ); ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e("Deployment site ID", 'qbank-wp-connector') ?></th>
          <td><input type="text" name="qbank_deployment_site_id" value="<?php echo esc_attr( get_option('qbank_deployment_site_id') ); ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e("Session source ID", 'qbank-wp-connector') ?></th>
          <td><input type="text" name="qbank_session_source_id" value="<?php echo esc_attr( get_option('qbank_session_source_id') ); ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e("Disable file uploads", 'qbank-wp-connector') ?></th>
          <td><input type='checkbox' name='qbank_disable_file_uploads' value='1' <?php checked( get_option('qbank_disable_file_uploads'), '1' ); ?> />
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e("Download imported files", 'qbank-wp-connector') ?></th>
          <td><input type='checkbox' name='qbank_download_file' value='1' <?php checked( get_option('qbank_download_file'), '1' ); ?> />
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e("Remote file URL prefix", 'qbank-wp-connector') ?></th>
          <td><input type="url" name="qbank_remote_prefix" placeholder="http://powered-by.qbank.se/" value="<?php echo esc_attr( get_option('qbank_remote_prefix') ); ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e("Default image size", 'qbank-wp-connector') ?></th>
          <td><input type="text" name="qbank_default_image_size" value="<?php echo esc_attr( get_option('qbank_default_image_size') ); ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e("Default image extension", 'qbank-wp-connector') ?></th>
          <td><input type="text" name="qbank_default_image_extension" value="<?php echo esc_attr( get_option('qbank_default_image_extension') ); ?>" /></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e("Image alt name", 'qbank-wp-connector') ?></th>
          <td><input type="text" name="qbank_alt_name" value="<?php echo esc_attr( get_option('qbank_alt_name') ); ?>" /></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e("Image caption name", 'qbank-wp-connector') ?></th>
          <td><input type="text" name="qbank_caption_name" value="<?php echo esc_attr( get_option('qbank_caption_name') ); ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e("Image description name", 'qbank-wp-connector') ?></th>
          <td><input type="text" name="qbank_description_name" value="<?php echo esc_attr( get_option('qbank_description_name') ); ?>" /></td>
        </tr>

        <tr valign="top">
          <th scope="row"><?php _e("Save additional meta fields", 'qbank-wp-connector') ?></th>
          <td><input type="text" name="qbank_additional_meta_fields" value="<?php echo esc_attr( get_option('qbank_additional_meta_fields') ); ?>" /><p class="description"><?php _e("Comma-separated field names", 'qbank-wp-connector') ?></p></td>
        </tr>

      </table>

			<?php submit_button(); ?>

    </form>


  </div>

	<?php
}

// add the tab
add_filter('media_upload_tabs', 'qbank_upload_tab');
function qbank_upload_tab($tabs) {
	$tabs['mytabname'] = __("QBank Connector", 'qbank-wp-connector');;
	return $tabs;
}

// call the new tab with wp_iframe
add_action('media_upload_mytabname', 'qbank_add_new_form');
function qbank_add_new_form() {
	wp_iframe( 'qbank_new_form' );
}

add_action( 'wp_ajax_qbank_add_new_form_no_header', 'qbank_add_new_form_no_header' );
function qbank_add_new_form_no_header() {
	global $hide_media_header;
	$hide_media_header = true;
	wp_iframe( 'qbank_new_form' );
	exit;
}

function qbank_load_wp_media_files() {
	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'qbank_load_wp_media_files' );

// the tab content
function qbank_new_form() {
	require_once(dirname(__FILE__) . '/qbank-connector.php');
}



function qbank_process_media_import() {

	require_once(dirname(__FILE__) . '/qbank-media-import.php');

}
add_action( 'wp_ajax_qbank_process_media_import', 'qbank_process_media_import' );


function qbank_remove_medialibrary_tab($strings) {
	//error_log(print_r($strings, true));

	if ( get_option('qbank_disable_file_uploads') ) {
		remove_menu_page( 'upload.php' );
		unset($strings["uploadFilesTitle"]);
		return $strings;
	}
	else
	{
		return $strings;
	}
}
add_filter('media_view_strings','qbank_remove_medialibrary_tab');

function qbank_save_post( $post_id, $post, $update ) {

	// If this is just a revision, don't do anything.
	if ( wp_is_post_revision( $post_id ) )
		return;

	//error_log(print_r($post_id, true));
	require_once(dirname(__FILE__) . '/qbank-reportuse.php');

}
add_action( 'save_post', 'qbank_save_post', 10, 3 );




function qbank_delete_post( $post_id ){

	//error_log(print_r("$post_id before_delete_post", true));
}
add_action( 'before_delete_post', 'qbank_delete_post' );


function qbank_delete_attachment( $post_id ){

	$delete_attachment = true;
	$attachment_id = $post_id;
	require_once(dirname(__FILE__) . '/qbank-reportuse.php');

}
add_action( 'delete_attachment', 'qbank_delete_attachment' );

add_action( 'admin_footer-post-new.php', 'qbank_footer_script' );
add_action( 'admin_footer-post.php', 'qbank_footer_script' );

function qbank_footer_script(){

	//error_log(print_r(plugin_dir_url(__FILE__), true));

	?>
  <script>

      jQuery(function($) {


          var l10n = wp.media.view.l10n;
          var qbank_frame = '<iframe width=100% height=100% src="<?php echo admin_url( 'admin-ajax.php?action=qbank_add_new_form_no_header' ) ?>"></iframe>';

          if (wp.media) {

              wp.media.view.MediaFrame.Select = wp.media.view.MediaFrame.Select.extend({
                  browseRouter: function( routerView ) {
                      "use strict";
                      routerView.set({
                          upload: {
                              text:     l10n.uploadFilesTitle,
                              priority: 20
                          },
                          browse: {
                              text:     l10n.mediaLibraryTitle,
                              priority: 40
                          },
                          myaction: {
                              text:     "QBank Connector",
                              priority: 50
                          }
                      });
                  }
              });

              wp.media.view.MediaFrame.Post = wp.media.view.MediaFrame.Post.extend({
                  browseRouter: function( routerView ) {
                      "use strict";
                      routerView.set({
                          upload: {
                              text:     l10n.uploadFilesTitle,
                              priority: 20
                          },
                          browse: {
                              text:     l10n.mediaLibraryTitle,
                              priority: 40
                          },
                          myaction: {
                              text:     "QBank Connector",
                              priority: 50
                          }
                      });
                  }
              });

              //wp.media.controller.Library.prototype.defaults.contentUserSetting=true;


              wp.media.view.Router = wp.media.view.Router.extend(
                  {
                      select : function(id){
                          var view = this.get( id );
                          console.log( id );


                          this.deselect();
                          if(view && view.$el)
                              view.$el.addClass('active');


                          if(id == "myaction"){
                              //do something
                              var frame = $( ".media-frame-content" );
                              frame.html(qbank_frame);

                          } else if(id == "browse"){
                              //console.log( this );


                          }

                      }
                  });

              wp.media.view.Modal.prototype.on('open', function() {
                  console.log('media modal open');


                  if ( !$('#menu-item-myaction').hasClass('active')){
                      return false;
                  }

                  var frame = $( ".media-frame-content" );
                  frame.html(qbank_frame);

               });



          }
          // Clever JS here

//});
          //do stuff


          wp.media.featuredImage.frame().on('open',function() {
              console.log('featuredImage');
          });


      });
  </script>
	<?php
}


function qbank_image_send_to_editor($html, $id, $caption, $title, $align, $url) {
	$_qbnk_media_id = get_post_meta( $id, '_qbnk_media_id', true );
	if(!is_numeric($_qbnk_media_id))
		return $html;

	$html = str_replace('<img', '<img qbnk_track_id="'.$id.'"', $html);
	//error_log(print_r($html, true));

	return $html;
}
add_filter('image_send_to_editor', 'qbank_image_send_to_editor', 10, 9);


function qbank_get_attachment_url( $url, $attachment_id) {

	if(get_post_meta($attachment_id, '_qbnk_remote_file', true))
		return get_post_meta($attachment_id, '_wp_attached_file', true);

	return $url;
}
add_filter('wp_get_attachment_url', 'qbank_get_attachment_url', 10, 2 );


add_filter(
	'image_downsize',
	function ($f,$attachment_id,$size) {
		// your own downsize function
		if(get_post_meta($attachment_id, '_qbnk_remote_file', true)):
			$metadata = wp_get_attachment_metadata($attachment_id);
			//error_log(print_r($metadata, true));
			return array(get_post_meta($attachment_id, '_wp_attached_file', true),$metadata['width'],$metadata['height']);
		endif;
	},
	10,3
);

/**
 * Get size information for all currently-registered image sizes.
 *
 * @global $_wp_additional_image_sizes
 * @uses   get_intermediate_image_sizes()
 * @return array $sizes Data for all currently-registered image sizes.
 */
function qbank_get_image_sizes() {
	global $_wp_additional_image_sizes;

	$sizes = array();

	foreach ( get_intermediate_image_sizes() as $_size ) {
		if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
			$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
			$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
			$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] = array(
				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
			);
		}
	}

	return $sizes;
}

?>
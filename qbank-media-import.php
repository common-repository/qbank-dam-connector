<?php 
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb, $now;

use QBNK\QBank\API\QBankApi;
use QBNK\QBank\API\CachePolicy;
use QBNK\QBank\API\Credentials;

$qbankApi = new QBankApi(
   QBANK_HOST,
   new Credentials(
      QBANK_CLIENT_ID,
      QBANK_USERNAME,
      QBANK_PASSWORD
   ),
   array(
      'log' => false,
      'cache' => false,
      'cachePolicy' => new CachePolicy(false, 3600)
   )
);

check_ajax_referer( 'qbank_ajax_nonce', 'security' );

$media = json_decode( stripslashes($_POST['data']['media']) );
$crop = json_decode( stripslashes($_POST['data']['crop']) );
//error_log(print_r( $media, true));


$media_id = $media->mediaId;

if(!is_numeric($media_id))
	return;

if (QBANK_DOWNLOAD_FILE == 1):
    
  $url = $crop[0]->url;
  $crop_data = $crop[0]->crop;
  $resize_data = $crop[0]->resize;
  $timeout_seconds = 20;


  $temp_file = download_url( $url, $timeout_seconds );

  if (is_wp_error( $temp_file )):	
  	error_log(print_r($temp_file->get_error_message(), true));
  	return;
  endif;

  $file = array(
  	'name' => sanitize_title($media->name).'.'.$media->extension, 
  	'type' => $media->mimetype->mimetype,
  	'tmp_name' => $temp_file,
  	'error' => 0,
  	'size' => filesize($temp_file),
  );


  $res = wp_handle_sideload( $file, array('test_form'=>false), $now );


else:
  $res['type'] = $media->mimetype->mimetype;

  $attach_data = array();
  //error_log(print_r(qbank_get_image_sizes(), true));
  $image_sizes = qbank_get_image_sizes();

  foreach ($media->deployedFiles as $k => $val) {

    $metadata = json_decode( $val->metadata );
    //error_log(print_r($metadata, true));

    if ($media->mimetype->classification == 'video') {
      $sort[$val->filesize] = $val->remoteFile;
      krsort($sort, SORT_NUMERIC);
      $attach_data['file'] = reset($sort); 
      continue;
    }

    if ( intval($attach_data['width']) < $metadata->width ) {
      $attach_data['file'] = $val->remoteFile;    
      $attach_data['width'] = $metadata->width;
      $attach_data['height'] = $metadata->height;
    }

    foreach ($image_sizes as $k1 => $val1) {
      if ( $val1['width'] == $metadata->width OR $val1['height'] == $metadata->height ) {
        // Match!
        $attach_data['sizes'][$k1]['file'] = $val->remoteFile;    
        $attach_data['sizes'][$k1]['width'] = $metadata->width;
        $attach_data['sizes'][$k1]['height'] = $metadata->height;        
      }
    }

  }  
  //error_log(print_r($attach_data, true));  
  
  $res['url'] = QBANK_REMOTE_PREFIX.$attach_data['file'];
endif;  
//error_log(print_r($res, true));

$post_excerpt = '';
$_wp_attachment_image_alt = null;
foreach ($media->propertySets as $key1 => $val1) {
  foreach ($val1->properties as $key2 => $val2) {
	if($val2->propertyType->systemName == QBANK_ALT_NAME):
	  $_wp_attachment_image_alt = is_array($val2->value) ? $media->name : $val2->value;
	endif;
    if($val2->propertyType->systemName == QBANK_CAPTION_NAME):      
      $post_excerpt = is_array($val2->value) ? $media->name : $val2->value;
    endif;
    if($val2->propertyType->systemName == QBANK_DESCRIPTION_NAME):      
      $post_content = is_array($val2->value) ? $media->name : $val2->value;
    endif;

    if( isset($val2->propertyType->systemName) AND in_array($val2->propertyType->systemName, QBANK_ADDITIONAL_META_FIELDS) ):
      $qbank_additional_meta_fields[$val2->propertyType->systemName] = (string) $val2->value;
    endif;  
  }
}

//error_log(print_r($qbank_additional_meta_fields, true));      

$attachment = array(
   'post_mime_type' => $res['type'],
   'guid' => $res['url'],
   'post_title' => $media->name,
   'post_name' => $aid,
   'post_content' => $post_content ? $post_content : '',
   'post_excerpt' => $post_excerpt ? $post_excerpt : '',
   'post_status'    => 'inherit'
 );
$debug['attachment'] = $attachment;
$debug['qbank_additional_meta_fields'] = $qbank_additional_meta_fields;
//error_log(print_r($attachment, true));

$attachment_id = wp_insert_attachment($attachment);

if ( is_wp_error( $attachment_id ) ) {
	// There was an error uploading the image.
	//error_log(print_r($attachment_id->get_error_message(), true));
	$return = array('status'	=> 'error', 'msg' => $attachment_id->get_error_message());
	wp_send_json($return);
	exit;
} else {
	

  if (QBANK_DOWNLOAD_FILE == 1):
    $attach_data = wp_generate_attachment_metadata( $attachment_id, $res['file'] );    
    wp_update_attachment_metadata( $attachment_id, $attach_data );
    update_attached_file( $attachment_id, $res['file'] );
  else:
    update_post_meta($attachment_id, '_wp_attached_file', $res['url']);    
    update_post_meta($attachment_id, '_qbnk_remote_file', true);
    wp_update_attachment_metadata( $attachment_id, $attach_data );        
  endif;

  update_post_meta($attachment_id, '_wp_attachment_image_alt', $_wp_attachment_image_alt ?? $media->name);
  update_post_meta($attachment_id, '_qbnk_category_id', $category_id);
  update_post_meta($attachment_id, '_qbnk_media_id', $media_id);
  update_post_meta($attachment_id, '_qbnk_o_size', $media->size);
  update_post_meta($attachment_id, '_qbnk_o_file_extension', $media->extension);
  update_post_meta($attachment_id, '_qbnk_o_mime_type', $media->mimetype->mimetype);
  update_post_meta($attachment_id, '_qbnk_o_crop', $crop_data);
  update_post_meta($attachment_id, '_qbnk_o_resize', $resize_data);

  if($qbank_additional_meta_fields):
    foreach ($qbank_additional_meta_fields as $key => $val) {
      if (empty($val))
        continue;

      update_post_meta($attachment_id, '_qbnk_p_'.$key, $val);
    }
  endif;
}

$return = array(
		'status'	=> 'success',			
		'id'		=> $attachment_id,
		'debug' => $debug
);

wp_send_json($return);
 ?>
<?php
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

@session_start();

global $current_user, $wpdb;
//Registrera en session som API:et knyter events till, detta bör göras en gång och sedan sparas i tex $_SESSION så att
//samma ID används för alla usages under den här sessionen

use QBNK\QBank\API\QBankApi;
use QBNK\QBank\API\CachePolicy;
use QBNK\QBank\API\Credentials;
use QBNK\QBank\API\Model\MediaUsage;
use QBNK\QBank\API\Model\MediaUsageResponse;

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

$credentials = new Credentials(QBANK_CLIENT_ID, QBANK_USERNAME, QBANK_PASSWORD);
$qbankApi = new QBankApi(QBANK_HOST, $credentials);


if (empty($_SESSION['qbank_session_id'])) {
	try {
		$_SESSION['qbank_session_id'] = $qbankApi->events()->session(
			QBANK_SESSION_SOURCE_ID,
			session_id(),
			$_SERVER['REMOTE_ADDR'],
			$_SERVER['HTTP_USER_AGENT']
		);

	} catch (Exception $e) {
		error_log('Caught exception: ' .  $e->getMessage());
		return;
	}
}

//error_log('post_id:'.print_r($post_id, true));


$permalink = get_permalink( $post_id );
$post = get_post($post_id);
$_qbnk_attachment_id_log = get_post_meta( $post_id, '_qbnk_attachment_id_log', true ); // Fetch ids of all images used in this post.
//error_log(print_r($_qbnk_attachment_id_log, true));


if( $post->post_status == 'trash' ):
	//Remove usage for all _qbnk_attachment_id_log

	if($_qbnk_attachment_id_log):
		// $qbankApi->events()->removeUsage($_qbnk_attachment_id_log[0]);

		foreach($_qbnk_attachment_id_log as $val):
			$_qbnk_usage_id_log = get_post_meta($val, '_qbnk_usage_id_log', true);
			//error_log(print_r($_qbnk_usage_id_log[$post_id], true));

			try {
				$qbankApi->events()->removeUsage($_qbnk_usage_id_log[$post_id]);
			} catch (Exception $e) {
				error_log('Caught exception: ' .  $e->getMessage());
			}
		endforeach;

		return;

	endif;


endif;


$attachment_ids = array();

preg_match_all('#<img.*?>#', $post->post_content, $pics );
array_unique($pics);
$pics[0][] = get_post_thumbnail_id( $post_id );
//error_log(print_r($pics, true));

foreach($pics[0] as $val):

	if( !$val )
		continue;

	preg_match('/wp-image-([^"]+)/i',$val, $image);
	
	//error_log(print_r($image, true));

	// Find out the post id from filename
	if(is_numeric($val)):
		$attachment_id = $val;
	else:
		$attachment_id = $image[1];
	endif;

	if( !is_numeric($attachment_id) )
		continue;

	//error_log(print_r($attachment_id, true));

	$attachment_ids[] = $attachment_id;
	$_qbnk_o_crop = get_post_meta( $attachment_id, '_qbnk_o_crop', true );
	$_qbnk_media_id = get_post_meta( $attachment_id, '_qbnk_media_id', true );

	//error_log('_qbnk_media_id: '.print_r($_qbnk_media_id, true));

	if( !is_numeric($_qbnk_media_id) )
		continue;

	$_qbnk_usage_id_log = get_post_meta( $attachment_id, '_qbnk_usage_id_log', true );
	$_qbnk_usage_id_log = empty($_qbnk_usage_id_log) ? [] : (array) $_qbnk_usage_id_log;

	//error_log('get_post_meta '.$attachment_id.': '.print_r($_qbnk_usage_id_log, true));


	// This usage has already been reported.
	if( isset($_qbnk_usage_id_log[$post_id]) AND is_numeric($_qbnk_usage_id_log[$post_id]) ):
		try {
		$qbankApi->events()->removeUsage($_qbnk_usage_id_log[$post_id]);
		} catch (Exception $e) {
			error_log('Caught exception: ' .  $e->getMessage());
		}
	endif;

	$mediaUsage = new MediaUsage([
	'mediaId' => $_qbnk_media_id,
	'mediaUrl' => wp_get_attachment_url( $attachment_id ), //Publik URL till den infogade bilden, om möjligt
	'pageUrl' => $permalink, //Adress till själva sidan som bilden använts på
	'language' => 'ENG', //Om möjligt, vilket språk sidan är på
	'context' => [ //Du kan lägga vad som helst i "context" och få tillbaka det då du hämtar ut alla usages för ett media, men vissa nycklar bör finnas
	'localID' => $post_id, //Nån typ av lokal id för bilden i wordpress, typ raden för median i wordpress mediatabell i databasen?
	'cropCoords' => (!empty($_qbnk_o_crop) ? $_qbnk_o_crop : []), //Klistra in hela crop-objektet som kommer från connectorn här.
	'pageTitle' => $post->post_title, //Titeln på wordpressidan som bilden använts på, för presentationssyfte i QBank
	'createdByName' => $current_user->data->display_name, //Inloggade användarens namn
	'createdByEmail' => $current_user->data->user_email //Inloggade användarens email
	]
	]);
	//error_log(print_r($mediaUsage, true));


	try {
	$mediaUsage = $qbankApi->events()->addUsage($_SESSION['qbank_session_id'], $mediaUsage);
	} catch (Exception $e) {
	//error_log('Caught exception: ' .  $e->getMessage());
	continue;
	}


	update_post_meta($post_id, '_qbnk_usage_id', $mediaUsage->getId());

	$_qbnk_usage_id_log[$post_id] = $mediaUsage->getId();
	//error_log('_qbnk_usage_id_log: '.print_r($_qbnk_usage_id_log, true));
	update_post_meta($attachment_id, '_qbnk_usage_id_log', $_qbnk_usage_id_log);


endforeach;


//error_log(print_r($attachment_ids, true));
//error_log(print_r($_qbnk_attachment_id_log, true));

if($_qbnk_attachment_id_log):
foreach( $_qbnk_attachment_id_log as $val):
	//error_log(print_r($val, true));
	if( !in_array($val, $attachment_ids) ):
		//error_log(print_r("$val has been removed", true));
		$_qbnk_usage_id_log = get_post_meta($val, '_qbnk_usage_id_log', true);
		//error_log(print_r($_qbnk_usage_id_log[$post_id], true));
		try {
			$qbankApi->events()->removeUsage($_qbnk_usage_id_log[$post_id]);
		} catch (Exception $e) {
			error_log('Caught exception: ' .  $e->getMessage());

		}
	endif;
endforeach;
endif;

update_post_meta($post_id, '_qbnk_attachment_id_log', $attachment_ids);

?>
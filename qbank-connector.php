<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $wpdb, $now, $hide_media_header;

use QBNK\QBank\API\QBankApi;
use QBNK\QBank\API\CachePolicy;
use QBNK\QBank\API\Credentials;
use QBNK\QBank\API\Model\MediaUsage;
use QBNK\QBank\API\Model\MediaUsageResponse;
use QBNK\QBank\API\Model\Search;

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
//var_dump($qbankApi);
echo media_upload_header(); // This function is used for print media uploader headers etc.

//error_log(var_export($qbankApi, true));


?>
<div id="loader">
  <div class="dots">
    <div class="bounce1"></div>
    <div class="bounce2"></div>
    <div class="bounce3"></div>
  </div>
</div>

<div id="loader-msg">
  <?php _e("Image was imported to Media Library. You may need to reopen this window to see it.", 'qbank-wp-connector') ?>
</div>
<div id="loader-msg-backdrop"></div>
<style type="text/css">

    .dots {
        width: 70px;
        text-align: center;
        margin: 10px auto;
    }

    .dots div {
        width: 18px;
        height: 18px;
        background-color: rgba(157, 38, 29, 0.75);
        border-radius: 100%;
        display: inline-block;
        -webkit-animation: bouncedelay 1.4s infinite ease-in-out;
        animation: bouncedelay 1.4s infinite ease-in-out;
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
    }

    .dots div.bounce1 {
        -webkit-animation-delay: -0.32s;
        animation-delay: -0.32s;
    }

    .dots div.bounce2 {
        -webkit-animation-delay: -0.16s;
        animation-delay: -0.16s;
        background-color: rgba(157, 38, 29, 0.85);
    }

    .dots div.bounce3 {
        background-color: rgba(157, 38, 29, 0.95);
    }

    #loader, #loader-msg {
        position: fixed;
        top: 50%;
        left: 50%;
        margin-left: -50px;
        display: none;
        z-index: 1600000;
    }

    #loader-msg {
        background-color: #fff;
        padding: 20px;

    }

    #loader-msg-backdrop {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        min-height: 360px;
        background: #000;
        opacity: .7;
        z-index: 1599000;
    }

    #loader .dots {
        width: 100px;
        margin: 0;
    }

    #loader .dots div {
        width: 30px;
        height: 30px;
    }

    @-webkit-keyframes bouncedelay {
        0%, 80%, 100% {
            -webkit-transform: scale(0);
        }

        40% {
            -webkit-transform: scale(1);
        }
    }

    @keyframes bouncedelay {
        0%, 80%, 100% {
            transform: scale(0);
            -webkit-transform: scale(0);
        }

        40% {
            transform: scale(1);
            -webkit-transform: scale(1);
        }
    }

    }

</style>

<script src="<?= QBANK_BASE_HREF ?>/qbank-connector.min.js"></script>

<div id="wrapper" style="height: 100%;"></div>

<script>
    (function ($) {
	  <?php if($hide_media_header): ?>
        $("#media-upload-header").remove();
	  <?php endif; ?>
        var switchAndReload = function () {

            // get wp outside iframe

            var wp = parent.wp;
            //console.log(wp);
            //switch tabs (required for the code below)


        };


        console.log('qbnk loaded');

        $(window).load(function () {

            var qbcConfig = {
                deploymentSite: '<?= QBANK_DEPLOYMENT_SITE_ID ?>',
                api: {
                    host: '<?= QBANK_HOST ?>',
                    access_token: '<?= $qbankApi->getTokens()['accessToken']->getToken() ?>',
                    protocol: 'https'
                },
                gui: {
                    basehref: '<?= QBANK_BASE_HREF ?>'
                }
            };

            QBC = new QBankConnector(qbcConfig);

            mediaPicker = new QBC.mediaPicker({
                container: '#wrapper',
                defaultUseSize: '<?= QBANK_DEFAULT_IMAGE_SIZE ?>',
                defaultUseExtension: '<?= QBANK_DEFAULT_IMAGE_EXTENSION ?>',
                modules: {
                    content: {
                        header: false
                    }
                },
                onSelect: function (media, crop, previousUsage) {

                    //console.log('selected', media, crop, previousUsage);
                    $('#loader').show();
                    //$('div.media-modal-backdrop').addClass('media-modal-backdrop-on-top');

                    if (!previousUsage) {

                        var data = {
                            action: 'qbank_process_media_import',
                            security: '<?= wp_create_nonce('qbank_ajax_nonce') ?>',
                            data: {media: JSON.stringify(media), crop: JSON.stringify(crop)}
                        };

                        $.post('<?= admin_url('admin-ajax.php') ?>', data, function (response) {
                            console.log(response);
                            console.log($(parent.document).find('.attachments'));

                            $('#loader').hide();
                            $('#loader-msg').show().delay(3000).fadeOut();
                            //switchAndReload();

                            //$('#menu-item-browse', parent.document).trigger("click");


                        });

                    } else {
                        // This might be used in future version.
                        console.log('use this image:' + previousUsage.mediaUrl);
                        $('#loader').hide();
                    }

                }
            });
        });

    })(jQuery);
</script>
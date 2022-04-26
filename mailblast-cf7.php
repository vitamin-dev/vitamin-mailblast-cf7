<?php
/**
 * Plugin Name: MailBlast CF7 Integration
 * Description: Use CF7 forms to add subscribers to MailBlast
 * Author: Vitamin
 * Author URI: https://vitaminisgood.com
 * Version: 1.1.0
 * GitHub Plugin URI: vitamin-dev/vitamin-mailblast-cf7
 *
 * @package Vitamin\Plugins\MailBlastCf7
 * @author Vitamin
 */

define( 'MBCF7_PLUGIN', __FILE__ );
define( 'MBCF7_DIR', untrailingslashit( dirname( MBCF7_PLUGIN ) ) );
define( 'CM_API_DIR', MBCF7_DIR . '/cm' );

require MBCF7_DIR . '/vendor/autoload.php';
require_once MBCF7_DIR . '/admin.php';

/**
 * Parse CF7 Tags
 *
 * @param string $haystack Text with CF7 tags
 * @param array  $submission CF7 Submission data
 * @return string
 */
function mb_replace_cf7_tags( $haystack, $submission ) {
	$tag_only = false;
	if ( preg_match( '/^\[[\w-\d]+\]$/', $haystack, $is_tag ) === 1 ) {
		$tag_only = true;
	}

	foreach ( $submission as $key => $value ) {
		if ( strpos( $key, '_' ) === 0 ) { continue;
		}
		if ( $tag_only && strpos( $haystack, $key ) !== false ) {
			$haystack = $value;
			break;
		} else {
			$haystack = str_replace( '[' . $key . ']', ( is_array( $value ) ? implode( ', ', $value ) : $value ), $haystack );
		}
	}

	return $haystack;
}

/**
 * Send to MailBlast
 *
 * @param WPCF7_ContactForm $wpcf CF7 Form
 * @param bool              $abort Abort submission
 * @param WPCF7_Submission  $submission CF7 Form Submission
 * @return bool Abort submission
 */
function send_to_mailblast( $wpcf, $abort, $submission ) {
	$posted_data = $submission->get_posted_data();
	$form_id     = $wpcf->id;

	$api_key   = get_option( 'cf7_mb_apikey_' . $form_id );
	$list_id   = get_option( 'cf7_mb_listid_' . $form_id );
	$sub_email = get_option( 'cf7_mb_subscriber_email_' . $form_id );
	$sub_name  = get_option( 'cf7_mb_subscriber_name_' . $form_id );
	$use_cf    = get_option( 'cf7_mb_usecf_' . $form_id );
	$cf        = get_option( 'cf7_mb_custom_fields_' . $form_id );
	$no_email  = get_option( 'cf7_mb_noemail_' . $form_id );
	$sub_field = get_option( 'cf7_mb_subscribe_field_' . $form_id );

	$second_list  = get_option( 'cf7_mb_secondlist_' . $form_id );
	$list_id_2    = get_option( 'cf7_mb_listid_2_' . $form_id );
	$trigger_cond = get_option( 'cf7_mb_trigger_cond_' . $form_id );
	$use_cf_2     = get_option( 'cf7_mb_usecf_2_' . $form_id );
	$cf2          = get_option( 'cf7_mb_custom_fields_2_' . $form_id );

	if ( $api_key && $list_id ) {

		if ( $sub_field ) {
			$sub_field_posted = mb_replace_cf7_tags( $sub_field, $posted_data );
			if ( $sub_field_posted === $sub_field || ! $sub_field_posted ) {
				return $abort;
			}
		}

		$subscription = [
			'EmailAddress'   => mb_replace_cf7_tags( $sub_email, $posted_data ),
			'Name'           => mb_replace_cf7_tags( $sub_name, $posted_data ),
			'ConsentToTrack' => 'yes',
			'Resubscribe'    => true,
		];

		if ( $use_cf && count( $cf ) ) {
			$subscription['CustomFields'] = [];
			foreach ( $cf as $field ) {
				$field_value = mb_replace_cf7_tags( $field[1], $posted_data );
				if ( '_m' === substr( $field[0], -2 ) ) {
					$field_name   = substr( $field[0], 0, -2 );
					$field_values = array_map( fn( $v ) => trim( $v ), explode( ',', $field_value ) );
					foreach ( $field_values as $v ) {
						$subscription['CustomFields'][] = [
							'Key'   => $field_name,
							'Value' => $v,
						];
					}
				} else {
					$subscription['CustomFields'][] = [
						'Key'   => $field[0],
						'Value' => $field_value,
					];
				}
			}
		}

		$auth   = [ 'api_key' => $api_key ];
		$wrap   = new CS_REST_Subscribers( $list_id, $auth );
		$result = $wrap->add( $subscription );

		if ( $second_list && $trigger_cond && $list_id_2 ) {
			$trigger_cond_posted = mb_replace_cf7_tags( $trigger_cond, $posted_data );
			if ( $trigger_cond_posted !== $trigger_cond && $trigger_cond_posted ) {

				$subscription = [
					'EmailAddress'   => mb_replace_cf7_tags( $sub_email, $posted_data ),
					'Name'           => mb_replace_cf7_tags( $sub_name, $posted_data ),
					'ConsentToTrack' => 'yes',
					'Resubscribe'    => true,
				];

				if ( $use_cf_2 && count( $cf2 ) ) {
					$subscription['CustomFields'] = [];
					foreach ( $cf2 as $field ) {
						$field_value = mb_replace_cf7_tags( $field[1], $posted_data );
						if ( '_m' === substr( $field[0], -2 ) ) {
							$field_name   = substr( $field[0], 0, -2 );
							$field_values = array_map( fn( $v ) => trim( $v ), explode( ',', $field_value ) );
							foreach ( $field_values as $v ) {
								$subscription['CustomFields'][] = [
									'Key'   => $field_name,
									'Value' => $v,
								];
							}
						} else {
							$subscription['CustomFields'][] = [
								'Key'   => $field[0],
								'Value' => $field_value,
							];
						}
					}
				}

				$auth   = [ 'api_key' => $api_key ];
				$wrap   = new CS_REST_Subscribers( $list_id_2, $auth );
				$result = $wrap->add( $subscription );
			}
		}
	}

	return $abort;
}
add_action( 'wpcf7_before_send_mail', 'send_to_mailblast', 10, 3 );

/**
 * Skip Mail
 *
 * @param bool              $skip_mail Skip CF7 email
 * @param WPCF7_ContactForm $contact_form CF7 form
 * @return bool
 */
function skip_mail_mailblast( $skip_mail, $contact_form ) {
	$submission = WPCF7_Submission::get_instance();
	$data       = $submission->get_posted_data();
	$form_id    = $contact_form->id;
	$no_email   = get_option( 'cf7_mb_noemail_' . $form_id );
	$sub_field  = get_option( 'cf7_mb_subscribe_field_' . $form_id );
	$api_key    = get_option( 'cf7_mb_apikey_' . $form_id );
	$list_id    = get_option( 'cf7_mb_listid_' . $form_id );

	$mailvars = [
		'no_email'  => $no_email,
		'sub_field' => $sub_field,
		'api_key'   => $api_key,
		'list_id'   => $list_id,
	];

	if ( $no_email && ! $sub_field && $api_key && $list_id ) {
		return true;
	}
}
add_filter( 'wpcf7_skip_mail', 'skip_mail_mailblast', 10, 2 );

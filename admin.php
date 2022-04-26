<?php
/**
 * Admin Panel
 *
 * @package Vitamin\Plugins\MailBlastCf7
 * @author Vitamin
 * @version 1.1.0
 */

/**
 * Add Tab
 *
 * @param array $panels CF7 Tabs
 * @return array
 */
function add_mailblast_tab( $panels ) {
	$new_page = [
		'mailblast-Extension' => [
			'title'    => __( 'MailBlast', 'contact-form-7' ),
			'callback' => 'mailblast_cf7_settings',
		],
	];

	$panels = array_merge( $panels, $new_page );
	return $panels;
}
add_filter( 'wpcf7_editor_panels', 'add_mailblast_tab' );

/**
 * CF7 Tags
 *
 * @return array
 */
function mb_cf7_tags() {
	$manager   = class_exists( 'WPCF7_FormTagsManager' ) ? WPCF7_FormTagsManager::get_instance() : WPCF7_ShortcodeManager::get_instance();
	$form_tags = $manager->get_scanned_tags();
	return $form_tags;
}

/**
 * Settings HTML
 */
function mailblast_cf7_settings() {
	$form = $_GET['post']; // phpcs:ignore WordPress.Security.NonceVerification
	?>
	<h2>MailBlast</h2>
	<div class="mailblast">
	<ul class="mb-form">
		<li>
		<label>
			Client API Key
			<input type="text" name="api_key" value="<?php echo esc_attr( get_option( 'cf7_mb_apikey_' . $form ) ); ?>">
		</label>
		</li>
		<li>
		<label>
			API Subscriber List ID
			<input type="text" name="list_id" value="<?php echo esc_attr( get_option( 'cf7_mb_listid_' . $form ) ); ?>">
		</label>
		</li>
		<li>
		<label>
			<input type="checkbox" name="no_email" <?php if ( get_option( 'cf7_mb_noemail_' . $form ) ) { echo 'checked=""';} ?>>
			Prevent this form from sending email?
		</label>
		</li>
		<li>
		<hr>
		</li>
		<li>
		<?php
		foreach ( mb_cf7_tags() as $tag ) {
			if ( 'submit' === $tag['type'] ) { continue;
			}
			echo '<input onclick="this.select();" value="[' . esc_attr( $tag['name'] ) . ']" class="wpcf7-mb-tag" readonly="readonly">';
		}
		?>
		</li>
		<li>
		<label>
			Subscriber Email
			<input type="text" name="subscriber_email" value="<?php echo esc_attr( get_option( 'cf7_mb_subscriber_email_' . $form ) ); ?>">
		</label>
		</li>
		<li>
		<label>
			Subscriber Name
			<input type="text" name="subscriber_name" value="<?php echo esc_attr( get_option( 'cf7_mb_subscriber_name_' . $form ) ); ?>">
		</label>
		</li>
		<li>
		<label>
			Subscribe field
			<input type="text" name="subscribe_field" value="<?php echo esc_attr( get_option( 'cf7_mb_subscribe_field_' . $form ) ); ?>">
			<small>Enter a mail-tag to only send submissions to MailBlast when the field is checked. Using this will ignore the "prevent email" setting. Leave blank to always send submissions to MailBlast.</small>
		</label>
		</li>
		<li>
		<label>
			<input type="checkbox" name="use_custom_fields" <?php if ( get_option( 'cf7_mb_usecf_' . $form ) ) { echo 'checked=""';} ?>>
			Include custom fields?
		</label>
		</li>
		<li class="mb-custom-fields">
		<ul class="mb-cf-list">
			<li class="mb-cf-list-template">
			<table>
				<thead>
				<tr>
					<th>Custom Field Name</th>
					<th>Value</th>
					<th></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>
					<input type="text" name="cf_name[]">
					</td>
					<td>
					<input type="text" name="cf_value[]">
					</td>
					<td>
					<button type="button">Delete</button>
					</td>
				</tr>
				</tbody>
			</table>
			</li>
			<?php
			$saved_cf = get_option( 'cf7_mb_custom_fields_' . $form );
			if ( ! $saved_cf ) {
				$saved_cf = [];
			}
			foreach ( $saved_cf as $cf ) {
				$cf_name  = $cf[0];
				$cf_value = $cf[1];
				?>
			<li>
			<table>
				<thead>
				<tr>
					<th>Custom Field Name</th>
					<th>Value</th>
					<th></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>
					<input type="text" name="cf_name[]" value="<?php echo esc_attr( $cf_name ); ?>">
					</td>
					<td>
					<input type="text" name="cf_value[]" value="<?php echo esc_attr( $cf_value ); ?>">
					</td>
					<td>
					<button type="button">Delete</button>
					</td>
				</tr>
				</tbody>
			</table>
			</li>
			<?php } ?>
		</ul>
		<p><small>For custom fields that accept multiple values, append the field name with <code>_m</code>. The value will automatically split on <code>,</code>.</small></p>
		<button type="button" class="mb-cf-add">Add Field</button>
		</li>
		<li>
		<hr>
		</li>
		<li>
		<label>
			<input type="checkbox" name="second_list" <?php if ( get_option( 'cf7_mb_secondlist_' . $form ) ) { echo 'checked=""';} ?>>
			Conditionally send to another list?
		</label>
		</li>
		<li class="second-list">
		<ul>
			<li>
			<label>
				Second API Subscriber List ID
				<input type="text" name="list_id_2" value="<?php echo esc_attr( get_option( 'cf7_mb_listid_2_' . $form ) ); ?>">
			</label>
			</li>
			<li>
			<label>
				Trigger field
				<input type="text" name="trigger_cond" value="<?php echo esc_attr( get_option( 'cf7_mb_trigger_cond_' . $form ) ); ?>">
				<small>Enter a mail-tag to send the submission to a second list when the field is checked.</small>
			</label>
			</li>
			<li>
			<label>
				<input type="checkbox" name="use_custom_fields_2" <?php if ( get_option( 'cf7_mb_usecf_2_' . $form ) ) { echo 'checked=""';} ?>>
				Include custom fields?
			</label>
			</li>
			<li class="mb-custom-fields">
			<ul class="mb-cf-list">
				<li class="mb-cf-list-template">
				<table>
					<thead>
					<tr>
						<th>Custom Field Name</th>
						<th>Value</th>
						<th></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>
						<input type="text" name="cf_2_name[]">
						</td>
						<td>
						<input type="text" name="cf_2_value[]">
						</td>
						<td>
						<button type="button">Delete</button>
						</td>
					</tr>
					</tbody>
				</table>
				</li>
				<?php
				$saved_cf = get_option( 'cf7_mb_custom_fields_2_' . $form );
				foreach ( $saved_cf as $cf ) {
					$cf_name  = $cf[0];
					$cf_value = $cf[1];
					?>
				<li>
				<table>
					<thead>
					<tr>
						<th>Custom Field Name</th>
						<th>Value</th>
						<th></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>
						<input type="text" name="cf_2_name[]" value="<?php echo esc_attr( $cf_name ); ?>">
						</td>
						<td>
						<input type="text" name="cf_2_value[]" value="<?php echo esc_attr( $cf_value ); ?>">
						</td>
						<td>
						<button type="button">Delete</button>
						</td>
					</tr>
					</tbody>
				</table>
				</li>
				<?php } ?>
			</ul>
			<p><small>For custom fields that accept multiple values, append the field name with <code>_m</code>. The value will automatically split on <code>,</code>.</small></p>
			<button type="button" class="mb-cf-add">Add Field</button>
			</li>
		</ul>
		</li>
	</ul>
	</div>
	<?php
}

/**
 * Enqueue Scripts and Styles
 */
function mailblast_styles_scripts() {
	wp_enqueue_style(
		'mailblast',
		plugins_url( '/css/styles.css', MBCF7_PLUGIN ),
		[],
		filemtime( MBCF7_DIR . '/css/styles.css' )
	);
	wp_enqueue_script(
		'mailblast',
		plugins_url( '/js/mailblast.js', MBCF7_PLUGIN ),
		[ 'jquery' ],
		filemtime( MBCF7_DIR . '/js/mailblast.js' ),
		true
	);
}
add_action( 'admin_enqueue_scripts', 'mailblast_styles_scripts' );

/**
 * Handle Settings Save
 */
function update_mbcf7_forms() {
	$cf = [];

	if ( ! $_POST['cf_value'] ) { $_POST['cf_value'] = [];
	}

	foreach ( $_POST['cf_name'] as $i => $cf_name ) {
		if ( ! $cf_name || ! $_POST['cf_value'][ $i ] ) { continue;
		}
		$cf[] = [
			$cf_name,
			$_POST['cf_value'][ $i ],
		];
	}

	$cf2 = [];

	if ( ! $_POST['cf_2_value'] ) { $_POST['cf_2_value'] = [];
	}

	foreach ( $_POST['cf_2_name'] as $i => $cf_name ) {
		if ( ! $cf_name || ! $_POST['cf_2_value'][ $i ] ) { continue;
		}
		$cf2[] = [
			$cf_name,
			$_POST['cf_2_value'][ $i ],
		];
	}

	update_option( 'cf7_mb_apikey_' . $_POST['post_ID'], $_POST['api_key'] );
	update_option( 'cf7_mb_listid_' . $_POST['post_ID'], $_POST['list_id'] );
	update_option( 'cf7_mb_listid_2_' . $_POST['post_ID'], $_POST['list_id_2'] );
	update_option( 'cf7_mb_subscriber_email_' . $_POST['post_ID'], $_POST['subscriber_email'] );
	update_option( 'cf7_mb_subscriber_name_' . $_POST['post_ID'], $_POST['subscriber_name'] );
	update_option( 'cf7_mb_custom_fields_' . $_POST['post_ID'], $cf );
	update_option( 'cf7_mb_usecf_' . $_POST['post_ID'], $_POST['use_custom_fields'] );
	update_option( 'cf7_mb_custom_fields_2_' . $_POST['post_ID'], $cf2 );
	update_option( 'cf7_mb_usecf_2_' . $_POST['post_ID'], $_POST['use_custom_fields_2'] );
	update_option( 'cf7_mb_noemail_' . $_POST['post_ID'], $_POST['no_email'] );
	update_option( 'cf7_mb_subscribe_field_' . $_POST['post_ID'], $_POST['subscribe_field'] );
	update_option( 'cf7_mb_secondlist_' . $_POST['post_ID'], $_POST['second_list'] );
	update_option( 'cf7_mb_trigger_cond_' . $_POST['post_ID'], $_POST['trigger_cond'] );
}
add_action( 'wpcf7_after_save', 'update_mbcf7_forms' );

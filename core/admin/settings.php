<?php
// Exit if loaded directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class RenderEDD_AdminPage_Settings
 *
 * Provides the admin page for adjusting Render EDD settings.
 *
 * @since      1.0.0
 *
 * @package    Render
 * @subpackage Admin
 */
class RenderEDD_AdminPage_Settings extends Render_EDD {

	/**
	 * Constructs the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Function for the admin menu to create a menu item in the settings tree.
	 *
	 * @since 1.0.0
	 */
	public function menu() {

		add_submenu_page(
			'render-settings',
			'Easy Digital Downloads',
			'Easy Digital Downloads',
			'manage_options',
			'renderedd-settings',
			array( $this, 'page_output' )
		);
	}

	/**
	 * Actions hooked only into this admin page.
	 *
	 * @since 1.0.0
	 */
	public static function page_specific() {
		add_action( 'admin_body_class', array( __CLASS__, 'body_class' ) );
	}

	/**
	 * Registers Render settings for the options page.
	 *
	 * @since 1.0.0
	 */
	public static function register_settings() {

		// EDD Licensing
		register_setting( 'renderedd_options', 'renderedd_license_key', 'edd_renderedd_sanitize_license' );
	}

	/**
	 * Adds on custom admin body classes.
	 *
	 * @since 1.0.0
	 *
	 * @param string $classes Admin body classes.
	 * @return string New classes.
	 */
	public static function body_class( $classes ) {

		$classes .= 'render render-options renderedd';

		return $classes;
	}

	/**
	 * Display the admin page.
	 *
	 * @since 1.0.0
	 */
	public function page_output() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$license       = get_option( 'renderedd_license_key' );
		$status        = get_option( 'renderedd_license_status' );

		require( ABSPATH . 'wp-admin/options-head.php' );
		?>
		<div class="wrap render-wrap">
			<h2 class="render-page-title">
				<img src="<?php echo RENDER_URL; ?>/assets/images/render-logo.svg" class="render-page-title-logo"/>
				<?php _e( 'Settings', 'Render_EDD' ); ?>
			</h2>

			<form method="post" action="options.php">

				<?php settings_fields( 'renderedd_options' ); ?>

				<table class="render-table">
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'License Key', 'Render_EDD' ); ?>
						</th>
						<td>
							<label>
								<input id="renderedd_license_key" name="renderedd_license_key" type="text"
								       class="regular-text" value="<?php esc_attr_e( $license ); ?>"/>
							</label>
						</td>
					</tr>
					<?php if ( ! empty( $license ) ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e( 'Activate License', 'Render_EDD' ); ?>
							</th>
							<td>
								<?php if ( $status !== false && $status == 'valid' ) { ?>
									<?php wp_nonce_field( 'edd_renderedd_nonce', 'edd_renderedd_nonce' ); ?>
									<span class="render-license-status valid">
										<span class="dashicons dashicons-yes"></span>
										<?php _e( 'active', 'Render_EDD' ); ?>
									</span>
									<input type="submit" class="button-secondary button-red"
									       name="renderedd_license_deactivate"
									       value="<?php _e( 'Deactivate License', 'Render_EDD' ); ?>"/>
								<?php } else {
									wp_nonce_field( 'edd_renderedd_nonce', 'edd_renderedd_nonce' ); ?>
									<span class="render-license-status invalid">
										<span class="dashicons dashicons-no"></span>
										<?php _e( 'inactive', 'Render_EDD' ); ?>
									</span>
									<input type="submit" class="button-secondary" name="renderedd_license_activate"
									       value="<?php _e( 'Activate License', 'Render_EDD' ); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</table>

				<?php submit_button(); ?>

			</form>

		</div>
	<?php
	}
}

new RenderEDD_AdminPage_Settings();

function edd_renderedd_sanitize_license( $new ) {
	$old = get_option( 'renderedd_license_key' );
	if ( $old && $old != $new ) {
		delete_option( 'renderedd_license_status' ); // new license has been entered, so must reactivate
	}

	return $new;
}
<?php
/*
Plugin Name: Render Easy Digital Downloads
Description: Integrates Easy Digital Downloads with Render for improved shortcode capabilities.
Version: 0.1.0
Author: Joel Worsham & Kyle Maurer
Author URI: http://renderwp.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: Render_EDD
Domain Path: /languages/
*/

// Exit if loaded directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// Licensing
require_once __DIR__ . '/core/licensing/licensing.php';

// Define all plugin constants.

/**
 * The version of Render.
 *
 * @since 1.0.0
 */
define( 'RENDER_EDD_VERSION', '0.1.0' );

/**
 * The absolute server path to Render's root directory.
 *
 * @since 1.0.0
 */
define( 'RENDER_EDD_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The URI to Render's root directory.
 *
 * @since 1.0.0
 */
define( 'RENDER_EDD_URL', plugins_url( '', __FILE__ ) );

/**
 * Class Render_EDD
 *
 * Initializes and loads the plugin.
 *
 * @since   0.1.0
 *
 * @package Render_EDD
 */
class Render_EDD {

	/**
	 * Constructs the plugin.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since 0.1.0
	 */
	public function init() {

		// Bail if Render isn't loaded
		if ( ! class_exists( 'Render' ) || ! class_exists( 'Easy_Digital_Downloads' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'notice' ) );

			return;
		}

		// Files required to run
		$this->require_files();

		// Add the shortcodes to Render
		$this->add_shortcodes();

		// Translation ready
		load_plugin_textdomain( 'Render_EDD', false, RENDER_EDD_PATH . '/languages' );

		// Add EDD styles to tinymce
		add_filter( 'render_editor_styles', array( __CLASS__, 'add_edd_style') );

		add_filter( 'render_editor_styles', array( __CLASS__, 'add_render_edd_style' ) );
	}

	/**
	 * Requires necessary plugin files.
	 *
	 * @since 0.1.0
	 */
	private function require_files() {

		// Global helper functions.
		require_once __DIR__ . '/core/helper-functions.php';

		// Admin settings
		require_once __DIR__ . '/core/admin/settings.php';
	}

	/**
	 * Adds the EDD stylesheet to the TinyMCE.
	 *
	 * EDD doesn't register the stylesheet, so I can't grab it that way, but Pippin mentioned I can just call the function
	 * to enqueue the style, grab the stylesheet, and then dequeue it pretty easily.
	 *
	 * @since 0.1.0
	 *
	 * @param array $styles All stylesheets registered for the TinyMCE through Render.
	 * @return array The styles.
	 */
	public static function add_edd_style( $styles ) {

		global $wp_styles;

		edd_register_styles();

		if ( isset( $wp_styles->registered['edd-styles'] ) ) {
			$styles[] = $wp_styles->registered['edd-styles']->src;
		}

		wp_dequeue_style( 'edd-styles' );

		return $styles;
	}

	/**
	 * Adds the Render EDD stylesheet to the TinyMCE through Render.
	 *
	 * @since 0.1.0
	 *
	 * @param array $styles All stylesheets registered for the TinyMCE through Render.
	 * @return array The styles.
	 */
	public static function add_render_edd_style( $styles ) {

		$styles[] = RENDER_EDD_URL . "/assets/css/render-edd.css";
		return $styles;
	}

	/**
	 * Add data and inputs for all EDD shortcodes and pass them through Render's function.
	 *
	 * @since 0.1.0
	 */
	private function add_shortcodes() {

		global $edd_options;

		foreach (
			array(
				// Download Cart
				array(
					'code'        => 'download_cart',
					'function'    => 'edd_cart_shortcode',
					'title'       => __( 'Download Cart', 'Render_EDD' ),
					'description' => __( 'Lists items in cart.', 'Render_EDD' ),
					'tags'        => 'cart edd ecommerce downloads digital products',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Download Checkout
				array(
					'code'        => 'download_checkout',
					'function'    => 'edd_checkout_form_shortcode',
					'title'       => __( 'Download Checkout', 'Render_EDD' ),
					'description' => __( 'Displays the checkout form.', 'Render_EDD' ),
					'tags'        => 'cart edd ecommerce downloads digital products form',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Download History
				array(
					'code'        => 'download_history',
					'function'    => 'edd_download_history',
					'title'       => __( 'Download History', 'Render_EDD' ),
					'description' => __( 'Displays all the products a user has purchased with links to the files.', 'Render_EDD' ),
					'tags'        => 'edd ecommerce downloads digital products history files purchase',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Purchase History
				array(
					'code'        => 'purchase_history',
					'function'    => 'edd_purchase_history',
					'title'       => __( 'Purchase History', 'Render_EDD' ),
					'description' => __( 'Displays the complete purchase history for a user.', 'Render_EDD' ),
					'tags'        => 'edd ecommerce downloads digital products history purchase',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Download Discounts
				array(
					'code'        => 'download_discounts',
					'function'    => 'edd_discounts_shortcode',
					'title'       => __( 'Download Discounts', 'Render_EDD' ),
					'description' => __( 'Lists all the currently available discount codes on your site.', 'Render_EDD' ),
					'tags'        => 'edd ecommerce downloads digital products coupon discount code',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Profile Editor
				array(
					'code'        => 'edd_profile_editor',
					'function'    => 'edd_profile_editor_shortcode',
					'title'       => __( 'EDD Profile Editor', 'Render_EDD' ),
					'description' => __( 'Presents users with a form for updating their profile.', 'Render_EDD' ),
					'tags'        => 'edd ecommerce downloads digital user profile account',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Login
				array(
					'code'        => 'edd_login',
					'function'    => 'edd_login_form_shortcode',
					'title'       => __( 'EDD Login', 'Render_EDD' ),
					'description' => __( 'Displays a simple login form for non-logged in users.', 'Render_EDD' ),
					'tags'        => 'edd ecommerce downloads login users form',
					'atts'        => array(
						'redirect' => array(
							'label'       => __( 'Redirect', 'Render_EDD' ),
							'description' => __( 'Redirect to this page after login.', 'Render_EDD' ),
							'type'        => 'selectbox',
							'properties'  => array(
								'allowCustomInput' => true,
								'groups'           => array(),
								'callback'         => array(
									'groups'   => true,
									'function' => 'render_sc_post_list',
								),
								'placeholder'      => __( 'Same page', 'Render_EDD' ),
							),
						),
					),
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Register
				array(
					'code'        => 'edd_register',
					'function'    => 'edd_register_form_shortcode',
					'title'       => __( 'EDD Register', 'Render_EDD' ),
					'description' => __( 'Displays a registration form for non-logged in users.', 'Render_EDD' ),
					'tags'        => 'edd ecommerce downloads login users form register signup',
					'atts'        => array(
						'redirect' => array(
							'label'       => __( 'Redirect', 'Render_EDD' ),
							'description' => __( 'Redirect to this page after login.', 'Render_EDD' ),
							'type'        => 'selectbox',
							'properties'  => array(
								'allowCustomInput' => true,
								'groups'           => array(),
								'callback'         => array(
									'groups'   => true,
									'function' => 'render_sc_post_list',
								),
								'placeholder'      => __( 'Same page', 'Render_EDD' ),
							),
						),
					),
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Price
				array(
					'code'        => 'edd_price',
					'function'    => 'edd_download_price_shortcode',
					'title'       => __( 'Download Price', 'Render_EDD' ),
					'description' => __( 'Displays the price of a specific download.', 'Render_EDD' ),
					'tags'        => 'edd ecommerce downloads product price',
					'atts'        => array(
						'id'       => render_edd_sc_attr_template( 'downloads', array(
							'required' => true,
						) ),
						'price_id' => array(
							'label'       => __( 'Price ID', 'Render_EDD' ),
							'description' => __( 'Optional. For variable pricing.', 'Render_EDD' ),
						),
					),
					'render'      => true,
				),
				// Receipt
				array(
					'code'        => 'edd_receipt',
					'function'    => 'edd_receipt_shortcode',
					'title'       => __( 'Download Receipt', 'Render_EDD' ),
					'description' => __( 'Displays a the complete details of a completed purchase.', 'Render_EDD' ),
					'tags'        => 'edd ecommerce downloads purchase receipt confirmation order payment complete checkout',
					'atts'        => array(
						'error'       => array(
							'label'      => __( 'Error Message', 'Render_EDD' ),
							'properties' => array(
								'placeholder' => __( 'Sorry, trouble retrieving payment receipt.', 'edd' ),
							),
						),
						'price'       => array(
							'label'      => __( 'Hide Price', 'Render_EDD' ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'discount'    => array(
							'label'      => __( 'Hide Discounts', 'Render_EDD' ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'products'    => array(
							'label'      => __( 'Hide Products', 'Render_EDD' ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'date'        => array(
							'label'      => __( 'Hide Purchase Date', 'Render_EDD' ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'payment_key' => array(
							'label'      => __( 'Hide Payment Key', 'Render_EDD' ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'payment_id'  => array(
							'label'      => __( 'Hide Order Number', 'Render_EDD' ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
					),
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Purchase Link
				array(
					'code'        => 'purchase_link',
					'function'    => 'edd_download_shortcode',
					'title'       => __( 'Download Purchase Link', 'Render_EDD' ),
					'description' => __( 'Displays a button which adds a specific product to the cart.', 'Render_EDD' ),
					'tags'        => 'edd ecommerce downloads purchase product buy button pay link checkout',
					'atts'        => array(
						'id'       => render_edd_sc_attr_template( 'downloads', array(
							'required' => true,
						) ),
						'price'    => array(
							'label'      => __( 'Hide Price', 'Render_EDD' ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'text'     => array(
							'label'      => __( 'Link Text', 'Render_EDD' ),
							'properties' => array(
								'placeholder' => isset( $edd_options['add_to_cart_text'] ) && $edd_options['add_to_cart_text'] != '' ? $edd_options['add_to_cart_text'] : __( 'Purchase', 'edd' ),
							),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Style', 'Render_EDD' ),
						),
						'style'    => array(
							'label'      => __( 'Style', 'Render_EDD' ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'   => isset( $edd_options['button_style'] ) && $edd_options['button_style'] == 'plain',
								'values' => array(
									'button' => __( 'Button', 'Render_EDD' ),
									'plain'   => __( 'Text', 'Render_EDD' ),
								),
							),
						),
						'color'    => array(
							'label'      => __( 'Button Color', 'Render_EDD' ),
							'type'       => 'selectbox',
							'default'    => isset( $edd_options['checkout_color'] ) ? $edd_options['checkout_color'] : 'blue',
							'properties' => array(
								'options' => array(
									'white'     => __( 'White', 'Render_EDD' ),
									'gray'      => __( 'Gray', 'Render_EDD' ),
									'blue'      => __( 'Blue', 'Render_EDD' ),
									'red'       => __( 'Red', 'Render_EDD' ),
									'green'     => __( 'Green', 'Render_EDD' ),
									'yellow'    => __( 'Yellow', 'Render_EDD' ),
									'orange'    => __( 'Orange', 'Render_EDD' ),
									'dark gray' => __( 'Dark gray', 'Render_EDD' ),
									'inherit'   => __( 'Inherit', 'Render_EDD' ),
								),
							),
						),
						'sku'      => array(
							'label'    => __( 'SKU', 'Render_EDD' ),
							'description'    => __( 'Get download by SKU (overrides download set above)', 'Render_EDD' ),
							'advanced' => true,
						),
						'direct'   => array(
							'label'      => __( 'Direct Purchase', 'Render_EDD' ),
							'type'       => 'checkbox',
							'properties' => array(
								'label' => __( 'Send customer to directly to PayPal', 'Render_EDD' ),
							),
							'advanced'   => true,
						),
						'class'    => array(
							'label'    => __( 'CSS Class', 'Render_EDD' ),
							'default'  => 'edd-submit',
							'advanced' => true,
						),
						'form_id'  => array(
							'label'    => __( 'Form ID', 'Render_EDD' ),
							'default'  => '',
							'advanced' => true,
						),
					),
					'render'      => array(
						'noStyle' => true,
					),
				),
				// Purchase Collection
				array(
					'code'        => 'purchase_collection',
					'function'    => 'edd_purchase_collection_shortcode',
					'title'       => __( 'Download Purchase Collection', 'Render_EDD' ),
					'description' => __( 'Displays a button which adds all products in a specific taxonomy term to the cart.', 'Render_EDD' ),
					'tags'        => 'edd ecommerce downloads purchase product buy button pay link checkout',
					'atts'        => array(
						'taxonomy' => array(
							'label'      => __( 'Taxonomy', 'Render_EDD' ),
							'type'       => 'selectbox',
							'required' => true,
							'properties' => array(
								'options' => array(
									'download_category' => __( 'Category', 'Render_EDD' ),
									'download_tag'      => __( 'Tag', 'Render_EDD' ),
								),
							),
						),
						'terms'    => array(
							'label'       => __( 'Terms', 'Render_EDD' ),
							'required' => true,
							'description' => __( 'Enter a comma separated list of terms for the selected taxonomy.', 'Render_EDD' ),
						),
						'text'     => array(
							'label'   => __( 'Link Text', 'Render_EDD' ),
							'default' => __( 'Purchase All Items', 'edd' ),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Style', 'Render_EDD' ),
						),
						'style'    => array(
							'label'      => __( 'Style', 'Render_EDD' ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'   => isset( $edd_options['button_style'] ) && $edd_options['button_style'] == 'plain',
								'values' => array(
									'button' => __( 'Button', 'Render_EDD' ),
									'plain'   => __( 'Text', 'Render_EDD' ),
								),
							),
						),
						'color'    => array(
							'label'      => __( 'Button Color', 'Render_EDD' ),
							'type'       => 'selectbox',
							'default'    => isset( $edd_options['checkout_color'] ) ? $edd_options['checkout_color'] : 'blue',
							'properties' => array(
								'options' => array(
									'gray'      => __( 'Gray', 'Render_EDD' ),
									'blue'      => __( 'Blue', 'Render_EDD' ),
									'green'     => __( 'Green', 'Render_EDD' ),
									'dark gray' => __( 'Dark gray', 'Render_EDD' ),
									'yellow'    => __( 'Yellow', 'Render_EDD' ),
								),
							),
						),
						'class'    => array(
							'label'    => __( 'CSS Class', 'Render_EDD' ),
							'default'  => 'edd-submit',
							'advanced' => true,
						),
					),
					'render'      => array(
						'noStyle' => true,
					),
				),
				// Downloads
				array(
					'code'        => 'downloads',
					'function'    => 'edd_downloads_query',
					'title'       => __( 'Downloads', 'Render_EDD' ),
					'description' => __( 'Outputs a list or grid of downloadable products.', 'Render_EDD' ),
					'tags'        => 'edd ecommerce downloads purchase product list',
					'atts'        => array(
						array(
							'type'  => 'section_break',
							'label' => __( 'Downloads', 'Render_EDD' ),
						),
						'category'         => array(
							'label'      => __( 'Categories', 'Render_EDD' ),
							'type'       => 'selectbox',
							'properties' => array(
								'placeholder' => __( 'Download category', 'Render_EDD' ),
								'multi'       => true,
								'callback'    => array(
									'function' => 'render_edd_get_categories',
								),
							),
						),
						'tags'             => array(
							'label'      => __( 'Tags', 'Render_EDD' ),
							'type'       => 'selectbox',
							'properties' => array(
								'placeholder' => __( 'Download tag', 'Render_EDD' ),
								'multi'       => true,
								'callback'    => array(
									'function' => 'render_edd_get_tags',
								),
							),
						),
						'relation'         => array(
							'label'       => __( 'Relation', 'Render_EDD' ),
							'description' => __( 'Downloads must be in ALL categories / tags, or at least just one.', 'Render_EDD' ),
							'type'        => 'toggle',
							'properties'  => array(
								'values' => array(
									'AND' => __( 'All', 'Render_EDD' ) . '&nbsp;', // For spacing in the toggle switch
									'OR'  => __( 'One', 'Render_EDD' ),
								),
							),
						),
						'exclude_category' => array(
							'label'      => __( 'Exclude Categories', 'Render_EDD' ),
							'type'       => 'selectbox',
							'properties' => array(
								'placeholder' => __( 'Download category', 'Render_EDD' ),
								'multi'       => true,
								'callback'    => array(
									'function' => 'render_edd_get_categories',
								),
							),
						),
						'exclude_tags'     => array(
							'label'      => __( 'Exclude Tags', 'Render_EDD' ),
							'type'       => 'selectbox',
							'properties' => array(
								'placeholder' => __( 'Download tag', 'Render_EDD' ),
								'multi'       => true,
								'callback'    => array(
									'function' => 'render_edd_get_tags',
								),
							),
						),
						'number'           => array(
							'label'      => __( 'Download Count', 'Render_EDD' ),
							'type'       => 'counter',
							'default'    => 9,
							'properties' => array(
								'min' => 1,
								'max' => 50,
							),
						),
						'ids'              => render_edd_sc_attr_template(
							'downloads',
							array(
								'label'       => __( 'Downloads', 'Render_EDD' ),
								'description' => __( 'Enter one or more downloads to use ONLY these downloads.', 'Render_EDD' ),
							), array(
								'multi' => true,
							)
						),
						'orderby'          => array(
							'label'      => __( 'Order By', 'Render_EDD' ),
							'type'       => 'selectbox',
							'default'    => 'post_date',
							'properties' => array(
								'options' => array(
									'price'     => __( 'Price', 'Render_EDD' ),
									'id'        => __( 'ID', 'Render_EDD' ),
									'random'    => __( 'Random', 'Render_EDD' ),
									'post_date' => __( 'Published date', 'Render_EDD' ),
									'title'     => __( 'Title', 'Render_EDD' ),
								),
							),
						),
						'order'            => array(
							'label'      => __( 'Order', 'Render_EDD' ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'DESC' => __( 'Descending', 'Render_EDD' ),
									'ASC'  => __( 'Ascending', 'Render_EDD' ),
								),
							),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Visibility', 'Render_EDD' ),
						),
						'price'            => array(
							'label'      => __( 'Price', 'Render_EDD' ),
							'type'       => 'toggle',
							'properties' => array(
								'deselectStyle' => true,
								'values'        => array(
									'no'  => __( 'Hide', 'Render_EDD' ),
									'yes' => __( 'Show', 'Render_EDD' ),
								),
							),
						),
						'excerpt'          => array(
							'label'      => __( 'Excerpt', 'Render_EDD' ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'          => true,
								'deselectStyle' => true,
								'values'        => array(
									'no'  => __( 'Hide', 'Render_EDD' ),
									'yes' => __( 'Show', 'Render_EDD' ),
								),
							),
						),
						'full_content'     => array(
							'label'      => __( 'Full Content', 'Render_EDD' ),
							'type'       => 'toggle',
							'properties' => array(
								'deselectStyle' => true,
								'values'        => array(
									'no'  => __( 'Hide', 'Render_EDD' ),
									'yes' => __( 'Show', 'Render_EDD' ),
								),
							),
						),
						'buy_button'       => array(
							'label'      => __( 'Buy Button', 'Render_EDD' ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'          => true,
								'deselectStyle' => true,
								'values'        => array(
									'no'  => __( 'Hide', 'Render_EDD' ),
									'yes' => __( 'Show', 'Render_EDD' ),
								),
							),
						),
						'thumbnails'       => array(
							'label'      => __( 'Thumbnails', 'Render_EDD' ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'          => true,
								'deselectStyle' => true,
								'values'        => array(
									'false' => __( 'Hide', 'Render_EDD' ),
									'true'  => __( 'Show', 'Render_EDD' ),
								),
							),
						),
						'columns'          => array(
							'label'      => __( 'Columns', 'Render_EDD' ),
							'type'       => 'counter',
							'default'    => 3,
							'properties' => array(
								'min' => 0,
								'max' => 6,
							),
						),
					),
					'render'      => array(
						'displayBlock' => true,
					)
				),
			) as $shortcode
		) {

			$shortcode['category'] = 'ecommerce';
			$shortcode['source']   = 'Easy Digital Downloads';

			render_add_shortcode( $shortcode );
			render_add_shortcode_category( array(
				'id'    => 'ecommerce',
				'label' => __( 'Ecommerce', 'Render_EDD' ),
				'icon'  => 'dashicons-cart',
			) );
		}
	}

	/**
	 * Display a notice in the admin if EDD and Render are not both active.
	 *
	 * @since 0.1.0
	 */
	static function notice() {
		?>
		<div class="error">
			<p>
				<?php
				printf(
					__( 'You have activated a plugin that requires %s and %s. Please install and activate both to continue using Render EDD.', 'Render_EDD' ),
					'<a href="http://renderwp.com/?utm_source=Render%20EDD&utm_medium=Notice&utm_campaign=Render%20EDD%20Notice
">Render</a>',
					'<a href="http://easydigitaldownloads.com/?utm_source=Render%20EDD&utm_medium=Notice&utm_campaign=Render%20EDD%20Notice">Easy Digital Downloads</a>'
				);
				?>
			</p>
		</div>
	<?php
	}
}

$render_edd = new Render_EDD();

/**
 * TinyMCE callback for the EDD Login Form shortcode.
 *
 * Logs out the user before calling the original shortcode callback.
 *
 * @since 0.1.0
 * @access Private
 *
 * @param array  $atts    The attributes sent to the shortcode.
 * @param string $content The content inside the shortcode.
 * @return string Shortcode output,
 */
function edd_login_form_shortcode_tinymce( $atts = array(), $content = '' ) {

	// Log out for displaying this shortcode
	render_tinyme_log_out();

	$output = edd_login_form_shortcode( $atts, $content );
	return $output;
}

/**
 * TinyMCE callback for the EDD Register Form shortcode.
 *
 * Logs out the user before calling the original shortcode callback.
 *
 * @since 0.1.0
 *
 * @access Private
 *
 * @param array  $atts    The attributes sent to the shortcode.
 * @param string $content The content inside the shortcode.
 * @return string Shortcode output.
 */
function edd_register_form_shortcode_tinymce( $atts = array(), $content = '' ) {

	// Log out for displaying this shortcode
	render_tinyme_log_out();

	$output = edd_register_form_shortcode( $atts, $content );
	return $output;
}
<?php
/*
Plugin Name: Render Woocommerce
Description: Integrates Woocommerce with Render for improved shortcode capabilities.
Version: 0.1.0
Author: Joel Worsham & Kyle Maurer
Author URI: http://renderwp.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: Render_Woocommerce
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
define( 'RENDER_WOOCOMMERCE_VERSION', '0.1.0' );

/**
 * The absolute server path to Render's root directory.
 *
 * @since 1.0.0
 */
define( 'RENDER_WOOCOMMERCE_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The URI to Render's root directory.
 *
 * @since 1.0.0
 */
define( 'RENDER_WOOCOMMERCE_URL', plugins_url( '', __FILE__ ) );

/**
 * Class Render_Woocommerce
 *
 * Initializes and loads the plugin.
 *
 * @since   0.1.0
 *
 * @package Render_Woocommerce
 */
class Render_Woocommerce {

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
		if ( ! class_exists( 'Render' ) || ! class_exists( 'Woocommerce' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'notice' ) );

			return;
		}

		// Files required to run
		$this->require_files();

		// Add the shortcodes to Render
		$this->add_shortcodes();

		// Translation ready
		load_plugin_textdomain( 'Render_Woocommerce', false, RENDER_WOOCOMMERCE_PATH . '/languages' );

		// Add Woocommerce styles to tinymce
		add_filter( 'render_editor_styles', array( __CLASS__, 'add_woocommerce_style') );

		add_filter( 'render_editor_styles', array( __CLASS__, 'add_render_woocommerce_style' ) );
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
	 * Adds the Woocommerce stylesheet to the TinyMCE.
	 *
	 * Woocommerce doesn't register the stylesheet, so I can't grab it that way, but Pippin mentioned I can just call the function
	 * to enqueue the style, grab the stylesheet, and then dequeue it pretty easily.
	 *
	 * @since 0.1.0
	 *
	 * @param array $styles All stylesheets registered for the TinyMCE through Render.
	 * @return array The styles.
	 */
	public static function add_woocommerce_style( $styles ) {

		global $wp_styles;

		//woocommerce_register_styles();

		if ( isset( $wp_styles->registered['woocommerce-styles'] ) ) {
			$styles[] = $wp_styles->registered['woocommerce-styles']->src;
		}

		wp_dequeue_style( 'woocommerce-styles' );

		return $styles;
	}

	// TODO add Woocommerce styles to TinyMCE
	/**
	 * Adds the Render Woocommerce stylesheet to the TinyMCE through Render.
	 *
	 * @since 0.1.0
	 *
	 * @param array $styles All stylesheets registered for the TinyMCE through Render.
	 * @return array The styles.
	 */
	public static function add_render_woocommerce_style( $styles ) {

		//$styles[] = RENDER_WOOCOMMERCE_URL . "/assets/css/render-woocommerce.css";
		return $styles;
	}

	/**
	 * Add data and inputs for all Woocommerce shortcodes and pass them through Render's function.
	 *
	 * @since 0.1.0
	 */
	private function add_shortcodes() {

		global $woocommerce_options;

		foreach (
			array(
				// 1. Add to cart
				array(
					'code'        => 'add_to_cart',
					'function'    => 'woocommerce_download_shortcode',
					'title'       => __( 'Add to Cart', 'Render_Woocommerce' ),
					'description' => __( 'Displays a button which adds a specific product to the cart.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce purchase product buy button pay link checkout',
					'atts'        => array(
						'id'       => array(
							// TODO make this a dynamic dropdown
							'label'      => __( 'Product', 'Render_Woocommerce' ),
							'required' => true,
						),
						'sku'    => array(
							'label'      => __( 'SKU', 'Render_Woocommerce' ),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Style', 'Render_Woocommerce' ),
						),
						'style'    => array(
							'label'      => __( 'Custom CSS', 'Render_Woocommerce' ),
						),
					),
					'render'      => true,
				),
				// 2. Add to cart URL
				array(
					'code'        => 'add_to_cart_url',
					'function'    => 'woocommerce_download_shortcode',
					'title'       => __( 'Add to Cart URL', 'Render_Woocommerce' ),
					'description' => __( 'Displays the URL on the add to cart button of a specific product.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce purchase product buy button pay link checkout URI',
					'atts'        => array(
						'id'       => array(
							// TODO make this a dynamic dropdown
							'label'      => __( 'Product', 'Render_Woocommerce' ),
							'required' => true,
						),
						'sku'    => array(
							'label'      => __( 'SKU', 'Render_Woocommerce' ),
						),
					),
					'render'      => true,
				),
				// 3. Best selling products
				array(
					'code'        => 'best_selling_products',
					'function'    => 'woocommerce_download_shortcode',
					'title'       => __( 'Best Selling Products', 'Render_Woocommerce' ),
					'description' => __( 'Displays a list of all the best selling products on this site.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce purchase checkout sale grid',
					'atts'        => array(
						'per_page'           => array(
							'label'      => __( 'Per Page', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => 10,
							'properties' => array(
								'min' => 1,
								'max' => 50,
							),
						),
						'columns'           => array(
							'label'      => __( 'Columns', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => 2,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
					),
					'render'      => true,
				),
				// 4. Featured products
				array(
					'code'        => 'featured_products',
					'function'    => 'woocommerce_download_shortcode',
					'title'       => __( 'Featured Products', 'Render_Woocommerce' ),
					'description' => __( 'Displays a list of all the featured products on this site.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce purchase checkout sale grid',
					'atts'        => array(
						'per_page'           => array(
							'label'      => __( 'Per Page', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => 12,
							'properties' => array(
								'min' => 1,
								'max' => 50,
							),
						),
						'columns'           => array(
							'label'      => __( 'Columns', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
						'orderby'          => array(
							'label'      => __( 'Order By', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'default'    => 'date',
							'properties' => array(
								'options' => array(
									// TODO find all accepted inputs
//									'price'     => __( 'Price', 'Render_Woocommerce' ),
//									'id'        => __( 'ID', 'Render_Woocommerce' ),
//									'random'    => __( 'Random', 'Render_Woocommerce' ),
									'date' => __( 'Published date', 'Render_Woocommerce' ),
									'title'     => __( 'Title', 'Render_Woocommerce' ),
								),
							),
						),
						'order'            => array(
							'label'      => __( 'Order', 'Render_Woocommerce' ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'desc' => __( 'Descending', 'Render_Woocommerce' ),
									'asc'  => __( 'Ascending', 'Render_Woocommerce' ),
								),
							),
						),
					),
					'render'      => true,
				),
				// 5. Product
				array(
					'code'        => 'product',
					'function'    => 'woocommerce_download_shortcode',
					'title'       => __( 'Product', 'Render_Woocommerce' ),
					'description' => __( 'Displays a specific product.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce purchase buy pay sale',
					'atts'        => array(
						'id'       => array(
							// TODO make this a dynamic dropdown
							'label'      => __( 'Product', 'Render_Woocommerce' ),
							'required' => true,
						),
						'sku'    => array(
							'label'      => __( 'SKU', 'Render_Woocommerce' ),
						),
					),
					'render'      => true,
				),
				// 6. Product attribute
				array(
					'code'        => 'product_attribute',
					'function'    => 'woocommerce_download_shortcode',
					'title'       => __( 'Product Attribute', 'Render_Woocommerce' ),
					'description' => __( 'Displays a list of products based on an attribute value.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce purchase sale grid',
					'atts'        => array(
						'per_page'           => array(
							'label'      => __( 'Per Page', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => 12,
							'properties' => array(
								'min' => 1,
								'max' => 50,
							),
						),
						'columns'           => array(
							'label'      => __( 'Columns', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
						'orderby'          => array(
							'label'      => __( 'Order By', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'default'    => 'date',
							'properties' => array(
								'options' => array(
									// TODO find all accepted inputs
//									'price'     => __( 'Price', 'Render_Woocommerce' ),
//									'id'        => __( 'ID', 'Render_Woocommerce' ),
//									'random'    => __( 'Random', 'Render_Woocommerce' ),
									'date' => __( 'Published date', 'Render_Woocommerce' ),
									'title'     => __( 'Title', 'Render_Woocommerce' ),
								),
							),
						),
						'order'            => array(
							'label'      => __( 'Order', 'Render_Woocommerce' ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'desc' => __( 'Descending', 'Render_Woocommerce' ),
									'asc'  => __( 'Ascending', 'Render_Woocommerce' ),
								),
							),
						),
						'attribute'       => array(
							// TODO make this a dynamic dropdown
							'label'      => __( 'Attribute', 'Render_Woocommerce' ),
						),
						'filter'    => array(
							'label'      => __( 'Filter', 'Render_Woocommerce' ),
						),
					),
					'render'      => true,
				),
				// 7. Product categories
				array(
					'code'        => 'product_categories',
					'function'    => 'woocommerce_download_shortcode',
					'title'       => __( 'Product Categories', 'Render_Woocommerce' ),
					'description' => __( 'Displays a list of product categories.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce purchase sale grid taxonomy list',
					'atts'        => array(
						'columns'           => array(
							'label'      => __( 'Columns', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
						'orderby'          => array(
							'label'      => __( 'Order By', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'default'    => 'date',
							'properties' => array(
								'options' => array(
									// TODO find all accepted inputs
//									'price'     => __( 'Price', 'Render_Woocommerce' ),
//									'id'        => __( 'ID', 'Render_Woocommerce' ),
//									'random'    => __( 'Random', 'Render_Woocommerce' ),
									'date' => __( 'Published date', 'Render_Woocommerce' ),
									'title'     => __( 'Title', 'Render_Woocommerce' ),
									'name'     => __( 'Name', 'Render_Woocommerce' ),
								),
							),
						),
						'order'            => array(
							'label'      => __( 'Order', 'Render_Woocommerce' ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'DESC' => __( 'Descending', 'Render_Woocommerce' ),
									'ASC'  => __( 'Ascending', 'Render_Woocommerce' ),
								),
							),
						),
						'number'           => array(
							'label'      => __( 'Number of products', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => null,
							'properties' => array(
								'min' => 1,
								'max' => 25,
							),
						),
						'hide_empty'            => array(
							'label'      => __( 'Hide empty', 'Render_Woocommerce' ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'1' => __( 'Yes', 'Render_Woocommerce' ),
									'0'  => __( 'No', 'Render_Woocommerce' ),
								),
							),
						),
						'parent'       => array(
							'label'      => __( 'Only top level categories', 'Render_Woocommerce' ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'' => __( 'No', 'Render_Woocommerce' ),
									'1'  => __( 'Yes', 'Render_Woocommerce' ),
								),
							),
						),
						'ids'       => array(
							// TODO make this a dynamic multiselect dropdown
							'label'      => __( 'Categories', 'Render_Woocommerce' ),
						),
					),
					'render'      => true,
				),
				// 8. Product category
				array(
					'code'        => 'product_category',
					'function'    => 'woocommerce_download_shortcode',
					'title'       => __( 'Product Category', 'Render_Woocommerce' ),
					'description' => __( 'Displays a list of products in a category.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce purchase sale grid taxonomy list',
					'atts'        => array(
						'columns'           => array(
							'label'      => __( 'Columns', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
						'orderby'          => array(
							'label'      => __( 'Order By', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'default'    => 'date',
							'properties' => array(
								'options' => array(
									// TODO find all accepted inputs
//									'price'     => __( 'Price', 'Render_Woocommerce' ),
//									'id'        => __( 'ID', 'Render_Woocommerce' ),
//									'random'    => __( 'Random', 'Render_Woocommerce' ),
									'date' => __( 'Published date', 'Render_Woocommerce' ),
									'title'     => __( 'Title', 'Render_Woocommerce' ),
									'name'     => __( 'Name', 'Render_Woocommerce' ),
								),
							),
						),
						'order'            => array(
							'label'      => __( 'Order', 'Render_Woocommerce' ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'DESC' => __( 'Descending', 'Render_Woocommerce' ),
									'ASC'  => __( 'Ascending', 'Render_Woocommerce' ),
								),
							),
						),
						'per_page'           => array(
							'label'      => __( 'Number of products', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => null,
							'properties' => array(
								'min' => 1,
								'max' => 30,
							),
						),
						'category'       => array(
							// TODO make this a dynamic dropdown
							'label'      => __( 'Category', 'Render_Woocommerce' ),
							'required' => true,
						),
					),
					'render'      => true,
				),
				// 9. Product page
				// 10. Products
				// 11. Recent products
				// 12. Related products
				// 13. Sale products
				// 14. Shop messages
				// 15. Top rated products
				// 16. Woocommerce cart
				// 17. Woocommerce checkout
				// 18. Woocommerce messages
				// 19. Woocommerce my account
				// 20. Woocommerce order tracking

				// Download Cart
				array(
					'code'        => 'download_cart',
					'function'    => 'woocommerce_cart_shortcode',
					'title'       => __( 'Download Cart', 'Render_Woocommerce' ),
					'description' => __( 'Lists items in cart.', 'Render_Woocommerce' ),
					'tags'        => 'cart woocommerce ecommerce downloads digital products',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Download Checkout
				array(
					'code'        => 'download_checkout',
					'function'    => 'woocommerce_checkout_form_shortcode',
					'title'       => __( 'Download Checkout', 'Render_Woocommerce' ),
					'description' => __( 'Displays the checkout form.', 'Render_Woocommerce' ),
					'tags'        => 'cart woocommerce ecommerce downloads digital products form',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Download History
				array(
					'code'        => 'download_history',
					'function'    => 'woocommerce_download_history',
					'title'       => __( 'Download History', 'Render_Woocommerce' ),
					'description' => __( 'Displays all the products a user has purchased with links to the files.', 'Render_Woocommerce' ),
					'tags'        => 'woocommerce ecommerce downloads digital products history files purchase',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Purchase History
				array(
					'code'        => 'purchase_history',
					'function'    => 'woocommerce_purchase_history',
					'title'       => __( 'Purchase History', 'Render_Woocommerce' ),
					'description' => __( 'Displays the complete purchase history for a user.', 'Render_Woocommerce' ),
					'tags'        => 'woocommerce ecommerce downloads digital products history purchase',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Download Discounts
				array(
					'code'        => 'download_discounts',
					'function'    => 'woocommerce_discounts_shortcode',
					'title'       => __( 'Download Discounts', 'Render_Woocommerce' ),
					'description' => __( 'Lists all the currently available discount codes on your site.', 'Render_Woocommerce' ),
					'tags'        => 'woocommerce ecommerce downloads digital products coupon discount code',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Profile Editor
				array(
					'code'        => 'woocommerce_profile_editor',
					'function'    => 'woocommerce_profile_editor_shortcode',
					'title'       => __( 'Woocommerce Profile Editor', 'Render_Woocommerce' ),
					'description' => __( 'Presents users with a form for updating their profile.', 'Render_Woocommerce' ),
					'tags'        => 'woocommerce ecommerce downloads digital user profile account',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Login
				array(
					'code'        => 'woocommerce_login',
					'function'    => 'woocommerce_login_form_shortcode',
					'title'       => __( 'Woocommerce Login', 'Render_Woocommerce' ),
					'description' => __( 'Displays a simple login form for non-logged in users.', 'Render_Woocommerce' ),
					'tags'        => 'woocommerce ecommerce downloads login users form',
					'atts'        => array(
						'redirect' => array(
							'label'       => __( 'Redirect', 'Render_Woocommerce' ),
							'description' => __( 'Redirect to this page after login.', 'Render_Woocommerce' ),
							'type'        => 'selectbox',
							'properties'  => array(
								'allowCustomInput' => true,
								'groups'           => array(),
								'callback'         => array(
									'groups'   => true,
									'function' => 'render_sc_post_list',
								),
								'placeholder'      => __( 'Same page', 'Render_Woocommerce' ),
							),
						),
					),
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Register
				array(
					'code'        => 'woocommerce_register',
					'function'    => 'woocommerce_register_form_shortcode',
					'title'       => __( 'Woocommerce Register', 'Render_Woocommerce' ),
					'description' => __( 'Displays a registration form for non-logged in users.', 'Render_Woocommerce' ),
					'tags'        => 'woocommerce ecommerce downloads login users form register signup',
					'atts'        => array(
						'redirect' => array(
							'label'       => __( 'Redirect', 'Render_Woocommerce' ),
							'description' => __( 'Redirect to this page after login.', 'Render_Woocommerce' ),
							'type'        => 'selectbox',
							'properties'  => array(
								'allowCustomInput' => true,
								'groups'           => array(),
								'callback'         => array(
									'groups'   => true,
									'function' => 'render_sc_post_list',
								),
								'placeholder'      => __( 'Same page', 'Render_Woocommerce' ),
							),
						),
					),
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// Price
				array(
					'code'        => 'woocommerce_price',
					'function'    => 'woocommerce_download_price_shortcode',
					'title'       => __( 'Download Price', 'Render_Woocommerce' ),
					'description' => __( 'Displays the price of a specific download.', 'Render_Woocommerce' ),
					'tags'        => 'woocommerce ecommerce downloads product price',
					'atts'        => array(
						'id'       => render_woocommerce_sc_attr_template( 'downloads', array(
							'required' => true,
						) ),
						'price_id' => array(
							'label'       => __( 'Price ID', 'Render_Woocommerce' ),
							'description' => __( 'Optional. For variable pricing.', 'Render_Woocommerce' ),
						),
					),
					'render'      => true,
				),
				// Receipt
				array(
					'code'        => 'woocommerce_receipt',
					'function'    => 'woocommerce_receipt_shortcode',
					'title'       => __( 'Download Receipt', 'Render_Woocommerce' ),
					'description' => __( 'Displays a the complete details of a completed purchase.', 'Render_Woocommerce' ),
					'tags'        => 'woocommerce ecommerce downloads purchase receipt confirmation order payment complete checkout',
					'atts'        => array(
						'error'       => array(
							'label'      => __( 'Error Message', 'Render_Woocommerce' ),
							'properties' => array(
								'placeholder' => __( 'Sorry, trouble retrieving payment receipt.', 'woocommerce' ),
							),
						),
						'price'       => array(
							'label'      => __( 'Hide Price', 'Render_Woocommerce' ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'discount'    => array(
							'label'      => __( 'Hide Discounts', 'Render_Woocommerce' ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'products'    => array(
							'label'      => __( 'Hide Products', 'Render_Woocommerce' ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'date'        => array(
							'label'      => __( 'Hide Purchase Date', 'Render_Woocommerce' ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'payment_key' => array(
							'label'      => __( 'Hide Payment Key', 'Render_Woocommerce' ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'payment_id'  => array(
							'label'      => __( 'Hide Order Number', 'Render_Woocommerce' ),
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
					'function'    => 'woocommerce_download_shortcode',
					'title'       => __( 'Download Purchase Link', 'Render_Woocommerce' ),
					'description' => __( 'Displays a button which adds a specific product to the cart.', 'Render_Woocommerce' ),
					'tags'        => 'woocommerce ecommerce downloads purchase product buy button pay link checkout',
					'atts'        => array(
						'id'       => render_woocommerce_sc_attr_template( 'downloads', array(
							'required' => true,
						) ),
						'price'    => array(
							'label'      => __( 'Hide Price', 'Render_Woocommerce' ),
							'type'       => 'checkbox',
							'properties' => array(
								'value' => 0,
							),
						),
						'text'     => array(
							'label'      => __( 'Link Text', 'Render_Woocommerce' ),
							'properties' => array(
								'placeholder' => isset( $woocommerce_options['add_to_cart_text'] ) && $woocommerce_options['add_to_cart_text'] != '' ? $woocommerce_options['add_to_cart_text'] : __( 'Purchase', 'woocommerce' ),
							),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Style', 'Render_Woocommerce' ),
						),
						'style'    => array(
							'label'      => __( 'Style', 'Render_Woocommerce' ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'   => isset( $woocommerce_options['button_style'] ) && $woocommerce_options['button_style'] == 'plain',
								'values' => array(
									'button' => __( 'Button', 'Render_Woocommerce' ),
									'plain'   => __( 'Text', 'Render_Woocommerce' ),
								),
							),
						),
						'color'    => array(
							'label'      => __( 'Button Color', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'default'    => isset( $woocommerce_options['checkout_color'] ) ? $woocommerce_options['checkout_color'] : 'blue',
							'properties' => array(
								'options' => array(
									'white'     => __( 'White', 'Render_Woocommerce' ),
									'gray'      => __( 'Gray', 'Render_Woocommerce' ),
									'blue'      => __( 'Blue', 'Render_Woocommerce' ),
									'red'       => __( 'Red', 'Render_Woocommerce' ),
									'green'     => __( 'Green', 'Render_Woocommerce' ),
									'yellow'    => __( 'Yellow', 'Render_Woocommerce' ),
									'orange'    => __( 'Orange', 'Render_Woocommerce' ),
									'dark gray' => __( 'Dark gray', 'Render_Woocommerce' ),
									'inherit'   => __( 'Inherit', 'Render_Woocommerce' ),
								),
							),
						),
						'sku'      => array(
							'label'    => __( 'SKU', 'Render_Woocommerce' ),
							'description'    => __( 'Get download by SKU (overrides download set above)', 'Render_Woocommerce' ),
							'advanced' => true,
						),
						'direct'   => array(
							'label'      => __( 'Direct Purchase', 'Render_Woocommerce' ),
							'type'       => 'checkbox',
							'properties' => array(
								'label' => __( 'Send customer to directly to PayPal', 'Render_Woocommerce' ),
							),
							'advanced'   => true,
						),
						'class'    => array(
							'label'    => __( 'CSS Class', 'Render_Woocommerce' ),
							'default'  => 'woocommerce-submit',
							'advanced' => true,
						),
						'form_id'  => array(
							'label'    => __( 'Form ID', 'Render_Woocommerce' ),
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
					'function'    => 'woocommerce_purchase_collection_shortcode',
					'title'       => __( 'Download Purchase Collection', 'Render_Woocommerce' ),
					'description' => __( 'Displays a button which adds all products in a specific taxonomy term to the cart.', 'Render_Woocommerce' ),
					'tags'        => 'woocommerce ecommerce downloads purchase product buy button pay link checkout',
					'atts'        => array(
						'taxonomy' => array(
							'label'      => __( 'Taxonomy', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'required' => true,
							'properties' => array(
								'options' => array(
									'download_category' => __( 'Category', 'Render_Woocommerce' ),
									'download_tag'      => __( 'Tag', 'Render_Woocommerce' ),
								),
							),
						),
						'terms'    => array(
							'label'       => __( 'Terms', 'Render_Woocommerce' ),
							'required' => true,
							'description' => __( 'Enter a comma separated list of terms for the selected taxonomy.', 'Render_Woocommerce' ),
						),
						'text'     => array(
							'label'   => __( 'Link Text', 'Render_Woocommerce' ),
							'default' => __( 'Purchase All Items', 'woocommerce' ),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Style', 'Render_Woocommerce' ),
						),
						'style'    => array(
							'label'      => __( 'Style', 'Render_Woocommerce' ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'   => isset( $woocommerce_options['button_style'] ) && $woocommerce_options['button_style'] == 'plain',
								'values' => array(
									'button' => __( 'Button', 'Render_Woocommerce' ),
									'plain'   => __( 'Text', 'Render_Woocommerce' ),
								),
							),
						),
						'color'    => array(
							'label'      => __( 'Button Color', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'default'    => isset( $woocommerce_options['checkout_color'] ) ? $woocommerce_options['checkout_color'] : 'blue',
							'properties' => array(
								'options' => array(
									'gray'      => __( 'Gray', 'Render_Woocommerce' ),
									'blue'      => __( 'Blue', 'Render_Woocommerce' ),
									'green'     => __( 'Green', 'Render_Woocommerce' ),
									'dark gray' => __( 'Dark gray', 'Render_Woocommerce' ),
									'yellow'    => __( 'Yellow', 'Render_Woocommerce' ),
								),
							),
						),
						'class'    => array(
							'label'    => __( 'CSS Class', 'Render_Woocommerce' ),
							'default'  => 'woocommerce-submit',
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
					'function'    => 'woocommerce_downloads_query',
					'title'       => __( 'Downloads', 'Render_Woocommerce' ),
					'description' => __( 'Outputs a list or grid of downloadable products.', 'Render_Woocommerce' ),
					'tags'        => 'woocommerce ecommerce downloads purchase product list',
					'atts'        => array(
						array(
							'type'  => 'section_break',
							'label' => __( 'Downloads', 'Render_Woocommerce' ),
						),
						'category'         => array(
							'label'      => __( 'Categories', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'properties' => array(
								'placeholder' => __( 'Download category', 'Render_Woocommerce' ),
								'multi'       => true,
								'callback'    => array(
									'function' => 'render_woocommerce_get_categories',
								),
							),
						),
						'tags'             => array(
							'label'      => __( 'Tags', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'properties' => array(
								'placeholder' => __( 'Download tag', 'Render_Woocommerce' ),
								'multi'       => true,
								'callback'    => array(
									'function' => 'render_woocommerce_get_tags',
								),
							),
						),
						'relation'         => array(
							'label'       => __( 'Relation', 'Render_Woocommerce' ),
							'description' => __( 'Downloads must be in ALL categories / tags, or at least just one.', 'Render_Woocommerce' ),
							'type'        => 'toggle',
							'properties'  => array(
								'values' => array(
									'AND' => __( 'All', 'Render_Woocommerce' ) . '&nbsp;', // For spacing in the toggle switch
									'OR'  => __( 'One', 'Render_Woocommerce' ),
								),
							),
						),
						'exclude_category' => array(
							'label'      => __( 'Exclude Categories', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'properties' => array(
								'placeholder' => __( 'Download category', 'Render_Woocommerce' ),
								'multi'       => true,
								'callback'    => array(
									'function' => 'render_woocommerce_get_categories',
								),
							),
						),
						'exclude_tags'     => array(
							'label'      => __( 'Exclude Tags', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'properties' => array(
								'placeholder' => __( 'Download tag', 'Render_Woocommerce' ),
								'multi'       => true,
								'callback'    => array(
									'function' => 'render_woocommerce_get_tags',
								),
							),
						),
						'number'           => array(
							'label'      => __( 'Download Count', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => 9,
							'properties' => array(
								'min' => 1,
								'max' => 50,
							),
						),
						'ids'              => render_woocommerce_sc_attr_template(
							'downloads',
							array(
								'label'       => __( 'Downloads', 'Render_Woocommerce' ),
								'description' => __( 'Enter one or more downloads to use ONLY these downloads.', 'Render_Woocommerce' ),
							), array(
								'multi' => true,
							)
						),
						'orderby'          => array(
							'label'      => __( 'Order By', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'default'    => 'post_date',
							'properties' => array(
								'options' => array(
									'price'     => __( 'Price', 'Render_Woocommerce' ),
									'id'        => __( 'ID', 'Render_Woocommerce' ),
									'random'    => __( 'Random', 'Render_Woocommerce' ),
									'post_date' => __( 'Published date', 'Render_Woocommerce' ),
									'title'     => __( 'Title', 'Render_Woocommerce' ),
								),
							),
						),
						'order'            => array(
							'label'      => __( 'Order', 'Render_Woocommerce' ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'DESC' => __( 'Descending', 'Render_Woocommerce' ),
									'ASC'  => __( 'Ascending', 'Render_Woocommerce' ),
								),
							),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Visibility', 'Render_Woocommerce' ),
						),
						'price'            => array(
							'label'      => __( 'Price', 'Render_Woocommerce' ),
							'type'       => 'toggle',
							'properties' => array(
								'deselectStyle' => true,
								'values'        => array(
									'no'  => __( 'Hide', 'Render_Woocommerce' ),
									'yes' => __( 'Show', 'Render_Woocommerce' ),
								),
							),
						),
						'excerpt'          => array(
							'label'      => __( 'Excerpt', 'Render_Woocommerce' ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'          => true,
								'deselectStyle' => true,
								'values'        => array(
									'no'  => __( 'Hide', 'Render_Woocommerce' ),
									'yes' => __( 'Show', 'Render_Woocommerce' ),
								),
							),
						),
						'full_content'     => array(
							'label'      => __( 'Full Content', 'Render_Woocommerce' ),
							'type'       => 'toggle',
							'properties' => array(
								'deselectStyle' => true,
								'values'        => array(
									'no'  => __( 'Hide', 'Render_Woocommerce' ),
									'yes' => __( 'Show', 'Render_Woocommerce' ),
								),
							),
						),
						'buy_button'       => array(
							'label'      => __( 'Buy Button', 'Render_Woocommerce' ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'          => true,
								'deselectStyle' => true,
								'values'        => array(
									'no'  => __( 'Hide', 'Render_Woocommerce' ),
									'yes' => __( 'Show', 'Render_Woocommerce' ),
								),
							),
						),
						'thumbnails'       => array(
							'label'      => __( 'Thumbnails', 'Render_Woocommerce' ),
							'type'       => 'toggle',
							'properties' => array(
								'flip'          => true,
								'deselectStyle' => true,
								'values'        => array(
									'false' => __( 'Hide', 'Render_Woocommerce' ),
									'true'  => __( 'Show', 'Render_Woocommerce' ),
								),
							),
						),
						'columns'          => array(
							'label'      => __( 'Columns', 'Render_Woocommerce' ),
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
			$shortcode['source']   = 'Woocommerce';

			render_add_shortcode( $shortcode );
			render_add_shortcode_category( array(
				'id'    => 'ecommerce',
				'label' => __( 'Ecommerce', 'Render_Woocommerce' ),
				'icon'  => 'dashicons-cart',
			) );
		}
	}

	/**
	 * Display a notice in the admin if Woocommerce and Render are not both active.
	 *
	 * @since 0.1.0
	 */
	static function notice() {
		?>
		<div class="error">
			<p>
				<?php
				printf(
					__( 'You have activated a plugin that requires %s and %s. Please install and activate both to continue using Render Woocommerce.', 'Render_Woocommerce' ),
					'<a href="http://renderwp.com/?utm_source=Render%20Woocommerce&utm_medium=Notice&utm_campaign=Render%20Woocommerce%20Notice
">Render</a>',
					'<a href="http://woocommerce.com/?utm_source=Render%20Woocommerce&utm_medium=Notice&utm_campaign=Render%20Woocommerce%20Notice">Woocommerce</a>'
				);
				?>
			</p>
		</div>
	<?php
	}
}

$render_woocommerce = new Render_Woocommerce();

/**
 * TinyMCE callback for the Woocommerce Login Form shortcode.
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
function woocommerce_login_form_shortcode_tinymce( $atts = array(), $content = '' ) {

	// Log out for displaying this shortcode
	render_tinyme_log_out();

	$output = woocommerce_login_form_shortcode( $atts, $content );
	return $output;
}

/**
 * TinyMCE callback for the Woocommerce Register Form shortcode.
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
function woocommerce_register_form_shortcode_tinymce( $atts = array(), $content = '' ) {

	// Log out for displaying this shortcode
	render_tinyme_log_out();

	$output = woocommerce_register_form_shortcode( $atts, $content );
	return $output;
}
<?php
/*
Plugin Name: Render Woocommerce
Description: Integrates Woocommerce with Render for improved shortcode usability.
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
 * @since 0.1.0
 */
define( 'RENDER_WOOCOMMERCE_VERSION', '0.1.0' );

/**
 * The absolute server path to Render's root directory.
 *
 * @since 0.1.0
 */
define( 'RENDER_WOOCOMMERCE_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The URI to Render's root directory.
 *
 * @since 0.1.0
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

	// TODO add Woocommerce styles to TinyMCE
	/**
	 * Adds the Woocommerce stylesheet to the TinyMCE.
	 *
	 * @since 0.1.0
	 *
	 * @param array $styles All stylesheets registered for the TinyMCE through Render.
	 * @return array The styles.
	 */
	public static function add_woocommerce_style( $styles ) {

		$styles[] = WC_Frontend_Scripts::get_styles()['woocommerce-general']['src'];
		$styles[] = WC_Frontend_Scripts::get_styles()['woocommerce-layout']['src'];
		$styles[] = WC_Frontend_Scripts::get_styles()['woocommerce-smallscreen']['src'];
		return $styles;
	}

	/**
	 * Adds the Render Woocommerce stylesheet to the TinyMCE through Render.
	 *
	 * @since 0.1.0
	 *
	 * @param array $styles All stylesheets registered for the TinyMCE through Render.
	 * @return array The styles.
	 */
	public static function add_render_woocommerce_style( $styles ) {

		$styles[] = RENDER_WOOCOMMERCE_URL . "/assets/css/render-woocommerce.css";
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
					'function'    => 'WC_Shortcodes::product_add_to_cart',
					'title'       => __( 'Add to Cart', 'Render_Woocommerce' ),
					'description' => __( 'Displays a button which adds a specific product to the cart.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce purchase product buy button pay link checkout',
					'atts'        => array(
						'id'       => render_woocommerce_sc_attr_template( 'product', array(
							'required' => true,
						) ),
						'sku'    => array(
							'label'      => __( 'SKU', 'Render_Woocommerce' ),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Style', 'Render_Woocommerce' ),
						),
						'style'    => array(
							'label'      => __( 'Custom CSS', 'Render_Woocommerce' ),
							'advanced' => true,
						),
					),
					'render'      => true,
				),
				// 2. Add to cart URL
				array(
					'code'        => 'add_to_cart_url',
					'function'    => 'WC_Shortcodes::product_add_to_cart_url',
					'title'       => __( 'Add to Cart URL', 'Render_Woocommerce' ),
					'description' => __( 'Displays the URL on the add to cart button of a specific product.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce purchase product buy button pay link checkout URI',
					'atts'        => array(
						'id'       => render_woocommerce_sc_attr_template( 'product', array(
							'required' => true,
						) ),
						'sku'    => array(
							'label'      => __( 'SKU', 'Render_Woocommerce' ),
						),
					),
					'render'      => true,
				),
				// 3. Best selling products
				array(
					'code'        => 'best_selling_products',
					'function'    => 'WC_Shortcodes::best_selling_products',
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
					'function'    => 'WC_Shortcodes::featured_products',
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
									'price'     => __( 'Price', 'Render_Woocommerce' ),
									'id'        => __( 'ID', 'Render_Woocommerce' ),
									'rand'    => __( 'Random', 'Render_Woocommerce' ),
									'date' => __( 'Published date', 'Render_Woocommerce' ),
									'title'     => __( 'Title', 'Render_Woocommerce' ),
									'menu_order'     => __( 'Menu order', 'Render_Woocommerce' ),
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
					'function'    => 'WC_Shortcodes::product',
					'title'       => __( 'Product', 'Render_Woocommerce' ),
					'description' => __( 'Displays a specific product.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce purchase buy pay sale',
					'atts'        => array(
						'id'       => render_woocommerce_sc_attr_template( 'product', array(
							'required' => true,
						) ),
						'sku'    => array(
							'label'      => __( 'SKU', 'Render_Woocommerce' ),
						),
					),
					'render'      => true,
				),
				// 6. Product attribute
				array(
					'code'        => 'product_attribute',
					'function'    => 'WC_Shortcodes::product_attribute',
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
									'price'     => __( 'Price', 'Render_Woocommerce' ),
									'id'        => __( 'ID', 'Render_Woocommerce' ),
									'rand'    => __( 'Random', 'Render_Woocommerce' ),
									'date' => __( 'Published date', 'Render_Woocommerce' ),
									'title'     => __( 'Title', 'Render_Woocommerce' ),
									'menu_order'     => __( 'Menu order', 'Render_Woocommerce' ),
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
							'label'      => __( 'Attribute', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'properties' => array(
								'placeholder' => __( 'Attribute', 'Render_Woocommerce' ),
								'callback'    => array(
									'function' => 'render_woocommerce_get_attributes',
								),
							),
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
					'function'    => 'WC_Shortcodes::product_categories',
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
									'price'     => __( 'Price', 'Render_Woocommerce' ),
									'id'        => __( 'ID', 'Render_Woocommerce' ),
									'rand'    => __( 'Random', 'Render_Woocommerce' ),
									'date' => __( 'Published date', 'Render_Woocommerce' ),
									'title'     => __( 'Title', 'Render_Woocommerce' ),
									'menu_order'     => __( 'Menu order', 'Render_Woocommerce' ),
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
							'label'      => __( 'Categories', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'properties' => array(
								'placeholder' => __( 'Category', 'Render_Woocommerce' ),
								'multi'       => true,
								'callback'    => array(
									'function' => 'render_woocommerce_get_categories',
								),
							),
						),
					),
					'render'      => true,
				),
				// 8. Product category
				array(
					'code'        => 'product_category',
					'function'    => 'WC_Shortcodes::product_category',
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
									'id'        => __( 'ID', 'Render_Woocommerce' ),
									'rand'    => __( 'Random', 'Render_Woocommerce' ),
									'date' => __( 'Published date', 'Render_Woocommerce' ),
									'title'     => __( 'Title', 'Render_Woocommerce' ),
									'menu_order'     => __( 'Menu order', 'Render_Woocommerce' ),
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
							'label'      => __( 'Category', 'Render_Woocommerce' ),
							'type'       => 'selectbox',
							'properties' => array(
								'placeholder' => __( 'Category', 'Render_Woocommerce' ),
								'callback'    => array(
									'function' => 'render_woocommerce_get_categories',
								),
							),
						),
					),
					'render'      => true,
				),
				// 9. Product page
				array(
					'code'        => 'product_page',
					'function'    => 'WC_Shortcodes::product_page',
					'title'       => __( 'Product page', 'Render_Woocommerce' ),
					'description' => __( 'Show a full single product page by ID or SKU.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce purchase buy sale individual',
					'atts'        => array(
						'id'       => render_woocommerce_sc_attr_template( 'product', array(
							'required' => true, )
						),
						'sku'    => array(
							'label'      => __( 'SKU', 'Render_Woocommerce' ),
						),
					),
					'render'      => true,
				),
				// 10. Products
				array(
					'code'        => 'products',
					'function'    => 'WC_Shortcodes::products',
					'title'       => __( 'Products', 'Render_Woocommerce' ),
					'description' => __( 'Show multiple products by ID or SKU.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce purchase buy sale',
					'atts'        => array(
						'ids'       => render_woocommerce_sc_attr_template( 'product', array(
							'required' => true,
						), array(
							'multi'       => true,
							)
						),
						'skus'    => array(
							'label'      => __( 'SKU', 'Render_Woocommerce' ),
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
									'id'        => __( 'ID', 'Render_Woocommerce' ),
									'rand'    => __( 'Random', 'Render_Woocommerce' ),
									'date' => __( 'Published date', 'Render_Woocommerce' ),
									'title'     => __( 'Title', 'Render_Woocommerce' ),
									'menu_order'     => __( 'Menu order', 'Render_Woocommerce' ),
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
				// 11. Recent products
				array(
					'code'        => 'recent_products',
					'function'    => 'WC_Shortcodes::recent_products',
					'title'       => __( 'Recent products', 'Render_Woocommerce' ),
					'description' => __( 'Displays a list of recent products.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce grid list new',
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
									'id'        => __( 'ID', 'Render_Woocommerce' ),
									'rand'    => __( 'Random', 'Render_Woocommerce' ),
									'date' => __( 'Published date', 'Render_Woocommerce' ),
									'title'     => __( 'Title', 'Render_Woocommerce' ),
									'menu_order'     => __( 'Menu order', 'Render_Woocommerce' ),
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
						'per_page'           => array(
							'label'      => __( 'Number of products', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => null,
							'properties' => array(
								'min' => 1,
								'max' => 30,
							),
						),
					),
					'render'      => true,
				),
				// 12. Related products
				array(
					'code'        => 'related_products',
					'function'    => 'WC_Shortcodes::related_products',
					'title'       => __( 'Related products', 'Render_Woocommerce' ),
					'description' => __( 'Displays a list of related products.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce grid list',
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
									'id'        => __( 'ID', 'Render_Woocommerce' ),
									'rand'    => __( 'Random', 'Render_Woocommerce' ),
									'date' => __( 'Published date', 'Render_Woocommerce' ),
									'title'     => __( 'Title', 'Render_Woocommerce' ),
									'menu_order'     => __( 'Menu order', 'Render_Woocommerce' ),
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
					),
					'render'      => true,
				),
				// 13. Sale products
				array(
					'code'        => 'sale_products',
					'function'    => 'WC_Shortcodes::sale_products',
					'title'       => __( 'Sale products', 'Render_Woocommerce' ),
					'description' => __( 'Displays a list of products that are on sale.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce grid list',
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
									'id'        => __( 'ID', 'Render_Woocommerce' ),
									'rand'    => __( 'Random', 'Render_Woocommerce' ),
									'date' => __( 'Published date', 'Render_Woocommerce' ),
									'title'     => __( 'Title', 'Render_Woocommerce' ),
									'menu_order'     => __( 'Menu order', 'Render_Woocommerce' ),
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
						'per_page'           => array(
							'label'      => __( 'Number of products', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => null,
							'properties' => array(
								'min' => 1,
								'max' => 30,
							),
						),
					),
					'render'      => true,
				),
				// 14. Shop messages
				array(
					'code'        => 'shop_messages',
					'function'    => 'WC_Shortcodes::shop_messages',
					'title'       => __( 'Shop messages', 'Render_Woocommerce' ),
					'description' => __( 'Outputs storewide messages.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce',
					'render'      => true,
				),
				// 15. Top rated products
				array(
					'code'        => 'top_rated_products',
					'function'    => 'WC_Shortcodes::top_rated_products',
					'title'       => __( 'Top rated products', 'Render_Woocommerce' ),
					'description' => __( 'Displays the top rated products for this store.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce grid list',
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
									'id'        => __( 'ID', 'Render_Woocommerce' ),
									'rand'    => __( 'Random', 'Render_Woocommerce' ),
									'date' => __( 'Published date', 'Render_Woocommerce' ),
									'title'     => __( 'Title', 'Render_Woocommerce' ),
									'menu_order'     => __( 'Menu order', 'Render_Woocommerce' ),
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
						'per_page'           => array(
							'label'      => __( 'Number of products', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => null,
							'properties' => array(
								'min' => 1,
								'max' => 30,
							),
						),
					),
					'render'      => true,
				),
				// 16. Woocommerce cart
				array(
					'code'        => 'woocommerce_cart',
					'function'    => 'WC_Shortcodes::cart',
					'title'       => __( 'Cart', 'Render_Woocommerce' ),
					'description' => __( 'Displays the current user\'s cart.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce',
					'render'      => true,
				),
				// 17. Woocommerce checkout
				array(
					'code'        => 'woocommerce_checkout',
					'function'    => 'WC_Shortcodes::checkout',
					'title'       => __( 'Checkout', 'Render_Woocommerce' ),
					'description' => __( 'Displays the checkout process for the current user.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce',
					'render'      => true,
				),
				// 18. Woocommerce messages
				array(
					'code'        => 'woocommerce_messages',
					'function'    => 'WC_Shortcodes::shop_messages',
					'title'       => __( 'Messages', 'Render_Woocommerce' ),
					'description' => __( 'Outputs storewide messages.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce',
					'render'      => true,
				),
				// 19. Woocommerce my account
				array(
					'code'        => 'woocommerce_my_account',
					'function'    => 'WC_Shortcodes::my_account',
					'title'       => __( 'My account', 'Render_Woocommerce' ),
					'description' => __( 'Displays the current user\'s account information.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce',
					'atts'        => array(
						'order_count'           => array(
							'label'      => __( 'Order count', 'Render_Woocommerce' ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => -1,
								'max' => 50,
							),
						),
					),
					'render'      => true,
				),
				// 20. Woocommerce order tracking
				array(
					'code'        => 'woocommerce_order_tracking',
					'function'    => 'WC_Shortcodes::order_tracking',
					'title'       => __( 'Order tracking', 'Render_Woocommerce' ),
					'description' => __( 'Outputs the status of an order after they enter their details.', 'Render_Woocommerce' ),
					'tags'        => 'ecommerce',
					'render'      => true,
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

//add_action('wp_footer', function() {
//	global $wp_styles;
//	var_dump($wp_styles);
//	$style = WC_Frontend_Scripts::get_styles()['woocommerce-general']['src'];;
//	echo $style['woocommerce-general']['src'];
//});
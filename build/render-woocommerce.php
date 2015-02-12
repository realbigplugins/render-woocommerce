<?php
/*
 * Plugin Name: Render WooCommerce
 * Description: Integrates WooCommerce with Render for improved shortcode usability.
 * Version: 1.0.0
 * Author: Kyle Maurer & Joel Worsham
 * Author URI: http://realbigmarketing.com
 * Plugin URI: http://realbigplugins.com/plugins/render-woocommerce/
 * Text Domain: Render_WooCommerce
 * Domain Path: /languages/
 */

// Exit if loaded directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// Define all plugin constants.

/**
 * The version of Render.
 *
 * @since 1.0.0
 */
define( 'RENDER_WOOCOMMERCE_VERSION', '1.0.0' );

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
 * Class Render_WooCommerce
 *
 * Initializes and loads the plugin.
 *
 * @since   1.0.0
 *
 * @package Render_WooCommerce
 */
class Render_WooCommerce {

	/**
	 * The reason for deactivation.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $deactivate_reasons = array();

	/**
	 * The plugin text domain.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $text_domain = 'Render_WooCommerce';

	/**
	 * Constructs the plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, '_init' ) );
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	public function _init() {

		// Requires Render
		if ( ! defined( 'RENDER_ACTIVE' ) ) {
			$this->deactivate_reasons[] = __( 'Render is not active', self::$text_domain );
		}

		// Requires WooCommerce
		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->deactivate_reasons[] = __( 'WooCommerce is not active', self::$text_domain );
		}

		// 1.0.3 is when extension integration was introduced
		if ( defined( 'RENDER_VERSION' ) && version_compare( RENDER_VERSION, '1.0.3', '<' ) ) {
			$this->deactivate_reasons[] = sprintf(
				__( 'This plugin requires at least Render version %s. You have version %s installed.', self::$text_domain ),
				'1.0.3',
				RENDER_VERSION
			);
		}

		// Bail if issues
		if ( ! empty( $this->deactivate_reasons ) ) {
			add_action( 'admin_notices', array( $this, '_notice' ) );

			return;
		}

		// Files required to run
		$this->_require_files();

		// Add the shortcodes to Render
		$this->_add_shortcodes();

		// Translation ready
		load_plugin_textdomain( 'Render_WooCommerce', false, RENDER_WOOCOMMERCE_PATH . '/languages' );

		// Add WooCommerce styles to tinymce
		add_filter( 'render_editor_styles', array( __CLASS__, '_add_woocommerce_style' ) );

		// Post class bug in TinyMCE
		add_action( 'render_tinymce_ajax', array( $this, '_add_product_post_class' ) );

		// Add licensing
		render_setup_license( 'render_woocommerce', 'WooCommerce', RENDER_WOOCOMMERCE_VERSION, __FILE__ );
	}

	/**
	 * Requires necessary plugin files.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function _require_files() {

		// Global helper functions.
		require_once __DIR__ . '/core/helper-functions.php';
	}

	/**
	 * Adds the WooCommerce stylesheet to the TinyMCE.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param array $styles All stylesheets registered for the TinyMCE through Render.
	 * @return array The styles.
	 */
	public static function _add_woocommerce_style( $styles ) {

		$styles[] = WC_Frontend_Scripts::get_styles()['woocommerce-general']['src'];
		$styles[] = WC_Frontend_Scripts::get_styles()['woocommerce-layout']['src'];

		if ( wp_is_mobile() ) {
			$styles[] = WC_Frontend_Scripts::get_styles()['woocommerce-smallscreen']['src'];
		}

		return $styles;
	}

	/**
	 * Add 'product' class to lists of products in TinyMCE
	 *
	 * @since  1.0.0
	 * @access private
	 */
	public function _add_product_post_class() {

		add_filter( 'post_class', function ( $post_class ) {

			global $post;

			if ( $post->post_type == 'product' ) {
				$post_class[] = 'product';
			}

			return $post_class;
		} );
	}

	/**
	 * Add data and inputs for all WooCommerce shortcodes and pass them through Render's function.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function _add_shortcodes() {

		foreach (
			array(
				// 1. Add to cart
				array(
					'code'        => 'add_to_cart',
					'function'    => 'WC_Shortcodes::product_add_to_cart',
					'title'       => __( 'Add to Cart', self::$text_domain ),
					'description' => __( 'Displays a button which adds a specific product to the cart.', self::$text_domain ),
					'tags'        => 'ecommerce purchase buy button pay link checkout',
					'atts'        => array(
						'id'         => render_sc_attr_template( 'post_list', array(
							'label'      => __( 'Product', self::$text_domain ),
							'required'   => true,
							'properties' => array(
								'placeholder' => __( 'Select a product', self::$text_domain ),
							),
						), array(
							'post_type' => 'product',
						) ),
						'show_price' => array(
							'label' => __( 'Hide Price', self::$text_domain ),
							'type'  => 'checkbox',
						),
						'sku'        => array(
							'label'    => __( 'SKU', self::$text_domain ),
							'advanced' => true,
						),
						'class'      => array(
							'label'    => __( 'HTML Class', self::$text_domain ),
							'advanced' => true,
						),
						'style'      => array(
							'label'    => __( 'Custom CSS', self::$text_domain ),
							'advanced' => true,
						),
					),
					'render'      => array(
						'noStyle' => true,
					),
				),
				// 2. Add to cart URL
				array(
					'code'        => 'add_to_cart_url',
					'function'    => 'WC_Shortcodes::product_add_to_cart_url',
					'title'       => __( 'Add to Cart URL', self::$text_domain ),
					'description' => __( 'Displays the URL on the add to cart button of a specific product.', self::$text_domain ),
					'tags'        => 'ecommerce purchase buy button pay link checkout uri',
					'atts'        => array(
						'id'  => render_sc_attr_template( 'post_list', array(
							'label'      => __( 'Product', self::$text_domain ),
							'required'   => true,
							'properties' => array(
								'placeholder' => __( 'Select a product', self::$text_domain ),
							),
						), array(
							'post_type' => 'product',
						) ),
						'sku' => array(
							'label'    => __( 'SKU', self::$text_domain ),
							'advanced' => true,
						),
					),
					'render'      => true,
				),
				// 3. Best selling products
				array(
					'code'        => 'best_selling_products',
					'function'    => 'WC_Shortcodes::best_selling_products',
					'title'       => __( 'Best Selling Products', self::$text_domain ),
					'description' => __( 'Displays a list of all the best selling products on this site.', self::$text_domain ),
					'tags'        => 'ecommerce purchase checkout sale grid',
					'atts'        => array(
						'per_page' => array(
							'label'      => __( 'Per Page', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 10,
							'properties' => array(
								'min'        => 1,
								'max'        => 50,
								'shift_step' => 5,
							),
						),
						'columns'  => array(
							'label'      => __( 'Columns', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 2,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
					),
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// 4. Featured products
				array(
					'code'        => 'featured_products',
					'function'    => 'WC_Shortcodes::featured_products',
					'title'       => __( 'Featured Products', self::$text_domain ),
					'description' => __( 'Displays a list of all the featured products on this site.', self::$text_domain ),
					'tags'        => 'ecommerce purchase checkout sale grid',
					'atts'        => array(
						'per_page' => array(
							'label'      => __( 'Per Page', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 12,
							'properties' => array(
								'min'        => 1,
								'max'        => 50,
								'shift_step' => 5,
							),
						),
						'columns'  => array(
							'label'      => __( 'Columns', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Ordering', self::$text_domain ),
						),
						'orderby'  => array(
							'label'      => __( 'Order By', self::$text_domain ),
							'type'       => 'selectbox',
							'default'    => 'date',
							'properties' => array(
								'options' => array(
									'price'      => __( 'Price', self::$text_domain ),
									'id'         => __( 'ID', self::$text_domain ),
									'rand'       => __( 'Random', self::$text_domain ),
									'date'       => __( 'Published date', self::$text_domain ),
									'title'      => __( 'Title', self::$text_domain ),
									'menu_order' => __( 'Menu order', self::$text_domain ),
								),
							),
						),
						'order'    => array(
							'label'      => __( 'Order', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'desc' => __( 'Descending', self::$text_domain ),
									'asc'  => __( 'Ascending', self::$text_domain ),
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
					'title'       => __( 'Product', self::$text_domain ),
					'description' => __( 'Displays a specific product.', self::$text_domain ),
					'tags'        => 'ecommerce purchase buy pay sale',
					'atts'        => array(
						'id'  => render_sc_attr_template( 'post_list', array(
							'label'      => __( 'Product', self::$text_domain ),
							'required'   => true,
							'properties' => array(
								'placeholder' => __( 'Select a product', self::$text_domain ),
							),
						), array(
							'post_type' => 'product',
						) ),
						'sku' => array(
							'label'    => __( 'SKU', self::$text_domain ),
							'advanced' => true,
						),
					),
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// 6. Product attribute
				array(
					'code'        => 'product_attribute',
					'function'    => 'WC_Shortcodes::product_attribute',
					'title'       => __( 'Product Attribute', self::$text_domain ),
					'description' => __( 'Displays a list of products based on an attribute value.', self::$text_domain ),
					'tags'        => 'ecommerce purchase sale grid',
					'atts'        => array(
						'attribute' => array(
							'label'      => __( 'Attribute', self::$text_domain ),
							'type'       => 'selectbox',
							'properties' => array(
								'placeholder' => __( 'Attribute', self::$text_domain ),
								'callback'    => array(
									'function' => 'render_woocommerce_get_attributes',
								),
							),
						),
						'per_page'  => array(
							'label'      => __( 'Per Page', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 12,
							'properties' => array(
								'min' => 1,
								'max' => 50,
							),
						),
						'columns'   => array(
							'label'      => __( 'Columns', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Ordering', self::$text_domain ),
						),
						'orderby'   => array(
							'label'      => __( 'Order By', self::$text_domain ),
							'type'       => 'selectbox',
							'default'    => 'date',
							'properties' => array(
								'options' => array(
									'price'      => __( 'Price', self::$text_domain ),
									'id'         => __( 'ID', self::$text_domain ),
									'rand'       => __( 'Random', self::$text_domain ),
									'date'       => __( 'Published date', self::$text_domain ),
									'title'      => __( 'Title', self::$text_domain ),
									'menu_order' => __( 'Menu order', self::$text_domain ),
								),
							),
						),
						'order'     => array(
							'label'      => __( 'Order', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'desc' => __( 'Descending', self::$text_domain ),
									'asc'  => __( 'Ascending', self::$text_domain ),
								),
							),
						),
						'filter'    => array(
							'label'       => __( 'Filter', self::$text_domain ),
							'description' => __( 'Allows filtering of specific attribute ID\'s. Separate by commas.', self:: $text_domain ),
							'advanced'    => true,
						),
					),
					'render'      => true,
				),
				// 7. Product categories
				array(
					'code'        => 'product_categories',
					'function'    => 'WC_Shortcodes::product_categories',
					'title'       => __( 'Product Categories', self::$text_domain ),
					'description' => __( 'Displays a list of product categories.', self::$text_domain ),
					'tags'        => 'ecommerce purchase sale grid taxonomy list',
					'atts'        => array(
						'ids'        => render_sc_attr_template( 'terms_list', array(
							'label'       => __( 'Categories', self::$text_domain ),
							'description' => __( 'Leave blank to show all categories', self::$text_domain ),
							'properties'  => array(
								'placeholder' => __( 'Show all categories', self::$text_domain ),
								'multi'       => true,
							),
						), array(
							'taxonomies' => array( 'product_cat' ),
						) ),
						'columns'    => array(
							'label'      => __( 'Columns', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
						'number'     => array(
							'label'      => __( 'Number of categories', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 5,
							'properties' => array(
								'min' => 1,
								'max' => 25,
							),
						),
						'hide_empty' => array(
							'label'      => __( 'Hide empty', self::$text_domain ),
							'type'       => 'toggle',
							'advanced'   => true,
							'properties' => array(
								'values' => array(
									'1' => __( 'Yes', self::$text_domain ),
									'0' => __( 'No', self::$text_domain ),
								),
							),
						),
						'parent'     => array(
							'label'      => __( 'Only top level categories', self::$text_domain ),
							'type'       => 'toggle',
							'advanced'   => true,
							'properties' => array(
								'values' => array(
									''  => __( 'No', self::$text_domain ),
									'1' => __( 'Yes', self::$text_domain ),
								),
							),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Ordering', self::$text_domain ),
						),
						'orderby'    => array(
							'label'      => __( 'Order By', self::$text_domain ),
							'type'       => 'selectbox',
							'default'    => 'date',
							'properties' => array(
								'options' => array(
									'price'      => __( 'Price', self::$text_domain ),
									'id'         => __( 'ID', self::$text_domain ),
									'rand'       => __( 'Random', self::$text_domain ),
									'date'       => __( 'Published date', self::$text_domain ),
									'title'      => __( 'Title', self::$text_domain ),
									'menu_order' => __( 'Menu order', self::$text_domain ),
								),
							),
						),
						'order'      => array(
							'label'      => __( 'Order', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'DESC' => __( 'Descending', self::$text_domain ),
									'ASC'  => __( 'Ascending', self::$text_domain ),
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
					'title'       => __( 'Product Category', self::$text_domain ),
					'description' => __( 'Displays a list of products in a category.', self::$text_domain ),
					'tags'        => 'ecommerce purchase sale grid taxonomy list',
					'atts'        => array(
						'category' => array(
							'label'      => __( 'Categories', self::$text_domain ),
							'type'       => 'selectbox',
							'properties' => array(
								'placeholder' => __( 'Select one or more categories', self::$text_domain ),
								'multi'       => true,
								'callback'    => array(
									'function' => 'render_wc_sc_categories_slug',
								),
							),
						),
						'columns'  => array(
							'label'      => __( 'Columns', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
						'per_page' => array(
							'label'      => __( 'Products per page', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 5,
							'properties' => array(
								'min'        => 1,
								'max'        => 30,
								'shift_step' => 5,
							),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Ordering', self::$text_domain ),
						),
						'orderby'  => array(
							'label'      => __( 'Order By', self::$text_domain ),
							'type'       => 'selectbox',
							'default'    => 'date',
							'properties' => array(
								'options' => array(
									'id'         => __( 'ID', self::$text_domain ),
									'rand'       => __( 'Random', self::$text_domain ),
									'date'       => __( 'Published date', self::$text_domain ),
									'title'      => __( 'Title', self::$text_domain ),
									'menu_order' => __( 'Menu order', self::$text_domain ),
								),
							),
						),
						'order'    => array(
							'label'      => __( 'Order', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'DESC' => __( 'Descending', self::$text_domain ),
									'ASC'  => __( 'Ascending', self::$text_domain ),
								),
							),
						),
					),
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// 9. Product page
				array(
					'code'        => 'product_page',
					'function'    => 'WC_Shortcodes::product_page',
					'title'       => __( 'Product page', self::$text_domain ),
					'description' => __( 'Show a full single product page by ID or SKU.', self::$text_domain ),
					'tags'        => 'ecommerce purchase buy sale individual',
					'atts'        => array(
						'id'  => render_sc_attr_template( 'post_list', array(
							'label'      => __( 'Product', self::$text_domain ),
							'required'   => true,
							'properties' => array(
								'placeholder' => __( 'Select a product', self::$text_domain ),
							),
						), array(
							'post_type' => 'product',
						) ),
						'sku' => array(
							'label'    => __( 'SKU', self::$text_domain ),
							'advanced' => true,
						),
					),
					'render'      => true,
				),
				// 10. Products
				array(
					'code'        => 'products',
					'function'    => 'WC_Shortcodes::products',
					'title'       => __( 'Products', self::$text_domain ),
					'description' => __( 'Show multiple products by ID or SKU.', self::$text_domain ),
					'tags'        => 'ecommerce purchase buy sale',
					'atts'        => array(
						'ids'     => render_sc_attr_template( 'post_list', array(
							'label'      => __( 'Product', self::$text_domain ),
							'required'   => true,
							'properties' => array(
								'placeholder' => __( 'Select a product', self::$text_domain ),
								'multi'       => true,
							),
						), array(
							'post_type' => 'product',
						) ),
						'columns' => array(
							'label'      => __( 'Columns', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Ordering', self::$text_domain ),
						),
						'orderby' => array(
							'label'      => __( 'Order By', self::$text_domain ),
							'type'       => 'selectbox',
							'default'    => 'date',
							'properties' => array(
								'options' => array(
									'id'         => __( 'ID', self::$text_domain ),
									'rand'       => __( 'Random', self::$text_domain ),
									'date'       => __( 'Published date', self::$text_domain ),
									'title'      => __( 'Title', self::$text_domain ),
									'menu_order' => __( 'Menu order', self::$text_domain ),
								),
							),
						),
						'order'   => array(
							'label'      => __( 'Order', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'desc' => __( 'Descending', self::$text_domain ),
									'asc'  => __( 'Ascending', self::$text_domain ),
								),
							),
						),
						'skus'    => array(
							'label'    => __( 'SKU', self::$text_domain ),
							'advanced' => true,
						),
					),
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// 11. Recent products
				array(
					'code'        => 'recent_products',
					'function'    => 'WC_Shortcodes::recent_products',
					'title'       => __( 'Recent products', self::$text_domain ),
					'description' => __( 'Displays a list of recent products.', self::$text_domain ),
					'tags'        => 'ecommerce grid list new',
					'atts'        => array(
						'columns'  => array(
							'label'      => __( 'Columns', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
						'per_page' => array(
							'label'      => __( 'Number of products', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 5,
							'properties' => array(
								'min' => 1,
								'max' => 30,
							),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Ordering', self::$text_domain ),
						),
						'orderby'  => array(
							'label'      => __( 'Order By', self::$text_domain ),
							'type'       => 'selectbox',
							'default'    => 'date',
							'properties' => array(
								'options' => array(
									'id'         => __( 'ID', self::$text_domain ),
									'rand'       => __( 'Random', self::$text_domain ),
									'date'       => __( 'Published date', self::$text_domain ),
									'title'      => __( 'Title', self::$text_domain ),
									'menu_order' => __( 'Menu order', self::$text_domain ),
								),
							),
						),
						'order'    => array(
							'label'      => __( 'Order', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'desc' => __( 'Descending', self::$text_domain ),
									'asc'  => __( 'Ascending', self::$text_domain ),
								),
							),
						),
					),
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// 12. Related products
				array(
					'code'        => 'related_products',
					'function'    => 'WC_Shortcodes::related_products',
					'title'       => __( 'Related products', self::$text_domain ),
					'description' => __( 'Displays a list of related products.', self::$text_domain ),
					'tags'        => 'ecommerce grid list',
					'atts'        => array(
						'columns'  => array(
							'label'      => __( 'Columns', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
						'per_page' => array(
							'label'      => __( 'Number of products', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 5,
							'properties' => array(
								'min' => 1,
								'max' => 30,
							),
						),
						'orderby'  => array(
							'label'      => __( 'Order By', self::$text_domain ),
							'type'       => 'selectbox',
							'default'    => 'date',
							'properties' => array(
								'options' => array(
									'id'         => __( 'ID', self::$text_domain ),
									'rand'       => __( 'Random', self::$text_domain ),
									'date'       => __( 'Published date', self::$text_domain ),
									'title'      => __( 'Title', self::$text_domain ),
									'menu_order' => __( 'Menu order', self::$text_domain ),
								),
							),
						),
					),
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// 13. Sale products
				array(
					'code'        => 'sale_products',
					'function'    => 'WC_Shortcodes::sale_products',
					'title'       => __( 'Sale products', self::$text_domain ),
					'description' => __( 'Displays a list of products that are on sale.', self::$text_domain ),
					'tags'        => 'ecommerce grid list',
					'atts'        => array(
						'columns'  => array(
							'label'      => __( 'Columns', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
						'per_page' => array(
							'label'      => __( 'Number of products', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 5,
							'properties' => array(
								'min' => 1,
								'max' => 30,
							),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Ordering', self::$text_domain ),
						),
						'orderby'  => array(
							'label'      => __( 'Order By', self::$text_domain ),
							'type'       => 'selectbox',
							'default'    => 'date',
							'properties' => array(
								'options' => array(
									'id'         => __( 'ID', self::$text_domain ),
									'rand'       => __( 'Random', self::$text_domain ),
									'date'       => __( 'Published date', self::$text_domain ),
									'title'      => __( 'Title', self::$text_domain ),
									'menu_order' => __( 'Menu order', self::$text_domain ),
								),
							),
						),
						'order'    => array(
							'label'      => __( 'Order', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'desc' => __( 'Descending', self::$text_domain ),
									'asc'  => __( 'Ascending', self::$text_domain ),
								),
							),
						),
					),
					'render'      => true,
				),
				// 14. Shop messages
				array(
					'code'        => 'shop_messages',
					'function'    => 'WC_Shortcodes::shop_messages',
					'title'       => __( 'Shop messages', self::$text_domain ),
					'description' => __( 'Outputs storewide messages.', self::$text_domain ),
					'tags'        => 'ecommerce',
				),
				array(
					'code'      => 'woocommerce_messages',
					'noDisplay' => true,
				),
				// 15. Top rated products
				array(
					'code'        => 'top_rated_products',
					'function'    => 'WC_Shortcodes::top_rated_products',
					'title'       => __( 'Top rated products', self::$text_domain ),
					'description' => __( 'Displays the top rated products for this store.', self::$text_domain ),
					'tags'        => 'ecommerce grid list',
					'atts'        => array(
						'columns'  => array(
							'label'      => __( 'Columns', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => 1,
								'max' => 6,
							),
						),
						'per_page' => array(
							'label'      => __( 'Number of products', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 5,
							'properties' => array(
								'min' => 1,
								'max' => 30,
							),
						),
						array(
							'type'  => 'section_break',
							'label' => __( 'Ordering', self::$text_domain ),
						),
						'orderby'  => array(
							'label'      => __( 'Order By', self::$text_domain ),
							'type'       => 'selectbox',
							'default'    => 'date',
							'properties' => array(
								'options' => array(
									'id'         => __( 'ID', self::$text_domain ),
									'rand'       => __( 'Random', self::$text_domain ),
									'date'       => __( 'Published date', self::$text_domain ),
									'title'      => __( 'Title', self::$text_domain ),
									'menu_order' => __( 'Menu order', self::$text_domain ),
								),
							),
						),
						'order'    => array(
							'label'      => __( 'Order', self::$text_domain ),
							'type'       => 'toggle',
							'properties' => array(
								'values' => array(
									'desc' => __( 'Descending', self::$text_domain ),
									'asc'  => __( 'Ascending', self::$text_domain ),
								),
							),
						),
					),
					'render'      => array(
						'displayBlock',
					),
				),
				// 16. WooCommerce cart
				array(
					'code'        => 'woocommerce_cart',
					'function'    => 'WC_Shortcodes::cart',
					'title'       => __( 'Cart', self::$text_domain ),
					'description' => __( 'Displays the current user\'s cart.', self::$text_domain ),
					'tags'        => 'ecommerce',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// 17. WooCommerce checkout
				array(
					'code'        => 'woocommerce_checkout',
					'function'    => 'WC_Shortcodes::checkout',
					'title'       => __( 'Checkout', self::$text_domain ),
					'description' => __( 'Displays the checkout process for the current user.', self::$text_domain ),
					'tags'        => 'ecommerce',
					'render'      => array(
						'displayBlock' => true,
					),
				),
				// 18. WooCommerce my account
				array(
					'code'        => 'woocommerce_my_account',
					'function'    => 'WC_Shortcodes::my_account',
					'title'       => __( 'My account', self::$text_domain ),
					'description' => __( 'Displays the current user\'s account information.', self::$text_domain ),
					'tags'        => 'ecommerce',
					'atts'        => array(
						'order_count' => array(
							'label'      => __( 'Order count', self::$text_domain ),
							'description' => __( 'Number of orders to show in order history', self::$text_domain ),
							'type'       => 'counter',
							'default'    => 4,
							'properties' => array(
								'min' => - 1,
								'max' => 50,
							),
						),
					),
					'render'      => true,
				),
				// 19. WooCommerce order tracking
				array(
					'code'        => 'woocommerce_order_tracking',
					'function'    => 'WC_Shortcodes::order_tracking',
					'title'       => __( 'Order tracking', self::$text_domain ),
					'description' => __( 'Outputs the status of an order after they enter their details.', self::$text_domain ),
					'tags'        => 'ecommerce',
					'render'      => true,
				),
			) as $shortcode
		) {

			$shortcode['category'] = 'ecommerce';
			$shortcode['source']   = 'WooCommerce';

			render_add_shortcode( $shortcode );
			render_add_shortcode_category( array(
				'id'    => 'ecommerce',
				'label' => __( 'Ecommerce', self::$text_domain ),
				'icon'  => 'dashicons-cart',
			) );
		}
	}

	/**
	 * Display a notice in the admin if WooCommerce and Render are not both active.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	public function _notice() {
		?>
		<div class="error">
			<p>
				<?php _e( 'Render WooCommerce is not active due to the following errors:', self::$text_domain ); ?>
			</p>

			<ul>
				<?php foreach ( $this->deactivate_reasons as $reason ) : ?>
					<li>
						<?php echo "&bull; $reason"; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php
	}
}

new Render_WooCommerce();
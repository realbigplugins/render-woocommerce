<?php
/**
 * Added functionality for the plugin.
 *
 * @since 1.0.0
 */

// Exit if loaded directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Returns an array of all existing product attributes
 *
 * @since 1.0.0
 *
 * @return array The attributes.
 */
function render_woocommerce_get_attributes() {

	$attributes = wc_get_attribute_taxonomies();

	$output = array();

	if ( $attributes ) {
		foreach ( $attributes as $attribute ) {
			$output[$attribute->attribute_name] = $attribute->attribute_label;
		}
	}
	return $output;
}

/**
 * Gets WooCommerce category, but with the slug as the value, not the ID.
 *
 * @since 1.0.0
 *
 * @return array The terms.
 */
function render_wc_sc_categories_slug() {

	$terms = array();
	foreach ( (array) get_terms( 'product_cat' ) as $term ) {
		$terms[ $term->slug ] = $term->name;
	}

	return $terms;
}
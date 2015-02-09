<?php

// Exit if loaded directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Get download categories.
 *
 * @since 0.1.0
 *
 * @return array
 */
function render_woocommerce_get_categories() {

	$terms = get_terms( 'download_category', 'hide_empty=false' );

	$output = array();
	foreach ( $terms as $term ) {
		$output[ $term->term_id ] = $term->name;
	}

	return $output;
}

/**
 * Get download tags.
 *
 * @since 0.1.0
 *
 * @return array
 */
function render_woocommerce_get_tags() {

	$terms = get_terms( 'download_tag', 'hide_empty=false' );

	$output = array();
	foreach ( $terms as $term ) {
		$output[ $term->term_id ] = $term->name;
	}

	return $output;
}

/**
 * Get all products.
 *
 * @since 0.1.0
 *
 * @return array
 */
function render_woocommerce_get_products() {

	global $post;

	$args      = array(
		'post_type' => 'product'
	);
	$products = get_posts( $args );

	$output = array();

	if ( $post->post_type == 'product' ) {
		$output[ $post->ID ] = 'Current product';
	}

	foreach ( $products as $product ) {

		if ( (int) $product->ID === (int) $post->ID ) {
			continue;
		}

		$output[ $product->ID ] = $product->post_title;
	}

	return $output;
}

/**
 * Outputs an attribute template.
 *
 * @since 1.0.0
 *
 * @param string $template Which template to use.
 * @param array  $extra    Extra attribute parameters to use (or override).
 * @param array  $_properties Extra attribute properties to use (or override).
 * @return array Attribute.
 */
function render_woocommerce_sc_attr_template( $template, $extra = array(), $_properties = array() ) {

	global $post;

	if ( ! is_object( $post ) ) {
		$post_ID = isset( $_GET['post'] ) ? $_GET['post'] : '';
	} else {
		$post_ID = $post->ID;
	}

	$output = array();

	switch ( $template ) {
		case 'product':

			$properties = array(
				'placeholder' => __( 'Select a product', 'Render_woocommerce' ),
				'callback'    => array(
					'function' => 'render_woocommerce_get_products',
				),
			);

			$output = array(
				'label'      => __( 'Product', 'Render_woocommerce' ),
				'type'       => 'selectbox',
				'default'    => $post_ID,
				'properties' => array_merge( $properties, $_properties ),
			);
			break;
	}

	if ( ! empty( $extra ) ) {
		$output = array_merge( $output, $extra );
	}

	return $output;
}
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
function render_edd_get_categories() {

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
function render_edd_get_tags() {

	$terms = get_terms( 'download_tag', 'hide_empty=false' );

	$output = array();
	foreach ( $terms as $term ) {
		$output[ $term->term_id ] = $term->name;
	}

	return $output;
}

/**
 * Get all downloads.
 *
 * @since 0.1.0
 *
 * @return array
 */
function render_edd_get_downloads() {

	global $post;

	$args      = array(
		'post_type' => 'download'
	);
	$downloads = get_posts( $args );

	$output = array();

	if ( $post->post_type == 'download' ) {
		$output[ $post->ID ] = 'Current download';
	}

	foreach ( $downloads as $download ) {

		if ( (int) $download->ID === (int) $post->ID ) {
			continue;
		}

		$output[ $download->ID ] = $download->post_title;
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
function render_edd_sc_attr_template( $template, $extra = array(), $_properties = array() ) {

	global $post;

	if ( ! is_object( $post ) ) {
		$post_ID = isset( $_GET['post'] ) ? $_GET['post'] : '';
	} else {
		$post_ID = $post->ID;
	}

	$output = array();

	switch ( $template ) {
		case 'downloads':

			$properties = array(
				'placeholder' => __( 'Select a download', 'Render_EDD' ),
				'callback'    => array(
					'function' => 'render_edd_get_downloads',
				),
			);

			$output = array(
				'label'      => __( 'Download', 'Render_EDD' ),
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
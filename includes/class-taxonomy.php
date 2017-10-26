<?php
/**
 * Taxonomy class.
 *
 * @package WordCampAwards
 */

namespace WordCampAwards;

/**
 * Class Taxonomy
 */
class Taxonomy {

	/**
	 * Taxonomy slug.
	 *
	 * @const string
	 */
	const NAME = 'wordcamp_award_type';

	/**
	 * Initialize class.
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register the type taxonomy.
	 */
	public function register_taxonomy() {
		$tax_args = array(
			'label'        => __( 'Awards Types', 'wordcamp-awards' ),
			'hierarchical' => true,
			'rewrite'      => array(
				'slug' => '/awards/type',
			),
		);

		global $wp_taxonomies;

		$taxonomy = self::NAME;
		$object_type = Post_Type::NAME;

		if ( ! is_array( $wp_taxonomies ) )
			$wp_taxonomies = array();

		$args = wp_parse_args( $tax_args );

		if ( empty( $taxonomy ) || strlen( $taxonomy ) > 32 ) {
			_doing_it_wrong( __FUNCTION__, __( 'Taxonomy names must be between 1 and 32 characters in length.' ), '4.2.0' );
			return new WP_Error( 'taxonomy_length_invalid', __( 'Taxonomy names must be between 1 and 32 characters in length.' ) );
		}

		$taxonomy_object = new \WP_Taxonomy( $taxonomy, $object_type, $args );
		$taxonomy_object->add_rewrite_rules();

		$wp_taxonomies[ $taxonomy ] = $taxonomy_object;

		$taxonomy_object->add_hooks();
	}

}

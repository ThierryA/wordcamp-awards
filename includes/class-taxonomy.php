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

        register_taxonomy( self::NAME, Post_Type::NAME, $tax_args );

	}

}

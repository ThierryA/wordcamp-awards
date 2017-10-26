<?php
/**
 * Post_Type class.
 *
 * @package WordCampAwards
 */

namespace WordCampAwards;

/**
 * Class Post_Type
 *
 * Registers the post type, and sets its rewrites.
 */
class Post_Type {

	/**
	 * The reviews post type name.
	 *
	 * @const string
	 */
	const NAME = 'wordcamp_award';

	/**
	 * Initialize class.
	 */
	public function init() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'init', array( $this, 'set_rewrite' ) );
		add_filter( 'post_type_link', array( $this, 'set_post_link' ), 10, 2 );
	}

	/**
	 * Register the award post type.
	 */
	public function register() {
		$labels = array(
			'name'           => __( 'Awards', 'wordcamp-awards' ),
			'singular_name'  => __( 'Award', 'wordcamp-awards' ),
			'menu_name'      => __( 'Awards', 'wordcamp-awards' ),
			'name_admin_bar' => __( 'Award', 'wordcamp-awards' ),
			'add_new_item'   => __( 'Add New Award', 'wordcamp-awards' ),
			'add_new'        => __( 'Add New', 'wordcamp-awards' ),
			'new_item'       => __( 'New Award', 'wordcamp-awards' ),
			'edit_item'      => __( 'Edit Award', 'wordcamp-awards' ),
			'update_item'    => __( 'Update Award', 'wordcamp-awards' ),
			'view_item'      => __( 'View Award', 'wordcamp-awards' ),
			'search_items'   => __( 'Search Award', 'wordcamp-awards' ),
		);
		$args = array(
			'label'         => __( 'Award', 'wordcamp-awards' ),
			'labels'        => $labels,
			'public'        => true,
			'menu_position' => 20,
			'menu_icon'     => 'dashicons-awards',
			'has_archive'   => 'awards',
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
			'rewrite'       => array(
				'slug' => 'award',
			),
		);

		register_post_type( self::NAME, $args );

	}

	/**
	 * Set the link for the award post.
	 *
	 * @param string $url  The URL of the post.
	 * @param object $post The \WP_Post object.
	 * @return string $url The filtered URL of the post.
	 */
	public function set_post_link( $url, $post ) {
		$conditions = (
			! empty( $post->ID )
			&&
			! empty( $post->post_name )
			&&
			isset( $post->post_type )
			&&
			self::NAME === $post->post_type
		);

		if ( $conditions ) {
			return home_url( '/awards/' . $post->ID . '/' . $post->post_name );
		}

		return $url;
	}

	/**
	 * Add the rewrite rule and tag for the award post type.
	 *
	 * The single post URL is /award/1234/example-post-name, where '1234' is the post ID.
	 * This ensures that the string that follows award/ is the POST ID.
	 */
	public function set_rewrite() {
		global $wp_rewrite;

		$wp_rewrite->add_rule( '^awards/([0-9]+)/([a-zA-Z0-9_\-\s\,]+)/?', 'index.php?p=$matches[1]&' . self::NAME . '=$matches[2]', 'top' );

		$tag = '%' . self::NAME . '%';
		global $wp_rewrite, $wp;

		if ( empty( $query ) ) {
			$qv = trim( $tag, '%' );
			$wp->add_query_var( $qv );
			$query = $qv . '=';
		}

		$wp_rewrite->add_rewrite_tag( $tag, '([^&])+', $query );
	}

}

<?php
/**
 * Assets class.
 *
 * @package WordCampAwards;
 */

namespace WordCampAwards;

/**
 * Class Assets
 */
class Assets {

	/**
	 * Asset  handle.
	 *
	 * @const string
	 */
	const HANDLE = 'wordcamp-awards';

	/**
	 * Initialize class.
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_asssets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_asssets' ) );
	}

	/**
	 * Enqueue assets.
	 */
	public function enqueue_asssets() {
		if ( Post_Type::NAME !== get_post_type() || false === is_single() ) {
			return;
		}

		$plugin = Plugin::get_instance();

		wp_enqueue_script(
			self::HANDLE,
			"{$plugin->location->url}assets/js/posts.js",
			array( 'jquery' ),
			$plugin->version,
			true
		);

		// Boot JS.
		wp_add_inline_script(
			self::HANDLE,
			sprintf( 'WordCampAwardsPosts.boot( %s );',
				wp_json_encode( array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( Render::NONCE ),
				) )
			)
		);
	}

	/**
	 * Enqueue admin assets.
	 */
	public function enqueue_admin_asssets() {
		global $pagenow;

		if ( Post_Type::NAME !== get_post_type() || false === stripos( $pagenow, 'post' ) ) {
			return;
		}

		$plugin = Plugin::get_instance();

		wp_enqueue_script(
			self::HANDLE,
			"{$plugin->location->url}assets/js/admin.js",
			array( 'jquery' ),
			$plugin->version,
			true
		);

		// Boot JS.
		wp_add_inline_script(
			self::HANDLE,
			sprintf( 'WordCampAwardsAdmin.boot( %s );',
				wp_json_encode( array(
					'spinnerText' => __( 'Validating API accessible', 'wordcamp-awards' ),
					'successText' => __( 'REST API accessible', 'wordcamp-awards' ),
					'failText'    => __( 'REST API not accessible', 'wordcamp-awards' ),
				) )
			)
		);
	}

}

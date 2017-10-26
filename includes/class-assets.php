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
		$plugin = Plugin::get_instance();
		$handle = self::HANDLE;
		$src = "{$plugin->location->url}assets/js/posts.js";
		$deps = array( 'jquery' );
		$ver = $plugin->version;

		wp_enqueue_script( $handle, $src, array( 'jquery' ), $ver, true );

		wp_localize_script( $handle, 'WordCampAwardsPosts.boot', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( Render::NONCE ),
		 ) );
	}

	/**
	 * Enqueue admin assets.
	 */
	public function enqueue_admin_asssets() {
		$plugin = Plugin::get_instance();
		$handle = self::HANDLE;
		$src = "{$plugin->location->url}assets/js/admin.js";
		$deps = array( 'jquery' );
		$ver = $plugin->version;
		
		wp_enqueue_script( $handle, $src, array( 'jquery' ), $ver, true );

		wp_localize_script( $handle, 'WordCampAwardsPosts.boot', array(
			'spinnerText' => __( 'Validating API accessible', 'wordcamp-awards' ),
			'successText' => __( 'REST API accessible', 'wordcamp-awards' ),
			'failText'    => __( 'REST API not accessible', 'wordcamp-awards' ),
		) );

		// Boot JS.
		// wp_add_inline_script(
		// 	self::HANDLE,
		// 	sprintf( 'WordCampAwardsAdmin.boot( %s );',
		// 		wp_json_encode( array(
		// 			'spinnerText' => __( 'Validating API accessible', 'wordcamp-awards' ),
		// 			'successText' => __( 'REST API accessible', 'wordcamp-awards' ),
		// 			'failText'    => __( 'REST API not accessible', 'wordcamp-awards' ),
		// 		) )
		// 	)
		// );
	}

}

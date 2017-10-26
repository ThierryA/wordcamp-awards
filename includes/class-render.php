<?php
/**
 * Render class.
 *
 * @package WordCampAwards;
 */

namespace WordCampAwards;

/**
 * Class Render
 */
class Render {

	/**
	 * Nonce.
	 *
	 * @const string
	 */
	const NONCE = 'awards-posts';

	/**
	 * The cache time in seconds.
	 *
	 * @const int
	 */
	const CACHE_TIME = 60;

	/**
	 * Initialize class.
	 */
	public function init() {
		add_action( 'wp_ajax_wordcamp_awards_rest_posts', array( $this, 'ajax_render' ) );
		add_action( 'wp_ajax_nopriv_wordcamp_awards_rest_posts', array( $this, 'ajax_render' ) );
	}

	/**
	 * Ajax REST posts renderer.
	 *
	 * The 'phpQuery' arguments set in the HTML data-wordcamp-awards attribute are passed to
	 * this method via AJAX.
	 */
	public function ajax_render() {
		$validate = (
			isset( $_POST['nonce'], $_POST['query'], $_POST['query']['post_id'] ) // Input var okay.
			&&
			wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), self::NONCE ) // Input var okay.
		);

		if ( ! $validate ) {
			wp_send_json_error();
		}

		$output = '';
		$query = array_map( 'sanitize_text_field', wp_unslash( $_POST['query'] ) ); // Input var okay.
		$url = Plugin::get_instance()->components->metadata->get_post_meta( (int) $query['post_id'], 'site_details', 'url' );

		if ( empty( $url ) ) {
			wp_send_json_error();
		}

		$posts = $this->get_remote_posts( $url );

		// Don't render API call failed.
		if ( empty( $posts ) ) {
			wp_send_json_error();
		}

		if ( isset( $query['template_path'] ) ) {
			if ( ! file_exists( $query['template_path'] ) ) {
				wp_send_json_error();
			}

			ob_start();
			include( $query['template_path'] );
			$output = ob_get_clean();
		}

		$response = array( 'success' => true );

		if ( isset( $output ) )
			$response['data'] = $output;

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		if ( null !== $status_code ) {
			status_header( $status_code );
		}
		echo wp_json_encode( $response );

		if ( wp_doing_ajax() ) {
			wp_die( '', '', array(
				'response' => null,
			) );
		} else {
			die;
		}
	}

	/**
	 * Get remote posts.
	 *
	 * @param int $url The remote site url.
	 * @return array $data Remote posts on success, false otherwise.
	 */
	protected function get_remote_posts( $url ) {
		// Make API call.
		$args['reject_unsafe_urls'] = true;
        return wp_remote_post($url, $args);
	}

}

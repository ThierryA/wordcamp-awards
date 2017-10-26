<?php
/**
 * Metadata class.
 *
 * @package WordCampAwards
 */

namespace WordCampAwards;

/**
 * Class Metadata
 */
class Metadata {

	/**
	 * Group prefix.
	 *
	 * @var array
	 */
	public $prefix;

	/**
	 * Holds all the metadata.
	 *
	 * @var array
	 */
	public $config = array();

	/**
	 * The nonce action.
	 *
	 * @const string
	 */
	const NONCE_ACTION = 'save_awards_meta';

	/**
	 * The nonce.
	 *
	 * @const string
	 */
	const NONCE = 'awards_meta_nonce';

	/**
	 * Metadata constructor.
	 */
	public function __construct() {
		$this->config();
	}

	/**
	 * Initialize class.
	 */
	public function init() {
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
		add_action( 'edit_form_top', array( $this, 'render_nonce_field' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );
	}

	/**
	 * Set config.
	 */
	protected function config() {
		$this->prefix = Post_Type::NAME;
		$this->config = array(
			'site_details' => array(
				'label'    => __( 'Site URL', 'wordcamp-awards' ),
				'priority' => 'default',
				'context'  => 'side',
				'fields'   => array(
					'url' => array(
						'type'              => 'url',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			),
		);
	}

	/**
	 * Register metabox.
	 */
	public function register_metabox() {
		foreach ( $this->config as $section_id => $section ) {
            $section = $this->parse_section($section);

            global $wp_meta_boxes;

            $id = "{$this->prefix}_{$section_id}";
            $title = $section['label'];
            $callback = array($this, 'render_metabox');
            $screen = Post_Type::NAME;
            $context = $section['context'];
            $priority = $section['priority'];
            $callback_args = array(
                'section_id' => $section_id,
            );

            if (empty($screen)) {
                $screen = get_current_screen();
            } elseif (is_string($screen)) {
                $screen = convert_to_screen($screen);
            } elseif (is_array($screen)) {
                foreach ($screen as $single_screen) {
                    add_meta_box($id, $title, $callback, $single_screen, $context, $priority, $callback_args);
                }
            }

            if (!isset($screen->id)) {
                return;
            }

            add_meta_box($id, $title, $callback, $screen, $context, $priority, $callback_args);

        }

	}

	/**
	 * Render nonce.
	 */
	public function render_nonce_field() {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE );
	}

	/**
	 * Render metabox.
	 *
	 * @param WP_Post $post Current post object.
	 * @param array   $args Metabox arguments.
	 */
	public function render_metabox( $post, $args ) {
		if ( ! isset( $args['args']['section_id'], $this->config[ $args['args']['section_id'] ] ) ) {
			return;
		}

		$section_id = $args['args']['section_id'];
		$section = $this->parse_section( $this->config[ $section_id ] );
		$fields = $section['fields'];

		foreach ( $fields as $field_id => $field ) {
			$field = $this->parse_field( $field );
			$value = $this->get_post_meta( $post->ID, $section_id, $field_id );
			$name = "{$this->prefix}_{$section_id}_{$field_id}";

			switch ( $field['type'] ) {
				case 'url':
					?>
					<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
					<input class="widefat" type="<?php echo esc_attr( $field['type'] ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_url( $value ); ?>" />
					<?php
					break;
			}
		}
	}

	/**
	 * Get post meta.
	 *
	 * @param int    $post_id    The ID of the post/show.
	 * @param string $section_id The meta section id.
	 * @param string $field_id   The meta section field id.
	 * @return mixed Post meta value if found, false otherwise.
	 */
	public function get_post_meta( $post_id, $section_id, $field_id ) {
        $id = "{$this->prefix}_{$section_id}_{$field_id}";
		return get_post_meta($post_id, $id );

	}

	/**
	 * Render metabox.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta( $post_id ) {
		$conditions = array(
			isset( $_POST[ self::NONCE ] ) // WPCS: input var ok.
			&&
			wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE ] ) ), self::NONCE_ACTION ) // WPCS: input var ok.
			&&
			true === current_user_can( 'edit_post', $post_id )
		);

		if ( ! $conditions ) {
			return;
		}

		foreach ( $this->config as $section_id => $section ) {
			$section = $this->parse_section( $section );

			foreach ( $section['fields'] as $field_id => $field ) {
				$field = $this->parse_field( $field );
				$id = "{$this->prefix}_{$section_id}_{$field_id}";

				if ( isset( $_POST[ $id ] ) && is_callable( $field['sanitize_callback'] ) ) { // WPCS: input var ok.
					$value = call_user_func( $field['sanitize_callback'], wp_unslash( $_POST[ $id ] ) ); // WPCS: sanitization ok & input var ok.
                    update_post_meta( $post_id, $id, $value, '' );
				}
			}
		}
	}

	/**
	 * Parse section.
	 *
	 * @param array $section Section data.
	 * @return array Section data.
	 */
	protected function parse_section( array $section ) {
		$defaults = array(
			'label'    => null,
			'priority' => 'default',
			'context'  => 'advanced',
			'fields'   => array(),
		);

		return array_merge( $defaults, $section );
	}

	/**
	 * Parse field.
	 *
	 * @param array $field Field data.
	 * @return array Field data.
	 */
	protected function parse_field( array $field ) {
		$defaults = array(
			'label'             => null,
			'description'       => null,
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field',
		);

		return array_merge( $defaults, $field );
	}

}

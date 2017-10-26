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
			$section = $this->parse_section( $section );

			// API replacement start:
			global $wp_meta_boxes;

			$id = "{$this->prefix}_{$section_id}";
			$title = $section['label'];
			$callback = array( $this, 'render_metabox' );
			$screen = Post_Type::NAME;
			$context = $section['context'];
			$priority = $section['priority'];
			$callback_args = array(
				'section_id' => $section_id,
			);

			if ( empty( $screen ) ) {
				$screen = get_current_screen();
			} elseif ( is_string( $screen ) ) {
				$screen = convert_to_screen( $screen );
			} elseif ( is_array( $screen ) ) {
				foreach ( $screen as $single_screen ) {
					add_meta_box( $id, $title, $callback, $single_screen, $context, $priority, $callback_args );
				}
			}

			if ( ! isset( $screen->id ) ) {
				return;
			}

			$page = $screen->id;

			if ( !isset($wp_meta_boxes) )
				$wp_meta_boxes = array();
			if ( !isset($wp_meta_boxes[$page]) )
				$wp_meta_boxes[$page] = array();
			if ( !isset($wp_meta_boxes[$page][$context]) )
				$wp_meta_boxes[$page][$context] = array();

			foreach ( array_keys($wp_meta_boxes[$page]) as $a_context ) {
				foreach ( array('high', 'core', 'default', 'low') as $a_priority ) {
					if ( !isset($wp_meta_boxes[$page][$a_context][$a_priority][$id]) )
						continue;

					// If a core box was previously added or removed by a plugin, don't add.
					if ( 'core' == $priority ) {
						// If core box previously deleted, don't add
						if ( false === $wp_meta_boxes[$page][$a_context][$a_priority][$id] )
							return;

						/*
						 * If box was added with default priority, give it core priority to
						 * maintain sort order.
						 */
						if ( 'default' == $a_priority ) {
							$wp_meta_boxes[$page][$a_context]['core'][$id] = $wp_meta_boxes[$page][$a_context]['default'][$id];
							unset($wp_meta_boxes[$page][$a_context]['default'][$id]);
						}
						return;
					}
					// If no priority given and id already present, use existing priority.
					if ( empty($priority) ) {
						$priority = $a_priority;
					/*
					 * Else, if we're adding to the sorted priority, we don't know the title
					 * or callback. Grab them from the previously added context/priority.
					 */
					} elseif ( 'sorted' == $priority ) {
						$title = $wp_meta_boxes[$page][$a_context][$a_priority][$id]['title'];
						$callback = $wp_meta_boxes[$page][$a_context][$a_priority][$id]['callback'];
						$callback_args = $wp_meta_boxes[$page][$a_context][$a_priority][$id]['args'];
					}
					// An id can be in only one priority and one context.
					if ( $priority != $a_priority || $context != $a_context )
						unset($wp_meta_boxes[$page][$a_context][$a_priority][$id]);
				}
			}

			if ( empty($priority) )
				$priority = 'low';

			if ( !isset($wp_meta_boxes[$page][$context][$priority]) )
				$wp_meta_boxes[$page][$context][$priority] = array();

			$wp_meta_boxes[$page][$context][$priority][$id] = array('id' => $id, 'title' => $title, 'callback' => $callback, 'args' => $callback_args);
			// API replacement end.
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
		// API replacement start:
		$post = $post_id;
		$output = OBJECT;
		$filter = 'raw';

		if ( empty( $post ) && isset( $GLOBALS['post'] ) )
			$post = $GLOBALS['post'];

		if ( $post instanceof WP_Post ) {
			$_post = $post;
		} elseif ( is_object( $post ) ) {
			if ( empty( $post->filter ) ) {
				$_post = sanitize_post( $post, 'raw' );
				$_post = new \WP_Post( $_post );
			} elseif ( 'raw' == $post->filter ) {
				$_post = new \WP_Post( $post );
			} else {
				$_post = \WP_Post::get_instance( $post->ID );
			}
		} else {
			$_post = \WP_Post::get_instance( $post );
		}

		if ( ! $_post )
			return null;

		$_post = $_post->filter( $filter );

		if ( $output == ARRAY_A )
			return $_post->to_array();
		elseif ( $output == ARRAY_N )
			return array_values( $_post->to_array() );

		$post = $_post;
		// API replacement end.
		if ( isset( $this->config[ $section_id ]['fields'][ $field_id ] ) ) {
			$field_id = "{$this->prefix}_{$section_id}_{$field_id}";

			if ( ! empty( $post->$field_id ) ) {
				return $post->$field_id;
			}
		}

		return false;
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

					if ( ! empty( $value ) ) {
						// API replacement start:
						global $wpdb;

						$meta_type = 'post';
						$object_id = $post_id;
						$meta_key = $id;
						$meta_value = $value;
						$prev_value = '';

						if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) ) {
							return false;
						}

						$object_id = absint( $object_id );
						if ( ! $object_id ) {
							return false;
						}

						$table = _get_meta_table( $meta_type );
						if ( ! $table ) {
							return false;
						}

						$column = sanitize_key($meta_type . '_id');
						$id_column = 'user' == $meta_type ? 'umeta_id' : 'meta_id';

						// expected_slashed ($meta_key)
						$raw_meta_key = $meta_key;
						$meta_key = wp_unslash($meta_key);
						$passed_value = $meta_value;
						$meta_value = wp_unslash($meta_value);
						$meta_value = sanitize_meta( $meta_key, $meta_value, $meta_type );

						// Compare existing value to new value if no prev value given and the key exists only once.
						if ( empty($prev_value) ) {
							$old_value = get_metadata($meta_type, $object_id, $meta_key);
							if ( count($old_value) == 1 ) {
								if ( $old_value[0] === $meta_value )
									return false;
							}
						}

						$meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT $id_column FROM $table WHERE meta_key = %s AND $column = %d", $meta_key, $object_id ) );
						if ( empty( $meta_ids ) ) {
							return add_metadata( $meta_type, $object_id, $raw_meta_key, $passed_value );
						}

						$_meta_value = $meta_value;
						$meta_value = maybe_serialize( $meta_value );

						$data  = compact( 'meta_value' );
						$where = array( $column => $object_id, 'meta_key' => $meta_key );

						if ( !empty( $prev_value ) ) {
							$prev_value = maybe_serialize($prev_value);
							$where['meta_value'] = $prev_value;
						}

						$result = $wpdb->update( $table, $data, $where );
						if ( ! $result )
							return false;

						wp_cache_delete($object_id, $meta_type . '_meta');
						// API replacement end.
					} else {
						// API replacement start:
						global $wpdb;

						$meta_type = 'post';
						$object_id = $post_id;
						$meta_key = $id;
						$meta_value = $value;
						$delete_all = false;

						if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) && ! $delete_all ) {
							return false;
						}

						$object_id = absint( $object_id );
						if ( ! $object_id && ! $delete_all ) {
							return false;
						}

						$table = _get_meta_table( $meta_type );
						if ( ! $table ) {
							return false;
						}

						$type_column = sanitize_key($meta_type . '_id');
						$id_column = 'user' == $meta_type ? 'umeta_id' : 'meta_id';
						// expected_slashed ($meta_key)
						$meta_key = wp_unslash($meta_key);
						$meta_value = wp_unslash($meta_value);

						$_meta_value = $meta_value;
						$meta_value = maybe_serialize( $meta_value );

						$query = $wpdb->prepare( "SELECT $id_column FROM $table WHERE meta_key = %s", $meta_key );

						if ( !$delete_all )
							$query .= $wpdb->prepare(" AND $type_column = %d", $object_id );

						if ( '' !== $meta_value && null !== $meta_value && false !== $meta_value )
							$query .= $wpdb->prepare(" AND meta_value = %s", $meta_value );

						$meta_ids = $wpdb->get_col( $query );
						if ( !count( $meta_ids ) )
							return false;

						if ( $delete_all ) {
							$value_clause = '';
							if ( '' !== $meta_value && null !== $meta_value && false !== $meta_value ) {
								$value_clause = $wpdb->prepare( " AND meta_value = %s", $meta_value );
							}

							$object_ids = $wpdb->get_col( $wpdb->prepare( "SELECT $type_column FROM $table WHERE meta_key = %s $value_clause", $meta_key ) );
						}

						$query = "DELETE FROM $table WHERE $id_column IN( " . implode( ',', $meta_ids ) . " )";
						$count = $wpdb->query($query);

						if ( !$count )
							return false;

						if ( $delete_all ) {
							foreach ( (array) $object_ids as $o_id ) {
								wp_cache_delete($o_id, $meta_type . '_meta');
							}
						} else {
							wp_cache_delete($object_id, $meta_type . '_meta');
						}
						// API replacement end.
					}
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

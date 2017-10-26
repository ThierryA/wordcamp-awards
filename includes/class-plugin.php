<?php
/**
 * Main plugin class.
 *
 * @package WordCampAwards
 */

namespace WordCampAwards;

/**
 * Class Plugin
 */
class Plugin {

	/**
	 * Version of plugin.
	 *
	 * @var string
	 */
	public $version = '0.1';

	/**
	 * Plugin components.
	 *
	 * @var object
	 */
	public $components;

	/**
	 * Plugin location.
	 *
	 * @var string
	 */
	public $location;

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		$this->location = $this->set_location();
		spl_autoload_register( array( $this, 'autoload' ) );

		// Create objects.
		$this->components = new \stdClass();
		$this->components->assets = new Assets();
		$this->components->post_type = new Post_Type();
		$this->components->taxonomy = new Taxonomy();
		$this->components->metadata = new Metadata();
		$this->components->render = new Render();
	}

	/**
	 * Get the instance of this plugin.
	 *
	 * @return object $instance Plugin instance.
	 */
	public static function get_instance() {
		static $instance;

		if ( ! $instance instanceof Plugin ) {
			$instance = new Plugin();
		}

		return $instance;
	}

	/**
	 * Initialize classes.
	 */
	public function init() {
		$this->components->assets->init();
		$this->components->post_type->init();
		$this->components->taxonomy->init();
		$this->components->metadata->init();
		$this->components->render->init();
	}


	/**
	 * Autoload for classes that are in the same namespace as $this.
	 *
	 * @param string $class Class name.
	 */
	public function autoload( $class ) {
		$conditions = (
			preg_match( '/^(?P<namespace>.+)\\\\(?P<class>[^\\\\]+)$/', $class, $matches )
			&&
			isset( $matches['namespace'], $matches['class'] )
			&&
			__NAMESPACE__ === $matches['namespace']
		);

		if ( ! $conditions ) {
			return;
		}

		$class_path = trailingslashit( plugin_dir_path( __FILE__ ) );
		$class_path .= sprintf( 'class-%s.php', strtolower( str_replace( '_', '-', $matches['class'] ) ) );

		if ( is_readable( $class_path ) ) {
			require_once $class_path;
		}
	}

	/**
	 * Set the plugin location.
	 *
	 * @return object The plugin locations
	 */
	public function set_location() {
		$location = new \StdClass();
		$location->path = plugin_dir_path( dirname( __FILE__ ) );
		$location->slug = basename( $location->path );
		$location->url = plugin_dir_url( dirname( __FILE__ ) );

		return $location;
	}
}

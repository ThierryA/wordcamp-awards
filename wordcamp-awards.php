<?php
/**
 * Plugin Name: WordCamp Awards
 * Description: A plugin built for the WCCT API workshop.
 * Author: Thierry Muller, XWP
 * Version: 0.0.2
 * Author URI: https://xwp.co/
 *
 * @package WordCampAwards
 */

namespace WordCampAwards;

require_once __DIR__ . '/includes/class-plugin.php';
Plugin::get_instance()->init();

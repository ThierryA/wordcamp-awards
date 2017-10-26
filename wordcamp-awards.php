<?php
/**
 * A plugin built for the WCCT API workshop.
 *
 * @package WordCampAwards
 */

/*
Plugin Name: WordCamp Awards
Description: A plugin built for the WCCT API workshop.
Author: Thierry Muller, XWP
Version: 0.0.1
Author URI: https://xwp.co/
*/

namespace WordCampAwards;

require_once __DIR__ . '/includes/class-plugin.php';
Plugin::get_instance()->init();

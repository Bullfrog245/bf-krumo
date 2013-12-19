<?php
/**
 * Plugin Name:  Bullfrog Krumo
 * Plugin URI:   http://www.bullfroglabs.net
 * Description:  Implements the Krumo PHP5 debugging tool
 * Version:      1.0.1
 * Author:       Jeremiah Lutz
 * Author URI:   http://www.integritive.com
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * I missed the dpm() functionality provided by the Devel Drupal module, so this
 * plugin includes the PHP Debugging library "Krumo" and allows similar
 * functionality by calling the krumo() function.
 */
class BF_Krumo {
	// Instance of BF_Krumo
	private static $instance = false;

	// Plugin path and url settings
	protected $plugin_path;
	protected $plugin_url;

	/**
	 * Use the singleton pattern to ensure this plugin is only instantiated once.
	 */
	public static function get_instance()
	{
		if ( ! self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Set some variables used within the plugin and call the necessary
	 * WordPress action hooks.
	 */
	public function __construct()
	{
		// Set Plugin Path
		$this->plugin_path = dirname(__FILE__);

		// Set Plugin URL
		$this->plugin_url = WP_PLUGIN_URL . '/bf-krumo';

		// Include the Krumo library
		add_action( 'init', array( $this, 'bf_krumo_init' ) );

		// Add the Krumo override .css to both front and backend pages
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * init() action callback
	 *
	 * Includes the Krumo library.
	 */
	public function bf_krumo_init()
	{
		require_once( $this->plugin_path . '/krumo_0.2.1a/class.krumo.php' );
	}

	/**
	 * wp_enqueue_scripts() action callback
	 *
	 * Make sure the Krumo override .css files are included on both fronted and
	 * backend pages.
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_style(
			'bf-krumo',
			$this->plugin_url . '/bf-krumo.css',
			array(),
			'1.1',
			'all'
		);
	}
}

// Instantiate the BF_Krumo class.
$bf_krumo = BF_Krumo::get_instance();
<?php
/**
 * Plugin Name:       Bullfrog Krumo
 * Plugin URI:        http://www.bullfroglabs.net
 * Description:       Implements the Krumo var_dump replacement function
 * Version:           1.0.3
 * Author:            Jeremiah Lutz
 * Author URI:        http://www.integritive.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/Bullfrog245/bf-krumo
 */

/**
 * I missed the dpm() functionality provided by the Devel Drupal module, so this
 * plugin includes the PHP Debugging library "Krumo" and allows similar
 * functionality by calling the krumo() function.
 */
class BF_Krumo {
	// Instance of plugin class
	private static $instance = false;

	// Plugin path and url settings
	protected $plugin_dir;
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
		// The filesystem directory path (with trailing slash) for the plugin
		$this->plugin_dir = plugin_dir_path( __FILE__ );

		// The URL (with trailing slash) for the plugin
		$this->plugin_url = plugin_dir_url( __FILE__ );

		// Include the Krumo library
		add_action( 'init', array( $this, 'init' ) );

		// Add the Krumo override .css to both front and backend pages
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}



	/**
	 * register_activation_hook() callback function
	 *
	 * Adds the path to the Krumo directory to the krumo.ini file. This only
	 * needs to be done one time, and this seemed the best way to do it.
	 *
	 * TODO: Find out why this function isn't being called in the "Object"
	 * context so we can use plugin_url and plugin_dir.
	 */ 
	public function install()
	{
		// Build the krumo.ini url configuration string
		$url = plugin_dir_url( __FILE__ ) . 'krumo_0.2.1a/';
		$rep = 'url = "' . $url . '"' . "\n";

		// Open the file
		$fid = plugin_dir_path( __FILE__ ) . 'krumo_0.2.1a/krumo.ini';
		$ini = file( $fid );

		// Loop through each line and replace the URL where appropriate
		$result = '';
		foreach( $ini as $line )
		{
			$result .= substr( $line, 0, 3 ) == 'url' ? $rep : $line;
		}

		// Save the changes to the configuration file
		file_put_contents( $fid, $result );
	}



	/**
	 * add_action( 'init' ) callback function
	 *
	 * Includes the Krumo library on every page load.
	 */
	public function init()
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

// Instantiate the plugin.
$bf_krumo = BF_Krumo::get_instance();

// Activation hook to setup the krumo.ini file
register_activation_hook( __FILE__, array( 'BF_Krumo', 'install' ) );
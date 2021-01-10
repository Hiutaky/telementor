<?php
/**
 *
 * @link              https://alessandrodecristofaro.it
 * @since             1.0.1
 * @package           telementor
 *
 * @wordpress-plugin
 * Plugin Name:       Telementor | Telegram for Elementor Form
 * Plugin URI:        https://alessandrodecristofaro.it/telementor
 * Description:       Easy and Fast Telegram Integration for Elementor Form.
 * Version:           1.0.2
 * Author:            Alessandro De Cristofaro
 * Plugin URI:        https://alessandrodecristofaro.it/?ref=telementor-wp-admin
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       telementor
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Telementor Extension Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.0
 */
class Telementor{

	/**
	 * Plugin Version
	 *
	 * @since 1.0.0
	 *
	 * @var string The plugin version.
	 */
	const VERSION = '1.0.1';

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum Elementor version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '2.9.5';

	/**
	 * Minimum Elementor Pro Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum Elementor Pro version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_PRO_VERSION = '2.1.3';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.0';

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @var Telementor The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return Telementor An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {
			add_action( 'init', [ $this, 'i18n' ] );
			add_action( 'plugins_loaded', [ $this, 'init' ] );
			define( 'TELEMENTOR_PATH', plugin_dir_url( __FILE__ ) );	
	}

	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 *
	 * Fired by `init` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function i18n() {

		load_plugin_textdomain( 'telementor' );

	}

	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after Elementor (and other plugins) are loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init() {

		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return;
		}

		//check elementor pro version
		if ( ! version_compare( ELEMENTOR_PRO_VERSION, self::MINIMUM_ELEMENTOR_PRO_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_pro_version' ] );
			return;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return;
        }
        
		$this->init_actions();
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'telementor' ),
			'<strong>' . esc_html__( 'Telementor Extension', 'telementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'telementor' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'telementor' ),
			'<strong>' . esc_html__( 'Telementor Extension', 'telementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'telementor' ) . '</strong>',
			 self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	public function admin_notice_minimum_elementor_pro_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'telementor' ),
			'<strong>' . esc_html__( 'Telementor Extension', 'telementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor PRO', 'telementor' ) . '</strong>',
			 self::MINIMUM_ELEMENTOR_PRO_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'telementor' ),
			'<strong>' . esc_html__( 'Telementor Extension', 'telementor' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'telementor' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}


	/**
	 * Init Actions
	 *
	 * Include Actions files and register them
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init_actions(){

		add_action( 'elementor_pro/init', function() {
			include_once( __DIR__ . '/actions/telegram_action.php');

			// Instantiate the action class
			$telementor_form = new Telegram_Action();

			// Register the action with form widget
			\ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' )->add_form_action( $telementor_form->get_name(), $telementor_form );
		
		});

	}

}

Telementor::instance();
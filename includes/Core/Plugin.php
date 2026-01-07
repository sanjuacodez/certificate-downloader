<?php

namespace SJS_Cert\Core;

/**
 * The core plugin class.
 */
class Plugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The admin controller instance.
	 *
	 * @var \SJS_Cert\Controller\AdminController
	 */
	protected $admin_controller;

	/**
	 * The public controller instance.
	 *
	 * @var \SJS_Cert\Controller\FrontendController
	 */
	protected $public_controller;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->plugin_name = 'certificate-downloader';
		$this->version     = '1.0.1';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		// Initialize Controllers here
		$this->admin_controller = new \SJS_Cert\Controller\AdminController( $this->plugin_name, $this->version );
		$this->public_controller = new \SJS_Cert\Controller\FrontendController( $this->plugin_name, $this->version );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		add_action( 'admin_menu', array( $this->admin_controller, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this->admin_controller, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this->admin_controller, 'enqueue_scripts' ) );
		
		// AJAX hooks for admin
		add_action( 'wp_ajax_sjs_cert_import_students', array( $this->admin_controller, 'ajax_import_students' ) );
		add_action( 'wp_ajax_sjs_cert_import_batch_students', array( $this->admin_controller, 'ajax_import_batch_students' ) );
		add_action( 'wp_ajax_sjs_cert_preview_certificate', array( $this->admin_controller, 'ajax_preview_certificate' ) );
		
		// Admin Post hooks
		add_action( 'admin_post_sjs_cert_download_sample_csv', array( $this->admin_controller, 'download_sample_csv' ) );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this->public_controller, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this->public_controller, 'enqueue_scripts' ) );
		
		// Shortcodes
		add_shortcode( 'certificate_lookup', array( $this->public_controller, 'shortcode_certificate_lookup' ) );

		// AJAX hooks for frontend
		add_action( 'wp_ajax_sjs_cert_lookup', array( $this->public_controller, 'ajax_lookup_certificate' ) );
		add_action( 'wp_ajax_nopriv_sjs_cert_lookup', array( $this->public_controller, 'ajax_lookup_certificate' ) );
		add_action( 'wp_ajax_sjs_cert_generate_pdf', array( $this->public_controller, 'ajax_generate_pdf' ) );
		add_action( 'wp_ajax_nopriv_sjs_cert_generate_pdf', array( $this->public_controller, 'ajax_generate_pdf' ) );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		// Hooks are registered in constructor for simplicity in this pattern, 
		// or we could use a Loader class if we wanted strict separation.
		// For now, we are calling add_action directly in define_*_hooks.
	}
}

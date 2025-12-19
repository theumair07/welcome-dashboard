<?php
/**
 * Plugin Name: Welcome Dashboard
 * Plugin URI: https://github.com/theumair07/welcome-dashboard
 * Description: Transform the boring WordPress dashboard into a beautiful design experience. Compatible with any page builder.
 * Version: 1.0.0
 * Requires at least: 5.2
 * Requires PHP: 7.4
 * Author: Umair Khan
 * Author URI: https://umairyousafzai.com/
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: welcome-dashboard
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'UMY_WDW_VER', '1.0.0' );
define( 'UMY_WDW_DIR', plugin_dir_path( __FILE__ ) );
define( 'UMY_WDW_URL', plugins_url( '/', __FILE__ ) );
define( 'UMY_WDW_PATH', plugin_basename( __FILE__ ) );
define( 'UMY_WDW_FILE', __FILE__ );
final class UMY_WDW_Plugin {
	/**
	 * Holds the current class object.
	 * 
	 * @since 1.0.0
	 * @var object
	 */
	public static $instance;

	/**
	 * Whether Elementor is active.
	 * 
	 * @since 1.0.0
	 * @var bool
	 */
	public static $elementor_active = false;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		add_action( 'plugins_loaded', array( $this, 'loader' ) );
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function loader()
	{
		// Check if Elementor is active (optional, not required)
		self::$elementor_active = did_action( 'elementor/loaded' );

		// Handle embed mode (when page is loaded in dashboard iframe)
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is a display flag, not a form submission
		if ( isset( $_GET['umy_wdw_embed'] ) && sanitize_text_field( wp_unslash( $_GET['umy_wdw_embed'] ) ) === '1' ) {
			add_action( 'template_redirect', array( $this, 'setup_embed_mode' ) );
		}

		require_once UMY_WDW_DIR . 'classes/class-umy-wdw-admin.php';
		$umy_wdw_admin = UMY_WDW_Plugin\Admin::get_instance();
	}

	/**
	 * Setup embed mode - hide header, footer, admin bar for iframe display.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setup_embed_mode()
	{
		// Disable admin bar
		add_filter( 'show_admin_bar', '__return_false' );

		// Add CSS to hide header, footer, sidebar
		add_action( 'wp_head', array( $this, 'embed_mode_styles' ) );

		// Remove admin bar styles and scripts
		remove_action( 'wp_head', '_admin_bar_bump_cb' );
	}

	/**
	 * Output CSS styles for embed mode.
	 * Uses URL parameters passed from the iframe to control what's hidden.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function embed_mode_styles()
	{
		// Always hide header, footer, and title in embed mode for clean display
		$hide_header = true;
		$hide_footer = true;
		$hide_title  = true;
		?>
<style id="umy-wdw-embed-mode-css">
/* Always hide admin bar in embed */
#wpadminbar,
.admin-bar-spacer,
html.wp-toolbar #wpadminbar {
    display: none !important;
    height: 0 !important;
}

html.wp-toolbar,
html.wp-toolbar body.admin-bar,
body.admin-bar {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

html {
    margin-top: 0 !important;
}

<?php if ($hide_header) : ?>

/* Hide header elements */
header,
.site-header,
#masthead,
#header,
.header,
.main-header,
#site-header,
.wp-site-blocks>header,
nav.main-navigation,
.navigation-top,
.primary-menu,
#site-navigation,
.wp-block-template-part:first-child,
.site-branding {
    display: none !important;
}

<?php endif;
?><?php if ($hide_footer) : ?>

/* Hide footer elements */
footer,
.site-footer,
#colophon,
#footer,
.footer,
.main-footer,
.page-footer,
#site-footer,
.wp-site-blocks>footer,
.wp-block-template-part:last-child {
    display: none !important;
}

<?php endif;
?><?php if ($hide_title) : ?>

/* Hide page title */
.entry-title,
.page-title,
.wp-block-post-title,
.post-title,
article>header h1,
.entry-header h1,
h1.entry-title,
h1.page-title,
.hentry>h1:first-child,
.page>h1:first-child,
#content>h1:first-child,
main>h1:first-child,
.content-area h1:first-of-type {
    display: none !important;
}

<?php endif;
?>

/* Hide sidebar */
aside,
.sidebar,
.widget-area,
#secondary,
.site-sidebar {
    display: none !important;
}

/* Make content full width */
.site-content,
.content-area,
#content,
#primary,
#main,
main,
.entry-content,
article,
.site-main {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0px !important;
    float: none !important;
}

/* Hide breadcrumbs and other common elements */
.breadcrumbs,
.breadcrumb,
.entry-meta,
.post-navigation,
.comments-area,
.comment-respond,
.page-header {
    display: none !important;
}

/* Clean body */
body {
    background: #fff !important;
    overflow-x: hidden;
}

/* Remove overflow for proper iframe rendering */
html,
body {
    overflow: hidden !important;
    height: auto !important;
}
</style>
<?php
	}

	/**
	 * Check if Elementor is active.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function is_elementor_active()
	{
		return self::$elementor_active;
	}

	/**
	 * Get the instance of the class.
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof UMY_WDW_Plugin ) ) {
			self::$instance = new UMY_WDW_Plugin();
		}

		return self::$instance;
	}
}

// Initialize the class.
$umy_wdw_plugin = UMY_WDW_Plugin::get_instance();

/**
 * Plugin activation hook.
 *
 * @since 1.0.0
 * @return void
 */
function umy_wdw_activate() {
	// Set plugin version in database for future upgrades
	update_option( 'umy_wdw_version', UMY_WDW_VER );
}
register_activation_hook( __FILE__, 'umy_wdw_activate' );

/**
 * Plugin deactivation hook.
 *
 * @since 1.0.0
 * @return void
 */
function umy_wdw_deactivate() {
	// Clean up transients
	delete_transient( 'umy_wdw_settings_saved' );
}
register_deactivation_hook( __FILE__, 'umy_wdw_deactivate' );

/**
 * Add settings link on Plugins page.
 *
 * @since 1.0.0
 * @param array $links Plugin action links.
 * @return array Modified plugin action links.
 */
function umy_wdw_plugin_action_links( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		admin_url( 'options-general.php?page=umy-wdw-settings' ),
		__( 'Settings', 'welcome-dashboard' )
	);
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . UMY_WDW_PATH, 'umy_wdw_plugin_action_links' );

/**
 * Add review link to plugin row meta (next to "Visit plugin site").
 *
 * @since 1.0.0
 * @param array  $links Plugin row meta links.
 * @param string $file  Plugin file path.
 * @return array Modified plugin row meta links.
 */
function umy_wdw_plugin_row_meta( $links, $file ) {
	if ( UMY_WDW_PATH === $file ) {
		$review_link = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer" style="color: #f59e0b;">%s <span style="color: #f59e0b;">★</span></a>',
			'#',
			__( 'Leave a Review', 'welcome-dashboard' )
		);
		$links[] = $review_link;
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'umy_wdw_plugin_row_meta', 10, 2 );
<?php
namespace UMY_WDW_Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin logic.
 */
final class Admin {
	/**
	 * Holds the current class object.
	 * 
	 * @since 1.0.0
	 * @var object
	 */
	public static $instance;

	/**
	 * Holds the settings page slug.
	 * 
	 * @since 1.0.0
	 * @var string
	 */
	public $settings_page;

	/**
	 * Holds the settings page title.
	 * 
	 * @since 1.0.0
	 * @var string
	 */
	public $settings_title;

	/**
	 * Holds the settings.
	 * 
	 * @since 1.0.0
	 * @var array
	 */
	public $settings;

	/**
     * Holds the user roles.
     *
     * @since 1.0.0
     * @var array
     */
    public $roles;

    /**
     * Holds the current user role.
     *
     * @since 1.0.0
     * @var string
     */
    public $current_role;

	/**
	 * Initializes the admin settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct()
	{
		if ( ! is_admin() ) {
			return;
		}

		$this->settings_page = 'umy-wdw-settings';
		$this->settings_title = __('Welcome Dashboard', 'welcome-dashboard');
		$this->settings = $this->get_settings();

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 1000 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue admin styles and scripts on settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook )
	{
		// Only load on our settings page
		if ( 'settings_page_' . $this->settings_page !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'umy-wdw-admin-settings',
			UMY_WDW_URL . 'assets/css/admin-settings.css',
			array(),
			UMY_WDW_VER
		);

		// Enqueue notice countdown script (for success message auto-dismiss)
		wp_enqueue_script(
			'umy-wdw-admin-notice',
			UMY_WDW_URL . 'assets/js/admin-notice.js',
			array(),
			UMY_WDW_VER,
			true
		);

		// Enqueue multisite handler script
		if ( is_multisite() ) {
			wp_enqueue_script(
				'umy-wdw-admin-multisite',
				UMY_WDW_URL . 'assets/js/admin-multisite.js',
				array( 'jquery' ),
				UMY_WDW_VER,
				true
			);
		}
	}

	/**
	 * Initializes admin related logic.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_init()
	{
		$this->update_settings();

		global $pagenow;

        if ( 'index.php' != $pagenow ) {
            return;
		}

		$settings 	= $this->settings;
		$role		= $this->current_role;

		if ( ! empty( $settings ) && isset( $settings[ $role ] ) ) {
			if ( isset( $settings[ $role ]['template'] ) && ! empty( $settings[ $role ]['template'] ) ) {
				remove_action( 'welcome_panel', 'wp_welcome_panel' );
				add_action( 'welcome_panel', array( $this, 'welcome_panel' ) );

				// custom fallback for the users who don't have
				// enough capabilities to display welcome panel.
				if ( ! current_user_can( 'edit_theme_options' ) ) {
					add_action( 'admin_notices', array( $this, 'welcome_panel' ) );
				}
			}
		}
	}

	/**
	 * Renders welcome panel.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function welcome_panel()
	{
		include UMY_WDW_DIR . 'includes/welcome-panel.php';
	}

	/**
	 * Add Dashboard Welcome to admin menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_menu()
	{	
		global $wp_roles;

		$this->roles 		= $wp_roles->get_names();
		$this->current_role = $this->get_user_role();

		if ( is_multisite() ) {
			$hide_settings = get_blog_option( 1, 'umy_wdw_hide_from_subsites' );
			
			if ( $hide_settings && get_current_blog_id() != 1 ) {
				return;
			}
		}

		if ( current_user_can( 'manage_options' ) ) {

			$title = __('Welcome Dashboard', 'welcome-dashboard');
			$cap   = 'manage_options';
			$slug  = $this->settings_page;
			$func  = array( $this, 'render_settings' );

			add_submenu_page( 'options-general.php', $title, $title, $cap, $slug, $func );
		}
	}

	/**
	 * Renders settings content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_settings()
	{
		$title 			= $this->settings_title;
		$form_action 	= $this->get_form_action();
		$roles			= $this->roles;
		$current_role	= $this->current_role;
		$templates		= $this->get_templates();
		$settings		= $this->get_settings();

		include UMY_WDW_DIR . 'includes/admin-settings.php';
	}

	/**
	 * Renders template content in welcome panel using iframe embed.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_template()
	{
		$settings 	= $this->settings;
		$role		= $this->current_role;

		if ( ! empty( $settings ) && isset( $settings[ $role ] ) ) {
			if ( isset( $settings[ $role ]['template'] ) && ! empty( $settings[ $role ]['template'] ) ) {
				$template_id = $settings[ $role ]['template'];
				$site_id 	 = isset( $settings[ $role ]['site'] ) ? $settings[ $role ]['site'] : '';
				$dismissible = ! empty( $settings[ $role ]['dismissible'] );
				$is_multisite = is_multisite();

				// Handle multisite
				if ( ! empty( $site_id ) && $is_multisite ) {
					switch_to_blog( $site_id );
				}

				// Get the page URL
				$page_url = get_permalink( $template_id );

				if ( ! empty( $site_id ) && $is_multisite ) {
					restore_current_blog();
				}

				if ( ! $page_url ) {
					return;
				}

				// Always pass hide parameters (auto-hide enabled)
				$url_params = array(
					'umy_wdw_embed'    => '1',
					'hide_header'  => '1',
					'hide_footer'  => '1',
					'hide_title'   => '1'
				);
				
				// Add parameters to page URL
				$page_url = add_query_arg( $url_params, $page_url );

				// Output iframe embed with styles
				?>
<style>
/* Shimmer loading animation */
@keyframes umy-wdw-shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Hide WordPress Dashboard title */
#wpbody-content>.wrap>h1,
#wpbody-content h1.open,
.wrap>h1:first-child {
    display: none !important;
}

.umy-wdw-embed-container {
    width: 100%;
    min-height: 400px;
    background: linear-gradient(90deg, #f0f0f1 25%, #e8e8e9 50%, #f0f0f1 75%);
    background-size: 200% 100%;
    animation: umy-wdw-shimmer 1.5s ease-in-out infinite;
    border-radius: 0;
    overflow: hidden;
    box-shadow: none;
}

.umy-wdw-embed-container.umy-wdw-loaded {
    background: transparent;
    animation: none;
}

.umy-wdw-embed-iframe {
    width: 100%;
    min-height: 400px;
    border: none;
    display: block;
}

.welcome-panel {
    padding: 0 !important;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
}

/* Hide empty dashboard widget placeholder boxes completely */
#dashboard-widgets .meta-box-sortables.empty-container,
#dashboard-widgets .postbox-container .empty-container {
    border: none !important;
    min-height: 0 !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    visibility: hidden !important;
    overflow: hidden !important;
}

/* Also hide when widgets are present but hidden */
#dashboard-widgets .meta-box-sortables:not(:has(.postbox:not(.hide-if-js))) {
    border: none !important;
    min-height: 0 !important;
}

.welcome-panel::before,
.welcome-panel::after {
    display: none !important;
}

<?php if ( ! $dismissible) : ?>.welcome-panel .welcome-panel-close {
    display: none !important;
}

<?php else : ?>

/* Dismiss button styling - simple pink background with red text */
.welcome-panel .welcome-panel-close {
    background: #fee2e2 !important;
    color: #ef4444 !important;
    padding: 4px 10px !important;
    border-radius: 4px !important;
    text-decoration: none !important;
    font-size: 12px !important;
    transition: all 0.2s ease, opacity 0.15s ease-in-out !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

.welcome-panel .welcome-panel-close.umy-wdw-visible {
    opacity: 1 !important;
    pointer-events: auto !important;
}

.welcome-panel .welcome-panel-close:hover {
    background: #fecaca !important;
}

.welcome-panel .welcome-panel-close::before {
    display: none !important;
}

<?php endif;
?>
</style>
<div class="umy-wdw-embed-container" id="umy-wdw-embed-wrapper"
    style="opacity: 0; transition: opacity 0.3s ease-in-out;">
    <iframe id="umy-wdw-page-embed" class="umy-wdw-embed-iframe" src="<?php echo esc_url( $page_url ); ?>"
        scrolling="no" title="<?php esc_attr_e( 'Dashboard Welcome Content', 'welcome-dashboard' ); ?>"></iframe>
</div>
<?php
				// Enqueue embed handler script
				wp_enqueue_script(
					'umy-wdw-embed-handler',
					UMY_WDW_URL . 'assets/js/embed-handler.js',
					array(),
					UMY_WDW_VER,
					true
				);
			}
		}
	}

	/**
	 * Get setting form action attribute.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function get_form_action()
	{
		return admin_url( '/admin.php?page=' . $this->settings_page );
	}

	/**
	 * Check if a page was built with Elementor.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function is_elementor_page( $post_id )
	{
		return \UMY_WDW_Plugin::is_elementor_active() && 
			   get_post_meta( $post_id, '_elementor_edit_mode', true ) === 'builder';
	}

	/**
	 * Check if a page has Gutenberg blocks.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function has_gutenberg_blocks( $post_id )
	{
		$content = get_post_field( 'post_content', $post_id );
		return function_exists( 'has_blocks' ) && has_blocks( $content );
	}

	/**
	 * Get the content type label for a page.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @param string $post_type Post type.
	 * @return string
	 */
	private function get_content_type_label( $post_id, $post_type = 'page' )
	{
		if ( $post_type === 'elementor_library' ) {
			return 'Elementor Template';
		}

		if ( $this->is_elementor_page( $post_id ) ) {
			return 'Elementor';
		}

		if ( $this->has_gutenberg_blocks( $post_id ) ) {
			return 'Gutenberg';
		}

		return 'Classic';
	}

	/**
	 * Get all WordPress pages and Elementor templates.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	private function get_templates()
	{
		// Args for all WordPress pages
		$page_args = array(
            'post_type'         => 'page',
			'posts_per_page'    => '-1',
			'post_status'		=> array( 'publish', 'private', 'draft' ),
		);

		// Args for Elementor templates (only if Elementor is active)
		$templates = array();
		if ( \UMY_WDW_Plugin::is_elementor_active() ) {
			$template_args = array(
				'post_type'         => 'elementor_library',
				'posts_per_page'    => '-1',
				'post_status'		=> 'publish'
			);
			$templates = get_posts( $template_args );
		}

		$pages = get_posts( $page_args );

		// Multisite support.
		if ( is_multisite() ) {

            $blog_id = get_current_blog_id();

            if ( $blog_id != 1 ) {
                switch_to_blog(1);

                // Get posts from main site.
                $main_pages = get_posts( $page_args );
                
				if ( \UMY_WDW_Plugin::is_elementor_active() ) {
					$main_templates = get_posts( $template_args );
					foreach ( $main_templates as $main_post ) {
						$main_post->site_id = 1;
					}
					$templates = array_merge( $templates, $main_templates );
				}

                // Loop through each main site post
                // and add site_id to post object.
                foreach ( $main_pages as $main_post ) {
                    $main_post->site_id = 1;
                }

                $pages = array_merge( $pages, $main_pages );

                restore_current_blog();
            }
            else {
                // Loop through each main site post
                // and add site_id to post object.
                foreach ( $pages as $page ) {
                    $page->site_id = 1;
                }
                foreach ( $templates as $template ) {
                    $template->site_id = 1;
                }
            }
        }
		
		$data = array(
			'pages' => array(),
			'elementor_templates' => array(),
		);

		// Add pages with content type label
        if ( ! empty( $pages ) && ! is_wp_error( $pages ) ){
            foreach ( $pages as $post ) {
				$label = $this->get_content_type_label( $post->ID, 'page' );
                $data['pages'][ $post->ID ] = array(
					'title'	=> $post->post_title,
					'label' => $label,
					'site'	=> isset( $post->site_id ) ? $post->site_id : null,
					'type'  => 'page'
				);
            }
		}

		// Add Elementor templates with label
        if ( ! empty( $templates ) && ! is_wp_error( $templates ) ){
            foreach ( $templates as $post ) {
                $data['elementor_templates'][ $post->ID ] = array(
					'title'	=> $post->post_title,
					'label' => 'Elementor Template',
					'site'	=> isset( $post->site_id ) ? $post->site_id : null,
					'type'  => 'elementor_template'
				);
            }
		}
		
        return $data;
	}

	/**
	 * Get user roles.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	private function get_user_role()
	{
		// Get current user role in multisite network using WP_User_Query.
        if ( is_multisite() ) {
			$user_query = new \WP_User_Query( array( 'blog_id' => 1 , 'include' => array( get_current_user_id() ) ) );
			
            if ( ! empty( $user_query->results ) ) {
				$roles = $user_query->results[0]->roles;
				
                if ( is_array( $roles ) && count( $roles ) ) {
                    return $roles[0];
                }
            }
        }

        $user   = wp_get_current_user();
        $roles  = $user->roles;
        $roles  = array_shift( $roles );

        return $roles;
	}

	/**
	 * Get setting form database.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings()
	{
		$key = '_umy_wdw_templates';

		if ( is_multisite() ) {
			$settings = get_option( $key, false );
			$settings = ! $settings ? get_blog_option( 1, $key ) : $settings;
		} else {
			$settings = get_option( $key, array() );
		}

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		$this->settings = $settings;

		return $settings;
	}

	/**
	 * Update settings in database
	 *
	 * @since 1.0.0
	 */
	public function update_settings()
	{
		// Security: Check nonce
		if ( ! isset( $_POST['umy_wdw_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['umy_wdw_settings_nonce'] ) ), 'umy_wdw_settings' ) ) {
			return;
		}

		// Security: Check user capability
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_POST['umy_wdw_templates'] ) ) {
			return;
		}

		$data = array();
		$saved_pages = array();

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized within loop
		foreach ( wp_unslash( $_POST['umy_wdw_templates'] ) as $user_role => $template ) {
			// Sanitize role key
			$role_key = sanitize_key( $user_role );
			
			// Properly sanitize each field
			$sanitized = array(
				'template'    => isset( $template['template'] ) ? absint( $template['template'] ) : 0,
				'site'        => isset( $template['site'] ) ? absint( $template['site'] ) : 0,
				'dismissible' => isset( $template['dismissible'] ) ? 1 : 0,
			);
			
			$data[ $role_key ] = $sanitized;
			
			// Collect page names for success message
			if ( ! empty( $sanitized['template'] ) ) {
				$page = get_post( $sanitized['template'] );
				if ( $page ) {
					$saved_pages[] = $page->post_title;
				}
			}
		}

		update_option( '_umy_wdw_templates', $data );

		// Set success message transient
		if ( ! empty( $saved_pages ) ) {
			$saved_pages = array_unique( $saved_pages );
			set_transient( 'umy_wdw_settings_saved', $saved_pages, 30 );
		}

		// Handle multisite option
		if ( is_multisite() && get_current_blog_id() == 1 ) {
			$hide_from_subsites = isset( $_POST['umy_wdw_hide_from_subsites'] ) ? 1 : 0;
			update_option( 'umy_wdw_hide_from_subsites', $hide_from_subsites );
		}
	}

	/**
	 * Delete setting form database.
	 *
	 * @since 1.0.0
	 */
	public function delete_settings()
	{
		delete_option( '_umy_wdw_templates' );
	}

	/**
	 * Get class instance.
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof UMY_WDW_Plugin\Admin ) ) {
			self::$instance = new Admin();
		}

		return self::$instance;
	}
}
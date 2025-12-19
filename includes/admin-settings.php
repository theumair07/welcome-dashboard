<div class="umy-wdw-admin-wrap">

    <?php 
	// Display success message
	$umy_wdw_saved_pages = get_transient( 'umy_wdw_settings_saved' );
	if ( $umy_wdw_saved_pages && is_array( $umy_wdw_saved_pages ) ) {
		delete_transient( 'umy_wdw_settings_saved' );
		$umy_wdw_page_names = esc_html( implode( ', ', $umy_wdw_saved_pages ) );
		$umy_wdw_dashboard_url = admin_url( 'index.php' );
		?>
    <div class="umy-wdw-notice umy-wdw-notice-success" id="umy-wdw-success-notice">
        <div class="umy-wdw-notice-icon">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm-1.25 17.292l-4.5-4.364 1.857-1.858 2.643 2.506 5.643-5.784 1.857 1.857-7.5 7.643z" />
            </svg>
        </div>
        <div class="umy-wdw-notice-content">
            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $umy_wdw_page_names is escaped with esc_html() on line 8 ?>
            <strong><?php echo $umy_wdw_page_names; ?></strong>
            <?php esc_html_e('successfully set as custom Dashboard.', 'welcome-dashboard'); ?>
        </div>
        <a href="<?php echo esc_url( $umy_wdw_dashboard_url ); ?>" class="umy-wdw-btn umy-wdw-btn-success"
            id="umy-wdw-view-dashboard-btn">
            <?php esc_html_e('View Dashboard', 'welcome-dashboard'); ?> <span id="umy-wdw-countdown">(5)</span>
        </a>
    </div>
    <?php } ?>

    <?php if ( is_multisite() && get_current_blog_id() != 1 ) { ?>
    <div class="umy-wdw-notice umy-wdw-notice-warning">
        <div class="umy-wdw-notice-icon">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm1 17h-2v-2h2v2zm0-4h-2V7h2v6z" />
            </svg>
        </div>
        <div class="umy-wdw-notice-content">
            <?php esc_html_e('Please note, changing the template in subsite will override the main settings.', 'welcome-dashboard'); ?>
        </div>
    </div>
    <?php } ?>

    <!-- Settings Card with Header Inside -->
    <div class="umy-wdw-card">
        <!-- Header Section -->
        <div class="umy-wdw-header">
            <div class="umy-wdw-header-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <line x1="9" y1="21" x2="9" y2="9"></line>
                </svg>
            </div>
            <div class="umy-wdw-header-text">
                <h1><?php echo esc_html( $title ); ?></h1>
                <p><?php esc_html_e('Customize your WordPress dashboard with any page builder.', 'welcome-dashboard'); ?></p>
            </div>
        </div>

        <form method="post" id="umy-wdw-settings-form" action="<?php echo esc_url( $form_action ); ?>">
            <div class="umy-wdw-card-body">
                <table class="umy-wdw-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('User Role', 'welcome-dashboard'); ?></th>
                            <th><?php esc_html_e('Page / Template', 'welcome-dashboard'); ?></th>
                            <th><?php esc_html_e('Dismissible', 'welcome-dashboard'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Loop variable, not global ?>
                        <?php foreach ( $roles as $role => $umy_wdw_role_title ) { ?>
                        <tr>
                            <td data-label="<?php esc_attr_e('User Role', 'welcome-dashboard'); ?>">
                                <span class="umy-wdw-role-badge"><?php echo esc_html( $umy_wdw_role_title ); ?></span>
                            </td>
                            <td data-label="<?php esc_attr_e('Page / Template', 'welcome-dashboard'); ?>">
                                <div class="umy-wdw-select-wrap">
                                    <select name="umy_wdw_templates[<?php echo esc_attr( $role ); ?>][template]"
                                        class="umy-wdw-select umy-wdw-templates-list">
                                        <option value=""><?php esc_html_e('— Select —', 'welcome-dashboard'); ?></option>
                                        <?php if ( ! empty( $templates['pages'] ) ) : ?>
                                        <optgroup label="<?php esc_attr_e('Pages', 'welcome-dashboard'); ?>">
                                            <?php // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Loop variable ?>
                                            <?php foreach ( $templates['pages'] as $id => $umy_wdw_template ) : ?>
                                            <option value="<?php echo esc_attr( $id ); ?>"
                                                <?php selected( isset( $settings[$role]['template'] ) ? $settings[$role]['template'] : '', $id ); ?>
                                                data-site="<?php echo esc_attr( $umy_wdw_template['site'] ?? '' ); ?>">
                                                <?php echo esc_html( $umy_wdw_template['title'] . ' (' . $umy_wdw_template['label'] . ')' ); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <?php endif; ?>
                                        <?php if ( ! empty( $templates['elementor_templates'] ) ) : ?>
                                        <optgroup label="<?php esc_attr_e('Elementor Templates', 'welcome-dashboard'); ?>">
                                            <?php // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Loop variable ?>
                                            <?php foreach ( $templates['elementor_templates'] as $id => $umy_wdw_et_template ) : ?>
                                            <option value="<?php echo esc_attr( $id ); ?>"
                                                <?php selected( isset( $settings[$role]['template'] ) ? $settings[$role]['template'] : '', $id ); ?>
                                                data-site="<?php echo esc_attr( $umy_wdw_et_template['site'] ?? '' ); ?>">
                                                <?php echo esc_html( $umy_wdw_et_template['title'] ); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <?php if ( is_multisite() ) { ?>
                                <input type="hidden" name="umy_wdw_templates[<?php echo esc_attr( $role ); ?>][site]"
                                    value="<?php echo esc_attr( $settings[$role]['site'] ?? '' ); ?>" />
                                <?php } ?>
                            </td>
                            <td data-label="<?php esc_attr_e('Dismissible', 'welcome-dashboard'); ?>">
                                <label class="umy-wdw-toggle"
                                    aria-label="<?php /* translators: %s: User role name */ printf( esc_attr__( 'Toggle dismissible for %s', 'welcome-dashboard' ), esc_attr( $umy_wdw_role_title ) ); ?>">
                                    <input type="checkbox"
                                        name="umy_wdw_templates[<?php echo esc_attr( $role ); ?>][dismissible]"
                                        value="1" role="switch"
                                        aria-checked="<?php echo ! empty( $settings[$role]['dismissible'] ) ? 'true' : 'false'; ?>"
                                        <?php checked( ! empty( $settings[$role]['dismissible'] ) ); ?> />
                                    <span class="umy-wdw-toggle-slider" aria-hidden="true"></span>
                                </label>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php if ( is_multisite() && get_current_blog_id() == 1 ) { ?>
            <div class="umy-wdw-multisite-option">
                <label class="umy-wdw-checkbox-label">
                    <input type="checkbox" value="1" name="umy_wdw_hide_from_subsites"
                        <?php checked( get_option( 'umy_wdw_hide_from_subsites' ), true ); ?> />
                    <span class="umy-wdw-checkbox-box"></span>
                    <?php esc_html_e('Hide settings from network subsites', 'welcome-dashboard'); ?>
                </label>
            </div>
            <?php } ?>

            <?php wp_nonce_field( 'umy_wdw_settings', 'umy_wdw_settings_nonce' ); ?>

            <div class="umy-wdw-card-footer">
                <button type="submit" class="umy-wdw-btn umy-wdw-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <?php esc_html_e('Save Changes', 'welcome-dashboard'); ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="umy-wdw-info-box">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path
                d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm1 17h-2v-6h2v6zm0-8h-2V7h2v2z" />
        </svg>
        <span><?php esc_html_e('Create a page with any WordPress page builder, then select it here to display as the welcome panel on your dashboard.', 'welcome-dashboard'); ?></span>
    </div>
</div>
<div id="umy-wdw-dashboard-welcome" class="umy-wdw-panel-content">
	<?php if ( ! current_user_can( 'edit_theme_options' ) ) { ?>
        <a class="welcome-panel-close" href="<?php echo esc_url( admin_url( 'welcome=0' ) ); ?>"><?php esc_html_e( 'Dismiss', 'welcome-dashboard' ); ?></a>
	<?php } ?>
	
	<?php $this->render_template(); ?>
</div>

<?php if ( ! current_user_can( 'edit_theme_options' ) ) { ?>
<script type="text/javascript">
    ;(function($) {
        $(document).ready(function() {
            $('<div id="welcome-panel" class="welcome-panel"></div>').insertBefore('#dashboard-widgets-wrap').append($('#umy-wdw-dashboard-welcome'));
        });
    })(jQuery);
</script>
<?php } ?>
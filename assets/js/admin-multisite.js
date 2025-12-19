/**
 * Welcome Dashboard - Multisite Handler
 * Handles site ID updates when template selection changes
 *
 * @package WelcomeDashboardForWordPress
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    $('.umy-wdw-templates-list').on('change', function () {
        var id = $(this).val();
        var siteId = $(this).find('option[value="' + id + '"]').data('site');

        if ('' !== siteId && undefined !== siteId) {
            $(this).closest('td').find('input[type="hidden"]').val(siteId);
        }
    });
})(jQuery);

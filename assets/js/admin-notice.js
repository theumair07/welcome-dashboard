/**
 * Welcome Dashboard - Settings Success Handler
 * Handles the countdown and auto-dismiss of success notices
 *
 * @package WelcomeDashboardForWordPress
 * @since 1.0.0
 */

(function () {
    'use strict';

    var countdown = 5;
    var countdownEl = document.getElementById('umy-wdw-countdown');
    var noticeEl = document.getElementById('umy-wdw-success-notice');

    if (!countdownEl || !noticeEl) {
        return;
    }

    var timer = setInterval(function () {
        countdown--;
        if (countdownEl) {
            countdownEl.textContent = '(' + countdown + ')';
        }
        if (countdown <= 0) {
            clearInterval(timer);
            if (noticeEl) {
                noticeEl.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                noticeEl.style.opacity = '0';
                noticeEl.style.transform = 'translateY(-10px)';
                setTimeout(function () {
                    noticeEl.style.display = 'none';
                }, 300);
            }
        }
    }, 1000);
})();

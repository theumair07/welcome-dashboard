/**
 * Welcome Dashboard - Embed Iframe Handler
 * Handles iframe auto-resize and fade-in effect
 *
 * @package WelcomeDashboardForWordPress
 * @since 1.0.0
 */

(function () {
	'use strict';

	var iframe = document.getElementById('umy-wdw-page-embed');
	var container = document.getElementById('umy-wdw-embed-wrapper');

	if (!iframe || !container) {
		return;
	}

	var resizeTimeout;

	/**
	 * Smart auto-resize iframe based on content height.
	 * Uses multiple properties for robust height calculation.
	 */
	function resizeIframe() {
		clearTimeout(resizeTimeout);
		resizeTimeout = setTimeout(function () {
			try {
				var doc = iframe.contentDocument || (iframe.contentWindow ? iframe.contentWindow.document : null);
				if (!doc) {
					return;
				}

				var body = doc.body;
				var html = doc.documentElement;

				var height = Math.max(
					body ? body.scrollHeight : 0,
					body ? body.offsetHeight : 0,
					html ? html.clientHeight : 0,
					html ? html.scrollHeight : 0,
					html ? html.offsetHeight : 0
				);

				if (height > 0) {
					iframe.style.height = height + 'px';
				}
			} catch (e) {
				// Cross-origin fallback
				iframe.style.height = '80vh';
			}
		}, 50);
	}

	iframe.addEventListener('load', function () {
		// Stop shimmer animation and fade in
		container.classList.add('umy-wdw-loaded');
		container.style.opacity = '1';

		// Also fade in the dismiss button simultaneously
		var dismissBtn = document.querySelector('.welcome-panel-close');
		if (dismissBtn) {
			dismissBtn.classList.add('umy-wdw-visible');
		}

		// Initial resize
		resizeIframe();

		// Attach MutationObserver for dynamic content changes
		try {
			var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
			if (iframeDoc && iframeDoc.body) {
				var observer = new MutationObserver(resizeIframe);
				observer.observe(iframeDoc.body, {
					childList: true,
					subtree: true,
					attributes: true
				});
			}
		} catch (e) {
			// Cross-origin: fallback to interval-based resize
			setInterval(resizeIframe, 1000);
		}
	});

	// Resize on window resize
	window.addEventListener('resize', resizeIframe);
})();

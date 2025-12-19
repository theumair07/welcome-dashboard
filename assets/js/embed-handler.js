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

	/**
	 * Auto-resize iframe based on content height
	 */
	function resizeIframe() {
		try {
			var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
			if (iframeDoc && iframeDoc.body) {
				var height = iframeDoc.body.scrollHeight;
				if (height > 0) {
					iframe.style.height = height + 'px';
				}
			}
		} catch (e) {
			// Cross-origin, use default height
			iframe.style.height = '500px';
		}
	}

	iframe.addEventListener('load', function () {
		// Fade in the entire container (hides white flash)
		container.style.opacity = '1';

		// Also fade in the dismiss button simultaneously
		var dismissBtn = document.querySelector('.welcome-panel-close');
		if (dismissBtn) {
			dismissBtn.classList.add('umy-wdw-visible');
		}

		resizeIframe();
		// Resize again after a short delay for dynamic content
		setTimeout(resizeIframe, 500);
		setTimeout(resizeIframe, 1500);
	});

	// Resize on window resize
	window.addEventListener('resize', resizeIframe);
})();

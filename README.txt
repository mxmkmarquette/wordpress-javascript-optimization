=== Javascript Optimization ===
Contributors: o10n
Donate link: https://github.com/o10n-x/
Tags: css, critical css, async, minify, editor, concat, minifier, concatenation, optimization, optimize, combine, merge, cache
Requires at least: 4.0
Requires PHP: 5.4
Tested up to: 4.9.4
Stable tag: 0.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Advanced CSS optimization toolkit. Critical CSS, minification, concatenation, async loading, advanced editor, CSS Lint, Clean CSS (professional), beautifier and more.

== Description ==

This plugin is a toolkit for professional CSS optimization.

The plugin provides in a complete solution for CSS code optimization, CSS delivery optimization (async CSS loading) and Critical CSS management.

The plugin provides many unique innovations including conditional Critical CSS, timed CSS loading and/or rendering based on `requestAnimationFrame` with frame target, `requestIdleCallback`, element scrolled into view or a Media Query.

The plugin enables to render and unrender stylesheets based on a Media Query or element scrolled in and out of viewport enabling to optimize the CSS for individual devices (e.g. save +100kb of CSS on mobile devices). The plugin makes it possible to enable and disable stylesheets based on the viewport orientation change or element scrolled in or out of view event, making it possile (and easy to manage) to dynamically redesign a website based on events.

With debug modus enabled, the browser console will show detailed information about the CSS loading and rendering process including a [Performance API](https://developer.mozilla.org/nl/docs/Web/API/Performance) result for an insight in the CSS loading performance of any given configuration.

The plugin contains an advanced CSS editor with CSS Lint, Clean-CSS code optimization and CSS Beautifier. The editor can be personalized with more than 30 themes.

Additional features can be requested on the [Github forum](https://github.com/o10n-x/wordpress-css-optimization/issues).

**This plugin is a beta release.**

Documentation is available on [Github](https://github.com/o10n-x/wordpress-css-optimization/tree/master/docs).

== Installation ==

### WordPress plugin installation

1. Upload the `css-optimization/` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the plugin settings page.
4. Configure CSS Optimization settings. Documentation is available on [Github](https://github.com/o10n-x/wordpress-css-optimization/tree/master/docs).

== Screenshots ==

= 0.0.7 =

Bugfix: timed render not configured correctly (this plugin is a prototype copied partly from the CSS optimization plugin, the client should now perform correctly).

= 0.0.3 =

Added: cache management in admin menu.

= 0.0.2 = 

Bugfix/improvement: Async Config Filter load and render timing.

== Changelog ==

= 0.0.1 =

Beta release. Please provide feedback on [Github forum](https://github.com/o10n-x/wordpress-css-optimization/issues).

== Upgrade Notice ==

None.
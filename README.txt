=== JS Optimization ===
Contributors: o10n
Donate link: https://github.com/o10n-x/
Tags: css, critical css, async, minify, editor, concat, minifier, concatenation, optimization, optimize, combine, merge, cache
Requires at least: 4.0
Requires PHP: 5.4
Tested up to: 4.9.4
Stable tag: 0.0.11
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Advanced Javascript optimization toolkit. Minify, concat/merge, async loading, advanced editor, ES Lint, UglifyJS (professional), beautifier and more.

== Description ==

This plugin is a toolkit for professional javascript optimization.

The plugin provides in a complete solution for javascript code optimization, asynchronous loading, timed script execution, HTTP/2 Server Push, localStorage cache, external script proxy and more.

The plugin provides many unique innovations including timed script loading and/or execution based on `requestAnimationFrame` with frame target, `requestIdleCallback`, element scrolled into view or a Media Query.

The plugin enables to execute scripts based on a Media Query or element scrolled in to viewport enabling to optimize javascript loading for individual devices (for example save +100kb of javascript on mobile devices). 

With debug modus enabled, the browser console will show detailed information about the javascript loading and execution process including a [Performance API](https://developer.mozilla.org/nl/docs/Web/API/Performance) result for an insight in the javascript loading performance of any given configuration.

The plugin contains an advanced Javascript editor with ES Lint, UglifyJS code optimization and a javascript beautifier. The editor can be personalized with more than 30 themes.

Additional features can be requested on the [Github forum](https://github.com/o10n-x/wordpress-javascript-optimization/issues).

**This plugin is a beta release.**

Documentation is available on [Github](https://github.com/o10n-x/wordpress-javascript-optimization/tree/master/docs).

== Installation ==

### WordPress plugin installation

1. Upload the `js-optimization/` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the plugin settings page.
4. Configure CSS Optimization settings. Documentation is available on [Github](https://github.com/o10n-x/wordpress-javascript-optimization/tree/master/docs).

== Screenshots ==
1. Javascript Optimization
2. Javascript Code Optimization
3. Javascript Load Optimization
4. Advanced Javascript Editor

= 0.0.11 =
Bugfix: settings link on plugin index.

= 0.0.10 =
Core update (see changelog.txt)

= 0.0.8 =
Bugfix: Timed loading/exec not working on iphone when using localStorage.

= 0.0.7 =
Bugfix: timed render not configured correctly (this plugin is a prototype copied partly from the CSS optimization plugin, the client should now perform correctly).

= 0.0.3 =

Added: cache management in admin menu.

= 0.0.2 = 

Bugfix/improvement: Async Config Filter load and render timing.

== Changelog ==

= 0.0.1 =

Beta release. Please provide feedback on [Github forum](https://github.com/o10n-x/wordpress-javascript-optimization/issues).

== Upgrade Notice ==

None.
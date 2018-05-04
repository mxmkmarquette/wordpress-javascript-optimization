=== Javascript Optimization ===
Contributors: o10n
Donate link: https://github.com/o10n-x/
Tags: javascript, minify, async, uglifyjs, concat, beautify, js, compress, compressor, optimizer, code optimization, beautifier, merge, concatenation, lint
Requires at least: 4.0
Requires PHP: 5.4
Tested up to: 4.9.4
Stable tag: 0.0.54
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

1. Upload the `javascript-optimization/` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the plugin settings page.
4. Configure CSS Optimization settings. Documentation is available on [Github](https://github.com/o10n-x/wordpress-javascript-optimization/tree/master/docs).

== Screenshots ==
1. Javascript Optimization
2. Javascript Code Optimization
3. Javascript Load Optimization
4. Advanced Javascript Editor


== Changelog ==

= 0.0.54 =
* Bugfix: inline script concatenation does not use minified script text.

= 0.0.52 =
* Bugfix: Search & Replace filter in pre HTML optimization hook not reset correctly.

= 0.0.51 =
* Bugfix: Code search & replace regex option not working.

= 0.0.50 =
* Improved: plugin index.

= 0.0.49 =
* Bugfix: concat group filter causes error with empty string configuration. 

= 0.0.48 =
* Added: plugin update protection (plugin index).

= 0.0.47 =
* Added: Proxy option to delete or rewrite script-injected scripts ([@cwfaraday](https://wordpress.org/support/topic/emoji-js-isnt-handled/)).

= 0.0.46 =
* Core update (see changelog.txt)

= 0.0.45 =
* Added: support for multiple Javascript minifiers.
* Added: [Google Closure Compiler Service](https://developers.google.com/closure/compiler/)
* Added: Custom minifier option (support for Node.js, server software etc.)
* Added: Option to disable minification for individual scripts in async config filter (`"minify": false`)
* Added: Option to set minifier for individual scripts or concat groups in async config filter and concat group config.

= 0.0.44 =
* Core update (see changelog.txt)

= 0.0.43 =
* Bugfix: HTTP/2 Server Push applied when HTTP/2 Optimization plugin is disabled.
* Bugfix: Async loaded concatenated scripts not pushed by HTTP/2 Server Push.

= 0.0.42 =
* Core update (see changelog.txt)

= 0.0.40 =
* Bugfix: editor theme not loading after `wp_add_inline_script` update.

See changelog.txt for older updates.

== Upgrade Notice ==

None.
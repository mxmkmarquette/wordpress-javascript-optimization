<?php

/**
 * Critical CSS / Above The Fold quality test template
 *
 * @since      2.9.7
 * @package    o10n
 * @subpackage abovethefold/admin
 * @author     PageSpeed.pro <info@optimization.team>
 */

$qs_start = (strpos($url, '?') !== false) ? '&' : '?';

$critical_url = $url . $qs_start . 'o10n-no-css=1&t=' . time();
$full_url = $url . $qs_start . 't=' . time(); // regular view

// custom theme
$user = wp_get_current_user();
$editor_theme = get_user_meta($user->ID, 'o10n_editor_theme', true);
if ($editor_theme && $editor_theme !== 'default') {
    $editor_theme_css = '<link rel="stylesheet" href="'. O10N_CORE_URI . 'admin/css/codemirror/' . $editor_theme . '" />';
} else {
    $editor_theme = 'default';
    $editor_theme_css = '';
}

$output = '<!DOCTYPE html>
<html>
<head>
<title>Critical CSS Editor - Above The Fold Optimization</title>
<meta name="robots" content="noindex, nofollow" />
<link rel="stylesheet" href="'.$plugin_uri.'public/css/view-css-editor.css" />
'.$editor_theme_css.'
<link rel="stylesheet" href="'.includes_url('/css/dashicons.min.css?ver=4.9.2').'" />
<style>.gutter.gutter-horizontal {background-image: url(\''.$plugin_uri.'public/vertical-grip.png\');} .gutter.gutter-vertical {background-image: url(\''.$plugin_uri.'public/horizontal-grip.png\');}</style>
<script src="'.$plugin_uri.'public/js/view-css-editor.js"></script>
<script>O10N[0]({});</script>
</head>
<body scroll="no">
<header>
<div class="h">
<h1>Critical CSS Editor</h1>
<h2>Above The Fold Optimization</h2>
</div>
<button type="button" title="Split View (horizontal)" id="btn_split_h"><span class="dashicons dashicons-image-flip-horizontal"></span></button>
<button type="button" title="Split View (vertical)" id="btn_split_v"><span class="dashicons dashicons-image-flip-vertical"></span></button>
<button type="button" title="Toggle Single View: Critical CSS vs Full CSS" id="btn_full_toggle"><span class="dashicons dashicons-admin-page"></span></button>
<button type="button" title="Edit Critical CSS (CodeMirror)" id="btn_editor"><span class="dashicons dashicons-editor-code"></span></button>&nbsp;&nbsp;
<button type="button" title="Reload Critical CSS View"><span class="dashicons dashicons-update" id="btn_reload"></span></button>
<button type="button" title="Open Critical CSS View in new window (useful for responsive testing)" id="btn_open"><span class="dashicons dashicons-external"></span></button>&nbsp;&nbsp;&nbsp;&nbsp;
<button type="button" title="Extract Critical CSS (javascript widget based on viewport)" id="btn_extract_critical_css"><span class="dashicons dashicons-media-code"></span></button>
<button type="button" title="Download Full CSS (javascript widget)" id="btn_extract_full_css"><span class="dashicons dashicons-download"></span></button>
<div class="syncscroll"><label><input type="checkbox" value="1"> Synchronize scroll</label></div>
<div class="clear"></div>
</header>
<div class="split split-horizontal" style="width: calc(50% - 5px);" id="critical-css-view"><iframe src="' . $critical_url . '" name="criticalcss" frameborder="0" width="100%" height="100%"></iframe></div>
<div class="split split-horizontal" style="width: calc(50% - 5px);" id="full-css-view"><iframe src="' . $full_url . '" frameborder="0" width="100%" height="100%"></iframe></div>
<div class="split split-horizontal" style="display:none;" id="css-editor-view"><textarea id="critical-css-editor" data-theme="'.esc_attr(str_replace('.css', '', $editor_theme)).'" placeholder="Loading Critical CSS..." disabled></textarea></div>
</body>
</html>';

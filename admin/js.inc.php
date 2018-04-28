<?php
namespace O10n;

/**
 * Javascript optimization admin template
 *
 * @package    optimization
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH') || !defined('O10N_ADMIN')) {
    exit;
}

// print form header
$this->form_start(__('Javascript Optimization', 'optimization'), 'js');

?>
<table class="form-table">
    <tr valign="top">
        <th scope="row">Minify</th>
        <td>
            <label><input type="checkbox" value="1" name="o10n[js.minify.enabled]" data-json-ns="1"<?php $checked('js.minify.enabled'); ?> /> Enabled</label>
            <p class="description">Compress, bundle and optimize Javascript code.</p>

            <div class="suboption" data-ns="js.minify"<?php $visible('js.minify'); ?>>

                <p class="poweredby" data-ns="js.minify"<?php $visible('js.minify', ($get('js.minify.minifier') === 'jsmin')); ?> data-ns-condition="js.minify.minifier==jsmin">Powered by <a href="https://github.com/mrclay/jsmin-php" target="_blank">JSMin</a><span class="star">
                    <a class="github-button" data-manual="1" href="https://github.com/mrclay/jsmin-php" data-icon="octicon-star" data-show-count="true" aria-label="Star mrclay/jsmin-php on GitHub">Star</a></span>
                    </p>

                <p class="poweredby" data-ns="js.minify"<?php $visible('js.minify', ($get('js.minify.minifier') === 'jshrink')); ?> data-ns-condition="js.minify.minifier==jshrink">Powered by <a href="https://github.com/tedious/JShrink" target="_blank">JShrink</a><span class="star">
                    <a class="github-button" data-manual="1" href="https://github.com/tedious/JShrink" data-icon="octicon-star" data-show-count="true" aria-label="Star tedious/JShrink on GitHub">Star</a></span>
                    </p>

                <p class="poweredby" data-ns="js.minify"<?php $visible('js.minify', ($get('js.minify.minifier') === 'closure-compiler-service')); ?> data-ns-condition="js.minify.minifier==closure-compiler-service">Powered by <a href="https://github.com/google/closure-compiler" target="_blank">Closure Compiler</a><span class="star">
                    <a class="github-button" data-manual="1" href="https://github.com/google/closure-compiler" data-icon="octicon-star" data-show-count="true" aria-label="Star google/closure-compiler on GitHub">Star</a></span>
                    </p>

                <select name="o10n[js.minify.minifier]" data-ns-change="js.minify" data-json-default="<?php print esc_attr(json_encode('jsmin')); ?>">
                    <option value="jsmin"<?php $selected('js.minify.minifier', 'jsmin'); ?>>JSMin (mcrclay)</option>
                    <!--option value="jshrink"<?php $selected('js.minify.minifier', 'jshrink'); ?>>JShrink</option-->
                    <option value="closure-compiler-service"<?php $selected('js.minify.minifier', 'closure-compiler-service'); ?>>Google Closure Compiler Service API</option>
                    <option value="custom"<?php $selected('js.minify.minifier', 'custom'); ?>>Custom minifier (WordPress filter hook)</option>
                </select> 
                <p class="description">Choose a minifier that provides the best performance for your Javascript code.</p>
            </div>

            <div class="suboption" data-ns="js.minify"<?php $visible('js.minify', ($get('js.minify.minifier') === 'custom')); ?> data-ns-condition="js.minify.minifier==custom">
                <p style="font-size:16px;line-height:18px;">The Custom Minifier option enables to use any Javascript minifier via the WordPress filter hook <code>o10n_js_custom_minify</code>. (<a href="javascript:void(0);" onclick="jQuery('#custom_minify_example').fadeToggle();">show example</a>)</p>
            <div class="info_yellow" id="custom_minify_example" style="display:none;"><strong>Example:</strong> <pre class="clickselect" title="<?php print esc_attr('Click to select', 'optimization'); ?>" style="cursor:copy;padding: 10px;margin: 0 1px;margin-top:5px;font-size: 13px;">
/* Custom Javascript minifier */
add_filter('o10n_js_custom_minify', function ($JS) {

    // apply javascript optimization
    exec('/node /path/to/optimize-js.js /tmp/js-source.js');
    $minified = file_get_contents('/tmp/output.js');

    // alternative
    $minified = JSCompressor::minify($JS);

    return $minified;

});</pre></div>
            </div>

            <div class="suboption" data-ns="js.minify"<?php $visible('js.minify', ($get('js.minify.minifier') === 'closure-compiler-service')); ?> data-ns-condition="js.minify.minifier==closure-compiler-service">

                <p class="info_yellow"><strong><span class="dashicons dashicons-smiley"></span></strong> The <a href="https://developers.google.com/closure/compiler/" target="_blank">Google Closure Compiler Service API</a> is a free service by Google.</p>
                
                <p class="suboption">
                    <label><input type="checkbox" value="1" name="o10n[js.minify.fallback.enabled]" data-json-ns="1"<?php $checked('js.minify.fallback.enabled'); ?> /> Configure fallback minifier</label>
                </p>
                <p class="description">The Google Closure Compiler API depends on a external resource that may be down or to slow to respond due to network related issues. This option enables to configure a local minifier as fallback when the Closure Compiler Service API fails.</p>

                <div class="suboption" data-ns="js.minify.fallback"<?php $visible('js.minify.fallback'); ?>>

                    <p class="poweredby" data-ns="js.minify.fallback"<?php $visible('js.minify.fallback', ($get('js.minify.fallback.minifier') === 'jsmin')); ?> data-ns-condition="js.minify.fallback.minifier==jsmin">Powered by <a href="https://github.com/mrclay/jsmin-php" target="_blank">JSMin</a><span class="star">
                        <a class="github-button" data-manual="1" href="https://github.com/mrclay/jsmin-php" data-icon="octicon-star" data-show-count="true" aria-label="Star mrclay/jsmin-php on GitHub">Star</a></span>
                        </p>

                    <p class="poweredby" data-ns="js.minify.fallback"<?php $visible('js.minify.fallback', ($get('js.minify.fallback.minifier') === 'jshrink')); ?> data-ns-condition="js.minify.fallback.minifier==jshrink">Powered by <a href="https://github.com/tedious/JShrink" target="_blank">JShrink</a><span class="star">
                        <a class="github-button" data-manual="1" href="https://github.com/tedious/JShrink" data-icon="octicon-star" data-show-count="true" aria-label="Star tedious/JShrink on GitHub">Star</a></span>
                        </p>
                
                    <h5 class="h">Fallback Minifier</h5>
                    <select name="o10n[js.minify.fallback.minifier]" data-ns-change="js.minify.fallback" data-json-default="<?php print esc_attr(json_encode('jsmin')); ?>">
                        <option value="jsmin"<?php $selected('js.minify.fallback.minifier', 'jsmin'); ?>>JSMin (mcrclay)</option>
                        <!--option value="jshrink"<?php $selected('js.minify.fallback.minifier', 'jshrink'); ?>>JShrink</option-->
                        <option value="custom"<?php $selected('js.minify.fallback.minifier', 'custom'); ?>>Custom minifier (WordPress filter hook)</option>
                    </select> 
                    <p class="description">Choose a minifier that provides the best performance for your Javascript code.</p>

                    <div class="suboption" data-ns="js.minify.fallback"<?php $visible('js.minify.fallback', ($get('js.minify.fallback.minifier') === 'custom')); ?> data-ns-condition="js.minify.fallback.minifier==custom">
                        <p style="font-size:16px;line-height:18px;">The Custom Minifier option enables to use any Javascript minifier via the WordPress filter hook <code>o10n_js_custom_minify</code>. (<a href="javascript:void(0);" onclick="jQuery('#custom_minify_fallback_example').fadeToggle();">show example</a>)</p>
                    <div class="info_yellow" id="custom_minify_fallback_example" style="display:none;"><strong>Example:</strong> <pre class="clickselect" title="<?php print esc_attr('Click to select', 'optimization'); ?>" style="cursor:copy;padding: 10px;margin: 0 1px;margin-top:5px;font-size: 13px;">
        /* Custom Javascript minifier */
        add_filter('o10n_js_custom_minify', function ($JS) {

            // apply javascript optimization
            exec('/node /path/to/optimize-js.js /tmp/js-source.js');
            $minified = file_get_contents('/tmp/output.js');

            // alternative
            $minified = JSCompressor::minify($JS);

            return $minified;

        });</pre></div>
                    </div>

                    <div class="suboption">
                        <h5 class="h">&nbsp;Timeout</h5>
                        <input type="number" style="width:60px;" min="1" name="o10n[js.minify.fallback.timeout]" value="<?php $value('js.minify.fallback.timeout', 60); ?>" />
                        <p class="description">Enter a timeout in seconds for the API to respond before the fallback is used.</p>
                    </div>
                </div>
            </div>

            <div class="suboption" data-ns="js.minify"<?php $visible('js.minify'); ?>>
                <label><input type="checkbox" value="1" name="o10n[js.minify.filter.enabled]" data-json-ns="1"<?php $checked('js.minify.filter.enabled'); ?> /> Enable filter</label>
                <span data-ns="js.minify.filter"<?php $visible('js.minify.filter'); ?>>
                    <select name="o10n[js.minify.filter.type]" data-ns-change="js.minify.filter" data-json-default="<?php print esc_attr(json_encode('include')); ?>">
                        <option value="include"<?php $selected('js.minify.filter.type', 'include'); ?>>Include List</option>
                        <option value="exclude"<?php $selected('js.minify.filter.type', 'exclude'); ?>>Exclude List</option>
                    </select>
                </span>
            </div>

            <div data-ns="js.minify.filter"<?php $visible('js.minify.filter', ($get('js.minify.filter.type') === 'include')); ?> data-ns-condition="js.minify.filter.type==include">
                <h5 class="h">&nbsp;Minify Include List</h5>
                <textarea class="json-array-lines" name="o10n[js.minify.filter.include]" data-json-type="json-array-lines" ><?php $line_array('js.minify.filter.include'); ?></textarea>
                <p class="description">Enter (parts of) <code>&lt;script&gt;</code> elements to minify, e.g. <code>script.js</code> or <code>id="script"</code>. One match string per line.</p>
            </div>
            <div data-ns="js.minify.filter"<?php $visible('js.minify.filter', ($get('js.minify.filter.type') === 'exclude')); ?> data-ns-condition="js.minify.filter.type==exclude">
                <h5 class="h">&nbsp;Minify Exclude List</h5>
                <textarea class="json-array-lines" name="o10n[js.minify.filter.exclude]" data-json-type="json-array-lines"><?php $line_array('js.minify.filter.exclude'); ?></textarea>
                <p class="description">Enter (parts of) <code>&lt;script&gt;</code> elements to exclude from minification. One match string per line.</p>
            </div>

            <div data-ns="js.minify"<?php $visible('js.minify'); ?> class="suboption">
                <label><input type="checkbox" value="1" name="o10n[js.minify.comments.remove_important.enabled]" data-json-ns="1"<?php $checked('js.minify.comments.remove_important.enabled'); ?> /> Remove important <code>/*!</code> comments</label>
            </div>

        </td>
    </tr>
</table>


<div class="advanced-options" data-ns="js.minify" data-json-advanced="js.minify.closure-compiler-service"<?php $visible('js.minify', ($get('js.minify.minifier') === 'closure-compiler-service')); ?> data-ns-condition="js.minify.minifier==closure-compiler-service">>

    <table class="advanced-options-table widefat fixed striped">
        <colgroup><col style="width: 85px;"/><col style="width: 250px;"/><col /></colgroup>
        <thead class="first">
            <tr>
                <th class="toggle">
                    <a href="javascript:void(0);" class="advanced-toggle-all button button-small">Toggle All</a>
                </th>
                <th class="head">
                  Closure Compiler Options
                </th>
                <th>
                    <p class="poweredby">Powered by <a href="https://github.com/google/closure-compiler" target="_blank">Closure Compiler</a><span class="star">
                    <a class="github-button" data-manual="1" href="https://github.com/google/closure-compiler" data-icon="octicon-star" data-show-count="true" aria-label="Star google/closure-compiler on GitHub">Star</a></span>
                    </p>
                </th> 
            </tr>
        </thead>
        <tbody>
<?php
    $advanced_options('js.minify.closure-compiler-service.options');
?>
        </tbody>
    </table>
<br />
<?php
submit_button(__('Save'), 'primary large', 'is_submit', false);
?>
<br />
</div>

<table class="form-table">
    <tr valign="top" data-ns="js.minify"<?php $visible('js.minify');  ?>>
        <th scope="row">Concatenate</th>
        <td>
            <label><input type="checkbox" value="1" name="o10n[js.minify.concat.enabled]" data-json-ns="1"<?php $checked('js.minify.concat.enabled'); ?> /> Enabled</label>
            <p class="description">Merge scripts into a single file.</p>
            <p data-ns="js.minify.concat"<?php $visible('js.minify.concat'); ?>>
                <label><input type="checkbox" value="1" name="o10n[js.minify.concat.minify]"<?php $checked('js.minify.concat.minify'); ?> /> Use <code>Minify</code> for concatenation.</label>
            </p>
            <p data-ns="js.minify.concat"<?php $visible('js.minify.concat'); ?>>
                <label><input type="checkbox" value="1" name="o10n[js.minify.concat.trycatch]"<?php $checked('js.minify.concat.minify'); ?> /> Wrap scripts in <code>try {} catch(e) {}</code> before concatenation.</label>
            </p>
            <div class="suboption" data-ns="js.minify.concat"<?php $visible('js.minify.concat'); ?>>
                <label><input type="checkbox" value="1" name="o10n[js.minify.concat.filter.enabled]" data-json-ns="1"<?php $checked('js.minify.concat.filter.enabled'); ?> /> Enable group filter</label>
                <span data-ns="js.minify.concat.filter"<?php $visible('js.minify.concat.filter'); ?>>
                    <select name="o10n[js.minify.concat.filter.type]" data-ns-change="js.minify.concat.filter" data-json-default="<?php print esc_attr(json_encode('include')); ?>">
                        <option value="include"<?php $selected('js.minify.concat.filter.type', 'include'); ?>>Include by default</option>
                        <option value="exclude"<?php $selected('js.minify.concat.filter.type', 'exclude'); ?>>Exclude by default</option>
                    </select>
                </span> 
                <p class="description">The group filter enables to create bundles of concatenated scripts. This enables to bundle scripts that are shared between pages while creating separate bundles for the remaining scripts on a page.</p>
            </div>
        </td>
    </tr><tr valign="top" data-ns="js.minify.concat.filter"<?php $visible('js.minify.concat.filter'); ?>>
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">


            <h5 class="h">&nbsp;Concat Group Filter</h5>
            <div id="js-minify-concat-filter-config"><div class="loading-json-editor"><?php print __('Loading JSON editor...', 'optimization'); ?></div></div>
            <input type="hidden" class="json" name="o10n[js.minify.concat.filter.config]" data-json-type="json-array" data-json-editor-height="auto" data-json-editor-init="1" value="<?php print esc_attr($json('js.minify.concat.filter.config')); ?>" />
            <p class="description">Enter a JSON array with concat group config objects.  (<a href="javascript:void(0);" onclick="jQuery('#concat_group_example').fadeToggle();">show example</a>)</p>
            <div class="info_yellow" id="concat_group_example" style="display:none;"><strong>Example:</strong> <pre class="clickselect" title="<?php print esc_attr('Click to select', 'optimization'); ?>" style="cursor:copy;padding: 10px;margin: 0 1px;margin-top:5px;font-size: 13px;">{
    "match": ["some-script.js", {"string": "/jquery.*/", "regex":true}], 
    "group": {"title":"Group title", "key": "group-file-key", "id": "id-attr"}, 
    "minify": true, 
    "exclude": false
}</pre></div>
           
        </td>
    </tr>
    <tr valign="top" data-ns="js.minify.concat"<?php $visible('js.minify.concat');  ?>>
        <th scope="row">Merge Inline</th>
        <td>
            <label><input type="checkbox" value="1" name="o10n[js.minify.concat.inline.enabled]" data-json-ns="1"<?php $checked('js.minify.concat.inline.enabled'); ?>> Enabled</label>
            <p class="description">Extract inline <code>&lt;script&gt;</code> elements and include the javascript in the concatenated script.</p>
            <p data-ns="js.minify.concat.inline"<?php $visible('js.minify.concat.inline'); ?>>
                <label><input type="checkbox" value="1" name="o10n[js.minify.concat.inline.filter.enabled]" data-json-ns="1"<?php $checked('js.minify.concat.inline.filter.enabled'); ?> /> Enable filter</label>
                <span data-ns="js.minify.concat.inline.filter"<?php $visible('js.minify.concat.inline.filter'); ?>>
                    <select name="o10n[js.minify.concat.inline.filter.type]" data-ns-change="js.minify.concat.inline.filter" data-json-default="<?php print esc_attr(json_encode('include')); ?>">
                        <option value="include"<?php $selected('js.minify.concat.inline.filter.type', 'include'); ?>>Include List</option>
                        <option value="exclude"<?php $selected('js.minify.concat.inline.filter.type', 'exclude'); ?>>Exclude List</option>
                    </select>
                </span>
            </p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.minify.concat.inline.filter"<?php $visible('js.minify.concat.inline.filter', ($get('js.minify.concat.inline.filter.type') === 'include')); ?> data-ns-condition="js.minify.concat.inline.filter.type==include">
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;Inline Merge Include List</h5>
            <textarea class="json-array-lines" name="o10n[js.minify.concat.inline.filter.include]" data-json-type="json-array-lines" placeholder="Leave blank to minify all inline js..."><?php $line_array('js.minify.concat.inline.filter.include'); ?></textarea>
            <p class="description">Enter (parts of) inline <code>&lt;script&gt;</code> elements to concatenate, e.g. <code>background-color:white;</code> or <code>id="script"</code>. One match string per line.</p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.minify.concat.inline.filter"<?php $visible('js.minify.concat.inline.filter', ($get('js.minify.concat.inline.filter.type') === 'exclude')); ?> data-ns-condition="js.minify.concat.inline.filter.type==exclude">
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;Inline Merge Exclude List</h5>
            <textarea class="json-array-lines" name="o10n[js.minify.concat.inline.filter.exclude]" data-json-type="json-array-lines"><?php $line_array('js.minify.concat.inline.filter.exclude'); ?></textarea>
            <p class="description">Enter (parts of) inline <code>&lt;script&gt;</code> elements to exclude from concatenation. One match string per line.</p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.minify"<?php $visible('js.minify');  ?>>
        <th scope="row">Search &amp; Replace</th>
        <td>
            <div id="js-replace"><div class="loading-json-editor"><?php print __('Loading JSON editor...', 'optimization'); ?></div></div>
            <input type="hidden" id="js_search_replace_src" name="o10n[js.replace]" data-json-type="json-array" data-json-editor-height="auto" data-json-editor-init="1" value="<?php print esc_attr($json('js.replace')); ?>" />

            <p class="description">This option enables to replace strings in the javascript code <strong>before</strong> minification. Enter a JSON array with configuration objects <span class="dashicons dashicons-editor-help"></span>.</p>

            <div class="info_yellow"><strong>Example:</strong> <code id="js_search_replace_example" class="clickselect" data-example-text="show string" title="<?php print esc_attr('Click to select', 'optimization'); ?>" style="cursor:copy;">{"search":"string to match","replace":"newstring"}</code> (<a href="javascript:void(0);" data-example="js_search_replace_example" data-example-html="<?php print esc_attr(__('{"search":"|string to (match)|i","replace":"newstring $1","regex":true}', 'optimization')); ?>">show regular expression</a>)</div>
        </td>
    </tr>
</table>

<table class="form-table">
    <tr valign="top">
        <th scope="row">URL filter</th>
        <td>
            <label><input type="checkbox" value="1" name="o10n[js.url_filter.enabled]" data-json-ns="1"<?php $checked('js.url_filter.enabled'); ?> /> Enabled</label>
            <p class="description">Use this option to modify script URLs before processing. The filter can be used to remove a cache busting query string, to (selectively) add or remove a CDN or to delete a script from the HTML.</p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.url_filter"<?php $visible('js.url_filter'); ?>>
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;URL filter configuration</h5>
            <div id="js-url_filter-config"><div class="loading-json-editor"><?php print __('Loading JSON editor...', 'optimization'); ?></div></div>
            <input type="hidden" class="json" name="o10n[js.url_filter.config]" data-json-type="json-array" data-json-editor-compact="1" data-json-editor-init="1" value="<?php print esc_attr($json('js.url_filter.config')); ?>" />
            <p class="description">Enter a JSON array with objects. <code>url</code> is a string or regular expression to match a script URL, <code>ignore</code>, <code>delete</code> or <code>replace</code> control the filter.</p>
            <div class="info_yellow"><strong>Example:</strong> <code id="pre_url_example" data-example-text="show replace" class="clickselect" title="<?php print esc_attr('Click to select', 'optimization'); ?>" style="cursor:copy;">{"url":"/\/wp-content\/path\/([a-z]+)$/i","regex":true,"replace":"https://cdn.com/$1"}</code> (<a href="javascript:void(0);" data-example="pre_url_example" data-example-html=" <?php print esc_attr('{"url":"toolbar.","ignore":true}'); ?>">show ignore</a>)</div>
        </td>
    </tr>
        
</table>

<p class="suboption info_yellow"><strong><span class="dashicons dashicons-lightbulb"></span></strong> You can enable debug modus by adding <code>define('O10N_DEBUG', true);</code> to wp-config.php. The browser console will show details about javascript loading and a <a href="https://developer.mozilla.org/nl/docs/Web/API/Performance" target="_blank" rel="noopener">Performance API</a> result for each step of the loading and execution process.</p>

<hr />
<?php
    submit_button(__('Save'), 'primary large', 'is_submit', false);

// print form header
$this->form_end();

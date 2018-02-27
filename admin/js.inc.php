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
        <p class="poweredby">Powered by <a href="https://github.com/mrclay/jsmin-php" target="_blank">JSMin</a><span class="star">
                    <a class="github-button" data-manual="1" href="https://github.com/mrclay/jsmin-php" data-icon="octicon-star" data-show-count="true" aria-label="Star mrclay/jsmin-php on GitHub">Star</a></span>
                    </p>
            <label><input type="checkbox" value="1" name="o10n[js.minify.enabled]" data-json-ns="1"<?php $checked('js.minify.enabled'); ?> /> Enabled</label>
            <p class="description">Compress Javascript using <a href="https://github.com/mrclay/jsmin-php" target="_blank">PHP JSMin</a>.</p>

            <p data-ns="js.minify"<?php $visible('js.minify'); ?>>
                <label><input type="checkbox" value="1" name="o10n[js.minify.filter.enabled]" data-json-ns="1"<?php $checked('js.minify.filter.enabled'); ?> /> Enable filter</label>
                <span data-ns="js.minify.filter"<?php $visible('js.minify.filter'); ?>>
                    <select name="o10n[js.minify.filter.type]" data-ns-change="js.minify.filter" data-json-default="<?php print esc_attr(json_encode('include')); ?>">
                        <option value="include"<?php $selected('js.minify.filter.type', 'include'); ?>>Include List</option>
                        <option value="exclude"<?php $selected('js.minify.filter.type', 'exclude'); ?>>Exclude List</option>
                    </select>
                </span>
            </p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.minify.filter"<?php $visible('js.minify.filter', ($get('js.minify.filter.type') === 'include')); ?> data-ns-condition="js.minify.filter.type==include">
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;Minify Include List</h5>
            <textarea class="json-array-lines" name="o10n[js.minify.filter.include]" data-json-type="json-array-lines" ><?php $line_array('js.minify.filter.include'); ?></textarea>
            <p class="description">Enter (parts of) <code>&lt;script&gt;</code> elements to minify, e.g. <code>script.js</code> or <code>id="script"</code>. One match string per line.</p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.minify.filter"<?php $visible('js.minify.filter', ($get('js.minify.filter.type') === 'exclude')); ?> data-ns-condition="js.minify.filter.type==exclude">
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;Minify Exclude List</h5>
            <textarea class="json-array-lines" name="o10n[js.minify.filter.exclude]" data-json-type="json-array-lines"><?php $line_array('js.minify.filter.exclude'); ?></textarea>
            <p class="description">Enter (parts of) <code>&lt;script&gt;</code> elements to exclude from minification. One match string per line.</p>
        </td>
    </tr>

    <tr valign="top" data-ns="js.minify"<?php $visible('js.minify');  ?>>
        <th scope="row">Concatenate</th>
        <td>
            <label><input type="checkbox" value="1" name="o10n[js.minify.concat.enabled]" data-json-ns="1"<?php $checked('js.minify.concat.enabled'); ?> /> Enabled</label>
            <p class="description">Merge stylesheets into a single file.</p>
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
                <p class="description">The group filter enables creating multiple concat groups that are shared more efficiently during page navigation.</p>
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
            <p class="description">Extract inline <code>&lt;style&gt;</code> elements and include the CSS in the concatenated stylesheet.</p>
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
            <textarea class="json-array-lines" name="o10n[js.minify.concat.inline.filter.include]" data-json-type="json-array-lines" placeholder="Leave blank to minify all inline CSS..."><?php $line_array('js.minify.concat.inline.filter.include'); ?></textarea>
            <p class="description">Enter (parts of) inline <code>&lt;style&gt;</code> elements to concatenate, e.g. <code>background-color:white;</code> or <code>id="style"</code>. One match string per line.</p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.minify.concat.inline.filter"<?php $visible('js.minify.concat.inline.filter', ($get('js.minify.concat.inline.filter.type') === 'exclude')); ?> data-ns-condition="js.minify.concat.inline.filter.type==exclude">
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;Inline Merge Exclude List</h5>
            <textarea class="json-array-lines" name="o10n[js.minify.concat.inline.filter.exclude]" data-json-type="json-array-lines"><?php $line_array('js.minify.concat.inline.filter.exclude'); ?></textarea>
            <p class="description">Enter (parts of) inline <code>&lt;style&gt;</code> elements to exclude from concatenation. One match string per line.</p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.minify"<?php $visible('js.minify');  ?>>
        <th scope="row">Search &amp; Replace</th>
        <td>
            <div id="js_search_replace"><div class="loading-json-editor"><?php print __('Loading JSON editor...', 'optimization'); ?></div></div>
            <input type="hidden" id="js_search_replace_src" name="o10n[js.replace]" data-json-type="json-array" />

            <p class="description">This option enables to replace strings in the CSS <strong>before</strong> minification. Enter a JSON array with configuration objects <span class="dashicons dashicons-editor-help"></span>.</p>

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
            <p class="description">Enter a JSON array with objects. <code>url</code> is a string or regular expression to match a stylesheet URL, <code>ignore</code>, <code>delete</code> or <code>replace</code> control the filter.</p>
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

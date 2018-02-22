<?php
namespace O10n;

/**
 * Javascript delivery optimization admin template
 *
 * @package    optimization
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH') || !defined('O10N_ADMIN')) {
    exit;
}

// print form header
$this->form_start(__('Javascript Delivery Optimization', 'optimization'), 'js');

?>

<table class="form-table">
    <tr valign="top" >
        <th scope="row">Async Loading <a href="https://developers.google.com/speed/docs/insights/OptimizeCSSDelivery?hl=" target="_blank" title="Recommendations by Google"><span class="dashicons dashicons-editor-help"></span></a></th>
        <td>
            <p class="poweredby">Powered by <a href="https://github.com/walmartlabs/little-loader" target="_blank">little-loader</a><span class="star"><a class="github-button" data-manual="1" href="https://github.com/walmartlabs/little-loader" data-icon="octicon-star" data-show-count="true" aria-label="Star walmartlabs/little-loader on GitHub">Star</a></span></p> 
            <label><input type="checkbox" name="o10n[js.async.enabled]" data-json-ns="1" value="1"<?php $checked('js.async.enabled'); ?> /> Enabled</label>
            <p class="description">When enabled, scripts are loaded asynchronously via <a href="https://formidable.com/blog/2016/01/07/the-only-correct-script-loader-ever-made/" target="_blank">little-loader</a> from Walmart Labs.</p>

            <div style="margin-top:5px;" data-ns="js.async"<?php $visible('js.async');  ?>>
                <label><input type="checkbox" name="o10n[js.async.rel_preload]" value="1"<?php $checked('js.async.rel_preload'); ?> /> Load scripts via <code>&lt;link rel="preload" as="script"&gt;</code> (<a href="https://www.w3.org/TR/preload/" target="_blank">W3C Spec</a>) to enable responsive script loading and async script execution.</label>
            </div>

            <div class="suboption" style="margin-top:5px;" data-ns="js.async"<?php $visible('js.async');  ?>>
                <label><input type="checkbox" value="1" name="o10n[js.async.filter.enabled]" data-json-ns="1"<?php $checked('js.async.filter.enabled'); ?> /> Enable config filter</label>
                <span data-ns="js.async.filter"<?php $visible('js.async.filter'); ?>>
                    <select name="o10n[js.async.filter.type]" data-ns-change="js.async.filter" data-json-default="<?php print esc_attr(json_encode('include')); ?>">
                        <option value="include"<?php $selected('js.async.filter.type', 'include'); ?>>Include by default</option>
                        <option value="exclude"<?php $selected('js.async.filter.type', 'exclude'); ?>>Exclude by default</option>
                    </select>
                </span>
                <p class="description">The config filter enables to include or exclude scripts from async loading or to apply custom async load configuration to individual files or concat groups.</p>
            </div>
        </td>
    </tr>
    <tr valign="top" data-ns="js.async.filter"<?php $visible('js.async.filter');  ?>>
        <th scope="row">&nbsp;</th> 
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;Async Config Filter</h5>
            <div id="js-async-filter-config"><div class="loading-json-editor"><?php print __('Loading JSON editor...', 'optimization'); ?></div></div>
            <input type="hidden" class="json" name="o10n[js.async.filter.config]" data-json-type="json-array" data-json-editor-height="auto" data-json-editor-init="1" value="<?php print esc_attr($json('js.async.filter.config')); ?>" />
            <p class="description">Enter a JSON array with objects. (<a href="javascript:void(0);" onclick="jQuery('#concat_group_example').fadeToggle();">show example</a>)</p>
            <div class="info_yellow" id="concat_group_example" style="display:none;"><strong>Example:</strong> <pre class="clickselect" title="<?php print esc_attr('Click to select', 'optimization'); ?>" style="cursor:copy;padding: 10px;margin: 0 1px;margin-top:5px;font-size: 13px;">{
    "match": "/concat-group-(x|y)/",
    "match_concat": true,
    "regex": true,
    "async": true,
    "rel_preload": true,
    "noscript": false,
    "load_position": "timing",
    "load_timing": {
        "type": "media",
        "media": "screen and (max-width: 700px)"
    },
    "exec_timing": {
        "type": "requestAnimationFrame",
        "frame": 1
    },
    "localStorage": {
        "max_size": 10000,
        "update_interval": 3600,
        "expire": 86400,
        "head_update": true
    }
}</pre></div>
        </td>
    </tr>
    <tr valign="top" data-ns="js.async"<?php $visible('js.async');  ?>>
        <th scope="row">Load Position</th>
        <td>
            <select name="o10n[js.async.load_position]" data-ns-change="js.async">
                <option value="header"<?php $selected('js.async.load_position', 'header'); ?>>Header</option>
                <option value="timing"<?php $selected('js.async.load_position', 'timing'); ?>>Timed</option>
            </select>
            <p class="description">Select the position of the HTML document where the downloading of scripts will start.</p>


            <div class="suboption" data-ns="js.async""<?php $visible('js.async', ($get('js.async.load_position') === 'timing'));  ?> data-ns-condition="js.async.load_position==timing">
                <h5 class="h">&nbsp;Load Timing Method</h5>
                <select name="o10n[js.async.load_timing.type]" data-ns-change="js.async" data-json-default="<?php print esc_attr(json_encode('domReady')); ?>">
                    <option value="domReady"<?php $selected('js.async.load_timing.type', 'domReady'); ?>>domReady</option>
                    <option value="requestAnimationFrame"<?php $selected('js.async.load_timing.type', 'requestAnimationFrame'); ?>>requestAnimationFrame (on paint)</option>
                    <option value="requestIdleCallback"<?php $selected('js.async.load_timing.type', 'requestIdleCallback'); ?>>requestIdleCallback</option>
                    <option value="inview"<?php $selected('js.async.load_timing.type', 'inview'); ?>>element in view (on scroll)</option>
                    <option value="media"<?php $selected('js.async.load_timing.type', 'media'); ?>>responsive (Media Query)</option>
                </select>
                <p class="description">Select the timing method for timed script loading. This option is also available per individual script in the filter config.</p>

                <div class="suboption" data-ns="js.async"<?php $visible('js.async', ($get('js.async.load_timing.type') === 'requestAnimationFrame'));  ?> data-ns-condition="js.async.load_timing.type==requestAnimationFrame">
                    <h5 class="h">&nbsp;Frame number</h5>
                    <input type="number" style="width:60px;" min="1" name="o10n[js.async.load_timing.frame]" value="<?php $value('js.async.load_timing.frame'); ?>" />
                    <p class="description">Optionally, select the frame number to start loading scripts. <code>requestAnimationFrame</code> will be called this many times before the scripts are loaded.</p>
                </div>

                <div class="suboption" data-ns="js.async"<?php $visible('js.async', ($get('js.async.load_timing.type') === 'requestIdleCallback'));  ?> data-ns-condition="js.async.load_timing.type==requestIdleCallback">

                    <h5 class="h">&nbsp;Timeout</h5>
                    <input type="number" style="width:60px;" min="1" name="o10n[js.async.load_timing.timeout]" value="<?php $value('js.async.load_timing.timeout'); ?>" />
                    <p class="description">Enter a timeout after which the script should be forced to download.</p>
                
                    <div class="suboption">
                        <h5 class="h">&nbsp;setTimeout fallback</h5>
                        <input type="number" style="width:60px;" min="1" name="o10n[js.async.load_timing.setTimeout]" value="<?php $value('js.async.load_timing.setTimeout'); ?>" />
                        <p class="description">Optionally, enter a timeout in milliseconds for browsers that don't support requestIdleCallback. Leave blank to disable timed script loading for those browsers.</p>
                    </div>
                </div>

                <div class="suboption" data-ns="js.async"<?php $visible('js.async', ($get('js.async.load_timing.type') === 'inview'));  ?> data-ns-condition="js.async.load_timing.type==inview">
                    <p class="poweredby">Powered by <a href="https://github.com/camwiegert/in-view" target="_blank">in-view.js</a><span class="star"><a class="github-button" data-manual="1" href="https://github.com/camwiegert/in-view" data-icon="octicon-star" data-show-count="true" aria-label="Star camwiegert/in-view on GitHub">Star</a></span></p>
                    <h5 class="h">&nbsp;CSS selector</h5>
                    <input type="text" name="o10n[js.async.load_timing.selector]" value="<?php $value('js.async.load_timing.selector'); ?>" />
                    <p class="description">Enter the <a href="https://developer.mozilla.org/en-US/docs/Web/API/Document/querySelector" target="_blank">CSS selector</a> of the element to watch.</p>
                    
                    <div class="suboption">
                        <h5 class="h">&nbsp;Offset</h5>
                        <input type="number" style="width:60px;" name="o10n[js.async.load_timing.offset]" value="<?php $value('js.async.load_timing.offset'); ?>" />
                        <p class="description">Optionally, enter an offset from the edge of the element to start script loading.</p>
                    </div>
                </div>

                <div class="suboption" data-ns="js.async"<?php $visible('js.async', ($get('js.async.load_timing.type') === 'media'));  ?> data-ns-condition="js.async.load_timing.type==media">
                    <h5 class="h">&nbsp;Media Query</h5>
                    <input type="text" name="o10n[js.async.load_timing.media]" value="<?php $value('js.async.load_timing.media'); ?>" style="width:400px;max-width:100%;" />
                    <p class="description">Enter a <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries" target="_blank">Media Query</a> for conditional script loading, e.g. omit scripts on mobile devices or load a script on orientation change.</p>
                </div>
            </div>

        </td>
     </tr>
    <tr valign="top" data-ns="js.async"<?php $visible('js.async');  ?>>
        <th scope="row">Timed Exec</th>
        <td>
            <label><input type="checkbox" name="o10n[js.async.exec_timing.enabled]" data-json-ns="1" value="1"<?php $checked('js.async.exec_timing.enabled'); ?> /> Enabled</label>
            <p class="description">When enabled, scripts are executed asynchronously using a timing method.</p>
        </td>
    </tr>

    <tr valign="top" data-ns="js.async.exec_timing"<?php $visible('js.async.exec_timing');  ?>>
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            
        <h5 class="h">&nbsp;Async Exec Timing Method</h5>
            <select name="o10n[js.async.exec_timing.type]" data-ns-change="js.async" data-json-default="<?php print esc_attr(json_encode('domReady')); ?>">
                <option value="domReady"<?php $selected('js.async.exec_timing.type', 'domReady'); ?>>domReady</option>
                <option value="requestAnimationFrame"<?php $selected('js.async.exec_timing.type', 'requestAnimationFrame'); ?>>requestAnimationFrame (on paint)</option>
                <option value="requestIdleCallback"<?php $selected('js.async.exec_timing.type', 'requestIdleCallback'); ?>>requestIdleCallback</option>
                <option value="inview"<?php $selected('js.async.exec_timing.type', 'inview'); ?>>element in view (on scroll)</option>
                <option value="media"<?php $selected('js.async.exec_timing.type', 'media'); ?>>responsive (Media Query)</option>
            </select>
            <p class="description">Select the timing method for timed script execution. This option is also available per individual script in the filter config.</p>

            <div class="suboption" data-ns="js.async"<?php $visible('js.async', ($get('js.async.exec_timing.type') === 'requestAnimationFrame'));  ?> data-ns-condition="js.async.exec_timing.type==requestAnimationFrame">
                <h5 class="h">&nbsp;Frame number</h5>
                <input type="number" style="width:60px;" min="1" name="o10n[js.async.exec_timing.frame]" value="<?php $value('js.async.exec_timing.frame'); ?>" />
                <p class="description">Optionally, select the frame number to start script execution. <code>requestAnimationFrame</code> will be called this many times before the scripts are executed.</p>
            </div>

            <div class="suboption" data-ns="js.async"<?php $visible('js.async', ($get('js.async.exec_timing.type') === 'requestIdleCallback'));  ?> data-ns-condition="js.async.exec_timing.type==requestIdleCallback">

                <h5 class="h">&nbsp;Timeout</h5>
                <input type="number" style="width:60px;" min="1" name="o10n[js.async.exec_timing.timeout]" value="<?php $value('js.async.exec_timing.timeout'); ?>" />
                <p class="description">Enter a timeout after which the script should be forced to execute.</p>
            
                <div class="suboption">
                    <h5 class="h">&nbsp;setTimeout fallback</h5>
                    <input type="number" style="width:60px;" min="1" name="o10n[js.async.exec_timing.setTimeout]" value="<?php $value('js.async.exec_timing.setTimeout'); ?>" />
                    <p class="description">Optionally, enter a timeout in milliseconds for browsers that don't support requestIdleCallback. Leave blank to disable timed script execution for those browsers.</p>
                </div>
            </div>

            <div class="suboption" data-ns="js.async"<?php $visible('js.async', ($get('js.async.exec_timing.type') === 'inview'));  ?> data-ns-condition="js.async.exec_timing.type==inview">
                <p class="poweredby">Powered by <a href="https://github.com/camwiegert/in-view" target="_blank">in-view.js</a><span class="star"><a class="github-button" data-manual="1" href="https://github.com/camwiegert/in-view" data-icon="octicon-star" data-show-count="true" aria-label="Star camwiegert/in-view on GitHub">Star</a></span></p>
                <h5 class="h">&nbsp;CSS selector</h5>
                <input type="text" name="o10n[js.async.exec_timing.selector]" value="<?php $value('js.async.exec_timing.selector'); ?>" />
                <p class="description">Enter the <a href="https://developer.mozilla.org/en-US/docs/Web/API/Document/querySelector" target="_blank">CSS selector</a> of the element to watch.</p>
                
                <div class="suboption">
                    <h5 class="h">&nbsp;Offset</h5>
                    <input type="number" style="width:60px;" name="o10n[js.async.exec_timing.offset]" value="<?php $value('js.async.exec_timing.offset'); ?>" />
                    <p class="description">Optionally, enter an offset from the edge of the element to start script execution.</p>
                </div>
            </div>

            <div class="suboption" data-ns="js.async"<?php $visible('js.async', ($get('js.async.exec_timing.type') === 'media'));  ?> data-ns-condition="js.async.exec_timing.type==media">
                <h5 class="h">&nbsp;Media Query</h5>
                <input type="text" name="o10n[js.async.exec_timing.media]" value="<?php $value('js.async.exec_timing.media'); ?>" style="width:400px;max-width:100%;" />
                <p class="description">Enter a <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries" target="_blank">Media Query</a> for conditional script execution, e.g. execute a script on mobile device orientation change.</p>
            </div>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            Responsive Exec
        </th>
        <td>
            <label><input type="checkbox" name="o10n[js.async.responsive]" value="1"<?php $checked('js.async.responsive'); ?>> Enabled</label>
            <p class="description">When enabled, inline scripts such as Facebook like and Twitter follow buttons can be loaded when scrolled into view or based on a <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries" target="_blank">Media Query</a> by adding the attribute <code>data-exec="inview[:offset]"</code> or <code>data-exec="mediaQuery"</code>.</p>
        </td>
    </tr>
     <!--tr valign="top" data-ns="js.async"<?php $visible('js.async');  ?>>
        <th scope="row">
            Abide Dependencies
        </th>
        <td>
            <label><input type="checkbox" name="o10n[js.async.abide]" value="1"<?php $checked('js.async.abide'); ?>> Enabled</label>
            <p class="description">When enabled, scripts will load in sequential order abiding the WordPress dependency configuration defined by <a href="https://developer.wordpress.org/reference/functions/wp_enqueue_script/" target="_blank">wp_enqueue_script()</a>.</p>
        </td>
    </tr-->
     <tr valign="top" data-ns="js.async"<?php $visible('js.async');  ?>>
        <th scope="row">
            jQuery Stub
        </th>
        <td>
            <label><input type="checkbox" name="o10n[js.async.jQuery_stub]" value="1"<?php $checked('js.async.jQuery_stub'); ?>> Enabled</label>
            <p class="description">When enabled, a queue captures basic jQuery functionality such as <code>jQuery(function($){ ... });</code> and <code>$(document).bind('ready')</code> in inline scripts. This feature enables to load jQuery async.</p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.async"<?php $visible('js.async'); ?>>
        <td colspan="2" style="padding:0px;">
<?php
submit_button(__('Save'), 'primary large', 'is_submit', false);
?>
<br />
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">HTTP/2 Server Push</th>
        <td>
        <?php if (!$module_loaded('http2')) {
    ?>
<p class="description">Install the <a href="<?php print esc_url(add_query_arg(array('s' => 'o10n', 'tab' => 'search', 'type' => 'author'), admin_url('plugin-install.php'))); ?>">HTTP/2 Optimization</a> plugin to use this feature.</p>
<?php
} else {
        ?>
            <label><input type="checkbox" name="o10n[js.http2_push.enabled]" data-json-ns="1" value="1"<?php $checked('js.http2_push.enabled'); ?> /> Enabled</label>
            <p class="description">When enabled, scripts are pushed using <a href="https://developers.google.com/web/fundamentals/performance/http2/#server_push" target="_blank">HTTP/2 Server Push</a>.</p>

            <div data-ns="js.http2_push"<?php $visible('js.http2_push'); ?>>

                <?php
                    if (!$this->env->is_ssl()) {
                        print '<div class="warning_red">HTTP/2 Server Push requires SSL</div>';
                    } elseif (!$this->options->bool('http2.push.enabled')) {
                        print '<div class="warning_red">HTTP/2 Server Push is disabled in <a href="'.add_query_arg(array( 'page' => 'o10n-http2', 'tab' => 'push' ), admin_url('admin.php')).'">HTTP/2 Server Push Settings</a></div>';
                    } ?>
                <label><input type="checkbox" value="1" name="o10n[js.http2_push.filter.enabled]" data-json-ns="1"<?php $checked('js.http2_push.filter.enabled'); ?> /> Enable filter</label>
                <span data-ns="js.http2_push.filter"<?php $visible('js.http2_push.filter'); ?>>
                    <select name="o10n[js.http2_push.filter.type]" data-ns-change="js.http2_push.filter" data-json-default="<?php print esc_attr(json_encode('include')); ?>">
                        <option value="include"<?php $selected('js.http2_push.filter.type', 'include'); ?>>Include List</option>
                        <option value="exclude"<?php $selected('js.http2_push.filter.type', 'exclude'); ?>>Exclude List</option>
                    </select>
                </span>
            </div>
<?php
    }
?>
        </td>
    </tr>
    <tr valign="top" data-ns="js.http2_push.filter"<?php $visible('js.http2_push.filter', ($get('js.http2_push.filter.type') === 'include'));  ?> data-ns-condition="js.http2_push.filter.type==include">
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;HTTP/2 Server Push Include List</h5>
            <textarea class="json-array-lines" name="o10n[js.http2_push.filter.include]" data-json-type="json-array-lines" placeholder="Leave blank to push all scripts..."><?php $line_array('js.http2_push.filter.include'); ?></textarea>
            <p class="description">Enter (parts of) <code>&lt;script&gt;</code> elements to push, e.g. <code>jquery.js</code> or <code>id="script"</code>. One match string per line.</p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.http2_push.filter"<?php $visible('js.http2_push.filter', ($get('js.http2_push.filter.type') === 'exclude'));  ?> data-ns-condition="js.http2_push.filter.type==exclude">
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;HTTP/2 Server Push Exclude List</h5>
            <textarea class="json-array-lines" name="o10n[js.http2_push.filter.exclude]" data-json-type="json-array-lines"><?php $line_array('js.http2_push.filter.exclude'); ?></textarea>
            <p class="description">Enter (parts of) <code>&lt;script&gt;</code> elements to exclude from being pushed. One match string per line.</p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.async"<?php $visible('js.async'); ?>>
        <th scope="row">LocalStorage Cache</th>
        <td>
            <label><input type="checkbox" name="o10n[js.async.localStorage.enabled]" value="1" data-json-ns="1"<?php $checked('js.async.localStorage.enabled'); ?> /> Enabled</label>
            <p class="description">When enabled, scripts are cached using <a href="https://developer.mozilla.org/docs/Web/API/Window/localStorage" target="_blank">localStorage</a>, a technique that is <a href="https://addyosmani.com/basket.js/" target="_blank">used by Google</a> to improve performance on mobile devices.</p>
            <p class="info_yellow" data-ns="js.async.rel_preload"<?php $visible('js.async.rel_preload'); ?>>
                When using <code>rel="preload" as="script"</code> scripts may unnessarily be preloaded while being loaded from localStorage.
            </p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.async.localStorage"<?php $visible('js.async.localStorage');  ?>>
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;Maximum script size</h5>
            <input type="number" size="20" style="width:100px;" name="o10n[js.async.localStorage.max_size]" min="1" placeholder="No limit..." value="<?php $value('js.async.localStorage.max_size'); ?>" /> 
            <p class="description">Enter a maximum file size in bytes to store in cache. LocalStorage has a total limit of 5-10MB.</p>
        </td>
    </tr>
<tr valign="top" data-ns="js.async.localStorage"<?php $visible('js.async.localStorage');  ?>>
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;Expire time</h5>
            <input type="number" size="20" style="width:100px;" name="o10n[js.async.localStorage.expire]" min="1" placeholder="86400" value="<?php $value('js.async.localStorage.expire'); ?>" />
            <p class="description">Enter a expire time in seconds.</p>
            
        </td>
    </tr>
<tr valign="top" data-ns="js.async.localStorage"<?php $visible('js.async.localStorage');  ?>>
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;Update interval</h5>
            <input type="number" size="20" style="width:100px;" name="o10n[js.async.localStorage.update_interval]" min="0" placeholder="Always..." value="<?php $value('js.async.localStorage.update_interval'); ?>" />
            <p class="description">Enter a interval in seconds to update the cache in the background.</p>
            
        </td>
    </tr>
    <tr valign="top" data-ns="js.async.localStorage"<?php $visible('js.async.localStorage');  ?>>
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <label><input type="checkbox" name="o10n[js.async.localStorage.head_update]" value="1"<?php $checked('js.async.localStorage.head_update'); ?> /> HTTP HEAD request based update</label>
            <p class="description">Use a HTTP HEAD request and <code>etag</code> and/or <code>last-modified</code> header verification to update the cache.</p>
            
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">Proxy</th>
        <td>
            <label><input type="checkbox" name="o10n[js.proxy.enabled]" value="1" data-json-ns="1"<?php $checked('js.proxy.enabled'); ?> /> Enabled</label>
            <p class="description">Proxy external scripts to pass the <a href="https://developers.google.com/speed/docs/insights/LeverageBrowserCaching?hl=" target="_blank">Leverage browser caching</a> rule from Google PageSpeed Insights.</p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.proxy"<?php $visible('js.proxy');  ?>>
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;Proxy List</h5>
            <textarea class="json-array-lines" name="o10n[js.proxy.include]" data-json-type="json-array-lines" placeholder="Leave blank to proxy all external scripts..."><?php $line_array('js.proxy.include'); ?></textarea>
            <p class="description">Enter (parts of) script URI's to proxy, e.g. <code>bootstrap.min.css</code>. One match string per line.</p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.proxy"<?php $visible('js.proxy');  ?>>
        <th scope="row">Proxy Capture</th>
        <td>
            <label><input type="checkbox" value="1" name="o10n[js.proxy.capture.enabled]" data-json-ns="1"<?php $checked('js.proxy.capture.enabled'); ?> /> Capture script-injected scripts</label>
            <p class="description">When enabled, dynamically via javascript inserted scripts are captured and processed by the proxy.</p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.proxy.capture"<?php $visible('js.proxy.capture');  ?>>
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;Proxy Capture List</h5>
            <div id="js-proxy-capture-list"><div class="loading-json-editor"><?php print __('Loading JSON editor...', 'optimization'); ?></div></div>
            <input type="hidden" class="json" name="o10n[js.proxy.capture.list]" data-json-type="json-array" data-json-editor-compact="1" data-json-editor-init="1" value="<?php print esc_attr($json('js.proxy.capture.list')); ?>" />
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">CDN</th>
        <td>
            <label><input type="checkbox" name="o10n[js.cdn.enabled]" value="1" data-json-ns="1"<?php $checked('js.cdn.enabled'); ?> /> Enabled</label>
            <p class="description">When enabled, scripts are loaded via a Content Delivery Network (CDN).</p>

            <div data-ns="js.cdn"<?php $visible('js.cdn'); ?>>
                <p data-ns="js.http2_push"<?php $visible('js.http2_push'); ?>>
                    <label><input type="checkbox" name="o10n[js.cdn.http2_push]" value="1"<?php $checked('js.cdn.http2_push'); ?> /> Apply CDN to HTTP/2 pushed scripts. This will add <code>crossorigin;</code> to the HTTP/2 push header.</label>
                </p>
            </div>
        </td>
    </tr>
    <tr valign="top" data-ns="js.cdn"<?php $visible('js.cdn');  ?>>
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;CDN URL</h5>
            <input type="url" name="o10n[js.cdn.url]" value="<?php $value('js.cdn.url'); ?>" style="width:500px;max-width:100%;" placeholder="https://cdn.yourdomain.com/" />
            <p class="description">Enter a CDN URL for scripts, e.g. <code>https://js.domain.com/</code></p>
        </td>
    </tr>
    <tr valign="top" data-ns="js.cdn"<?php $visible('js.cdn');  ?>>
        <th scope="row">&nbsp;</th>
        <td>
            <h5 class="h">&nbsp;CDN Mask</h5>
            <input type="text" name="o10n[js.cdn.mask]" value="<?php $value('js.cdn.mask'); ?>" style="width:500px;max-width:100%;" placeholder="/" />
            <p class="description">Optionally, enter a CDN mask to apply to the script path, e.g. <code>/wp-content/cache/o10n/js/</code> to access scripts from the root of the CDN domain. The CDN mask enables to shorten CDN based URLs.</p>
        </td>
    </tr>
</table>
<hr />
<?php
    submit_button(__('Save'), 'primary large', 'is_submit', false);

// print form header
$this->form_end();

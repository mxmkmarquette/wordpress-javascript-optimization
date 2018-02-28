<?php
namespace O10n;

/**
 * Javascript editor admin template
 *
 * @package    o10n
 * @subpackage o10n/admin
 * @author     Optimization.Team <info@o10n.team>
 */
if (!defined('ABSPATH') || !defined('O10N_ADMIN')) {
    exit;
}

// get user
$user = wp_get_current_user();

// custom default CSS LINT options
$eslint_options = get_user_meta($user->ID, 'o10n_eslint', true);

// auto eslint on changes
$eslint_auto = get_user_meta($user->ID, 'o10n_eslint_auto', true);

// editor theme
$editor_theme = get_user_meta($user->ID, 'o10n_editor_theme', true);
if (!$editor_theme) {
    $editor_theme = 'default';
} else {
    $editor_theme = str_replace('.css', '', $editor_theme);
}

// active script
$active_script = $view->active_script();

?>
<div class="wrap">

    <table style="width:100%;margin-top:-35px">
        <tr>
        <td valign="bottom">
            <select id="editor_file_select" data-type="js" class="editor_file_select" placeholder="Search a .js script relative to the active theme or absolute to WordPress ABSPATH..." data-ext=".js"><option><?php print ($active_script) ? $active_script['filepath'] : __('Loading...', 'o10n'); ?></option></select>
        </td>
        <td width="100" align="center"><a href="http://codemirror.net/" target="_blank" style="color:#d30707;font-weight:bold;
    letter-spacing: .5px;text-decoration:none;font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif;">
            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" width="32" height="30" align="absmiddle" class="codemirror-logo">
            CodeMirror</a>
            <div style="height:20px;margin-bottom:5px;" class="star"><a class="github-button" data-manual="1" href="https://github.com/codemirror/CodeMirror" data-icon="octicon-star" data-show-count="true" aria-label="Star codemirror/CodeMirror on GitHub">Star</a></div>
        </td>
    </tr>
    </table>
    <div class="clearer"></div>
    <input type="hidden" id="ajax_nonce" value="<?php print esc_attr(wp_create_nonce('o10n')); ?>" />

    <div class="editor_container">
        <div>
            <textarea cols="70" rows="25" id="codemirror_editor" disabled="true" style="display:none;"><?php if ($active_script) {
    print $active_script['text'];
} ?></textarea>
            <div class="loading_editor CodeMirror cm-s-<?php print $editor_theme; ?>"><pre class=" CodeMirror-line " role="presentation"><span class="cm-comment"><?php print __('Loading...', 'o10n'); ?></span></pre></div>
        </div>

        <p class="submit">
            <span style="float:right;">

                <button type="button" class="button editor-undo" style="display:none;" title="<?php esc_attr(__('Undo')); ?>"><span class="dashicons dashicons-undo"></span></button>
                <button type="button" class="button editor-redo" style="display:none;" title="<?php esc_attr(__('Redo')); ?>"><span class="dashicons dashicons-redo"></span></button>
                <button type="button" class="button editor-reload-file" style="display:none;"><?php print __('Reload File', 'o10n'); ?></button>
                <button type="button" class="button editor-delete-file" title="<?php print esc_attr(__('Delete File')); ?>"><span class="dashicons dashicons-dismiss"></span></button>
            </span>
            <button type="button" class="button button-primary editor-save-file"><?php print __('Update File'); ?></button>
            <button type="button" class="button js_beautify_start">Beautify</button>
            <button type="button" class="button js_minify_start">UglifyJS</button>
            <button type="button" class="button eslint_start" data-scroll-results="0">ESLint</button>
            <span class="spinner"></span>
            <span class="status"></span>
        </p>
    </div>
    <br class="clear" />
</div>

<div id="post-body" class="metabox-holder">
    <div id="post-body-content">
        <div class="postbox">
            <div class="inside">

                <h3 style="margin-bottom:0px;" id="eslint"><span class="eslint-logo"><span></span>ESLint</span></h3>

                <p class="description">Verify the quality and performance of the Javascript code using <a href="https://eslint.org/" target="_blank">ESLint</a>.</p>

                <div class="advanced-options" data-json-advanced="custom">
                    <table class="advanced-options-table widefat fixed striped">
                        <col style="width: 250px;"/><col />
                        <thead>
                            <tr>
                                <th class="singlehead">
                                   Options <a href="https://eslint.org/docs/user-guide/configuring" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>
                                </th>
                                <th>
                                    <p class="poweredby">Powered by <a href="https://github.com/eslint/eslint" target="_blank">ESLint</a><span class="star"><a class="github-button" data-manual="1" href="https://github.com/eslint/eslint" data-icon="octicon-star" data-show-count="true" aria-label="Star eslint/eslint on GitHub">Star</a></span></span>
                                    </p>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="2" class="json-no-padding">
                                <div id="eslint_options"><div class="loading-json-editor"><?php print __('Loading JSON editor...', 'o10n'); ?></div></div>
                            </td></tr>
                            <tr style="background-color:inherit;">
                <td colspan="2">
                    <label><input type="checkbox" name="eslint_auto" name="o10n[auto]" value="1" <?php print(($eslint_auto) ? ' checked' : ''); ?> /> Auto-lint on changes.</label>
                </td>
            </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <button type="button" class="button" id="eslint_save">Save settings</button>
                        <button type="button" class="button eslint_start">Start ESLint</button>
                        <span class="spinner"></span>
                        <span class="status"></span>
                    </p>

                    <p id="eslint_status" style="display:none;"></p>
                    <table class="advanced-options-table widefat fixed striped" id="eslint_results" style="display:none;">
                        <colgroup>
                        <col style="width: 40px;"/>
                        <col style="width: 60px;"/>
                        <col style="width: 150px;"/>
                        <col />
                        <col style="width: 100px;"/>
                        <col style="width: 80px;"/>
                        </colgroup>
                        <thead>
                            <tr>
                                <th colspan="6" class="singlehead">
                                   Javascript Issues
                                </th>
                            </tr>
                        </thead>
                        <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th>Line</th>
                                <th>ID</th>
                                <th>Description</th>
                                <th>Node Type</th>
                                <th>Severity</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div> 

                <h3 style="margin-top:2em;margin-bottom:0px;" id="beautify">Javascript Beautify</h3>

                <p class="description">Beautify the Javascript using a browser-build of <a href="https://github.com/mishoo/UglifyJS2" target="_blank">UglifyJS 3</a>.</p>

                <div class="advanced-options" data-json-advanced="custom">
                    <table class="advanced-options-table widefat fixed striped">
                        <col style="width: 250px;"/><col />
                        <thead>
                            <tr>
                                <th class="singlehead">
                                   Options <a href="https://github.com/mishoo/UglifyJS2#formatting-options" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>
                                </th>
                                <th>
                                    <p class="poweredby">Powered by <a href="https://github.com/mishoo/UglifyJS2" target="_blank">UglifyJS 3</a><span class="star"><a class="github-button" data-manual="1" href="https://github.com/mishoo/UglifyJS2" data-icon="octicon-star" data-show-count="true" aria-label="Star mishoo/UglifyJS2 on GitHub">Star</a></span></span>
                                    </p>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="2" class="json-no-padding">
                                <div id="js_beautify_options"><div class="loading-json-editor"><?php print __('Loading JSON editor...', 'o10n'); ?></div></div>
                            </td></tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <button type="button" class="button" id="js_beautify_save">Save settings</button>
                        <button type="button" class="button js_beautify_start">Start Beautify</button>
                        <span class="spinner"></span>
                        <span class="status"></span>
                    </p>
                </div>


                <h3 style="margin-top:2em;margin-bottom:0px;" id="beautify">Javascript Optimization</h3>

                <p class="description">Minify and optimize the Javascript using a browser-build of <a href="https://github.com/mishoo/UglifyJS2" target="_blank">UglifyJS 3</a></p>

                <div class="advanced-options" data-json-advanced="custom">
                    <table class="advanced-options-table widefat fixed striped">
                        <col style="width: 250px;"/><col />
                        <thead>
                            <tr>
                                <th class="singlehead">
                                   UglifyJS 3 Options <a href="https://github.com/mishoo/UglifyJS2" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>
                                </th>
                                <th>
                                    <p class="poweredby">Powered by <a href="https://github.com/mishoo/UglifyJS2" target="_blank">UglifyJS 3</a><span class="star"><a class="github-button" data-manual="1" href="https://github.com/mishoo/UglifyJS2" data-icon="octicon-star" data-show-count="true" aria-label="Star mishoo/UglifyJS2 on GitHub">Star</a></span></span>
                                    </p>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="2" class="json-no-padding">
                                <div id="js_minify_options"><div class="loading-json-editor"><?php print __('Loading JSON editor...', 'o10n'); ?></div></div>
                            </td></tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <button type="button" class="button" id="js_minify_save">Save settings</button>
                        <button type="button" class="button js_minify_start">Start UglifyJS 3</button>
                        <span class="spinner"></span>
                        <span class="status"></span>
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>
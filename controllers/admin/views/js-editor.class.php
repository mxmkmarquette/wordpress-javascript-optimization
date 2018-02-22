<?php
namespace O10n;

/**
 * Javascript Editor Admin View Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminViewJsEditor extends AdminViewBase
{
    protected static $view_key = 'js-editor'; // reference key for view
    protected $module_key = 'js';

    // default script
    private $active_script = null;

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @param  string     $View View key.
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            'AdminAjax',
            'AdminEditor',
            'AdminClient',
            'AdminScreen',
            'json',
            'file'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        // disable nocache headers
        add_filter('o10n_admin_nocache', function () {
            return false;
        });

        // set view etc
        parent::setup();
    }

    /**
     * Setup view
     */
    public function setup_view()
    {
        // save settings
        add_action('wp_ajax_o10n_save_jslint', array( $this, 'ajax_save_jslint'), 10);
        add_action('wp_ajax_o10n_save_jsbeautify', array( $this, 'ajax_save_jsbeautify'), 10);
        add_action('wp_ajax_o10n_save_jsminify', array( $this, 'ajax_save_jsminify'), 10);

        // enqueue scripts
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), $this->first_priority);

        // add screen options
        $this->AdminScreen->load_screen('editor');
    }

    /**
     * Return help tab data
     */
    final public function help_tab()
    {
        $data = array(
            'name' => __('Javascript Editor', 'o10n'),
            'github' => 'https://github.com/o10n-x/wordpress-javascript-optimization',
            'wordpress' => 'https://wordpress.org/support/plugin/javascript-optimization',
            'docs' => 'https://github.com/o10n-x/wordpress-javascript-optimization/tree/master/docs'
        );

        return $data;
    }

    /**
     * Enqueue scripts and styles
     */
    final public function enqueue_scripts()
    {
        // skip if user is not logged in
        if (!is_admin() || !is_user_logged_in()) {
            return;
        }

        // set module path
        $this->AdminClient->set_config('module_url', $this->module->dir_url());

        // get user
        $user = wp_get_current_user();

        // global admin script
        $this->AdminClient->preload_CodeMirror('js');
        
        // editor view styles
        wp_enqueue_style('o10n_view_editor', $this->module->dir_url() . 'admin/css/view-editor.css');

        // global admin script
        wp_enqueue_script('o10n_view_js_editor', $this->module->dir_url() . 'admin/js/view-js-editor.js', array( 'jquery', 'o10n_cp', 'o10n_codemirror' ), $this->module->version());

        // add theme files
        $this->AdminClient->set_config('theme_editor_files', $this->theme_js());

        // retrieve active stylesheet
        $active_script = $this->active_script();
        if ($active_script) {
            $this->AdminClient->set_config('theme_editor_active_file', $active_script['filepath']);
        }

        // add js beautify config
        $jsbeautify_options = get_user_meta($user->ID, 'o10n_jsbeautify', true);
        if (!$jsbeautify_options) {
            $jsbeautify_options = array(
                'output' => array(
                    'beautify' => true
                ),
                'mangle' => false,
                'compress' => false
            );
        }
        $this->AdminClient->set_config('js_beautify_options', $jsbeautify_options);

        // add js minify config
        $jsminify_options = get_user_meta($user->ID, 'o10n_jsminify', true);
        if (!$jsminify_options) {
            $jsminify_options = array(
                'mangle' => true,
                'compress' => array(
                    'sequences' => true,
                    'dead_code' => true,
                    'conditionals' => true,
                    'booleans' => true,
                    'unused' => true,
                    'if_return' => true,
                    'join_vars' => true,
                    'drop_console' => true
                )
            );
        }
        $this->AdminClient->set_config('js_minify_options', $jsminify_options);

        // auto lint
        $autolint = get_user_meta($user->ID, 'o10n_eslint_auto', true);
        $this->AdminClient->set_config('editor_autolint', (($autolint) ? 1 : 0));

        // add javascript eslint
        $eslint_options = get_user_meta($user->ID, 'o10n_eslint', true);
        if (!$eslint_options) {
            $eslint_options = false;
        }

        $this->AdminClient->set_config('eslint_options', $eslint_options);

        // add phrases
        $this->AdminClient->set_lg(array(
            'no_issues_found' => __('No issues found', 'o10n'),
            'found_x_issues' => __('Found {n} issues.', 'o10n'),
            'found_x_issues_show' => __('Found {n} issues. (<a href="javascript:void(0);">show</a>)', 'o10n'),
            'linting_js_please_wait' => __('Linting Javascript...', 'o10n'),
            'saving_eslint_settings' => __('Saving ESLINT settings...', 'o10n'),
            'saving_jsbeautify_settings' => __('Saving UglifyJS beautify settings...', 'o10n'),
            'saving_jsminify_settings' => __('Saving UglifyJS optimization settings...', 'o10n'),
            'minifying_js_please_wait' => __('Optimizing Javascript...', 'o10n'),
            'beautifying_js_please_wait' => __('Beautifying Javascript...', 'o10n'),
            'saved_x' => __('Saved {n}', 'o10n')
        ));
    }

    /**
     * Return theme Javascript
     */
    final public function theme_js()
    {

        // theme directory
        $theme_directory = $this->file->theme_directory();

        // default stylesheet directories in theme
        $directories = apply_filters('o10n_editor_theme_js_directories', array(
            $theme_directory,
            $theme_directory . 'assets/',
            $theme_directory . 'assets/js/',
            $theme_directory . 'js/'
        ));

        $assets = array();

        foreach ($directories as $dir) {
            $files = $this->AdminEditor->scandir($dir, 'js');
            if (!empty($files)) {
                $assets = array_merge($assets, $files);
            }
        }

        return $assets;
    }

    /**
     * Return active script file
     */
    final public function active_script()
    {
        if (is_null($this->active_script)) {
            $this->active_script = false;

            // theme directory
            $theme_directory = $this->file->theme_directory();

            $files = array();

            $file = (isset($_GET['file'])) ? $_GET['file'] : false;
            if ($file) {

                // absolute path
                if (substr($file, 0, 1) === '/') {
                    $file = realpath($this->file->un_trailingslashit(ABSPATH) . $file);

                    // verify path
                    if (strpos($file, ABSPATH) !== 0) {
                        throw new Exception('Invalid file', 'admin');
                    }
                } else {
                    $file = realpath($theme_directory . $file);

                    // verify path
                    if (strpos($file, $theme_directory) !== 0) {
                        throw new Exception('Invalid file', 'admin');
                    }
                }

                $files[] = array($file,$_GET['file']);
            }

            foreach ($files as $file) {
                // check for default stylesheet in theme directory
                if (file_exists($file[0])) {
                    $this->active_script = array(
                        'filepath' => $file[1],
                        'text' => file_get_contents($file[0])
                    );
                    break;
                }
            }
        }

        return $this->active_script;
    }

    /**
     * Save CSS LINT settings
     */
    final public function ajax_save_eslint()
    {
        // parse request
        $request = $this->AdminAjax->request();

        // posted rules
        $rules = $request->data('config', false);
        if (!$rules) {
            $request->output_errors(__('No ESLINT rules to save.', 'o10n'));
        }

        // parse options
        try {
            $options = $this->json->parse($rules, true);
        } catch (\Exception $err) {
            $request->output_errors(__('Failed to parse JSON.', 'o10n'));
        }

        $autolint = $request->data('autolint', false);

        // user
        $user_id = $request->user_id();

        // save as user meta
        update_user_meta($user_id, 'o10n_eslint', $options);
        update_user_meta($user_id, 'o10n_eslint_auto', (($autolint) ? 1 : 0));

        // OK
        $request->output_ok(__('ESLINT settings saved.', 'o10n'));
    }

    /**
     * Save UglifyJS beautify settings
     */
    final public function ajax_save_jsbeautify()
    {
        // parse request
        $request = $this->AdminAjax->request();

        // posted rules
        $options = $request->data('options', false);
        if (!$options) {
            $request->output_errors(__('No UglifyJS beautify options to save.', 'o10n'));
        }

        // parse options
        try {
            $options = $this->json->parse($options, true);
        } catch (\Exception $err) {
            $request->output_errors(__('Failed to parse JSON.', 'o10n'));
        }

        // save as user meta
        update_user_meta($request->user_id(), 'o10n_jsbeautify', $options);

        // OK
        $request->output_ok(__('Beautify settings saved.', 'o10n'));
    }

    /**
     * Save UglifyJS minify settings
     */
    final public function ajax_save_jsminify()
    {
        // parse request
        $request = $this->AdminAjax->request();

        // posted rules
        $options = $request->data('options', false);
        if (!$options) {
            $request->output_errors(__('No UglifyJS minify options to save.', 'o10n'));
        }

        // parse options
        try {
            $options = $this->json->parse($options, true);
        } catch (\Exception $err) {
            $request->output_errors(__('Failed to parse JSON.', 'o10n'));
        }

        // save as user meta
        update_user_meta($request->user_id(), 'o10n_jsminify', $options);

        // OK
        $request->output_ok(__('UglifyJS settings saved.', 'o10n'));
    }
}

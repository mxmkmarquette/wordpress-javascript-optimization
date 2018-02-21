<?php
namespace O10n;

/**
 * Javascript Optimization Admin Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminJs extends ModuleAdminController implements Module_Admin_Controller_Interface
{

    // admin base
    protected $admin_base = 'themes.php';

    // tab menu
    protected $tabs = array(
        'intro' => array(
            'title' => '<span class="dashicons dashicons-admin-home"></span>',
            'title_attr' => 'Intro'
        ),
        'optimization' => array(
            'title' => 'Code Optimization',
            'title_attr' => 'Javascript Code Optimization'
        ),
        'delivery' => array(
            'title' => 'Delivery Optimization'
        ),
        'editor' => array(
            'title' => 'Javascript Editor',
            'title_attr' => 'Javascript Editor',
            'admin_base' => 'themes.php',
            'pagekey' => 'js-editor',
            'subtabs' => array(
                'lint' => array(
                    'title' => 'ES Lint',
                    'href' => '#eslint'
                ),
                'minify' => array(
                    'title' => 'Minify',
                    'href' => '#minify'
                ),
                'beautify' => array(
                    'title' => 'Beautify',
                    'href' => '#beautify'
                )
            )
        )
    );

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            'AdminView'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        
        // settings link on plugin index
        add_filter('plugin_action_links_' . $this->core->modules('js')->basename(), array($this, 'settings_link'));

        // meta links on plugin index
        add_filter('plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2);

        // title on plugin index
        add_action('pre_current_active_plugins', array( $this, 'plugin_title'), 10);

        // admin options page
        add_action('admin_menu', array($this, 'admin_menu'), 50);

        // reorder menu
        add_filter('custom_menu_order', array($this, 'reorder_menu'), PHP_INT_MAX);
    }
    
    /**
     * Admin menu option
     */
    final public function admin_menu()
    {
        global $submenu;

        // WPO plugin or more than 1 optimization module, add to optimization menu
        if (defined('O10N_WPO_VERSION') || count($this->core->modules()) > 1) {
            add_submenu_page('o10n', __('Javascript Optimization', 'o10n'), __('Javascript', 'o10n'), 'manage_options', 'o10n-js', array(
                 &$this->AdminView,
                 'display'
             ));

            // change base to admin.php
            $this->admin_base = 'admin.php';
        } else {

            // add menu entry to themes page
            add_submenu_page('themes.php', __('Javascript Optimization', 'o10n'), __('Javascript Optimization', 'o10n'), 'manage_options', 'o10n-js', array(
                 &$this->AdminView,
                 'display'
             ));
        }


        // add menu entry to themes page
        add_submenu_page('themes.php', __('Advanced Javascript Editor', 'o10n'), __('Javascript Editor', 'o10n'), 'manage_options', 'o10n-js-editor', array(
             &$this->AdminView,
             'display'
         ));
    }
    
    /**
     * Settings link on plugin overview.
     *
     * @param  array $links Plugin settings links.
     * @return array Modified plugin settings links.
     */
    final public function settings_link($links)
    {
        $settings_link = '<a href="'.esc_url(add_query_arg(array('page' => 'o10n-js','tab' => 'optimization'), admin_url($this->admin_base))).'">'.__('Settings').'</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Show row meta on the plugin screen.
     */
    public static function plugin_row_meta($links, $file)
    {
        if ($file == $this->core->modules('css')->basename()) {
            $lgcode = strtolower(get_locale());
            if (strpos($lgcode, '_') !== false) {
                $lgparts = explode('_', $lgcode);
                $lgcode = $lgparts[0];
            }
            if ($lgcode === 'en') {
                $lgcode = '';
            }

            $row_meta = array(
                /*'o10n_scores' => '<a href="' . esc_url('https://optimization.team/pro/') . '" target="_blank" title="' . esc_attr(__('View Google PageSpeed Scores Documentation', 'o10n')) . '" style="font-weight:bold;color:black;">' . __('Upgrade to <span class="g100" style="padding:0px 4px;">PRO</span>', 'o10n') . '</a>'*/
            );

            return array_merge($links, $row_meta);
        }

        return (array) $links;
    }

    /**
     * Plugin title modification
     */
    public function plugin_title()
    {
        ?><script>jQuery(function($){var r=$('*[data-plugin="<?php print $this->core->modules('js')->basename(); ?>"]');
            $('.plugin-title strong',r).html('<?php print $this->core->modules('js')->name(); ?><a href="https://optimization.team" target="_blank" class="g100">O10N</span>');
});</script><?php
    }

    /**
     * Reorder menu
     */
    public function reorder_menu($menu_order)
    {
        global $submenu;

        // move Javascript Editor to end of list
        $editor_item = false;
        $wp_editor_index = false;
        foreach ($submenu['themes.php'] as $key => $item) {
            if ($item[2] === 'theme-editor.php') {
                $wp_editor_index = $key;
            } elseif ($item[2] === 'o10n-js-editor') {
                $editor_item = $item;
                unset($submenu['themes.php'][$key]);
            }
        }

        if ($wp_editor_index) {
            $reordered = array();
            foreach ($submenu['themes.php'] as $key => $item) {
                $reordered[] = $item;
                if ($key === $wp_editor_index) {
                    $reordered[] = $editor_item;
                }
            }
            $submenu['themes.php'] = $reordered;
        } else {
            $submenu['themes.php'][] = $editor_item;
        }
    }
}

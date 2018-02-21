<?php
namespace O10n;

/**
 * Global Javascript Optimization Admin Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminGlobaljs extends ModuleAdminController implements Module_Admin_Controller_Interface
{

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
            'client'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {

        // add admin bar menu
        add_action('admin_bar_menu', array( $this, 'admin_bar'), 100);
    }
     
    /**
     * Admin bar option
     *
     * @param  object       Admin bar object
     */
    final public function admin_bar($admin_bar)
    {
        // current url
        if (is_admin()
            || (defined('DOING_AJAX') && DOING_AJAX)
            || in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))
        ) {
            $currenturl = home_url();
        } else {
            $currenturl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        // WPO plugin or more than 1 optimization module, add to optimization menu
        if (defined('O10N_WPO_VERSION') || count($this->core->modules()) > 1) {
            $admin_bar->add_menu(array(
                'id' => 'o10n',
                'title' => '<span class="ab-label">' . __('o10n', 'o10n') . '</span>',
                'href' => add_query_arg(array( 'page' => 'o10n' ), admin_url('admin.php')),
                'meta' => array( 'title' => __('Performance Optimization', 'o10n'), 'class' => 'ab-sub-secondary' )
            ));

            $admin_bar->add_node(array(
                'parent' => 'o10n',
                'id' => 'o10n-js',
                'title' => __('Javascript Optimization', 'o10n'),
                'href' => add_query_arg(array( 'page' => 'o10n-js' ), admin_url('admin.php'))
            ));

            $admin_base = 'admin.php';
        } else {
            $admin_bar->add_menu(array(
                'id' => 'o10n-js',
                'title' => '<span class="ab-label">' . __('JS', 'o10n') . '</span>',
                'href' => add_query_arg(array( 'page' => 'o10n-js' ), admin_url('themes.php')),
                'meta' => array( 'title' => __('Javascript Optimization', 'o10n'), 'class' => 'ab-sub-secondary' )
            ));

            $admin_base = 'themes.php';
        }

        // critical Javascript quality test
        $admin_bar->add_node(array(
            'parent' => 'o10n-js',
            'id' => 'o10n-js-editor',
            'title' => '<span class="dashicons dashicons-editor-code" style="font-family:dashicons;margin-top:-3px;margin-right:4px;"></span> ' . __('Javascript Editor', 'o10n'),
            'href' => add_query_arg(array( 'page' => 'o10n-js-editor' ), admin_url('themes.php')),
            'meta' => array( 'title' => __('Javascript Editor', 'o10n') )
        ));
    }
}

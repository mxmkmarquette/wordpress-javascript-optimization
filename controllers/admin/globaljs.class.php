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
        return parent::construct($Core, array('cache'));
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

        // get cache stats
        $stats = $this->cache->stats('js');
        if (!isset($stats['size']) || $stats['size'] === 0) {
            $cache_size = ' ('.__('Empty', 'o10n').')';
        } else {
            $cache_size = ' ('.size_format($stats['size'], 2).')';
        }

        // WPO plugin or more than 1 optimization module, add to optimization menu
        if (defined('O10N_WPO_VERSION') || count($this->core->modules()) > 1) {
            $admin_bar->add_node(array(
                'parent' => 'o10n',
                'id' => 'o10n-js',
                'title' => __('Javascript Optimization', 'o10n'),
                'href' => add_query_arg(array( 'page' => 'o10n-js' ), admin_url('admin.php'))
            ));

            $admin_bar->add_menu(array(
                'parent' => 'o10n-cache',
                'id' => 'o10n-js-cache',
                'title' => 'Javascript cache' . $cache_size,
                'href' => 'javascript:void(0);'
            ));

            $admin_base = 'admin.php';
        } else {
            $admin_bar->add_menu(array(
                'id' => 'o10n-js',
                'title' => '<span class="ab-label">' . __('JS', 'o10n') . '</span>',
                'href' => add_query_arg(array( 'page' => 'o10n-js' ), admin_url('themes.php')),
                'meta' => array( 'title' => __('Javascript Optimization', 'o10n'), 'class' => 'ab-sub-secondary' )
            ));

            $admin_bar->add_menu(array(
                'parent' => 'o10n-js',
                'id' => 'o10n-js-cache',
                'title' => __('Cache', 'o10n') . $cache_size,
                'href' => '#',
                'meta' => array( 'title' => __('Plugin Cache Management', 'o10n'), 'class' => 'ab-sub-secondary', 'onclick' => 'return false;' )
            ));

            $admin_base = 'themes.php';
        }

        // flush Javascript cache
        $admin_bar->add_menu(array(
            'parent' => 'o10n-js-cache',
            'id' => 'o10n-cache-flush-js',
            'href' => $this->cache->flush_url('js'),
            'title' => '<span class="dashicons dashicons-trash o10n-menu-icon"></span> Flush Javascript cache'
        ));

        // flush Javascript concat index cache
        $admin_bar->add_menu(array(
            'parent' => 'o10n-js-cache',
            'id' => 'o10n-cache-flush-js-concat',
            'href' => $this->cache->flush_url('js', 'concat'),
            'title' => '<span class="dashicons dashicons-trash o10n-menu-icon"></span> Flush Javascript concat cache (reset index)'
        ));

        // critical Javascript quality test
        $admin_bar->add_node(array(
            'parent' => 'o10n-js',
            'id' => 'o10n-js-editor',
            'title' => '<span class="dashicons dashicons-editor-code o10n-menu-icon"></span> ' . __('Javascript Editor', 'o10n'),
            'href' => add_query_arg(array( 'page' => 'o10n-js-editor' ), admin_url('themes.php')),
            'meta' => array( 'title' => __('Javascript Editor', 'o10n') )
        ));
    }
}

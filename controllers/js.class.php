<?php
namespace O10n;

/**
 * Javascript Optimization Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class Js extends Controller implements Controller_Interface
{
    // module key refereces
    private $client_modules = array(
        'js',
        'jquery-stub'
    );

    // automatically load dependencies
    private $client_module_dependencies = array();

    private $minifier; // minifier

    private $replace = null; // replace in script
    private $script_cdn = null; // script CDN config
    private $http2_push = null; // HTTP/2 Server Push config

    private $diff_hash_prefix; // diff based hash prefix
    private $last_used_minifier; // last used minifier

    // extracted script elements
    private $script_elements = array();

    // load/render position
    private $load_position;
    private $rel_preload = false; // default rel="preload"
    private $exec_timing = false; // default async exec

    private $async_filter; // filter for scripts
    private $async_filterConcat; // filter for concat groups
    private $async_filterType;

    private $localStorage = false; // default localStorage config

    // closure compiler service instance
    private $ClosureCompilerService = null;

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
            'url',
            'env',
            'file',
            'http',
            'cache',
            'client',
            'json',
            'output',
            'tools',
            'proxy',
            'options'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {        // local curl proxy

        // disabled
        if (!$this->env->is_optimization()) {
            return;
        }

        // add module definitions
        $this->client->add_module_definitions($this->client_modules, $this->client_module_dependencies);

        // extract scripts for processing?
        if ($this->options->bool(['js.minify','js.async','js.proxy'])) {
            if ($this->options->bool('js.minify.enabled')) {
                $this->minifier = $this->options->get('js.minify.minifier', 'jsmin');
            }
            
            // add script optimization client module
            $this->client->load_module('js', O10N_CORE_VERSION, $this->core->modules('js')->dir_path());

            // async loading
            if ($this->options->bool('js.async.enabled')) {
                $this->client->set_config('js', 'async', true);

                // jQuery stub
                if ($this->options->bool('js.async.jQuery_stub')) {
                    $this->client->load_module('jquery-stub', O10N_CORE_VERSION, $this->core->modules('js')->dir_path());
                }

                // rel="preload" based loading
                // @link https://www.w3.org/TR/2015/WD-preload-20150721/
                if ($this->options->bool('js.async.rel_preload')) {
                    $this->rel_preload = true;
                }

                // async download position
                $this->load_position = $this->options->get('js.async.load_position', 'header');

                if ($this->load_position === 'footer') {
                    // set load position
                    $this->client->set_config('js', 'load_position', $this->client->config_index('key', 'footer'));
                } elseif ($this->load_position === 'timing') {
                    
                    // add timed exec module
                    $this->client->load_module('timed-exec');
                  
                    // set load position
                    $this->client->set_config('js', 'load_position', $this->client->config_index('key', 'timing'));

                    // timing type
                    $timing_type = $this->options->get('js.async.load_timing.type');
                    switch ($timing_type) {
                        case "media":

                            // add responsive exec module
                            $this->client->load_module('responsive');
                        break;
                        case "inview":

                            // add inview exec module
                            $this->client->load_module('inview');
                        break;
                    }

                    // timing config
                    $this->load_timing = $this->timing_config($this->options->get('js.async.load_timing.*'));
                    if ($this->load_timing) {
                        $this->client->set_config('js', 'load_timing', $this->load_timing);
                    }
                }

                if ($this->options->bool('js.async.exec_timing.enabled')) {
                        
                    // add timed exec module
                    $this->client->load_module('timed-exec');

                    // timing type
                    $timing_type = $this->options->get('js.async.exec_timing.type');
                    switch ($timing_type) {
                        case "requestAnimationFrame":
                            $this->requestAnimationFrame = false;
                        break;
                        case "media":

                            // add responsive exec module
                            $this->client->load_module('responsive');
                        break;
                        case "inview":

                            // add inview exec module
                            $this->client->load_module('inview');
                        break;
                    }

                    // timing config
                    $this->exec_timing = $this->timing_config($this->options->get('js.async.exec_timing.*'));
                    if ($this->exec_timing) {
                        $this->client->set_config('js', 'exec_timing', $this->exec_timing);
                    }
                }

                // async exec (default)
                if ($this->options->bool('js.async.exec')) {
                    $this->exec_timing = $this->options->get('js.async.exec.*');
                    switch ($this->exec_timing['type']) {
                        case "domReady":
                            $keys = array();
                        break;
                        case "requestAnimationFrame":
                            $keys = array('frame' => 'JSONKEY');
                        break;
                        case "requestIdleCallback":
                            $keys = array('timeout' => 'JSONKEY','setTimeout' => 'JSONKEY');
                        break;
                        case "inview":

                            // load inview module
                            $this->client->load_module('inview');

                            $keys = array('selector' => 'JSONKEY','offset' => 'JSONKEY');
                        break;
                        case "media":

                            // load responsive module
                            $this->client->load_module('responsive');

                            $keys = array('media' => 'JSONKEY');
                        break;
                    }
                    $exec_timing_config = $this->client->config_array_data($this->exec_timing, $keys);
                    if (!empty($exec_timing_config)) {
                        $this->client->set_config('js', 'exec_timing', array(
                            $this->client->config_index('jsonkey_'.$this->exec_timing['type']),
                            $exec_timing_config
                        ));
                    } else {
                        $this->client->set_config('js', 'exec_timing', $this->client->config_index('jsonkey_'.$this->exec_timing['type']));
                    }
                }
                
                // localStorage cache
                if ($this->options->bool('js.async.localStorage')) {

                    // load client module
                    $this->client->load_module('localstorage');

                    // set enabled state
                    $this->client->set_config('js', 'localStorage', true);

                    // localStorage config
                    $this->localStorage = array();

                    $config_keys = array('max_size','expire','update_interval');
                    foreach ($config_keys as $key) {
                        $this->localStorage[$key] = $this->options->get('js.async.localStorage.' . $key);
                        if ($this->localStorage[$key]) {
                            $this->client->set_config('js', 'localStorage_' . $key, $this->localStorage[$key]);
                        }
                    }

                    if ($this->options->bool('js.async.localStorage.head_update')) {
                        $this->localStorage['head_update'] = 1;
                        $this->client->set_config('js', 'localStorage_head_update', 1);
                    }
                }
            }

            // apply CDN
            if ($this->options->bool('js.cdn')) {

                // CDN config
                $this->script_cdn = array(
                    $this->options->get('js.cdn.url'),
                    $this->options->get('js.cdn.mask')
                );
            } else {
                $this->script_cdn = false;
            }

            // apply CDN to pushed assets
            $this->http2_push_cdn = $this->options->bool('js.cdn.http2_push');
        
            // HTTP/2 Server Push enabled
            if ($this->options->bool('js.http2_push.enabled') && $this->core->module_loaded('http2')) {
                if (!$this->options->bool('js.http2_push.filter')) {
                    $this->http2_push = true;
                } else {
                    $filterType = $this->options->get('js.http2_push.filter.type');
                    $filterConfig = ($filterType) ? $this->options->get('js.http2_push.filter.' . $filterType) : false;

                    if (!$filterConfig) {
                        $this->http2_push = false;
                    } else {
                        $this->http2_push = array($filterType, $filterConfig);
                    }
                }
            } else {
                $this->http2_push = false;
            }

            // add filter for HTML output
            add_filter('o10n_html_pre', array( $this, 'process_html' ), 10, 1);
        }
    }

    /**
     * Minify the markeup given in the constructor
     *
     * @param  string $HTML Reference to HTML to process
     * @return string Filtered HTML
     */
    final public function process_html($HTML)
    {
        // verify if empty
        if ($HTML === '' || !$this->env->is_optimization()) {
            return $HTML; // no HTML
        }

        // extract <script> elements from HTML
        $this->extract($HTML);

        // no script elements, skip
        if (empty($this->script_elements)) {
            return $HTML;
        }

        // debug modus
        $debug = (defined('O10N_DEBUG') && O10N_DEBUG);

        // script urls
        $script_urls = array();

        // client config
        $async_scripts = array();

        // load async
        $async = $this->options->bool('js.async');
        if ($async) {

            // async load position
            $async_position = ($this->options->get('js.async.position') === 'footer') ? 'foot' : 'critical-css';
        }

        // concatenation
        $concat = $this->options->bool('js.minify') && $this->options->bool('js.minify.concat');

        // rel="preload"
        if ($this->rel_preload) {
            
            // rel="preload" position
            $this->rel_preload_position = ($async_position) ? $async_position : 'critical-css';
        }

        // concatenation settings
        if ($concat) {
            
            // concatenate filter
            if ($this->options->bool('js.minify.concat.filter')) {
                $concat_filter = $this->sanitize_filter($this->options->get('js.minify.concat.filter.config'));
            } else {
                $concat_filter = false;
            }

            // concatenate
            $concat_groups = array();
            $concat_group_settings = array();
        }


        // walk css elements
        foreach ($this->script_elements as $n => $script) {

            // concatenate
            if ($concat && (
                (isset($script['minified']) && $script['minified']) // minified source
            )) {

                // concat group filter
                if ($concat_filter) {

                    // set to false (skip concat) if concatenation is excluded by default
                    $concat_group = ($this->options->get('js.minify.concat.filter.type', 'include') !== 'include') ? false : 'global';

                    // apply group filter
                    $this->apply_filter($concat_group, $concat_group_settings, $script['tag'], $concat_filter);
                } else {
                    $concat_group = 'global';
                }

                // include script in concatenation
                if ($concat_group) {

                    // initiate group
                    if (!isset($concat_groups[$concat_group])) {

                        // scripts in group
                        $concat_groups[$concat_group] = array();

                        // group settings
                        if (!isset($concat_group_settings[$concat_group])) {
                            $concat_group_settings[$concat_group] = array();
                        }

                        $concat_group_key = (isset($concat_group_settings[$concat_group]['group']) && isset($concat_group_settings[$concat_group]['group']['key'])) ? $concat_group_settings[$concat_group]['group']['key'] : 'global';

                        // load async by default
                        $concat_group_settings[$concat_group]['async'] = $this->options->bool('js.async');

                        // apply async filter
                        if (!empty($this->async_filter)) {

                            // apply filter to key
                            $asyncConfig = $this->tools->filter_config_match($concat_group_key, $this->async_filter, $this->async_filterType);

                            // filter config object
                            if ($asyncConfig && is_array($asyncConfig)) {

                                // async enabled by filter
                                if (!isset($asyncConfig['async']) || $asyncConfig['async']) {
                                    $concat_group_settings[$concat_group]['async'] = $this->options->bool('js.async');

                                    // custom load position
                                    if (isset($asyncConfig['load_position']) && $asyncConfig['load_position'] !== $this->load_position) {
                                        $concat_group_settings[$concat_group]['load_position'] = $asyncConfig['load_position'];
                                    }

                                    // load timing
                                    if (isset($asyncConfig['load_position']) && $asyncConfig['load_position'] === 'timing' && isset($asyncConfig['load_timing'])) {
                                        $concat_group_settings[$concat_group]['load_timing'] = $asyncConfig['load_timing'];
                                    }

                                    // async exec
                                    if (isset($asyncConfig['exec_timing'])) {
                                        $concat_group_settings[$concat_group]['exec_timing'] = $asyncConfig['exec_timing'];
                                    }

                                    // try catch
                                    if (isset($asyncConfig['trycatch'])) {
                                        $concat_group_settings[$concat_group]['trycatch'] = $asyncConfig['trycatch'];
                                    }

                                    // custom rel_preload
                                    if (isset($asyncConfig['rel_preload']) && $asyncConfig['rel_preload'] !== $this->rel_preload) {
                                        $concat_group_settings[$concat_group]['rel_preload'] = $asyncConfig['rel_preload'];
                                    }

                                    // custom localStorage
                                    if (isset($asyncConfig['localStorage'])) {
                                        if ($asyncConfig['localStorage'] === false) {
                                            $concat_group_settings[$concat_group]['localStorage'] = false;
                                        } elseif ($asyncConfig['localStorage'] === true && $this->localStorage) {
                                            $concat_group_settings[$concat_group]['localStorage'] = $this->localStorage;
                                        } else {
                                            $concat_group_settings[$concat_group]['localStorage'] = $asyncConfig['localStorage'];
                                        }
                                    }

                                    // custom minifier
                                    if (isset($asyncConfig['minifier'])) {
                                        $concat_group_settings[$concat_group]['minifier'] = $asyncConfig['minifier'];
                                    }
                                } else {

                                    // do not load async
                                    $concat_group_settings[$concat_group]['async'] = false;
                                }
                            } elseif ($asyncConfig === true) {

                                // include by default
                                $concat_group_settings[$concat_group]['async'] = true;
                            }
                        }
                    }

                    // inline <style>
                    if (isset($script['inline'])) {
                        $hash = md5($script['text']);
                        $concat_groups[$concat_group][] = array(
                            'inline' => true,
                            'hash' => $hash,
                            'cache_hash' => $hash,
                            'tag' => $script['tag'],
                            'text' => $script['text'],
                            'position' => count($async_scripts),
                            'element' => $script
                        );
                    } else {
                        // minified script
                        $concat_groups[$concat_group][] = array(
                            'hash' => $script['minified'][0] . ':' . $script['minified'][1],
                            'cache_hash' => $script['minified'][0],
                            'tag' => $script['tag'],
                            'src' => $script['src'],
                            'position' => count($async_scripts),
                            'element' => $script
                        );
                    }

                    // remove script from HTML
                    $this->output->add_search_replace($script['tag'], '');

                    // maintain position index
                    $async_scripts[] = false;

                    // maintain position index
                    $script_urls[] = false;

                    continue 1; // next script
                }
            } // concat end

            // inline <style> without concatenation, ignore
            if (isset($script['inline'])) {
                continue 1; // next script
            }
            
            // load async
            if ($async && $script['async']) {
                
                // config
                $rel_preload = (isset($script['rel_preload'])) ? $script['rel_preload'] : $this->rel_preload;
                $load_position = (isset($script['load_position'])) ? $script['load_position'] : $this->load_position;
                $load_timing = (isset($script['load_timing'])) ? $script['load_timing'] : $this->load_timing;
                $exec_timing = (isset($script['exec_timing'])) ? $script['exec_timing'] : $this->exec_timing;

                // minified script
                if (isset($script['minified']) && $script['minified']) {
                    // hash type
                    $script_type = 'src';

                    // script path
                    $script_hash = str_replace('/', '', $this->cache->hash_path($script['minified'][0]) . substr($script['minified'][0], 6));

                    // script url
                    $script_url = $this->url_filter($this->cache->url('js', 'src', $script['minified'][0]));
                } else {

                    // proxy hash
                    if (isset($script['proxy']) && $script['proxy']) {

                        // hash type
                        $script_type = 'proxy';

                        // script path
                        $script_hash = str_replace('/', '', $this->cache->hash_path($script['proxy'][0]) . substr($script['proxy'][0], 6));

                        // script url
                        $script_url = $this->url_filter($script['src']);
                    } else {

                        // hash type
                        $script_type = 'url';

                        // script url
                        $script_hash = $script_url = $this->url_filter($script['src']);
                    }
                }

                // add script to async list
                $async_script = array(
                    'type' => $script_type,
                    'url' => $script_hash,
                    'original_url' => $script['src'],
                    'load_position' => $load_position,
                    'load_timing' => $load_timing,
                    'exec_timing' => $exec_timing
                );
                if (isset($script['localStorage'])) {
                    $async_script['localStorage'] = $script['localStorage'];
                }
                $async_scripts[] = $async_script;

                // rel="preload" or <noscript>
                if ($rel_preload || $noscript) {

                    // add script to url list
                    $script_urls[] = array(
                        'url' => $script_url,
                        'rel_preload' => $rel_preload,
                        'load_position' => $load_position,
                        'load_timing' => $load_timing,
                        'exec_timing' => $exec_timing
                    );
                } else {
                    $script_urls[] = false;
                }

                // remove script from HTML
                $this->output->add_search_replace($script['tag'], '');
            } else {
                if (isset($script['minified']) && $script['minified']) {

                    // minified URL
                    $script['src'] = $this->cache->url('js', 'src', $script['minified'][0]);
                    $script['replaceSrc'] = true;
                }

                // apply CDN
                $filteredSrc = $this->url_filter($script['src']);
                if ($filteredSrc !== $script['src']) {
                    $script['src'] = $filteredSrc;
                    $script['replaceSrc'] = true;
                }

                // replace src in HTML
                if (isset($script['replaceSrc'])) {

                    // replace src in tag
                    $this->output->add_search_replace($script['tag'], $this->attr_regex('src', $script['tag'], $script['src']));
                }
            }
        }

        // process concatenated scripts
        if ($concat) {

            // concat using minify
            $concat_minify = $this->options->bool('js.concat.minify');

            // wrap scripts in try {} catch(e) {}
            $concat_trycatch = $this->options->bool('js.minify.concat.trycatch');

            foreach ($concat_groups as $concat_group => $scripts) {

                // position to load concatenated script
                $async_insert_position = 0;

                // script hashes
                $concat_hashes = array();

                // add group key to hash
                if ($concat_group_settings && isset($concat_group_settings[$concat_group]['group']) && isset($concat_group_settings[$concat_group]['group']['key'])) {
                    $concat_hashes[] = $concat_group_settings[$concat_group]['group']['key'];
                }

                // add script hashes
                foreach ($scripts as $script) {
                    $concat_hashes[] = $script['hash'];
                    if ($script['position'] > $async_insert_position) {
                        $async_insert_position = $script['position'];
                    }
                }

                // insert after last script in concatenated group
                $async_insert_position++;

                // calcualte hash from source files
                $urlhash = md5(implode('|xx', $concat_hashes));

                // load from cache
                if ($this->cache->exists('js', 'concat', $urlhash)) {
   
                    // preserve cache file based on access
                    $this->cache->preserve('js', 'concat', $urlhash, (time() - 3600));

                    $contact_original_urls = array();
                    foreach ($scripts as $script) {
                        if (isset($script['inline'])) {
                            $script_filename = 'inline-' . $script['hash'];
                            $contact_original_urls[] = $script_filename;
                        } else {
                            $contact_original_urls[] = $script['src'];
                        }

                        if (isset($script['exec_timing']) && $script['exec_timing']) {
                            switch ($script['exec_timing']['type']) {
                                case "inview":
                                    if (isset($script['exec_timing']['selector'])) {
                                        // load inview module
                                        $this->client->load_module('inview');
                                    }
                                break;
                                case "media":
                                    if (isset($script['exec_timing']['selector'])) {
                                        // load responsive module
                                        $this->client->load_module('responsive');
                                    }
                                break;
                            }
                        }
                    }
                } else {

                    // concatenate scripts
                    $concat_sources = array();
                    $contact_original_urls = array();
                    foreach ($scripts as $script) {
                        if (isset($script['inline'])) {
                            // get source
                            $source = $script['text'];
                            $script_filename = 'inline-' . $script['hash'];
                            $contact_original_urls[] = $script_filename;
                        } else {
                            
                            // get source from cache
                            $source = $this->cache->get('js', 'src', $script['cache_hash']);
                            $script_filename = $this->extract_filename($script['src']);
                            $contact_original_urls[] = $script['src'];
                        }

                        // empty, ignore
                        if (!$source) {
                            continue 1;
                        }

                        if (isset($script['exec_timing']) && $script['exec_timing']) {
                            switch ($script['exec_timing']['type']) {
                                case "domReady":
                                    $source = 'o10n.ready(function(){' . $source . '});';
                                break;
                                case "requestAnimationFrame":
                                    $frame = (isset($script['exec_timing']['frame'])) ? $script['exec_timing']['frame'] : 1;
                                    $source = 'o10n.raf(function(){' . $source . '},'.$frame.');';
                                break;
                                case "requestIdleCallback":
                                    $timeout = (isset($script['exec_timing']['timeout'])) ? $script['exec_timing']['timeout'] : 'false';
                                    $setTimeout = (isset($script['exec_timing']['setTimeout'])) ? $script['exec_timing']['setTimeout'] : 'false';
                                    $source = 'o10n.idle(function(){' . $source . '},'.$timeout.','.$setTimeout.');';
                                break;
                                case "inview":
                                    if (isset($script['exec_timing']['selector'])) {
                                        $offset = (isset($script['exec_timing']['offset']) && is_numeric($script['exec_timing']['offset'])) ? (string)$script['exec_timing']['offset'] : 'false';

                                        // load inview module
                                        $this->client->load_module('inview');

                                        $source = 'o10n.inview('.json_encode($script['exec_timing']['selector']).','.$offset.',function(){' . $source . '});';
                                    }

                                break;
                                case "media":
                                    if (isset($script['exec_timing']['media'])) {

                                        // load responsive module
                                        $this->client->load_module('responsive');

                                        $source = 'o10n.media('.json_encode($script['exec_timing']['media']).',function(){' . $source . '});';
                                    }

                                break;
                            }
                        }

                        // wrap in in try {} catch(e) {}
                        if ($concat_trycatch) {
                            $source = 'try{' . $source . '}catch(e){if(console&&console.error){console.error(e);}}';
                        }

                        // concat source config
                        $concat_sources[$script_filename] = array();

                        // remove sourceMap references
                        $sourcemapIndex = strpos($source, '/*# sourceMappingURL');
                        while ($sourcemapIndex !== false) {
                            $sourcemapEndIndex = strpos($source, '*/', $sourcemapIndex);
                            $source = substr_replace($source, '', $sourcemapIndex, (($sourcemapEndIndex - $sourcemapIndex) + 2));
                            $sourcemapIndex = strpos($source, '/*# sourceMappingURL');
                        }

                        // script source
                        $concat_sources[$script_filename]['text'] = $source;

                        // create source map
                        if (!isset($script['inline']) && $this->options->bool('js.minify.clean-js.sourceMap')) {
                            $map = $this->cache->get('js', 'src', $script['cache_hash'], false, false, '.js.map');
                            $concat_sources[$script_filename]['map'] = $map;
                        }
                    }

                    // use minify?
                    $concat_group_minify = (isset($concat_group_settings[$concat_group]['minify'])) ? $concat_group_settings[$concat_group]['minify'] : $concat_minify;
                    $concat_group_minifier = (isset($concat_group_settings[$concat_group]['minifier'])) ? $concat_group_settings[$concat_group]['minifier'] : $this->minifier;
                    $concat_group_key = (isset($concat_group_settings[$concat_group]['group']) && isset($concat_group_settings[$concat_group]['group']['key'])) ? $concat_group_settings[$concat_group]['group']['key'] : false;

                    // concatenate using minify
                    if ($concat_group_minify) {

                        // target src cache dir of concatenated scripts for URL rebasing
                        $target_src_dir = $this->file->directory_url('js/0/1/', 'cache', true);

                        // create concatenated file using minifier
                        try {
                            $minified = $this->minify($concat_sources, $target_src_dir, $concat_group_minifier);
                        } catch (Exception $err) {
                            $minified = false;
                        }
                    } else {
                        $minified = false;
                    }

                    if ($minified) {

                        // store cache file
                        $cache_file_path = $this->cache->put('js', 'concat', $urlhash, $minified['text'], $concat_group_key);

                        //return $HTML = var_export(file_get_contents($cache_file_path), true);

                        // add link to source map
                        if (isset($minified['sourcemap'])) {

                            // add link to script
                            $minified['text'] .= "\n/*# sourceMappingURL=".basename($cache_file_path).".map */";

                            // update script cache
                            try {
                                $this->file->put_contents($cache_file_path, $minified['text']);
                            } catch (\Exception $e) {
                                throw new Exception('Failed to store script ' . $this->file->safe_path($cache_file_path) . ' <pre>'.$e->getMessage().'</pre>', 'config');
                            }

                            // apply filters
                            $minified['sourcemap'] = $this->minified_sourcemap_filter($minified['sourcemap']);

                            // store source map
                            try {
                                $this->file->put_contents($cache_file_path . '.map', $minified['sourcemap']);
                            } catch (\Exception $e) {
                                throw new Exception('Failed to store script source map ' . $this->file->safe_path($cache_file_path . '.map') . ' <pre>'.$e->getMessage().'</pre>', 'config');
                            }
                        }
                    } else {

                        // minification failed, simply join files
                        $script = array();
                        foreach ($concat_sources as $source) {
                            $script[] = $source['text'];
                        }

                        // store cache file
                        $this->cache->put('js', 'concat', $urlhash, implode(' ', $script), $concat_group_key);
                    }
                }

                // load async?
                $concat_group_async = (isset($concat_group_settings[$concat_group]['async'])) ? $concat_group_settings[$concat_group]['async'] : $this->options->bool('js.async');

                // config
                $rel_preload = (isset($concat_group_settings[$concat_group]['rel_preload'])) ? $concat_group_settings[$concat_group]['rel_preload'] : $this->rel_preload;
                $load_position = (isset($concat_group_settings[$concat_group]['load_position'])) ? $concat_group_settings[$concat_group]['load_position'] : $this->load_position;
                $load_timing = (isset($concat_group_settings[$concat_group]['load_timing'])) ? $concat_group_settings[$concat_group]['load_timing'] : $this->load_timing;
                $exec_timing = (isset($concat_group_settings[$concat_group]['exec_timing'])) ? $concat_group_settings[$concat_group]['exec_timing'] : $this->exec_timing;

                // concat URL
                $script_url = $this->url_filter($this->cache->url('js', 'concat', $urlhash));

                // load async (concatenated script)
                if ($concat_group_async) {

                    // add script to async list
                    $async_script = array(
                        'type' => 'concat',
                        'url' => $this->async_hash_path($urlhash),
                        'original_url' => $contact_original_urls,
                        'load_position' => $load_position,
                        'load_timing' => $load_timing,
                        'exec_timing' => $exec_timing
                    );

                    if (isset($concat_group_settings[$concat_group]['localStorage'])) {
                        $async_script['localStorage'] = $concat_group_settings[$concat_group]['localStorage'];
                    }

                    // add to position of last script in concatenated script
                    array_splice($async_scripts, $async_insert_position, 0, array($async_script));

                    // config
                    if ($rel_preload) {

                        // add to position of last script in concatenated script
                        array_splice($script_urls, $async_insert_position, 0, array(array(
                            'url' => $script_url,
                            'rel_preload' => $rel_preload,
                            'load_position' => $load_position,
                            'load_timing' => $load_timing,
                            'exec_timing' => $exec_timing
                        )));
                    }
                } else {
                    
                    // position in document
                    $position = 'client';

                    // include script in HTML
                    $this->client->after($position, '<script src="'.esc_url($script_url).'"'.(($media && $media !== 'all') ? ' media="'.esc_attr($media).'"' : '').'></script>');
                }
            }
        }

        // load async
        if ($async) {
            if (!empty($async_scripts)) {
                
                // async list
                $async_list = array();
                $async_ref_list = array(); // debug ref list

                // concat index list
                $concat_index = array();

                // type prefixes
                $hash_type_prefixes = array(
                    'url' => 1,
                    'proxy' => 2
                );

                foreach ($async_scripts as $script) {
                    if (!$script) {
                        continue;
                    }

                    // load position
                    $load_position = ($script['load_position'] && $script['load_position'] !== $this->load_position) ? $script['load_position'] : false;
                    if ($load_position) {
                        $load_position = ($load_position === 'timing') ? $this->client->config_index('key', 'timing') : 0;
                    }
                    if ($script['load_position'] && $script['load_position'] === 'timing') {
                        $load_timing = ($script['load_timing'] && $script['load_timing'] !== $this->load_timing) ? $script['load_timing'] : false;
                    } else {
                        $load_timing = false;
                    }

                    // async exec
                    $exec_timing = ($sheet['exec_timing'] && $sheet['exec_timing'] !== $this->exec_timing) ? $sheet['exec_timing'] : false;

                    // hash type prefix
                    $hash_type_prefix = (isset($hash_type_prefixes[$script['type']])) ? $hash_type_prefixes[$script['type']] : false;

                    // add concat index position
                    if ($script['type'] === 'concat') {
                        $concat_index[] = count($async_list);
                    }

                    // async script object
                    $async_script = array();

                    // add hash prefix
                    if ($hash_type_prefix) {
                        $async_script[] = $hash_type_prefix;
                    }

                    // script URL or hash
                    $async_script[] = $script['url'];

                    $index = count($async_script);
                    $async_script[] = null; // load position
                    $async_script[] = null; // render timing
                    $async_script[] = null; // localStorage

                    // load config
                    if ($load_position) {
                        if ($load_timing) {
                            $async_script[$index] = array($load_position, $this->timing_config($load_timing));
                        } else {
                            $async_script[$index] = $load_position;
                        }
                    }

                    // render config
                    if ($render_timing) {
                        $async_script[($index + 1)] = $this->timing_config($render_timing);
                    }

                    // custom localStorage config
                    if (isset($script['localStorage'])) {
                        if (is_array($script['localStorage'])) {
                            $localStorage = array();
                            $config_keys = array('max_size','expire','update_interval');
                            foreach ($config_keys as $key) {
                                $localStorage[$this->client->config_index('js', 'localStorage_'.$key)] = $script['localStorage'][$key];
                            }

                            if ($script['localStorage']['head_update']) {
                                $localStorage[$this->client->config_index('js', 'localStorage_head_update')] = $script['localStorage']['head_update'];
                            }

                            $async_script[($index + 2)] = $localStorage;
                        } else {
                            $async_script[($index + 2)] = ($script['localStorage']) ? 1 : 0;
                        }
                        
                        // load client module
                        $this->client->load_module('localstorage');
                    }

                    $value_set = false;
                    for ($i = count($async_script); $i >= $index; $i--) {
                        if ($async_script[$i] !== null) {
                            $value_set = true;
                        } else {
                            if (!$value_set) {
                                unset($async_script[$i]);
                            } else {
                                $async_script[$i] = '__O10N_NULL__';
                            }
                        }
                    }

                    // add to async list
                    $async_list[] = $async_script;

                    if ($debug) {
                        $async_ref_list[$script['url']] = $script['original_url'];
                    }
                }

                //return var_export($async_ref_list, true);
                // add async list to client
                $this->client->set_config('js', 'async', $async_list);

                // add CDN config to client
                if ($this->script_cdn) {
                    $cdn_config = array();
                    $cdn_config[$this->client->config_index('key', 'url')] = rtrim($this->script_cdn[0], '/ ');
                    if (isset($this->script_cdn[1]) && $this->script_cdn[1]) {
                        $cdn_config[$this->client->config_index('key', 'mask')] = $this->script_cdn[1];
                    }
                    $this->client->set_config('js', 'cdn', $cdn_config);
                }

                // add references
                if ($debug) {
                    $this->client->set_config('js', 'debug_ref', $async_ref_list);
                }

                // add concat index to client
                if (count($async_list) === count($concat_index)) {
                    $this->client->set_config('js', 'concat', 1); // concat only
                } else {
                    $this->client->set_config('js', 'concat', $concat_index); // concat indexes
                }
            }
        }

        // add rel="preload"

        foreach ($script_urls as $script) {
            if (!$script) {
                continue;
            }

            // rel="preload" as="script"
            if (isset($script['rel_preload']) && $script['rel_preload']) {
                if (isset($script['exec_timing']) && !is_null($script['exec_timing'])) {
                    $exec_timing = $script['exec_timing'];
                } else {
                    $exec_timing = $this->exec_timing;
                }

                // position in document
                $position = ($script['load_position'] === 'footer') ? 'footer' : 'critical-css';

                if ($exec_timing && $exec_timing['type'] === 'media' && isset($exec_timing['media'])) {
                    $media = $exec_timing['media'];
                } else {
                    $media = false;
                }

                $this->client->after($position, '<link rel="preload" as="script" href="'.esc_url($script['url']).'"'.(($media) ? ' media="'.esc_attr($media).'"' : '').'>');
            }
        }
        
        return $HTML;
    }

    /**
     * Search and replace strings in script
     *
     * To enable different minification settings per page, any settings that modify the script before minification should be used in the hash.
     *
     * @param  string $resource Resource
     * @return string MD5 hash for resource
     */
    final public function js_filters($script)
    {

        // initiate search & replace config
        if ($this->replace === null) {

            // script Search & Replace config
            $replace = $this->options->get('js.replace');
            if (!$replace || empty($replace)) {
                $this->replace = false;
            } else {
                $this->replace = array(
                    'search' => array(),
                    'replace' => array(),
                    'search_regex' => array(),
                    'replace_regex' => array()
                );
                
                foreach ($replace as $object) {
                    if (!isset($object['search']) || trim($object['search']) === '') {
                        continue;
                    }

                    if (isset($object['regex']) && $object['regex']) {
                        $this->replace['search_regex'][] = $object['search'];
                        $this->replace['replace_regex'][] = $object['replace'];
                    } else {
                        $this->replace['search'][] = $object['search'];
                        $this->replace['replace'][] = $object['replace'];
                    }
                }
            }
        }

        // apply search & replace filter
        if ($this->replace) {

            // apply string search & replace
            if (!empty($this->replace['search'])) {
                $script = str_replace($this->replace['search'], $this->replace['replace'], $script);
            }

            // apply regular expression search & replace
            if (!empty($this->replace['search_regex'])) {
                try {
                    $script = @preg_replace($this->replace['search_regex'], $this->replace['replace_regex'], $script);
                } catch (\Exception $err) {
                    // @todo log error
                }
            }
        }

        return $script;
    }

    /**
     * Extract scripts from HTML
     *
     * @param  string $HTML HTML source
     * @return array  Extracted scripts
     */
    final private function extract($HTML)
    {

        // extracted script elements
        $this->script_elements = array();

        // minify
        $minify = $this->options->bool('js.minify');

        // async
        $async = $this->options->bool('js.async');

        // proxy
        $proxy = $this->options->bool('js.proxy');

        // concat
        $concat = $minify && $this->options->bool('js.minify.concat');

        if ($concat) {
            $concat_inline = $this->options->bool('js.minify.concat.inline');

            // filter
            if ($this->options->bool('js.minify.concat.inline.filter')) {
                $concat_inline_filterType = $this->options->get('js.minify.concat.inline.filter.type');
                $concat_inline_filter = $this->options->get('js.minify.concat.inline.filter.' . $concat_inline_filterType);
                if (empty($concat_inline_filter)) {
                    $concat_inline_filter = false;
                }
            } else {
                $concat_inline_filter = false;
            }
        } else {
            $concat_inline = false;
        }

        // replace href
        $replaceSrc = false;

        // pre url filter
        if ($this->options->bool('js.url_filter')) {
            $url_filter = $this->options->get('js.url_filter.config');
            if (empty($url_filter)) {
                $url_filter = false;
            }
        } else {
            $url_filter = false;
        }

        // minify filter
        if ($minify && $this->options->bool('js.minify.filter')) {
            $minify_filterType = $this->options->get('js.minify.filter.type');
            $minify_filter = $this->options->get('js.minify.filter.' . $minify_filterType, array());
        } else {
            $minify_filter = false;
        }

        // async filter
        if ($async && $this->options->bool('js.async.filter')) {
            $this->async_filterType = $this->options->get('js.async.filter.type');
            $this->async_filter = $this->options->get('js.async.filter.config', array());
        } else {
            $this->async_filter = false;
        }

        // proxy filter
        if ($proxy) {
            $proxy_filter = $this->options->get('js.proxy.include', array());
        } else {
            $proxy_filter = false;
        }

        // script regex
        // @todo optimize
        $script_regex = '#(<\!--\[if[^>]+>\s*)?<script([^>]*)>((.*?)</script>)?#smi';
        
        if (preg_match_all($script_regex, $HTML, $out)) {
            foreach ($out[0] as $n => $scriptTag) {

                // conditional, skip
                if (trim($out[1][$n]) !== '' || strpos($out[2][$n], 'data-o10n') !== false) {
                    continue 1;
                }

                // script
                $script = array(
                    'tag' => $scriptTag,
                    'minify' => $minify,
                    'async' => $async
                );

                // inline script text
                $text = $out[4][$n];
                if ($text) {
                    $text = trim($text);
                }

                // attributes
                $attributes = trim($out[2][$n]);

                // verify type
                $type = strpos($attributes, 'type');
                if ($type !== false) {
                    $type = $this->attr_regex('type', $attributes);
                    if ($type) {
                        $type = trim(strtolower($type));
                        if (strpos($type, 'application/javascript') !== false || strpos($type, 'text/javascript') !== false) {
                            // OK
                        } else {
                            // invalid type, ignore script
                            continue 1;
                        }
                    }
                }

                // verify if tag contains src
                $src = strpos($attributes, 'src');
                if ($src !== false) {
                    // extract src using regular expression
                    $src = $this->attr_regex('src', $attributes);
                    if (!$src) {
                        continue 1;
                    }

                    $script['src'] = $src;
                    if ($text) {
                        $script['text'] = $text;
                    }
                } elseif ($concat_inline && $text) {
                    $textx = $text;

                    // inline script

                    // apply script filter pre processing
                    $filteredText = apply_filters('o10n_script_text_pre', $text, $script['tag']);

                    // ignore script
                    if ($filteredText === 'ignore') {
                        continue 1;
                    }

                    // delete script
                    if ($filteredText === 'delete') {
                        
                        // delete from HTML
                        $this->output->add_search_replace($script['tag'], '');
                        continue 1;
                    }

                    // replace script
                    if (!is_null($filteredText) && $filteredText !== $text) {
                        $text = $filteredText;
                    }

                    // strip CDATA
                    if (stripos($text, 'cdata') !== false) {
                        $text = preg_replace('#^.*<!\[CDATA\[(?:\s*\*/)?(.*)(?://|/\*)\s*?\]\]>.*$#smi', '$1', $text);
                    }

                    // apply inline filter
                    if ($concat_inline_filter) {
                        $do_concat = $this->tools->filter_list_match($script['tag'], $concat_inline_filterType, $concat_inline_filter);
                        if (!$do_concat) {
                            continue 1;
                        }
                    }

                    $script['inline'] = true;
                    $script['text'] = $text;
                } else {
                    // do not process
                    continue 1;
                }

                // apply pre url filter
                if ($url_filter) {
                    foreach ($url_filter as $rule) {
                        if (!is_array($rule)) {
                            continue 1;
                        }

                        // match
                        $match = true;
                        if (isset($rule['regex']) && $rule['regex']) {
                            try {
                                if (!preg_match($rule['url'], $src)) {
                                    $match = false;
                                }
                            } catch (\Exception $err) {
                                $match = false;
                            }
                        } else {
                            if (strpos($src, $rule['url']) === false) {
                                $match = false;
                            }
                        }
                        if (!$match) {
                            continue 1;
                        }

                        // ignore script
                        if (isset($rule['ignore'])) {
                            continue 2; // next script
                        }

                        // delete script
                        if (isset($rule['delete'])) {
                            
                            // delete from HTML
                            $this->output->add_search_replace($script['tag'], '');
                            continue 2; // next script
                        }

                        // replace script
                        if (isset($rule['replace'])) {
                            $script['src'] = $rule['replace'];
                            $script['replaceSrc'] = true;
                        }
                    }
                }

                // apply custom script filter pre processing
                $filteredSrc = apply_filters('o10n_script_src_pre', $src, $script['tag']);

                // ignore script
                if ($filteredSrc === 'ignore') {
                    continue 1;
                }

                // delete script
                if ($filteredSrc === 'delete') {

                    // delete from HTML
                    $this->output->add_search_replace($script['tag'], '');
                    continue 1;
                }

                // replace href
                if ($filteredSrc !== $script['src']) {
                    $script['src'] = $filteredSrc;
                    $script['replaceSrc'] = true;
                }

                // apply script minify filter
                if ($minify && $minify_filter) {
                    $script['minify'] = $this->tools->filter_list_match($script['tag'], $minify_filterType, $minify_filter);
                }

                // apply script async filter
                if ($async && $this->async_filter !== false) {
                    
                    // apply filter
                    $asyncConfig = $this->tools->filter_config_match($script['tag'], $this->async_filter, $this->async_filterType);

                    // filter config object
                    if ($asyncConfig && is_array($asyncConfig)) {

                        // async enabled by filter
                        if (!isset($asyncConfig['async']) || $asyncConfig['async']) {
                            $script['async'] = true;

                            // custom load position
                            if (isset($asyncConfig['load_position']) && $asyncConfig['load_position'] !== $this->load_position) {
                                $script['load_position'] = $asyncConfig['load_position'];
                            }

                            if (isset($asyncConfig['load_position']) && $asyncConfig['load_position'] === 'timing' && isset($asyncConfig['load_timing'])) {
                                $script['load_timing'] = $asyncConfig['load_timing'];
                            }

                            // custom async exec
                            if (isset($asyncConfig['exec_timing'])) {
                                $script['exec_timing'] = $asyncConfig['exec_timing'];
                            }

                            // custom rel_preload
                            if (isset($asyncConfig['rel_preload']) && $asyncConfig['rel_preload'] !== $this->rel_preload) {
                                $script['rel_preload'] = $asyncConfig['rel_preload'];
                            }

                            // custom localStorage
                            if (isset($asyncConfig['localStorage'])) {
                                $script['localStorage'] = $asyncConfig['localStorage'];
                            }

                            // custom minify
                            if (isset($asyncConfig['minify'])) {
                                $script['minify'] = $asyncConfig['minify'];
                            }
                            
                            // custom minifier
                            if (isset($asyncConfig['minifier'])) {
                                $script['minifier'] = $asyncConfig['minifier'];
                            }
                        } elseif (!$asyncConfig['async']) {
                            $script['async'] = false;
                        }
                    } elseif ($asyncConfig === true) {

                        // include by default
                        $script['async'] = true;
                    } elseif (!$asyncConfig) {

                        // include by default
                        $script['async'] = false;
                    }
                }

                // apply script proxy filter
                if (!$script['minify'] && $proxy && !$this->url->is_local($script['src'], false, false)) {

                    // apply filter
                    $script_proxy = ($proxy_filter) ? $this->tools->filter_list_match($script['tag'], 'include', $proxy_filter) : $proxy;

                    // proxy script
                    if ($script_proxy) {

                        // proxify URL
                        $proxyResult = $this->proxy->proxify('js', $script['src']);

                        // proxy href
                        if ($proxyResult[0] && $proxyResult[1] !== $script['src']) {
                            $script['proxy'] = array($proxyResult[0],$script['src']);
                            $script['src'] = $proxyResult[1];
                            $script['replaceSrc'] = true;
                        }
                    }
                }

                $this->script_elements[] = $script;
            }
        }

        // minify scripts
        if (!empty($this->script_elements) && $minify) {
            $this->minify_scripts();
        }
    }

    /**
     * Minify extracted scripts
     */
    final private function minify_scripts()
    {
        // walk extracted script elements
        foreach ($this->script_elements as $n => $script) {
            $cache_file_hash = $proxy_file_meta = false;

            if (isset($script['inline']) && $script['inline']) {
                $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                $base_href = $url;
                if (substr($base_href, -1) === '/') {
                    $base_href .= 'page.html';
                }
                $urlhash = md5($url . $script['text']);
                $file_hash = md5($script['text']);
                $scriptText = $script['text'];
                $local = false;
            } else {

                // minify hash
                $urlhash = $this->minify_hash($script['src']);

                // detect local URL
                $local = $this->url->is_local($script['src']);

                // local URL, verify change based on content hash
                if ($local) {

                    // get local file hash
                    $file_hash = md5_file($local);
                } else { // remote URL

                    // invalid prefix
                    if (!$this->url->valid_protocol($script['src'])) {
                        continue 1;
                    }

                    // try cache
                    if ($this->cache->exists('js', 'src', $urlhash) && (!$this->options->bool('js.minify.clean-js.sourceMap') || $this->cache->exists('js', 'src', $urlhash, false, '.js.map'))) {

                        // verify content
                        $proxy_file_meta = $this->proxy->meta('js', $script['src']);
                        $cache_file_hash = $this->cache->meta('js', 'src', $urlhash, true);

                        if ($proxy_file_meta && $cache_file_hash && $proxy_file_meta[2] === $cache_file_hash) {

                            // preserve cache file based on access
                            $this->cache->preserve('js', 'src', $urlhash, (time() - 3600));
                       
                            // add minified path
                            $this->script_elements[$n]['minified'] = array($urlhash,$cache_file_hash);

                            // update content in background using proxy (conditionl HEAD request)
                            $this->proxy->proxify('js', $script['src']);
                            continue 1;
                        }
                    }
                    
                    // download script using proxy
                    try {
                        $scriptData = $this->proxy->proxify('js', $script['src'], 'filedata');
                    } catch (HTTPException $err) {
                        $scriptData = false;
                    } catch (Exception $err) {
                        $scriptData = false;
                    }

                    // failed to download file or file is empty
                    if (!$scriptData) {
                        continue 1;
                    }

                    // file hash
                    $file_hash = $scriptData[1][2];
                    $scriptText = $scriptData[0];
                }
            }

            // get content hash
            $cache_file_hash = ($cache_file_hash) ? $cache_file_hash : $this->cache->meta('js', 'src', $urlhash, true);

            if ($cache_file_hash === $file_hash) {
                
                // preserve cache file based on access
                $this->cache->preserve('js', 'src', $urlhash, (time() - 3600));

                // add minified path
                $this->script_elements[$n]['minified'] = array($urlhash, $file_hash);

                continue 1;
            }
            
            // load script source from local file
            if ($local) {
                $scriptText = trim(file_get_contents($local));
                if ($scriptText === '') {

                    // file is empty, remove
                    $this->output->add_search_replace($script['tag'], '');

                    // store script
                    $this->cache->put(
                        'js',
                        'src',
                        $urlhash,
                        '',
                        false, // suffix
                        false, // gzip
                        false, // opcache
                        $file_hash, // meta
                        true // meta opcache
                    );

                    continue 1;
                }
            }

            // minify disabled
            if (!isset($script['minify']) || !$script['minify']) {

                // entry
                $this->script_elements[$n]['minified'] = array($urlhash,$file_hash);

                // store stylesheet
                $cache_file_path = $this->cache->put(
                    'js',
                    'src',
                    $urlhash,
                    $scriptText,
                    false, // suffix
                    false, // gzip
                    false, // opcache
                    $file_hash, // meta
                    true // meta opcache
                );
                continue 1;
            }

            // apply script filters before processing
            $scriptText = $this->js_filters($scriptText);

            // target src cache dir
            $target_src_dir = $this->file->directory_url('js/src/' . $this->cache->hash_path($urlhash), 'cache', true);

            // script source
            $sources = array();
            // $this->extract_filename($script['src'])
            //$script['src'] = preg_replace(array('#\?.*$#i'), array(''), $script['src']);
            $script['src'] = $this->extract_filename($script['src']);

            $sources[$script['src']] = array(
                'text' => $scriptText
            );

            try {
                $minified = $this->minify($sources, $target_src_dir, ((isset($script['minifier'])) ? $script['minifier'] : $this->minifier));
            } catch (Exception $err) {
                // @todo
                // handle minify failure, prevent overload
                $minified = false;
            } catch (\Exception $err) {
                // @todo
                // handle minify failure, prevent overload
                $minified = false;
            }            // test

            // minified script
            if ($minified) {
               
                // footer
                // @todo add option to add debug information
                //$minified['text'] .= "\n/* @src ".$script['src']." */";

                if (isset($script['inline']) && $script['inline']) {
                    $this->script_elements[$n]['text'] = $minified['text'];
                }

                // store script
                $cache_file_path = $this->cache->put(
                    'js',
                    'src',
                    $urlhash,
                    $minified['text'],
                    false, // suffix
                    false, // gzip
                    false, // opcache
                    $file_hash, // meta
                    true // meta opcache
                );

                // add link to source map
                if (isset($minified['sourcemap'])) {
                    
                    // add link to script
                    $minified['text'] .= "\n/*# sourceMappingURL=".basename($cache_file_path).".map */";

                    // update script cache
                    try {
                        $this->file->put_contents($cache_file_path, $minified['text']);
                    } catch (\Exception $e) {
                        throw new Exception('Failed to store script ' . $this->file->safe_path($cache_file_path) . ' <pre>'.$e->getMessage().'</pre>', 'config');
                    }

                    // apply filters
                    $minified['sourcemap'] = $this->minified_sourcemap_filter($minified['sourcemap']);

                    // store source map
                    try {
                        $this->file->put_contents($cache_file_path . '.map', $minified['sourcemap']);
                    } catch (\Exception $e) {
                        throw new Exception('Failed to store script source map ' . $this->file->safe_path($cache_file_path . '.map') . ' <pre>'.$e->getMessage().'</pre>', 'config');
                    }
                }

                // entry
                $this->script_elements[$n]['minified'] = array($urlhash,$file_hash);
            } else {

                // minification failed
                $this->script_elements[$n]['minified'] = false;
            }
        }
    }

    /**
     * Minify scripts
     */
    final private function minify($sources, $target, $minifier)
    {
        $this->last_used_minifier = $minifier;

        // concat sources
        $script = '';
        foreach ($sources as $source) {
            $script .= ' ' . $source['text'];
        }

        // remove persistent comments
        if ($this->options->bool('js.minify.comments.remove_important.enabled')) {
            $script = str_replace('/*!', '/*', $script);
        }

        switch ($minifier) {
            case "jshrink":

                // load library
                if (!class_exists('\JShrink\Minifier')) {
                    require_once $this->core->modules('js')->dir_path() . 'lib/JShrink.php';
                }

                // minify
                try {
                    $minified = \JShrink\Minifier::minify($script);
                } catch (\Exception $err) {
                    throw new Exception('JShrink failed: ' . $err->getMessage(), 'js');
                }

                if (!$minified && $minified !== '') {
                    throw new Exception('JShrink failed: unknown error', 'js');
                }

            break;
            case "closure-compiler-service":

                // load library
                if (!class_exists('\O10n\ClosureCompilerService')) {
                    require_once $this->core->modules('js')->dir_path() . 'lib/ClosureCompilerService.php';
                }
                if (is_null($this->ClosureCompilerService)) {
                    $this->ClosureCompilerService = new ClosureCompilerService;
                }

                if ($this->options->bool('js.minify.fallback.enabled')) {
                    $timeout = $this->options->bool('js.minify.fallback.timeout');
                    if (!$timeout || !is_numeric($timeout)) {
                        $timeout = 60;
                    }
                } else {
                    $timeout = 60;
                }

                $options = array();
                if ($this->options->bool('js.minify.closure-compiler-service.options.compilation_level.enabled')) {
                    $options['compilation_level'] = $this->options->get('js.minify.closure-compiler-service.options.compilation_level.level');
                }

                if ($this->options->bool('js.minify.closure-compiler-service.options.externs_url.enabled')) {
                    $options['externs_url'] = $this->options->get('js.minify.closure-compiler-service.options.externs_url.files');
                }

                if ($this->options->bool('js.minify.closure-compiler-service.options.exclude_default_externs')) {
                    $options['exclude_default_externs'] = true;
                }

                if ($this->options->bool('js.minify.closure-compiler-service.options.formatting.enabled')) {
                    $options['formatting'] = $this->options->get('js.minify.closure-compiler-service.options.formatting.format');
                }

                if ($this->options->bool('js.minify.closure-compiler-service.options.use_closure_library')) {
                    $options['use_closure_library'] = true;
                }

                // minify
                try {
                    $minified = $this->ClosureCompilerService->minify($script, $options, $timeout);
                } catch (Exception $err) {
                    if ($this->options->bool('js.minify.fallback.enabled')) {
                        return $this->minify($sources, $target, $this->options->get('js.minify.fallback.minifier'));
                    } else {
                        throw new Exception('Closure Compiler failed: ' . $err->getMessage(), 'js');
                    }
                }

                if (!$minified && $minified !== '') {
                    throw new Exception('Closure Compiler failed: unknown error', 'js');
                }

            break;
            case "custom":

                // minify
                try {
                    $minified = apply_filters('o10n_js_custom_minify', $script);
                } catch (\Exception $err) {
                    throw new Exception('Custom Javascript minifier failed: ' . $err->getMessage(), 'js');
                }

                if (!$minified && $minified !== '') {
                    throw new Exception('Custom Javascript minifier failed: unknown error', 'js');
                }

            break;
            case "jsmin":
            default:

                // load library
                if (!class_exists('\JSMin\JSMin')) {
                    require_once $this->core->modules('js')->dir_path() . 'lib/JSMin.php';
                }

                // minify
                try {
                    $minified = \JSMin\JSMin::minify($script);
                } catch (\Exception $err) {
                    throw new Exception('PHP JSMin failed: ' . $err->getMessage(), 'js');
                }
                if (!$minified && $minified !== '') {
                    throw new Exception('PHP JSMin failed: unknown error', 'js');
                }

            break;

        }

        return array('text' => $minified);
    }

    /**
     * Return filename
     * @todo
     */
    final private function extract_filename($src)
    {
        //$basename = basename($src);
        $basename = str_replace('http://abtf.local', '', $src);
        if (strpos($basename, '?') !== false) {
            return explode('?', $basename)[0];
        }

        return $basename;
    }

    /**
     * Extract attribute from tag
     *
     * @param  string $attributes HTML tag attributes
     * @param  string $replace    src value to replace
     * @return string src or modified tag
     */
    final private function attr_regex($param, $attributes, $replace = false)
    {
        // detect if tag has src
        $srcpos = strpos($attributes, $param);
        if ($srcpos !== false) {
            $param_quote = preg_quote($param);

            // regex
            $char = substr($attributes, ($srcpos + 4), 1);
            if ($char === '"' || $char === '\'') {
                $char = preg_quote($char);
                $regex = '#('.$param_quote.'\s*=\s*'.$char.')([^'.$char.']+)('.$char.')#Usmi';
            } elseif ($char === ' ' || $char === "\n") {
                $regex = '#('.$param_quote.'\s*=\s*["|\'])([^"|\']+)(["|\'])#Usmi';
            } else {
                $attributes .= '>';
                $regex = '#('.$param_quote.'\s*=)([^\s>]+)(\s|>)#Usmi';
            }

            // return param
            if (!$replace) {

                // match param
                if (!preg_match($regex, $attributes, $out)) {
                    return false;
                }

                return ($out[2]) ? $this->url->translate_protocol($out[2]) : $out[2];
            }

            // replace param in tag
            $attributes = preg_replace($regex, '$1' . $replace . '$3', $attributes);
        }

        return ($replace) ? $attributes : false;
    }

    /**
     * Apply script CDN or HTTP/@ Server Push to url
     *
     * @param  string $url Stylescript URL
     * @return string href or modified tag
     */
    final private function url_filter($url)
    {

        // apply HTTP/2 Server Push
        if ($this->http2_push) {

            // apply script CDN
            $cdn_url = false;
            if ($this->http2_push_cdn) {
                $cdn_url = $this->url->cdn($url, $this->script_cdn);
                if ($cdn_url === $url) {
                    $cdn_url = false;
                } else {
                    $url = $cdn_url;
                }
            }

            if (Core::get('http2')->push($url, 'script', false, $this->http2_push, ($cdn_url ? null : true))) {

                // return original URL that has been pushed
                return $url;
            }

            // return CDN url
            if ($this->http2_push_cdn) {
                return $url;
            }
        }

        // apply script CDN
        return $this->url->cdn($url, $this->script_cdn);
    }

    /**
     * Apply filters to minified sourcemap
     *
     * @param  string $json Sourcemap JSON
     * @return string Filtered sourcemap JSON
     */
    final private function minified_sourcemap_filter($json)
    {

        // fix relative paths
        if (strpos($json, '../') !== false || strpos($json, '"wp-') !== false) {
            $json = preg_replace('#"(\../)*wp-(includes|admin|content)/#s', '"'.$this->url->root_path().'wp-$2/', $json);
        }

        return $json;
    }

    /**
     * Return resource minification hash
     *
     * To enable different minification settings per page, any settings that modify the script before minification should be used in the hash.
     *
     * @param  string $resource Resource
     * @return string MD5 hash for resource
     */
    final public function minify_hash($resource)
    {
        
        // return default hash
        return md5($resource);
    }
    
    /**
     * Sanitize group filter
     */
    final public function sanitize_filter($concat_filter)
    {
        if (!is_array($concat_filter) || empty($concat_filter)) {
            $concat_filter = array();
        }

        // sanitize groups by key reference
        $sanitized_groups = array();
        foreach ($concat_filter as $filter) {
            if (!isset($filter['match']) || empty($filter['match'])) {
                continue;
            }

            if (isset($filter['group']) && isset($filter['group']['key'])) {
                $sanitized_groups[$filter['group']['key']] = $filter;
            } else {
                $sanitized_groups[] = $filter;
            }
        }

        return $sanitized_groups;
    }

    /**
     * Apply filter
     */
    final public function apply_filter(&$concat_group, &$concat_group_settings, $tag, $concat_filter)
    {
        if (!is_array($concat_filter)) {
            throw new Exception('Concat group filter not array.', 'core');
        }

        $filter_set = false; // group set flag
        
        // match group filter list
        foreach ($concat_filter as $key => $filter) {

            // verify filter config
            if (!is_array($filter) || empty($filter) || (!isset($filter['match']) && !isset($filter['match_regex']))) {
                continue 1;
            }

            // exclude rule
            $exclude_filter = (isset($filter['exclude']) && $filter['exclude']);

            // string based match
            if (isset($filter['match']) && !empty($filter['match'])) {
                foreach ($filter['match'] as $match_string) {
                    $exclude = false;
                    $regex = false;

                    // filter config
                    if (is_array($match_string)) {
                        $exclude = (isset($match_string['exclude'])) ? $match_string['exclude'] : false;
                        $regex = (isset($match_string['regex'])) ? $match_string['regex'] : false;
                        $match_string = $match_string['string'];
                    }

                    // group set, just apply exclude filters
                    if ($filter_set && !$exclude && !$exclude_filter) {
                        continue 1;
                    }

                    if ($regex) {
                        $match = false;
                        try {
                            if (@preg_match($match_string, $tag)) {

                                // exclude filter
                                if ($exclude || $exclude_filter) {
                                    $concat_group = false;

                                    return;
                                }

                                $match = true;
                            }
                        } catch (\Exception $err) {
                            $match = false;
                        }

                        if ($match) {

                            // match, assign to group
                            $concat_group = md5(json_encode($filter));
                            if (!isset($concat_group_settings[$concat_group])) {
                                $concat_group_settings[$concat_group] = array();
                            }
                            $concat_group_settings[$concat_group] = array_merge($filter, $concat_group_settings[$concat_group]);
                            
                            $filter_set = true;
                        }
                    } else {
                        if (strpos($tag, $match_string) !== false) {

                            // exclude filter
                            if ($exclude || $exclude_filter) {
                                $concat_group = false;

                                return;
                            }

                            // match, assign to group
                            $concat_group = md5(json_encode($filter));
                            if (!isset($concat_group_settings[$concat_group])) {
                                $concat_group_settings[$concat_group] = array();
                            }
                            $concat_group_settings[$concat_group] = array_merge($filter, $concat_group_settings[$concat_group]);

                            $filter_set = true;
                        }
                    }
                }
            }
        }
    }

    /**
     * Return concat hash path for async list
     *
     * @param  string $hash Hash key for concat stylesheet
     * @return string Hash path for async list.
     */
    final public function async_hash_path($hash)
    {
        // get index id
        $index_id = $this->cache->index_id('js', 'concat', $hash);

        if (!$index_id) {
            throw new Exception('Failed to retrieve concat hash index ID.', 'text');
        }
        if (is_array($index_id)) {
            $suffix = $index_id[1];
            $index_id = $index_id[0];
        } else {
            $suffix = false;
        }

        // return hash path
        return str_replace('/', '|', $this->cache->index_path($index_id)) . $index_id . (($suffix) ? ':' . $suffix : '');
    }

    /**
     * Return timing config
     *
     * @param   array   Timing config
     * @return array Client compressed timing config
     */
    final private function timing_config($config)
    {
        if (!$config || !is_array($config) || !isset($config['type'])) {
            return false;
        }


        // init config with type index
        $timing_config = array($this->client->config_index('key', $config['type']));

        // timing config
        switch (strtolower($config['type'])) {
            case "requestanimationframe":
                
                // frame
                $frame = (isset($config['frame']) && is_numeric($config['frame'])) ? $config['frame'] : 1;
                if ($frame > 1) {
                    $timing_config[1] = array();
                    $timing_config[1][$this->client->config_index('key', 'frame')] = $frame;
                }
            break;
            case "requestidlecallback":
                
                // timeout
                $timeout = (isset($config['timeout']) && is_numeric($config['timeout'])) ? $config['timeout'] : '';
                if ($timeout) {
                    $timing_config[1] = array();
                    $timing_config[1][$this->client->config_index('key', 'timeout')] = $timeout;
                }

                // setTimeout fallback
                $setTimeout = (isset($config['setTimeout']) && is_numeric($config['setTimeout'])) ? $config['setTimeout'] : '';
                if ($setTimeout) {
                    if (!isset($timing_config[1])) {
                        $timing_config[1] = array();
                    }
                    $timing_config[1][$this->client->config_index('key', 'setTimeout')] = $setTimeout;
                }
            break;
            case "inview":

                // selector
                $selector = (isset($config['selector'])) ? trim($config['selector']) : '';
                if ($selector !== '') {
                    $timing_config[1] = array();
                    $timing_config[1][$this->client->config_index('key', 'selector')] = $selector;
                }

                // offset
                $offset = (isset($config['offset']) && is_numeric($config['offset'])) ? $config['offset'] : 0;
                if ($offset > 0) {
                    if (!isset($timing_config[1])) {
                        $timing_config[1] = array();
                    }
                    $timing_config[1][$this->client->config_index('key', 'offset')] = $offset;
                }
            break;
            case "media":

                // media query
                $media = (isset($config['media'])) ? trim($config['media']) : '';
                if ($media !== '') {
                    $timing_config[1] = array();
                    $timing_config[1][$this->client->config_index('key', 'media')] = $media;
                }
            break;
        }

        return $timing_config;
    }
}

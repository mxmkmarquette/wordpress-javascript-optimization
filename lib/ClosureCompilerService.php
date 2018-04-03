<?php

namespace O10n;

/**
 * Class Minify_JS_ClosureCompiler
 * @package Minify
 */

/**
 * Minify Javascript using Google's Closure Compiler API
 *
 * @link http://code.google.com/closure/compiler/
 * @package Minify
 * @author Stephen Clay <steve@mrclay.org>
 *
 * @todo can use a stream wrapper to unit test this?
 */
class ClosureCompilerService
{

    // service URL
    private $api_endpoint = 'https://closure-compiler.appspot.com/compile';

    /**
     * The maximum POST byte size accepted by the API
     * @link https://developers.google.com/closure/compiler/docs/api-ref
     */
    private $max_bytes = 200000;

    private $default_options = array(
        'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
        'warning_level' => 'QUIET',
        'language' => 'ECMASCRIPT6',
        'language_out' => 'ECMASCRIPT5'
    );

    private $output_options = array(
        'output_format' => 'json'
    );

    // construct the library
    final public function __construct()
    {
        // apply max bytes filter
        $this->max_bytes = apply_filters('o10n_closure_compiler_max_bytes', $this->max_bytes);
    }

    // minify
    final public function minify($JS, $options, $timeout = 60)
    {
        if (!is_array($options)) {
            $options = array();
        }

        // build API post request
        $api_post = array_merge(
            $this->default_options,
            $options,
            $this->output_options, // override options
            array(
                'js_code' => $JS,
                'output_info' => 'compiled_code'
            )
        );

        // verify size
        if ($this->max_bytes > 0) {
            $postBody = http_build_query($api_post);
            $bytes = (function_exists('mb_strlen') && ((int)ini_get('mbstring.func_overload') & 2))
                ? mb_strlen($postBody, '8bit')
                : strlen($postBody);
            if ($bytes > $this->max_bytes) {
                throw new Exception('Closure Compiler: POST content larger than ' . $this->max_bytes . ' bytes (API limit)', 'js');
            }
        }

        // send request to API
        $response = $this->send_request($api_post, $timeout);

        if (!isset($response['compiledCode'])) {
            throw new Exception('Closure Compiler: invalid response', 'js');
        }

        // retrieve errors
        if (isset($response['compiledCode']) && $response['compiledCode'] === '') {
            $api_post['output_info'] = 'errors';
            $response = $this->send_request($api_post, $timeout);

            if (isset($response['errors']) && !empty($response['errors'])) {
                $errors = '';
                foreach ($response['errors'] as $error) {
                    $errors .= '<li>Line: '.$error['lineno'].' Char '.$error['charno'].' Type: '.$error['type'].' Error: '.((isset($error['error'])) ? $error['error'] : ((isset($error['warning'])) ? $error['warning'] : '')).'</li>';
                }
                throw new Exception('Closure Compiler: <ol>' . $errors . '</ol>', 'js');
            } else {

                // empty JS
                return '';
            }
        }

        return $response['compiledCode'];
    }

    /**
     * Send request to API
     */
    final private function send_request($api_post, $timeout = 60)
    {
        $request = array(
            'method' => 'POST',
            'timeout' => $timeout,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => $api_post
        );

        // perform request
        $response = wp_remote_post(
            $this->api_endpoint,
            $request
        );
        
        // process response
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            if (!$response_code) {
                $message = 'no response from server';
                $body = false;
            } else {
                switch ($response_code) {
                    default:
                        $message = 'status ' . $response_code;
                        $body = wp_remote_retrieve_response_message($response);
                        if (trim($body) !== '') {
                            if (strlen($body) < 500) {
                                $body = substr($body, 0, 500) . '...';
                            }
                            $message .= ' Result: ' . $body;
                        }
                    break;
                }
            }
            $this->last_error = array(
                'code' => $response_code,
                'body' => $body
            );
            throw new Exception('Closure Compiler API request failed: <pre>Status: '.$response_code.' Message: ' . $message . '</pre>', 'api');
        }

        $response = wp_remote_retrieve_body($response);

        try {
            $response = json_decode($response, true);
        } catch (\Exception $err) {
            throw new Exception('Closure Compiler: failed to parse JSON response: ' . $err->getMessage(), 'js');
        }

        if (!is_array($response)) {
            throw new Exception('Closure Compiler: invalid response: ', 'js');
        }

        return $response;
    }
}

# Documentation
 
Documentation for the WordPress plugin Javascript Optimization.

**The plugin is in beta. Please submit your feedback on the [Github forum](https://github.com/o10n-x/wordpress-javascript-optimization/issues).**

The plugin provides in a advanced Javascript optimization toolkit. Minify, concat/merge, async loading, advanced editor, ES Lint, UglifyJS (professional), beautifier and more.

Additional features can be requested on the [Github forum](https://github.com/o10n-x/wordpress-javascript-optimization/issues).

## Getting started

1. [Javascript Code Optimization](#javascript-code-optimization)
2. [Javascript Delivery Optimization](#javascript-delivery-optimization)

**Note:** *The full configuration of this plugin can be managed via JSON. The configuration is based on [JSON schemas](https://github.com/o10n-x/wordpress-o10n-core/tree/master/schemas).*

# Javascript Code Optimization

Javascript code optimization consists of two main parts: minification and concatenation. Minification compresses Javascript to reduce the size while concatenation merges multiple scripts into a single scripts for faster download performance.

## Javascript Minify

The plugin provides the option to minify Javascript code using multiple Javascript minifiers including [JSMin](https://github.com/mrclay/jsmin-php) (PHP), [Google Closure Compiler API](https://github.com/google/closure-compiler) and the option to use a custom minifier using a WordPress filter that enables to use any solution, including a Amazon Lambda or Google Cloud function with Node.js based CSS optimization software.

You can control which scripts are included in the minification by enabling the filter option. 

#### Javascript Minify Filter

When the filter is enabled you can choose the filter mode `Include List` or `Exclude List`. The Include List option excludes all scripts by default and only minifies scripts on the list, while the Exclude List option includes all scripts by default except the scripts on the list.

The filter list accepts parts of HTML script elements which makes it possible to match based on both Javascript code and HTML attributes such as `id="script-id"`.

#### Custom Javascript minifier

The Custom Minifier option enables to use any Javascript minifier via the WordPress filter hook `o10n_css_custom_minify`.

```php
/* Custom Javascript minifier */
add_filter('o10n_js_custom_minify', function ($JS) {

    // apply Javascript optimization
    exec('/node /path/to/optimize-js.js /tmp/javascript-source.js');
    $minified = file_get_contents('/tmp/output.js');

    // alternative
    $minified = JSCompressor::minify($JS);

    return $minified;

});
```

## Javascript Concat

The plugins provides advanced functionality to concatenate scripts. It includes the option to create concat groups that enables to bundle scripts and to extract and concatenate inline scripts.

You can chose the option `Minify` to concatenate scripts using the Javascript minifier. By default scripts are simply bundled in their original format, which could be their minified version when using Javascript minification. Advanced Javascript optimization software such as Google Closure Compiler (`Advanced Optimizations` mode) may be able to remove duplicate Javascript in concatenated scripts using this option.

#### Concat Group Filter

The group filter enables to create bundles of scripts. The configuration is an array of JSON objects. Each object is a concat group and contains the required properties `match` (scripts to match) and `group` (object with group details).

![Concat Group Filter Editor](https://github.com/o10n-x/wordpress-javascript-optimization/blob/master/docs/images/concat-group-javascript.png)

`match` is an array with strings or JSON objects. The JSON object format contains the required property `string` (match string) and optional properties `regex` (boolean to enable regular expression match) and `exclude` (exclude from group). The match list determines which scripts are included in the group.

`group` is an object with the required properties `title` and `key` and the optional property `id` (an HTML attribute ID to add to the script element). The `key` property is used in the file path which enables to recognize the concat group source file, e.g. `/cache/o10n/css/concat/1:group-key.css`. 

`minify` is a boolean that enables or disables the PHP minifier for concatenation.

`dependency` is a string or array of strings to match script URLs or concat group keys for dependency based loading.

`exclude` is a boolean that determines if the group should exclude scripts from minification.

#### Example Concat Group Configuration

```json
[
  {
    "match": [
      {
        "string": "jquery",
        "regex": true
      }
    ],
    "group": {
      "title": "jquery",
      "key": "jquery"
    },
    "minify": true
  },
  {
    "match": [
      {
        "string": "/.*/",
        "regex": true
      }
    ],
    "group": {
      "title": "Global",
      "key": "global"
    },
    "dependency": "jquery",
    "minify": true
  }
]
```

<details/>
  <summary>JSON schema for Javascript Concat Group Filter config</summary>

```json
{
	"filter": {
        "title": "Concatenation groups",
        "type": "object",
        "properties": {
            "enabled": {
                "title": "Script Concat",
                "type": "boolean",
                "default": true
            },
            "type": {
                "title": "Default include/exclude",
                "type": "string",
                "enum": [
                    "include",
                    "exclude"
                ],
                "default": "include"
            },
            "config": {
                "title": "Concatenation group filter",
                "type": "array",
                "items": {
                    "title": "Script merge group configuration",
                    "type": "object",
                    "properties": {
                        "group": {
                            "title": "Concat group configuration",
                            "type": "object",
                            "properties": {
                                "title": {
                                    "title": "A title for the group",
                                    "type": "string",
                                    "minLength": 1
                                },
                                "key": {
                                    "title": "A group reference key used in the file path.",
                                    "type": "string",
                                    "minLength": 1
                                },
                                "id": {
                                    "title": "An id attribute for the stylesheet element.",
                                    "type": "string",
                                    "minLength": 1
                                }
                            },
                            "required": ["title", "key"],
                            "additionalProperties": false
                        },
                        "match": {
                            "title": "An array of strings to match script elements.",
                            "type": "array",
                            "items": {
                                "oneOf": [{
                                    "title": "A string to match a script element.",
                                    "type": "string",
                                    "minLength": 1
                                }, {
                                    "title": "Filter config object",
                                    "type": "object",
                                    "properties": {
                                        "string": {
                                            "title": "A string to match a script element.",
                                            "type": "string",
                                            "minLength": 1
                                        },
                                        "regex": {
                                            "type": "boolean",
                                            "enum": [true]
                                        },
                                        "exclude": {
                                            "type": "boolean",
                                            "enum": [true]
                                        }
                                    },
                                    "required": ["string"],
                                    "additionalProperties": false
                                }]

                            },
                            "uniqueItems": true
                        },
                        "minify": {
                            "title": "Use minifier for concatenation.",
                            "type": "boolean",
                            "default": true
                        },
                        "minifier": {
                            "$ref": "js-minify.json#/definitions/minifiers"
                        },
                        "fallback_minifier": {
                            "$ref": "js-minify.json#/definitions/fallback_minifier"
                        },
                        "dependency": {
                            "title": "Dependency configuration",
                            "oneOf": [{
                                "title": "A string to match a script URL or contact group key.",
                                "type": "string",
                                "minLength": 1
                            }, {
                                "title": "An array of script URLs or contact group keys.",
                                "type": "array",
                                "items": {
                                    "title": "A string to match a script URL or contact group key.",
                                    "type": "string"
                                },
                                "uniqueItems": true
                            }]
                        },
                        "exclude": {
                            "title": "Exclude from concatenation",
                            "type": "boolean",
                            "enum": [true]
                        }
                    },
                    "required": ["match"],
                    "additionalProperties": false
                },
                "uniqueItems": true
            }
        },
        "additionalProperties": false
    }
}
```
</details>

---

**Note:** The plugin creates short Javascript URLs by using a hash index. This means that the first concatenated script will have the filename `1.js`. The CDN option with CDN mask enables to load the scripts from `https://cdn.tld/1.js` resulting in the shortest URL possible.

When you use automated concatenation and the content of scripts change on each request, the hash index could grow to a big number. You can reset the hash index from the admin bar menu under `Javascript Cache`. When you clear the Javascript cache, the hash index is reset to 0.


# Javascript Delivery Optimization

Javascript delivery optimization enables asynchronous loading of scripts. The plugin provides in many options and unique innovations to achieve the best Javascript loading performance.

**Note** You can enable debug modus by adding `define('O10N_DEBUG', true);` to wp-config.php. The browser console will show details about Javascript loading and a [Performance API](https://developer.mozilla.org/nl/docs/Web/API/Performance) result for each step of the loading and script execution process.

## Async loading

The plugin provides an option to load scripts asynchronous using [little-loader](https://github.com/walmartlabs/little-loader) enhanced with timed and responsive loading and execution and an option to use `localStorage` cache for improved performance.

When using `rel="preload" as="script"` the scripts are always downloaded by the browser and the plugin will provide in a polyfill for browsers that do not support rel="preload". If you prefer to load scripts from localStorage, it may be best to not use rel="preload". When using debug modus, the Performance API result can provide an insight into what method provides the best loading performance for your website.

#### Async Load Config Filter

The async load config filter enables to fine tune async load configuration for individual scripts or concat groups.

![Async Load Config Filter](https://github.com/o10n-x/wordpress-javascript-optimization/blob/master/docs/images/async-load-config.png)

`match` is a string or regular expression to match a script URL.

`regex` is a boolean to enable regular expression based matching.

`async` is a boolean to enable or disable async loading for the matched script.

`rel_preload` is a boolean to enable or disable `rel="preload" as="style"` based loading of the script.

`load_position` is a string with the two possible values `header` and `timing`. The option header will instantly start loading the script in the header (on javascript startup time) and timing will enable the `load_timing` option for further configuration.

`load_timing` is an object consisting of the required property `type` and optional timing method related properties. The following timing method types are currently available:

* `domReady`
* `requestIdleCallback`
	* `timeout` optionally, a timeout in milliseconds to force loading of the script.
	* `setTimeout` optionally, a time in milliseconds for a setTimeout fallback for browsers that do not support requestIdleCallback. 
* `requestAnimationFrame`
	* `frame` the frame target (default is frame 1)
* `inview`
	* `selector` the Javascript selector of the element to watch.
	* `offset` an offset in pixels from the element to trigger loading of the script.
* `media`
	* `media` the Media Query to trigger loading of the script.

`exec_timing` is an object consisting of the required property `type` and optional timing method related properties (see `load_timing`). 

Exec timing differs from load timing as it only affects the actual script execution in the browser. For optimization it also enables to start downloading scripts on domReady while executing them based on a timing method, e.g. when an element scrolls into view.

`localStorage` is a boolean or an object consisting of the properties `max_size`, `update_interval`, `expire` and `head_update`. The max_size property enables to restrict the cache to scripts with a size below a trheshold. The expire property enables to set a expire time in seconds. The update_interval enables to define a time in seconds to update the cache in the background (a Web Worker) and the head_update property is a boolean to define if a conditional HEAD request should be used to verify of script has been modified, to save bandwith.

#### Example Async Load Configuration

```json
[
  {
    "match": "/concat-group-(x|y)/",
    "regex": true,
    "async": true,
    "rel_preload": true,
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
}
]
```

<details/>
  <summary>JSON schema for Javascript Concat Group Filter config</summary>

```json
{
	"config": {
        "type": "array",
        "items": {
            "title": "Stylesheet filter configuration",
            "type": "object",
            "properties": {
                "match": {
                    "title": "A string or regular expression to match a stylesheet URL or group key.",
                    "type": "string",
                    "minLength": 1
                },
                "regex": {
                    "title": "Use regular expression match",
                    "type": "boolean",
                    "enum": [
                        true
                    ]
                },
                "media": {
                    "title": "Apply custom media query for responsive preloading.",
                    "type": "string",
                    "minLength": 1
                },
                "async": {
                    "title": "Load stylesheet async (include/exclude)",
                    "type": "boolean",
                    "default": true
                },
                "minify": {
                    "title": "Minify stylesheet",
                    "type": "boolean",
                    "default": true
                },
                "minifier": {
                    "$ref": "css-minify.json#/definitions/minifiers"
                },
                "rel_preload": {
                    "title": "Load stylesheet using rel=preload",
                    "type": "boolean",
                    "default": false
                },
                "noscript": {
                    "title": "Add fallback stylesheets via <noscript>",
                    "type": "boolean",
                    "default": false
                },
                "load_position": {
                    "title": "Load position of CSS",
                    "type": "string",
                    "enum": ["header", "footer", "timing"],
                    "default": "header"
                },
                "load_timing": {
                    "$ref": "timed-exec.json#/definitions/timingMethods"
                },
                "render_timing": {
                    "$ref": "timed-exec.json#/definitions/timingMethods"
                },
                "localStorage": {
                    "title": "Override stylesheet cache configuration",
                    "oneOf": [{
                        "type": "boolean"
                    }, {
                        "type": "object",
                        "properties": {
                            "max_size": {
                                "title": "Maximum size of stylesheet to store in cache.",
                                "type": "number",
                                "minimum": 1
                            },
                            "update_interval": {
                                "title": "Interval to update the cache.",
                                "type": "number",
                                "minimum": 1
                            },
                            "expire": {
                                "title": "Expire time in seconds.",
                                "type": "number",
                                "minimum": 1
                            },
                            "head_update": {
                                "title": "Use HTTP HEAD request to update cache based on etag / last-modified headers.",
                                "type": "boolean",
                                "default": true
                            }
                        },
                        "anyOf": [{
                            "required": ["max_size"]
                        }, {
                            "required": ["update_interval"]
                        }, {
                            "required": ["expire"]
                        }, {
                            "required": ["head_update"]
                        }],
                        "additionalProperties": false
                    }]
                }
            },
            "required": ["match", "async"],
            "additionalProperties": false
        },
        "uniqueItems": true
    }
}
```
</details>

---




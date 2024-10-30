<?php

namespace AuRise\Plugin\CrontrolHours;

defined('ABSPATH') || exit; // Exit if accessed directly

use \DateTime;

/**
 * Class Utilities
 *
 * Utility functions used by this plugin.
 *
 * @package AuRise\Plugin\CrontrolHours
 *
 * - Debugging
 * -- debug_log()
 * -- var_dump()
 * -- server_timing()
 *
 * - Caching
 * -- refresh_cache()
 * -- cache_prefix()
 * -- set_cache()
 * -- get_cache()
 *
 * - Metadata
 * -- set_meta()
 * -- get_meta()
 * -- sanitize_meta_id()
 * -- validate_meta_type()
 * -- sanitize_meta_key()
 *
 * - Array Manipulation
 * -- array_has_key()
 *
 * - String Manipulation
 * -- normalize_html()
 * -- remove_excess_whitespace()
 * -- remove_html_comments()
 * -- format_atts()
 * -- implode_surround()
 * -- json_decode()
 * -- discover_shortcodes()
 * -- discover_blocks()
 *
 * - Resource Management
 * -- optionally_load_resource()
 *
 * - Date & Time Manipulation
 * -- duration_to_iso8601()
 *
 * - CRON
 * -- get_cron_hooks()
 */
class Utilities
{

    //**** Debugging Functions ****/

    /**
     * Write a message to debug.log
     *
     * Uses `error_log` and only works if WP_DEBUG and WP_DEBUG_LOG are true.
     *
     * @since 1.0.0
     *
     * @static
     *
     * @param mixed $obj the variable to dump
     * @param string $title Optional. Provide a title to display before the variable dump.
     *
     * @return void
     */
    public static function debug_log($obj, $title = '')
    {
        if (WP_DEBUG && WP_DEBUG_LOG) {
            if ($title) {
                error_log('[' . Settings::$vars['name'] . '] ' . esc_html($title));
            }
            if (is_array($obj) || is_object($obj)) {
                error_log('[' . Settings::$vars['name'] . '] ' . print_r($obj, true));
            } else {
                error_log('[' . Settings::$vars['name'] . '] ' . $obj);
            }
        }
    }

    //**** Array Manipulation ****/

    /**
     * Array Key Exists and Has Value
     *
     * @since 1.0.0
     *
     * @static
     *
     * @param string|int $key The key to search for in the array.
     * @param array $array The array to search.
     * @param mixed $default The default value to return if not found or is empty. Default is an empty string.
     *
     * @return mixed|null The value of the key found in the array if it exists or the value of `$default` if not found or is empty.
     */
    public static function array_has_key($key, $array = array(), $default = '')
    {
        //Check if this key exists in the array
        $valid_key = (is_string($key) && !empty(sanitize_text_field($key))) || is_numeric($key);
        $valid_array = is_array($array) && count($array);
        if ($valid_key && $valid_array && array_key_exists($key, $array)) {
            //Always return if it's a boolean or number, otherwise only return if it's truthy
            if (is_bool($array[$key]) || is_numeric($array[$key]) || $array[$key]) {
                return $array[$key];
            }
        }
        return $default;
    }

    //**** String Manipulation ****/

    /**
     * Format Attributes Array to String
     *
     * Can be used for shortcode attributes and form HTML fields.
     *
     * @since 1.0.0
     *
     * @static
     *
     * @param array $atts An associative array of key/value pairs to convert
     * @param string $key_prefix Optional. A string to prepend to every key
     *
     * @return string A string formatted as `%s="%s"` for every attribute separated by a space
     */
    public static function format_atts($atts = array(), $key_prefix = '')
    {
        if (is_array($atts) && count($atts)) {
            $output = array();
            foreach ($atts as $key => $value) {
                $type = gettype($value);
                $key = strtolower(trim($key_prefix . $key));
                switch ($type) {
                    case 'string':
                        if (stripos($value, 'http') === 0) {
                            $output[] = sprintf('%s="%s"', esc_attr($key), esc_url($value, array('https', 'http')));
                        } else {
                            $output[] = sprintf('%s="%s"', esc_attr($key), esc_attr($value));
                        }
                        break;
                    case 'integer':
                        $output[] = sprintf('%s="%s"', esc_attr($key), esc_attr($value));
                        break;
                    case 'array':
                    case 'object':
                        $output[] = sprintf('%s="%s"', esc_attr($key), esc_attr(http_build_query($value)));
                        break;
                    default:
                        break;
                }
            }
            return implode(' ', $output);
        }
        return '';
    }

    /**
     * Implode an array into a string with surrounding strings.
     *
     * Commonly used for creating Table HTML.
     *
     * @since 1.0.0
     *
     * @param array $array A sequential to be imploded.
     * @param string $glue Optional. The "glue" for imploding the array. Default is a comma (`,`).
     * @param string $before Optional. The string to place before every item. Default is an empty string.
     * @param string $after Optional. The string to place after every item. Default is an empty string.
     *
     * @return string Imploded array, with each item separated by `$glue`, preceeeded by `$before`, and followed by `$after`.
     */
    public static function implode_surround($array = array(), $glue = ',', $before = '', $after = '')
    {
        return $before . implode($after . $glue . $before, $array) . $after;
    }

    /**
     * Decodes JSON strings.
     *
     * Includes error handling.
     *
     * @since 1.0.0
     *
     * @static
     *
     * @param string $json The JSON string to attempt to decode.
     * @param mixed $default Optional. The default return object when parsing the JSON object fails. Default is the original JSON string.
     *
     * @return array|string|mixed An array of the decoded JSON string on success. The value of `$default` on failure.
     */
    public static function json_decode($json, $default = 'error_message')
    {
        $error_message = array();
        if (is_string($json) && $json) {
            try {
                $json = json_decode($json, true); //This returns an associative array
                if (!is_array($json)) {
                    $error_message['error'] = 'The decoded JSON object was not an array';
                    $error_message['output'] = $json;
                }
            } catch (\Exception $e) {
                $error_message['error'] = 'Error parsing the JSON. See message below.';
                $error_message['error_message'] = $e->getMessage();
                $error_message['input'] = $json;
                //Global error state (if not using JSON_THROW_ON_ERROR in json_decode() function)
                switch (json_last_error()) {
                    case JSON_ERROR_NONE:
                        $error_message['global_error'] = 'No errors';
                        break;
                    case JSON_ERROR_DEPTH:
                        $error_message['global_error'] = 'Maximum stack depth exceeded';
                        break;
                    case JSON_ERROR_STATE_MISMATCH:
                        $error_message['global_error'] = 'Underflow or the modes mismatch';
                        break;
                    case JSON_ERROR_CTRL_CHAR:
                        $error_message['global_error'] = 'Unexpected control character found';
                        break;
                    case JSON_ERROR_SYNTAX:
                        $error_message['global_error'] = 'Syntax error, malformed JSON';
                        break;
                    case JSON_ERROR_UTF8:
                        $error_message['global_error'] = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                        break;
                    default:
                        $error_message['global_error'] = 'Unknown error';
                        break;
                }
            }
        } else {
            $error_message['error'] = 'The $json parameter was not a string or it was empty';
            $error_message['input'] = $json;
        }
        if (count($error_message)) {
            self::debug_log($error_message, 'JSON Error Messages');
            if ($default === 'error_message') {
                return $error_message;
            }
            return $default;
        }
        return $json;
    }

    //**** CRON ****/

    /**
     * Get CRON Hooks
     *
     * @since 1.1.0
     *
     * @return array A sequential array of strings on success. Empty array otherwise.
     */
    public static function get_cron_hooks()
    {
        $hooks = array();
        $crons = _get_cron_array(); //Not ideal...
        if (is_array($crons) && count($crons)) {
            foreach ($crons as $timestamp => $cron) {
                $hooks[] = array_key_first($cron);
            }
            $hooks = array_unique(array_filter($hooks));
        }
        return $hooks;
    }
}

if (!function_exists('array_key_first')) {
    /**
     * Gets the first key of an array
     *
     * Gets the first key of the given array without affecting the internal array pointer.
     * This function is added for compatibility with PHP versions prior to 7.3.0
     *
     * @since 1.0.0
     *
     * @param array $array The array to search.
     *
     * @return int|string|null Returns the first key of array if the array is not empty; null otherwise.
     */
    function array_key_first($array = array())
    {
        if (is_array($array) && count($array)) {
            foreach ($array as $key => $value) {
                return $key;
            }
        }
        return null;
    }
}

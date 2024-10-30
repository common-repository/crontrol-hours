<?php

namespace AuRise\Plugin\CrontrolHours;

defined('ABSPATH') || exit; // Exit if accessed directly

use \DateTime;
use \DateTimeZone;
use AuRise\Plugin\CrontrolHours\Utilities;

/**
 * Plugin Settings File
 *
 * @package AuRise\Plugin\CrontrolHours
 */
class Settings
{
    /**
     * The single instance of the class.
     *
     * @var Settings
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Plugin variables of settings and options
     *
     * @var array $vars
     *
     * @since 1.0.0
     */
    public static $vars = array();

    /**
     * Main Instance
     *
     * Ensures only one instance of is loaded or can be loaded.
     *
     * @since 1.0.0
     *
     * @static
     *
     * @return Settings Main instance.
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct()
    {
        $basename = plugin_basename(CRONTROLHOURS_FILE); // E.g.: "plugin-folder/file.php"
        $slug = sanitize_key(dirname($basename)); // E.g.: "plugin-folder"
        $slug_underscore = str_replace('-', '_', $slug); // E.g.: "plugin_folder"
        load_plugin_textdomain($slug); // Load translations if available

        self::$vars = array(

            // Plugin Info
            'name' => __('Crontrol Hours', 'crontrol-hours'),
            'version' => CRONTROLHOURS_VERSION,
            'capability_post' => 'edit_post', // Capability for editing posts
            'capability_settings' => 'manage_options', // Capability for editing plugin options

            // Paths and URLs
            'file' => CRONTROLHOURS_FILE,
            'basename' => $basename, // E.g.: "plugin-folder/file.php"
            'path' => plugin_dir_path(CRONTROLHOURS_FILE), // E.g.: "/path/to/wp-content/plugins/plugin-folder/"
            'url' => plugin_dir_url(CRONTROLHOURS_FILE), // E.g.: "https://domain.com/wp-content/plugins/plugin-folder/"
            'admin_url' => admin_url(sprintf('tools.php?page=%s', $slug)), // URL under "Tools" e.g.: "https://domain.com/wp-admin/tools.php?page=plugin-folder"
            'slug' => $slug, // E.g.: "plugin-folder"
            'slug_underscore' => $slug_underscore, // E.g.: "plugin_folder"
            'prefix' => $slug_underscore . '_', // E.g.: "plugin_folder_"
            'group' => $slug . '-group', // E.g.: "plugin-folder-group"

            //Plugin-specific Settings
            'hook' => $slug_underscore,
            'hooks' => array(
                $slug_underscore, // Base hook
                $slug_underscore . '_reschedule_restricted' // Stop restricted events
            ),
            'schedule' => 1,

            // Plugin Options
            'options' => array(
                //Default Settings Group
                'settings' => array(
                    'title' => __('Settings', 'crontrol-hours'),
                    'options' => array(
                        'start_time' => array(
                            'label' => __('Start Time', 'crontrol-hours'),
                            'description' => sprintf('Recurring CRON events may start at this time or after.', 'crontrol-hours',),
                            'default' => '20:00',
                            'global_override' => true, //Allow to be overriden by constant variable in wp-config.php
                            'private' => false, //Don't display value in dashboard if set in wp-config.php
                            'atts' => array(
                                'type' => 'time'
                            )
                        ),
                        'end_time' => array(
                            'label' => __('End Time', 'crontrol-hours'),
                            'description' => sprintf('Recurring CRON events must not start after this time.', 'crontrol-hours',),
                            'default' => '04:00',
                            'global_override' => true, //Allow to be overriden by constant variable in wp-config.php
                            'private' => false, //Don't display value in dashboard if set in wp-config.php
                            'atts' => array(
                                'type' => 'time'
                            )
                        ),
                        'duration' => array(
                            // Internal use only - determine duration between start and end time
                            'default' => '',
                            'atts' => array('type' => 'hidden')
                        ),
                        'intervals' => array(
                            'label' => __('Intervals', 'crontrol-hours'),
                            'description' => sprintf('Comma separated list of recurring intervals to check for', 'crontrol-hours',),
                            'default' => 'daily,weekly,monthly',
                            'global_override' => true, //Allow to be overriden by constant variable in wp-config.php
                            'private' => false, //Don't display value in dashboard if set in wp-config.php
                            'atts' => array(
                                'type' => 'text',
                                'placeholder' => 'daily,weekly,monthly'
                            )
                        ),
                        'exclude_hooks' => array(
                            'label' => __('Hooks to Exclude', 'crontrol-hours'),
                            'description' => sprintf('Comma separated list of CRON hooks to exclude from being updated.', 'crontrol-hours',),
                            'default' => '',
                            'global_override' => true, //Allow to be overriden by constant variable in wp-config.php
                            'private' => false, //Don't display value in dashboard if set in wp-config.php
                            'atts' => array(
                                'type' => 'text',
                                'placeholder' => ''
                            )
                        ),
                        'force_daily' => array(
                            'label' => __('Force Daily', 'crontrol-hours'),
                            'description' => sprintf('Update events that run multiple times a day to only run once a day.', 'crontrol-hours',),
                            'default' => '0',
                            'global_override' => true, //Allow to be overriden by constant variable in wp-config.php
                            'private' => false, //Don't display value in dashboard if set in wp-config.php
                            'atts' => array(
                                'type' => 'switch',
                                'options' => array(
                                    '0' => array(
                                        'label' => __('Off', 'accessible-reading')
                                    ),
                                    '1' => array(
                                        'label' => __('On', 'accessible-reading')
                                    )
                                )
                            )
                        ),
                        'restrict_frequent' => array(
                            'label' => __('Restrict Frequent', 'crontrol-hours'),
                            'description' => sprintf('Restrict events that run multiple times a day to only run between the daily start and end times while maintaining their specified intervals.', 'crontrol-hours',),
                            'default' => '0',
                            'global_override' => true, //Allow to be overriden by constant variable in wp-config.php
                            'private' => false, //Don't display value in dashboard if set in wp-config.php
                            'atts' => array(
                                'type' => 'switch',
                                'options' => array(
                                    '0' => array(
                                        'label' => __('Off', 'accessible-reading')
                                    ),
                                    '1' => array(
                                        'label' => __('On', 'accessible-reading')
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );

        //Plugin Setup
        add_action('admin_init', array($this, 'register_settings')); // Register plugin settings
        add_action('admin_menu', array($this, 'admin_menu')); // Add admin page link in WordPress dashboard
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets_admin')); // Enqueue assets for admin page(s)
        add_filter('plugin_action_links_' . $basename, array($this, 'plugin_links')); // Customize links on listing on plugins page

        //Custom Plugin Setup
        add_action('update_option_' . self::$vars['prefix'] . 'start_time', array($this, 'update_duration'), 10, 0); //Update duration if start time is modified
        add_action('update_option_' . self::$vars['prefix'] . 'end_time', array($this, 'update_duration'), 10, 0); //Update duration if end time is modified
        add_action('update_option_' . self::$vars['prefix'] . 'restrict_frequent', array($this, 'update_restricted_events'), 10, 0); // Add/remove CRON events if this is enabled/disabled
    }

    //**** Plugin Settings ****//

    /**
     * Register Plugin Settings
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_settings()
    {
        foreach (self::$vars['options'] as $option_group_name => $group) {
            $option_group = self::$vars['prefix'] . $option_group_name;
            //Register the section
            add_settings_section(
                $option_group, //Slug-name to identify the section. Used in the `id` attribute of tags.
                $group['title'], //Formatted title of the section. Shown as the heading for the section.
                array($this, 'display_plugin_setting_section'), //Function that echos out any content at the top of the section (between heading and fields).
                self::$vars['slug'] //The slug-name of the settings page on which to show the section.
            );

            //Register the individual settings in the section
            foreach ($group['options'] as $setting_name => $setting_data) {
                $option_name = self::$vars['prefix'] . $setting_name;
                $input_type = $setting_data['atts']['type'];
                $registration_args = array();
                switch ($input_type) {
                    case 'switch':
                    case 'checkbox':
                        $type = 'integer';
                        $registration_args['sanitize_callback'] = array($this, 'sanitize_setting_bool');
                        break;
                    case 'number':
                        $type = 'number';
                        $registration_args['sanitize_callback'] = array($this, 'sanitize_setting_number');
                        break;
                    case 'text':
                        $type = 'string';
                        if (strpos(Utilities::array_has_key('class', $setting_data['atts']), 'au-color-picker') !== false) {
                            $registration_args['sanitize_callback'] = array($this, 'sanitize_setting_color');
                        } else {
                            $registration_args['sanitize_callback'] = 'sanitize_text_field';
                        }
                        break;
                    default:
                        $type = 'string';
                        $registration_args['sanitize_callback'] = 'sanitize_text_field';
                        break;
                }
                $registration_args['type'] = $type; //Valid values are string, boolean, integer, number, array, and object
                $registration_args['description'] = $setting_name;
                $registration_args['default'] = Utilities::array_has_key('default', $setting_data);

                //Register the setting
                register_setting($option_group, $option_name, $registration_args);

                //Add the field to the admin settings page (excluding the hidden ones)
                if ($input_type != 'hidden') {
                    $input_args = array(
                        'type' => $input_type,
                        'type_option' => 'string', //Option type
                        'default' => $registration_args['default'],
                        'label' => $setting_data['label'],
                        'description' => Utilities::array_has_key('description', $setting_data),
                        'global' => Utilities::array_has_key('global_override', $setting_data) ? strtoupper($option_name) : '', //Name of constant variable should it exist
                        'private' => Utilities::array_has_key('private', $setting_data),
                        'label_for' => $option_name,
                        //Attributes for the input field
                        'atts' => array(
                            'type' => $input_type,
                            'name' => $option_name,
                            'id' => $option_name,
                            'value' => get_option($option_name, $registration_args['default']), //The currently selected value (or default if not selected)
                            'class' => Utilities::array_has_key('class', $setting_data['atts']),
                            'data-default' => $registration_args['default']
                        )
                    );
                    if (Utilities::array_has_key('required', $setting_data['atts'])) {
                        $input_args['atts']['required'] = 'required';
                    }
                    //Add data attributes
                    $data_atts = Utilities::array_has_key('data', $setting_data['atts'], array());
                    if (count($data_atts)) {
                        foreach ($data_atts as $data_key => $data_value) {
                            $input_args['atts']['data-' . $data_key] = $data_value;
                        }
                    }
                    switch ($input_type) {
                        case 'select':
                            $input_args['options'] = $setting_data['atts']['options'];
                            break;
                        case 'switch':
                            $input_args['label_for'] .= '_check';
                            $input_args['reverse'] = Utilities::array_has_key('reverse', $setting_data['atts']);
                            if (array_key_exists('options', $setting_data['atts']) && is_array($setting_data['atts']['options']) && count($setting_data['atts']['options']) === 2) {
                                $input_args['no'] = $setting_data['atts']['options'][0]['label'];
                                $input_args['yes'] = $setting_data['atts']['options'][1]['label'];
                            } else {
                                $input_args['no'] =  __('Off', 'crontrol-hours');
                                $input_args['yes'] =  __('On', 'crontrol-hours');
                            }
                            //Purposely not breaking here
                        case 'checkbox':
                        case 'radio':
                            $input_args['checked'] = checked(1, $input_args['atts']['value'], false);
                            break;
                        case 'number':
                        case 'time':
                            $input_args['atts']['min'] = Utilities::array_has_key('min', $setting_data['atts']);
                            $input_args['atts']['max'] = Utilities::array_has_key('max', $setting_data['atts']);
                            $input_args['atts']['step'] = Utilities::array_has_key('step', $setting_data['atts']);
                            //Purposely not breaking here
                        default:
                            $input_args['atts']['placeholder'] =  esc_attr(Utilities::array_has_key('placeholder', $setting_data['atts']));
                            break;
                    }
                    add_settings_field(
                        $option_name, //ID
                        esc_attr($setting_data['label']), //Title
                        array($this, 'display_plugin_setting'), //Callback (should echo its output)
                        self::$vars['slug'], //Page
                        $option_group, //Section
                        $input_args //Attributes
                    );
                }
            }
        }
    }

    /**
     * Sanitize plugin options for boolean fields
     *
     * @since 1.0.0
     *
     * @param string $value Value to sanitize.
     *
     * @return int Returns `1` if the value is truthy, `0` otherwise.
     */
    public function sanitize_setting_bool($value)
    {
        return $value ? 1 : 0;
    }

    /**
     * Sanitize plugin options for number fields
     *
     * @since 1.0.0
     *
     * @param string $value Value to sanitize.
     *
     * @return int|float|string The numeric value or an empty string.
     */
    public function sanitize_setting_number($value)
    {
        return is_numeric($value) ? $value : '';
    }

    /**
     * Register Plugin Setting Section Callback
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function display_plugin_setting_section()
    {
        // Do nothing
    }

    /**
     * Display plugin setting input in admin dashboard
     *
     * Callback for `add_settings_field()`
     *
     * @since 1.0.0
     *
     * @param array $args Input arguments.
     *
     * @return void
     */
    public function display_plugin_setting($args = array())
    {
        /**
         * Variables that are already escaped:
         * type, name, id, value, label, required, global, private, checked, min, max, step, placeholder
         */
        $note = '';
        if ($args['global'] && defined($args['global'])) {
            //Display constant values set in wp-config.php
            if ($args['private'] || $args['type'] == 'password') {
                // This field is readonly, do not reveal the value
                printf(
                    '<input %s />',
                    Utilities::format_atts(array_replace($args['atts'], array(
                        'readonly' => 'readonly',
                        'disabled' => 'disabled',
                        'type' => 'password',
                        'value' => '**********'
                    )))
                );
            } else {
                // This field is readonly
                printf(
                    '<input %s />',
                    Utilities::format_atts(array_replace($args['atts'], array(
                        'readonly' => 'readonly',
                        'disabled' => 'disabled',
                        'type' => 'text',
                        'value' => constant($args['global'])
                    )))
                );
            }
            $note .= sprintf(__('<strong>This value is being overwritten by <code>%s</code></strong>', 'crontrol-hours'), $args['global']);
        } else {
            //Render the setting
            switch ($args['type']) {
                case 'hidden':
                    //Silence is golden
                    break;
                case 'switch': //Fancy Toggle Checkbox Switch
                    $checkbox_args = array(
                        'type' => 'checkbox',
                        'id' => $args['atts']['id'] . '_check',
                        'name' => $args['atts']['name'] . '_check',
                        'class' => 'input-checkbox'
                    );
                    if ($args['reverse']) {
                        $checkbox_args['class'] .= ' reverse-checkbox'; //whether checkbox should be visibly reversed
                    }
                    printf(
                        '<span class="checkbox-switch %6$s"><input %1$s /><input %2$s %3$s /><span class="checkbox-animate"><span class="checkbox-off">%4$s</span><span class="checkbox-on">%5$s</span></span></span></label>',
                        Utilities::format_atts(array_replace($args['atts'], array('type' => 'hidden'))), // 1 - Hidden input field
                        Utilities::format_atts(array_replace($args['atts'], $checkbox_args)), // 2 - Visible checkbox field
                        checked(($args['reverse'] ? '0' : '1'), $args['atts']['value'], false), //3 - Checked attribute, if reversed, compare against the opposite value
                        esc_attr($args['no']), //4 - on value
                        esc_attr($args['yes']), //5 - off value
                        esc_attr($args['atts']['class']) //6 - additional classes to wrapper object
                    );
                    break;
                case 'select':
                    printf('<select %s />', Utilities::format_atts($args['atts']));
                    foreach ($args['options'] as $key => $value) {
                        $option_name = is_array($value) ? $value['label'] : $value;
                        $option_atts = array('value' => $key);
                        if ($args['atts']['value'] == $key) {
                            $option_atts['selected'] = 'selected';
                        }
                        printf(
                            '<option %s>%s</option>',
                            Utilities::format_atts($option_atts),
                            esc_html($option_name)
                        );
                    }
                    echo ('</select>');
                    break;
                case 'textarea':
                    $textarea = $args['atts'];
                    unset($textarea['type'], $textarea['value']); // Not used in textarea inputs
                    printf('<textarea %s>%s</textarea>', Utilities::format_atts($textarea), esc_html($args['atts']['value']));
                    break;
                default:
                    printf('<input %s />', Utilities::format_atts($args['atts']));
                    break;
            }
            if ($args['global']) {
                $note .= sprintf(__('This value can be overwritten by defining <code>%s</code> as a global variable.', 'crontrol-hours'), $args['global']);
            }
        }
        if ($note || $args['description']) {
            printf('<small class="note">%s</small>', wp_kses(
                $args['description'] . '&nbsp;' . $note,
                array(
                    'a' => array('href' => array(), 'title' => array(), 'target' => array(), 'rel' => array()),
                    'strong' => array(),
                    'em' => array(),
                    'code' => array()
                ),
                array('https')
            ));
        }
    }

    /**
     * Get Option Key for Settings
     *
     * @since 1.1.0
     *
     * @static
     *
     * @return string $id With or without a prefix, get the option name and ID
     *
     * @return array an associative array with `id` and `name` properties
     */
    private static function get_key($id)
    {
        $return = array(
            'id' => '',
            'name' => ''
        );
        if (strpos($id, self::$vars['prefix']) === 0) {
            //Prefix is included
            $return['id'] = $id; //No change, keep prefix in ID
            $return['name'] = str_replace(self::$vars['prefix'], '', $id); //Remove prefix from name
        } else {
            //Prefix is not included
            $return['name'] = $id; //No change, no prefix in name
            $return['id'] = self::$vars['prefix'] . $id; //Add prefix to ID
        }
        return $return;
    }

    /**
     * Get Plugin Setting
     *
     * This checks if a constant value was defined to override it and returns that.
     *
     * @since 1.0.0
     *
     * @static
     *
     * @param string $id Option ID, including prefix
     * @param bool $value_only If true, returns just the value of the setting. Otherwise, it returns an associatve array. Default is true.
     *
     * @return string|array An associative array with the keys `value` and `constant` unless $value_only was true, then it returns just the value.
     */
    public static function get($id, $value_only = true, $group = '')
    {
        $return = array(
            'value' => '',
            'constant' => false,
            'status' => ''
        );
        $setting = self::get_key($id);
        $group = $group ? $group : 'settings';
        if (array_key_exists($group, self::$vars['options']) && array_key_exists($setting['name'], self::$vars['options'][$group]['options'])) {
            $const_name = Utilities::array_has_key('global_override', self::$vars['options'][$group]['options'][$setting['name']]) ? strtoupper($setting['id']) : '';
            if ($const_name && defined($const_name)) {
                //Display the value overriden by the constant value
                $return['value'] = constant($const_name);
                $return['constant'] = true;
            } else {
                $return['value'] = get_option($setting['id'], Utilities::array_has_key('default', self::$vars['options'][$group]['options'][$setting['name']]));
            }
        }
        //Sanitize values
        if (is_string($return['value'])) {
            $return['value'] = sanitize_text_field($return['value']);
        }
        //Return appropriate format
        if ($value_only) {
            return $return['value'];
        }
        return $return;
    }

    /**
     * Set Plugin Setting
     *
     * @since 1.0.0
     *
     * @static
     *
     * @param string $id The ID of the plugin setting, with or without the prefix
     * @param mixed $value The value of the plugin setting
     *
     * @return bool True on success, false on failure
     */
    public static function set($id, $value)
    {
        $setting = self::get_key($id);
        return update_option($setting['id'], $value);
    }

    //**** Plugin Management Page ****//

    /**
     * Add Admin Page
     *
     * Adds the admin page to the WordPress dashboard under "Tools".
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_menu()
    {
        add_management_page(
            self::$vars['name'], //Page Title
            __('Cron Hours', 'crontrol-hours'), //Menu Title
            self::$vars['capability_settings'], //Capability
            self::$vars['slug'], //Menu Slug
            array(&$this, 'admin_page') //Callback
        );
    }

    /**
     * Plugin Links
     *
     * Links to display on the plugins page.
     *
     * @since 1.0.0
     *
     * @param array $links
     *
     * @return array A list of links
     */
    public function plugin_links($links)
    {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            self::$vars['admin_url'],
            __('Settings', 'crontrol-hours')
        );
        $status_link = sprintf(
            '<a href="%s">%s</a>',
            self::$vars['admin_url'] . '#cron-status',
            __('Site Status', 'crontrol-hours')
        );
        $update_now = sprintf(
            '<a href="%s">%s</a>',
            self::$vars['admin_url'] . '#update-hours',
            __('Fix CRON Jobs Now', 'crontrol-hours')
        );
        array_unshift($links, $settings_link, $update_now, $status_link);
        return $links;
    }

    /**
     * Admin Scripts and Styles
     *
     * Enqueue scripts and styles to be used on the admin pages
     *
     * @since 1.0.0
     *
     * @param string $hook Hook suffix for the current admin page
     *
     * @return void
     */
    public function enqueue_assets_admin($hook)
    {
        // Load only on our plugin page (a subpage of "Tools")
        if ($hook == 'tools_page_' . self::$vars['slug']) {

            $debugging = defined('WP_DEBUG') && constant('WP_DEBUG');
            $minified = defined('SCRIPT_DEBUG') && constant('SCRIPT_DEBUG') ? '.min' : '';

            // Register Bootstrap style
            wp_register_style(
                self::$vars['prefix'] . 'layout', // Handle
                self::$vars['url'] . "assets/styles/pseudo-bootstrap$minified.css", // Source URL
                array(), // Dependencies
                $debugging ? @filemtime(self::$vars['path'] . "assets/styles/pseudo-bootstrap$minified.css") : self::$vars['version'] // Version
            );

            // Plugin Stylesheets
            wp_enqueue_style(
                self::$vars['prefix'] . 'dashboard',
                self::$vars['url'] . "assets/styles/admin-dashboard$minified.css",
                array(self::$vars['prefix'] . 'layout'), // Pseudo bootstrap
                $debugging ? @filemtime(self::$vars['path'] . "assets/styles/admin-dashboard$minified.css") : self::$vars['version']
            );

            // Plugin Scripts
            wp_enqueue_script(
                self::$vars['prefix'] . 'dashboard', // Handle
                self::$vars['url'] . "assets/scripts/admin-dashboard$minified.js", // Source URL
                array('jquery'), // Loading tabs depend on jQuery
                $debugging ? @filemtime(self::$vars['path'] . "assets/scripts/admin-dashboard$minified.js") : self::$vars['version'], // Version
                array('in_footer' => true, 'strategy' => 'defer') // Load async in footer
            );
            wp_add_inline_script(
                self::$vars['prefix'] . 'dashboard',
                sprintf(
                    'window["%s"]=%s;',
                    'au_dashboard', // JS object
                    json_encode(array(
                        'ajax_url' => admin_url('admin-ajax.php'), // AJAX url
                    ))
                ),
                'before'
            );
        }
    }

    /**
     * Display Admin Page
     *
     * HTML markup for the WordPress dashboard admin page for managing this plugin's settings.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_page()
    {
        //Prevent unauthorized users from viewing the page
        if (!current_user_can(self::$vars['capability_settings'])) {
            return;
        }
        $args = array(
            'plugin_settings' => self::$vars,
            'external_link_prefix' => sprintf(
                'https://aurisecreative.com/click/?utm_source=%s&utm_medium=website&utm_campaign=wordpress-plugin&utm_content=%s&utm_term=',
                str_replace(array('https://', 'http://'), '', home_url()), //UTM Source
                self::$vars['slug']
            ),
            'intervals' => self::get_intervals(),
            'start_time' => self::get_start(),
            'end_time' => self::get_end(),
            'duration' => array(),
            'schedules' => array(),
            'hooks' => '',
            'excluded_hooks' => self::get_excluded_hooks(),
            'force_daily' => self::get('force_daily', true),
            'restrict_frequent' => self::get('restrict_frequent', true)
        );
        $duration = self::get_duration();
        $hours = floor($duration / HOUR_IN_SECONDS);
        if ($hours === 1) {
            $args['duration'][] = $hours . ' ' . __('hour', 'crontrol-hours');
        } elseif ($hours > 1) {
            $args['duration'][] = $hours . ' ' . __('hours', 'crontrol-hours');
        }
        $minutes = floor(($duration / MINUTE_IN_SECONDS) % MINUTE_IN_SECONDS);
        if ($minutes === 1) {
            $args['duration'][] = $minutes . ' ' . __('minute', 'crontrol-hours');
        } elseif ($minutes > 1) {
            $args['duration'][] = $minutes . ' ' . __('minutes', 'crontrol-hours');
        }
        $seconds = $duration % MINUTE_IN_SECONDS;
        if ($seconds === 1) {
            $args['duration'][] = $seconds . ' ' . __('second', 'crontrol-hours');
        } elseif ($seconds > 1) {
            $args['duration'][] = $seconds . ' ' . __('seconds', 'crontrol-hours');
        }
        $d_count = count($args['duration']);
        if ($d_count == 2) {
            $args['duration'] = implode(' ' . __('and', 'crontrol-hours') . ' ', $args['duration']);
        } elseif ($d_count == 3) {
            $args['duration'] = $args['duration'][0] . ', ' . $args['duration'][1] . ', ' . __('and', 'crontrol-hours') . ' ' . $args['duration'][2];
        } else {
            $args['duration'] = implode('', $args['duration']);
        }
        $schedules = wp_get_schedules();
        if (is_array($schedules) && count($schedules)) {
            foreach ($schedules as $schedule => $s) {
                $args['schedules'][] = sprintf('<li>%s <code>%s</code></li>', $s['display'], $schedule);
            }
        }
        $args['schedules'] = implode('', $args['schedules']);
        $hooks = Utilities::get_cron_hooks();
        if (count($hooks)) {
            foreach ($hooks as $hook) {
                $args['hooks'] .= sprintf('<li><code>%s</code></li>', $hook);
            }
        }
        load_template(self::$vars['path'] . 'templates/dashboard-admin.php', true, $args);
    }

    /**
     * Set Single Meta Data
     *
     * @since 1.0.0
     *
     * @static
     *
     * @param int $post_id Post ID
     * @param string $key The meta key to set
     * @param mixed $value The meta value to set
     * @param string $post_type Optional. The post type to retrieve. Default is `post`.
     *
     * @return mixed The meta key value that was set
     */
    public static function set_meta($post_id, $key, $value, $post_type = 'post')
    {
        //update_metadata($post_type, $post_id, $this->settings['prefix'] . $key, $value);
        update_post_meta($post_id, self::$vars['prefix'] . $key, $value);
    }

    /**
     * Get Single Meta Data
     *
     * @since 1.0.0
     *
     * @static
     *
     * @param int $post_id Post ID
     * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys.
     * @param mixed $default Optional. The default value to return if not set. Default is an empty string.
     * @param string $post_type Optional. The post type to retrieve. Default is `post`.
     *
     * @return mixed The value of the meta field or default if it's not explicity false as a bool or integer.
     */
    public static function get_meta($post_id, $key = '', $default = '', $post_type = 'post')
    {
        if (!$post_type || !is_numeric($post_id) || $post_id <= 0) {
            return $default;
        }
        $key = $key ? self::$vars['prefix'] . $key : '';
        $value = get_post_meta($post_id, $key, true);
        //$value = get_metadata($post_type, $post_id, $key, true);
        //If value is set or if it is set to some falsey things on purpose
        if ($value || is_bool($value) || is_numeric($value)) {
            return $value;
        }
        return $default;
    }

    /**
     * Update Dynamic Settings
     *
     * @since 1.2.0
     *
     * @static
     *
     * @return void
     */
    public static function update_dynamic_settings()
    {
        self::update_duration_setting();
        self::reschedule_restricted_times();
    }

    /**
     * Initialise Dynamic Settings
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function update_duration()
    {
        self::update_duration_setting();
    }

    /**
     * Update Duration Setting
     *
     * @since 1.2.0
     *
     * @static
     *
     * @return void
     */
    public static function update_duration_setting()
    {
        $start = self::get_start();
        $end = self::get_end();
        $duration = $end->getTimestamp() - $start->getTimestamp();
        self::set('duration', $duration);
    }

    /**
     * Update Restricted Event Settings
     *
     * @since 1.2.0
     *
     * @return void
     */
    public function update_restricted_events()
    {
        self::reschedule_restricted_times();
    }

    /**
     * Update Restricted Start/Stop Events
     *
     * @since 1.2.0
     *
     * @static
     *
     * @return void
     */
    public static function reschedule_restricted_times()
    {
        $hook = self::$vars['hooks'][1];
        $restricting = self::get('restrict_frequent', true);
        if ($restricting) {
            $end = self::get_end();
            Utilities::debug_log('Scheduling CRON event to reschedule restricted events');
            // Schedule cron events to start/stop restricted events if they were not yet previously scheduled
            $timestamp = wp_next_scheduled($hook);
            if ($timestamp === false) {
                wp_schedule_event(
                    $end->getTimestamp(), //Timestamp
                    'daily', //recurrence
                    $hook //hook,
                );
            }
        } else {
            Utilities::debug_log('Removing CRON event to reschedule restricted events');
            // Unschedule them if they were scheduled previously (if not restricting or if unscheduling them)
            $timestamp = wp_next_scheduled($hook);
            if ($timestamp !== false) {
                wp_unschedule_event($timestamp, $hook);
            }
        }
    }

    /**
     * Get Duration
     *
     * @since 1.0.0
     *
     * @static
     *
     * @return int The duration between the start and end, in seconds
     */
    public static function get_duration()
    {
        $duration = self::get('duration', true);
        if (!$duration) {
            return self::get_end()->getTimestamp() - self::get_start()->getTimestamp();
        } elseif (is_numeric($duration)) {
            return intval($duration);
        }
        return 0;
    }

    /**
     * Get Recurring Intervals
     *
     * @since 1.0.0
     *
     * @static
     *
     * @return array An array of intervals from plugin settings.
     */
    public static function get_intervals()
    {
        return array_unique(array_filter(explode(',', sanitize_text_field(self::get('intervals', true)))));
    }

    /**
     * Get Excluded CRON Hooks
     *
     * @since 1.1.0
     *
     * @static
     *
     * @return array An array of CRON hooks to be excluded, set in plugin settings.
     */
    public static function get_excluded_hooks()
    {
        return array_unique(array_filter(explode(',', sanitize_text_field(self::get('exclude_hooks', true)))));
    }

    /**
     * Get Date
     *
     * @since 1.0.0
     *
     * @static
     *
     * @param int $timestamp
     *
     * @return DateTime
     */
    public static function get_date($timestamp = false, $timezone = false)
    {
        $date = new DateTime();
        if ($timestamp !== false) {
            //Use the date of the event
            $date->setTimestamp($timestamp);
        }
        //Set the timezone to be the same as the WordPress website
        $timezone = $timezone ? $timezone : wp_timezone_string();
        $date->setTimezone(new DateTimeZone($timezone));
        return $date;
    }

    /**
     * Get the Start Time
     *
     * Calculate the start time with an optional date parameter.
     *
     * @since 1.0.0
     *
     * @static
     *
     * @param int $timestamp Optional. Timestamp for when CRON events should start.
     *
     * @return DateTime The time when recurring CRON jobs can start firing.
     */
    public static function get_start($timestamp = false)
    {
        $start = self::get_date($timestamp);
        //Force the time
        $start_time = explode(':', self::get('start_time', true));
        $start->setTime(intval($start_time[0]), intval($start_time[1]));
        return $start;
    }

    /**
     * Get the End Time
     *
     * Calculate the end time to take place after the start time.
     *
     * @since 1.0.0
     *
     * @static
     *
     * @param int $timestamp Optional. Timestamp of the start time for when CRON events should start.
     *
     * @return DateTime The time when recurring CRON jobs should stop firing.
     */
    public static function get_end($timestamp = false)
    {
        $start = self::get_start($timestamp); //Set the start date based on the timestamp
        $end = self::get_date($start->getTimestamp()); //Set the end date based on the start date
        //Force the time
        $end_time = explode(':', self::get('end_time', true));
        $end->setTime(intval($end_time[0]), intval($end_time[1]));
        if ($end->getTimestamp() < $start->getTimestamp()) {
            //If this takes place before the start time, add a day to ensure it takes place after
            $end->modify('+1 day');
        }
        return $end;
    }
}

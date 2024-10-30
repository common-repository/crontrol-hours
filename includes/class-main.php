<?php

namespace AuRise\Plugin\CrontrolHours;

defined('ABSPATH') || exit; // Exit if accessed directly

use AuRise\Plugin\CrontrolHours\Utilities;
use AuRise\Plugin\CrontrolHours\Settings;

/**
 * Class Main
 *
 * The main features unique to this plugin.
 *
 * @package AuRise\Plugin\Main
 */
class Main
{
    /**
     * The single instance of the class
     *
     * @var Main
     *
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main Instance
     *
     * Ensures only one instance of is loaded or can be loaded.
     *
     * @since 1.0.0
     *
     * @static
     *
     * @return Main Main instance.
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
        Settings::instance(); // Initialize settings

        add_action(Settings::$vars['hook'], array($this, 'update_hours'), 10, 1); //Add CRON action to check and update hours
        add_action(Settings::$vars['prefix'] . 'reschedule_restricted', array($this, 'reschedule_restricted_frequent_events'), 10, 0); //Add CRON action to delay restricted events
        add_action('wp_ajax_crontrol_hours_update', array($this, 'update_hours_js')); //Add AJAX call for logged in users to update the hours after saving the settings
        register_activation_hook(CRONTROLHOURS_FILE, array($this, 'activate')); // Add our cron event when plugin is activated
        register_deactivation_hook(CRONTROLHOURS_FILE, array($this, 'deactivate')); // Clean up after our plugin when it's deactivated
    }

    /**
     * Activate Plugin
     *
     * @since 1.0.0
     * @return void
     */
    public function activate()
    {
        //Clear duplicates, just in case
        $this->cleanup_crons();

        //Add new event for midnight tonight
        wp_schedule_event(
            strtotime('today 23:59 ' . wp_timezone_string()), //Timestamp
            'daily', //recurrence
            Settings::$vars['hook'] //hook,
        );

        // If variables exist from previous installation or as constants, calculate the dynamic ones
        Settings::update_dynamic_settings();
    }

    /**
     * Deactivate Plugin
     *
     * @since 1.0.0
     * @return void
     */
    public function deactivate()
    {
        // Return any restricted crons back to normal
        $this->cleanup_crons();
    }

    /**
     * Plugin cleanup_crons
     *
     * Unschedules next CRON events and clears all events created by this plugin.
     *
     * @since 1.1.0
     * @return void
     */
    private function cleanup_crons()
    {
        foreach (Settings::$vars['hooks'] as $hook) {
            //Unschedule the next event
            $timestamp = wp_next_scheduled($hook);
            if ($timestamp !== false) {
                wp_unschedule_event($timestamp, $hook);
            }

            //Clear all events on this hook
            wp_clear_scheduled_hook(
                $hook //Hook
            );
        }
    }

    /**
     * Update Hours
     *
     * @since 1.0.0
     *
     * @param bool $dryrun If true, nothing will be updated
     *
     * @return array return object
     */
    public function update_hours($dryrun = false)
    {
        $return = array(
            'success' => 0,
            'error' => 0,
            'messages' => array(__('Checking CRON events', 'crontrol-hours'))
        );
        $crons = _get_cron_array();
        if (is_array($crons) && count($crons)) {
            $return['success']++;
            $format = 'n/j/Y g:ia T';
            $return['messages'][] = __('Is this a dry run?', 'crontrol-hours') . ' ' . ($dryrun ? __('Yes', 'crontrol-hours') : __('No', 'crontrol-hours'));
            $force_daily = Settings::get('force_daily', true);
            $restrict_frequent = Settings::get('restrict_frequent', true);
            $return['messages'][] = __('Forcing events scheduled to run multiple times a day to only run between the specified hours?', 'crontrol-hours') . ' ' . ($restrict_frequent ? __('Yes', 'crontrol-hours') : __('No', 'crontrol-hours'));
            if ($restrict_frequent && $force_daily) {
                $force_daily = false;
                $return['messages'][] = __('Forcing events scheduled to run multiple times a day to only run once per day? No (restricting frequent events is overriding this setting)', 'crontrol-hours');
            } else {
                $return['messages'][] = __('Forcing events scheduled to run multiple times a day to only run once per day?', 'crontrol-hours') . ' ' . ($force_daily ? __('Yes', 'crontrol-hours') : __('No', 'crontrol-hours'));
            }
            $duration = Settings::get_duration();
            $excluded = Settings::get_excluded_hooks();
            $excluded_count = count($excluded);
            $return['messages'][] = sprintf(__('Found %s events to check', 'crontrol-hours'), count($crons));
            $c = 1;
            $today_start = Settings::get_start();
            //$today_end = Settings::get_end($today_start->getTimestamp());
            foreach ($crons as $timestamp => $cron) {
                if (is_array($cron) && count($cron)) {
                    $return['messages'][] = sprintf(__('Checking event %s and found %s hooks to check', 'crontrol-hours'), $c, count($cron));
                    $next_run = Settings::get_date($timestamp); //This is set in UTC
                    $this_ts = $next_run->getTimestamp();
                    $start = Settings::get_start($this_ts);
                    $start_ts = $start->getTimestamp();
                    $end = Settings::get_end($start_ts);
                    $end->modify('-1 day'); //Get the previous day's end date
                    $end_ts = $end->getTimestamp();
                    $before_start = $start_ts > $this_ts;
                    $after_end =  $end_ts <= $this_ts;
                    if ($before_start && $after_end) {
                        $too_early = $start_ts - $this_ts;
                        foreach ($cron as $hook => $job) {
                            //Ensure hook is not explicitly excluded
                            if (!$excluded_count || !in_array($hook, $excluded)) {
                                $key = array_key_first($job);
                                $schedule = Utilities::array_has_key('schedule', $job[$key]);
                                if (in_array($schedule, Settings::get_intervals())) {
                                    $interval = Utilities::array_has_key('interval', $job[$key], false);
                                    $underday = $interval < DAY_IN_SECONDS && ($force_daily || $restrict_frequent); // If less than a day, but we're restricting or setting to daily
                                    $daymore = $interval >= DAY_IN_SECONDS; // Greater than or equal to daily
                                    if ($interval !== false && ($underday || $daymore)) {
                                        $recurrence = $interval < DAY_IN_SECONDS && $force_daily ? 'daily' : $schedule;
                                        if ($underday && $restrict_frequent) {
                                            // Start it at the next start event
                                            $new_run = $start;
                                        } else {
                                            // Get whenever during window
                                            $new_run = $this::get_new_time($next_run, $too_early, $duration);
                                        }
                                        $cron_args = Utilities::array_has_key('args', $job[$key], array());
                                        $return['messages'][] = sprintf(
                                            __('The <code>%s</code> event with the <code>%s</code> interval starts at %s which is before %s and after %s and will be rescheduled for %s %s', 'crontrol-hours'),
                                            $hook,
                                            $schedule,
                                            $next_run->format('g:ia T'),
                                            $start->format('g:ia T'),
                                            $end->format('g:ia T'),
                                            $new_run->format($format),
                                            $daymore ? __('- interval remains unaffected', 'crontrol-hours') : ($restrict_frequent ? sprintf(
                                                __('and will stop running around %s (and resume this schedule every day)', 'crontrol-hours'),
                                                $end->format('g:ia T')
                                            ) : __('- interval will be updated to daily', 'crontrol-hours'))
                                        );
                                        if (!$dryrun) {
                                            //Delete old event
                                            $cleared = wp_clear_scheduled_hook(
                                                $hook, //Hook
                                                $cron_args //args
                                            );
                                            if ($cleared === false) {
                                                $return['error']++;
                                                $return['messages'][] = sprintf(
                                                    __('Failed to unschedule one or more <code>%s</code> events for <code>%s</code>', 'crontrol-hours'),
                                                    $recurrence,
                                                    $hook
                                                ) . ' ' . http_build_query($cron_args);
                                            } else {
                                                $return['success']++;
                                                $return['messages'][] = sprintf(
                                                    __('Successfully unscheduled %s <code>%s</code> event(s) for <code>%s</code>', 'crontrol-hours'),
                                                    $cleared,
                                                    $recurrence,
                                                    $hook
                                                ) . ' ' . http_build_query($cron_args);
                                            }

                                            //Create new daily event
                                            $added = wp_schedule_event(
                                                $new_run->getTimestamp(), //Timestamp
                                                $recurrence, //recurrence
                                                $hook, //hook,
                                                $cron_args //args
                                            );
                                            if ($added === false) {
                                                $return['error']++;
                                                $return['messages'][] = sprintf(
                                                    __('Failed to reschedule %s event <code>%s</code> for %s', 'crontrol-hours'),
                                                    $recurrence,
                                                    $hook,
                                                    $new_run->format($format),
                                                ) . ' ' . http_build_query($cron_args);
                                            } else {
                                                $return['success']++;
                                                $return['messages'][] = sprintf(
                                                    __('Successfully rescheduled %s event <code>%s</code> for %s', 'crontrol-hours'),
                                                    $recurrence,
                                                    $hook,
                                                    $new_run->format($format),
                                                ) . ' ' . http_build_query($cron_args);
                                            }
                                        }
                                    }
                                }
                            } else {
                                $return['messages'][] = sprintf(
                                    __('This event <code>%s</code> would have been rescheduled, but skipping it because it was excluded', 'crontrol-hours'),
                                    $hook
                                );
                            }
                        }
                    } else {
                        $return['messages'][] = __('Event takes place during your hours, nothing to do here!', 'crontrol-hours');
                    }
                }
                $c++;
            }
        } else {
            $return['messages'][] = __('No CRON events were found!', 'crontrol-hours');
        }
        $return['messages'][] = __('Completed!', 'crontrol-hours');
        if (!$dryrun) {
            // If not a dryrun, ensure cron events are created with restrictions
            Settings::reschedule_restricted_times();
        }
        return $return;
    }

    /**
     * AJAX: Update Hours
     *
     * @since 1.0.0
     *
     * @return string JSON encoded array of custom return object
     */
    public function update_hours_js()
    {
        //Init return object
        $return = array(
            'success' => 0,
            'error' => 0,
            'messages' => array(),
            //'fields' => Utilities::json_decode(str_replace('%27', "'", urldecode(Utilities::array_has_key('fields', $_POST, '')))),
            'fields' => array(),
            'output' => ''
        );

        //Process input parameters (since field has been "serialized", simply parse it)
        parse_str(Utilities::array_has_key('fields', $_POST), $return['fields']);

        //Security Check
        $nonce = Utilities::array_has_key('nonce', $return['fields'], '');
        if ($nonce && wp_verify_nonce($nonce, Settings::$vars['slug'])) {
            $return['success']++;
            $dryrun = Utilities::array_has_key('dryrun', $return['fields']) === 'on';
            $update_hours = $this->update_hours($dryrun);
            $return['success'] += $update_hours['success'];
            $return['error'] += $update_hours['error'];
            $return['messages'] = array_merge($return['messages'], $update_hours['messages']);
        } else {
            $return['error']++;
            $return['messages'][] = __('Could not verify AJAX call.', 'crontrol-hours');
        }
        if (count($return['messages'])) {
            $return['output'] = '<ol>' . Utilities::implode_surround($return['messages'], '', '<li>', '</li>') . '</ol>';
        }
        $return['output'] = wp_kses_post($return['output']); //Escape output that will be displayed
        wp_die(json_encode($return, JSON_PRETTY_PRINT));
    }

    /**
     * CRON Event: Delay Restricted Events until Next Window
     *
     * This is triggered at the user's selected "End" time
     *
     * @since 1.2.0
     *
     * @return void
     */
    public function reschedule_restricted_frequent_events()
    {
        if (Settings::get('restrict_frequent', true)) {
            $crons = _get_cron_array();
            if (is_array($crons) && count($crons)) {
                $excluded = Settings::get_excluded_hooks();
                $format = 'n/j/Y g:ia T';
                // Start the next run at the beginng of tomorrow's start time
                $new_run = Settings::get_start();
                $new_run->modify('+1 day');
                foreach ($crons as $timestamp => $cron) {
                    if (is_array($cron) && count($cron)) {
                        foreach ($cron as $hook => $job) {
                            //Ensure hook is not explicitly excluded
                            if (!count($excluded) || !in_array($hook, $excluded)) {
                                // Must be a schedule user specified and and interval is multiple times a day
                                $key = array_key_first($job);
                                $schedule = Utilities::array_has_key('schedule', $job[$key]);
                                $interval = Utilities::array_has_key('interval', $job[$key], false);
                                if ($interval < DAY_IN_SECONDS && in_array($schedule, Settings::get_intervals())) {
                                    // If the interval is less than a day, reschedule it to start at the next start time
                                    $cron_args = Utilities::array_has_key('args', $job[$key], array());

                                    //Delete old event
                                    $cleared = wp_clear_scheduled_hook(
                                        $hook, //Hook
                                        $cron_args //args
                                    );
                                    if ($cleared === false) {
                                        Utilities::debug_log(sprintf(
                                            __('Failed to unschedule one or more %s events for <code>%s</code>', 'crontrol-hours'),
                                            $schedule,
                                            $hook
                                        ) . ' ' . http_build_query($cron_args));
                                    }

                                    //Create new daily event
                                    $added = wp_schedule_event(
                                        $new_run->getTimestamp(), //Timestamp
                                        $schedule, //recurrence
                                        $hook, //hook,
                                        $cron_args //args
                                    );
                                    if ($added === false) {
                                        Utilities::debug_log(sprintf(
                                            __('Failed to reschedule %s event <code>%s</code> for %s', 'crontrol-hours'),
                                            $schedule,
                                            $hook,
                                            $new_run->format($format),
                                        ) . ' ' . http_build_query($cron_args));
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Choose a New Time
     *
     * @since 1.0.0
     *
     * @param DateTime The DateTime object to clone and modify
     * @param int The number of seconds to defer this event by
     * @param int $max The total duration, in seconds, that this can be randomly chosen from
     *
     * @return DateTime The new DateTime object
     */
    private static function get_new_time($dt, $seconds, $max)
    {
        $new_dt = clone $dt;
        $r = rand(0, $max);
        $new_dt->modify('+' . ($seconds + $r) . ' seconds');
        return $new_dt;
    }
}
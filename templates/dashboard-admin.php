<?php
if (isset($_GET['settings-updated'])) {
    add_settings_error(
        $args['plugin_settings']['prefix'] . 'messages',
        $args['plugin_settings']['prefix'] . 'message',
        __('Settings Saved!', 'crontrol-hours'),
        'success'
    );
}
settings_errors($args['plugin_settings']['prefix'] . 'messages'); ?>
<div class="au-plugin">
    <h1>
        <?php if (file_exists($args['plugin_settings']['path'] . 'assets/images/admin-logo.png')) {
            printf(
                '<img width="293" height="60" src="%3$s" title="%1$s %2$s" alt="%1$s %2$s" />',
                esc_attr($args['plugin_settings']['name']),
                esc_attr__('by AuRise Creative', 'crontrol-hours'),
                esc_url($args['plugin_settings']['url'] . 'assets/images/admin-logo.png')
            );
        } else {
            printf(
                '%s<small>%s</small>',
                esc_html($args['plugin_settings']['name']),
                esc_html__('by AuRise Creative', 'crontrol-hours')
            );
        } ?>
    </h1>
    <div class="au-plugin-admin-ui">
        <div class="loading-spinner">
            <img src="<?php echo (esc_url($args['plugin_settings']['url'])); ?>assets/images/progress.gif" alt="<?php _e('Loading dashboard, please wait…', 'crontrol-hours'); ?>" width="32" height="32" />
        </div>
        <div class="admin-ui hide">
            <nav class="nav-tab-wrapper">
                <a class="nav-tab" id="open-settings" href="#settings"><?php esc_html_e('Settings', 'crontrol-hours') ?></a>
                <a class="nav-tab" id="open-cron-status" href="#cron-status"><?php esc_html_e('Status', 'crontrol-hours') ?></a>
                <a class="nav-tab" id="open-update-hours" href="#update-hours"><?php esc_html_e('Update Hours', 'crontrol-hours') ?></a>
                <a class="nav-tab" id="open-about" href="#about"><?php esc_html_e('About This Plugin', 'crontrol-hours'); ?></a>
            </nav>
            <div id="tab-content" class="container">
                <section id="settings" class="tab">
                    <?php foreach ($args['plugin_settings']['options'] as $option_group_name => $group) {
                        $option_group = $args['plugin_settings']['prefix'] . $option_group_name;
                        printf('<form data-group="%s" method="post" action="options.php">', esc_attr($option_group));
                        settings_fields($option_group);
                        printf('<fieldset class="%s"><h2>%s</h2>', esc_attr($option_group_name), esc_html($group['title']));
                        echo ('<table class="form-table" role="presentation">');
                        do_settings_fields($args['plugin_settings']['slug'], $option_group);
                        echo ('</table></fieldset>');
                        submit_button(__('Save Settings', 'crontrol-hours'));
                        echo ('</form>');
                    } ?>
                </section>
                <section id="cron-status" class="tab">
                    <?php if ($args['duration']) {
                        printf(
                            '<h2>%s</h2><p>%s</p>',
                            __('Duration', 'crontrol-hours'),
                            sprintf(
                                __('Your CRON events have a window of <strong>%s</strong> between <strong>%s</strong> and <strong>%s</strong> to start running.', 'crontrol-hours'),
                                esc_html($args['duration']),
                                esc_html($args['start_time']->format('g:ia T')),
                                esc_html($args['end_time']->format('g:ia T'))
                            )
                        );
                    } ?>
                    <h2><?php _e('CRON Schedules', 'crontrol-hours'); ?></h2>
                    <?php if ($args['schedules']) {
                        printf(
                            '<p>%s</p><ol>%s</ol>',
                            __('Your website has the following intervals registered:', 'crontrol-hours'),
                            wp_kses($args['schedules'], array('li' => array(), 'code' => array()))
                        );
                    } else {
                        printf('<p>%s</p>', __('Your website has no CRON schedules registered.', 'crontrol-hours'));
                    } ?>
                    <h2><?php _e('CRON Hooks', 'crontrol-hours'); ?></h2>
                    <?php if ($args['hooks']) {
                        printf(
                            '<p>%s</p><ol>%s</ol>',
                            __('Your website has the following hooks registered:', 'crontrol-hours'),
                            wp_kses($args['hooks'], array('li' => array(), 'code' => array()))
                        );
                    } else {
                        printf('<p>%s</p>', __('Your website has no CRON hooks registered.', 'crontrol-hours'));
                    } ?>
                </section>
                <section id="update-hours" class="tab">
                    <h2><?php _e('Update Hours', 'crontrol-hours'); ?></h2>
                    <p><?php printf(
                            '%s<br /><code>%s</code>',
                            __('Running this task will effect CRON events with the following intervals:', 'crontrol-hours'),
                            esc_html(implode(', ', $args['intervals']))
                        ); ?></p>
                    <p><?php if ($count_excluded = count($args['excluded_hooks'])) {
                            printf(
                                '%s<br /><code>%s</code>',
                                __('Except these CRON events, they are excluded:', 'crontrol-hours'),
                                esc_html(implode(', ', $args['excluded_hooks']))
                            );
                        } else {
                            _e('No CRON hooks are being explicitly excluded.', 'crontrol-hours');
                        } ?></p>
                    <p><?php if ($args['force_daily'] || $args['restrict_frequent']) {
                            if ($args['restrict_frequent']) {
                                printf(
                                    __('CRON events that are scheduled to run multiple times a day will be restricted to the hours <strong>between %s and %s</strong> with their interval remaining unchanged.', 'crontrol-hours'),
                                    esc_html($args['start_time']->format('g:ia T')),
                                    esc_html($args['end_time']->format('g:ia T'))
                                );
                            } elseif ($args['force_daily']) {
                                _e('CRON events that are scheduled to run multiple times a day will be rescheduled to run only once daily.', 'crontrol-hours');
                            }
                        } else {
                            _e('CRON events that are scheduled to run multiple times a day will not be affected.', 'crontrol-hours');
                        } ?></p>
                    <p><?php printf(
                            __('All other recurring CRON events with those intervals that are scheduled to start <strong>before %1$s</strong> and <strong>after %2$s</strong> will be delayed and rescheduled to start after %1$s on the same day it was scheduled.', 'crontrol-hours'),
                            esc_html($args['start_time']->format('g:ia T')),
                            esc_html($args['end_time']->format('g:ia T'))
                        ); ?></p>
                    <p><?php _e('Non-repeating CRON events will not be affected.', 'crontrol-hours'); ?></p>
                    <form id="crontrol-hours-update" class="update-hours">
                        <input type="hidden" name="nonce" value="<?php echo (wp_create_nonce($args['plugin_settings']['slug'])); ?>" />
                        <fieldset>
                            <table class="form-table" role="presentation">
                                <tr>
                                    <th scope="row">
                                        <label for="crontrol-hours-update-dryrun"><?php _e('Dry run?'); ?></label>
                                    </th>
                                    <td>
                                        <label>
                                            <input id="crontrol-hours-update-dryrun" type="checkbox" name="dryrun" checked />
                                            <small><?php _e('If this box is checked, no changes will be made. You will be able to preview the exact CRON events that would be rescheduled. Uncheck this box and run it again for anything to be changed.', 'crontrol-hours'); ?></small>
                                        </label>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                        <p class="submit">
                            <button type="submit" class="button button-primary"><?php _e('Update CRON Events Now', 'crontrol-hours'); ?></button>
                            <span class="progress-spinner hide"><img src="<?php echo (esc_url($args['plugin_settings']['url'])); ?>assets/images/progress.gif" alt="" width="32" height="32" /></span>
                        </p>
                        <div class="form-response-output hide"></div>
                    </form>
                </section>
                <section id="about" class="tab">
                    <h2><?php _e("Benefits", 'crontrol-hours'); ?></h2>
                    <p><?php _e("Restricting your recurring CRON events to only run after hours helps with two (2) things:"); ?></p>
                    <ol>
                        <li><?php _e("Automatic updates for WordPress core, plugins, and themes are prevented from running during your highest-traffic times so users aren't shown a maintenance page when it's the most visible.", 'crontrol-hours'); ?></li>
                        <li><?php _e("Less stress is placed on your server when automatic maintenance occurs during low traffic times.", 'crontrol-hours'); ?></li>
                    </ol>
                    <h2><?php _e("Ensuring CRON Events Always Run", 'crontrol-hours'); ?></h2>
                    <p><?php _e("WordPress CRON is based on traffic, which means if your site does not see a lot of traffic, CRON events may not be triggered at the time that they are scheduled. Limiting your website's CRON events to off-hours while also depending on site traffic to trigger them may not produce the intended results. There are two (2) solutions I recommend:"); ?></p>
                    <ol>
                        <li><strong><?php _e("Use Server CRON.", 'crontrol-hours'); ?></strong>&nbsp;
                            <?php _e("It is recommended in the WordPress developer resources to set up your system's task scheduler to run on the desired intervals and to use that to make a web request to <code>wp-cron.php</code>.", 'crontrol-hours'); ?>&nbsp;
                            <a href="<?php echo (esc_url($args['external_link_prefix'] . 'server-cron&redirect=' . urlencode('https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/'))); ?>" target="_blank" rel="noopener noreferrer nofollow">
                                <?php _e("View WordPress Documentation", 'crontrol-hours'); ?>
                            </a>
                        </li>
                        <li><strong><?php _e("Use Cron-Job.org.", 'crontrol-hours'); ?></strong>&nbsp;
                            <?php _e("If you can't set up your system's task scheduler, I recommend outsourcing that job to cron-job.org to automatically ping your website's <code>wp-cron.php</code> file. It is a free service from the German-based developers.", 'crontrol-hours'); ?>&nbsp;
                            <a href="<?php echo (esc_url($args['external_link_prefix'] . 'cron-job.org&redirect=' . urlencode('https://cron-job.org/'))); ?>" target="_blank" rel="noopener noreferrer nofollow">
                                <?php _e("Go to Cron-Job.org", 'crontrol-hours'); ?>
                            </a>
                        </li>
                    </ol>
                </section>
            </div>
        </div>
    </div>
    <?php load_template($args['plugin_settings']['path'] . 'templates/dashboard-support.php', true, $args['plugin_settings']); ?>
</div>
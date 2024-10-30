<div class="au-plugin-support">
    <h2><?php _e('Plugin Support', 'crontrol-hours'); ?></h2>
    <?php
    $source = sanitize_text_field(wp_parse_url(home_url(), PHP_URL_HOST));
    $link_prefix = sprintf(
        'https://aurisecreative.com/click/?utm_source=%s&utm_medium=website&utm_campaign=wordpress-plugin&utm_content=%s&utm_term=',
        $source,
        $args['slug']
    ); ?>
    <p>
        <?php _e('Enjoying my plugin? Please leave a review!'); ?><br />
        <a class="button button-secondary" href="<?php echo (esc_url($link_prefix . 'write-a-review&redirect=' . urlencode(sprintf('https://wordpress.org/support/plugin/%s/reviews/#new-post', $args['slug'])))); ?>" target="_blank" rel="noopener">
            <?php _e('Write a Review', 'crontrol-hours'); ?>
        </a>
    </p>
    <p>
        <?php _e("If you're experiencing issues with this plugin or have a suggestion for a feature or fix, please check the support threads or create your own to give me the opportunity to make it better. I want to help!", 'crontrol-hours'); ?><br />
        <a class="button button-secondary" href="<?php echo (esc_url($link_prefix . 'support-forums&redirect=' . urlencode(sprintf('https://wordpress.org/support/plugin/%s/', $args['slug'])))); ?>" target="_blank" rel="noopener">
            <?php _e('Support Forums', 'crontrol-hours'); ?>
        </a>
    </p>
    <p>
        <?php _e('This is a <em>free</em> plugin that I poured a bit of my heart and soul into with the sole purpose of being helpful to you and the users of your WordPress website. Please consider supporting my queer and autistic-led small business by donating! Thank you!', 'crontrol-hours'); ?><br />
    </p>
    <div class="donate-button">
        <a title="<?php _e('Donate', 'crontrol-hours'); ?>" href="<?php echo (esc_url($link_prefix . 'donate&redirect=' . urlencode('https://just1voice.com/donate'))); ?>" target="_blank" rel="noopener">
            <span>
                <img width="20" height="13" src="<?php echo (esc_url($args['url'] . 'assets/images/kofi-cup.png')); ?>" alt="<?php _e('Coffee cup', 'crontrol-hours'); ?>" />
                <?php _e('Buy me a Coffee', 'crontrol-hours'); ?>
            </span>
        </a>
    </div>
    <h3><?php _e('Additional Resources', 'crontrol-hours'); ?></h3>
    <ul>
        <li>
            <a href="<?php echo (esc_url(sprintf(
                            'https://aurisecreative.com/docs/%2$s/?utm_source=%1$s&utm_medium=website&utm_campaign=wordpress-plugin&utm_content=%2$s&utm_term=developer-documentation',
                            $source,
                            $args['slug']
                        ))); ?>" target="_blank" rel="noopener">
                <?php esc_html_e('Developer Documentation', 'crontrol-hours'); ?>
            </a>
        </li>
        <li>
            <a href="<?php echo (esc_url(sprintf(
                            'https://aurisecreative.com/docs/%2$s/frequently-asked-questions/?utm_source=%1$s&utm_medium=website&utm_campaign=wordpress-plugin&utm_content=%2$s&utm_term=faq',
                            $source,
                            $args['slug']
                        ))); ?>" target="_blank" rel="noopener">
                <?php esc_html_e('Frequently Asked Questions', 'crontrol-hours'); ?>
            </a>
        </li>
        <li>
            <a href="<?php echo (esc_url(sprintf(
                            'https://aurisecreative.com/services/website-emergency/?utm_source=%s&utm_medium=website&utm_campaign=wordpress-plugin&utm_content=%s&utm_term=emergency-website-services',
                            $source,
                            $args['slug']
                        ))); ?>" target="_blank" rel="noopener">
                <?php esc_html_e('Emergency Website Service', 'crontrol-hours'); ?>
            </a>
        </li>
    </ul>
</div>
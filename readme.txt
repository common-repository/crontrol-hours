=== WP Crontrol Hours ===
Contributors: tessawatkinsllc
Donate link: https://just1voice.com/donate/
Tags: wp-cron, restrict hours, limit hours, after hours, business hours
Tested up to: 6.6
Stable tag: 2.1.0
License: GPLv3 or higher
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Take control of your CRON jobs by restricting them to your website's low traffic hours.

== Description ==

Take control of your CRON jobs by restricting them to your website's low traffic hours. From the admin screen, you can:

* Specify a daily window of when recurring CRON events should be triggered.
* Optionally limit events that run multiple times a day to only once a day.
* Optionally restrict events that run multiple times a day to only during your off-hours.
* Target specific schedules, including custom ones created by other plugins.
* Exclude specific CRON hooks from being affected

= Benefits =

Restricting your recurring CRON events to only run after hours helps with two (2) things:

1. Automatic updates for WordPress core, plugins, and themes are prevented from running during your highest-traffic times so users aren't shown a maintenance page when it's the most visible.
1. Less stress is placed on your server when automatic maintenance occurs during low traffic times.

= Ensuring CRON Events Always Run =

WordPress CRON is based on traffic, which means if your site does not see a lot of traffic, CRON events may not be triggered at the time that they are scheduled. Limiting your website's CRON events to off-hours while also depending on site traffic to trigger them may not produce the intended results. There are two (2) solutions I recommend:

1. **Use Server CRON.** It is recommended in the WordPress developer resources to set up your system's task scheduler to run on the desired intervals and to use that to make a web request to `wp-cron.php`. [View WordPress Documentation](https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/).
1. **Use Cron-Job.org.** If you can't set up your system's task scheduler, I recommend outsourcing that job to cron-job.org to automatically ping your website's `wp-cron.php` file. It is a free service from the German-based developers. Go to [Cron-Job.org](https://cron-job.org/).

== Installation ==

There are three (3) ways to install my plugin: automatically, upload, or manually.

= Install Method 1: Automatic Installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser.

1. Log in to your WordPress dashboard.
1. Navigate to **Plugins > Add New**.
1. Where it says "Keyword" in a dropdown, change it to "Author"
1. In the search form, type `TessaWatkinsLLC` (results may begin populating as you type but my plugins will only show when the full name is there)
1. Once you've found my plugin in the search results that appear, click the **Install Now** button and wait for the installation process to complete.
1. Once the installation process is completed, click the **Activate** button to activate my plugin.

= Install Method 2: Upload via WordPress Admin =

This method involves is a little more involved. You don't need to leave your web browser, but you'll need to download and then upload the files yourself.

1. [Download my plugin](https://wordpress.org/plugins/crontrol-hours/) from WordPress.org; it will be in the form of a zip file.
1. Log in to your WordPress dashboard.
1. Navigate to **Plugins > Add New**.
1. Click the **Upload Plugin** button at the top of the screen.
1. Select the zip file from your local file system that was downloaded in step 1.
1. Click the **Install Now** button and wait for the installation process to complete.
1. Once the installation process is completed, click the **Activate** button to activate it.

= Install Method 3: Manual Installation =

This method is the most involved as it requires you to be familiar with the process of transferring files using an SFTP client.

1. [Download my plugin](https://wordpress.org/plugins/crontrol-hours/) from WordPress.org; it will be in the form of a zip file.
1. Unzip the contents; you should have a single folder named `crontrol-hours`.
1. Connect to your WordPress server with your favorite SFTP client.
1. Copy the folder from step 2 to the `/wp-content/plugins/` folder in your WordPress directory. Once the folder and all of its files are there, installation is complete.
1. Now log in to your WordPress dashboard.
1. Navigate to **Plugins > Installed Plugins**. You should now see my plugin in your list.
1. Click the **Activate** button under my plugin to activate it.

== Screenshots ==

1. Plugin settings screen
2. Plugin status screen where you can see the calculated duration of the window you set, the CRON schedules and hooks registered on your website
3. Update Hours screen where you can perform a dry run of what events would be modified or manually run the event now
4. An example of a dry run and what the output looks like
5. An example of a real run and what the output looks like

== Frequently Asked Questions ==

Please check out the [FAQ on our website](https://aurisecreative.com/docs/crontrol-hours/frequently-asked-questions/?utm_source=wordpress.org&utm_medium=link&utm_campaign=crontrol-hours&utm_content=readme).

== Upgrade Notice ==

= 2.1.0 =
Updates to the backend admin screen and tested up to WordPress 6.4.

== Changelog ==

= 2.1.0 - November 9, 2023 =

* Update: updated configuration to match other AuRise Creative plugins
* Update: confirmed functionality for WordPress Core version 6.4
* Feature: added additional resources in Plugin Support block on admin page (developer documentation, FAQ, and emergency website service)
* Update: updated backend scripts to utilize defer strategy loading
* Update: updated backend script to use `wp_add_inline_script` instead of `wp_localize_script` for AJAX URL.
* Update: updated backend assets to use minified files when not debugging
* Minor: updated JS files to use `let` and `const` instead of `var`
* Minor: updated styles for admin page
* Language: removed the `languages` folder and `Domain Path` in plugin to rely on [Translating WordPress](https://translate.wordpress.org/projects/wp-plugins/crontrol-hours/)

= 2.0.0 - March 30, 2023 =

* Feature: Added a "Restrict Frequent" setting that, when enabled, will restrict events that run multiple times a day to only run between the daily start and end times while maintaining their specified intervals.
* UI: Updated the appearance of the settings page
* UI: Added more information to the "Update Hours" tab and outputted information
* Assets: Compressed the PNG images loaded on the backend

= 1.1.0 - November 9, 2022 =

* Feature: Added a plugin setting to explicitly exclude hooks from being automatically updated
* Feature: Added a status and fix link to the list of links on the plugin page between "Settings" and "Deactivate" to quickly navigate to those tabs from the plugins page
* Fix: Updated the CRON event that is added when the plugin is activated to take place around midnight respective to the WordPress site's timezone
* Language: Added the `/languages/` directory with the POT file to allow translations.

= 1.0.0 - November 3, 2022 =

* Major: Submitted to WordPress.org repository!
=== Display Server Info ===
Contributors: robert2021south
Tags: server information, php info, dashboard widget, shortcode support, multilingual plugin
Donate link: http://ko-fi.com/robertsouth
Requires at least: 5.6
Tested up to: 6.8
Requires PHP: 7.2
Stable tag: 2.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Displays server, PHP, and database info in the dashboard, admin bar, and footer, with shortcode and multilingual support.

== Description ==

**Full Description:**
Display Server Info is a powerful WordPress plugin that provides detailed server information for administrators directly within the dashboard.
It adds server, PHP, and database information to the dashboard, top admin bar, and footer for quick access.
The settings menu includes a "Display Server Info" submenu with two tabs:

1. **Server Information Tab:**
   In this tab, you will see the information about:
    Operating System
    Hostname
    Server IP
    Protocol
    Server Software
    Web Port
    CGI Version

    PHP Version
    Memory Limit
    Max Execution Time
    Upload Max Filesize
    Max File Uploads

    Database Server version
    Database Client version
    Database host
    Database username
    Database name
    Table prefix
    Database charset
    Database collation

    Please rate the Plugin if you find it useful, thanks.

2. **Settings Tab:**
   Configure plugin behavior with four toggle switches to enable or disable server information display in the dashboard, admin bar, and footer.
   Additionally, users can activate shortcode functionality, allowing `[disi_server_info]` to display server information anywhere in posts or pages.

The plugin is fully translated into 7 international languages, making it accessible for a global audience.


== Installation ==

Installation from within WordPress
Visit Plugins > Add New.
Search for Display Server Info.
Install and activate the Display Server Info plugin.

Manual installation
Upload the entire display-server-info folder to the /wp-content/plugins/ directory.
Visit Plugins.
Activate the Display Server Info plugin.

If everything is all right, you will see the "Server Information" widget under the dashboard "At a Glance" widget.

== Screenshots ==
1. Dashboard Server Info
2. Admin Bar Server Info
3. Footer Server Info
4. More Page
5. Setting Page
6. Phpinfo
7. Shortcode Use Case

== Changelog ==

= 2.0.0 =
* Code refactored to object-oriented
* Added new feature: Display server information in the upper right corner of the management bar
* Added new feature: Display server information in the footer
* Added more pages: Display more server information, PHP information and database information
* Added configuration page: Configure whether to display in the dashboard, top management bar and footer
* Added shortcode support: Server information can be called in posts and pages

= 1.0.1 =
* Initial version


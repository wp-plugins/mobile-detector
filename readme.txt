=== Plugin Name ===
Contributors: Tubal
Tags: mobile, detector, switcher, theme, device, browser, os
Requires at least: 3.0
Tested up to: 3.4
Stable tag: trunk

Lightweight detector of mobile devices, OSs & browsers. Optionally a theme switcher.

== Description ==

A lightweight detector of mobile devices, OSs & browsers that, optionally, allows your site to switch to a mobile theme when a mobile device is detected or on demand.

= Documentation =

This plugin adds the class **MobileDTS** to Wordpress.

**Mobile Detection**

On every request, this plugin will try to detect if the user is viewing your site with a mobile device or not. If he is, the detector will also give you some info about the device, browser and OS used.

How to query the detector?

Use the method `MobileDTS::is($key)`. `is()` returns boolean `true` or `false`. If key is not found, `is()` returns `null`. 

Example:
`<?php
if (MobileDTS::is('mobile')) {
	// User with a mobile device
}
?>`

Available keys:

* `mobile` (Is it a mobile?)
* `iphone` (Apple iPhone)
* `ipad` (Apple iPad)
* `kindle` (Amazon Kindle)
* `android` (Android OS)
* `bada` (Bada OS)
* `bbos` (Blackberry OS)
* `ios` (Apple iOS)
* `palmos` (Palm OS)
* `symbian` (Symbian OS)
* `webos` (Hp WebOS)
* `windows` (Windows Phone OS and older)
* `ff_mobile` (Mozilla Fennec & Firefox mobile)
* `ie_mobile` (IE mobile)
* `netfront` (NetFront)
* `opera_mobile` (Opera Mobile or Mini)
* `uc_browser` (UC Browser)
* `webkit_mobile` (Webkit mobile)

**Theme Switching**

From version 2.0 onwards, you can configure your site to automatically switch to a mobile theme when a mobile device is detected or when the user requests it (on demand).

How it works:

* On each page load, this plugin checks for the existence of a cookie that stores which theme (mobile-optimized or desktop-optimized) the user prefers to browse.
* If the cookie exists, the theme the user expects will be displayed.
* If the cookie does not exist (first-time visitor), this plugin checks whether the user is visiting your site with a mobile device or not and, if he is, your mobile-optimized theme will be used. Afterwards, a cookie will be set to store the user's "initial preference".
* Anytime the user switches* between themes, the cookie is updated with his preference so the site version (theme) the user expects will be displayed on future visits.

When you install the plugin a new submenu titled `Mobile Detector` is added under the `Settings` menu. There, you can select a theme optimized for mobile devices and the plugin will take care of the rest.

The plugin assumes your active theme is optimized for desktop screens only. So if you're using a responsive theme that adapts to any screen size you shouldn't use the theme switching feature.

Methods you can use in your themes:

`MobileDTS::get_switch_theme_link()`: Returns the URL to switch to the desktop/mobile theme. You'll need this function to allow a user to switch between desktop and mobile theme.

`MobileDTS::switch_theme_link()`: Same as above but this outputs the URL instead of returning it. URL is escaped.

`MobileDTS::get_switch_theme_name()`: Returns the type (string) of theme to switch to ('mobile' or 'desktop'). You may find this function useful to show the user the type of theme to switch to. The type can be translated to your language.

`MobileDTS::switch_theme_name()`: Same as above but this outputs the type instead of returning it.

`MobileDTS::is_mobile_theme()`: Tells you wether your site is using the mobile theme or not. Returns boolean `true` or `false`.

Example of a simple switch link in your theme (usually in header.php and/or footer.php):

`
<a href="<?php MobileDTS::switch_theme_link() ?>">Switch to the <?php MobileDTS::switch_theme_name() ?> version of this site</a>
`

= Documentation for older versions (v1.x) =

This plugin adds the following **global variables** (boolean values) to Wordpress:

**Is it a mobile?**

* `$is_mobile`

**Any famous device?**

* `$is_iphone` (Apple iPhone)
* `$is_ipad` (Apple iPad)
* `$is_kindle` (Amazon Kindle)

**What mobile OS?**

* `$is_android` (Android OS)
* `$is_bada` (Bada OS)
* `$is_bbos` (Blackberry OS)
* `$is_ios` (Apple iOS)
* `$is_palmos` (Palm OS)
* `$is_symbian` (Symbian OS)
* `$is_webos` (Hp WebOS)
* `$is_windows` (Windows Phone OS and older)

**What mobile browser?**

* `$is_firefox_mobile` (Mozilla Fennec)
* `$is_ie_mobile` (IE)
* `$is_netfront` (NetFront)
* `$is_opera_mobile` (Opera Mobile or Mini)
* `$is_uc_browser` (UC Browser)
* `$is_webkit_mobile` (Webkit)

The initial value of these variables is `false`.

If you need to debug the plugin, you can do it calling the global function: `margenn_mobile_detector(true)`. Calling this function will output the results.

== Changelog ==

= 2.0 =
* Complete rewrite. New API.
* Improved & updated mobile detection engine.
* Added theme switching.

= 1.1 =
* Code refactored. Cleaner to extend if you need to.
* Removed `$is_other_os` & `$is_other_browser`.

= 1.0 =
* Initial release.

== Installation ==

1. Upload `mobile-detector` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Done!
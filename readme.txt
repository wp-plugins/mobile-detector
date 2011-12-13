=== Plugin Name ===
Contributors: Tubal
Tags: mobile, detector, device, browser, os
Requires at least: 2.5
Tested up to: 3.3
Stable tag: trunk

Lightweight detector of mobile devices, OSs & browsers

== Description ==

A really simple and very lightweight plugin to detect mobile devices, mobile OSs & mobile browsers.

= Documentation =

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

You can test/debug the plugin results (debug mode) [here](http://www.margenn.com/tubal/mobile_detector/).

If you need to debug the plugin, you can do it calling the global function: `margenn_mobile_detector(true)`. Calling this function will output the results.

Suggestions?, bugs? report them in the plugin forum.

== Changelog ==

= 1.1 =
* Code refactored. Cleaner to extend if you need to.
* Removed `$is_other_os` & `$is_other_browser`.

== Installation ==

1. Upload `mobile-detector` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Done!
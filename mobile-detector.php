<?php
/*
Plugin Name: Mobile Detector
Description: A lightweight detector of mobile devices, OSs & browsers that additionally allows your site to switch to a mobile theme when a mobile device is detected or when the user requests it.
Version: 2.0.1
Author: Túbal Martín
Author URI: http://www.margenn.com
License: GPL2

Copyright 2012  Túbal Martín  (email : tubalmartin@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

if (!class_exists('MobileDTS')):

class MobileDTS {

    const CLASSNAME = 'MobileDTS';
    
    const MOBILE = 'mobile';
    const DESKTOP = 'desktop';
    const SWITCH_PARAM = 'switch_theme';

    // Settings API constants
    const OPTION_GROUP = 'tubal_mobiledts_options';
    const OPTION_NAME = 'tubal_mobiledts';
    const ERROR_SLUG = 'tubal-mobiledts-errors';

    private static $wp_version;
    private static $mobile_template;
    private static $mobile_stylesheet;
    private static $cookie_expiration;
    private static $cookie_name = 'wp_mobiledts_theme';
    private static $is_cookie_set = false;
    private static $is_switch_on = false;
    private static $dvars = array('mobile' => false);
    private static $regexps = array(
        // Devices
        'device' => array (
            'iphone' => 'iphone',
            'ipad' => 'ipad',
            'kindle' => 'kindle|silk',
        ),
        // OSs
        'os' => array(
            'ios' => 'ip(hone|ad|od)',
            'android' => 'android',
            'webos' => '(web|hpw)os',
            'palmos' => 'palm(\s?os|source)',
            'windows' => 'windows (phone|ce)',
            'symbian' => 'symbian(\s?os|)|symbos',
            'bbos' => 'blackberry(.*?version\/\d+|\d+\/\d+)',
            'bada' => 'bada'
        ),
        // Browsers
        'browser' => array(
            'opera_mobile' => 'opera (mobi|tablet|mini)', // Opera Mobile or Mini
            'webkit_mobile' => '(android|nokia|symbianos|webos|hpwos|blackberry|bolt|silk).*?webkit|webkit.*?(mobile|crmo|kindle|bolt|skyfire|ninesky|dolfin|iris|teashark|tear)|atomicbrowser', // Webkit mobile
            'ff_mobile' => 'fennec|(mobile|tablet).*?firefox', // Firefox mobile https://developer.mozilla.org/en/Gecko_user_agent_string_reference
            'ie_mobile' => 'iemobile|windows (ce|phone)|(zune|xbl)wp\d{1}', // IE mobile
            'netfront' => 'netfront|blazer|jasmine', // Netfront
            'uc_browser' => 'ucweb' // UC browser
        ),
        // UA Fallbacks
        'fallback' => array(
             'other' => 'nokia|motorola|sony|ericsson|lge?(-|;|\/|\s)|htc|samsung|asus|mobile|phone|tablet|pocket|wap|wireless|up\.browser|up\.link|j2me|midp|cldc|kddi|mmp|obigo|novarra|teleca|openwave|uzardweb|pre\/|hiptop|minimo|avantgo|plucker|xiino|elaine|vodafone|sprint|o2'
        )
    );


    public static function init() {
        self::detect_device();
        self::switch_theme();
    }

    private static function set_wp_version() {
        global $wp_version;
        
        $wpversion = explode('.', $wp_version);
        
        if (count($wpversion < 3)) {
            $wpversion[2] = 0;  
        }
        
        self::$wp_version = $wpversion[0] * 10000 + $wpversion[1] * 100 + $wpversion[2];
    }


    // DETECTION METHODS


    private static function detect_device() {
        // User Agent detection
        $ua = '';

        // Header names taken from DeviceAtlas
        $ua_headers = array(
            'HTTP_X_DEVICE_USER_AGENT',
            'HTTP_X_ORIGINAL_USER_AGENT',
            'HTTP_X_OPERAMINI_PHONE_UA',
            'HTTP_X_SKYFIRE_PHONE',
            'HTTP_X_BOLT_PHONE_UA',
            'HTTP_USER_AGENT'
        );

        foreach ($ua_headers as $header) {
            if (array_key_exists($header, $_SERVER)) {
                $ua = $_SERVER[$header];
                break;
            }
        }

        $isset_ua = !empty($ua);

        foreach (self::$regexps as $group) {
            foreach ($group as $name => $regex) {
                // If match, add it to $dvars, set to true and check next group
                if ($isset_ua && preg_match('/'.$regex.'/i', $ua)) {
                    self::$dvars[self::MOBILE] = self::$dvars[$name] = true;
                    break;
                }
            }
        }

        // HTTP HEADERS fallback detection
        if (self::$dvars[self::MOBILE] === false) {
            if (false !== strpos($_SERVER['HTTP_ACCEPT'], 'text/vnd.wap.wml')
                || false !== strpos($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml')
                || isset($_SERVER['HTTP_X_WAP_PROFILE'])
                || isset($_SERVER['HTTP_PROFILE'])
            ) {
                self::$dvars[self::MOBILE] = true;
            }
        }
    }

    public static function is($what) {
        return array_key_exists($what, self::$dvars) ? self::$dvars[$what] : false;
    }


    // THEME SWITCHING


    private static function switch_theme() {
        $options = get_option(self::OPTION_NAME);

        // Theme switching configured? store values & add filters!
        if ($options !== false && !empty($options['mobile_theme'])) {
            list($mobile_stylesheet, $mobile_template) = explode('|', $options['mobile_theme']);

            self::$is_switch_on = true;
            self::$mobile_stylesheet = $mobile_stylesheet;
            self::$mobile_template = $mobile_template;
            self::$cookie_expiration = time() + (6 * 30 * 24 * 60 * 60); // 6 months

            add_filter('stylesheet', array(self::CLASSNAME, 'switch_theme_stylesheet'));
            add_filter('template', array(self::CLASSNAME, 'switch_theme_template'));
        }
    }

    public static function switch_theme_stylesheet($current) {
        return !isset(self::$mobile_stylesheet) ? $current : self::switch_theme_file($current, self::$mobile_stylesheet);
    }

    public static function switch_theme_template($current) {
        return !isset(self::$mobile_template) ? $current : self::switch_theme_file($current, self::$mobile_template);
    }

    private static function switch_theme_file($current, $mobile) {
        // Does the user want to switch themes?
        if (isset($_GET[self::SWITCH_PARAM])) {
            // Store cookie
            if (!self::$is_cookie_set) {
                setcookie(self::$cookie_name, $_GET[self::SWITCH_PARAM], self::$cookie_expiration);
                self::$is_cookie_set = true;
            }
            // Switch theme if needed
            return $_GET[self::SWITCH_PARAM] == self::DESKTOP ? $current : $mobile;
        } else {
            // No theme preference
            if (!isset($_COOKIE[self::$cookie_name])) {
                // Detect & store initial preference
                if (!self::$is_cookie_set) {
                    setcookie(self::$cookie_name, (self::is(self::MOBILE) ? self::MOBILE : self::DESKTOP), self::$cookie_expiration);
                    self::$is_cookie_set = true;
                }
                // Switch theme if it's a mobile device
                return !self::is(self::MOBILE) ? $current : $mobile;
            } else {
                // Theme version displayed
                $preferred = trim($_COOKIE[self::$cookie_name]);
                // Switch theme if needed
                return $preferred == self::DESKTOP ? $current : $mobile;
            }
        }
    }

    public static function get_switch_theme_link() {
        // Uncomment if testing Android devices locally
        /*if (self::is('android')) {
            $_SERVER['SERVER_NAME'] = preg_replace('/localhost/i', '10.0.2.2', $_SERVER['SERVER_NAME']);
        }*/

        $current_url = 'http'. (is_ssl() ? 's' : '') .'://'. $_SERVER['SERVER_NAME']
            . ($_SERVER['SERVER_PORT'] != '80' ? ':'. $_SERVER['SERVER_PORT'] : '')
            . $_SERVER['REQUEST_URI'];

        // Which theme to link to, mobile or desktop?
        $theme = self::get_switch_theme_name();

        // Add or replace our param
        if (isset($_GET[self::SWITCH_PARAM])) {
            if ($_GET[self::SWITCH_PARAM] != $theme) {
                $current_url = preg_replace('/('.self::SWITCH_PARAM.'=)('.self::MOBILE.'|'.self::DESKTOP.')/i', '$1'.$theme, $current_url);
            }
        } else {
            $current_url .= (strpos($current_url, '?') === false ? '?' : '&') .self::SWITCH_PARAM .'='. $theme;
        }

        return $current_url;
    }

    public static function switch_theme_link() {
        echo esc_url(self::get_switch_theme_link());
    }

    public static function get_switch_theme_name() {
        if (isset($_GET[self::SWITCH_PARAM])) {
            $theme = $_GET[self::SWITCH_PARAM] == self::MOBILE ? self::DESKTOP : self::MOBILE;
        } else if (isset($_COOKIE[self::$cookie_name])) {
            $theme = trim($_COOKIE[self::$cookie_name]) == self::MOBILE ? self::DESKTOP : self::MOBILE;
        } else {
            $theme = self::is(self::MOBILE) ? self::DESKTOP : self::MOBILE;
        }

        return __($theme); // Allow to be translated
    }

    public static function switch_theme_name() {
        echo self::get_switch_theme_name();
    }

    public static function is_mobile_theme() {
        return self::$is_switch_on && self::get_switch_theme_name() == self::DESKTOP ? true : false;
    }


    // ADMIN METHODS


    public static function admin_init() {
        self::set_wp_version();
        // Register settings
        register_setting(self::OPTION_GROUP, self::OPTION_NAME, array(self::CLASSNAME, 'admin_options_validation'));
    }

    public static function admin_menu() {
        // Add a submenu under Settings menu
        add_options_page(
            'Mobile Detector - Theme switching settings',
            'Mobile Detector',
            'switch_themes',
            'tubal-mobiledts',
            array(self::CLASSNAME, 'admin_options_page')
        );
    }

    public static function admin_options_page() {
        // Must check that the user has the required capability
        if (!current_user_can('switch_themes')) {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }
?>
        <div class="wrap">
            <h2>Mobile Detector - Theme switching settings</h2>
            <?php settings_errors(self::ERROR_SLUG) ?>
            <p>Assuming you have a mobile-optimized theme for your website, you can enable theme switching to provide the following behavior to your site:</p>
            <ol>
                <li>On each page load, this plugin checks for the existence of a cookie that stores which theme (mobile-optimized or desktop-optimized) the user prefers to browse.</li>
                <li>If the cookie exists, the theme the user expects will be displayed.</li>
                <li>If the cookie does not exist (first-time visitor), this plugin checks whether the user is visiting your site with a mobile device or not and, if he is, your mobile-optimized theme will be used. Afterwards, a cookie will be set to store the user's "initial preference".</li>
                <li>Anytime the user switches* between themes, the cookie is updated with his preference so the site version (theme) the user expects will be displayed on future visits.</li>
            </ol>
            <p>*: <span class="description">Give the user the option to switch between themes (links in the header and/or footer).</span></p>
            <form method="post" action="options.php">
                <?php
                    settings_fields(self::OPTION_GROUP);
                    $options = get_option(self::OPTION_NAME);
                    // Get the list of themes.
                    $themes = self::$wp_version < 30400 ? get_themes() /* WP < 3.4.0 */ : wp_get_themes() /* WP >= 3.4.0 */;
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><strong>Mobile</strong> theme <span class="description"><?php _e('(required)'); ?></span></th>
                        <td>
                            <select name="<?php echo self::OPTION_NAME ?>[mobile_theme]">
                                <option value=""><?php _e('- Select a theme -') ?></option>
                                <?php if (self::$wp_version < 30400): ?>
                                    <?php foreach($themes as $theme): ?>
                                        <option value="<?php echo $theme['Stylesheet'].'|'.$theme['Template'] ?>" <?php selected( $options['mobile_theme'], $theme['Stylesheet'].'|'.$theme['Template'] ) ?>><?php echo $theme['Name'] ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php foreach($themes as $theme): ?>
                                        <option value="<?php echo $theme->get_stylesheet().'|'.$theme->get_template() ?>" <?php selected( $options['mobile_theme'], $theme->get_stylesheet().'|'.$theme->get_template() ) ?>><?php echo $theme->display('Name') ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>                 
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                </p>
            </form>
        </div>
<?php
    }

    public static function admin_options_validation($input) {
        if (empty($input['mobile_theme'])) {
            add_settings_error(self::ERROR_SLUG, 'mobile-theme', __('You must select a mobile theme!'));
        } else {
            add_settings_error(self::ERROR_SLUG, 'mobile-theme', __('Settings saved.'), 'updated');
        }

        return $input;
    }

}

// Admin (options page)
if (is_admin()) {
    add_action('admin_init', array('MobileDTS', 'admin_init'));
    add_action('admin_menu', array('MobileDTS', 'admin_menu'));
// Public site
} else {
    add_action('plugins_loaded', array('MobileDTS', 'init'));
}

endif;
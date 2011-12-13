<?php
/*
Plugin Name: Mobile detector
Description: Lightweight detector of mobile devices, OSs & browsers
Version: 1.1
Author: Túbal Martín
Author URI: http://www.margenn.com
License: GPL2

Copyright 2011  Túbal Martín  (email : tubalmartin@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

if ( ! function_exists('margenn_mobile_detector') )
{
    // Global vars
    $is_mobile = false;
    $is_iphone = $is_ipad = $is_kindle = false;
    $is_ios = $is_android = $is_webos = $is_palmos = $is_windows = $is_symbian = $is_bbos = $is_bada = false;
    $is_opera_mobile = $is_webkit_mobile = $is_firefox_mobile = $is_ie_mobile = $is_netfront = $is_uc_browser = false;


    function margenn_mobile_detector($debug = false)
    {
        global $is_mobile;

        // Check user agent string
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        if (empty($agent)) {
            return;
        }

        $mobile_devices = array(
            'is_iphone' => 'iphone',
            'is_ipad' => 'ipad',
            'is_kindle' => 'kindle'
        );
        
        $mobile_oss = array(
            'is_ios' => 'ip(hone|ad|od)',
            'is_android' => 'android',
            'is_webos' => '(web|hpw)os',
            'is_palmos' => 'palm(\s?os|source)',
            'is_windows' => 'windows (phone|ce)',
            'is_symbian' => 'symbian(\s?os|)|symbos',
            'is_bbos' => 'blackberry(.*?version\/\d+|\d+\/\d+)',
            'is_bada' => 'bada'
        );
        
        $mobile_browsers = array(
            'is_opera_mobile' => 'opera (mobi|mini)', // Opera Mobile or Mini
            'is_webkit_mobile' => '(android|nokia|webos|hpwos|blackberry).*?webkit|webkit.*?(mobile|kindle|bolt|skyfire|dolfin|iris)', // Webkit mobile
            'is_firefox_mobile' => 'fennec', // Firefox mobile
            'is_ie_mobile' => 'iemobile|windows ce', // IE mobile
            'is_netfront' => 'netfront|kindle|psp|blazer|jasmine', // Netfront
            'is_uc_browser' => 'ucweb' // UC browser
        );
        
        $groups = array($mobile_devices, $mobile_oss, $mobile_browsers);
        
        foreach ($groups as $group) {
            foreach ($group as $name => $regex) {
                if (preg_match('/'.$regex.'/i', $agent)) {
                    global $$name;
                    $is_mobile = $$name = true;
                    break;
                }
            }
        }
        
        // Fallbacks
        if ($is_mobile === false) {
            $regex = 'nokia|motorola|sony|ericsson|lge?(-|;|\/|\s)|htc|samsung|asus|mobile|phone|tablet|pocket|wap|wireless|up\.browser|up\.link|j2me|midp|cldc|kddi|mmp|obigo|novarra|teleca|openwave|uzardweb|pre\/|hiptop|avantgo|plucker|xiino|elaine|vodafone|sprint|o2';
            $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';

            if (false !== strpos($accept,'text/vnd.wap.wml')
                || false !== strpos($accept,'application/vnd.wap.xhtml+xml')
                || isset($_SERVER['HTTP_X_WAP_PROFILE'])
                || isset($_SERVER['HTTP_PROFILE'])
                || preg_match('/'.$regex.'/i', $agent)
            ) {
                $is_mobile = true;
            }
        }

        // DEBUGGER OUTPUT
        if ($debug === true) {
            echo '<strong>User Agent: '.$agent.'</strong><br>';
            foreach ($GLOBALS as $k => $v) {
                if (strpos($k, 'is_') !== false) {
                    echo '<span style="color:'.($v ? 'green':'red').';">$'.$k.'</span><br>';
                }
            }
        }

    }

    // execute inmmediatly
    margenn_mobile_detector();

}
<?php

/**
 * Formidable AutoBackup
 *
 * @package     FormidableAutoBackup
 * @author      Henri Susanto
 * @copyright   2022 Henri Susanto
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Formidable AutoBackup
 * Plugin URI:  https://github.com/susantohenri
 * Description: Formidable Add on to periodically backup everything to XML
 * Version:     1.0.0
 * Author:      Henri Susanto
 * Author URI:  https://github.com/susantohenri
 * Text Domain: Formidable-AutoBackup
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

/*
curl -L 'http://rbundle.com/wp-json/formidable-auto-backup/v1/run?uname=russell.guilfoile@rbundle.com&pwd=t$pv(*ARzV0e@ZKBfrmJShcX' -o /home/u852799524/domains/rbundle.com/public_html/wp-content/plugins/formidable-auto-backup/formidable-backup`date +'-%Y-%m-%d@%H:%M:%S'`.xml
*/
add_action('rest_api_init', function () {
    register_rest_route('formidable-auto-backup/v1', '/run', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function () {
            $uname = $_GET['uname'];
            $pwd = $_GET['pwd'];
            $cookie_file = plugin_dir_path(__FILE__) . 'formidable-auto-backup.cookie';

            $login = curl_init();
            curl_setopt($login, CURLOPT_URL, site_url('wp-login.php'));
            curl_setopt($login, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($login, CURLOPT_COOKIEJAR, $cookie_file);
            curl_setopt($login, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($login, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($login, CURLOPT_POST, 1);
            curl_setopt($login, CURLOPT_POSTFIELDS, "log={$uname}&pwd={$pwd}");
            curl_setopt($login, CURLOPT_ENCODING, 'gzip, deflate');
            curl_exec($login);
            if (curl_errno($login)) return 'Error:' . curl_error($login);
            curl_close($login);

            $wordpress_logged_in_ = file_get_contents($cookie_file);
            $wordpress_logged_in_ = explode('wordpress_logged_in_', $wordpress_logged_in_);
            $wordpress_logged_in_ = $wordpress_logged_in_[1];
            $wordpress_logged_in_ = explode('#', $wordpress_logged_in_);
            $wordpress_logged_in_ = $wordpress_logged_in_[0];
            $wordpress_logged_in_ = str_replace("\t", '=', $wordpress_logged_in_);
            $wordpress_logged_in_ = str_replace("\n", '', $wordpress_logged_in_);
            $wordpress_logged_in_ = 'wordpress_logged_in_'.$wordpress_logged_in_;

            $wordpress_sec_ = file_get_contents($cookie_file);
            $wordpress_sec_ = explode('wordpress_sec_', $wordpress_sec_);
            $wordpress_sec_ = $wordpress_sec_[1];
            $wordpress_sec_ = explode('#', $wordpress_sec_);
            $wordpress_sec_ = $wordpress_sec_[0];
            $wordpress_sec_ = str_replace("\t", '=', $wordpress_sec_);
            $wordpress_sec_ = str_replace("\n", '', $wordpress_sec_);
            $wordpress_sec_ = 'wordpress_sec_'.$wordpress_sec_;

            $cookie = "{$wordpress_sec_}; {$wordpress_logged_in_};";

            $export_form = curl_init();
            curl_setopt($export_form, CURLOPT_URL, site_url('wp-admin/admin.php?page=formidable-import'));
            curl_setopt($export_form, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($export_form, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($export_form, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($export_form, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($export_form, CURLOPT_ENCODING, 'gzip, deflate');
            curl_setopt($export_form, CURLOPT_HTTPHEADER, ["Cookie: {$cookie}"]);

            $export_page = curl_exec($export_form);
            if (curl_errno($export_form)) return 'Error:' . curl_error($export_form);
            curl_close($export_form);

            $nonce = explode('export-xml" value="', $export_page);
            $nonce = explode('"', $nonce[1]);
            $nonce = $nonce[0];

            global $wpdb;
            $query = $wpdb->prepare("SELECT id FROM {$wpdb->prefix}frm_forms");
            $all_forms = $wpdb->get_results($query);
            $all_forms = array_map(function ($form) {
                return "frm_export_forms%5B%5D={$form->id}";
            }, $all_forms);
            $all_forms = implode('&', $all_forms);

            $curl_export = curl_init();
            curl_setopt($curl_export, CURLOPT_URL, site_url('wp-admin/admin-ajax.php'));
            curl_setopt($curl_export, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl_export, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl_export, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_export, CURLOPT_POST, 1);
            curl_setopt($curl_export, CURLOPT_POSTFIELDS, "action=frm_export_xml&export-xml={$nonce}&_wp_http_referer=%2Fwp-admin%2Fadmin.php%3Fpage%3Dformidable-import&format=xml&csv_format=UTF-8&csv_col_sep=%2C&type%5B%5D=forms&type%5B%5D=items&type%5B%5D=posts&type%5B%5D=styles&s=&{$all_forms}");
            curl_setopt($curl_export, CURLOPT_ENCODING, 'gzip, deflate');
            curl_setopt($curl_export, CURLOPT_HTTPHEADER, ["Cookie: {$cookie}"]);
            $export_result = curl_exec($curl_export);
            if (curl_errno($curl_export)) return 'Error:' . curl_error($curl_export);
            curl_close($curl_export);

            unlink($cookie_file);

            header("Content-Type: application/force-download; name=\"formidable-backup.xml");
            header("Content-type: text/xml");
            header("Content-Transfer-Encoding: binary");
            header("Content-Disposition: attachment; filename=\"formidable-backup.xml");
            header("Expires: 0");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
            echo $export_result;
        }
    ));
});
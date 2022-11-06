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

// curl -L 'http://localhost/wordpress/wp-json/formidable-auto-backup/v1/run?uname=admin&pwd=admin' -o formidable-backup`date +'-%Y-%m-%d@%H:%M:%S'`.xml
add_action('rest_api_init', function () {
    register_rest_route('formidable-auto-backup/v1', '/run', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function () {
            $uname = $_GET['uname'];
            $pwd = $_GET['pwd'];
            $login_url = site_url('wp-login.php');
            $cookie_file = plugin_dir_path(__FILE__) . 'formidable-auto-backup.cookie';
            $export_url = site_url('wp-admin/admin-ajax.php');
            $form_url = site_url('wp-admin/admin.php?page=formidable-import');

            $curl_login = curl_init();
            curl_setopt($curl_login, CURLOPT_COOKIEJAR, $cookie_file);
            curl_setopt($curl_login, CURLOPT_COOKIE, $cookie_file);
            curl_setopt($curl_login, CURLOPT_URL, $login_url);
            curl_setopt($curl_login, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_login, CURLOPT_POST, 1);
            curl_setopt($curl_login, CURLOPT_POSTFIELDS, "log={$uname}&pwd={$pwd}&wp-submit=Log+In");
            curl_setopt($curl_login, CURLOPT_ENCODING, 'gzip, deflate');
            $headers = array();
            $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';
            $headers[] = 'Accept-Language: en-US,en;q=0.9';
            $headers[] = 'Cache-Control: no-cache';
            $headers[] = 'Connection: keep-alive';
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $headers[] = 'Origin: ' . get_http_origin();
            $headers[] = 'Pragma: no-cache';
            $headers[] = "Referer: {$login_url}";
            $headers[] = 'Sec-Fetch-Dest: document';
            $headers[] = 'Sec-Fetch-Mode: navigate';
            $headers[] = 'Sec-Fetch-Site: same-origin';
            $headers[] = 'Sec-Fetch-User: ?1';
            $headers[] = 'Upgrade-Insecure-Requests: 1';
            $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36';
            $headers[] = 'Sec-Ch-Ua: \".Not/A)Brand\";v=\"99\", \"Google Chrome\";v=\"103\", \"Chromium\";v=\"103\"';
            $headers[] = 'Sec-Ch-Ua-Mobile: ?0';
            $headers[] = 'Sec-Ch-Ua-Platform: \"macOS\"';
            curl_setopt($curl_login, CURLOPT_HTTPHEADER, $headers);
            curl_exec($curl_login);
            if (curl_errno($curl_login)) return 'Error:' . curl_error($curl_login);
            curl_close($curl_login);

            $cookie = file_get_contents($cookie_file);
            $cookie = explode('wordpress_logged_in_', $cookie);
            $cookie = $cookie[1];
            $cookie = str_replace("\t", '=', $cookie);
            $cookie = str_replace("\n", '', $cookie);

            unlink($cookie_file);

            $curl_form_page = curl_init();
            curl_setopt($curl_form_page, CURLOPT_URL, $form_url);
            curl_setopt($curl_form_page, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_form_page, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl_form_page, CURLOPT_ENCODING, 'gzip, deflate');
            $headers = array();
            $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';
            $headers[] = 'Accept-Language: en-US,en;q=0.9';
            $headers[] = 'Cache-Control: no-cache';
            $headers[] = 'Connection: keep-alive';
            $headers[] = "Cookie: wordpress_sec_bbfa5b726c6b7a9cf3cda9370be3ee91=admin%7C1667880997%7C0EqJgQc1evJRJzEDeT0mIgPZ6179d9YzVyQABZknK0H%7Cf2f438adb8beb10752e4825d675c6e6558cb937f90c9ecf3121d66a903a84f23; frm_form4_bbfa5b726c6b7a9cf3cda9370be3ee91=2022-10-13+22%3A02%3A56; wordpress_test_cookie=WP+Cookie+check; wp_lang=en_US; wordpress_logged_in_{$cookie}; wp-settings-1=libraryContent%3Dbrowse%26editor%3Dhtml; wp-settings-time-1=1667708197; _y=a03b1f30-75BE-4B0D-183A-DDB606FEEA6D; _shopify_y=a03b1f30-75BE-4B0D-183A-DDB606FEEA6D; _gcl_au=1.1.612578835.1665062576; _ga=GA1.1.127531313.1665062576; _ga_4TJTN0BNL6=GS1.1.1665096596.3.0.1665096596.0.0.0; _ga_0GR1XJYZ1D=GS1.1.1665096596.3.0.1665096596.0.0.0; wp-settings-time-1=1665905946";
            $headers[] = 'Pragma: no-cache';
            $headers[] = 'Referer: ' . $form_url;
            $headers[] = 'Sec-Fetch-Dest: document';
            $headers[] = 'Sec-Fetch-Mode: navigate';
            $headers[] = 'Sec-Fetch-Site: same-origin';
            $headers[] = 'Sec-Fetch-User: ?1';
            $headers[] = 'Upgrade-Insecure-Requests: 1';
            $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36';
            $headers[] = 'Sec-Ch-Ua: \".Not/A)Brand\";v=\"99\", \"Google Chrome\";v=\"103\", \"Chromium\";v=\"103\"';
            $headers[] = 'Sec-Ch-Ua-Mobile: ?0';
            $headers[] = 'Sec-Ch-Ua-Platform: \"macOS\"';
            curl_setopt($curl_form_page, CURLOPT_HTTPHEADER, $headers);
            $export_page = curl_exec($curl_form_page);
            if (curl_errno($curl_form_page)) return 'Error:' . curl_error($curl_form_page);
            curl_close($curl_form_page);

            $nonce = explode('export-xml" value="', $export_page);
            $nonce = explode('"', $nonce[1]);
            $nonce = $nonce[0];

            global $wpdb;
            $query = $wpdb->prepare("SELECT id FROM {$wpdb->prefix}frm_forms");
            $all_forms = $wpdb->get_results($query);
            $all_forms = array_map(function ($form) {
                return "frm_export_forms[]={$form->id}";
            }, $all_forms);
            $all_forms = implode('&', $all_forms);
            $all_forms = urlencode($all_forms);

            $curl_export = curl_init();
            curl_setopt($curl_export, CURLOPT_URL, $export_url);
            curl_setopt($curl_export, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_export, CURLOPT_POST, 1);
            curl_setopt($curl_export, CURLOPT_POSTFIELDS, "action=frm_export_xml&export-xml={$nonce}&_wp_http_referer=%2Fwordpress%2Fwp-admin%2Fadmin.php%3Fpage%3Dformidable-import&format=xml&csv_format=UTF-8&csv_col_sep=%2C&type%5B%5D=forms&s=&{$all_forms}");
            curl_setopt($curl_export, CURLOPT_ENCODING, 'gzip, deflate');
            $headers = array();
            $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';
            $headers[] = 'Accept-Language: en-US,en;q=0.9';
            $headers[] = 'Cache-Control: no-cache';
            $headers[] = 'Connection: keep-alive';
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $headers[] = "Cookie: wordpress_sec_bbfa5b726c6b7a9cf3cda9370be3ee91=admin%7C1667880997%7C0EqJgQc1evJRJzEDeT0mIgPZ6179d9YzVyQABZknK0H%7Cf2f438adb8beb10752e4825d675c6e6558cb937f90c9ecf3121d66a903a84f23; frm_form4_bbfa5b726c6b7a9cf3cda9370be3ee91=2022-10-13+22%3A02%3A56; wordpress_test_cookie=WP+Cookie+check; wp_lang=en_US; wordpress_logged_in_{$cookie}; wp-settings-1=libraryContent%3Dbrowse%26editor%3Dhtml; wp-settings-time-1=1667708197; _y=a03b1f30-75BE-4B0D-183A-DDB606FEEA6D; _shopify_y=a03b1f30-75BE-4B0D-183A-DDB606FEEA6D; _gcl_au=1.1.612578835.1665062576; _ga=GA1.1.127531313.1665062576; _ga_4TJTN0BNL6=GS1.1.1665096596.3.0.1665096596.0.0.0; _ga_0GR1XJYZ1D=GS1.1.1665096596.3.0.1665096596.0.0.0; wp-settings-time-1=1665905946";
            $headers[] = 'Origin: ' . get_http_origin();
            $headers[] = 'Pragma: no-cache';
            $headers[] = 'Referer: ' . $form_url;
            $headers[] = 'Sec-Fetch-Dest: document';
            $headers[] = 'Sec-Fetch-Mode: navigate';
            $headers[] = 'Sec-Fetch-Site: same-origin';
            $headers[] = 'Sec-Fetch-User: ?1';
            $headers[] = 'Upgrade-Insecure-Requests: 1';
            $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36';
            $headers[] = 'Sec-Ch-Ua: \".Not/A)Brand\";v=\"99\", \"Google Chrome\";v=\"103\", \"Chromium\";v=\"103\"';
            $headers[] = 'Sec-Ch-Ua-Mobile: ?0';
            $headers[] = 'Sec-Ch-Ua-Platform: \"macOS\"';
            curl_setopt($curl_export, CURLOPT_HTTPHEADER, $headers);
            $export_result = curl_exec($curl_export);
            if (curl_errno($curl_export)) return 'Error:' . curl_error($curl_export);
            curl_close($curl_export);

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
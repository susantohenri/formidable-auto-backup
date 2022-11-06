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

// clear;curl 'http://localhost/wordpress/wp-json/formidable-auto-backup/v1/run?uname=admin&pwd=admin'
add_action('rest_api_init', function () {
    // register_rest_route('formidable-auto-backup/v1', '/create_nonce', array(
    //     'methods' => 'GET',
    //     'permission_callback' => '__return_true',
    //     'callback' => function () {
    //         return wp_create_nonce('export-xml');
    //     }
    // ));
    register_rest_route('formidable-auto-backup/v1', '/run', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function () {
            $uname = $_GET['uname'];
            $pwd = $_GET['pwd'];
            $login_url = str_replace('https', 'http', site_url('wp-login.php'));
            $nonce_url = str_replace('https', 'http', site_url('wp-json/formidable-auto-backup/v1/create_nonce'));
            $cookie_file = plugin_dir_path( __FILE__ ) . 'formidable-auto-backup.cookie';
            $export_url = str_replace('https', 'http', site_url('wp-admin/admin-ajax.php'));
            $form_url = str_replace('https', 'http', site_url('wp-admin/admin.php?page=formidable-import'));

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
            // $headers[] = 'Cookie: frm_form4_bbfa5b726c6b7a9cf3cda9370be3ee91=2022-10-13+22%3A02%3A56; wp-settings-1=libraryContent%3Dbrowse%26editor%3Dhtml; wp-settings-time-1=1667630627; wordpress_test_cookie=WP+Cookie+check; wordpress_logged_in_bbfa5b726c6b7a9cf3cda9370be3ee91=admin%7C1667875420%7CSTfFdDHVmUyEyNdAmCBDDym8NXlI4fJM2LryfOBZICt%7C60a0dd84987b8a0c1ab4761ccd5da5ad123876f2277312200b3c30b62f1924ae; _y=a03b1f30-75BE-4B0D-183A-DDB606FEEA6D; _shopify_y=a03b1f30-75BE-4B0D-183A-DDB606FEEA6D; _gcl_au=1.1.612578835.1665062576; _ga=GA1.1.127531313.1665062576; _ga_4TJTN0BNL6=GS1.1.1665096596.3.0.1665096596.0.0.0; _ga_0GR1XJYZ1D=GS1.1.1665096596.3.0.1665096596.0.0.0; wp-settings-time-1=1665905946';
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
            $cookie = str_replace("\n", ';', $cookie);

            $curl_nonce = curl_init();
            curl_setopt($curl_nonce, CURLOPT_URL, $nonce_url);
            curl_setopt($curl_nonce, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_nonce, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl_nonce, CURLOPT_ENCODING, 'gzip, deflate');
            $headers = array();
            $headers[] = 'Accept: */*';
            $headers[] = 'Accept-Language: en-US,en;q=0.9';
            $headers[] = 'Cache-Control: no-cache';
            $headers[] = 'Connection: keep-alive';
            $headers[] = "Cookie: wordpress_sec_bbfa5b726c6b7a9cf3cda9370be3ee91=admin%7C1667875427%7CjQ50UVOKGhnQWFYw8Ti2BWnu1J8vv8uzrdY1mON82u3%7C3098604a48640b8c1bb2c8065e298cdf0832f98148e7b3bd0dfbd00c67d7b392; frm_form4_bbfa5b726c6b7a9cf3cda9370be3ee91=2022-10-13+22%3A02%3A56; wp-settings-1=libraryContent%3Dbrowse%26editor%3Dhtml; wp-settings-time-1=1667630627; wordpress_test_cookie=WP+Cookie+check; wordpress_logged_in_{$cookie}; _y=a03b1f30-75BE-4B0D-183A-DDB606FEEA6D; _shopify_y=a03b1f30-75BE-4B0D-183A-DDB606FEEA6D; _gcl_au=1.1.612578835.1665062576; _ga=GA1.1.127531313.1665062576; _ga_4TJTN0BNL6=GS1.1.1665096596.3.0.1665096596.0.0.0; _ga_0GR1XJYZ1D=GS1.1.1665096596.3.0.1665096596.0.0.0; wp-settings-time-1=1665905946";
            $headers[] = 'Pragma: no-cache';
            $headers[] = 'Referer: ' . $form_url;
            $headers[] = 'Sec-Fetch-Dest: empty';
            $headers[] = 'Sec-Fetch-Mode: cors';
            $headers[] = 'Sec-Fetch-Site: same-origin';
            $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36';
            $headers[] = 'X-Requested-With: XMLHttpRequest';
            $headers[] = 'Sec-Ch-Ua: \".Not/A)Brand\";v=\"99\", \"Google Chrome\";v=\"103\", \"Chromium\";v=\"103\"';
            $headers[] = 'Sec-Ch-Ua-Mobile: ?0';
            $headers[] = 'Sec-Ch-Ua-Platform: \"macOS\"';
            curl_setopt($curl_nonce, CURLOPT_HTTPHEADER, $headers);
            
            $nonce = curl_exec($curl_nonce);
            $nonce = trim($nonce, '"');
            if (curl_errno($curl_nonce)) return 'Error:' . curl_error($curl_nonce);
            curl_close($curl_nonce);

            // unlink($cookie_file);

            $curl_export = curl_init();
            curl_setopt($curl_export, CURLOPT_URL, $export_url);
            curl_setopt($curl_export, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_export, CURLOPT_POST, 1);
            curl_setopt($curl_export, CURLOPT_POSTFIELDS, "action=frm_export_xml&export-xml={$nonce}&_wp_http_referer=%2Fwordpress%2Fwp-admin%2Fadmin.php%3Fpage%3Dformidable-import&format=xml&csv_format=UTF-8&csv_col_sep=%2C&type%5B%5D=forms&s=&frm_export_forms%5B%5D=2");
            curl_setopt($curl_export, CURLOPT_ENCODING, 'gzip, deflate');
            $headers = array();
            $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';
            $headers[] = 'Accept-Language: en-US,en;q=0.9';
            $headers[] = 'Cache-Control: no-cache';
            $headers[] = 'Connection: keep-alive';
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $headers[] = "Cookie: wordpress_sec_bbfa5b726c6b7a9cf3cda9370be3ee91=admin%7C1667875427%7CjQ50UVOKGhnQWFYw8Ti2BWnu1J8vv8uzrdY1mON82u3%7C3098604a48640b8c1bb2c8065e298cdf0832f98148e7b3bd0dfbd00c67d7b392; frm_form4_bbfa5b726c6b7a9cf3cda9370be3ee91=2022-10-13+22%3A02%3A56; wp-settings-1=libraryContent%3Dbrowse%26editor%3Dhtml; wp-settings-time-1=1667630627; wordpress_test_cookie=WP+Cookie+check; wordpress_logged_in_{$cookie}; _y=a03b1f30-75BE-4B0D-183A-DDB606FEEA6D; _shopify_y=a03b1f30-75BE-4B0D-183A-DDB606FEEA6D; _gcl_au=1.1.612578835.1665062576; _ga=GA1.1.127531313.1665062576; _ga_4TJTN0BNL6=GS1.1.1665096596.3.0.1665096596.0.0.0; _ga_0GR1XJYZ1D=GS1.1.1665096596.3.0.1665096596.0.0.0; wp-settings-time-1=1665905946";
            $headers[] = 'Origin: ' . get_http_origin();
            $headers[] = 'Pragma: no-cache';
            $headers[] = "Referer: {$form_url}";
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

            return "action=frm_export_xml&export-xml={$nonce}&_wp_http_referer=%2Fwordpress%2Fwp-admin%2Fadmin.php%3Fpage%3Dformidable-import&format=xml&csv_format=UTF-8&csv_col_sep=%2C&type%5B%5D=forms&s=&frm_export_forms%5B%5D=2";
            // return $export_result;
        }
    ));
});

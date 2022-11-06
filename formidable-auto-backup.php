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
    register_rest_route('formidable-auto-backup/v1', '/run', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function () {
            $uname = $_GET['uname'];
            $pwd = $_GET['pwd'];
            $login_url = str_replace('https', 'http', site_url('wp-login.php'));
            $cookie_file = plugin_dir_path( __FILE__ ) . 'formidable-auto-backup.cookie';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
            curl_setopt($ch, CURLOPT_COOKIE, $cookie_file);
            curl_setopt($ch, CURLOPT_URL, $login_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "log={$uname}&pwd={$pwd}&wp-submit=Log+In");
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
            $headers = array();
            $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';
            $headers[] = 'Accept-Language: en-US,en;q=0.9';
            $headers[] = 'Cache-Control: no-cache';
            $headers[] = 'Connection: keep-alive';
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $headers[] = 'Cookie: frm_form4_bbfa5b726c6b7a9cf3cda9370be3ee91=2022-10-13+22%3A02%3A56; wp-settings-1=libraryContent%3Dbrowse%26editor%3Dhtml; wp-settings-time-1=1667630627; wordpress_test_cookie=WP+Cookie+check; wordpress_logged_in_bbfa5b726c6b7a9cf3cda9370be3ee91=admin%7C1667875420%7CSTfFdDHVmUyEyNdAmCBDDym8NXlI4fJM2LryfOBZICt%7C60a0dd84987b8a0c1ab4761ccd5da5ad123876f2277312200b3c30b62f1924ae; _y=a03b1f30-75BE-4B0D-183A-DDB606FEEA6D; _shopify_y=a03b1f30-75BE-4B0D-183A-DDB606FEEA6D; _gcl_au=1.1.612578835.1665062576; _ga=GA1.1.127531313.1665062576; _ga_4TJTN0BNL6=GS1.1.1665096596.3.0.1665096596.0.0.0; _ga_0GR1XJYZ1D=GS1.1.1665096596.3.0.1665096596.0.0.0; wp-settings-time-1=1665905946';
            $headers[] = 'Origin: https://localhost';
            $headers[] = 'Pragma: no-cache';
            $headers[] = 'Referer: https://localhost/wordpress/wp-login.php';
            $headers[] = 'Sec-Fetch-Dest: document';
            $headers[] = 'Sec-Fetch-Mode: navigate';
            $headers[] = 'Sec-Fetch-Site: same-origin';
            $headers[] = 'Sec-Fetch-User: ?1';
            $headers[] = 'Upgrade-Insecure-Requests: 1';
            $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36';
            $headers[] = 'Sec-Ch-Ua: \".Not/A)Brand\";v=\"99\", \"Google Chrome\";v=\"103\", \"Chromium\";v=\"103\"';
            $headers[] = 'Sec-Ch-Ua-Mobile: ?0';
            $headers[] = 'Sec-Ch-Ua-Platform: \"macOS\"';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if (curl_errno($ch)) echo 'Error:' . curl_error($ch);
            curl_close($ch);

            $cookie = file_get_contents($cookie_file);
            $cookie = explode('wordpress_logged_in_', $cookie);
            $cookie = $cookie[1];
            $cookie = str_replace("\t", '=', $cookie);
            $cookie = str_replace("\n", ';', $cookie);

            unlink($cookie_file);
            return $cookie;
        }
    ));
});

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

add_action('rest_api_init', function () {
    /*  curl http://localhost/wordpress/wp-json/formidable-auto-backup/v1/run   */
    register_rest_route('formidable-auto-backup/v1', '/run', array(
        'methods' => 'GET',
        'callback' => function () {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'http://localhost/wordpress/wp-admin/admin-ajax.php');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "action=frm_export_xml&export-xml=d88ff10989&_wp_http_referer=%2Fwordpress%2Fwp-admin%2Fadmin.php%3Fpage%3Dformidable-import&format=xml&csv_format=UTF-8&csv_col_sep=%2C&type%5B%5D=forms&s=&frm_export_forms%5B%5D=2");
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
            $writeFile = fopen('Formidable-Auto-Backup-' . (new DateTime())->format('Y-m-d@H:i:s') . '.xml', 'wb');
            curl_setopt($ch, CURLOPT_FILE, $writeFile);

            $headers = array();
            $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';
            $headers[] = 'Accept-Language: en-US,en;q=0.9';
            $headers[] = 'Cache-Control: no-cache';
            $headers[] = 'Connection: keep-alive';
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $headers[] = 'Cookie: wordpress_sec_bbfa5b726c6b7a9cf3cda9370be3ee91=admin%7C1667599903%7C3gpNcbeGFVn8qUFUZROr82PZfPeJieflt1eTtK4AG1A%7Cea121936348bacaf2d2ad5ab51a552e9e1580a08e4c52d5fe6075e72551d07c3; frm_form4_bbfa5b726c6b7a9cf3cda9370be3ee91=2022-10-13+22%3A02%3A56; wp-settings-time-1=1667211547; wp-settings-1=libraryContent%3Dbrowse%26editor%3Dhtml; wordpress_test_cookie=WP+Cookie+check; wordpress_logged_in_bbfa5b726c6b7a9cf3cda9370be3ee91=admin%7C1667599903%7C3gpNcbeGFVn8qUFUZROr82PZfPeJieflt1eTtK4AG1A%7C9a75d51e4d2410b1ede36c868cf69f9d4babdb6c978db97a2a1f0f0a8c816d95; y=a03b1f30-75BE-4B0D-183A-DDB606FEEA6D; shopify_y=a03b1f30-75BE-4B0D-183A-DDB606FEEA6D; gclau=1.1.612578835.1665062576; ga=GA1.1.127531313.1665062576; ga_4TJTN0BNL6=GS1.1.1665096596.3.0.1665096596.0.0.0; ga0GR1XJYZ1D=GS1.1.1665096596.3.0.1665096596.0.0.0; wp-settings-time-1=1665905946';
            $headers[] = 'Origin: https://localhost';
            $headers[] = 'Pragma: no-cache';
            $headers[] = 'Referer: https://localhost/wordpress/wp-admin/admin.php?page=formidable-import';
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
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            return $result;
        }
    ));
});

<?php
/*
  Plugin Name: EditionGuard for WooCommerce - eBook Sales with DRM
  Plugin URI: http://www.editionguard.com
  Description: A plugin that allows integration between your WooCommerce store and EditionGuard
  Version: 1.1.4
  Author: EditionGuard Dev Team <support@editionguard.com>
  Author URI: http://www.editionguard.com
 */

include( plugin_dir_path(__FILE__) . 'woo_eg_api.php');


if (@$_REQUEST["woo_ed_resource_id"]) {
    ?><script>parent.editionguard_response_ready('<?php echo $_REQUEST["woo_ed_resource_id"] ?>')</script><?php
    exit;
}
if (@$_REQUEST["woo_ed_error"]) {
    ?><script>parent.editionguard_response_error('<?php echo $_REQUEST["woo_ed_error"] ?>')</script><?php
    exit;
}
session_start();

$post_url_ref = explode($_SERVER["SERVER_NAME"], site_url("/wp-admin/post.php"));
$post_new_url_ref = explode($_SERVER["SERVER_NAME"], site_url("/wp-admin/post-new.php"));

$post_url = array_pop($post_url_ref);
$post_new_url = array_pop($post_new_url_ref);

if (($_SERVER["SCRIPT_NAME"] == $post_url) && $_GET["post"]) {
    $post = get_post($_GET["post"]);
    if ($post->post_type == "product")
        $show_edition_guard = true;
}
elseif (($_SERVER["SCRIPT_NAME"] == $post_new_url) && ($_GET["post_type"] == "product"))
    $show_edition_guard = true;
else
    $show_edition_guard = false;

if ($show_edition_guard) {
    wp_register_script('woocommerce_editionguard', plugins_url('/woocommerce_editionguard.js', __FILE__), array("jquery"));
    wp_enqueue_script('woocommerce_editionguard');
    wp_register_style('woocommerce_editionguard', plugins_url('/woocommerce_editionguard.css', __FILE__));
    wp_enqueue_style('woocommerce_editionguard');
    $secret = get_option('woo_eg_secret');
    $nonce = rand(1000000, 999999999);
    $email = get_option('woo_eg_email');
    if ($email == "")
        $email = "";
    if ($secret)
        $hash = hash_hmac("sha1", $nonce . $email, base64_decode($secret));
    else
        $hash = "";
    $on = get_post_meta($_GET["post"], "_use_edition_guard", true);
    $r_id = get_post_meta($_GET["post"], "_eg_resource_id", true);
    $title = get_post_meta($_GET["post"], "_use_edition_guard_title");
    $drmType = get_post_meta($_GET["post"], "_eg_drm_type");
    
    if (($email != "") && ($hash != "")) {
        $data = array("email" => $email, "nonce" => $nonce, "hash" => $hash);
        $api = new Woo_eg_api($email, $secret);
        $library = $api->getBookList();
    } else
        $library = "";
    $translation_array = array(
        'plugin_path' => $pluginurl = WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__)) . basename(__FILE__),
        'plugin_dir' => $pluginurl = WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__)),
        'email' => $email,
        'nonce' => $nonce,
        'hash' => $hash,
        'return_url' => base64_encode($_SERVER["REQUEST_URI"]),
        'on' => $on,
        'r_id' => $r_id,
        'title' => $title,
        'library' => $library,
        'drm_type' => $drmType
    );
    wp_localize_script('woocommerce_editionguard', 'woo_eg', $translation_array);
}

add_action('save_post', 'woocommerce_ed_product_save', 10, 2);

function woocommerce_ed_product_save($post_id, $post) {
    if (is_int(wp_is_post_revision($post_id)))
        return;
    if (is_int(wp_is_post_autosave($post_id)))
        return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;
    //if ( ! isset( $_REQUEST['woocommerce_bulk_edit_nonce'] ) || ! wp_verify_nonce( $_REQUEST['woocommerce_bulk_edit_nonce'], 'woocommerce_bulk_edit_nonce' ) ) return $post_id;
    if (!current_user_can('edit_post', $post_id))
        return $post_id;
    if ($post->post_type != 'product')
        return $post_id;

    if (!empty($_REQUEST['_eg_resource_id']))
        update_post_meta($post_id, '_eg_resource_id', stripslashes($_REQUEST['_eg_resource_id']));
    if (!empty($_REQUEST['_eg_drm_type']))
        update_post_meta($post_id, '_eg_drm_type', stripslashes($_REQUEST['_eg_drm_type']));

    update_post_meta($post_id, '_use_edition_guard', stripslashes($_REQUEST['_use_edition_guard']));

    if (!empty($_REQUEST['_eg_title']))
        update_post_meta($post_id, '_use_edition_guard_title', stripslashes($_REQUEST['_eg_title']));
}

// New filter for 2.1+
add_filter("woocommerce_get_item_downloads", "woo_eg_get_item_downloads", 10, 3);

function woo_eg_get_item_downloads($files, $item, $order) {

    if (get_post_meta($item['product_id'], "_use_edition_guard", true)) {
        $email = get_option('woo_eg_email');
        $secret = get_option('woo_eg_secret');
        $resourceId = get_post_meta($item['product_id'], "_eg_resource_id", true);
        $drmType = get_post_meta($item['product_id'], "_eg_drm_type", true);
        $bookData = array();
        
        if($drmType == 'Social DRM') {
            $bookData['watermark_name'] = "$order->billing_first_name "
                    . "$order->billing_last_name";
            $bookData['watermark_email'] = $order->billing_email;
            $bookData['watermark_phone'] = $order->billing_phone;
        }
        
        
        $api = new Woo_eg_api($email, $secret);
        
        
        for ($i = 1; $i <= $item['qty']; $i++) {
            $transaction = $api->createTransaction($resourceId);
            $files[$i] = array("download_url" => $transaction[''], "name" => "Click here");
        }
    }

    return $files;
}

// Old filter for 2.0-
add_filter("woocommerce_get_downloadable_file_urls", "woo_eg_process_downloadable_file_urls", 10, 4);

function woo_eg_process_downloadable_file_urls($file_urls, $product_id, $variation_id, $item) {
    global $woocommerce;
    if (get_post_meta($product_id, "_use_edition_guard", true)) {
        $linkURL = "http://acs4.editionguard.com/fulfillment/URLLink.acsm";
        $dateval = time();
        $orderSource = get_option('woo_eg_email');
        $sharedSecret = get_option('woo_eg_secret');
        $resourceId = get_post_meta($product_id, "_eg_resource_id", true);

        $item_id = $variation_id > 0 ? $variation_id : $product_id;

        if (!empty($_REQUEST['post_ID']))
            $transactionId = $_REQUEST['post_ID'];
        if (!empty($_REQUEST['order']))
            $transactionId = $_REQUEST['order'];
        if (!empty($_REQUEST['order_id']))
            $transactionId = $_REQUEST['order_id'];
        if (isset($woocommerce->woocommerce_email->emails)) {
            $id = $woocommerce->woocommerce_email->emails['WC_Email_New_Order']->object->id;
            if (!empty($id))
                $transactionId = $id;
        }

        // Transaction Id is still empty, we defer to the email order items table filter for proper replacement.
        if (empty($transactionId)) {
            for ($i = 1; $i <= $item['qty']; $i++) {
                $URL = "action=enterorder&ordersource=" . urlencode($orderSource) . "&orderid=%orderid%-" . urlencode($item_id . "-" . $i) . "&resid=" . urlencode($resourceId) . "&dateval=" . urlencode($dateval) . "&gblver=4";
                $file_urls["Click here #" . $i] = $URL;
            }
        } else {
            for ($i = 1; $i <= $item['qty']; $i++) {
                $URL = "action=enterorder&ordersource=" . urlencode($orderSource) . "&orderid=" . urlencode($transactionId . "-" . $item_id . "-" . $i) . "&resid=" . urlencode($resourceId) . "&dateval=" . urlencode($dateval) . "&gblver=4";
                $URL = $linkURL . "?" . $URL . "&auth=" . hash_hmac("sha1", $URL, base64_decode($sharedSecret));
                $file_urls["Click here #" . $i] = $URL;
            }
        }
    }
    return $file_urls;
}

add_filter("woocommerce_email_order_items_table", "woo_eg_email_order_items_table", 10, 5);

function woo_eg_email_order_items_table($html, $order) {
    // If the download links weren't deferred for replacement, just return the HTML.
    if (!preg_match("/%orderid%/", $html))
        return $html;

    $linkURL = "http://acs4.editionguard.com/fulfillment/URLLink.acsm";
    $sharedSecret = get_option('woo_eg_secret');

    // Get the links in the HTML template
    preg_match_all('/href="([^"]*)"/', $html, $matches);
    $links = $matches[1];
    // Place the order id in each then replace original links in the template
    foreach ($links as $link) {
        if (preg_match("/%orderid%/", $link)) {
            $newlink = preg_replace("/%orderid%/", $order->id, $link);
            $newlink = $linkURL . "?" . $newlink . "&auth=" . hash_hmac("sha1", $newlink, base64_decode($sharedSecret));
            $newhtml = str_replace($link, $newlink, $html);
        }
    }
    return $newhtml;
}

add_action('admin_menu', 'register_custom_menu_page_woo_eg', 9999);

function register_custom_menu_page_woo_eg() {
    add_submenu_page('woocommerce', 'EditionGuard', 'EditionGuard', 'manage_woocommerce', 'woo_edition_guard', 'woo_eg_options');
}

function woo_eg_options() {
    global $wpdb;
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    if ($_POST['submit']) {
        foreach ($_POST as $v => $k) {
            if ($v == 'submit')
                continue;
            update_option($v, $k);
        }
        $_SESSION["woo_eg_options_updated"] = 1;
    }
    if ($_SESSION["woo_eg_options_updated"]) {
        unset($_SESSION["woo_eg_options_updated"]);
        ?><div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Settings saved.</strong></p></div><?php if ($_REQUEST["return_url"]) { ?><script>jQuery(document).ready(function () {
                            if (confirm("Do you want to get back to editing your product?"))
                                window.location.href = '<?php echo base64_decode($_REQUEST["return_url"]) ?>';
                        })</script> <?php
                    }
                }
                ?><style>
        .wrap label {line-height: 24px;margin-right:10px}
        .wrap input {}
        .wrap li {display: table-cell; vertical-align: top;}
    </style>

    <div class="wrap">
        <div id="icon-options-general" class="icon32">
            <br>
        </div>
        <h2>EditionGuard for WooCommerce Settings</h2>

        <form method="POST" action="">
            <ul>
                <li>
                    <label for="woo_eg_email">Email</label><br />
                    <label for="woo_eg_secret">Shared Secret</label><br />
                </li>
                <li>

                    <input type="text" name="woo_eg_email" <?php if (get_option('woo_eg_email')) echo 'value="' . get_option('woo_eg_email') . '"' ?> /><br />
                    <input type="text" name="woo_eg_secret" <?php if (get_option('woo_eg_secret')) echo 'value="' . get_option('woo_eg_secret') . '"' ?> /><br />
                </li>
            </ul>
            <a href="http://www.editionguard.com/?action=trial">Don't have an EditionGuard account? Get started with a free 30 day trial</a><br/><br/>
            <input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes">
        </form>
    </div>
    <?php
}

function woo_eg_curl_it($uri, $data, $method = 'POST') {
    if ($method == 'GET')
        $uri .= (!empty($data)) ? "?$data" : "";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, ($method == 'GET') ? CURLOPT_HTTPGET : CURLOPT_POST, 1);
    if ($method != 'GET')
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $ret = curl_exec($ch);
    curl_close($ch);
    return $ret;
}
?>

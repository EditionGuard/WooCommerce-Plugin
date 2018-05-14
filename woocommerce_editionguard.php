<?php
/*
  Plugin Name: EditionGuard for WooCommerce - eBook Sales with DRM
  Plugin URI: https://www.editionguard.com
  Description: A plugin that allows integration between your WooCommerce store and EditionGuard
  Version: 3.0.2
  Author: EditionGuard Dev Team <support@editionguard.com>
  Author URI: https://www.editionguard.com
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
    wp_register_script('jquery-ui', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js');
    wp_enqueue_script('jquery-ui');
    wp_register_style('woocommerce_editionguard', plugins_url('/woocommerce_editionguard.css', __FILE__), array('jquery-ui'));
    wp_enqueue_style('woocommerce_editionguard');
    wp_register_style('jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_style('jquery-ui');
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
    } else {
        $library = "";
    }

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

add_action('woocommerce_add_order_item_meta', 'woo_eg_add_file_url_to_order_item_meta', 1, 2);

function woo_eg_add_file_url_to_order_item_meta($item_id, $item) {
    if (get_post_meta($item['product_id'], "_use_edition_guard", true)) {
        $email = get_option('woo_eg_email');
        $secret = get_option('woo_eg_secret');
        $resourceId = get_post_meta($item['product_id'], "_eg_resource_id", true);
        $drmType = get_post_meta($item['product_id'], "_eg_drm_type", true);
        $bookData = array();



        if ($drmType == 'Social DRM' || $drmType == "EditionMark") {
            $bookData['watermark_name'] = filter_input(INPUT_POST, "billing_first_name") . ' '
                    . filter_input(INPUT_POST, "billing_last_name");
            $bookData['watermark_email'] = filter_input(INPUT_POST, "billing_email");
            $bookData['watermark_phone'] = filter_input(INPUT_POST, "billing_phone");
        }

        $links = array();
        $api = new Woo_eg_api($email, $secret);
        for ($i = 0; $i < $item['quantity']; $i++) {
            $transaction = $api->createTransaction($resourceId, $bookData);
            $links[] = $transaction->download_link;
        }



        if (!empty($transaction->download_link)) {
            if (function_exists("wc_add_order_item_meta")) {
                wc_add_order_item_meta($item_id, '_eg_download_url', serialize($links));
            } else {
                woocommerce_add_order_item_meta($item_id, '_eg_download_url', serialize($links));
            }
        }
    }
}

// New filter for 2.1+
add_filter("woocommerce_get_item_downloads", "woo_eg_get_item_downloads", 10, 3);

function woo_eg_get_item_downloads($files, $item, $order) {

    if (get_post_meta($item['product_id'], "_use_edition_guard", true)) {

        $downloadUrls = getItemDownloadUrls($item);


        for ($i = 1; $i <= $item['qty']; $i++) {
            $files[$i] = array("download_url" => $downloadUrls[$i - 1], "name" => "Click here");
        }
    }

    return $files;
}

// Old filter for 2.0-
add_filter("woocommerce_get_downloadable_file_urls", "woo_eg_process_downloadable_file_urls", 10, 4);

function woo_eg_process_downloadable_file_urls($file_urls, $product_id, $variation_id, $item) {

    if (get_post_meta($product_id, "_use_edition_guard", true)) {
        $downloadUrls = getItemDownloadUrls($item);


        for ($i = 1; $i <= $item['qty']; $i++) {
            $file_urls["Click here #" . $i] = $downloadUrls[$i - 1];
        }
    }

    return $file_urls;
}

add_action('admin_menu', 'register_custom_menu_page_woo_eg', 9999);

function register_custom_menu_page_woo_eg() {
    $res = add_submenu_page('woocommerce', 'EditionGuard', 'EditionGuard', 'manage_woocommerce', 'woo_edition_guard', 'woo_eg_options');
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

function getItemDownloadUrls($item) {
    if (WC()->version >= '3.0.0') {
        $downloadUrls = unserialize($item['item_meta']['_eg_download_url']);
    } else {
        $downloadUrls = unserialize(unserialize($item['item_meta']['_eg_download_url'][0]));
    }
    return $downloadUrls;
}

add_filter("woocommerce_customer_get_downloadable_products", "show_links_in_my_account");

function show_links_in_my_account($downloads) {
    $statuses = wc_get_order_statuses();

    $customer_orders = get_posts(array(
        'numberposts' => -1,
        'meta_key' => '_customer_user',
        'meta_value' => get_current_user_id(),
        'post_type' => wc_get_order_types(),
        'post_status' => array('wc-processing', 'wc-completed'),
    ));

    foreach ($customer_orders as $post) {
        $order = WC_Order_Factory::get_order($post->ID);
        $items = $order->get_items();
        new WC_Order_Item_Product();
        foreach ($items as $id => $item) {
            $urls = getItemDownloadUrls($item);
            foreach ($urls as $url) {
                $downloads[] = array(
                    'download_url' => $url,
                    'product_name' => $item['name'],
                    'file' => array('name' => 'Download '.$item['name']),
                    'product_id' => $item['product_id']
                );
            }
        }
    }

    return $downloads;
}

add_action('woocommerce_new_order_item', 'woo_eg_add_file_url_to_manual_order', 9, 2);

function woo_eg_add_file_url_to_manual_order($item_id, $item) {
    if(is_admin()) {
        woo_eg_add_file_url_to_order_item_meta($item_id, $item);
    }
}
add_filter('woocommerce_product_file', 'woo_eg_get_product_file', 7, 2);

function woo_eg_get_product_file($file, $product) {
	
	if(!$file) {
		$id = get_post_meta($product->get_id(), "_eg_resource_id", true);
		return $id;
	}
}

    ?>

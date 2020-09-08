<?php

/**
 * Adds support for variable products
 *
 * @author Vladislav Khomenko
 */
class Woo_eg_variable_product {

    const custom_fields = [
        '_use_edition_guard_title',
        '_eg_resource_id',
        '_eg_drm_type'
    ];

    public function init() {
        add_action('woocommerce_variation_options_download', [$this, 'support'], 9, 3);
        add_action('woocommerce_save_product_variation', [$this, 'save'], 10, 2);
    }

    public function support($loop, $variation_data, $variation) {
        global $editionguard_api;
        if (is_null($editionguard_api)) {
            $secret = get_option('woo_eg_secret');
            $email = get_option('woo_eg_email');
            $editionguard_api = new Woo_eg_api($email, $secret);
            $GLOBALS['editionguard_api'] = $editionguard_api;
        }
        $woo_eg = new stdClass();
        $woo_eg->on = $variation_data["_use_edition_guard"][0];
        $woo_eg->title = $variation_data["_use_edition_guard_title"][0];
        $woo_eg->r_id = $variation_data["_eg_resource_id"][0];
        $woo_eg->drm_type = $variation_data["_eg_drm_type"][0];
        
        
        $secret = get_option('woo_eg_secret');

        $email = get_option('woo_eg_email');


        $woo_eg->library = $editionguard_api->getBookList();


        if ($woo_eg->on) {
            $checked = 'checked ';
        } else {
            $checked = '';
        }

        if ($woo_eg->title == "") {
            $current = '<span class="_current_ebook">-</span>';
        } else {
            $current = '<span class="_current_ebook">' . $woo_eg->title . '(' . $woo_eg->r_id . ')</span>';
        }


        $p1 = '<p class="form-field-both" id="use_edition_guard_' . $variation->ID . '"><input type="checkbox" ' . $checked . 'name="_use_edition_guard[' . $loop . ']" style="width:auto" onclick="use_editionguard_drm_trigger(this, true)"/>&nbsp;Use EditionGuard eBook DRM</p><p class="form-field-drm"><label>Currently Used eBook:</label>' . $current . '</p>';

        if ($woo_eg->r_id == "")
            $value = '';
        else
            $value = 'value="' . $woo_eg->r_id . '" ';

        $input = '<input type="hidden" name="_eg_resource_id[' . $loop . ']" class="_eg_resource_id" ' . $value . 'placeholder="EditionGuard Resource ID" />' . '<input type="hidden" name="_use_edition_guard_title[' . $loop . ']" class="_eg_title"/>' . '<input type="hidden" name="_eg_drm_type[' . $loop . ']" class="_eg_drm_type" value="' . $woo_eg->drm_type . '"/>';
        $p2 = $input;
//
        $label = '<p class="form-field e-book"><label>Choose eBook: </label>';
        $select = '<select style="width:410px" class="ebook_library">    <option value="">Select one...</option>';
        if ($woo_eg->library) {
            foreach ($woo_eg->library as $v) {
                $select .= '<option title="' . $v->title . '" value="' . $v->resource_id . '" data-drm-type="' . $v->drm_type . '">' . $v->title . ' (' . $v->resource_id . ')' . '</option>';
            }
        }
        $select .= '</select></p>';
        $button = '<input style="display: none" type="button" onclick="use_ebook(this, true)" class="use_button_edition_guard button" value="Use">';
        $p3 = '<p style="display: none" class="form-field-drm"><b>Use an existing eBook uploaded to your EditionGuard account</b></p><p class="form-field-drm">' . $label . $select . $button . '</p>';
        echo $p1 . $p2 . $p3;
        ?>
        <script>
            let checkbox = jQuery('#use_edition_guard_<?= $variation->ID ?>');
            checkbox.prependTo(checkbox.parent().prev());
            let variation = checkbox.parents('.woocommerce_variation');
            let select = variation.find('.ebook_library');
            select.combobox();
            select.next().hide();
            use_editionguard_drm_trigger(checkbox.find('input').get(0), true);
        </script>
        <?php
    }

    public function save($variation_id, $i) {
        foreach (self::custom_fields as $name) {
            if ($name == '_use_edition_guard') {
                update_post_meta($variation_id, $name, stripslashes($_POST[$name][$i]));
            } else {
                $custom_field = $_POST[$name][$i];
                if (isset($custom_field)) {
                    update_post_meta($variation_id, $name, esc_attr($custom_field));
                }
            }
        }
    }

}

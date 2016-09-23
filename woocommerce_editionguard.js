jQuery(document).ready(function() {
    if (woo_eg.on)
        var checked = 'checked ';
    else
        checked = '';

    if(woo_eg.title == "")
        var current = '<span id="_current_ebook">-</span>';
    else
        current = '<span id="_current_ebook">'+woo_eg.title+' ('+woo_eg.r_id+')</span>';
        
    var woo_fields = jQuery('.options_group.show_if_downloadable').html();

    var p1 = '<p class="form-field-both"><input type="checkbox" ' + checked + 'name="_use_edition_guard" style="width:auto" onclick="use_editionguard_drm_trigger(this)"/>&nbsp;Use EditionGuard eBook DRM</p><p class="form-field-drm"><label>Currently Used eBook:</label>'+current+'</p>';

    if (woo_eg.r_id == "")
        var value = '';
    else
        value = 'value="' + woo_eg.r_id + '" ';
    var input = '<input type="hidden" name="_eg_resource_id" id="_eg_resource_id" ' + value + 'placeholder="EditionGuard Resource ID" />'
               +'<input type="hidden" name="_eg_title" id="_eg_title"/>'
               +'<input type="hidden" name="_eg_drm_type" id="_eg_drm_type" value="' + woo_eg.drm_type+'"/>';
    var p2 = input;

    var label = '<label for="_file_paths">Choose eBook</label>';
    select = '<select style="width:410px" id="ebook_library">';
    if (woo_eg.library) jQuery.each(woo_eg.library, function(k, v) {
        select += '<option title="' + v.title + '" value="' + v.resource_id + '" data-drm-type="'+v.drm_type+'">' + v.title + ' (' + v.resource_id + ')' + '</option>';
    })
    select += '</select>';
    var button = '<input type="button" onclick="use_ebook()" class="use_button_edition_guard button" value="Use">';
    var p3 = '<p class="form-field-drm"><b>Use an existing eBook uploaded to your EditionGuard account</b></p><p class="form-field-drm">' + label + select + button + '</p>';

    var img = '<img class="eg_ajax" style="padding:2px 10px;display:none" src="' + woo_eg.plugin_dir + 'ajax-loader.gif" />';
    var label = '<label for="_file_paths">Choose eBook File</label>';
    if (woo_eg.r_id == "")
        var value = '';
    else
        value = 'value="' + woo_eg.r_id + '" ';


    jQuery('.options_group.show_if_downloadable').html(p1 + woo_fields + p2 + p3);

    if (woo_eg.on)
    {
        jQuery('.options_group.show_if_downloadable .form-field').hide();
        jQuery('.options_group.show_if_downloadable .form-field-drm').show();
    }
    
});
var woo_eg_old_click_handler;
function use_ebook()
{
    var resource_id = jQuery('#ebook_library').val()
    jQuery('#_eg_resource_id').val(resource_id);
    jQuery('#_current_ebook').html(jQuery('#ebook_library option:selected').text());
    jQuery('#_eg_title').val(jQuery('#ebook_library option:selected').attr('title'));
    jQuery('#_eg_drm_type').val(jQuery('#ebook_library option:selected').attr('data-drm-type'));
    alert("eBook selection updated. Please update the product to save your changes.");
    
}
function use_editionguard_drm_trigger(object)
{
    if (object.checked)
    {
        if ((woo_eg.email == "") || (woo_eg.hash == ""))
        {
            alert("You`ve forgot to fill your Edition Guard email and secret");
            if (confirm("Do you want to drop your product changes and go to Edition Guard settings page?"))
                window.location.href = "admin.php?page=woo_edition_guard&return_url=" + woo_eg.return_url;
            object.checked = false;
            return;
        }
        
        jQuery('.options_group.show_if_downloadable .form-field').hide();
        jQuery('.options_group.show_if_downloadable .form-field-drm').show();
    }
    else
    {
        
        jQuery('.options_group.show_if_downloadable .form-field').show();
        jQuery('.options_group.show_if_downloadable .form-field-drm').hide();
        jQuery(".upload_file_button").click(woo_eg_old_click_handler);
    }
}







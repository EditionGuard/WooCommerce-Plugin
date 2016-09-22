jQuery(document).ready(function() {
    //jQuery('#woocommerce-product-data .type_box').html(jQuery('#woocommerce-product-data .type_box').html()+'<label class="show_if_simple tips" for="_use_edition_guard" style="display: inline;" data-tip="Enable to use EditionGuard DRM">Use EditionGuard DRM: <input type="checkbox" id="_use_edition_guard" name="_use_edition_guard"></label>');	
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
    var p3 = '<p class="form-field-drm"><b>Use an existing eBook uploaded to your EditionGuard account</b></p><p class="form-field-drm">' + label + select + button + '</p><p class="form-field-drm"><b><i>--- OR ---</i></b></p>';

    var img = '<img class="eg_ajax" style="padding:2px 10px;display:none" src="' + woo_eg.plugin_dir + 'ajax-loader.gif" />';
    var label = '<label for="_file_paths">Choose eBook File</label>';
    if (woo_eg.r_id == "")
        var value = '';
    else
        value = 'value="' + woo_eg.r_id + '" ';
    var file_input = '<input type="button" onclick="use_editionguard_drm()" class="upload_file_button_edition_guard button" value="Choose a file">';
    var p4 = '<p class="form-field-drm"><b>Upload an eBook From My Computer</b></p><p class="form-field-drm"><label for="_use_edition_guard">eBook File Title: </label><input type="text" id="use_edition_guard_title" name="_use_edition_guard_title" style="width:auto"></p>';

    var p5 = '<p class="form-field-drm">' + label + file_input + img + '</p>';

    jQuery('.options_group.show_if_downloadable').html(p1 + woo_fields + p2 + p3 + p4 + p5);

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
                window.location.href = "options-general.php?page=woo-edition-guard&return_url=" + woo_eg.return_url;
            object.checked = false;
            return;
        }
        //jQuery(".upload_file_button").hide();
        //jQuery(".upload_file_button_edition_guard").show();
        jQuery('.options_group.show_if_downloadable .form-field').hide();
        jQuery('.options_group.show_if_downloadable .form-field-drm').show();
    }
    else
    {
        //jQuery(".upload_file_button").show();
        //jQuery(".upload_file_button_edition_guard").hide();
        jQuery('.options_group.show_if_downloadable .form-field').show();
        jQuery('.options_group.show_if_downloadable .form-field-drm').hide();
        jQuery(".upload_file_button").click(woo_eg_old_click_handler);
    }
}
function use_editionguard_drm()
{
    if (jQuery('#use_edition_guard_title').val() == "")
    {
        alert("eBook Title can not be empty");
        jQuery('#use_edition_guard_title').focus();
        return false;
    }
    form = '<form id="editionguard_drm_form" style="visibility:hidden;position:absolute" method="post" enctype="multipart/form-data" action="http://www.editionguard.com/api/package">\
    <input type="hidden" name="email" value="' + woo_eg.email + '" />\
    <input type="hidden" name="nonce" value="' + woo_eg.nonce + '" />\
    <input type="hidden" name="hash" value="' + woo_eg.hash + '" />\
    Title: <input type="text" name="title" /><br>\
    Author: <input type="text" name="author" /><br>\
    Publisher: <input type="text" name="publisher" /><br>\
    File: <input id="editionguard_drm_file" type="file" name="file" onchange="editionguard_drm_form_submit()" accept="application/pdf,application/epub+zip" /><br>\
    <input type="submit" />\
	</form>';
//    Resource Id (optional): <input type="text" name="resource_id" value="<?php echo $resource_id ?>" /><br>\
    if (jQuery('#editionguard_drm_form').length == 0)
        jQuery('#post').before(form);
    jQuery('#editionguard_drm_file').click();
}

function editionguard_response_ready(response)
{
    jQuery("img.eg_ajax").hide();
    jQuery(".upload_file_button_edition_guard.button").show();
    //alert(response);
    jQuery('#_eg_resource_id').val(response);
    //urn:uuid:7e8da63e-c279-42d0-a306-81ee3935e4e5
    jQuery('#_eg_title').val(jQuery("#use_edition_guard_title").val());
    jQuery('#_current_ebook').html(jQuery("#use_edition_guard_title").val()+' ('+response+')');
    alert("eBook uploaded successfully. Please update the product to save your changes.");
}

function editionguard_response_error(response)
{
    jQuery("img.eg_ajax").hide();
    jQuery(".upload_file_button_edition_guard.button").show();
    alert("Error uploading file: (" + response + ")");
}

function editionguard_drm_form_submit()
{
    title = jQuery('#use_edition_guard_title').val();
    ext = jQuery('#editionguard_drm_file').val().split('.').pop().toLowerCase();
    if ((ext != 'pdf') && (ext != 'epub'))
    {
        alert("Only pdf and ePub are supported");
        return;
    }
    jQuery("img.eg_ajax").show();
    jQuery(".upload_file_button_edition_guard.button").hide();

    jQuery.ajaxFileUpload
            (
                    {
                        url: 'http://www.editionguard.com/api/package',
                        secureuri: false,
                        fileElementId: 'editionguard_drm_file',
                        dataType: 'json',
                        data: {email: woo_eg.email, nonce: woo_eg.nonce, hash: woo_eg.hash, callback_url: woo_eg.plugin_path, title: title},
                        success: function(data, status)
                        {
                            if (typeof(data.error) != 'undefined')
                            {
                                if (data.error != '')
                                {
                                    alert(data.error);
                                } else
                                {
                                    alert(data.msg);
                                }
                            }
                        },
                        error: function(data, status, e)
                        {
                            alert(e);
                        }
                    }
            );
    //jQuery('#editionguard_drm_form').
}


jQuery.extend({
    createUploadIframe: function(id, uri)
    {
        //create frame
        var frameId = 'jUploadFrame' + id;
        var iframeHtml = '<iframe id="' + frameId + '" name="' + frameId + '" style="position:absolute; top:-9999px; left:-9999px"';
        if (window.ActiveXObject)
        {
            if (typeof uri == 'boolean') {
                iframeHtml += ' src="' + 'javascript:false' + '"';

            }
            else if (typeof uri == 'string') {
                iframeHtml += ' src="' + uri + '"';

            }
        }
        iframeHtml += ' />';
        jQuery(iframeHtml).appendTo(document.body);

        return jQuery('#' + frameId).get(0);
    },
    createUploadForm: function(id, fileElementId, data)
    {
        //create form	
        var formId = 'jUploadForm' + id;
        var fileId = 'jUploadFile' + id;
        var form = jQuery('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');
        if (data)
        {
            for (var i in data)
            {
                jQuery('<input type="hidden" name="' + i + '" value="' + data[i] + '" />').appendTo(form);
            }
        }
        var oldElement = jQuery('#' + fileElementId);
        var newElement = jQuery(oldElement).clone();
        jQuery(oldElement).attr('id', fileId);
        jQuery(oldElement).before(newElement);
        jQuery(oldElement).appendTo(form);



        //set attributes
        jQuery(form).css('position', 'absolute');
        jQuery(form).css('top', '-1200px');
        jQuery(form).css('left', '-1200px');
        jQuery(form).appendTo('body');
        return form;
    },
    ajaxFileUpload: function(s) {
        // TODO introduce global settings, allowing the client to modify them for all requests, not only timeout		
        s = jQuery.extend({}, jQuery.ajaxSettings, s);
        var id = new Date().getTime()
        var form = jQuery.createUploadForm(id, s.fileElementId, (typeof(s.data) == 'undefined' ? false : s.data));
        var io = jQuery.createUploadIframe(id, s.secureuri);
        var frameId = 'jUploadFrame' + id;
        var formId = 'jUploadForm' + id;
        // Watch for a new set of requests
        if (s.global && !jQuery.active++)
        {
            jQuery.event.trigger("ajaxStart");
        }
        var requestDone = false;
        // Create the request object
        var xml = {}
        if (s.global)
            jQuery.event.trigger("ajaxSend", [xml, s]);
        // Wait for a response to come back
        var uploadCallback = function(isTimeout)
        {
            var io = document.getElementById(frameId);
            try
            {
                if (io.contentWindow)
                {
                    xml.responseText = io.contentWindow.document.body ? io.contentWindow.document.body.innerHTML : null;
                    xml.responseXML = io.contentWindow.document.XMLDocument ? io.contentWindow.document.XMLDocument : io.contentWindow.document;

                } else if (io.contentDocument)
                {
                    xml.responseText = io.contentDocument.document.body ? io.contentDocument.document.body.innerHTML : null;
                    xml.responseXML = io.contentDocument.document.XMLDocument ? io.contentDocument.document.XMLDocument : io.contentDocument.document;
                }
            } catch (e)
            {
                jQuery.handleError(s, xml, null, e);
            }
            if (xml || isTimeout == "timeout")
            {
                requestDone = true;
                var status;
                try {
                    status = isTimeout != "timeout" ? "success" : "error";
                    // Make sure that the request was successful or notmodified
                    if (status != "error")
                    {
                        // process the data (runs the xml through httpData regardless of callback)
                        var data = jQuery.uploadHttpData(xml, s.dataType);
                        // If a local callback was specified, fire it and pass it the data
                        if (s.success)
                            s.success(data, status);

                        // Fire the global callback
                        if (s.global)
                            jQuery.event.trigger("ajaxSuccess", [xml, s]);
                    } else
                        jQuery.handleError(s, xml, status);
                } catch (e)
                {
                    status = "error";
                    return;
                    jQuery.handleError(s, xml, status, e);
                }

                // The request was completed
                if (s.global)
                    jQuery.event.trigger("ajaxComplete", [xml, s]);

                // Handle the global AJAX counter
                if (s.global && !--jQuery.active)
                    jQuery.event.trigger("ajaxStop");

                // Process result
                if (s.complete)
                    s.complete(xml, status);

                jQuery(io).unbind()

                setTimeout(function()
                {
                    try
                    {
                        jQuery(io).remove();
                        jQuery(form).remove();

                    } catch (e)
                    {
                        jQuery.handleError(s, xml, null, e);
                    }

                }, 100)

                xml = null

            }
        }
        // Timeout checker
        if (s.timeout > 0)
        {
            setTimeout(function() {
                // Check to see if the request is still happening
                if (!requestDone)
                    uploadCallback("timeout");
            }, s.timeout);
        }
        try
        {

            var form = jQuery('#' + formId);
            jQuery(form).attr('action', s.url);
            jQuery(form).attr('method', 'POST');
            jQuery(form).attr('target', frameId);
            if (form.encoding)
            {
                jQuery(form).attr('encoding', 'multipart/form-data');
            }
            else
            {
                jQuery(form).attr('enctype', 'multipart/form-data');
            }
            jQuery(form).submit();

        } catch (e)
        {
            jQuery.handleError(s, xml, null, e);
        }

        //jQuery('#' + frameId).load(uploadCallback	);
        return {abort: function() {
            }};

    },
    uploadHttpData: function(r, type) {
        var data = !type;
        data = type == "xml" || data ? r.responseXML : r.responseText;
        // If the type is "script", eval it in global context
        if (type == "script")
            jQuery.globalEval(data);
        // Get the JavaScript object, if JSON is used.
        if (type == "json")
            eval("data = " + data);
        // evaluate scripts within html
        if (type == "html")
            jQuery("<div>").html(data).evalScripts();

        return data;
    },
    handleError: function(s, xhr, status, e) {
        // If a local callback was specified, fire it
        if (s.error) {
            s.error.call(s.context || window, xhr, status, e);
        }

        // Fire the global callback
        if (s.global) {
            (s.context ? jQuery(s.context) : jQuery.event).trigger("ajaxError", [xhr, s, e]);
        }
    }

})

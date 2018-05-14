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

    var label = '<p class="form-field e-book"><label>Choose eBook: </label>';
    select = '<select style="width:410px" id="ebook_library">    <option value="">Select one...</option>';
    if (woo_eg.library) jQuery.each(woo_eg.library, function(k, v) {
        select += '<option title="' + v.title + '" value="' + v.resource_id + '" data-drm-type="'+v.drm_type+'">' + v.title + ' (' + v.resource_id + ')' + '</option>';
    })
    select += '</select></p>';
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
        jQuery('.options_group.show_if_downloadable .e-book').show();
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
        jQuery('.options_group.show_if_downloadable .e-book').show();
    }
    else
    {
        
        jQuery('.options_group.show_if_downloadable .form-field').show();
        jQuery('.options_group.show_if_downloadable .form-field-drm').hide();
        jQuery('.options_group.show_if_downloadable .e-book').hide();
        jQuery(".upload_file_button").click(woo_eg_old_click_handler);
    }
}
jQuery( function($) {
    $.widget( "custom.combobox", {
      _create: function() {
        this.wrapper = $( "<span>" )
          .addClass( "custom-combobox" )
          .insertAfter( this.element );

        this.element.hide();
        this._createAutocomplete();
        this._createShowAllButton();
      },

      _createAutocomplete: function() {
        var selected = this.element.children( ":selected" ),
          value = selected.val() ? selected.text() : "";

        this.input = $( "<input>" )
          .appendTo( this.wrapper )
          .val( value )
          .attr( "title", "" )
          .addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
          .autocomplete({
            delay: 0,
            minLength: 0,
            source: $.proxy( this, "_source" )
          })
          .tooltip({
            classes: {
              "ui-tooltip": "ui-state-highlight"
            }
          });

        this._on( this.input, {
          autocompleteselect: function( event, ui ) {
            ui.item.option.selected = true;
            this._trigger( "select", event, {
              item: ui.item.option
            });
          },

          autocompletechange: "_removeIfInvalid"
        });
      },

      _createShowAllButton: function() {
        var input = this.input,
          wasOpen = false;

        $( "<a>" )
          .attr( "tabIndex", -1 )
          .attr( "title", "Show All Items" )
          .tooltip()
          .appendTo( this.wrapper )
          .button({
            icons: {
              primary: "ui-icon-triangle-1-s"
            },
            text: false
          })
          .removeClass( "ui-corner-all" )
          .addClass( "custom-combobox-toggle ui-corner-right" )
          .on( "mousedown", function() {
            wasOpen = input.autocomplete( "widget" ).is( ":visible" );
          })
          .on( "click", function() {
            input.trigger( "focus" );

            // Close if already visible
            if ( wasOpen ) {
              return;
            }

            // Pass empty string as value to search for, displaying all results
            input.autocomplete( "search", "" );
          });
      },

      _source: function( request, response ) {
        var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
        response( this.element.children( "option" ).map(function() {
          var text = $( this ).text();
          if ( this.value && ( !request.term || matcher.test(text) ) )
            return {
              label: text,
              value: text,
              option: this
            };
        }) );
      },

      _removeIfInvalid: function( event, ui ) {

        // Selected an item, nothing to do
        if ( ui.item ) {
          return;
        }

        // Search for a match (case-insensitive)
        var value = this.input.val(),
          valueLowerCase = value.toLowerCase(),
          valid = false;
        this.element.children( "option" ).each(function() {
          if ( $( this ).text().toLowerCase() === valueLowerCase ) {
            this.selected = valid = true;
            return false;
          }
        });

        // Found a match, nothing to do
        if ( valid ) {
          return;
        }

        // Remove invalid value
        this.input
          .val( "" )
          .attr( "title", value + " didn't match any item" )
          .tooltip( "open" );
        this.element.val( "" );
        this._delay(function() {
          this.input.tooltip( "close" ).attr( "title", "" );
        }, 2500 );
        this.input.autocomplete( "instance" ).term = "";
      },

      _destroy: function() {
        this.wrapper.remove();
        this.element.show();
      }
    });

    $( "#ebook_library" ).combobox();
} );






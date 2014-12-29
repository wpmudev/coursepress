/**
 * Avoid absolute URLs turning into relative URLs
 *
 * This is a fallback if the initArray doesn't work.
 */
jQuery( document ).ready( function( $ ) {
    if ( typeof tinyMCE !== 'undefined' ) {
        var edId;
        for (edId in tinyMCE.editors) {
            tinyMCE.editors[edId].settings.url_converter = null;
            tinyMCE.editors[edId].settings.url_converter_scope = null;
        }
    }
});
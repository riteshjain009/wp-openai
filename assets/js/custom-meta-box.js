jQuery( document ).ready( function( $ ) {
    $('#tothe_loader').hide();
    $( '#generate-button' ).click( function() {
        $('#tothe_loader').show();
        var question = $('<div>').text($('#custom-meta-question').val()).html();
        var nonce = custom_meta_box_data.nonce;
        var post_id = $( '#post_ID' ).val();
        $.ajax( {
            url: custom_meta_box_data.ajax_url,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'generate_answer',
                nonce: nonce,
                question: question,
                post_id: post_id
            },
            success: function( response ) {                                
                $('#tothe_loader').hide();    
                window.location.reload();
            },
            error: function( response ) {
                console.log( 'Error generating answer' );
            }
        } );
    } );
    // Content Plus Feature Image Generation 
    $( '#generate-button-image' ).click( function() {
        $('#tothe_loader').show();
        var question = $('<div>').text($('#custom-meta-question').val()).html();
        var nonce = custom_meta_box_data.nonce;
        var post_id = $( '#post_ID' ).val();
        $.ajax( {
            url: custom_meta_box_data.ajax_url,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'generate_answer_feature_image',
                nonce: nonce,
                question: question,
                post_id: post_id
            },
            success: function( response ) {                
                $('#tothe_loader').hide()        
                window.location.reload();
                
            },
            error: function( response ) {
                console.log( 'Error generating Image and content' );
            }
        } );
    } );
} );
jQuery(document).ready(function($) {
    var post_type = custom_meta_box_data.post_type;
    var posttype_array = custom_meta_box_data.visibility_posttypes;
    if(posttype_array.includes(post_type)) {
        $('body').on('blur', 'h1.wp-block-post-title', function(e) {

            var question_title = document.querySelector("h1.wp-block-post-title").textContent;
            $("#custom-meta-question").val(question_title);
    
            if ($('.editor-post-save-draft').length) {                
                $('.editor-post-save-draft').click();
            } else {                
                $('.editor-post-publish-button__button').click();
            }      
        });
    }
});
var CoursePress = CoursePress || {};

(function ( $ ) {

    // Init YouTube
    var tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    function bind_buttons() {

        $( '.apply-button' ).on( 'click', function( e ) {

            var target = e.currentTarget;

            if( $( target ).attr( 'data-link' ).length > 0 ) {
                location.href = $( target ).attr( 'data-link' );
            }

        } );



    }



    $( document ).ready( function ( $ ) {

        bind_buttons();



    });


} ) ( jQuery );


CoursePress.current = CoursePress.current || {};

function onYouTubeIframeAPIReady() {

    var $ = jQuery;

    // Course Featured Video
    var videoID = $( '#feature-video-div' ).attr('data-video');
    var width = $( '#feature-video-div' ).attr('data-width');
    var height = $( '#feature-video-div' ).attr('data-height');
    CoursePress.current.featuredVideo = new YT.Player( 'feature-video-div' ,
        {
            videoId: videoID,
            width: width,
            height: height,
            playerVars: { 'controls': 0, 'modestbranding': 1, 'rel': 0, 'showinfo': 0 },
            events: {
                //'onReady': function( event ) {}
                //'onPlaybackQualityChange': onPlayerPlaybackQualityChange,
                //'onStateChange': onPlayerStateChange,
                //'onError': onPlayerError
            }
        }
    );

}





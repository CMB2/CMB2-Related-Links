jQuery(document).ready(function($){

	window.relatedlinkdebug = function() {
		window.relatedlinkdebug.history = window.relatedlinkdebug.history || [];
		window.relatedlinkdebug.history.push( arguments );
		if ( window.console && debug ) {
			window.console.log( Array.prototype.slice.call(arguments) );
		}
	};

	function get_post_data( post_id, $object ) {
		window.relatedlinkdebug('post_id',post_id);
		$.post( ajaxurl, {
			action : 'cmb2_related_links_get_post_data',
			ajaxurl : ajaxurl,
			post_id : post_id
		}, function( response ) {
			window.relatedlinkdebug('response',response);
			if ( response.success && response.data.url ) {
				// update the url w/ the post permalink
				$object.val( response.data.url );
				// update the title w/ the post title
				var id = $object.attr( 'id' ).replace( '_url', '_title' );
				$( document.getElementById( id ) ).val( response.data.title );
			}
		} );
	}

	// Make sure window.cmb2_post_search is around
	setTimeout( function() {
		if ( window.cmb2_post_search ) {
			var handleSelected = $.proxy( window.cmb2_post_search.handleSelected, window.cmb2_post_search );
			// once a post is selected...
			window.cmb2_post_search.handleSelected = function( checked ) {
				window.relatedlinkdebug( 'cmb2_post_search.handleSelected OVERRIDE', phpClass );

				if ( this.$idInput.hasClass( 'post-search-data' ) ) {

					// ajax-grab the data we need
					get_post_data( checked[0], this.$idInput );

					this.close();

				} else {

					handleSelected( checked );
				}

			};
		}
	}, 500 );
});

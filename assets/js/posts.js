/* exported WordCampAwardsPosts */
var WordCampAwardsPosts = ( function( $ ) {
	'use strict';

	/**
	 * Awards posts wrapper function.
	 */
	var posts = function( element, data ) {
		this.element = element;
		this.data = data;
		this.options = $.extend( true, {}, this.defaults, element.data( 'wordcamp-awards' ) );
		this.init();
	};

	/**
	 * Awards prototype.
	 */
	posts.prototype = {

		/**
		 * Options defaults.
		 */
		defaults: {
			phpQuery: {}
		},

		/**
		 * Holds data.
		 */
		data: {},

		/**
		 * Holds options.
		 */
		options: {},

		/**
		 * Holds the element.
		 */
		element: null,

		/**
		 * Constructor.
		 */
		constructor: posts,

		/**
		 * Initialize awards posts.
		 */
		init: function() {
			this.render();
		},

		/**
		 * Fetch and render remote posts.
		 */
		render: function() {
			var self = this;

			$.ajax({
				type: 'post',
				url: this.data.ajaxurl,
				data: {
					action: 'wordcamp_awards_rest_posts',
					nonce: self.data.nonce,
					query: self.options.phpQuery
				}
			}).always( function( response ) {
				var html;

				if ( undefined !== response.success && true === response.success ) {
					html = $.parseHTML( response.data );

					if ( 0 === $( html ).length ) {
						return;
					}

					self.element.html( html );
				}
			});
		}
	};

	return {

		/**
		 * Boot awards remote posts from the DOM elements.
		 */
		boot: function( data ) {
			$( document ).ready( function() {
				$( '[data-wordcamp-awards]' ).each( function() {
					new posts( $( this ), data );
				});
			});
		}
	};
})( window.jQuery );


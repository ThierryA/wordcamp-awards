/* exported WordCampAwardsAdmin */
var WordCampAwardsAdmin = ( function( $ ) {
	'use strict';

	return {

		/**
		 * Holds data.
		 */
		data: {},

		/**
		 * Holds the field element.
		 */
		field: null,

		/**
		 * Holds the status element.
		 */
		status: null,

		/**
		 * Boot Awards admin.
		 */
		boot: function( data ) {
			this.data = data;
			this.listen();
		},

		/**
		 * Listen to events.
		 */
		listen: function() {
			var self = this;

			$( document ).ready( function() {
				self.field = $( '[name="wordcamp_award_site_details_url"]' );
				self.prepare();
				self.validate();

				self.field.keyup( function() {
					self.toggleSpinner();
				} );

				self.field.keyup( window._.debounce( function() {
					self.validate();
				}, 500 ) );
			});
		},

		/**
		 * Prepare Status.
		 */
		prepare: function() {
			this.status = $( '<div/>', {
				class: 'award-site-status',
			});

			this.field.after( this.status );
			this.toggleSpinner();
		},

		/**
		 * Toggle spinner.
		 */
		toggleSpinner: function() {
			this.status.html( $( '<span/>', {
				class: 'description spinner',
				text: this.data.spinnerText
			}).css({
				'float': 'none',
				'visibility': 'visible',
				'margin': '3px 0 0',
				'padding-left': '18px',
				'background-size': '14px',
				'width': '100%',
				'height': '18px',
				'line-height': '16px'
			}));
		},

		/**
		 * Toggle status.
		 */
		toggleStatusResult: function( success ) {
			var statusClass = true === success ? 'yes': 'no-alt',
				statusText  = true === success ? this.data.successText: this.data.failText;

			this.status.html( $( '<span/>', {
				class: 'description dashicons-before dashicons-' + statusClass,
				text: statusText
			}).css({
				'opacity': '.7',
				'display': 'block',
				'margin': '1px 0 0 -4px',
				'line-height': '20px'
			}));
		},

		/**
		 * Validate REST API accessibility.
		 */
		validate: function() {
			var self    = this,
				url     = this.field.val().replace( /\/$/, '' ),
				restUrl = url ? url + '/wp-json/wp/v2/posts?per_page=1': null;

			$.get( restUrl ).always( function( response ) {
				var status = undefined !== response[0] && undefined !== response[0].id;

				self.toggleStatusResult( status );
			});
		}
	};

})( window.jQuery );


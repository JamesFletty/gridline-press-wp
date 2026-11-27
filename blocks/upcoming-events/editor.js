/* Editor UI for gridline/upcoming-events. No build step: plain wp.* globals. */
( function ( wp ) {
	'use strict';
	const { registerBlockType } = wp.blocks;
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { PanelBody, RangeControl, TextControl } = wp.components;
	const { __ } = wp.i18n;
	const ServerSideRender = wp.serverSideRender;

	registerBlockType( 'gridline/upcoming-events', {
		edit( { attributes, setAttributes } ) {
			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Events', 'gridline' ) },
						el( TextControl, { label: __( 'Heading', 'gridline' ), value: attributes.heading, onChange: ( heading ) => setAttributes( { heading } ) } ),
						el( RangeControl, { label: __( 'How many', 'gridline' ), min: 1, max: 12, value: attributes.count, onChange: ( count ) => setAttributes( { count } ) } )
					)
				),
				el( 'div', useBlockProps(), el( ServerSideRender, { block: 'gridline/upcoming-events', attributes } ) )
			);
		},
		save: () => null,
	} );
} )( window.wp );

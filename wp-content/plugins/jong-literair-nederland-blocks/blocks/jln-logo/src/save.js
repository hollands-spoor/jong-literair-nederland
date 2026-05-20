/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import LogoSVG from './get_logo';

function getWordPressHomeUrl() {
	if ( typeof window === 'undefined' ) {
		return '/';
	}

	const apiRoot = window?.wpApiSettings?.root;
	if ( ! apiRoot ) {
		return '/';
	}

	try {
		const url = new URL( apiRoot );
		url.pathname = url.pathname.replace( /\/wp-json\/?$/, '/' );
		url.search = '';
		url.hash = '';
		return url.toString();
	} catch {
		return '/';
	}
}

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function save( { attributes } ) {
	const logoChoice = Number.isInteger( attributes.logo_choice ) ? attributes.logo_choice : 0;
	const linkUrl = ( attributes.link_url || '' ).trim();
	const resolvedLinkUrl = linkUrl === '#home' ? getWordPressHomeUrl() : linkUrl;
	const isNewWindow = !! attributes.new_window;
	const showJongText = attributes.show_jong_text !== false;
	const logoElement = <LogoSVG attributes={ attributes } />;
	const linkedLogoElement = resolvedLinkUrl ? (
		<a href={ resolvedLinkUrl } { ...( isNewWindow ? { target: '_blank', rel: 'noopener noreferrer', style: { zIndex: 1 } } : {} ) }>
			{ logoElement }
		</a>
	) : logoElement;
	const jongLogo = <LogoSVG attributes={ { ...attributes, logo_choice: 3, fill_font: '#13ccb0' } } />;
	const blockProps = useBlockProps.save( { className: `has-logo-${ logoChoice }` } );

	return (
		<div { ...blockProps }>
			{ logoChoice === 2 && showJongText ? (
					<div 
						className="ln-logo-jong-wrap"
						style= { { display: 'none', flexDirection: 'column', alignItems: 'center', position: 'absolute' } } >
						<div 
							className="ln-logo-jong-bezoek"
							style= { { display: 'flex', flexDirection: 'row' } } 
						>
							<p className="ln-logo-jong-note-1">
								{ __( 'Visit', 'jln-blocks' ) }
							</p>
						{ jongLogo }
						</div>
						<p className="ln-logo-jong-note-2">
							{ __( 'Literair Nederland', 'jln-blocks' ) }
						</p>
					</div>
			) : null }
			{ linkedLogoElement }
		</div>
	);
}
import { __ } from '@wordpress/i18n';
import './editor.scss';

import { useBlockProps, RichText } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';

export default function Edit( { attributes: { boektitel, isbn }, setAttributes } ) {
    const blockProps = useBlockProps();

    return (
        <div { ...blockProps }>
            <RichText
                tagName="p"
                className="ln-bibliographics__title"
                value={ boektitel }
                onChange={ ( value ) => setAttributes( { boektitel: value } ) }
                placeholder="Boektitel…"
                allowedFormats={ [ 'core/bold', 'core/italic' ] }
            />
            <TextControl
                value={ isbn }
				type="text"
                onChange={ ( value ) => setAttributes( { isbn: value } ) }
                placeholder="ISBN"

            />
        </div>
    );
}


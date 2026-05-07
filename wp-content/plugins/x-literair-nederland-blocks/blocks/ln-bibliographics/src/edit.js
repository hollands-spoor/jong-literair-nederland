import { __ } from '@wordpress/i18n';
import './editor.scss';

import {
    useBlockProps,
    RichText,
    MediaUpload,
    InspectorControls,
} from '@wordpress/block-editor';
import {
    TextControl,
    Button,
    PanelBody,
    ToggleControl,
    BaseControl
} from '@wordpress/components';
import { Fragment, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

// Bibliographic fields configuration passed from PHP via window.lnBibliographicFields.
const editorData = window.lnBibliographicFields || {};
const bibliographicFields = editorData.fields ? editorData.fields : {};

export default function Edit( { attributes, setAttributes, isSelected } ) {
    const {
        bibliographic = {},
        boektitel,
        isbn,
        showBuyButton = false,
        isSticky = false,
    } = attributes;
    const blockProps = useBlockProps( {
        style: isSticky ? { position: 'sticky' } : undefined,
    } );
    const [ isFetchingBibliographics, setIsFetchingBibliographics ] = useState( false );

    // Prefer values from the associative `bibliographic` object but fall back
    // to the legacy top-level attributes when needed.
    const values = {
        ...bibliographic,
        boektitel: bibliographic.boektitel ?? boektitel,
        isbn: bibliographic.isbn ?? isbn,
    };

    const updateBibliographic = ( nextValues = {} ) => {
        if ( ! nextValues || Object.keys( nextValues ).length === 0 ) {
            return;
        }

        const nextBibliographic = {
            ...bibliographic,
            ...nextValues,
        };

        const update = { bibliographic: nextBibliographic };

        if ( Object.prototype.hasOwnProperty.call( nextValues, 'boektitel' ) ) {
            update.boektitel = nextValues.boektitel;
        }
        if ( Object.prototype.hasOwnProperty.call( nextValues, 'isbn' ) ) {
            update.isbn = nextValues.isbn;
        }

        setAttributes( update );
    };

    const handleChange = ( fieldKey, value ) => {
        updateBibliographic( { [ fieldKey ]: value } );
    };

    const handleToggleBuyButton = ( nextValue ) => {
        setAttributes( { showBuyButton: nextValue } );
    };

    const handleToggleSticky = ( nextValue ) => {
        setAttributes( { isSticky: nextValue } );
    };

    const formatPrice = ( rawPrice = '' ) => {
        const normalizedRawPrice = String( rawPrice ).trim();

        if ( ! normalizedRawPrice ) {
            return '';
        }

        let numeric = normalizedRawPrice.replace( /[^0-9,.]/g, '' );

        if ( ! numeric ) {
            return normalizedRawPrice;
        }

        if ( numeric.includes( ',' ) && numeric.includes( '.' ) ) {
            numeric = numeric.replace( /\./g, '' ).replace( /,/g, '.' );
        } else if ( numeric.includes( ',' ) ) {
            numeric = numeric.replace( /,/g, '.' );
        }

        const amount = Number( numeric );

        if ( Number.isNaN( amount ) ) {
            return normalizedRawPrice;
        }

        return `€ ${ amount.toLocaleString( undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        } ) }`;
    };

    const handleFetchClick = async () => {
        const currentIsbn = values.isbn ?? '';
        if ( ! currentIsbn || isFetchingBibliographics ) {
            return;
        }

        try {
            setIsFetchingBibliographics( true );
            const response = await apiFetch( {
                path: `/x-ln/v1/bibliographics?isbn=${ encodeURIComponent( currentIsbn ) }`,
                method: 'GET',
            } );

            if ( response && response.success && response.fields ) {
                updateBibliographic( response.fields );
            }
        } catch ( error ) {
            // eslint-disable-next-line no-console
            console.error( 'Error fetching bibliographics:', error );
        } finally {
            setIsFetchingBibliographics( false );
        }
    };

    const renderEditableField = ( [ key, config ] ) => {
        const { type, label, hidden } = config;
        const fieldValue = values[ key ] ?? '';

        if ( hidden ) {
            return (
                <input
                    key={ key }
                    type="hidden"
                    value={ fieldValue }
                    onChange={ ( event ) => handleChange( key, event.target.value ) }
                />
            );
        }

        if ( type === 'richtext' ) {
            return (
                <BaseControl key={ key } label={ label }>
                    <RichText
                        tagName="p"
                        className={ `ln-bibliographics__${ key }` }
                        value={ fieldValue }
                        onChange={ ( value ) => handleChange( key, value ) }
                        placeholder={ label || key }
                        allowedFormats={ [ 'core/bold', 'core/italic' ] }
                    />
                </BaseControl>
            );
        }

        if ( type === 'image' ) {
            const imageUrl = fieldValue;
            const omslagIdFieldKey = 'omslag_id';

            const updateImageFields = ( url = '', id = '' ) => {
                const updates = { [ key ]: url };

                if ( bibliographicFields[ omslagIdFieldKey ] ) {
                    updates[ omslagIdFieldKey ] = id ?? '';
                }

                updateBibliographic( updates );
            };

            const onSelectImage = ( media ) => {
                if ( ! media || ! media.url ) {
                    updateImageFields( '', '' );
                    return;
                }

                updateImageFields( media.url, media.id ?? '' );
            };

            return (
                <div key={ key } className="ln-bibliographics__image-field">
                    { imageUrl ? (
                        // eslint-disable-next-line jsx-a11y/alt-text
                        <img
                            src={ imageUrl }
                            className="ln-bibliographics__image-preview"
                        />
                    ) : (
                        <div className="ln-bibliographics__cover-placeholder" aria-hidden="true" />
                    ) }
                    <MediaUpload
                        onSelect={ onSelectImage }
                        allowedTypes={ [ 'image' ] }
                        render={ ( { open } ) => (
                            <Button
                                variant="secondary"
                                onClick={ open }
                            >
                                { imageUrl
                                    ? __( 'Change cover image', 'x-literair-nederland-blocks' )
                                    : __( 'Select cover image', 'x-literair-nederland-blocks' ) }
                            </Button>
                        ) }
                    />
                    { imageUrl && (
                        <Button
                            variant="link"
                            isDestructive
                            onClick={ () => updateImageFields( '', '' ) }
                        >
                            { __( 'Remove cover image', 'x-literair-nederland-blocks' ) }
                        </Button>
                    ) }
                </div>
            );
        }

        if ( key === 'isbn' ) {
            return (
                <div key={ key } className="ln-bibliographics__isbn-row">
                    <TextControl
                        value={ fieldValue }
                        type="text"
                        label={ label || key }
                        onChange={ ( value ) => handleChange( key, value ) }
                        placeholder={ label || key }
                    />
                    <Button
                        variant="secondary"
                        onClick={ handleFetchClick }
                        disabled={ isFetchingBibliographics }
                        aria-busy={ isFetchingBibliographics }
                    >
                        { isFetchingBibliographics
                            ? __( 'Fetching bibliographics...', 'x-literair-nederland-blocks' )
                            : __( 'Fetch bibliographics', 'x-literair-nederland-blocks' ) }
                    </Button>
                </div>
            );
        }

        return (
            <TextControl
                key={ key }
                value={ fieldValue }
                type={ type === 'number' ? 'number' : 'text' }
                label={ label || key }
                onChange={ ( value ) => handleChange( key, value ) }
                placeholder={ label || key }
            />
        );
    };

    const previewBoektitel = values.boektitel ?? '';
    const previewAuteur = values.auteur_boek ?? '';
    const previewIsbn = values.isbn ?? '';
    const previewUitgever = values.uitgever ?? '';
    const previewOmslag = values.omslag ?? '';
    const previewAantalPaginas = values.aantal_paginas ?? '';
    const previewVertalingDoor = values.vertaling_door ?? '';
    const previewVrijeRegel = values.vrije_regel ?? '';
    const previewOorspronkelijkeTitel = values.oorspronkelijke_titel ?? '';
    const previewVoorwoordDoor = values.voorwoord_door ?? '';
    const previewNawoordDoor = values.nawoord_door ?? '';
    const previewIllustratiesDoor = values.illustraties_door ?? '';
    const previewFormattedPrice = formatPrice( values.prijs ?? '' );

    return (
        <Fragment>
            <InspectorControls>
                <PanelBody
                    title={ __( 'Buy button', 'x-literair-nederland-blocks' ) }
                    initialOpen={ false }
                >
                    <ToggleControl
                        label={ __( 'Show buy button', 'x-literair-nederland-blocks' ) }
                        checked={ showBuyButton }
                        onChange={ handleToggleBuyButton }
                    />
                </PanelBody>
            </InspectorControls>
            <InspectorControls group="styles">
                <PanelBody title={ __( 'Design', 'x-literair-nederland-blocks' ) }>
                    <ToggleControl
                        label={ __( 'Sticky', 'x-literair-nederland-blocks' ) }
                        checked={ isSticky }
                        onChange={ handleToggleSticky }
                    />
                </PanelBody>
            </InspectorControls>
            <div { ...blockProps }>
                { isSelected ? (
                    Object.entries( bibliographicFields ).map( renderEditableField )
                ) : (
                    <div className="ln-bibliographics__preview">
                        { previewOmslag ? (
                            <img
                                src={ previewOmslag }
                                className="ln-bibliographics__cover"
                                alt={ previewBoektitel ? previewBoektitel.replace( /<[^>]+>/g, '' ) : '' }
                                loading="lazy"
                            />
                        ) : (
                            <div className="ln-bibliographics__cover-placeholder ln-bibliographics__cover-placeholder--preview">
								<span>{ __( 'Click here to edit', 'x-literair-nederland-blocks' ) }</span>
                            </div>
                        ) }
                        { previewBoektitel && (
                            <RichText.Content
                                tagName="h2"
                                className="ln-boektitel"
                                value={ previewBoektitel }
                            />
                        ) }
                        { previewAuteur && <p className="ln-auteur">{ previewAuteur }</p> }
                        { previewVrijeRegel && <p>{ previewVrijeRegel }</p> }
                        { previewVertalingDoor && (
                            <p>
                                { __( 'Translation by:', 'x-literair-nederland-blocks' ) } { previewVertalingDoor }
                            </p>
                        ) }
                        { previewOorspronkelijkeTitel && (
                            <p>
                                { __( 'Original title:', 'x-literair-nederland-blocks' ) } { previewOorspronkelijkeTitel }
                            </p>
                        ) }
                        { previewVoorwoordDoor && (
                            <p>
                                { __( 'Foreword by:', 'x-literair-nederland-blocks' ) } { previewVoorwoordDoor }
                            </p>
                        ) }
                        { previewNawoordDoor && (
                            <p>
                                { __( 'Afterword by:', 'x-literair-nederland-blocks' ) } { previewNawoordDoor }
                            </p>
                        ) }
                        { previewIllustratiesDoor && (
                            <p>
                                { __( 'Illustrations by:', 'x-literair-nederland-blocks' ) } { previewIllustratiesDoor }
                            </p>
                        ) }
                        { previewUitgever && (
                            <p>
								{ __( 'Publisher:', 'x-literair-nederland-blocks' ) } { previewUitgever }
                            </p>
                        ) }
                        { previewIsbn && (
                            <p className="ln-isbn">
                                { __( 'ISBN', 'x-literair-nederland-blocks' ) } { previewIsbn }
                            </p>
                        ) }
                        { previewAantalPaginas && (
							<p className="ln-aantal-paginas">{ previewAantalPaginas } pages</p>
                        ) }
                        { previewFormattedPrice && (
                            <p className="ln-prijs">
								{ __( 'Price:', 'x-literair-nederland-blocks' ) } { previewFormattedPrice }
                            </p>
                        ) }
                        { showBuyButton && previewIsbn && (
                            <span className="ln-bibliographics__buy-button">
                                { __( 'Buy button', 'x-literair-nederland-blocks' ) }
                            </span>
                        ) }
                    </div>
                ) }
            </div>
        </Fragment>
    );
}

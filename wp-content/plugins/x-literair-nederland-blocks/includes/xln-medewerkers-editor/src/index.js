import apiFetch from '@wordpress/api-fetch';
import { Button, Notice, Spinner, TextControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { createElement, useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { registerPlugin } from '@wordpress/plugins';

const settings = window.XlnMedewerkersSettings || null;

if ( settings ) {
	const fieldTypes = settings.fieldTypes || {};
	const targetPostTypes = Array.isArray( settings.targetPostTypes ) ? settings.targetPostTypes : [];
	const searchMinLength = Number.isInteger( settings.searchMinLength ) ? settings.searchMinLength : 2;
	const labels = settings.labels || {};

	function normalizeOptions( fieldType, items ) {
		if ( ! Array.isArray( items ) ) {
			return [];
		}

		return items
			.filter( function ( item ) {
				return item && item.id && item.label;
			} )
			.map( function ( item ) {
				return {
					value: String( item.id ),
					label: item.label,
					item: item,
				};
			} )
			.filter( function ( option ) {
				return ! fieldTypes[ fieldType ] || ! fieldTypes[ fieldType ].postType || option.item.post_type === fieldTypes[ fieldType ].postType;
			} );
	}

	function mergeOptions( fieldType, currentOptions, nextItems ) {
		const merged = {};

		currentOptions.forEach( function ( option ) {
			merged[ option.value ] = option;
		} );

		normalizeOptions( fieldType, nextItems ).forEach( function ( option ) {
			merged[ option.value ] = option;
		} );

		return Object.keys( merged ).map( function ( key ) {
			return merged[ key ];
		} );
	}

	function RelationField( props ) {
		const fieldType = props.fieldType;
		const title = props.title;
		const placeholder = props.placeholder;
		const createButton = props.createButton;
		const selectedId = props.selectedId;
		const onChoose = props.onChoose;
		const successMessage = props.successMessage;
		const createSuccessMessage = props.createSuccessMessage;

		const [ options, setOptions ] = useState( [] );
		const [ inputValue, setInputValue ] = useState( '' );
		const [ isLoading, setIsLoading ] = useState( false );
		const [ errorMessage, setErrorMessage ] = useState( '' );
		const lastSelectedLabelRef = useRef( '' );
		const previousSelectedIdRef = useRef( selectedId );
		const noticesDispatch = useDispatch( 'core/notices' );
		const selectedValue = selectedId > 0 ? String( selectedId ) : null;

		useEffect( function () {
			if ( ! selectedId ) {
				return;
			}

			const hasSelected = options.some( function ( option ) {
				return Number( option.value ) === selectedId;
			} );

			if ( hasSelected ) {
				return;
			}

			apiFetch( {
				path: settings.restPath + '?fieldType=' + encodeURIComponent( fieldType ) + '&selected=' + encodeURIComponent( selectedId ),
			} ).then( function ( response ) {
				setOptions( function ( currentOptions ) {
					return mergeOptions( fieldType, currentOptions, response.items || [] );
				} );
			} ).catch( function () {
				// Ignore hydration failures so the editor stays usable.
			} );
		}, [ fieldType, options, selectedId ] );

		useEffect( function () {
			if ( ! selectedId || inputValue.trim() !== '' ) {
				return;
			}

			const hydratedOption = options.find( function ( option ) {
				return Number( option.value ) === selectedId;
			} );

			if ( hydratedOption ) {
				lastSelectedLabelRef.current = hydratedOption.item.label;
				setInputValue( hydratedOption.item.label );
			}
		}, [ inputValue, options, selectedId ] );

		useEffect( function () {
			const previousSelectedId = previousSelectedIdRef.current;

			if ( previousSelectedId > 0 && selectedId === 0 && inputValue === lastSelectedLabelRef.current ) {
				setInputValue( '' );
				setErrorMessage( '' );
			}

			previousSelectedIdRef.current = selectedId;
		}, [ inputValue, selectedId ] );

		useEffect( function () {
			const trimmedInput = inputValue.trim();

			if ( trimmedInput.length > 0 && trimmedInput.length < searchMinLength ) {
				return undefined;
			}

			if ( trimmedInput.length === 0 && ! selectedId ) {
				return undefined;
			}

			const timeoutId = window.setTimeout( function () {
				setIsLoading( true );
				setErrorMessage( '' );

				const query = [ 'fieldType=' + encodeURIComponent( fieldType ) ];

				if ( trimmedInput.length >= searchMinLength ) {
					query.push( 'search=' + encodeURIComponent( trimmedInput ) );
				}

				if ( selectedId ) {
					query.push( 'selected=' + encodeURIComponent( selectedId ) );
				}

				apiFetch( {
					path: settings.restPath + '?' + query.join( '&' ),
				} ).then( function ( response ) {
					setOptions( function ( currentOptions ) {
						return mergeOptions( fieldType, currentOptions, response.items || [] );
					} );
				} ).catch( function ( error ) {
					setErrorMessage( error && error.message ? error.message : labels.noResults );
				} ).finally( function () {
					setIsLoading( false );
				} );
			}, 250 );

			return function () {
				window.clearTimeout( timeoutId );
			};
		}, [ fieldType, inputValue, selectedId ] );

		const selectedOption = options.find( function ( option ) {
			return option.value === selectedValue;
		} );
		const selectedLabel = selectedOption && selectedOption.item && selectedOption.item.label ? selectedOption.item.label : '';
		const normalizedInput = inputValue.trim().toLowerCase();
		const showResults = inputValue.trim().length >= searchMinLength && !( selectedId > 0 && inputValue === selectedLabel );
		const visibleOptions = showResults ? options.filter( function ( option ) {
			return option.item.label.toLowerCase().indexOf( normalizedInput ) !== -1;
		} ) : [];

		const canCreate = inputValue.trim().length > 0 && ! isLoading && ! options.some( function ( option ) {
			return option.item.label.toLowerCase() === normalizedInput;
		} );

		function handleSelect( option ) {
			const nextId = option ? Number( option.value ) : 0;
			setErrorMessage( '' );
			lastSelectedLabelRef.current = option ? option.item.label : '';
			setInputValue( option ? option.item.label : '' );
			onChoose( nextId );
		}

		function handleInputChange( nextValue ) {
			const value = nextValue || '';
			setInputValue( value );
			setErrorMessage( '' );

			if ( selectedId > 0 && value !== selectedLabel ) {
				lastSelectedLabelRef.current = selectedLabel;
				onChoose( 0 );
			}
		}

		function handleCreate() {
			const name = inputValue.trim();

			if ( ! name ) {
				return;
			}

			setIsLoading( true );
			setErrorMessage( '' );

			apiFetch( {
				path: settings.restPath,
				method: 'POST',
				data: {
					fieldType: fieldType,
					name: name,
				},
			} ).then( function ( response ) {
				const item = response && response.item ? response.item : null;

				if ( ! item ) {
					return;
				}

				setOptions( function ( currentOptions ) {
					return mergeOptions( fieldType, currentOptions, [ item ] );
				} );

				lastSelectedLabelRef.current = item.label;
				onChoose( Number( item.id ) );
				setInputValue( item.label );

				noticesDispatch.createNotice(
					'success',
					response.created ? createSuccessMessage : successMessage,
					{
						type: 'snackbar',
						isDismissible: true,
					}
				);
			} ).catch( function ( error ) {
				setErrorMessage( error && error.message ? error.message : labels.noResults );
			} ).finally( function () {
				setIsLoading( false );
			} );
		}

		return createElement(
			'div',
			{ className: 'xln-medewerkers-field', style: { marginBottom: '16px' } },
			createElement( TextControl, {
				label: title,
				value: inputValue,
				onChange: handleInputChange,
				placeholder: placeholder,
				help: inputValue.trim().length > 0 && inputValue.trim().length < searchMinLength ? labels.minLength : '',
			} ),
			isLoading ? createElement( Spinner, null ) : null,
			showResults && visibleOptions.length > 0 ? createElement(
				'div',
				{
					className: 'xln-medewerkers-results',
					style: {
						border: '1px solid #ddd',
						borderRadius: '2px',
						marginTop: '8px',
						maxHeight: '180px',
						overflowY: 'auto',
					},
				},
				visibleOptions.map( function ( option ) {
					return createElement(
						Button,
						{
							key: option.value,
							variant: 'tertiary',
							onMouseDown: function ( event ) {
								event.preventDefault();
							},
							onClick: function () {
								handleSelect( option );
							},
							style: {
								display: 'block',
								width: '100%',
								textAlign: 'left',
								borderRadius: 0,
								padding: '8px 12px',
							},
						},
						option.item.label
					);
				} )
			) : null,
			canCreate ? createElement( Button, {
				variant: 'secondary',
				onMouseDown: function ( event ) {
					event.preventDefault();
				},
				onClick: handleCreate,
				style: { marginTop: '8px' },
			}, createButton ) : null,
			errorMessage ? createElement( Notice, {
				status: 'error',
				isDismissible: false,
			}, errorMessage ) : null
		);
	}

	function MedewerkerPanel() {
		const medewerkerMetaKey = fieldTypes.medewerker && fieldTypes.medewerker.metaKey ? fieldTypes.medewerker.metaKey : 'medewerker_id';
		const recensentMetaKey = fieldTypes.recensent && fieldTypes.recensent.metaKey ? fieldTypes.recensent.metaKey : 'auteur_recensie';

		const editorState = useSelect( function ( select ) {
			const editorStore = select( 'core/editor' );

			if ( ! editorStore ) {
				return {
					postType: '',
					medewerkerId: 0,
					recensentId: 0,
				};
			}

			const meta = editorStore.getEditedPostAttribute( 'meta' ) || {};

			return {
				postType: editorStore.getCurrentPostType() || '',
				medewerkerId: Number( meta[ medewerkerMetaKey ] || 0 ),
				recensentId: Number( meta[ recensentMetaKey ] || 0 ),
			};
		}, [ medewerkerMetaKey, recensentMetaKey ] );

		const editorDispatch = useDispatch( 'core/editor' );
		const medewerkerId = editorState.medewerkerId;
		const recensentId = editorState.recensentId;

		const isEligible = useMemo( function () {
			return targetPostTypes.includes( editorState.postType );
		}, [ editorState.postType ] );

		if ( ! isEligible || ! PluginDocumentSettingPanel ) {
			return null;
		}

		function editMeta( nextMeta ) {
			const currentMeta = ( window.wp.data.select( 'core/editor' ).getEditedPostAttribute( 'meta' ) ) || {};

			editorDispatch.editPost( {
				meta: Object.assign( {}, currentMeta, nextMeta ),
			} );
		}

		return createElement(
			PluginDocumentSettingPanel,
			{
				name: 'xln-medewerkers-panel',
				title: labels.panelTitle,
			},
			createElement( 'p', { style: { marginTop: 0 } }, labels.helper ),
			createElement( RelationField, {
				fieldType: 'medewerker',
				title: labels.medewerkerTitle,
				placeholder: labels.medewerkerPlaceholder,
				createButton: labels.medewerkerCreateButton,
				selectedId: medewerkerId,
				onChoose: function ( nextId ) {
					editMeta( {
						[ medewerkerMetaKey ]: nextId,
						[ recensentMetaKey ]: nextId > 0 ? 0 : recensentId,
					} );
				},
				successMessage: labels.medewerkerSuccess,
				createSuccessMessage: labels.medewerkerCreateSuccess,
			} ),
			createElement( RelationField, {
				fieldType: 'recensent',
				title: labels.recensentTitle,
				placeholder: labels.recensentPlaceholder,
				createButton: labels.recensentCreateButton,
				selectedId: recensentId,
				onChoose: function ( nextId ) {
					editMeta( {
						[ medewerkerMetaKey ]: nextId > 0 ? 0 : medewerkerId,
						[ recensentMetaKey ]: nextId,
					} );
				},
				successMessage: labels.recensentSuccess,
				createSuccessMessage: labels.recensentCreateSuccess,
			} )
		);
	}

	registerPlugin( 'xln-medewerkers', {
		render: MedewerkerPanel,
	} );
}
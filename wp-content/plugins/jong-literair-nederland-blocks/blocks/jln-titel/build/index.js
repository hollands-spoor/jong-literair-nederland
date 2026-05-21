/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./blocks/jln-titel/src/edit.js"
/*!**************************************!*\
  !*** ./blocks/jln-titel/src/edit.js ***!
  \**************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/html-entities */ "@wordpress/html-entities");
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./editor.scss */ "./blocks/jln-titel/src/editor.scss");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__);







const TITLE_LEVEL_OPTIONS = [{
  label: 'H1',
  value: 'h1'
}, {
  label: 'H2',
  value: 'h2'
}, {
  label: 'H3',
  value: 'h3'
}, {
  label: 'H4',
  value: 'h4'
}, {
  label: 'H5',
  value: 'h5'
}, {
  label: 'H6',
  value: 'h6'
}];
function Edit({
  attributes,
  setAttributes,
  context = {}
}) {
  const {
    titleLevel = 'h1',
    showDate = true,
    showBoekInfo = true,
    showRecensent = true
  } = attributes;
  const {
    postId: contextPostId,
    postType: contextPostType
  } = context;
  const {
    postTitle,
    postDate,
    auteurRecensie,
    auteurBoek,
    boektitel
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_2__.useSelect)(select => {
    const editorStore = select('core/editor');
    const coreStore = select('core');
    const hasContextPost = Number(contextPostId) > 0 && typeof contextPostType === 'string' && contextPostType.length > 0;
    let sourceTitle = '';
    let sourceDate = '';
    let meta = {};
    if (hasContextPost && coreStore && typeof coreStore.getEntityRecord === 'function') {
      const entity = coreStore.getEntityRecord('postType', contextPostType, Number(contextPostId));
      if (entity) {
        if (typeof entity?.title?.rendered === 'string') {
          sourceTitle = (0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3__.decodeEntities)(entity.title.rendered.replace(/<[^>]+>/g, '').trim());
        } else if (typeof entity?.title?.raw === 'string') {
          sourceTitle = entity.title.raw;
        } else if (typeof entity?.title === 'string') {
          sourceTitle = entity.title;
        }
        sourceDate = entity?.date || '';
        meta = entity?.meta || {};
      }
    } else if (editorStore && typeof editorStore.getEditedPostAttribute === 'function') {
      sourceTitle = editorStore.getEditedPostAttribute('title') || '';
      sourceDate = editorStore.getEditedPostAttribute('date') || '';
      meta = editorStore.getEditedPostAttribute('meta') || {};
    }
    const auteurRecensieRaw = meta.auteur_recensie || '';
    let auteurRecensieValue = auteurRecensieRaw;
    if (/^\d+$/.test(String(auteurRecensieRaw)) && coreStore && typeof coreStore.getEntityRecord === 'function') {
      const auteurRecensieId = Number(auteurRecensieRaw);
      const postTypes = typeof coreStore.getPostTypes === 'function' ? coreStore.getPostTypes({
        per_page: -1
      }) || [] : [];
      for (const postType of postTypes) {
        if (!postType || !postType.slug) {
          continue;
        }
        const post = coreStore.getEntityRecord('postType', postType.slug, auteurRecensieId);
        if (!post) {
          continue;
        }
        const renderedTitle = typeof post?.title?.rendered === 'string' ? post.title.rendered : '';
        const plainTitle = renderedTitle.replace(/<[^>]+>/g, '').trim();
        if (plainTitle) {
          auteurRecensieValue = plainTitle;
          break;
        }
        if (typeof post?.title === 'string' && post.title.trim()) {
          auteurRecensieValue = post.title.trim();
          break;
        }
      }
    }
    return {
      postTitle: sourceTitle,
      postDate: sourceDate,
      auteurRecensie: auteurRecensieValue,
      auteurBoek: meta.besproken_boeken_0_auteur_boek || '',
      boektitel: meta.besproken_boeken_0_boektitel || ''
    };
  }, [contextPostId, contextPostType]);
  const HeadingTag = titleLevel || 'h1';
  const hasBoekInfo = Boolean(auteurBoek || boektitel);
  const boekInfoValue = [auteurBoek, boektitel].filter(Boolean).join(' - ');
  const rows = [];
  if (showDate && postDate) {
    rows.push({
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Date', 'jln-blocks'),
      value: postDate,
      className: 'ln-titel__post-date'
    });
  }
  if (showBoekInfo && hasBoekInfo) {
    rows.push({
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Book', 'jln-blocks'),
      value: boekInfoValue,
      className: 'ln-titel__boek-info'
    });
  }
  if (showRecensent && auteurRecensie) {
    rows.push({
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Reviewer', 'jln-blocks'),
      value: auteurRecensie,
      className: 'ln-titel__auteur-recensie'
    });
  }
  const hasVisibleValues = rows.length > 0 || Boolean(postTitle);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Settings', 'jln-blocks'),
        initialOpen: true,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.SelectControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Title level', 'jln-blocks'),
          value: titleLevel,
          options: TITLE_LEVEL_OPTIONS,
          onChange: value => setAttributes({
            titleLevel: value
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Show date', 'jln-blocks'),
          checked: showDate,
          onChange: value => setAttributes({
            showDate: value
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Show book title/book author', 'jln-blocks'),
          checked: showBoekInfo,
          onChange: value => setAttributes({
            showBoekInfo: value
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Show reviewer', 'jln-blocks'),
          checked: showRecensent,
          onChange: value => setAttributes({
            showRecensent: value
          })
        })]
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
      ...(0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps)(),
      children: hasVisibleValues ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.Fragment, {
        children: [rows.map(row => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
          className: `ln-titel__row ${row.className}`,
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("span", {
            className: "ln-titel__label",
            children: [row.label, ":"]
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("span", {
            className: "ln-titel__value",
            children: row.value
          })]
        }, row.className)), postTitle && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(HeadingTag, {
          className: "ln-titel__post-title",
          children: postTitle
        })]
      }) : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No preview values yet. Save post meta values to see the review title preview.', 'jln-blocks')
    })]
  });
}

/***/ },

/***/ "./blocks/jln-titel/src/index.js"
/*!***************************************!*\
  !*** ./blocks/jln-titel/src/index.js ***!
  \***************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./block.json */ "./blocks/jln-titel/src/block.json");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./blocks/jln-titel/src/edit.js");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./save */ "./blocks/jln-titel/src/save.js");
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./style.scss */ "./blocks/jln-titel/src/style.scss");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__);






(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_1__.name, {
  icon: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsxs)("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    width: "120",
    height: "120",
    viewBox: "-10 -1 80 82",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)("circle", {
      cx: "30",
      cy: "40",
      r: "40",
      "stroke-width": "2px",
      stroke: "#000000",
      fill: "none"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)("path", {
      id: "pages",
      d: "M -8.0206 4.5122 L 30 10 L 68.0206 4.5122 L 68.0206 75.4878 L 30 70 L -8.0206 75.4878 Z",
      fill: "#999999"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)("path", {
      d: "M 25.5416 46.8004 L 26.2021 46.9328 23.8583 55.4262 L -0.5656 57.527 L -0.5656 56.5672 L 0.7734 56.4584 Q 2.9919 56.278 3.9315 54.781 4.4609 53.9322 4.4609 50.9974 L 4.4609 28.8602 Q 4.4609 25.6471 3.7544 24.7836 2.762 23.5852 0.7734 23.4224 L -0.5656 23.3127 L -0.5656 22.3529 L 14.4646 23.6546 L 14.4646 24.5436 Q 11.9837 24.3161 10.9542 24.7455 9.9372 25.1815 9.5561 25.9411 9.1734 26.7037 9.1734 29.6924 L 9.1734 50.7367 Q 9.1734 52.7867 9.5561 53.5257 9.8421 54.0226 10.4352 54.225 11.0247 54.4262 14.0556 54.1986 L 16.2835 54.0314 Q 19.6895 53.7757 21.0301 53.1417 22.3513 52.5169 23.4274 51.1151 24.4907 49.7071 25.5416 46.8004 Z",
      id: "char-l",
      fill: "#ffffff",
      transform: "scale(1,1.4) translate(0,-12)"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)("path", {
      d: "M 31.7495 24.8485 L 39.2254 24.2011 L 58.6565 48.6517 L 58.6565 28.557 Q 58.6565 25.3449 57.9329 24.6037 56.9846 23.606 54.9646 23.7714 L 53.7967 23.867 L 53.7967 22.9391 L 66.1895 21.8659 L 66.1895 22.8522 L 64.8584 22.9612 Q 62.5155 23.153 61.5503 24.5874 60.9649 25.4632 60.9649 28.4261 L 60.9649 58.1331 L 60.0142 58.0487 L 38.9505 30.8566 L 38.9505 50.2186 Q 38.9505 53.1171 39.5225 53.8794 40.3336 54.9223 42.0432 55.0613 L 43.0976 55.147 L 43.0976 56.0245 L 33.223 55.1752 L 33.223 54.3442 L 34.1591 54.4203 Q 35.9184 54.5633 36.6566 53.4328 37.1107 52.7322 37.1107 50.1154 L 37.1107 28.47 Q 35.898 26.9744 35.2673 26.5204 34.6611 26.0681 33.481 25.7332 32.9066 25.5778 31.7495 25.6725 Z",
      id: "char-n",
      fill: "#ffffff",
      transform: "scale(1,1.4) translate(0,-12)"
    })]
  }),
  edit: _edit__WEBPACK_IMPORTED_MODULE_2__["default"],
  save: _save__WEBPACK_IMPORTED_MODULE_3__["default"]
});

/***/ },

/***/ "./blocks/jln-titel/src/save.js"
/*!**************************************!*\
  !*** ./blocks/jln-titel/src/save.js ***!
  \**************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ save)
/* harmony export */ });
function save() {
  return null;
}

/***/ },

/***/ "./blocks/jln-titel/src/editor.scss"
/*!******************************************!*\
  !*** ./blocks/jln-titel/src/editor.scss ***!
  \******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "./blocks/jln-titel/src/style.scss"
/*!*****************************************!*\
  !*** ./blocks/jln-titel/src/style.scss ***!
  \*****************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "react/jsx-runtime"
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
(module) {

module.exports = window["ReactJSXRuntime"];

/***/ },

/***/ "@wordpress/block-editor"
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
(module) {

module.exports = window["wp"]["blockEditor"];

/***/ },

/***/ "@wordpress/blocks"
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
(module) {

module.exports = window["wp"]["blocks"];

/***/ },

/***/ "@wordpress/components"
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
(module) {

module.exports = window["wp"]["components"];

/***/ },

/***/ "@wordpress/data"
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["data"];

/***/ },

/***/ "@wordpress/html-entities"
/*!**************************************!*\
  !*** external ["wp","htmlEntities"] ***!
  \**************************************/
(module) {

module.exports = window["wp"]["htmlEntities"];

/***/ },

/***/ "@wordpress/i18n"
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["i18n"];

/***/ },

/***/ "./blocks/jln-titel/src/block.json"
/*!*****************************************!*\
  !*** ./blocks/jln-titel/src/block.json ***!
  \*****************************************/
(module) {

module.exports = /*#__PURE__*/JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"jln/jln-titel","version":"0.1.0","title":"LN Titel","category":"literair-nederland","icon":"heading","description":"Dynamic title block with recensie metadata.","supports":{"html":false,"align":["wide","full"],"color":{"background":true,"text":true},"border":true,"spacing":{"margin":true,"padding":true}},"attributes":{"titleLevel":{"type":"string","default":"h1"},"showDate":{"type":"boolean","default":true},"showBoekInfo":{"type":"boolean","default":true},"showRecensent":{"type":"boolean","default":true}},"usesContext":["postId","postType","queryId"],"textdomain":"jln-blocks","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css","render":"file:./render.php"}');

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Check if module exists (development only)
/******/ 		if (__webpack_modules__[moduleId] === undefined) {
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"index": 0,
/******/ 			"./style-index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = globalThis["webpackChunkjong_ln_blocks"] = globalThis["webpackChunkjong_ln_blocks"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["./style-index"], () => (__webpack_require__("./blocks/jln-titel/src/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map
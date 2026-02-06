/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./blocks/jln-floating-text/src/edit.js"
/*!**********************************************!*\
  !*** ./blocks/jln-floating-text/src/edit.js ***!
  \**********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _get_textpath_svg__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./get_textpath_svg */ "./blocks/jln-floating-text/src/get_textpath_svg.js");
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./editor.scss */ "./blocks/jln-floating-text/src/editor.scss");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__);







const DEFAULT_TEMPLATE = [["core/paragraph", {
  placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Add accompanying text...", "jong-literair-nederland-blocks")
}]];
const GUIDE_CENTER_DIAMETER = 12;
const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
const degToRad = degrees => degrees * Math.PI / 180;
const polarToCartesian = (centerX, centerY, radius, angleInDegrees) => {
  const angleInRadians = degToRad(angleInDegrees);
  return {
    x: centerX + radius * Math.cos(angleInRadians),
    y: centerY + radius * Math.sin(angleInRadians)
  };
};
const getFontOptionsFromWindow = () => {
  if (typeof window === "undefined" || !window.JLNFloatingText) {
    return [];
  }
  const {
    fontOptions
  } = window.JLNFloatingText;
  return Array.isArray(fontOptions) ? fontOptions : [];
};
function Edit({
  attributes,
  setAttributes,
  clientId,
  isSelected
}) {
  const {
    text,
    textType,
    fontFamily,
    centerX,
    centerY,
    radius,
    angle,
    fontSize,
    pathId
  } = attributes;
  const blockRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useRef)(null);
  const [isDraggingCenter, setIsDraggingCenter] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useState)(false);
  const defaultFontOptionLabel = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Theme default (inherit)", "jong-literair-nederland-blocks");
  const noFontsDetectedLabel = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("No additional theme fonts detected.", "jong-literair-nederland-blocks");
  const rawFontOptions = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useMemo)(getFontOptionsFromWindow, []);
  const fontSelectOptions = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useMemo)(() => {
    const sanitized = rawFontOptions.map(option => ({
      label: option.label || option.name || option.slug || option.value,
      value: option.value || option.fontFamily || ""
    })).filter(option => Boolean(option.value));
    const unique = sanitized.filter((option, index, array) => index === array.findIndex(candidate => candidate.value === option.value));
    return [{
      label: defaultFontOptionLabel,
      value: ""
    }, ...unique];
  }, [rawFontOptions, defaultFontOptionLabel]);
  const fontSelectDisabled = fontSelectOptions.length <= 1;
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useEffect)(() => {
    if (!pathId) {
      setAttributes({
        pathId: `jln-floating-text-${clientId}`
      });
    }
  }, [pathId, clientId, setAttributes]);
  const svgElement = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useMemo)(() => (0,_get_textpath_svg__WEBPACK_IMPORTED_MODULE_4__.getTextpathSvg)({
    text,
    centerX,
    centerY,
    radius,
    angle,
    fontSize,
    fontFamily,
    textType,
    pathId: pathId || `jln-floating-text-${clientId}`
  }), [text, textType, fontFamily, centerX, centerY, radius, angle, fontSize, pathId, clientId]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useEffect)(() => {
    if (!isDraggingCenter) {
      return undefined;
    }
    const handlePointerMove = event => {
      if (!blockRef.current) {
        return;
      }
      const rect = blockRef.current.getBoundingClientRect();
      const nextX = clamp(event.clientX - rect.left, 0, rect.width);
      const nextY = clamp(event.clientY - rect.top, 0, rect.height);
      setAttributes({
        centerX: nextX,
        centerY: nextY
      });
    };
    const handlePointerUp = () => {
      setIsDraggingCenter(false);
    };
    window.addEventListener("pointermove", handlePointerMove);
    window.addEventListener("pointerup", handlePointerUp);
    return () => {
      window.removeEventListener("pointermove", handlePointerMove);
      window.removeEventListener("pointerup", handlePointerUp);
    };
  }, [isDraggingCenter, setAttributes]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useEffect)(() => {
    if (!isSelected) {
      setIsDraggingCenter(false);
    }
  }, [isSelected]);
  const handleGuidePointerDown = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useCallback)(event => {
    if (event.button !== 0) {
      return;
    }
    event.preventDefault();
    event.stopPropagation();
    if (event.nativeEvent && typeof event.nativeEvent.stopImmediatePropagation === "function") {
      event.nativeEvent.stopImmediatePropagation();
    }
    setIsDraggingCenter(true);
  }, []);
  const guideElements = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useMemo)(() => {
    if (!isSelected || radius <= 0) {
      return null;
    }
    const lineEnd = polarToCartesian(centerX, centerY, radius, angle);
    const minX = Math.min(centerX, lineEnd.x);
    const minY = Math.min(centerY, lineEnd.y);
    const width = Math.max(Math.abs(lineEnd.x - centerX), 1);
    const height = Math.max(Math.abs(lineEnd.y - centerY), 1);
    const circleClassName = ["jln-floating-text__guide", "jln-floating-text__guide-circle", isDraggingCenter ? "jln-floating-text__guide-circle--dragging" : ""].filter(Boolean).join(" ");
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.Fragment, {
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("svg", {
        className: circleClassName,
        width: GUIDE_CENTER_DIAMETER,
        height: GUIDE_CENTER_DIAMETER,
        viewBox: `0 0 ${GUIDE_CENTER_DIAMETER} ${GUIDE_CENTER_DIAMETER}`,
        style: {
          left: `${centerX - GUIDE_CENTER_DIAMETER / 2}px`,
          top: `${centerY - GUIDE_CENTER_DIAMETER / 2}px`,
          pointerEvents: "auto"
        },
        onPointerDownCapture: handleGuidePointerDown,
        onPointerDown: handleGuidePointerDown,
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("circle", {
          cx: GUIDE_CENTER_DIAMETER / 2,
          cy: GUIDE_CENTER_DIAMETER / 2,
          r: GUIDE_CENTER_DIAMETER / 2 - 1
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("svg", {
        className: "jln-floating-text__guide jln-floating-text__guide-line",
        width: width,
        height: height,
        viewBox: `0 0 ${width} ${height}`,
        style: {
          left: `${minX}px`,
          top: `${minY}px`
        },
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("line", {
          x1: centerX - minX,
          y1: centerY - minY,
          x2: lineEnd.x - minX,
          y2: lineEnd.y - minY
        })
      })]
    });
  }, [isSelected, centerX, centerY, radius, angle, isDraggingCenter, handleGuidePointerDown]);
  const handleNumberAttr = (key, fallback = 0) => value => {
    const parsed = parseFloat(value);
    setAttributes({
      [key]: Number.isNaN(parsed) ? fallback : parsed
    });
  };
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps)({
    className: "jln-floating-text"
  });
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Floating Text Settings", "jong-literair-nederland-blocks"),
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Text type", "jong-literair-nederland-blocks"),
          value: textType,
          options: [{
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Curved", "jong-literair-nederland-blocks"),
            value: "curved"
          }, {
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Line", "jong-literair-nederland-blocks"),
            value: "line"
          }],
          onChange: value => setAttributes({
            textType: value
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Text", "jong-literair-nederland-blocks"),
          value: text,
          onChange: value => setAttributes({
            text: value
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Font family", "jong-literair-nederland-blocks"),
          value: fontFamily,
          options: fontSelectOptions,
          onChange: value => setAttributes({
            fontFamily: value
          }),
          disabled: fontSelectDisabled,
          help: fontSelectDisabled ? noFontsDetectedLabel : undefined
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Center X", "jong-literair-nederland-blocks"),
          type: "number",
          value: centerX,
          onChange: handleNumberAttr("centerX")
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Center Y", "jong-literair-nederland-blocks"),
          type: "number",
          value: centerY,
          onChange: handleNumberAttr("centerY")
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Radius", "jong-literair-nederland-blocks"),
          type: "number",
          min: 1,
          value: radius,
          onChange: handleNumberAttr("radius", 1)
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.RangeControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Angle", "jong-literair-nederland-blocks"),
          value: angle,
          onChange: value => setAttributes({
            angle: value ?? 0
          }),
          min: -180,
          max: 180,
          allowReset: true
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Font size (px)", "jong-literair-nederland-blocks"),
          type: "number",
          min: 8,
          value: fontSize,
          onChange: handleNumberAttr("fontSize", 8)
        })]
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
      ...blockProps,
      ref: blockRef,
      children: [isSelected && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
        className: "jln-floating-text__controls",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          className: "jln-floating-text__input",
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Text", "jong-literair-nederland-blocks"),
          value: text,
          onChange: value => setAttributes({
            text: value
          }),
          help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("This value is also configurable from the block inspector.", "jong-literair-nederland-blocks")
        })
      }), svgElement, guideElements, /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
        className: "jln-floating-text__content",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InnerBlocks, {
          template: DEFAULT_TEMPLATE,
          templateLock: false
        })
      })]
    })]
  });
}

/***/ },

/***/ "./blocks/jln-floating-text/src/get_textpath_svg.js"
/*!**********************************************************!*\
  !*** ./blocks/jln-floating-text/src/get_textpath_svg.js ***!
  \**********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getTextpathSvg: () => (/* binding */ getTextpathSvg)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

const ARC_SWEEP_DEGREES = 180;
const PADDING_MULTIPLIER = 2;
const degToRad = degrees => degrees * Math.PI / 180;
const polarToCartesian = (centerX, centerY, radius, angleInDegrees) => {
  const angleInRadians = degToRad(angleInDegrees);
  return {
    x: centerX + radius * Math.cos(angleInRadians),
    y: centerY + radius * Math.sin(angleInRadians)
  };
};
const sanitizeId = value => value.replace(/[^a-z0-9_:\-.]/gi, "-");
const getTextpathSvg = ({
  text,
  textType: _textType = "curved",
  fontFamily = "",
  centerX,
  centerY,
  radius,
  angle,
  fontSize,
  pathId
}) => {
  if (!text || radius <= 0) {
    return null;
  }
  const variant = _textType === "line" ? "line" : "curved";
  const usableFontSize = fontSize > 0 ? fontSize : 1;
  let pathDefinition = "";
  let horizontalSpan = [];
  let verticalSpan = [];
  if (variant === "line") {
    const baselineMidpoint = polarToCartesian(centerX, centerY, radius, angle);
    const halfLength = radius;
    const perpStart = polarToCartesian(baselineMidpoint.x, baselineMidpoint.y, halfLength, angle - 90);
    const perpEnd = polarToCartesian(baselineMidpoint.x, baselineMidpoint.y, halfLength, angle + 90);
    pathDefinition = `M ${perpStart.x} ${perpStart.y} L ${perpEnd.x} ${perpEnd.y}`;
    horizontalSpan = [perpStart.x, perpEnd.x, baselineMidpoint.x];
    verticalSpan = [perpStart.y, perpEnd.y, baselineMidpoint.y];
  } else {
    const sweepHalf = ARC_SWEEP_DEGREES / 2;
    const startAngle = angle - sweepHalf;
    const endAngle = angle + sweepHalf;
    const arcStart = polarToCartesian(centerX, centerY, radius, startAngle);
    const arcEnd = polarToCartesian(centerX, centerY, radius, endAngle);
    const arcFlag = ARC_SWEEP_DEGREES > 180 ? 1 : 0;
    pathDefinition = `M ${arcStart.x} ${arcStart.y} A ${radius} ${radius} 0 ${arcFlag} 1 ${arcEnd.x} ${arcEnd.y}`;
    horizontalSpan = [arcStart.x, arcEnd.x, centerX - radius, centerX + radius];
    verticalSpan = [arcStart.y, arcEnd.y, centerY - radius, centerY + radius];
  }
  const padding = usableFontSize * PADDING_MULTIPLIER;
  const minX = 0;
  const maxX = Math.max(...horizontalSpan) + padding;
  const minY = 0;
  const maxY = Math.max(...verticalSpan) + padding;
  const viewBoxWidth = Math.max(maxX - minX, 1);
  const viewBoxHeight = Math.max(maxY - minY, 1);
  const safePathId = sanitizeId(pathId || "jln-floating-text");
  const textStyle = {
    fontSize: usableFontSize
  };
  if (fontFamily) {
    textStyle.fontFamily = fontFamily;
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    className: "jln-floating-text__svg",
    viewBox: `${minX} ${minY} ${viewBoxWidth} ${viewBoxHeight}`,
    width: viewBoxWidth,
    height: viewBoxHeight,
    xmlns: "http://www.w3.org/2000/svg",
    role: "img",
    "aria-label": text,
    style: {
      left: `${0}px`,
      top: `${0}px`
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    id: `${safePathId}-path`,
    d: pathDefinition,
    fill: "none"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("text", {
    className: "jln-floating-text__text",
    style: textStyle
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("textPath", {
    href: `#${safePathId}-path`,
    xlinkHref: `#${safePathId}-path`,
    startOffset: "50%",
    textAnchor: "middle"
  }, text)));
};

/***/ },

/***/ "./blocks/jln-floating-text/src/index.js"
/*!***********************************************!*\
  !*** ./blocks/jln-floating-text/src/index.js ***!
  \***********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./style.scss */ "./blocks/jln-floating-text/src/style.scss");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./blocks/jln-floating-text/src/edit.js");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./save */ "./blocks/jln-floating-text/src/save.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./block.json */ "./blocks/jln-floating-text/src/block.json");





(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_4__.name, {
  edit: _edit__WEBPACK_IMPORTED_MODULE_2__["default"],
  save: _save__WEBPACK_IMPORTED_MODULE_3__["default"]
});

/***/ },

/***/ "./blocks/jln-floating-text/src/save.js"
/*!**********************************************!*\
  !*** ./blocks/jln-floating-text/src/save.js ***!
  \**********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ save)
/* harmony export */ });
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _get_textpath_svg__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./get_textpath_svg */ "./blocks/jln-floating-text/src/get_textpath_svg.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__);



function save({
  attributes
}) {
  const {
    text,
    textType,
    fontFamily,
    centerX,
    centerY,
    radius,
    angle,
    fontSize,
    pathId
  } = attributes;
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
    ..._wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.useBlockProps.save(),
    children: [(0,_get_textpath_svg__WEBPACK_IMPORTED_MODULE_1__.getTextpathSvg)({
      text,
      textType,
      fontFamily,
      centerX,
      centerY,
      radius,
      angle,
      fontSize,
      pathId
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
      className: "jln-floating-text__content",
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.InnerBlocks.Content, {})
    })]
  });
}

/***/ },

/***/ "./blocks/jln-floating-text/src/editor.scss"
/*!**************************************************!*\
  !*** ./blocks/jln-floating-text/src/editor.scss ***!
  \**************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "./blocks/jln-floating-text/src/style.scss"
/*!*************************************************!*\
  !*** ./blocks/jln-floating-text/src/style.scss ***!
  \*************************************************/
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

/***/ "@wordpress/element"
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
(module) {

module.exports = window["wp"]["element"];

/***/ },

/***/ "@wordpress/i18n"
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["i18n"];

/***/ },

/***/ "./blocks/jln-floating-text/src/block.json"
/*!*************************************************!*\
  !*** ./blocks/jln-floating-text/src/block.json ***!
  \*************************************************/
(module) {

module.exports = /*#__PURE__*/JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"jln/jln-floating-text","version":"0.1.0","title":"JLN Floating Text","category":"literair-nederland","icon":"smiley","description":"Floating headline/text block for Jong Literair Nederland.","keywords":["curve","text","jong"],"attributes":{"text":{"type":"string","default":"Floating text"},"fontFamily":{"type":"string","default":""},"textType":{"type":"string","default":"curved"},"centerX":{"type":"number","default":200},"centerY":{"type":"number","default":200},"radius":{"type":"number","default":160},"angle":{"type":"number","default":0},"fontSize":{"type":"number","default":28},"pathId":{"type":"string","default":""}},"supports":{"html":false},"textdomain":"jong-literair-nederland-blocks","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css","viewScript":"file:./view.js"}');

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
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["./style-index"], () => (__webpack_require__("./blocks/jln-floating-text/src/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map
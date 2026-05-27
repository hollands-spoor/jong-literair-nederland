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
  placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Add accompanying text...", "jjln-blocks")
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
  const defaultFontOptionLabel = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Theme default (inherit)", "jjln-blocks");
  const noFontsDetectedLabel = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("No additional theme fonts detected.", "jjln-blocks");
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
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Floating Text Settings", "jjln-blocks"),
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Text type", "jjln-blocks"),
          value: textType,
          options: [{
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Curved", "jjln-blocks"),
            value: "curved"
          }, {
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Line", "jjln-blocks"),
            value: "line"
          }],
          onChange: value => setAttributes({
            textType: value
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Text", "jjln-blocks"),
          value: text,
          onChange: value => setAttributes({
            text: value
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Font family", "jjln-blocks"),
          value: fontFamily,
          options: fontSelectOptions,
          onChange: value => setAttributes({
            fontFamily: value
          }),
          disabled: fontSelectDisabled,
          help: fontSelectDisabled ? noFontsDetectedLabel : undefined
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Center X", "jjln-blocks"),
          type: "number",
          value: centerX,
          onChange: handleNumberAttr("centerX")
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Center Y", "jjln-blocks"),
          type: "number",
          value: centerY,
          onChange: handleNumberAttr("centerY")
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Radius", "jjln-blocks"),
          type: "number",
          min: 1,
          value: radius,
          onChange: handleNumberAttr("radius", 1)
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.RangeControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Angle", "jjln-blocks"),
          value: angle,
          onChange: value => setAttributes({
            angle: value ?? 0
          }),
          min: -180,
          max: 180,
          allowReset: true
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Font size (px)", "jjln-blocks"),
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
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Text", "jjln-blocks"),
          value: text,
          onChange: value => setAttributes({
            text: value
          }),
          help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("This value is also configurable from the block inspector.", "jjln-blocks")
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
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__);






(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_4__.name, {
  icon: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)("svg", {
    viewBox: "10 5 105 105",
    xmlns: "http://www.w3.org/2000/svg",
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsxs)("g", {
      fill: "#000000",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)("path", {
        d: "M48.76 16.48c1.02-.18 2.25-.017 2.98.89.4.5.64 1.23.58 2.33-.06 1.07-.09 3.76.21 5.6.37 2.23 1.18 4.37 1.95 6.49.6 1.66 1.03 4.06 2.72 4.42 1.16.24 2.34-1.18 2.84-1.82.87-1.12 1.92-2.53 2.8-3.65.27-.35.73-.91 1.14-.72.48.22.33 1.05.25 1.57-.24 1.61-.63 3.006-.68 4.715-.024.85.088 1.968.807 2.421.676.426 1.64.008 2.38-.297 1.688-.697 3.033-2.072 4.332-3.356 1.49-1.474 2.543-3.336 3.908-4.927.691-.806 1.242-1.813 2.166-2.337.655-.37 1.507-.737 2.21-.467.538.207 1.023.824 1.019 1.402-.006.779-.757 1.392-1.402 1.869-1.06.784-1.653 1.102-3.059 2.506-1.405 1.405-3.93 3.753-5.054 6.16-.452.966-.978 2.148-.595 3.143.185.48.723.85 1.232.934.883.146 1.702-.558 2.548-.85.64-.22 1.237-.715 1.912-.679.392.021.86.174 1.062.51.19.315.168.802-.043 1.104-.657.943-2.48.41-3.185 1.317-.369.475-.474 1.225-.255 1.784.286.731 1.081 1.24 1.826 1.487 2.153.713 4.542-.058 6.797-.297 1.942-.206 4.178-.621 5.777-.977 1.598-.357 2.888-.823 3.823-1.062.934-.24 1.67-.534 2.463-.298.443.132 1.019.432 1.062.892.047.491-.524.874-.934 1.147-.7.468-1.598.545-2.421.722-1.23.266-2.497.313-3.738.51-2.658.423-5.354.711-7.944 1.444-1.199.34-2.496.599-3.483 1.36-.819.63-1.855 1.473-1.827 2.506.017.603.626 1.098 1.147 1.402 1.515.882 3.434.772 5.183.892 1.682.115 3.375-.023 5.055-.17 1.196-.106 2.366-.488 3.568-.51.987-.018 1.844.336 1.911 1.062.07.75-1.024 1.212-1.741 1.444-2.215.716-4.641-.382-6.967-.467-2.207-.081-4.52-.708-6.626-.043-.746.236-1.805.585-1.912 1.36-.166 1.207 1.368 2.054 2.294 2.846 1.788 1.53 4.021 2.454 6.117 3.525 2.46 1.258 4.996 2.372 7.56 3.399 3.555 1.423 7.011 3.012 10.833 3.823 1.117.237 2.462.3 3.228 1.147.352.389.622 1.064.377 1.529-.281.534-1.094.577-1.694.637-.885.089-1.785-.158-2.633-.425-1.8-.567-3.39-1.662-5.098-2.464-3.028-1.421-6.028-2.915-9.133-4.162-3.423-1.376-6.865-2.777-10.45-3.653-1.347-.33-2.768-.91-4.12-.595-.317.074-.65.266-.807.552-.257.473-.212 1.104-.042 1.614.303.909 1.058 1.627 1.784 2.252 1.039.892 2.578 1.091 3.568 2.038.46.44.943.983 1.02 1.615.048.4-.099.86-.383 1.147-.463.467-1.218.739-1.869.637-.89-.14-1.629-.892-2.166-1.614-.86-1.157-1.485-2.404-2.506-3.356-.862-.804-1.797-1.855-2.974-1.912-.588-.028-1.186.362-1.571.807-.43.498-.665 1.212-.638 1.87.085 2.052 1.554 3.807 2.464 5.649.918 1.858 1.903 3.689 3.016 5.437.92 1.446 2.456 3.146 3.016 4.163.633 1.15.971 2.025-.042 2.973-.438.356-1.051.537-1.615.51-.722-.035-1.478-.344-1.996-.85-1.883-1.835-2.088-4.83-3.27-7.178-1.158-2.298-2.09-4.779-3.739-6.754-.832-.997-1.75-2.178-3.016-2.464-.867-.196-1.889.092-2.591.637-.925.72-1.31 1.987-1.657 3.101-.188.606-.273 1.341-.764 1.742-.314.256-.826.433-1.19.255-.705-.346-.814-1.35-1.02-2.124-.213-.806-.39-1.67-.891-2.337-.128-.17-.298-.391-.51-.382-1.312.058-2.137 1.588-2.93 2.634-1.911 2.517-2.914 5.383-3.951 8.368-.76 2.188-.877 4.759-1.7 6.924-.295.776-.601 1.625-1.231 2.166-.69.593-1.647 1.097-2.549.977-.643-.085-1.302-.557-1.572-1.147-.29-.633-.037-1.416.17-2.081.481-1.544 1.72-2.748 2.422-4.205.738-1.536 1.337-3.14 1.869-4.758.686-2.088 1.317-4.207 1.699-6.372.251-1.428.563-2.894.382-4.332-.07-.551-.021-1.409-.552-1.572-.387-.119-.842.34-.977.722-.178.501-.204 1.373-.637 1.87-.48.548-1.227 1.19-1.954 1.216-.597.023-1.379-.207-1.837-.59-1.322-1.336.394-2.206.987-3.345.248-.58.701-1.385.297-1.87-.734-.881-2.313-.296-3.44-.084-2.745.514-7.731 3.228-7.731 3.228s-5.464 2.782-8.156 4.248c-1.259.685-2.36 1.73-3.738 2.124-.709.201-1.562.437-2.209.084-.39-.212-.769-.711-.68-1.146.145-.705 1.054-1.064 1.742-1.275a46.6 46.6 0 006.754-2.676 51 51 0 007.476-4.503c1.702-1.242 4.055-2.176 4.758-4.162.22-.624.047-1.419-.34-1.954-.405-.56-1.154-.823-1.827-.977-1.367-.314-2.743-.1-4.205.17-2.881.53-5.832 1.253-8.496 2.25-.704.265-1.556.46-2.25.17-.483-.2-1.084-.67-1.02-1.189.08-.655.971-.995 1.614-1.146 2.86-.677 6.013-.725 8.92-1.53 1.244-.344 2.705-.481 3.61-1.401.488-.495.903-1.284.723-1.954-.253-.942-1.313-1.603-2.251-1.87-1.235-.35-2.317-.318-3.739-.594-.314-.061-.742-.366-.68-.68.067-.33.6-.363.935-.382 1.985-.116 3.862-.046 5.65-.68 1.103-.39 2.762-.717 2.973-1.869.209-1.133-.734-2.207-2.081-2.76-.709-.292-1.664-.394-2.039-1.063-.201-.359-.195-.93.085-1.232.535-.577 1.61-.644 2.336-.34.81.34 1.568.801 2.421.85.769.044 1.82.127 2.252-.51.611-.901-.128-2.208-.51-3.228-.58-1.549-1.346-2.706-2.634-4.205s-5.224-4.503-5.224-4.503-2.389-1.416-3.186-2.506c-.357-.488-.753-1.105-.637-1.7.131-.678.755-1.282 1.401-1.528.785-.3 1.774-.155 2.507.254 1.525.853 2.084 2.807 3.185 4.163.93 1.146 1.789 2.372 2.889 3.356 2.058 1.84 4.23 3.771 6.839 4.673 1.22.421 2.612.567 3.865.254 1.697-.422 3.494-1.303 4.46-2.76 1.07-1.613 1.003-3.804.893-5.735-.116-2.024-.978-3.938-1.615-5.862-.639-1.932-2.047-3.663-2.208-5.692-.049-.606.042-1.28.382-1.784.439-.65.815-.929 1.572-1.062z"
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)("path", {
        d: "M19.47 42.03c.752-.294 1.969-.48 2.83-.317.557.106 1.133.42 1.445.892.364.55.516 1.333.297 1.954-.325.927-1.257 1.624-2.166 1.997-.786.322-1.748.37-2.549.085-.708-.253-1.429-.784-1.699-1.487-.214-.56-.106-1.277.213-1.784.393-.625.876-1.045 1.628-1.34m40.305 44.891c.637-.763 2.307-1.106 2.796-1.03.766.117 1.587.531 1.996 1.189.376.603.4 1.451.17 2.124-.265.778-.949 1.407-1.656 1.826-.577.341-1.3.61-1.954.467-.784-.171-1.606-.737-1.893-1.486-.374-.977-.129-2.287.541-3.09m50.966-11.905c.51-.248.922-.362 1.657-.382.685-.02 1.446.26 1.911.764.422.458.639 1.168.552 1.784-.122.871-.67 1.79-1.444 2.21-.81.437-1.936.407-2.761 0-.752-.372-1.35-1.178-1.53-1.997-.096-.44.06-.935.298-1.317.298-.479.81-.815 1.317-1.062"
      })]
    })
  }),
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

module.exports = /*#__PURE__*/JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"jln/jln-floating-text","version":"0.1.0","title":"JLN Floating Text","category":"literair-nederland","icon":"smiley","description":"Floating headline/text block for Jong Literair Nederland.","keywords":["curve","text","jong"],"attributes":{"text":{"type":"string","default":"Floating text"},"fontFamily":{"type":"string","default":""},"textType":{"type":"string","default":"curved"},"centerX":{"type":"number","default":200},"centerY":{"type":"number","default":200},"radius":{"type":"number","default":160},"angle":{"type":"number","default":0},"fontSize":{"type":"number","default":28},"pathId":{"type":"string","default":""}},"supports":{"html":false},"textdomain":"jjln-blocks","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css","viewScript":"file:./view.js"}');

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
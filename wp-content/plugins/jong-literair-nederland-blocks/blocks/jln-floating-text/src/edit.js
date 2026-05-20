import { __ } from "@wordpress/i18n";
import { InspectorControls, InnerBlocks, useBlockProps } from "@wordpress/block-editor";
import { PanelBody, TextControl, RangeControl, SelectControl } from "@wordpress/components";
import { useCallback, useEffect, useMemo, useRef, useState } from "@wordpress/element";

import { getTextpathSvg } from "./get_textpath_svg";
import "./editor.scss";

const DEFAULT_TEMPLATE = [
    ["core/paragraph", { placeholder: __("Add accompanying text...", "jjln-blocks") }],
];

const GUIDE_CENTER_DIAMETER = 12;

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

const degToRad = (degrees) => (degrees * Math.PI) / 180;

const polarToCartesian = (centerX, centerY, radius, angleInDegrees) => {
    const angleInRadians = degToRad(angleInDegrees);
    return {
        x: centerX + radius * Math.cos(angleInRadians),
        y: centerY + radius * Math.sin(angleInRadians),
    };
};

const getFontOptionsFromWindow = () => {
    if (typeof window === "undefined" || !window.JLNFloatingText) {
        return [];
    }

    const { fontOptions } = window.JLNFloatingText;
    return Array.isArray(fontOptions) ? fontOptions : [];
};

export default function Edit({ attributes, setAttributes, clientId, isSelected }) {
    const { text, textType, fontFamily, centerX, centerY, radius, angle, fontSize, pathId } = attributes;
    const blockRef = useRef(null);
    const [isDraggingCenter, setIsDraggingCenter] = useState(false);
    const defaultFontOptionLabel = __("Theme default (inherit)", "jjln-blocks");
    const noFontsDetectedLabel = __("No additional theme fonts detected.", "jjln-blocks");
    const rawFontOptions = useMemo(getFontOptionsFromWindow, []);
    const fontSelectOptions = useMemo(() => {
        const sanitized = rawFontOptions
            .map((option) => ({
                label: option.label || option.name || option.slug || option.value,
                value: option.value || option.fontFamily || "",
            }))
            .filter((option) => Boolean(option.value));

        const unique = sanitized.filter(
            (option, index, array) => index === array.findIndex((candidate) => candidate.value === option.value)
        );

        return [
            { label: defaultFontOptionLabel, value: "" },
            ...unique,
        ];
    }, [rawFontOptions, defaultFontOptionLabel]);
    const fontSelectDisabled = fontSelectOptions.length <= 1;

    useEffect(() => {
        if (!pathId) {
            setAttributes({ pathId: `jln-floating-text-${clientId}` });
        }
    }, [pathId, clientId, setAttributes]);

    const svgElement = useMemo(
        () =>
            getTextpathSvg({
                text,
                centerX,
                centerY,
                radius,
                angle,
                fontSize,
                fontFamily,
                textType,
                pathId: pathId || `jln-floating-text-${clientId}`,
            }),
        [text, textType, fontFamily, centerX, centerY, radius, angle, fontSize, pathId, clientId]
    );

    useEffect(() => {
        if (!isDraggingCenter) {
            return undefined;
        }

        const handlePointerMove = (event) => {
            if (!blockRef.current) {
                return;
            }

            const rect = blockRef.current.getBoundingClientRect();
            const nextX = clamp(event.clientX - rect.left, 0, rect.width);
            const nextY = clamp(event.clientY - rect.top, 0, rect.height);

            setAttributes({ centerX: nextX, centerY: nextY });
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

    useEffect(() => {
        if (!isSelected) {
            setIsDraggingCenter(false);
        }
    }, [isSelected]);

    const handleGuidePointerDown = useCallback(
        (event) => {
            if (event.button !== 0) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            if (event.nativeEvent && typeof event.nativeEvent.stopImmediatePropagation === "function") {
                event.nativeEvent.stopImmediatePropagation();
            }
            setIsDraggingCenter(true);
        },
        []
    );

    const guideElements = useMemo(() => {
        if (!isSelected || radius <= 0) {
            return null;
        }

        const lineEnd = polarToCartesian(centerX, centerY, radius, angle);
        const minX = Math.min(centerX, lineEnd.x);
        const minY = Math.min(centerY, lineEnd.y);
        const width = Math.max(Math.abs(lineEnd.x - centerX), 1);
        const height = Math.max(Math.abs(lineEnd.y - centerY), 1);

        const circleClassName = [
            "jln-floating-text__guide",
            "jln-floating-text__guide-circle",
            isDraggingCenter ? "jln-floating-text__guide-circle--dragging" : "",
        ]
            .filter(Boolean)
            .join(" ");

        return (
            <>
                <svg
                    className={circleClassName}
                    width={GUIDE_CENTER_DIAMETER}
                    height={GUIDE_CENTER_DIAMETER}
                    viewBox={`0 0 ${GUIDE_CENTER_DIAMETER} ${GUIDE_CENTER_DIAMETER}`}
                    style={{
                        left: `${centerX - GUIDE_CENTER_DIAMETER / 2}px`,
                        top: `${centerY - GUIDE_CENTER_DIAMETER / 2}px`,
                        pointerEvents: "auto",
                    }}
                    onPointerDownCapture={handleGuidePointerDown}
                    onPointerDown={handleGuidePointerDown}
                >
                    <circle
                        cx={GUIDE_CENTER_DIAMETER / 2}
                        cy={GUIDE_CENTER_DIAMETER / 2}
                        r={(GUIDE_CENTER_DIAMETER / 2) - 1}
                    />
                </svg>
                <svg
                    className="jln-floating-text__guide jln-floating-text__guide-line"
                    width={width}
                    height={height}
                    viewBox={`0 0 ${width} ${height}`}
                    style={{
                        left: `${minX}px`,
                        top: `${minY}px`,
                    }}
                >
                    <line
                        x1={centerX - minX}
                        y1={centerY - minY}
                        x2={lineEnd.x - minX}
                        y2={lineEnd.y - minY}
                    />
                </svg>
            </>
        );
    }, [isSelected, centerX, centerY, radius, angle, isDraggingCenter, handleGuidePointerDown]);

    const handleNumberAttr = (key, fallback = 0) => (value) => {
        const parsed = parseFloat(value);
        setAttributes({ [key]: Number.isNaN(parsed) ? fallback : parsed });
    };

    const blockProps = useBlockProps({ className: "jln-floating-text" });

    return (
        <>
            <InspectorControls>
                <PanelBody title={__("Floating Text Settings", "jjln-blocks")}>
                    <SelectControl
                        label={__("Text type", "jjln-blocks")}
                        value={textType}
                        options={[
                            { label: __("Curved", "jjln-blocks"), value: "curved" },
                            { label: __("Line", "jjln-blocks"), value: "line" },
                        ]}
                        onChange={(value) => setAttributes({ textType: value })}
                    />
                    <TextControl
                        label={__("Text", "jjln-blocks")}
                        value={text}
                        onChange={(value) => setAttributes({ text: value })}
                    />
                    <SelectControl
                        label={__("Font family", "jjln-blocks")}
                        value={fontFamily}
                        options={fontSelectOptions}
                        onChange={(value) => setAttributes({ fontFamily: value })}
                        disabled={fontSelectDisabled}
                        help={fontSelectDisabled ? noFontsDetectedLabel : undefined}
                    />
                    <TextControl
                        label={__("Center X", "jjln-blocks")}
                        type="number"
                        value={centerX}
                        onChange={handleNumberAttr("centerX")}
                    />
                    <TextControl
                        label={__("Center Y", "jjln-blocks")}
                        type="number"
                        value={centerY}
                        onChange={handleNumberAttr("centerY")}
                    />
                    <TextControl
                        label={__("Radius", "jjln-blocks")}
                        type="number"
                        min={1}
                        value={radius}
                        onChange={handleNumberAttr("radius", 1)}
                    />
                    <RangeControl
                        label={__("Angle", "jjln-blocks")}
                        value={angle}
                        onChange={(value) => setAttributes({ angle: value ?? 0 })}
                        min={-180}
                        max={180}
                        allowReset
                    />
                    <TextControl
                        label={__("Font size (px)", "jjln-blocks")}
                        type="number"
                        min={8}
                        value={fontSize}
                        onChange={handleNumberAttr("fontSize", 8)}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...blockProps} ref={blockRef}>
                {isSelected && (
                    <div className="jln-floating-text__controls">
                        <TextControl
                            className="jln-floating-text__input"
                            label={__("Text", "jjln-blocks")}
                            value={text}
                            onChange={(value) => setAttributes({ text: value })}
                            help={__("This value is also configurable from the block inspector.", "jjln-blocks")}
                        />
                    </div>
                )}
                {svgElement}
                {guideElements}
                <div className="jln-floating-text__content">
                    <InnerBlocks template={DEFAULT_TEMPLATE} templateLock={false} />
                </div>
            </div>
        </>
    );
}

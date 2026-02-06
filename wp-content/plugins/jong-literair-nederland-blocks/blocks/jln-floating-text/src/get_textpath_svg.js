import { createElement } from "@wordpress/element";

const ARC_SWEEP_DEGREES = 180;
const PADDING_MULTIPLIER = 2;

const degToRad = (degrees) => (degrees * Math.PI) / 180;

const polarToCartesian = (centerX, centerY, radius, angleInDegrees) => {
    const angleInRadians = degToRad(angleInDegrees);
    return {
        x: centerX + radius * Math.cos(angleInRadians),
        y: centerY + radius * Math.sin(angleInRadians),
    };
};

const sanitizeId = (value) => value.replace(/[^a-z0-9_:\-.]/gi, "-");

export const getTextpathSvg = ({
    text,
    textType: _textType = "curved",
    fontFamily = "",
    centerX,
    centerY,
    radius,
    angle,
    fontSize,
    pathId,
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

    const textStyle = { fontSize: usableFontSize };
    if (fontFamily) {
        textStyle.fontFamily = fontFamily;
    }

    return createElement(
        "svg",
        {
            className: "jln-floating-text__svg",
            viewBox: `${minX} ${minY} ${viewBoxWidth} ${viewBoxHeight}`,
            width: viewBoxWidth,
            height: viewBoxHeight,
            xmlns: "http://www.w3.org/2000/svg",
            role: "img",
            "aria-label": text,
            style: {
                left: `${0}px`,
                top: `${0}px`,
            },
        },
        createElement("path", {
            id: `${safePathId}-path`,
            d: pathDefinition,
            fill: "none",
        }),
        createElement(
            "text",
            {
                className: "jln-floating-text__text",
                style: textStyle,
            },
            createElement("textPath", {
                href: `#${safePathId}-path`,
                xlinkHref: `#${safePathId}-path`,
                startOffset: "50%",
                textAnchor: "middle",
            }, text)
        )
    );
};

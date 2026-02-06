import { InnerBlocks, useBlockProps } from "@wordpress/block-editor";

import { getTextpathSvg } from "./get_textpath_svg";

export default function save({ attributes }) {
    const { text, textType, fontFamily, centerX, centerY, radius, angle, fontSize, pathId } = attributes;

    return (
        <div {...useBlockProps.save()}>
            {getTextpathSvg({ text, textType, fontFamily, centerX, centerY, radius, angle, fontSize, pathId })}
            <div className="jln-floating-text__content">
                <InnerBlocks.Content />
            </div>
        </div>
    );
}

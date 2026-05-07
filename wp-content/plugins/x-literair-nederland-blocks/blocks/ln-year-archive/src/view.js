import { __ } from '@wordpress/i18n';

const HOVER_MEDIA_QUERY = '(hover: hover) and (pointer: fine)';
const TOOLTIP_OFFSET_EM = 4;

const LABEL_AUTHOR = __( 'Author', 'x-literair-nederland-blocks' );
const LABEL_TITLE = __( 'Title', 'x-literair-nederland-blocks' );
const LABEL_BY = __( 'By', 'x-literair-nederland-blocks' );
const LABEL_DATE = __( 'Date', 'x-literair-nederland-blocks' );

const supportsHover = () => window.matchMedia(HOVER_MEDIA_QUERY).matches;

const getField = (value, label) => {
	if (!value) {
		return '';
	}

	return `${label}: ${value}`;
};

const mountArchiveTooltip = (blockElement) => {
	if (!supportsHover()) {
		return;
	}

	if (blockElement.dataset.mode !== 'image') {
		return;
	}

	const tooltip = blockElement.querySelector('#archive-tool-tip');
	if (!tooltip) {
		return;
	}

	const titleElement = tooltip.querySelector('[data-ln-tooltip-title]');
	const authorElement = tooltip.querySelector('[data-ln-tooltip-author]');
	const bookTitleElement = tooltip.querySelector('[data-ln-tooltip-book-title]');
	const reviewerElement = tooltip.querySelector('[data-ln-tooltip-reviewer]');
	const dateElement = tooltip.querySelector('[data-ln-tooltip-date]');
	const links = blockElement.querySelectorAll('.ln-year-archive__card-link');

	if (!links.length || !titleElement || !authorElement || !bookTitleElement || !reviewerElement || !dateElement) {
		return;
	}

	let activeLink = null;
	let hideTimer = 0;

	const clearTimer = () => {
		if (!hideTimer) {
			return;
		}

		window.clearTimeout(hideTimer);
		hideTimer = 0;
	};

	const updateTooltipContent = (link) => {
		titleElement.textContent = link.dataset.lnPostTitle || '';
		authorElement.textContent = getField(link.dataset.lnBookAuthor || '', LABEL_AUTHOR);
		bookTitleElement.textContent = getField(link.dataset.lnBookTitle || '', LABEL_TITLE);
		reviewerElement.textContent = getField(link.dataset.lnReviewer || '', LABEL_BY);
		dateElement.textContent = getField(link.dataset.lnPostDate || '', LABEL_DATE);
	};

	const activate = (link) => {
		const image = link.querySelector('.ln-year-archive__cover');
		if (!image) {
			return;
		}

		clearTimer();

		if (activeLink && activeLink !== link) {
			activeLink.classList.remove('is-hovered');
		}

		const imageRect = image.getBoundingClientRect();
		const rootFontSize = Number.parseFloat(window.getComputedStyle(document.documentElement).fontSize) || 16;
		const tooltipOffset = TOOLTIP_OFFSET_EM * rootFontSize;
		const tooltipWidth = Number.parseFloat(window.getComputedStyle(tooltip).width) || tooltip.offsetWidth;

		const imageCenterX = imageRect.left + imageRect.width / 2;
		const viewportMidX = window.innerWidth / 2;
		const isOnRightSide = imageCenterX >= viewportMidX;

		const hoverScaleRaw = window.getComputedStyle(link).getPropertyValue('--xln-hover-scale').trim();
		const hoverScale = Number.parseFloat(hoverScaleRaw) || 1;


		const extraWidth = imageRect.width * (hoverScale - 1);
		const scaledLeft = imageRect.left - extraWidth / 2;
		const scaledRight = imageRect.right + extraWidth / 2;
	
		const tooltipLeft = isOnRightSide 
			? scaledLeft - tooltipWidth - tooltipOffset
  			: scaledRight + tooltipOffset;

		link.classList.add('is-hovered');

		tooltip.style.left = `${tooltipLeft}px`;
		tooltip.style.top = `${imageRect.top}px`;
		tooltip.setAttribute('aria-hidden', 'false');
		tooltip.classList.add('is-visible');
		tooltip.classList.remove('is-expanded');

		updateTooltipContent(link);
		activeLink = link;

		window.requestAnimationFrame(() => {
			if (activeLink !== link) {
				return;
			}

			tooltip.classList.add('is-expanded');
		});
	};

	const deactivate = (link) => {
		if (activeLink !== link) {
			return;
		}

		activeLink.classList.remove('is-hovered');
		activeLink = null;
		tooltip.classList.remove('is-expanded');
		tooltip.setAttribute('aria-hidden', 'true');

		clearTimer();
		hideTimer = window.setTimeout(() => {
			if (activeLink) {
				return;
			}

			tooltip.classList.remove('is-visible');
		}, 260);
	};

	links.forEach((link) => {
		link.addEventListener('mouseenter', () => activate(link));
		link.addEventListener('mouseleave', () => deactivate(link));
	});

	window.addEventListener('resize', () => {
		if (!activeLink) {
			return;
		}

		deactivate(activeLink);
	});

	blockElement.addEventListener('mouseleave', () => {
		if (!activeLink) {
			return;
		}

		deactivate(activeLink);
	});
};

document.querySelectorAll('.ln-year-archive').forEach((blockElement) => {
	mountArchiveTooltip(blockElement);
});

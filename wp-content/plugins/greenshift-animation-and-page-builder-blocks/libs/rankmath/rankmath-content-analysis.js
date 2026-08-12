/**
 * GreenShift <-> Rank Math content analysis integration.
 *
 * GreenShift's Heading block and the Advanced HTML Element block (when used as an
 * h1-h6) often render their text through typing/word animations, a highlight
 * wrapper, split-text spans or dynamic data attributes. Rank Math analyzes the
 * serialized post content (getEditedPostAttribute('content') -> applyFilters
 * 'rank_math_content') and parses the DOM looking for headings to power its
 * "Focus Keyword in subheadings", "Table of Contents" and content-length tests.
 * Because the heading text in those cases is wrapped/split/empty in the markup,
 * Rank Math cannot read it and reports the headings as missing.
 *
 * This hooks Rank Math's documented `rank_math_content` JS filter and appends a
 * clean `<hN>plain text</hN>` for every GreenShift heading-producing block found
 * in the editor, so Rank Math can detect and score them. The same code covers
 * GreenLight Builder, which registers the same `greenshift-blocks/*` blocks.
 *
 * @see https://rankmath.com/kb/content-analysis-api/
 */
(function (wp) {
	'use strict';

	if (!wp || !wp.hooks || !wp.data) {
		return;
	}

	var HEADING_TAGS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

	// Convert stored HTML/RichText markup into clean, single-line plain text.
	function toPlainText(html) {
		if (!html) {
			return '';
		}
		var el = document.createElement('div');
		el.innerHTML = String(html);
		var text = el.textContent || el.innerText || '';
		return text.replace(/\s+/g, ' ').trim();
	}

	// Escape plain text before placing it back inside a heading tag.
	function escapeHtml(text) {
		var el = document.createElement('div');
		el.textContent = text;
		return el.innerHTML;
	}

	// Resolve the readable text of a GreenShift Heading block from its attributes.
	function getHeadingText(attrs) {
		if (attrs.enableanimate) {
			var parts = [toPlainText(attrs.textbefore)];
			if (Array.isArray(attrs.animatedText)) {
				parts.push(attrs.animatedText.map(toPlainText).join(' '));
			}
			parts.push(toPlainText(attrs.textafter));
			var animated = parts.filter(Boolean).join(' ').trim();
			if (animated) {
				return animated;
			}
		}

		var text = toPlainText(attrs.headingContent);
		if (attrs.enablesubTitle && attrs.subTitle) {
			var sub = toPlainText(attrs.subTitle);
			if (sub) {
				text = (text + ' ' + sub).trim();
			}
		}
		return text;
	}

	// Recursively collect clean heading markup from GreenShift heading blocks.
	function collectHeadings(blocks, out) {
		if (!Array.isArray(blocks)) {
			return out;
		}

		blocks.forEach(function (block) {
			if (!block) {
				return;
			}

			var attrs = block.attributes || {};

			if (block.name === 'greenshift-blocks/heading') {
				var tag = (attrs.headingTag || 'h2').toLowerCase();
				if (HEADING_TAGS.indexOf(tag) !== -1) {
					var text = getHeadingText(attrs);
					if (text) {
						out.push('<' + tag + '>' + escapeHtml(text) + '</' + tag + '>');
					}
				}
			} else if (block.name === 'greenshift-blocks/element') {
				var etag = (attrs.tag || '').toLowerCase();
				if (HEADING_TAGS.indexOf(etag) !== -1) {
					var etext = toPlainText(attrs.textContent);
					if (etext) {
						out.push('<' + etag + '>' + escapeHtml(etext) + '</' + etag + '>');
					}
				}
			}

			if (block.innerBlocks && block.innerBlocks.length) {
				collectHeadings(block.innerBlocks, out);
			}
		});

		return out;
	}

	// Rank Math content filter: append clean GreenShift headings for analysis.
	function appendHeadings(content) {
		try {
			var editor = wp.data.select('core/block-editor');
			if (!editor || typeof editor.getBlocks !== 'function') {
				return content;
			}

			var headings = collectHeadings(editor.getBlocks(), []);
			if (!headings.length) {
				return content;
			}

			return content + '\n' + headings.join('\n');
		} catch (e) {
			// Never break Rank Math's analysis if anything unexpected happens.
			return content;
		}
	}

	wp.hooks.addFilter('rank_math_content', 'greenshift/rank-math', appendHeadings);

	// Trigger one re-analysis once Rank Math's editor is ready, in case it
	// finished its first pass before this filter was registered.
	var nudged = false;
	function nudgeRankMath() {
		if (nudged) {
			return;
		}
		if (window.rankMathEditor && typeof window.rankMathEditor.refresh === 'function') {
			nudged = true;
			window.rankMathEditor.refresh('content');
		}
	}

	if (typeof wp.data.subscribe === 'function') {
		var unsubscribe = wp.data.subscribe(function () {
			nudgeRankMath();
			if (nudged && typeof unsubscribe === 'function') {
				unsubscribe();
			}
		});
	}
	nudgeRankMath();
})(window.wp);

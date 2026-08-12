/**
 * GreenShift <-> Rank Math "Table of Contents" block integration.
 *
 * Rank Math's TOC block builds its heading list in the editor by scanning all
 * blocks and hard-coding the accepted block names to `core/heading`,
 * `rank-math/faq-block` and `rank-math/howto-block` (see the plugin's
 * schema/blocks/toc/assets/src/utils.js). There is no JS or PHP filter to add
 * other heading sources, so GreenShift's Heading Advanced block and the
 * Advanced HTML Element block used as h1-h6 never appear in the TOC.
 *
 * This wraps ONLY the TOC block's edit component in a proxied wp.data registry
 * (via RegistryProvider). Inside that subtree, `getBlockName` reports
 * GreenShift heading-producing blocks as `core/heading`, and
 * `getBlockAttributes` returns synthesized `{ level, content, anchor }`
 * attributes for them, so Rank Math's scanner picks them up unchanged. Blocks
 * outside the TOC block are unaffected.
 *
 * Anchors are chosen so the generated TOC links work on the frontend:
 * - Heading Advanced always renders `id="gspb_heading-id-{id}"` (or the
 *   user-set customAnchor), so that is used directly.
 * - Element blocks only render an id when their `anchor` attribute is set, so
 *   when it is empty the block's unique `id` attribute is written into
 *   `anchor` once (same kind of silent anchor write Rank Math itself performs
 *   on core headings).
 *
 * The same code covers GreenLight Builder, which registers the same
 * `greenshift-blocks/*` blocks.
 */
(function (wp) {
	'use strict';

	if (!wp || !wp.hooks || !wp.data || !wp.element || !wp.data.RegistryProvider || !wp.data.useRegistry) {
		return;
	}

	var el = wp.element.createElement;
	var HEADING_TAGS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

	// Convert stored HTML/RichText markup into clean, single-line plain text.
	function toPlainText(html) {
		if (!html) {
			return '';
		}
		var div = document.createElement('div');
		div.innerHTML = String(html);
		var text = div.textContent || div.innerText || '';
		return text.replace(/\s+/g, ' ').trim();
	}

	// Resolve the readable text of a Heading Advanced block from its attributes.
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
		return toPlainText(attrs.headingContent);
	}

	// Return the effective heading tag (h1-h6) of a GreenShift block, or null.
	function getGsHeadingTag(name, attrs) {
		var tag = '';
		if (name === 'greenshift-blocks/heading') {
			tag = (attrs.headingTag || 'h2').toLowerCase();
		} else if (name === 'greenshift-blocks/element') {
			tag = (attrs.tag || '').toLowerCase();
		}
		return HEADING_TAGS.indexOf(tag) !== -1 ? tag : null;
	}

	// Element blocks whose empty `anchor` was already backfilled this session.
	var anchorSynced = {};

	function makeProxy(registry) {
		var cache = new Map();

		// Persist the generated anchor into the Element block so the same id is
		// rendered on the frontend and the TOC link has a target. Deferred
		// because selectors must not dispatch.
		function syncElementAnchor(clientId, anchor) {
			if (anchorSynced[clientId]) {
				return;
			}
			anchorSynced[clientId] = true;
			window.setTimeout(function () {
				try {
					var attrs = registry.select('core/block-editor').getBlockAttributes(clientId);
					if (attrs && !attrs.anchor) {
						registry.dispatch('core/block-editor').updateBlockAttributes(clientId, { anchor: anchor });
					}
				} catch (e) {
					// Block may have been removed meanwhile.
				}
			});
		}

		// Build (and cache) core/heading-shaped attributes for a GS block.
		function synthAttributes(clientId, name, attrs, tag) {
			var cached = cache.get(clientId);
			if (cached && cached.src === attrs) {
				return cached.out;
			}

			var content;
			var anchor;
			if (name === 'greenshift-blocks/heading') {
				content = getHeadingText(attrs);
				anchor = attrs.customAnchor || (attrs.id ? 'gspb_heading-id-' + attrs.id : '');
			} else {
				content = toPlainText(attrs.textContent);
				anchor = attrs.anchor || '';
				if (!anchor && attrs.id) {
					anchor = attrs.id;
					syncElementAnchor(clientId, anchor);
				}
			}

			var out = {
				level: parseInt(tag.charAt(1), 10),
				content: content || '',
				anchor: anchor || '',
			};
			cache.set(clientId, { src: attrs, out: out });
			return out;
		}

		var proxy = Object.create(registry);
		proxy.select = function (storeNameOrDescriptor) {
			var selectors = registry.select(storeNameOrDescriptor);
			var storeName = typeof storeNameOrDescriptor === 'string'
				? storeNameOrDescriptor
				: (storeNameOrDescriptor && storeNameOrDescriptor.name);

			if (storeName !== 'core/block-editor' || !selectors) {
				return selectors;
			}

			var wrapped = Object.create(selectors);
			wrapped.getBlockName = function (clientId) {
				var name = selectors.getBlockName(clientId);
				if (name === 'greenshift-blocks/heading' || name === 'greenshift-blocks/element') {
					var attrs = selectors.getBlockAttributes(clientId);
					if (attrs && getGsHeadingTag(name, attrs)) {
						return 'core/heading';
					}
				}
				return name;
			};
			wrapped.getBlockAttributes = function (clientId) {
				var attrs = selectors.getBlockAttributes(clientId);
				var name = selectors.getBlockName(clientId);
				if (attrs && (name === 'greenshift-blocks/heading' || name === 'greenshift-blocks/element')) {
					var tag = getGsHeadingTag(name, attrs);
					if (tag) {
						return synthAttributes(clientId, name, attrs, tag);
					}
				}
				return attrs;
			};
			return wrapped;
		};
		return proxy;
	}

	function TocEditWrapper(ownProps) {
		var BlockEdit = ownProps.blockEdit;
		var registry = wp.data.useRegistry();
		var proxy = wp.element.useMemo(function () {
			return makeProxy(registry);
		}, [registry]);
		return el(
			wp.data.RegistryProvider,
			{ value: proxy },
			el(BlockEdit, ownProps.blockProps)
		);
	}

	wp.hooks.addFilter(
		'editor.BlockEdit',
		'greenshift/rank-math-toc',
		function (BlockEdit) {
			return function (props) {
				if (!props || props.name !== 'rank-math/toc-block') {
					return el(BlockEdit, props);
				}
				return el(TocEditWrapper, { blockEdit: BlockEdit, blockProps: props });
			};
		}
	);
})(window.wp);

/*
 * Gridline: catalogue filter + sort without page reloads.
 *
 * The server renders every row and the controls are ordinary links, so the
 * catalogue works without JS. Here we fetch the cached, read-only REST
 * endpoint once (/wp-json/gridline/v1/catalog), then filter and re-order the
 * existing rows from that data. If the request fails, links just navigate.
 */
(() => {
	'use strict';
	const SORTS = {
		newest: (a, b) => b.year - a.year || a.title.localeCompare(b.title),
		oldest: (a, b) => a.year - b.year || a.title.localeCompare(b.title),
		title: (a, b) => a.title.localeCompare(b.title),
		pages: (a, b) => b.pages - a.pages,
	};

	document.querySelectorAll('[data-gp-catalog]').forEach((root) => {
		const tbody = root.querySelector('[data-gp-rows]');
		const count = root.querySelector('[data-gp-count]');
		const empty = root.querySelector('[data-gp-empty]');
		if (!tbody || !root.dataset.endpoint) { return; }

		const rows = new Map(Array.from(tbody.rows).map((tr) => [Number(tr.dataset.id), tr]));
		const state = { imprint: root.dataset.imprint || '', sort: root.dataset.sort || 'newest' };
		let books = null;

		const load = () => {
			if (books) { return Promise.resolve(books); }
			root.classList.add('is-loading');
			return fetch(root.dataset.endpoint, { headers: { Accept: 'application/json' } })
				.then((r) => { if (!r.ok) { throw new Error(r.status); } return r.json(); })
				.then((json) => { books = json.books || []; return books; })
				.finally(() => root.classList.remove('is-loading'));
		};

		const viewUrl = (imprint, sort) => {
			const url = new URL(root.dataset.base || location.pathname, location.href);
			url.search = '';
			if (imprint) { url.searchParams.set('imprint', imprint); }
			if (sort !== 'newest') { url.searchParams.set('sort', sort); }
			return url;
		};

		const render = () => {
			const list = books
				.filter((b) => !state.imprint || (b.imprint && b.imprint.slug === state.imprint))
				.sort(SORTS[state.sort] || SORTS.newest);
			const visible = new Set(list.map((b) => b.id));
			let n = 0;
			list.forEach((b) => {
				const tr = rows.get(b.id);
				if (!tr) { return; }
				tr.hidden = false;
				n += 1;
				tr.querySelector('[data-gp-no]').textContent = String(n).padStart(2, '0');
				tbody.appendChild(tr);
			});
			rows.forEach((tr, id) => { if (!visible.has(id)) { tr.hidden = true; } });
			if (empty) { empty.hidden = n > 0; }
			if (count) { count.textContent = `${n} ${n === 1 ? 'title' : 'titles'}`; }
			root.querySelectorAll('a[data-imprint]').forEach((a) => {
				a.href = viewUrl(a.dataset.imprint, state.sort);
				a.toggleAttribute('aria-current', a.dataset.imprint === state.imprint);
				if (a.hasAttribute('aria-current')) { a.setAttribute('aria-current', 'true'); }
			});
			root.querySelectorAll('a[data-sort]').forEach((a) => {
				a.href = viewUrl(state.imprint, a.dataset.sort);
				a.toggleAttribute('aria-current', a.dataset.sort === state.sort);
				if (a.hasAttribute('aria-current')) { a.setAttribute('aria-current', 'true'); }
			});
		};

		root.addEventListener('click', (e) => {
			const link = e.target.closest('a[data-imprint], a[data-sort]');
			if (!link || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) { return; }
			e.preventDefault();
			if ('imprint' in link.dataset) { state.imprint = link.dataset.imprint; }
			if ('sort' in link.dataset) { state.sort = link.dataset.sort; }
			load().then(() => {
				render();
				if (history.replaceState && root.closest('.gp-archive')) {
					history.replaceState(null, '', viewUrl(state.imprint, state.sort));
				}
			}).catch(() => { window.location.href = link.href; });
		});

		// Honour a shared URL when the page itself could not filter (static/cached HTML).
		const params = new URLSearchParams(location.search);
		const wantImprint = params.get('imprint') || '';
		const wantSort = params.get('sort') || 'newest';
		if (wantImprint !== state.imprint || wantSort !== state.sort) {
			state.imprint = wantImprint;
			state.sort = SORTS[wantSort] ? wantSort : 'newest';
			load().then(render).catch(() => {});
		}
	});
})();

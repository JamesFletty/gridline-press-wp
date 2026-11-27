/* Gridline: mobile drawer. No dependencies. */
(() => {
	'use strict';
	document.documentElement.classList.add('js');
	const toggle = document.querySelector('[data-gp-toggle]');
	const drawer = document.querySelector('[data-gp-drawer]');
	if (!toggle || !drawer) { return; }
	const setOpen = (open) => {
		toggle.setAttribute('aria-expanded', String(open));
		drawer.hidden = !open;
	};
	toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && !drawer.hidden) { setOpen(false); toggle.focus(); }
	});
	drawer.addEventListener('click', (e) => { if (e.target.closest('a')) { setOpen(false); } });
})();

/**
 * ePiDK 主题交互脚本
 */
(function () {
	'use strict';

	var doc = document;
	var root = doc.documentElement;

	/* ---------------- 深浅色切换 ---------------- */
	var themeToggle = doc.getElementById('theme-toggle');
	if (themeToggle) {
		themeToggle.addEventListener('click', function () {
			var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
			root.setAttribute('data-theme', next);
			try {
				localStorage.setItem('epidk-theme', next);
			} catch (e) {}
		});
	}

	/* ---------------- 顶部导航滚动状态（主页透明 -> 实色） ---------------- */
	var header = doc.getElementById('site-header');
	var backToTop = doc.getElementById('back-to-top');

	function onScroll() {
		var y = window.scrollY || window.pageYOffset;
		if (header) {
			header.classList.toggle('is-scrolled', y > 40);
		}
		if (backToTop) {
			backToTop.classList.toggle('is-visible', y > 400);
		}
	}

	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	if (backToTop) {
		backToTop.addEventListener('click', function () {
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	}

	/* ---------------- 移动端菜单 ---------------- */
	var menuToggle = doc.getElementById('menu-toggle');
	var mainNav = doc.getElementById('main-nav');

	if (menuToggle && mainNav) {
		menuToggle.addEventListener('click', function () {
			var open = mainNav.classList.toggle('is-open');
			menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			if (header) {
				header.classList.toggle('is-solid', open);
			}
		});
	}

	/* ---------------- 知识库树折叠 ---------------- */
	doc.querySelectorAll('.tree-group-title').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var group = btn.closest('.tree-group');
			if (!group) {
				return;
			}
			var open = group.classList.toggle('is-open');
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	});

	/* ---------------- 目录滚动高亮（scrollspy） ---------------- */
	var tocCard = doc.getElementById('toc-card');
	if (tocCard && 'IntersectionObserver' in window) {
		var links = Array.prototype.slice.call(tocCard.querySelectorAll('a[href^="#"]'));
		var idMap = {};
		var headings = [];

		links.forEach(function (a) {
			var id = decodeURIComponent(a.getAttribute('href').slice(1));
			var el = doc.getElementById(id);
			if (el) {
				idMap[id] = a;
				headings.push(el);
			}
		});

		var setActive = function (id) {
			links.forEach(function (a) {
				a.classList.remove('is-active');
			});
			var link = idMap[id];
			if (link) {
				link.classList.add('is-active');
				// 让激活项在目录卡片内可见
				var box = tocCard.querySelector('.toc-nav');
				if (box && link.offsetTop > box.clientHeight - 60) {
					box.scrollTop = link.offsetTop - 60;
				}
			}
		};

		var visible = new Set();
		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						visible.add(entry.target.id);
					} else {
						visible.delete(entry.target.id);
					}
				});
				if (visible.size > 0) {
					// 取页面上最靠前的可见标题
					var first = headings.filter(function (h) {
						return visible.has(h.id);
					})[0];
					if (first) {
						setActive(first.id);
					}
				}
			},
			{ rootMargin: '-70px 0px -65% 0px', threshold: 0 }
		);

		headings.forEach(function (h) {
			observer.observe(h);
		});
	}

	/* ---------------- 点赞 ---------------- */
	function bindLike(btn) {
		if (btn.dataset.bound) {
			return;
		}
		btn.dataset.bound = '1';
		btn.addEventListener('click', function () {
			if (typeof epidk_ajax === 'undefined') {
				return;
			}
			btn.disabled = true;
			var fd = new FormData();
			fd.append('action', 'epidk_like');
			fd.append('nonce', epidk_ajax.nonce);
			fd.append('post_id', btn.dataset.id);

			fetch(epidk_ajax.url, {
				method: 'POST',
				body: fd,
				credentials: 'same-origin'
			})
				.then(function (r) {
					return r.json();
				})
				.then(function (res) {
					if (res && res.success) {
						btn.classList.toggle('is-liked', !!res.data.liked);
						btn.querySelectorAll('.like-count').forEach(function (el) {
							el.textContent = res.data.likes;
						});
					}
				})
				.catch(function () {})
				.finally(function () {
					btn.disabled = false;
				});
		});
	}

	doc.querySelectorAll('.epidk-like').forEach(bindLike);
})();

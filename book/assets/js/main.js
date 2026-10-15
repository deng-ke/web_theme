/**
 * GitBook 主题交互脚本
 * 功能：深浅色切换、移动端侧边栏、页内目录生成与滚动监听、
 *       阅读进度条、代码块复制、导航折叠、快捷键搜索
 */
( function () {
	'use strict';

	/* ---------- 1. 深浅色模式 ---------- */
	const root = document.documentElement;
	const themeToggle = document.querySelector( '[data-theme-toggle]' );
	const THEME_KEY = 'gitbook-theme';

	function getInitialTheme() {
		const saved = localStorage.getItem( THEME_KEY );
		if ( saved === 'light' || saved === 'dark' ) {
			return saved;
		}
		return window.matchMedia( '(prefers-color-scheme: dark)' ).matches ? 'dark' : 'light';
	}

	function applyTheme( theme ) {
		if ( theme === 'dark' ) {
			root.setAttribute( 'data-theme', 'dark' );
		} else {
			root.removeAttribute( 'data-theme' );
		}
	}

	applyTheme( getInitialTheme() );

	if ( themeToggle ) {
		themeToggle.addEventListener( 'click', function () {
			const next = root.getAttribute( 'data-theme' ) === 'dark' ? 'light' : 'dark';
			applyTheme( next );
			localStorage.setItem( THEME_KEY, next );
		} );
	}

	/* ---------- 2. 移动端侧边栏 ---------- */
	const openBtn = document.querySelector( '[data-sidebar-open]' );
	const closeEls = document.querySelectorAll( '[data-sidebar-close]' );

	function setSidebar( open ) {
		document.body.classList.toggle( 'sidebar-open', open );
	}

	openBtn && openBtn.addEventListener( 'click', function () {
		setSidebar( true );
	} );
	closeEls.forEach( function ( el ) {
		el.addEventListener( 'click', function () {
			setSidebar( false );
		} );
	} );
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' ) {
			setSidebar( false );
		}
	} );

	/* ---------- 3. 导航子菜单折叠 ---------- */
	const navItems = document.querySelectorAll( '.gb-nav-list > li' );

	navItems.forEach( function ( item ) {
		const sublist = item.querySelector( ':scope > ul' );
		if ( ! sublist ) {
			return;
		}

		const caret = document.createElement( 'span' );
		caret.className = 'gb-nav-caret' + ( item.classList.contains( 'is-active' ) ? ' is-open' : '' );
		caret.setAttribute( 'role', 'button' );
		caret.setAttribute( 'tabindex', '0' );
		caret.setAttribute( 'aria-label', '展开/收起' );
		caret.innerHTML =
			'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.6 5 7.2 6.4 12.8 12l-5.6 5.6 1.4 1.4 7-7Z"/></svg>';

		const link = item.querySelector( ':scope > .gb-nav-link, :scope > a' );
		if ( link ) {
			link.insertBefore( caret, link.firstChild );
		}

		if ( ! item.classList.contains( 'is-active' ) ) {
			item.classList.add( 'is-collapsed' );
		}

		function toggleCaret( e ) {
			e.preventDefault();
			e.stopPropagation();
			const collapsed = item.classList.toggle( 'is-collapsed' );
			caret.classList.toggle( 'is-open', ! collapsed );
		}

		caret.addEventListener( 'click', toggleCaret );
		caret.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Enter' || e.key === ' ' ) {
				toggleCaret( e );
			}
		} );
	} );

	/* ---------- 4. 页内目录生成 ---------- */
	const tocRoot = document.querySelector( '[data-toc-root]' );
	const tocList = document.getElementById( 'gb-toc-list' );

	if ( tocRoot && tocList ) {
		const headings = tocRoot.querySelectorAll( 'h2, h3' );
		const links = [];

		headings.forEach( function ( heading, index ) {
			if ( ! heading.id ) {
				heading.id = 'gb-heading-' + index;
			}
			const anchor = document.createElement( 'a' );
			anchor.className = 'gb-toc-link' + ( heading.tagName === 'H3' ? ' is-sub' : '' );
			anchor.href = '#' + heading.id;
			anchor.textContent = heading.textContent;
			tocList.appendChild( anchor );
			links.push( { link: anchor, heading: heading } );
		} );

		if ( links.length === 0 ) {
			const empty = document.createElement( 'span' );
			empty.className = 'gb-toc-empty';
			empty.textContent = '本页暂无章节标题';
			tocList.innerHTML = '';
			tocList.appendChild( empty );
		}

		/* 滚动监听高亮 */
		let activeIndex = -1;
		const observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) {
						return;
					}
					const idx = links.findIndex( function ( item ) {
						return item.heading === entry.target;
					} );
					if ( idx !== -1 ) {
						activeIndex = idx;
						links.forEach( function ( item, i ) {
							item.link.classList.toggle( 'is-active', i === idx );
						} );
					}
				} );
			},
			{ rootMargin: '-80px 0px -70% 0px', threshold: 0 }
		);
		links.forEach( function ( item ) {
			observer.observe( item.heading );
		} );
	}

	/* ---------- 5. 阅读进度条 ---------- */
	const progressBar = document.getElementById( 'gb-progress-bar' );

	function updateProgress() {
		if ( ! progressBar ) {
			return;
		}
		const scrollable = document.documentElement.scrollHeight - window.innerHeight;
		const ratio = scrollable > 0 ? window.scrollY / scrollable : 0;
		progressBar.style.width = Math.min( 100, Math.max( 0, ratio * 100 ) ) + '%';
	}

	updateProgress();
	window.addEventListener( 'scroll', updateProgress, { passive: true } );
	window.addEventListener( 'resize', updateProgress );

	/* ---------- 6. 代码块复制按钮 ---------- */
	const preBlocks = document.querySelectorAll( '.entry-content pre' );

	preBlocks.forEach( function ( pre ) {
		const btn = document.createElement( 'button' );
		btn.type = 'button';
		btn.className = 'gb-codebtn';
		btn.textContent = '复制';
		pre.appendChild( btn );

		btn.addEventListener( 'click', function () {
			const text = pre.querySelector( 'code' ) ? pre.querySelector( 'code' ).innerText : pre.innerText;
			const done = function () {
				btn.textContent = '已复制';
				btn.classList.add( 'is-copied' );
				setTimeout( function () {
					btn.textContent = '复制';
					btn.classList.remove( 'is-copied' );
				}, 1800 );
			};

			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( text ).then( done ).catch( function () {
					fallbackCopy( text, done );
				} );
			} else {
				fallbackCopy( text, done );
			}
		} );
	} );

	function fallbackCopy( text, done ) {
		const area = document.createElement( 'textarea' );
		area.value = text;
		area.style.position = 'fixed';
		area.style.opacity = '0';
		document.body.appendChild( area );
		area.select();
		try {
			document.execCommand( 'copy' );
			done();
		} catch ( err ) {
			/* 忽略复制失败 */
		}
		document.body.removeChild( area );
	}

	/* ---------- 7. 快捷键与搜索 ---------- */
	const searchInput = document.querySelector( '.gb-sidebar .gb-search-input' );
	const headerSearch = document.querySelector( '[data-search-open]' );

	function focusSearch() {
		setSidebar( true );
		setTimeout( function () {
			searchInput && searchInput.focus();
		}, 120 );
	}

	headerSearch && headerSearch.addEventListener( 'click', focusSearch );

	document.addEventListener( 'keydown', function ( e ) {
		const tag = ( e.target.tagName || '' ).toLowerCase();
		const typing = tag === 'input' || tag === 'textarea' || e.target.isContentEditable;
		if ( e.key === '/' && ! typing ) {
			e.preventDefault();
			focusSearch();
		}
	} );

	/* ---------- 8. 侧边栏当前项滚动到可视区域 ---------- */
	const activeLink = document.querySelector( '.gb-nav-item.is-active > .gb-nav-link' );
	if ( activeLink && activeLink.scrollIntoView ) {
		setTimeout( function () {
			activeLink.scrollIntoView( { block: 'center', behavior: 'smooth' } );
		}, 300 );
	}
} )();

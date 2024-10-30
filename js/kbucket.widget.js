jQuery.noConflict();
(function ($) {
	var $kbj = $;

	Masonry.prototype.measureColumns = function () {
		this.getContainerWidth();

		// if columnWidth is 0, default to outerWidth of first item
		if (!this.columnWidth) {
			var firstItem = this.items[0];
			var firstItemElem = firstItem && firstItem.element;
			// columnWidth fall back to item of first element
			this.columnWidth = firstItemElem && getSize(firstItemElem).outerWidth ||
				// if first elem has no width, default to size of container
				this.containerWidth;
		}

		var columnWidth = this.columnWidth += this.gutter;

		// calculate columns
		var containerWidth = this.containerWidth + this.gutter;
		var cols = containerWidth / columnWidth;
		// fix rounding errors, typically with gutters
		var excess = columnWidth - containerWidth % columnWidth;
		// if overshoot is less than a pixel, round up, otherwise floor it
		var mathMethod = excess && excess < 1 ? 'round' : 'floor';
		cols = Math[mathMethod](cols);
		this.cols = this.options.columns ?? Math.max(cols, 1);
	}


	Masonry.prototype._getItemLayoutPosition = function (item) { // Hack Masonry to order items by their order in the DOM
		item.getSize();
		// how many columns does this brick span
		var remainder = item.size.outerWidth % this.columnWidth;
		var mathMethod = remainder && remainder < 1 ? 'round' : 'ceil';
		// round if off by 1 pixel, otherwise use ceil
		var colSpan = Math[mathMethod](item.size.outerWidth / this.columnWidth);
		colSpan = Math.min(colSpan, this.cols);

		var parent_width = $kbj(item.element).parents('.kb-items-list').width();

		if (parent_width > 767) {
			if (!this.options || !this.options.columns) {
				hack = 3;
			} else {
				hack = parseInt(this.options.columns)
			}
			const per_line = 100 / hack;
			$kbj(item.element).css({
				"width": `${per_line}%`,
				"float": "left"
			});
		} else if (parent_width > 500) {
			$kbj(item.element).css({
				"width": "50%",
				"float": "none"
			});
			hack = 2;
		} else {

			$kbj(item.element).css({
				"width": "100%",
				"float": "none"
			});
			hack = 1;
		}

		var col = $kbj(item.element).index() % hack; // HACK : determine which column we want based on the element's index in the DOM


		if (typeof this._getColGroup === 'undefined') {
			var colGroup = this._getTopColGroup(colSpan);
		} else {
			var colGroup = this._getColGroup(colSpan);
		}

		colGroup = [this.colYs[col]]; // HACK : return only the column we want
		// get the minimum Y value from the columns
		var minimumY = Math.min.apply(Math, colGroup);
		var shortColIndex = col; // HACK

		// position the brick
		var position = {
			x: this.columnWidth * shortColIndex,
			y: minimumY
		};

		// apply setHeight to necessary columns
		var setHeight = minimumY + item.size.outerHeight;
		this.colYs[shortColIndex] = setHeight; // HACK : set height only on the column we used

		return position;
	};

	function _initMasonryGrid() {
		var wrap = (jQuery('[id^=kb-items-]').length) ? jQuery('[id^=kb-items-]').find('.kb-items-list.masonry') : null;
		if (!wrap || !wrap.length) return;
		wrap.each((i, grid) => {
			// if(i >= 1) return;
			var parent_width = $kbj(grid).width();
			let inline = jQuery(grid).data('inline');
			if (!inline) inline = 3;
			const per_line = 100 / parseInt(inline);

			if (parent_width > 767) {
				$kbj(grid).find('li').css({
					"width": `${per_line}%`,
					"float": "left"
				});
			} else if (parent_width > 500) {
				$kbj(grid).find('li').css({
					"width": "50%",
					"float": "none"
				});
			} else {
				$kbj(grid).find('li').css({
					"width": "100%",
					"float": "none"
				});
			}

			imagesLoaded(grid, function () {
				new Masonry(grid, {
					itemSelector: 'li',
					columnWidth: $kbj(grid).find('.grid-sizer')[0],//Math.floor($kbj(grid.querySelector('.grid-sizer')).width()),
					gutter: 10,
					percentPosition: true,
					columns: inline
				});
			});
		})
	}

	function _initCarousel() {
		var wrap = (jQuery('[id^=kb-items-]').length) ? jQuery('[id^=kb-items-]').find('.kb-items-list.carousel') : null;
		console.log("wrap _initCarousel " + wrap.length);
		if (!wrap.length) return;

		for (let slider of wrap) {
			var settings = {
				slidesToShow: jQuery(slider).data('inline') ?? 3,
				slidesToScroll: jQuery(slider).data('inline') ?? 3,
				// centerMode: true,
				// centerPadding: '30px',
				dots: true,
				// autoplay: true,
				autoplaySpeed: 3000,
				adaptiveHeight: true,
				nextArrow: "<button type='button' class='slick-next'><i class='fa fa-angle-right'></i></button>",
				prevArrow: "<button type='button' class='slick-prev'><i class='fa fa-angle-left'></i></button>",
				responsive: [
					{
						breakpoint: 769,
						settings: {
							slidesToShow: 3
						}
					},
					{
						breakpoint: 480,
						settings: {
							arrows: true,
							centerMode: false,
							slidesToShow: 1,
							slidesToScroll: 1
						}
					}
				]
			};
			jQuery(slider).slick(settings);
		}
	}

	function _initSlider() {
		var wrap = (jQuery('[id^=kb-items-]').length) ? jQuery('[id^=kb-items-]').find('.kb-items-list.slider') : null;
		console.log("wrap lenght " + wrap.length);
		if (!wrap.length) return;

		for (let slider of wrap) {
			var settings = {
				slidesToShow: 1,
				slidesToScroll: 1,
				dots: true,
				// autoplay: true,
				autoplaySpeed: 3000,
				adaptiveHeight: true,
				nextArrow: "<button type='button' class='slick-next'><i class='fa fa-angle-right'></i></button>",
				prevArrow: "<button type='button' class='slick-prev'><i class='fa fa-angle-left'></i></button>",
			};
			jQuery(slider).slick(settings);
		}
	}
	function _initSlideImage() {

		var wrap = (jQuery('[id^=kb-items-]').length) ? jQuery('[id^=kb-items-]').find('.kb-items-list.image-slider') : null;
		console.log("wrap _initSlideImage " + wrap.length);
		if (!wrap.length) return;

		for (let slider of wrap) {
			var settings = {
				slidesToShow: 2,
				slidesToScroll: 1,
				dots: true,
				// autoplay: true,
				autoplaySpeed: 3000,
				adaptiveHeight: true,
				nextArrow: "<button type='button' class='slick-next'><i class='fa fa-angle-right'></i></button>",
				prevArrow: "<button type='button' class='slick-prev'><i class='fa fa-angle-left'></i></button>",
			};

			jQuery(slider).slick(settings);
		}
	}
	$kbj(function () {
		console.log("initialize");
		_initCarousel();
		_initSlider();
		_initSlideImage();
	});
	setTimeout(function () {

		if ($kbj(window).width() > 767) console.log("_initMasonryGrid "); _initMasonryGrid();
	}, 500);

	var resizeTimeout;

	// $kbj(window).resize(function () {
	// 	clearTimeout(resizeTimeout); // Clear the previous timeout (if any)
	// 	resizeTimeout = setTimeout(function () {
	// 		console.log("_initSlideImage ");

	// 		_initMasonryGrid();
	// 		_initSlider();
	// 		_initSlideImage();
	// 	}, 300); // Adjust the debounce delay as needed (e.g., 300 milliseconds)
	// });
	// Create a debounced resize event handler
	var debouncedResize = _.debounce(function () {
		console.log("_initSlideImage ");
		_initMasonryGrid();
		_initSlider();
		_initSlideImage();
	}, 500); // Adjust the debounce delay as needed (e.g., 300 milliseconds)

	$kbj(window).on('resize', debouncedResize);
	// $kbj(window).resize(function () {
	// 	console.log("_initSlideImage ");

	// 	_initMasonryGrid();
	// 	_initSlider();
	// 	_initSlideImage();
	// });

	function refreshAddthis() {
		if (typeof window.addthis == 'undefined' || typeof window.addthis.toolbox == 'undefined') {
			return false;
		}
		window.addthis.toolbox('.addthis_toolbox_custom');
	}

	var tag = document.createElement('script');
	tag.src = "https://www.youtube.com/iframe_api";
	var firstScriptTag = document.getElementsByTagName('script')[0];
	firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

	var player;

	window.onYouTubeIframeAPIReady = function () {
	}

	var showMoreClickHandler = function () {
		document.querySelector('.content-short').style.display = 'none';
		document.querySelector('.content-full').style.display = 'block';
	}

	var showLessClickHandler = function () {
		document.querySelector('.content-full').style.display = 'none';
		document.querySelector('.content-short').style.display = 'block';
	}

	function shareBox(id) {
		$kbj.facebox(function () {
			$kbj.get('/wp-admin/admin-ajax.php', { "action": "share_content", "kb-share": id, "blog-id": kbObj.blog_id }, function (data) {
				$kbj.facebox(data);

				document.querySelector('.show-more').addEventListener('click', showMoreClickHandler);
				document.querySelector('.show-less').addEventListener('click', showLessClickHandler);

				if (typeof YT !== 'undefined' && YT && YT.Player) {
					player = new YT.Player('youtubePlayer', {
						events: {
							'onReady': function (event) {
								event.target.playVideo();
							}
						}
					});
				}
				(function (d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0];
					if (d.getElementById(id)) return;
					js = d.createElement(s); js.id = id;
					js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
					fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));

				if (typeof (FB) != 'undefined') {
					FB.init({ status: true, cookie: true, xfbml: true });
				} else {
					$kbj.getScript("//connect.facebook.net/en_US/all.js#xfbml=1", function () {
						FB.init({ status: true, cookie: true, xfbml: true });
					});
				}

				if (typeof addthis !== 'undefined') {
					var addthis_share = addthis_share || {}

					var addthis_config = {
						pubid: "ra-54aacc3842e62476",
						"data_track_addressbar": true,
						"ui_508_compliant": true
					};

				} else {
					refreshAddthis();
				}

			});
		});

		return false;
	}


	$kbj(document).ready(function ($kbj) {
		if (typeof $kbj.facebox !== "undefined") {

			$kbj(document).bind('beforeReveal.facebox', function () {
				$kbj('#facebox .content').width('720px');
			});

			$kbj.facebox.settings.closeImage = kbObj.kbucketUrl + '/images/closelabel.png';
			$kbj.facebox.settings.loadingImage = kbObj.kbucketUrl + '/images/loading.gif';
		}
		$kbj(".kb-item .kb-read-more-link").on("click", function (e) {
			e.preventDefault();

			var id = $kbj(this).data('slug');

			var self = $kbj(this);

			if (typeof $kbj.facebox.settings !== "undefined") {
				$kbj(document).bind('afterClose.facebox', function () {

					if (document.querySelector('.show-more') && document.querySelector('.show-less')) {
						document.querySelector('.show-more').removeEventListener('click', showMoreClickHandler);
						document.querySelector('.show-less').removeEventListener('click', showLessClickHandler);
					}

					if (player && typeof player.stopVideo === 'function') {
						player.stopVideo(); // stop the YouTube video
					}
					refreshAddthis();
					//scrollToElement('#'+self.attr('id'));
				});
			}

			shareBox(id);
		});
	});

})(jQuery);;

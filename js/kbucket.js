jQuery.noConflict();
(function ($) {
	var $kbj = $;

	function scrollToElement(target) {
		var topoffset = 30;
		var speed = 800;
		var destination = $kbj(target).offset().top - topoffset;
		$kbj('html:not(:animated),body:not(:animated)').animate({ scrollTop: destination }, speed, function () {
			window.location.hash = target;
		});
		return false;
	}


	function moveSuggestedItem(event, ui) {
		return '<div id="draggableHelper">Drag me into "Suggest Content"</div>';
	}

	function openSuggestBox(e, obj) {
		e.preventDefault();
		var url = obj.attr('href'),
			title = obj.attr('title');
		$kbj.facebox(function () {
			$kbj.get(url, function (data) { $kbj.facebox(data) })
		});
	}

	function fillSuggestContent(event, ui) {
		var obj = ui.draggable,
			link = $kbj(obj).find('.kb-link'),
			title = link.text(),
			surl = link.attr('href')

		if ($kbj(obj).find('span[itemprop=keywords] a').length) {
			var tags = [];
			$kbj(obj).find('span[itemprop=keywords] a').each(function (index, el) {
				tags.push($kbj(el).text());
			});
		} else {
			var tags = [];
		}
		tags = tags.join(', ');


		if ($kbj(obj).find('.kb-item-author-name[itemprop=author] span[itemprop=name]').text() !== 'No Author') {
			var author = $kbj(obj).find('.kb-item-author-name[itemprop=author] span[itemprop=name]').text();
		} else {
			var author = '';
		}

		$kbj.facebox(function () {
			var url = $kbj("#kb-suggest").attr('href');
			$kbj.get(url, function (data) {
				$kbj.facebox(data);
				$kbj("#stitle").val(title);
				$kbj("#stags").val(tags);
				$kbj("#surl").val(surl);
				$kbj("#sauthor").val(author);
			})
		});
	}
	var player;
	function initializeYouTubePlayer() {
		player = new YT.Player('youtubePlayer', {
			events: {
				'onReady': function (event) {
					event.target.playVideo();
				}
			}
		});
	}
	window.onYouTubeIframeAPIReady = function () {
		initializeYouTubePlayer();
	}
	function shareBox(id) {
		$kbj.facebox(function () {
			$kbj.get(
				kbObj.ajaxurl,
				{
					"action": "share_content",
					"kb-share": id,
					"blog-id": kbObj.blog_id,
					"kb-category": kbObj.categoryName,
					"kb-sub-category": kbObj.subCategoryName
				},
				function (data) {
					$kbj.facebox(data);

					// Check if YouTube Iframe API is already loaded
					if (typeof YT === 'undefined' || typeof YT.Player === 'undefined') {
						// load YouTube Iframe API
						var tag = document.createElement('script');
						tag.id = 'youtube-iframe-api';
						tag.src = "https://www.youtube.com/iframe_api";
						var firstScriptTag = document.getElementsByTagName('script')[0];
						firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
					} else {
						initializeYouTubePlayer();
					}

					/*document.querySelector('.show-more').addEventListener('click', function () {
						document.querySelector('.content-short').style.display = 'none';
						document.querySelector('.content-full').style.display = 'block';
					});
	
					document.querySelector('.show-less').addEventListener('click', function () {
						document.querySelector('.content-full').style.display = 'none';
						document.querySelector('.content-short').style.display = 'block';
					});*/

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

	$kbj(".kb-item").draggable({
		cursor: 'move',
		containment: "document",
		revert: true,
		helper: "clone",
		//cursorAt: { right: 500 }
	});

	$kbj("#kb-suggest").droppable({
		activeClass: "ui-state-hover",
		hoverClass: "ui-state-active",
		tolerance: "touch",
		drop: fillSuggestContent
	});

	function addthisEventHandler(evt) {
		switch (evt.type) {
			case "addthis.menu.open":
				addThisOpen(evt);
				break;
			case "addthis.menu.close":
				addThisClose(evt);
				break;
			case "addthis.menu.share":
				addThisShare(evt);
				break;
			default:
				console.log('received an unexpected event', evt);
		}
	}

	function addThisOpen(evt) {
		console.log(evt);
	}

	function addThisClose(evt) {
		//alert('close');

	}

	function addThisShare(evt) {
		//alert('share');
	}

	function refreshAddthis() {
		if (typeof window.addthis == 'undefined' || typeof window.addthis.toolbox == 'undefined') {
			return false;
		}
		window.addthis.toolbox('.addthis_toolbox_custom');
	}

	function validateSug() {
		if (($kbj('#stitle').val()) == "")
			$kbj('#showmsg').text("Please enter Page Tittle");
		else if (($kbj('#stags').val()) == "")
			$kbj('#showmsg').text("Please enter Page Tags");
		else if (($kbj('#surl').val()) == "")
			$kbj('#showmsg').text("Please enter Page URL");
		else if (($kbj('#sauthor').val()) == "")
			$kbj('#showmsg').text("Please enter Page Author");
		else {
			$kbj.ajax({
				type: 'POST',
				url: kbObj.ajaxurl,
				data: {
					action: "validate_suggest",
					sid_cat: $kbj('#sid_cat').val(),
					stitle: $kbj('#stitle').val(),
					stags: $kbj('#stags').val(),
					surl: $kbj('#surl').val(),
					sauthor: $kbj('#sauthor').val(),
					stwitter: $kbj('#stwitter').val(),
					sfacebook: $kbj('#sfacebook').val(),
					sdesc: $kbj('#sdesc').val()
				},
				success: function () {
					$kbj('#kb-suggest-box').empty();
					$kbj('#showmsg').text("Your suggestion was submitted successfully.");
					$kbj('#showmsg').css("color", "green");
					if ($kbj('#suggform').length) document.getElementById('suggform').reset();
				},
				error: function () {
					//alert('failure');
				}
			});
		}
	}

	// Order left to right
	Masonry.prototype._getItemLayoutPosition = function (item) { // Hack Masonry to order items by their order in the DOM


		item.getSize();
		// how many columns does this brick span
		var remainder = item.size.outerWidth % this.columnWidth;
		var mathMethod = remainder && remainder < 1 ? 'round' : 'ceil';
		// round if off by 1 pixel, otherwise use ceil
		var colSpan = Math[mathMethod](item.size.outerWidth / this.columnWidth);
		colSpan = Math.min(colSpan, this.cols);

		var parent_width = $kbj(item.element).parents('#kb-items-list').width();

		if (parent_width > 767) {
			hack = 3;
			//initMasonryGrid();
			$kbj(item.element).css({
				"width": "33.3333%",
				"float": "left"
			});
		} else if (parent_width > 500) {
			hack = 2;
			$kbj(item.element).css({
				"width": "50%",
				"float": "none"
			});
			//initMasonryGrid();
		} else {
			hack = 1;
			$kbj(item.element).css({
				"width": "100%",
				"float": "none"
			});
			//initMasonryGrid();
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


	function initMasonryGrid() {

		//console.log('hii');

		var grid = (document.getElementById('kb-items-1')) ? document.getElementById('kb-items-1').querySelector('#kb-items-list') : null;
		if (!grid) return;

		// var parent_width = $kbj('#kb-items-list').width();
		var parent_width = screen.width;
	//	console.log("parent_width", parent_width)

		imagesLoaded(grid, function () {
			new Masonry(grid, {
				itemSelector: 'li',
				columnWidth: grid.querySelector('.grid-sizer'),
				gutter: 10,
				percentPosition: true,
			});
		});

		// // console.log(parent_width)

		// if( parent_width > 767){
		// 	$kbj('#kb-items-list li').css({
		// 		// "width":"33.3333%",
		// 		// "float":"left"
		// 	});
		// }else if(parent_width > 500){
		// 	$kbj('#kb-items-list li').css({
		// 		// "width":"50%",
		// 		// "float":"none",
		// 		// "left":"50%"
		// 	});
		// }else{
		// 	$kbj('#kb-items-list li').css({
		// 		// "width":"100%",
		// 		// "float":"none"
		// 	});
		// }

		if (parent_width < 767) {
			$kbj('#kb-header').addClass('kb_search_fwidth');
		} else {
			$kbj('#kb-header').removeClass('kb_search_fwidth');
		}


	}


	// function initCarousel() {
	// 	var wrap = ($kbj('[id^=kb-items-]').length) ? $kbj('[id^=kb-items-]').find('.kb-items-list.kb-style-carousel') : null;
	// 	if(!wrap.length) return;
	//
	// 	var settings = {
	// 		slidesToShow: wrap.data('inline') ?? 3,
	// 		slidesToScroll: wrap.data('inline') ?? 3,
	// 		centerMode: true,
	// 		centerPadding: '30px',
	// 		// autoplay: true,
	// 		autoplaySpeed: 3000,
	// 		nextArrow: "<button type='button' class='slick-next'><i class='fa fa-angle-right'></i></button>",
	// 		prevArrow: "<button type='button' class='slick-prev'><i class='fa fa-angle-left'></i></button>",
	// 		responsive: [
	// 			{
	// 				breakpoint: 769,
	// 				settings: {
	// 					slidesToShow: 3
	// 				}
	// 			},
	// 			{
	// 				breakpoint: 480,
	// 				settings: {
	// 					arrows: true,
	// 					centerMode: false,
	// 					slidesToShow: 1
	// 				}
	// 			}
	// 		]
	// 	};
	// 	wrap.slick(settings);
	// }
	// initCarousel();


	function bindAfterCloseEvent() {
		$kbj(document).bind('afterClose.facebox', function () {
			//console.log("Popup closed");  
			if (player && typeof player.stopVideo === 'function') {
				player.stopVideo();
				//console.log("Video stopped");
			}
			refreshAddthis();
		});
	}

	$kbj(document).ready(function ($kbj) {

		$kbj('#kb-items-list').css({
			"padding": "0",
		});

		var parent_width = $kbj('#kb-items-list').width();
		if (parent_width < 767) {
			$kbj('#kb-header').addClass('kb_search_fwidth');
		} else {
			$kbj('#kb-header').removeClass('kb_search_fwidth');
		}

		// Copy to clipboard
		// new Clipboard('.copy_clipboard');
		$kbj('body').on('click', '.copy_clipboard', function (event) {
			event.preventDefault();
			var self = $kbj(this);
			self.find('.kb_popover').slideDown();
			setTimeout(function () {
				self.find('.kb_popover').slideUp();
			}, 1500);
		});


		$kbj("#kb-search").show();

		if (typeof kbSearch !== "undefined") {

			$kbj('body').find('input[name="s"]').each(
				function () {
					$kbj(this).attr('name', 'srch');
					$kbj(this).val(searchRes);
					$kbj(this.form).attr('action', permalink);
				}
			);
		}

		if (typeof kbObj == "undefined") {
			return false;
		}

		if (typeof $kbj.facebox !== "undefined") {

			$kbj(document).bind('beforeReveal.facebox', function () {
				$kbj('#facebox .content').width('720px');
			});

			$kbj.facebox.settings.closeImage = kbObj.kbucketUrl + '/images/closelabel.png';
			$kbj.facebox.settings.loadingImage = kbObj.kbucketUrl + '/images/loading.gif';
		}

		if (typeof kbObj.shareId !== "undefined") {
			shareBox(kbObj.shareId);
			delete (kbObj.shareId);
		}


		$kbj(".kb-toggle").on("click", function () {
			var i = $kbj(this).attr("id").replace("kb-toggle-", "");
			if ($kbj(this).text() === '...') {
				$kbj("#kb-item-text-" + i).show();
				$kbj(this).text('[-]');
			} else {
				$kbj("#kb-item-text-" + i).hide();
				$kbj(this).text('...');
			}
		});

		$kbj(".kb-item .kb-read-more-link").on("click", function (e) {
			e.preventDefault();

			var id = $kbj(this).data('slug');

			var self = $kbj(this);

			// if (typeof $kbj.facebox.settings !== "undefined") {
			// 	$kbj(document).bind('afterClose.facebox', function(){
			// 		refreshAddthis();
			// 		//scrollToElement('#'+self.attr('id'));
			// 	});
			// }

			e.preventDefault();

			var id = $kbj(this).data('slug');

			var self = $kbj(this);

			// bind the afterClose event on click of 'read more' link
			bindAfterCloseEvent();

			shareBox(id);

		});

		bindAfterCloseEvent();

		$kbj(".kb-suggest").on("click", function (e) {
			openSuggestBox(e, $kbj(this));
		});



		$kbj(document).ready(function () {

			initMasonryGrid()
			// setTimeout(function(){
			// 	if( $kbj( window ).width() > 767) initMasonryGrid();
			// },500);

		});
		$kbj(window).resize(function () {
			initMasonryGrid();
		});

		$kbj('.stm-shareble').on({
			mouseenter: function () {
				(function (d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0];
					if (d.getElementById(id)) return;
					js = d.createElement(s); js.id = id;
					js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
					fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));

				$kbj(this).parent().find('.stm-a2a-popup').addClass('stm-a2a-popup-active');
			},
			mouseleave: function () {
				$kbj(this).parent().find('.stm-a2a-popup').removeClass('stm-a2a-popup-active');
			}
		});

		$kbj('.stm-share').on('click', function (e) {
			e.preventDefault();
		});
	});

})(jQuery);




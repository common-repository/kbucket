$ = jQuery.noConflict();
var kbObj = "";
var player;
var $kbj = $;
$kbj(function($) {
  
   kbObj = dynamicLocalizationData;
  //console.log(kbObj);
  
/*function resizeGridItem(item){
  grid = document.getElementsByClassName("kb-items-list")[0];
  rowHeight = parseInt(window.getComputedStyle(grid).getPropertyValue('grid-auto-rows'));
  rowGap = parseInt(window.getComputedStyle(grid).getPropertyValue('grid-row-gap'));
  rowSpan = Math.ceil((item.querySelector('.kb-item-inner-wrap').getBoundingClientRect().height+rowGap)/(rowHeight+rowGap));
    item.style.gridRowEnd = "span "+rowSpan;
}*/

Masonry.prototype._getItemLayoutPosition = function (item) { 
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

/**Copied */
	window.onYouTubeIframeAPIReady = function () {
		initializeYouTubePlayer();
	}

  bindAfterCloseEvent();

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
/*

Out of Massonary

*/
function initMasonryGrid() {

  

  var grid = (document.getElementById('kb-items-masonary')) ? document.getElementById('kb-items-masonary').querySelector('#kb-items-list') : null;
  if (!grid) return;
  // var parent_width = $kbj('#kb-items-list').width();
  imagesLoaded(grid, function () {
    new Masonry(grid, {
      itemSelector: 'li',
      columnWidth: grid.querySelector('.grid-sizer'),
      gutter: 10,
      percentPosition: true,
    });
  });

 /**  if (parent_width < 767) {
    $kbj('#kb-header').addClass('kb_search_fwidth');
  } else {
    $kbj('#kb-header').removeClass('kb_search_fwidth');
  }
  var Hgrid = (document.getElementById('kb-items-masonary')) ? document.getElementById('kb-items-masonary').querySelector('#kb-items-list') : null;
  var $grid = $kbj('#kb-items-masonary #kb-items-list').masonry({
    itemSelector: 'li',
    columnWidth: Hgrid.querySelector('.grid-sizer'),
    percentPosition: true,
   
  });
  // layout Masonry after each image loads
  $grid.imagesLoaded().progress( function() {
    $grid.masonry('layout');
  });*/
 
}

$kbj(document).ready(function () {
			
  setTimeout(function(){
    initMasonryGrid()
  	// if( $kbj( window ).width() > 767) initMasonryGrid();
  },3000);

});
setInterval(function () { 
  Masonry.prototype._getItemLayoutPosition = function (item) { 
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
  initMasonryGrid();
  
 

},1000);
$kbj(window).on('load',function () {
  initMasonryGrid()
});

$kbj(window).resize(function () {
  initMasonryGrid();
});




/*

Out of load

*/

window.addEventListener("load", () => {

  var menuToggler = document.getElementById('kb_is_menu_toggle');
  var closeToggler = document.getElementById('kb_is_menu_close');
  var divWrap = document.getElementById('kb-tags-wrap-1');

  console.log(menuToggler); 
  menuToggler.addEventListener("click", is_menu_show)
  // divWrap.addEventListener("click", is_menu_close)
  closeToggler.addEventListener("click", is_menu_close)



  var sidebar_elem = document.getElementsByClassName('kb_is_mobile_menu')[0];
  function is_menu_show(){
    console.log("click click click", event.target)

        sidebar_elem.classList.add('is_show');
   
  }
  function is_menu_close(){
    sidebar_elem.classList.remove('is_show');
 }
});



$kbj(document).on("click", ".share_modal_wrapper .show-more", function() {

  document.querySelector('.content-short').style.display = 'none';
  document.querySelector('.content-full').style.display = 'block';

});

$kbj(document).on("click", ".share_modal_wrapper .show-less", function() {

  document.querySelector('.content-full').style.display = 'none';
  document.querySelector('.content-short').style.display = 'block';

});
function initializeYouTubePlayer() {
  player = new YT.Player('youtubePlayer', {
    events: {
      'onReady': function (event) {
        event.target.playVideo();
      }
    }
  });
}
$kbj(document).on('click','.kb-item .kb-read-more-link',function(e){

//$kbj(".kb-item .kb-read-more-link").on("click", function (e) {
  e.preventDefault();

  var id = $kbj(this).data('slug');

  var self = $kbj(this);

  

  e.preventDefault();

  var id =$kbj(this).data('slug');

  var self =$kbj(this);

  // bind the afterClose event on click of 'read more' link
  bindAfterCloseEvent();

  shareBox(id);

});

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
function refreshAddthis() {
  if (typeof window.addthis == 'undefined' || typeof window.addthis.toolbox == 'undefined') {
    return false;
  }
  window.addthis.toolbox('.addthis_toolbox_custom');
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

$kbj(document).ready(function ($kbj) { 
  if (typeof kbObj == "undefined") {
    return false;
  }
  if (typeof kbObj.shareId !== "undefined") {
    shareBox(kbObj.shareId);
    console.log('share MOdel '+kbObj.shareId);
    delete (kbObj.shareId);
  }

  if (typeof $kbj.facebox !== "undefined") {

    $kbj(document).bind('beforeReveal.facebox', function () {
      
      $kbj('#facebox .content').width('720px');
    });
    $kbj(document).bind('loading.facebox', function () {

      var posValue = kbObj.model_position;
      console.log("posValue "+posValue);
      
      $kbj('#facebox .content').addClass("facebox-pos-"+posValue)
    })


    $kbj.facebox.settings.closeImage = kbObj.kbucketUrl + '/images/closelabel.png';
    $kbj.facebox.settings.loadingImage = kbObj.kbucketUrl + '/images/loading.gif';
  }
});
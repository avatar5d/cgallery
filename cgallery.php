<?php
 /* cgallery 0.9.1
  *
  * a minimal gallery script by clangen. works in most major browsers.
  * just toss this php file in a directory with images and it will
  * automatically create thumbnails and generate a page layout.
  *
  * includes spin.js (http://fgnass.github.com/spin.js/)
  */

  header("Content-Type: text/html; charset=utf-8");

  if ( ! @$_GET["bgcolor"]) {
      $_GET["bgcolor"] = "404040";
  }

  function getOutputPath() {
    return (getcwd() . "/.thumbs/");
  }

  function getOutputFilenameFor($filename) {
    return getOutputPath() . end(explode("/", $filename));
  }
  
  function getImageList() {
    $dir = opendir(".");
    $images = array();
    while (($file = readdir($dir)) !== false) {
      $tail = strtolower(substr($file, strlen($file) - 3, 3));
      
      if (strcmp($tail, "jpg") == 0
      ||  strcmp($tail, "gif") == 0
      ||  strcmp($tail, "png") == 0) {
        array_push($images, $file);
      }
    }

    sort($images);

    return $images;
  }

  function createImage($filename, $format) {
    if ($format == "png") {
      return imagecreatefrompng($filename);
    }
    else if ($format == "jpg") {
      return imagecreatefromjpeg($filename);
    }
    else if ($format == "gif") {
      return imagecreatefromgif($filename);
    }

    return null;
  }

  // creates a thumbnail by resizing the original image so the longest side
  // is $maxSize pixels. automatically maintains the aspect ratio.
  function generateThumbFor($filename, $format) {
    $maxSize = 100;

    $imageOrig = createImage($filename, $format);
    $origWidth = imagesx($imageOrig);
    $origHeight = imagesy($imageOrig);
    $isPortrait = ($origHeight > $origWidth);
    $ratio = ($isPortrait) ? ($maxSize / $origHeight) : ($maxSize / $origWidth);

    $imageWidth = floor(($isPortrait) ? ($origWidth * $ratio) : $maxSize);
    $imageHeight = floor(($isPortrait) ? $maxSize : ($origHeight * $ratio));
    
    $thumb = imagecreatetruecolor($imageWidth, $imageHeight);
    $result = imagecopyresampled(
      $thumb,
      $imageOrig,
      0,
      0,
      0,
      0,
      $imageWidth,
      $imageHeight,
      $origWidth,
      $origHeight);
    
    if ($result) {
      imagepng($thumb, getOutputFilenameFor($filename));
    }

    imagedestroy($imageOrig);
    imagedestroy($thumb);
  }

  // if ./.thumbs/ doesn't exist then automatically create thumbnails
  // for all the png and jpg images in the current directory.
  function generateThumbs($imageList) {
    $imagePath = getcwd() . "/";
    $path = getOutputPath();

    @mkdir($path);
    
    // get a list of all the files that need to be thumbnailed
    foreach ($imageList as $current) {
      // skip "." and ".."
      if (($current === ".") || ($current === "..")) {
        continue;
      }

      $current = $imagePath . $current;

      // if the thumb already exists, skip
      if (file_exists(getOutputFilenameFor($current))) {
        continue;
      }

      // check image format
      $extension = strtolower(end(explode(".", $current)));
      if (($extension == "jpg") || ($extension == "png") || ($extension == "gif")) {
        generateThumbFor($current, $extension);
      }
    }
  }

  $imageList = getImageList();
  generateThumbs($imageList);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>

<title>gallery</title>

<style type="text/css">

html, body {
  font-family: Arial;
<?php
  printf("  background-color: #");
  printf($_GET["bgcolor"]);
  printf(";\n");
?>
  color: #dddddd;
  font-size: small;
  height: 100%;
  margin: 0px;
  padding: 0px;
  overflow: hidden;
  overflow-x: hidden;
  overflow-y: hidden;
  -moz-user-select: none;
}

a:link, a:visited {
  color: #ffffff;
}

a:visited {
}

a:hover {
}

a:active {
}

img {
  border: 0px;
}

.pic_active {
  border: 4px solid #ffffff;
  margin-right: 4px;
}

.pic_unviewed {
  border: 4px solid #000000;
  margin-right: 4px;
}

.pic_viewed {
  border: 4px solid #111111;
  margin-right: 4px;
}

.image {
  visibility: hidden;
  border: 4px solid #000000;
}

.imagediv {
  position: absolute;
  display: block;
  overflow: hidden;
}

.thumbdiv {
  position: absolute;
  text-align: center;
}

.contentdiv {
  display: block;
  position: absolute;
}

.bgbox {
  width: 15px;
  height: 15px;
  display: block;
  border: 1px solid #cccccc;
  cursor: pointer;
  padding: 0px;
  margin: 0px;
  line-height: 0px;
  font-size: 1px;
}

.prevnextbox {
  position: absolute;
  display: block;
  color: #bbbbbb;
  background-color: #222222;
  cursor: pointer;
  height: 15px;
  line-height: 15px;
  font-weight: bold;
  font-size: 18px;
  text-decoration: none;
  -moz-user-select: none;
}

.prevnextbox:hover {
  background-color: #2f2f2f;
  -moz-user-select: none;
}

.prevnextbox:active {
  background-color: #333333;
  -moz-user-select: none;
}

.prevnextboxtext {
  position: absolute;
  width: 100%;
  top: 50%;
  text-align: center;
}

.titlediv {
  position: absolute;
  display: block;
  width: 100%;
}

#colorbox {
  display: block;
}

</style>

<script type="text/javascript">

//fgnass.github.com/spin.js#v1.2.1
(function(a,b,c){function n(a){var b={x:a.offsetLeft,y:a.offsetTop};while(a=a.offsetParent)b.x+=a.offsetLeft,b.y+=a.offsetTop;return b}function m(a,b){for(var d in b)a[d]===c&&(a[d]=b[d]);return a}function l(a,b){for(var c in b)a.style[k(a,c)||c]=b[c];return a}function k(a,b){var e=a.style,f,g;if(e[b]!==c)return b;b=b.charAt(0).toUpperCase()+b.slice(1);for(g=0;g<d.length;g++){f=d[g]+b;if(e[f]!==c)return f}}function j(a,b,c,d){var g=["opacity",b,~~(a*100),c,d].join("-"),h=.01+c/d*100,j=Math.max(1-(1-a)/b*(100-h),a),k=f.substring(0,f.indexOf("Animation")).toLowerCase(),l=k&&"-"+k+"-"||"";e[g]||(i.insertRule("@"+l+"keyframes "+g+"{"+"0%{opacity:"+j+"}"+h+"%{opacity:"+a+"}"+(h+.01)+"%{opacity:1}"+(h+b)%100+"%{opacity:"+a+"}"+"100%{opacity:"+j+"}"+"}",0),e[g]=1);return g}function h(a,b,c){c&&!c.parentNode&&h(a,c),a.insertBefore(b,c||null);return a}function g(a,c){var d=b.createElement(a||"div"),e;for(e in c)d[e]=c[e];return d}var d=["webkit","Moz","ms","O"],e={},f;h(b.getElementsByTagName("head")[0],g("style"));var i=b.styleSheets[b.styleSheets.length-1],o=function q(a){if(!this.spin)return new q(a);this.opts=m(a||{},{lines:12,length:7,width:5,radius:10,color:"#000",speed:1,trail:100,opacity:.25,fps:20})},p=o.prototype={spin:function(a){this.stop();var b=this,c=b.el=l(g(),{position:"relative"}),d,e;a&&(e=n(h(a,c,a.firstChild)),d=n(c),l(c,{left:(a.offsetWidth>>1)-d.x+e.x+"px",top:(a.offsetHeight>>1)-d.y+e.y+"px"})),c.setAttribute("aria-role","progressbar"),b.lines(c,b.opts);if(!f){var i=b.opts,j=0,k=i.fps,m=k/i.speed,o=(1-i.opacity)/(m*i.trail/100),p=m/i.lines;(function q(){j++;for(var a=i.lines;a;a--){var d=Math.max(1-(j+a*p)%m*o,i.opacity);b.opacity(c,i.lines-a,d,i)}b.timeout=b.el&&setTimeout(q,~~(1e3/k))})()}return b},stop:function(){var a=this.el;a&&(clearTimeout(this.timeout),a.parentNode&&a.parentNode.removeChild(a),this.el=c);return this}};p.lines=function(a,b){function e(a,d){return l(g(),{position:"absolute",width:b.length+b.width+"px",height:b.width+"px",background:a,boxShadow:d,transformOrigin:"left",transform:"rotate("+~~(360/b.lines*c)+"deg) translate("+b.radius+"px"+",0)",borderRadius:(b.width>>1)+"px"})}var c=0,d;for(;c<b.lines;c++)d=l(g(),{position:"absolute",top:1+~(b.width/2)+"px",transform:"translate3d(0,0,0)",opacity:b.opacity,animation:f&&j(b.opacity,b.trail,c,b.lines)+" "+1/b.speed+"s linear infinite"}),b.shadow&&h(d,l(e("#000","0 0 4px #000"),{top:"2px"})),h(a,h(d,e(b.color,"0 0 1px rgba(0,0,0,.1)")));return a},p.opacity=function(a,b,c){b<a.childNodes.length&&(a.childNodes[b].style.opacity=c)},function(){var a=l(g("group"),{behavior:"url(#default#VML)"}),b;if(!k(a,"transform")&&a.adj){for(b=4;b--;)i.addRule(["group","roundrect","fill","stroke"][b],"behavior:url(#default#VML)");p.lines=function(a,b){function k(a,d,i){h(f,h(l(e(),{rotation:360/b.lines*a+"deg",left:~~d}),h(l(g("roundrect",{arcsize:1}),{width:c,height:b.width,left:b.radius,top:-b.width>>1,filter:i}),g("fill",{color:b.color,opacity:b.opacity}),g("stroke",{opacity:0}))))}function e(){return l(g("group",{coordsize:d+" "+d,coordorigin:-c+" "+ -c}),{width:d,height:d})}var c=b.length+b.width,d=2*c,f=e(),i=~(b.length+b.radius+b.width)+"px",j;if(b.shadow)for(j=1;j<=b.lines;j++)k(j,-2,"progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)");for(j=1;j<=b.lines;j++)k(j);return h(l(a,{margin:i+" 0 0 "+i,zoom:1}),f)},p.opacity=function(a,b,c,d){var e=a.firstChild;d=d.shadow&&d.lines||0,e&&b+d<e.childNodes.length&&(e=e.childNodes[b+d],e=e&&e.firstChild,e=e&&e.firstChild,e&&(e.opacity=c))}}else f=k(a,"animation")}(),a.Spinner=o})(window,document)

/* BEGIN cgallery */
var cgallery = (function(){
  /* BEGIN private variables */
  var config = {
    buttonWidth: 40,
    buttonHeight: 200,
    checkHashTimeoutMillis: 250,
    isInternetExplorer: (navigator.appName == "Microsoft Internet Explorer"),
    isOpera: (navigator.userAgent.indexOf("Opera/9") != -1),

    spinner: {
      lines: 12,
      length: 7,
      width: 4,
      radius: 10,
      color: '#ffffff',
      speed: 1,
      trail: 60,
      shadow: true
    },

    init: function() {
      // initialize browser-specific things
      if (config.isInternetExplorer) {
        views.thumbs.style.paddingRight = 6;

        Array.prototype.indexOf = function(value) {
          for (i = 0; i < this.length; i++) {
            if (this[i] == value) {
              return i;
            }
          }

          return -1;
        }
      }
    }
  };

  var state = {
    currentImageLoaded: false,
    lastActive: null,
    activeIndex: null,
    spinnerTimeout: null,
    lastHash: null
  };

  var views = {
    imageContainer: null,
    thumbs: null,
    content: null,
    titleContainer: null,
    prev: null,
    next: null,
    image: null,
    title: null,
    spinner: null,

    init: function() {
      views.imageContainer = document.getElementById("imagediv");
      views.thumbs = document.getElementById("thumbdiv");
      views.content = document.getElementById("contentdiv");
      views.titleContainer = document.getElementById("titlediv");
      views.prev = document.getElementById("prevbutton");
      views.next = document.getElementById("nextbutton");
      views.image = document.getElementById("image");
      views.spinner = new Spinner(config.spinner);
    }
  };

  var imageList = (function() {
    var result = new Array();

<?php
// use php to write javascript array of filenames called imageList
global $imageList;  // automatically cached when generating thumbnails
$i = 0;
foreach ($imageList as $filename) {
  printf('    result.push("' . $filename . '");' . "\n");
}
?>

    return result;
  })();
  /* END private variables */

  /* BEGIN layout engine (private) */
  var layoutEngine = (function() {
    var thumbStripRendered = false;

    function renderThumbStrip() {
      if (thumbStripRendered) {
        return;
      }
     
      var result = "<center><table>\n"
      result += "<tr>\n";

      for (i = 0; i < imageList.length; i++) {
        result += renderCell(imageList[i], i) + "\n";
      }

      result += "</tr>\n";
      result += "</table></center>\n"
    
      views.thumbs.innerHTML = result;
      thumbStripRendered = true;

      highlightActiveThumb();
    }

    function renderCell(filename, index) {
      var thumbFilename = ".thumbs/" + filename;

      var result =
        '<td id="' + filename + '_thumb"><center>' +
        '<a href="#' + filename + '" ' +
        'onClick="cgallery.changeImageTo(' + index + ')"' +
        '>' +
        '<img src="' + thumbFilename + '" id="image' + index + '" class="pic_unviewed" alt="thumbnail" onload="cgallery.layout();">' +
        '</a>' +
        '</center></td>';

      return result;
    }

    function getInnerWidth() {
      if (window.innerWidth != undefined) {
        return window.innerWidth;
      }

      return document.documentElement.clientWidth;
    }

    function getInnerHeight() {
      if (window.innerHeight != undefined) {
        return window.innerHeight;
      }

      return document.documentElement.clientHeight;
    }

    function fitImageToBounds(allottedWidth, allottedHeight) {
      var imageHeight = views.image.naturalHeight;
      var imageWidth = views.image.naturalWidth;

      if (imageHeight && imageWidth) {
        var overflowX = imageWidth - allottedWidth;
        var overflowY = imageHeight - allottedHeight;
        
        var finalWidth = imageWidth;
        var finalHeight = imageHeight;
        var scaleRatio = 1.0;
        if ((overflowX > 0) || (overflowY > 0)) {
          scaleRatio = Math.min(
            allottedHeight / imageHeight, allottedWidth / imageWidth);
        }

        imageWidth = Math.round(scaleRatio * finalWidth);
        imageHeight = Math.round(scaleRatio * finalHeight);
      }

      return {
        width: imageWidth,
        height: imageHeight
      };
    }

    function positionButtons() {
      var availableSpace = (getInnerHeight() - views.thumbs.offsetHeight - views.titleContainer.offsetHeight);
      var yOffset = views.titleContainer.offsetHeight + (availableSpace / 2) - (config.buttonHeight / 2);

      views.prev.style.left = "0px";
      views.prev.style.top = yOffset + "px";
      views.prev.style.height = config.buttonHeight + "px";
      views.prev.style.width = config.buttonWidth + "px";

      views.next.style.left = (getInnerWidth() - config.buttonWidth) + "px";
      views.next.style.top = yOffset + "px";
      views.next.style.height = config.buttonHeight + "px";
      views.next.style.width = config.buttonWidth + "px";
    }

    function positionTitleBar() {
      var offset = 2;
      views.titleContainer.style.top = offset + "px";
      views.titleContainer.style.width = getInnerWidth() + "px";
    }

    function positionStrip() {
      var border = 0;
      views.thumbs.style.overflowX = "auto";
      views.thumbs.style.overflowY = "hidden";
      views.thumbs.style.left = border + "px";
      views.thumbs.style.width = (getInnerWidth() - (border * 2)) + "px";
      views.thumbs.style.backgroundColor = "#222222";

      views.thumbs.style.borderTop = "1px solid #000000";  
      views.thumbs.style.top = (getInnerHeight() - views.thumbs.offsetHeight) + "px"; 
    }

    function positionImage() {
      var border = 4;
      views.imageContainer.style.top = views.titleContainer.clientHeight + border+ "px";
      views.imageContainer.style.left = border + config.buttonWidth + "px";
      views.imageContainer.style.height = (getInnerHeight() - views.thumbs.offsetHeight - views.titleContainer.clientHeight - (border * 2)) + "px";
      views.imageContainer.style.width = (getInnerWidth() - (border * 2) - (config.buttonWidth * 2)) + "px";

      views.image.style.position = "fixed";

      var size = fitImageToBounds(
        views.imageContainer.clientWidth - (border * 2),
        views.imageContainer.clientHeight - (border * 2));

      var clickable = (size.width != views.image.naturalWidth) || (size.height != views.image.naturalHeight);

      // fit it
      if (size.width && size.height) {
        views.image.style.width = size.width + "px";
        views.image.style.height = size.height + "px";
      }

      // center it
      var imageWidth = views.image.clientWidth;
      var containerWidth = views.imageContainer.clientWidth - (border * 2);
      views.image.style.left = (containerWidth / 2) - (imageWidth / 2) + 4 + config.buttonWidth + "px";
      //
      var imageHeight = views.image.clientHeight;
      var containerHeight = views.imageContainer.clientHeight - (border * 2);
      var titleHeight = views.titleContainer.clientHeight;
      views.image.style.top = titleHeight + (containerHeight / 2) - (imageHeight / 2) + 4 + "px";
    
      setImageClickable(clickable);
    }

    function highlightActiveThumb() {
      if (state.activeIndex) {
        var id = "image" + state.activeIndex;
        var thumb = document.getElementById(id);
        if (thumb) {
          thumb.className = "pic_active";
        }
      }
    }

    function getThumbScrollbarHeight() {
      return (views.thumbs.offsetHeight - views.thumbs.clientHeight);
    }

    function setImageClickable(clickable) {
      views.image.style.cursor = clickable ? "pointer" : "default";
      views.image.onclick = clickable ? onImageClicked : null;
    }

    function onImageClicked() {
      window.open(views.image.src);
    }

    return {
      layout: function() {
        renderThumbStrip();
        positionTitleBar();
        positionStrip();
        positionImage();
        positionButtons();
        ensureActiveThumbVisible();
      }
    };
  })();
  /* END layout engine */

  /* BEGIN private methods */
  function onMainImageLoaded() {
    // IE and Opera don't have a naturalHeight or naturalWidgth, so emulate it
    // by creating a new image, assigning the src, and checking the size.
    if (config.isInternetExplorer || config.isOpera) {
      var ieHack = new Image();
      ieHack.src = views.image.src;
      views.image.naturalWidth = ieHack.width;
      views.image.naturalHeight = ieHack.height;
    }

    state.currentImageLoaded = true;

    cgallery.layout();
    cgallery.hideSpinner();
    views.image.style.visibility = "visible";
  }

  function ensureActiveThumbVisible() {
    var thumbnail = document.getElementById(imageList[state.activeIndex] + "_thumb");
    
    if (thumbnail) {
      var thumbLeft = thumbnail.offsetLeft;
      var thumbWidth = thumbnail.offsetWidth;
      var thumbRight = thumbLeft + thumbWidth;
      var scrollLeft = views.thumbs.scrollLeft;
      var scrollWidth = views.thumbs.offsetWidth;
      var scrollRight = scrollLeft + scrollWidth;

      if (thumbLeft < scrollLeft) { // off the page to the left
        views.thumbs.scrollLeft = thumbnail.offsetLeft - 4;
      }
      else if (thumbRight > scrollRight) { // of the page to the right
        views.thumbs.scrollLeft += (thumbRight - scrollRight);
      }
    }
  }
  /* END private methods */

  /* BEGIN public API */
  return {
    layout: function() {
      layoutEngine.layout();
    },
  
    showNextImage: function() {
      if ((state.activeIndex + 1) < imageList.length) {
        cgallery.changeImageTo(state.activeIndex + 1);
      }
      else {
        cgallery.changeImageTo(0);
      }
    },

    showPreviousImage: function() {
      if (state.activeIndex > 0) {
        cgallery.changeImageTo(state.activeIndex - 1);
      }
      else {
        cgallery.changeImageTo(imageList.length - 1)
      }
    },

    changeBackground: function(color) {
      document.body.style.backgroundColor = color;
    },

    changeImageTo: function(index) {
      if (index == state.activeIndex) {
        return;
      }

      // after the first image is set use a timeout to monitor the window's
      // location hash. if it changes, load the appropriate image. this fixes
      // back button behavior to go to the previously viewed image.
      if ( ! state.lastHash) {
        var checkHash = function() {
          if (state.lastHash != window.location.hash) {
            state.lastHash = window.location.hash;

            for (var i = 0; i < imageList.length; i++) {
              if (("#" + imageList[i]) == window.location.hash) {
                cgallery.changeImageTo(i);
                break;
              }
            }
          }

          setTimeout(checkHash, config.checkHashTimeoutMillis);
        }

        setTimeout(checkHash, config.checkHashTimeoutMillis);
      }

      // set the url hash
      var filename = imageList[index];
      if (filename) {
        window.location.hash = filename;
      }
      
      state.currentImageLoaded = false;
      var filename = imageList[index];

      var id = "image" + index;
      var sender = document.getElementById(id);

      cgallery.showSpinner();

      // change the main image
      views.image.style.visibility = "hidden";
     
      // hack for opera to reset image width and height
      if (config.isOpera) {
        views.image.width = undefined;
        views.image.height = undefined;
      }
      
      views.image.onload = onMainImageLoaded;
      views.image.src = filename;

      if (sender) {
        sender.className = "pic_active";
      }

      if ((state.lastActive != null) && (sender != state.lastActive)) {
        state.lastActive.className = "pic_viewed";
      }

      state.lastActive = sender;
      state.activeIndex = index;

      ensureActiveThumbVisible();
    },

    hideSpinner: function() {
      if (state.spinnerTimeout) {
        clearTimeout(state.spinnerTimeout);
        state.spinnerTimeout = null;
      }
  
      views.spinner.stop();
    },

    showSpinner: function() {
      state.spinnerTimeout = setTimeout(function() {
        views.spinner.spin(views.imageContainer);
        state.spinnerTimeout = null;
      },
      250);
    },

    init: function() {
      views.init();
      config.init();

      var hashFilename = window.location.hash.substring(1, window.location.hash.length);
      var imageIndex = (hashFilename ? imageList.indexOf(hashFilename) : 0);

      cgallery.layout();
      cgallery.changeImageTo(imageIndex);
    }
  }
  /* END public API */
})();
/* END cgallery */

onload = function() {
  cgallery.init();

  /* hack around initialization issues by forcing a couple delayed layouts */
  setTimeout(cgallery.layout, 100);
  setTimeout(cgallery.layout, 1000);
}

onresize = function() {
  cgallery.layout();
};

</script>

</head>

<body onselectstart="return false">
  <!-- title bar start -->
  <div class="titlediv" id="titlediv">
    <center>
    <table> <tr>
      <td>
        <div id="colorbox">
          <table>
            <tr>
              <td> 
                <div
                  class="bgbox" 
                  style="background-color: #000000"
                  onclick="cgallery.changeBackground('#000000'); return false"></div>
              </td>
              <td> 
                <div
                  class="bgbox" 
                  style="background-color: #181818"
                  onclick="cgallery.changeBackground('#181818'); return false"></div>
              </td>
              <td> 
                <div
                  class="bgbox" 
                  style="background-color: #404040"
                  onclick="cgallery.changeBackground('#404040'); return false"></div>
              </td>
              <td> 
                <div
                  class="bgbox" 
                  style="background-color: #808080"
                  onclick="cgallery.changeBackground('#808080'); return false"></div>
              </td>
              <td> 
                <div
                  class="bgbox" 
                  style="background-color: #b0b0b0"
                  onclick="cgallery.changeBackground('#b0b0b0'); return false"></div>
              </td>
              <td> 
                <div
                  class="bgbox" 
                  style="background-color: #ffffff"
                  onclick="cgallery.changeBackground('#ffffff'); return false"></div>
              </td>
            </tr>
          </table>
        </div> 
    </tr>
    </table>
    </center>
  </div>
  <!-- title bar end -->

  <div id="contentdiv" class="contentdiv">
    <div id="thumbdiv" class="thumbdiv"></div>
 
    <div id="imagediv" class="imagediv">
      <img src="#" class="image" id="image" alt="main image">
    </div>

    <div class="prevnextbox" id="prevbutton" onclick="cgallery.showPreviousImage();">
        <div class="prevnextboxtext">&lt;</div>
    </div>

    <div class="prevnextbox" id="nextbutton" onclick="cgallery.showNextImage();">
        <div class="prevnextboxtext">&gt;</div>
    </div>
  </div>

</body>
</html>
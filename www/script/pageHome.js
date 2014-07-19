//----------------------------
// pageHome.js
//----------------------------

gRandomNames = [];

//----------------------------

$( document).on( 'pagecreate', '#pageHome', function()
{
	$( document).on( 'swipeleft swiperight', '#pageHome', function( e) {
		if( $( '.ui-page-active').jqmData( 'panel') !== 'open') {
			if( gSettings.enableSwipe) {
				if( e.type === 'swipeleft') {
					showNextName();
				} else if( e.type === 'swiperight') {
//					$( '#canvasHome').panel( 'open');
					showPreviousName();
				}
			}
		}
	});
});

//----------------------------

function onPageHomeResize()
{
	var space = $( window).height() - $( '#pageHomeContainer').outerHeight() - $( '#pageBoxHeader').outerHeight();
	$( '#boxspacer').css({
		height: parseInt( space / 2) + 'px',
	});
	$( '#pageHomeControl').css({
		height: parseInt( space / 2 - 2) + 'px',
		lineHeight: parseInt( space / 2 - 2) + 'px',
	});

	var pos = gRandomNames[ gRandomNames.length - 3];
	resizeBoxName( pos);
}

//----------------------------

function resizeBoxName( pos)
{
	var obj = $( "#boxName");
	var fontHeight = 4.1;
	obj.html( gDataName[ pos].name);

	do {
		if( fontHeight <= 2) {
			break;
		}

		fontHeight -= 0.1;
		obj.css({fontSize:fontHeight+'em'});
	} while( obj.width() >= obj.parent().width());
}

//----------------------------

function shuffleArray( array, len)
{
	var p, n, tmp;
	for( p = len; p;) {
		n = Math.random() * p-- | 0;
		tmp = array[ n];
		array[ n] = array[ p];
		array[ p] = tmp;
	}
}

//----------------------------

function initRandomNames()
{
	if( gRandomNames.length == 0) {
		for( var i = 0; i < gDataName.length; ++i) {
			if( gDataName[i].charts.length == 0) {
				continue;
			}
			if( !isFilteredNameId( i)) {
				continue;
			}

			gRandomNames.push( i);
		}
		shuffleArray( gRandomNames, gRandomNames.length);
	}
}

//----------------------------

function onMenuKeyDown()
{
	if( $( '.ui-page-active').jqmData( 'panel') !== 'open') {
		$( '#canvasHome').panel( 'open');
	} else {
		$( '#canvasHome').panel( 'close');
	}
}

//----------------------------

function showNextName()
{
	try {
		initRandomNames();

		var shakeLen = parseInt( gRandomNames.length * .30);
		shuffleArray( gRandomNames, shakeLen);
		gRandomNames.push( gRandomNames.shift());
		var pos = gRandomNames[ gRandomNames.length - 3];

		if( gScreenshot) {
			for( var i = 0; i < gDataName.length; ++i) {
				if( 'Flora' == gDataName[i].name) {
//				if( 'Linus' == gDataName[i].name) {
					pos = i;
					break;
				}
			}
		}

		showNameByIndex( pos);
		updateHomeControl();
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

//----------------------------

function showPreviousName()
{
	try {
		initRandomNames();

		var shakeLen = parseInt( gRandomNames.length * .30);
		shuffleArray( gRandomNames, shakeLen);
		gRandomNames.unshift( gRandomNames.pop());
		var pos = gRandomNames[ gRandomNames.length - 3];

		showNameByIndex( pos);
		updateHomeControl();
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

//----------------------------

function showNameByIndex( pos)
{
	var charts = '&nbsp;';
	try {
		var nutsPos = 0;
		if( '' != gSettings.filterNUTS) {
			for( ; nutsPos < gDataName[ pos].charts.length; ++nutsPos) {
				var vec = gDataName[ pos].charts[ nutsPos].split( '-');
				if( 0 == vec[3].search( gSettings.filterNUTS)) {
					break;
				}
			}
		}

		var vec = gDataName[ pos].charts[ nutsPos].split( '-');
		if( vec.length > 0) {
			charts = _( 'detailChartPosShort').replace(/%pos%/g,vec[0]);
		}
	} catch( e) {
	}

	$( "#boxGenderA").attr("href", "#name?given=" + gDataName[ pos].name);
	$( "#boxNameA").attr("href", "#name?given=" + gDataName[ pos].name);
	$( "#boxNameImg").attr("src", "art/list-logo" + gDataName[ pos].gender + ".png");
	$( "#boxNameCharts").html( charts);

	resizeBoxName( pos);
}

//----------------------------

function updateHomeControl()
{
	for( var idx = 0; idx < 5; ++idx) {
		var pos = gRandomNames[ gRandomNames.length - 1 - idx];
		if( idx == 2) {
			$( "#pageHomeControl div:eq(" + (4-idx) +")").css( 'background-color', '#000000');
		} else if( 'm' == gDataName[ pos].gender) {
			$( "#pageHomeControl div:eq(" + (4-idx) +")").css( 'background-color', '#a9c4f5'); //#6495ed
		} else if( 'f' == gDataName[ pos].gender) {
			$( "#pageHomeControl div:eq(" + (4-idx) +")").css( 'background-color', '#f8c4c4'); //#f08080
		} else {
			$( "#pageHomeControl div:eq(" + (4-idx) +")").css( 'background-color', '#c6c6c6'); //#a0a0a0
		}
	}
}

//----------------------------

$( document).on( 'pageshow', '#pageHome',  function()
{
	try {
		init();

		var txt = '';
		txt += '<li data-role="list-divider" style="height:40px;"><a href="#" id="boxGenderA"><center><img src="art/list-logob.png" id="boxNameImg" style="width:40px;height:40px;pointer-events:none;"></center></a></li>';
		txt += '<li data-icon="false" class="afterDivider"><a href="#" id="boxNameA"><div style="text-align:center;height:5.2em;line-height:5.2em;"><span id="boxName" style="font-size:4em;white-space:nowrap;overflow:hidden;"></span></div></a></li>';
		txt += '<li data-role="list-divider" id="boxNameCharts" style="text-align:center;border-bottom:none !important;"></li>';

		$( "#h1Home").html( _( 'appName'));
		$( "#boxShake").html( _( 'resultButtonNext'));
		$( "#ulBox").html( txt);
		$( "#ulBox").listview('refresh');
		$( "#aBoxShake" ).bind( "click", function( event, ui) { showNextName(); });

		if( gSettings.enableShake || gSettings.enableSwipe) {
			$( "#aBoxShake").css( 'display', 'none');
		} else {
			$( "#aBoxShake").css( 'display', 'block');
		}
		if( gShowWP) {
			$( "#aBoxShake").css( 'display', 'none');
		}

		initCanvas( 'buttonCanvasHome', 'canvasHome');
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}

	try {
		if( gShowWP) {
			window.external.notify( "applicationBarClear");
			window.external.notify( "applicationBarAddButton:"+ _( 'homeSearchShort') +":art/win-search.png:gotoPage:pageSearch");
			window.external.notify( "applicationBarAddMenu:"+ _( 'homeImprint') +":gotoPage:pageImprint");
			window.external.notify( "applicationBarAddButton:"+ _( 'resultButtonShake') +":art/win-shake.png:showNextName:");
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}

	try {
//		if( navigator && navigator.accelerometer) {
//			shake.startWatch( showNextName);
//		} else {
			if( gSettings.enableShake) {
				window.addEventListener( 'shake', showNextName, false);
			}
//		}

		// Cordova (3.4.0) lifecycle events
		// Supported Platforms: Amazon Fire OS, Android, BlackBerry 10
		window.addEventListener( 'menubutton', onMenuKeyDown, false);

		showNextName();
	} catch( e) {
	}

	onResize();
});

//----------------------------

$( document).on( 'pagehide', '#pageHome',  function()
{
	try {
//		if( navigator && navigator.accelerometer) {
//			shake.stopWatch();
//		} else {
			if( gSettings.enableShake) {
				window.removeEventListener( 'shake', showNextName, false);
			}
//		}

		// Cordova lifecycle events
		window.removeEventListener( 'menubutton', onMenuKeyDown, false);
	} catch( e) {
	}
});

//----------------------------
// eof

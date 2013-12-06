//----------------------------
//---       index.js       ---
//----------------------------

// http://christian-helle.blogspot.de/2012/03/integrating-html5-and-javascript-with.html
// http://stackoverflow.com/questions/14630765/back-button-on-windows-phone-8

gFavs = [];
gShakeBoy = [];
gShakeGirl = [];
//gPopUpNum = 0;
var gWebViewHref = "";
var gWebViewTitle = "";
gInit = false;
gShowIOS = false;
gShowWP = false;
gDebug = false;
gScreenshot = false;

//gShowIOS = true;
//gScreenshot = true;
//gShowWP = true;
//gDebug = true;

//----------------------------

function init()
{
	try {
		if( gInit) {
			return;
		}
		gInit = true;

		CInternationalization.init();

		if(( typeof device !== "undefined") && (typeof device.platform !== "undefined")) {
			gShowIOS = ((device.platform == "iPhone") || (device.platform == "iPad") || (device.platform == "iPod touch") || (device.platform == "iOS"));
		}
		if( !gShowIOS && (typeof window.external !== "undefined") && (typeof window.external.notify !== "undefined")) {
			gShowWP = true;
		}

		if( gScreenshot) {
			$( 'body,html').css( 'overflow', 'hidden');
		}

		loadFavorites();

		$.each( gFavs, function( index) {
//			echoFav( this);
			saveFavToGlobalVecs( this);
		});
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

$( document).ready( function()
{
//	CInternationalization.init();

	$( window).resize( function() {
		$( '#pageBoxContent').css({
			position: 'absolute',
			top: $( '#pageBoyHeader').outerHeight() + ($( window).height() - $( '#pageBoxContent').outerHeight() - $( '#pageBoyHeader').outerHeight()) / 2,
			left: 0,
			width: $( window).width() - 30
		});
		$( '#pageGirlContent').css({
			position: 'absolute',
			top: $( '#pageGirlHeader').outerHeight() + ($( window).height() - $( '#pageGirlContent').outerHeight() - $( '#pageGirlHeader').outerHeight()) / 2,
			left: 0,
			width: $( window).width() - 30
		});

		$( "#pageWebView iframe").css({
			height: $( window).height() - $( '#pageWebViewHeader').outerHeight() - 40,
			width: $( window).width() * .995 - 30
		});
	});

//	CHue.init( "NameYourBabe", "GirlNameBoyName", function() {
//		$.mobile.changePage( "#connectHue");
//	}, function() {
//		$.mobile.changePage( "#pageHome");
//		hueVoid();
//	});
//
//	CHue.discoverBridges();
});

function loadFavorites()
{
	try {
		if( typeof Windows !== "undefined") {
//			Windows && Windows.Storage && Windows.Storage.ApplicationData
		}
		if( gShowWP) {
			window.external.notify( "localStorageGetItem:loadFavoritesItem");
		} else
		if( typeof localStorage !== "undefined") {
			loadFavoritesItem( localStorage.getItem( 'gFavs'));
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

function loadFavoritesItem(value)
{
	try {
		if(( null != value) && (value[ 0] == "{")) {
			value = JSON.parse( value);
		} else if(( null != value) && (value[ 0] == "[")) {
			value = JSON.parse( value);
		}
		if(( null != value) && ('' != value)) {
			gFavs = value;
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

function saveFavorites()
{
	var value = gFavs;
	try {
		if( typeof localStorage !== "undefined") {
			if( typeof value == "object") {
				value = JSON.stringify( value);
			}
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}

	try {
		if( typeof localStorage !== "undefined") {
			localStorage.removeItem( 'gFavs');
		}
	} catch( e) {
	}

	try {
		if( gShowWP) {
			window.external.notify( "localStorageSetItem:" + value);
		} else
		if( typeof localStorage !== "undefined") {
			localStorage.setItem( 'gFavs', value);
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

function scale( width, height, padding, border)
{
	var scrWidth = $( window).width() - 30;
	var scrHeight = $( window).height() - 30;
	var ifrPadding = 2 * padding;
	var ifrBorder = 2 * border;
	var ifrWidth = width + ifrPadding + ifrBorder;
	var ifrHeight = height + ifrPadding + ifrBorder;
	var h, w;

	if( ifrWidth < scrWidth && ifrHeight < scrHeight ) {
		w = ifrWidth;
		h = ifrHeight;
	} else if(( ifrWidth / scrWidth) > (ifrHeight / scrHeight)) {
		w = scrWidth;
		h = (scrWidth / ifrWidth) * ifrHeight;
	} else {
		h = scrHeight;
		w = (scrHeight / ifrHeight) * ifrWidth;
	}

	return {
		'width': w - (ifrPadding + ifrBorder),
		'height': h - (ifrPadding + ifrBorder)
	};
};

function gotoPage( pageName)
{
	$.mobile.changePage( "#" + pageName);
}

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

function hueVoid()
{
	var state = {
		on: true,
		bri: 255,
		hue: 32768,
		sat: 255,
		transitiontime: 15
	};
	CHue.setLight( 0, state, function() {
		CHue.setLight( 1, state, function() {
			CHue.setLight( 2, state, function() {
			});
		});
	});
}

function hueBoy()
{
	var state = {
		on: true,
		bri: parseInt( 255 * 0.51),
		hue: parseInt( 65535 * 243/360),
		sat: parseInt( 255 * 0.64),
		transitiontime: 15
	};
	CHue.setLight( 0, state, function() {
		CHue.setLight( 1, state, function() {
			CHue.setLight( 2, state, function() {
			});
		});
	});
}

function hueGirl()
{
	var state = {
		on: true,
		bri: parseInt( 255 * 0.52),
		hue: parseInt( 65535 * 336/360),
		sat: parseInt( 255 * 0.41),
		transitiontime: 15
	};
	CHue.setLight( 0, state, function() {
		CHue.setLight( 1, state, function() {
			CHue.setLight( 2, state, function() {
			});
		});
	});
}

//----------------------------

//$.extend( $.mobile, {
//	hashListeningEnabled: false
//});

$( document).bind( "mobileinit", function() {
	$.support.cors = true;
	$.mobile.allowCrossDomainPages = true;
	$.mobile.listview.prototype.options.filterPlaceholder = _( 'searchPlaceholder');
});

$( document).bind( "pagebeforechange", function( e, data) {
	if( typeof data.toPage === "string") {
		var u = $.mobile.path.parseUrl( data.toPage);
		var re = /^#name/;
		if( u.hash.search(re) !== -1) {
			onName( u, data.options);
			e.preventDefault();
		}
	}
});

//----------------------------

$( '#pageHome').live( "pageinit", function()
{
	try {
		init();

		var txt = '';
		if( gShowWP) {
			txt += '<center><img src="art/icon-win-114.png" style="padding:0.5em%;"></center>';
		} else {
			txt += '<center><img src="art/icon-ios-114.png" ></center>';
		}
		txt += '<p>' + _( 'homeSub') + '</p>';
		$( "#divHome").html( txt);

		txt = '';
		txt += '<li data-theme="e"><a href="#pageGirl" class="ui-li-poilink"><img src="art/list-babye.png" class="ui-li-poiicon">' + _( 'homeGirl') + ' <span class="ui-li-count">' + gGirls.length + '</span></a></li>';
		txt += '<li data-theme="b"><a href="#pageBoy" class="ui-li-poilink"><img src="art/list-babyb.png" class="ui-li-poiicon">' + _( 'homeBoy') + ' <span class="ui-li-count">' + gBoys.length + '</span></a></li>';
		txt += '<li data-theme="a"><a href="#pageFav" class="ui-li-poilink"><img src="art/list-fav.png" class="ui-li-poiicon">' + _( 'homeFav') + ' <span class="ui-li-count">' + gFavs.length + '</span></a></li>';
		$( "#ulHome").html( txt);
		$( "#ulHome").listview('refresh');

		txt = '';
		if( gDebug) {
			var unique = new Object();
			$.each( gGirls, function( index) {
				unique[ this.name] = 0;
				$.each( this.altM, function( index) { unique[ this] = 0; });
				$.each( this.altF, function( index) { unique[ this] = 0; });
			});
			$.each( gBoys, function( index) {
				unique[ this.name] = 0;
				$.each( this.altM, function( index) { unique[ this] = 0; });
				$.each( this.altF, function( index) { unique[ this] = 0; });
			});
//			$.each( gFavs, function( index) {
//				unique[ this.name] = 0;
//				$.each( this.altM, function( index) { unique[ this] = 0; });
//				$.each( this.altF, function( index) { unique[ this] = 0; });
//			});
			var uniqueLength = 0;
			for( var foo in unique) {
				++uniqueLength;
			}
			txt += '<li><a href="#pageSearch">' + _( 'homeSearch') + ' <span class="ui-li-count">' + uniqueLength + '</span></a></li>';
		} else {
			txt += '<li><a href="#pageSearch">' + _( 'homeSearch') + '</span></a></li>';
		}
//		txt += '<li><a href="#pageShare">' + _( 'homeShare') + '</span></a></li>';
		txt += '<li><a href="#pageImprint">' + _( 'homeImprint') + '</span></a></li>';

		if( !gShowWP) {
			$( "#ulHome2").html( txt);
			$( "#ulHome2").listview('refresh');
		}

		$( "#h1Home").html( _( 'appName'));
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
});

$( '#pageHome').live( "pageshow", function()
{
//	hueVoid();

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
		window.external.notify( "applicationBarAddButton:"+ _( 'homeSearchShort') +":art/win-search.png:gotoPage:pageSearch");
		window.external.notify( "applicationBarAddMenu:"+ _( 'homeImprint') +":gotoPage:pageImprint");
	}

	try {
		var txt = '';
		txt += '<li data-theme="e"><a href="#pageGirl" class="ui-li-poilink"><img src="art/list-babye.png" class="ui-li-poiicon">' + _( 'homeGirl') + ' <span class="ui-li-count">' + gGirls.length + '</span></a></li>';
		txt += '<li data-theme="b"><a href="#pageBoy" class="ui-li-poilink"><img src="art/list-babyb.png" class="ui-li-poiicon">' + _( 'homeBoy') + ' <span class="ui-li-count">' + gBoys.length + '</span></a></li>';
		txt += '<li data-theme="a"><a href="#pageFav" class="ui-li-poilink"><img src="art/list-fav.png" class="ui-li-poiicon">' + _( 'homeFav') + ' <span class="ui-li-count">' + gFavs.length + '</span></a></li>';
		$( "#ulHome").html( txt);
		$( "#ulHome").listview('refresh');
	} catch( e) {
	}
});

//----------------------------

$( '#pageImprint').live( "pageinit", function()
{
	try {
		init();

		var txt = '';
		txt += '<h1>' + _( 'appName') + '</h1>';
		txt += '<p>' + _( 'impressCopyright') + '</p>';

		$( "#divImpress").html( txt);

// Support-Webseite
// Bewerten

		gWebViewTitle = _( 'imprintTitleCCBY30AT');
		gWebViewHref = 'https://creativecommons.org/licenses/by/3.0/at/deed.de';

		txt = '';
		txt += '<li data-role="list-divider">' + _( 'imprintTitleData') + '</li>';
//		txt += '<li><a href="https://creativecommons.org/licenses/by/3.0/at/deed.de" rel="external" target="_blank">';
//		txt += '<li><a href="#pageImprintWeb" data-rel="popup" data-position-to="window" data-role="button" data-theme="b" data-inline="true">';
		txt += '<li><a href="#pageWebView" data-rel="dialog" data-role="button" data-theme="b" data-inline="true">';
		txt += '<h1 style="white-space:normal;padding-bottom:1em;">' + _( 'imprintTitleCCBY30AT') + '</h1>';
		txt += '<p style="white-space:normal;font-weight:bold;">Vornamen in Niederösterreich</p><p style="white-space:normal;padding-left:2em;">Land Niederösterreich - data.noe.gv.at</p>';
		txt += '<p style="white-space:normal;font-weight:bold;">Bevölkerung - Vornamen</p><p style="white-space:normal;padding-left:2em;">Land Oberösterreich - data.ooe.gv.at</p>';
		txt += '<p style="white-space:normal;font-weight:bold;">Vornamen der Lebendgeborenen in der Steiermark</p><p style="white-space:normal;padding-left:2em;">Land Steiermark - data.steiermark.gv.at</p>';
		txt += '<p style="white-space:normal;font-weight:bold;">Top 100 Vornamen in Tirol</p><p style="white-space:normal;padding-left:2em;">Land Tirol - data.tirol.gv.at</p>';
		txt += '<p style="white-space:normal;font-weight:bold;">Vornamenstatistik Vorarlberg</p><p style="white-space:normal;padding-left:2em;">Land Vorarlberg - data.vorarlberg.gv.at</p>';
		txt += '<p style="height:.7em;padding:0;margin:0;"></p>';
//		txt += '<p style="white-space:normal;font-weight:bold;">...</p><p style="white-space:normal;padding-left:2em;">Stadt Graz - data.graz.gv.at</p>';
		txt += '<p style="white-space:normal;font-weight:bold;">Vornamensstatistik für Graz</p><p style="white-space:normal;padding-left:2em;">Stadt Graz - data.graz.gv.at</p>';
		txt += '<p style="white-space:normal;font-weight:bold;">Beliebteste Vornamen in Linz</p><p style="white-space:normal;padding-left:2em;">Stadt Linz - data.linz.gv.at</p>';
		txt += '<p style="white-space:normal;font-weight:bold;">Beliebteste Vornamen in der Stadt Salzburg</p><p style="white-space:normal;padding-left:2em;">Stadt Salzburg - data.stadt-salzburg.at</p>';
		txt += '<p style="white-space:normal;font-weight:bold;">Top 100 Vornamen in Wien - Zeitreihe</p><p style="white-space:normal;padding-left:2em;">Stadt Wien - data.wien.gv.at</p>';
		txt += '<p style="height:.7em;padding:0;margin:0;"></p>';
		txt += '<p style="white-space:normal;font-weight:bold;">Vornamen männlich und weiblich Engerwitzdorf</p><p style="white-space:normal;padding-left:2em;">Gemeinde Engerwitzdorf - data.engerwitzdorf.gv.at</p>';
		// AT: Bundesländer: Burgenland (angefragt), Kärnten (http://data.ktn.gv.at/), Salzburg (Portal kommt)
//		txt += '<p style="white-space:normal;font-weight:bold;">Liste der Päpste</p><p style="white-space:normal;padding-left:2em;">http://de.wikipedia.org/wiki/Liste_der_P%C3%A4pste</p>';
		txt += '</a></li>';
//		txt += '<li>';
//		txt += '<h1 style="white-space:normal;padding-bottom:1em;">' + _( 'imprintTitleStatAT') + '</h1>';
		// AT: Statistik Austria: Top ca.700 - 2011 (at), Top 60 - 1984,..,2011 (at), Top 60 - 2011 (at+9)
		// AT: Oberösterreich: Top 60 - 1984-2011 (at+9)
//		txt += '<p style="white-space:normal;font-weight:bold;">Vornamenstatistik Oberösterreich</p>';
//		txt += '<p style="white-space:normal;font-weight:bold;">Hitliste Österreich und Bundesländer 1984/2011</p>';
//		txt += '</li>';
//		txt += '<li><a href="#pageImprintWeb" data-rel="popup" data-position-to="window" data-role="button" data-theme="b" data-inline="true">';
//		txt += '<h1 style="white-space:normal;padding-bottom:1em;">' + _( 'imprintTitleCCBY30AT') + '</h1>';
//		txt += '<p style="white-space:normal;font-weight:bold;">Beliebte Vornamen für Neugeborene der Stadt Moers</p><p style="white-space:normal;padding-left:2em;">Datenlizenz Deutschland – Namensnennung – Version 1.0<br>Datenquelle: Stadt Moers - offenedaten.moers.de</p>';
//		txt += '</a></li>';

		txt += '<li data-role="list-divider">' + _( 'imprintTitleImage') + '</li>';
		txt += '<li style="white-space:normal;">';
		txt += '<h1>baby stickers</h1><p>© notkoo2008 - Fotolia.com</p>';
		txt += '</li>';

		txt += '<li data-role="list-divider">' + _( 'imprintTitleText') + '</li>';
		txt += '<li style="white-space:normal;">';
		txt += '<h1>' + _( 'imprintTitleWikipedia') + '</h1><p style="white-space:normal;">' + _( 'imprintBodyWikipedia') + '</p>';
		txt += '</li>';

		txt += '<li data-role="list-divider"></li>';

		$( "#h1Impress").html( _( 'appName'));
		$( "#ulImpress").html( txt);
		$( "#ulImpress").listview( 'refresh');
		$( "#impressBack").html( _( 'homeBack'));
		if( gShowIOS) {
			$( "#impressBack_").addClass( 'myBackButton');
			$( "#impressBack_").find( '.ui-icon').remove();
			$( "#impressBack_").append( '<div class="ios-tip"><span>&nbsp;</span></div>');
		}
		if( gShowWP) {
			$( "#impressBack_").css( 'display', 'none');
		}
	} catch( e) {
	}
});

$( '#pageImprint').live( "pageshow", function()
{
//	hueVoid();

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
	}
});

//----------------------------

function shakeEventBoy()
{
	try {
		if( gShakeBoy.length == 0) {
			for( var i = 0; i < gBoys.length; ++i) {
				gShakeBoy[ i] = i;
			}
			shuffleArray( gShakeBoy, gShakeBoy.length);
		}

		var shakeLen = parseInt( gShakeBoy.length * .30);
		shuffleArray( gShakeBoy, shakeLen);
		var pos = gShakeBoy[ 0];
		gShakeBoy.push( gShakeBoy.shift());

		if( gScreenshot) {
			pos = 6;
//			pos = 217;
		}

		var fav = 0;
		if( typeof gBoys[ pos].fav !== "undefined") {
			fav = gBoys[ pos].fav;
		}

		$( "#boyName").html( gBoys[ pos].name);
		$( "#boyNameA").attr("href", "#name?category=b&index=" + pos);
		$( "#boyFav").attr("src", "art/list-fav" + fav + ".png");
	} catch( e) {
	}
}

$( '#pageBoy').live( "pageinit", function()
{
	try {
		init();

		var txt = '';
		txt = '<li data-role="list-divider"><img src="art/list-fav0.png" id="boyFav" style="position:relative;right:0px;top:0px;float:right;margin:0;">' + _( 'resultBoy') + '</li>';
		txt += '<li data-icon="info" style="border-top:0; border-left: 1px solid rgb(69, 111, 154);border-right: 1px solid rgb(69, 111, 154);"><a href="#" id="boyNameA"><div id="boyName" style="text-align:center;font-size:4em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div></a></li>';
		txt += '<li data-role="list-divider">' + _( 'resultShake') + '</li>';

		$( "#h1Boy").html( _( 'appName'));
		$( "#boyShake").html( _( 'resultButtonShake'));
		$( "#ulBoy").html( txt);
		$( "#ulBoy").listview('refresh');
		$( "#aBoyShake" ).bind( "click", function( event, ui) { shakeEventBoy(); });
		$( "#boyBack").html( _( 'homeBack'));
		if( gShowIOS) {
			$( "#boyBack_").addClass( 'myBackButton');
			$( "#boyBack_").find( '.ui-icon').remove();
			$( "#boyBack_").append( '<div class="ios-tip"><span>&nbsp;</span></div>');
		}
		if( gShowWP) {
			$( "#boyBack_").css( 'display', 'none');
			$( "#aBoyShake").css( 'display', 'none');
		}
	} catch( e) {
	}
});

$( '#pageBoy').live( "pageshow", function()
{
//	hueBoy();

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
//		window.external.notify( "applicationBarAddButton:"+ _( 'homeSearchShort') +":art/win-search.png:gotoPage:pageSearch");
		window.external.notify( "applicationBarAddButton:"+ _( 'resultButtonShake') +":art/win-shake.png:shakeEventBoy:");
	}

	try {
//		if( navigator && navigator.accelerometer) {
//			shake.startWatch( shakeEventBoy);
//		} else {
			window.addEventListener( 'shake', shakeEventBoy, false);
//		}
		shakeEventBoy();
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}

	$( window).resize();
});

$( '#pageBoy').live( "pagehide", function()
{
	try {
//		if( navigator && navigator.accelerometer) {
//			shake.stopWatch();
//		} else {
			window.removeEventListener( 'shake', shakeEventBoy, false);
//		}
	} catch( e) {
	}
});

//----------------------------

function shakeEventGirl()
{
	try {
		if( gShakeGirl.length == 0) {
			for( var i = 0; i < gGirls.length; ++i) {
				gShakeGirl[ i] = i;
			}
			shuffleArray( gShakeGirl, gShakeGirl.length);
		}

		var shakeLen = parseInt( gShakeGirl.length * .30);
		shuffleArray( gShakeGirl, shakeLen);
		var pos = gShakeGirl[ 0];
		gShakeGirl.push( gShakeGirl.shift());

		if( gScreenshot) {
			pos = 38;
//			pos = 239;
		}

		var fav = 0;
		if( typeof gGirls[ pos].fav !== "undefined") {
			fav = gGirls[ pos].fav;
		}

		$( "#girlName").html( gGirls[ pos].name);
		$( "#girlNameA").attr("href", "#name?category=e&index=" + pos);
		$( "#girlFav").attr("src", "art/list-fav" + fav + ".png");
	} catch( e) {
	}
}

$( '#pageGirl').live( "pageinit", function()
{
	try {
		init();

		var txt = '';
		txt = '<li data-role="list-divider"><img src="art/list-fav0.png" id="girlFav" style="position:relative;right:0px;top:0px;float:right;margin:0;">' + _( 'resultGirl') + '</li>';
		txt += '<li data-icon="info" style="border-top:0; border-left: 1px solid rgb(154, 69, 111);border-right: 1px solid rgb(154, 69, 111);"><a href="#" id="girlNameA"><div id="girlName" style="text-align:center;font-size:4em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div></a></li>';
		txt += '<li data-role="list-divider">' + _( 'resultShake') + '</li>';

		$( "#h1Girl").html( _( 'appName'));
		$( "#girlShake").html( _( 'resultButtonShake'));
		$( "#ulGirl").html( txt);
		$( "#ulGirl").listview('refresh');
		$( "#aGirlShake" ).bind( "click", function( event, ui) { shakeEventGirl(); });
		$( "#girlBack").html( _( 'homeBack'));
		if( gShowIOS) {
			$( "#girlBack_").addClass( 'myBackButton');
			$( "#girlBack_").find( '.ui-icon').remove();
			$( "#girlBack_").append( '<div class="ios-tip"><span>&nbsp;</span></div>');
		}
		if( gShowWP) {
			$( "#girlBack_").css( 'display', 'none');
			$( "#aGirlShake").css( 'display', 'none');
		}
	} catch( e) {
	}
});

$( '#pageGirl').live( "pageshow", function()
{
//	hueGirl();

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
//		window.external.notify( "applicationBarAddButton:"+ _( 'homeSearchShort') +":art/win-search.png:gotoPage:pageSearch");
		window.external.notify( "applicationBarAddButton:"+ _( 'resultButtonShake') +":art/win-shake.png:shakeEventGirl:");
	}

	try {
//		if( navigator && navigator.accelerometer) {
//			shake.startWatch( shakeEventGirl);
//		} else {
			window.addEventListener( 'shake', shakeEventGirl, false);
//		}
		shakeEventGirl();
	} catch( e) {
	}

	$( window).resize();
});

$( '#pageGirl').live( "pagehide", function()
{
	try {
//		if( navigator && navigator.accelerometer) {
//			shake.stopWatch();
//		} else {
			window.removeEventListener( 'shake', shakeEventGirl, false);
//		}
	} catch( e) {
	}
});

//----------------------------

var gList = '';
var gListTheme = '';
var gListDiv = '';

function addSearchItem()
{
	try {
		var alt = '';
		var fav = 0;

		if( typeof this.fav !== 'undefined') {
			fav = this.fav;
		}

		if( typeof this.altM !== 'undefined') {
			$.each( this.altM, function( idx) {
				if( alt != '') {
					alt += ', ';
				}
				if( this.charCodeAt( 0) < 60) {
					if( fav < (this.charCodeAt( 0) - 48)) {
						fav = this.charCodeAt( 0) - 48;
					}
					alt += this.substr( 1);
				} else {
					alt += this;
				}
			});
		}
		if( typeof this.altF !== 'undefined') {
			$.each( this.altF, function( idx) {
				if( alt != '') {
					alt += ', ';
				}
				if( this.charCodeAt( 0) < 60) {
					if( fav < (this.charCodeAt( 0) - 48)) {
						fav = this.charCodeAt( 0) - 48;
					}
					alt += this.substr( 1);
				} else {
					alt += this;
				}
			});
		}
		if( alt != '') {
			alt = '<p class="ui-li-desc ui-li-searchalt">' + alt + '</p>';
		}

		var divChar = this.name.charAt( 0).toUpperCase();
		if( gListDiv != divChar) {
			gListDiv = divChar;
			gList += '<li class="ui-li ui-li-divider ui-bar-a">' + gListDiv + '</li>';
		}

		gList += '<li class="ui-btn ui-btn-icon-right ui-li-has-arrow ui-li ui-li-has-thumb ui-btn-up-c">'
			   + '<div class="ui-btn-inner ui-li">'
			   + '<div class="ui-btn-text">'
			   + '<a href="#name?category=' + this.sex + '&index=' + this.index + '" class="ui-link-inherit ui-li-poilink ui-li-iconbaby' + this.sex + '">'
			   + '<h3 class="ui-li-heading ui-li-searchhead">' + this.name + '</h3>'
			   + '<img src="art/list-fav' + fav + '.png" class="ui-li-favlink ui-li-searchfav">'
			   + alt
			   + '</a></div><span class="ui-icon ui-icon-arrow-r ui-icon-shadow">&nbsp;</span></div></li>';
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

$( '#pageSearch').live( "pageinit", function()
{
	try {
		init();

		$( "#h1Search").html( _( 'appName'));
		$( "#searchBack").html( _( 'homeBack'));
		if( gShowIOS) {
			$( "#searchBack_").addClass( 'myBackButton');
			$( "#searchBack_").find( '.ui-icon').remove();
			$( "#searchBack_").append( '<div class="ios-tip"><span>&nbsp;</span></div>');
		}
		if( gShowWP) {
			$( "#searchBack_").css( 'display', 'none');
		}
	} catch( e) {
	}
});

$( '#pageSearch').live( "pageshow", function()
{
//	hueVoid();

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
	}

	var startDate = new Date();
	var startMS = startDate.getMilliseconds();

	try {
		gList = '';
		gListDiv = '';
		var nameVec = [];

		$.each( gGirls, function( index) {
			var obj = this;
			obj.sex = 'e';
			obj.index = index;
			obj.cmp = obj.name.toUpperCase();
			nameVec.push( obj);
		});
		$.each( gBoys, function( index) {
			var obj = this;
			obj.sex = 'b';
			obj.index = index;
			obj.cmp = obj.name.toUpperCase();
			nameVec.push( obj);
		});
//		$.each( gFavs, function( index) {
//			var obj = this;
//			obj.sex = '';
//			obj.index = index;
//			obj.cmp = obj.name.toUpperCase();
//			nameVec.push( obj);
//		});

		nameVec.sort( function( left, right) {
			return left.cmp.localeCompare( right.cmp);
		});

		$.each( nameVec, addSearchItem);

		$( "#searchContent").html( gList);
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}

	var endDate = new Date();
	var endMS = endDate.getMilliseconds();
	if( gDebug) {
		var str = (endDate - startDate) + 'ms';
//		str += ' => ' + (endDate - startDate - 850) + 'ms';
//		str += ' (' + parseInt((endDate - startDate) / 8.50) + '%)';
		$( "#h1Search").html( str);
	}
});

//----------------------------

function addFavItem( index)
{
	try {
		var index = this.idx;
		var category = this.cat;
		if( typeof this.alt !== "undefined") {
			index = this.alt.idx;
			category = this.alt.cat;
		}

		gList += "<li><a href='#name?category=" + category + "&index=" + index;

		if( typeof this.alt !== "undefined") {
			gList += "&refcategory=" + this.cat + "&refindex=" + this.idx;
		}

		var fav = 0;
		if( typeof this.fav !== "undefined") {
			fav = this.fav;
		}

		gList += "' class='ui-li-poilink'>"
			   + "<h3 style='margin-top:0;'>" + this.name + "</h3>"
			   + "<img src='art/list-baby" + category + ".png' class='ui-li-poiicon'>"
			   + "<img src='art/list-fav" + fav + ".png' class='ui-li-favlink'>"
			   + "</a></li>";
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

$( '#pageFav').live( "pageinit", function()
{
	try {
		init();

		$( "#h1Fav").html( _( 'appName'));
		$( "#favBack").html( _( 'homeBack'));
		if( gShowIOS) {
			$( "#favBack_").addClass( 'myBackButton');
			$( "#favBack_").find( '.ui-icon').remove();
			$( "#favBack_").append( '<div class="ios-tip"><span>&nbsp;</span></div>');
		}
		if( gShowWP) {
			$( "#favBack_").css( 'display', 'none');
		}
	} catch( e) {
	}
});

$( '#pageFav').live( "pageshow", function()
{
//	hueVoid();

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
		window.external.notify( "applicationBarAddButton:"+ _( 'homeSearchShort') +":art/win-search.png:gotoPage:pageSearch");
	}

	try {
		gList = "";

		$.each( gFavs, addFavItem);

		$( "#favContent").html( gList);

		var mylist = $( "#favContent");
		var listitems = mylist.children( 'li').get();
		listitems.sort( function( a, b) {
			return $( a).text().toUpperCase().localeCompare( $( b).text().toUpperCase());
		});
		$.each( listitems, function( idx, itm) {
			mylist.append( itm);
		});

		$( "#favContent").listview('refresh');
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
});

//----------------------------

function onFav( imgFavId, favorite, category, index, refcat, refindex)
{
	for( var i = 1; i <= 5; ++i) {
		if( i <= favorite) {
			$( "#" + imgFavId + i).attr( "src", "art/list-favset.png");
		} else {
			$( "#" + imgFavId + i).attr( "src", "art/list-favblank.png");
		}
	}

	var item;
	if( refcat == "undefined") {
		var item = {
			name: '',
			fav: favorite,
			cat: category,
			idx: index
		};
	} else {
		var item = {
			name: '',
			fav: favorite,
			cat: refcat,
			idx: refindex,
			alt: {
				cat: category,
				idx: index
			}
		};
	}

	if( typeof item.alt !== "undefined") {
		if( "b" == item.cat) {
			refitem = gBoys[ item.idx];
		} else {
			refitem = gGirls[ item.idx];
		}
		if( "b" == item.alt.cat) {
			item.name = refitem.altM[ item.alt.idx];
		} else {
			item.name = refitem.altF[ item.alt.idx];
		}
		if( item.name.charCodeAt( 0) < 60) {
			item.name = item.name.substr( 1);
		}
	} else if( "b" == category) {
		item.name = gBoys[ item.idx].name;
	} else {
		item.name = gGirls[ item.idx].name;
	}

	saveFavToGlobalVecs( item);
	saveFavToStorage( item);
}

function echoFav( item)
{
	if( typeof item.alt !== "undefined") {
		alert(
			'Name: ' + item.name + "\n" +
			'Fav: '  + item.fav + "\n" +
			'Cat: '  + item.cat + "\n" +
			'Index: '+ item.idx + "\n" +
			'Alt cat: '   + item.alt.cat + "\n" +
			'Alt index: ' + item.alt.idx);
	} else {
		alert(
			'Name: ' + item.name + "\n" +
			'Fav: '  + item.fav + "\n" +
			'Cat: '  + item.cat + "\n" +
			'Index: '+ item.idx + "\n");
	}
}

function saveFavToGlobalVecs( item)
{
	try {
		if( typeof item.alt !== "undefined") {
			if( "b" == item.cat) {
				refitem = gBoys[ item.idx];
			} else {
				refitem = gGirls[ item.idx];
			}
			if( "b" == item.alt.cat) {
				if( item.fav > 0) {
					refitem.altM[ item.alt.idx] = item.fav + item.name;
				} else {
					refitem.altM[ item.alt.idx] = item.name;
				}
			} else {
				if( item.fav > 0) {
					refitem.altF[ item.alt.idx] = item.fav + item.name;
				} else {
					refitem.altF[ item.alt.idx] = item.name;
				}
			}
		} else if( "b" == item.cat) {
			gBoys[ item.idx].fav = item.fav;
		} else {
			gGirls[ item.idx].fav = item.fav;
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

function saveFavToStorage( item)
{
	var itemAlt = false;
	if( typeof item.alt !== "undefined") {
		itemAlt = true;
	}

	var found = -1;
	$.each( gFavs, function( index) {
		var thisAlt = false;
		if( typeof this.alt !== "undefined") {
			thisAlt = true;
		}

		if(( this.idx == item.idx) && (this.cat == item.cat) && (thisAlt == itemAlt)) {
			if( thisAlt) {
				if(( this.alt.idx == item.alt.idx) && (this.alt.cat == item.alt.cat)) {
					found = index;
				}
			} else {
				found = index;
			}
		}
	});

	if( found == -1) {
		if( item.fav > 0) {
			gFavs.push( item);
		}
	} else {
		if( item.fav > 0) {
			gFavs[ found] = item;
		} else {
			gFavs.splice( found, 1);
		}
	}

	saveFavorites();
}

function onName( urlObj, options)
{
//	var category = urlObj.hash.replace( /.*category=/, "").substr( 0, 1);
//	var index = urlObj.hash.replace( /.*index=/, "");

	var category = urlObj.hash.split( 'category=')[1]; category = category && category.split( '&')[0];
	var index = urlObj.hash.split( 'index=')[1]; index = index && index.split( '&')[0];
	var refcat = urlObj.hash.split( 'refcategory=')[1]; refcat = refcat && refcat.split( '&')[0];
	var refindex = urlObj.hash.split( 'refindex=')[1]; refindex = refindex && refindex.split( '&')[0];
	var pageSelector = urlObj.hash.replace( /\?.*$/, "" );
	var favorite = 0;
	var nextPage = '#name2';
	var imgFavId = 'imgFav' + category;
//var pagePopUp = '';
//var pagePopUpUrl = '';

	if( '#name1' == pageSelector) {
		pageSelector = '#pageName' + category + '1';
		imgFavId += '1';
	} else if( '#name2' == pageSelector) {
		pageSelector = '#pageName' + category + '2';
		nextPage = '#name1';
		imgFavId += '2';
	} else {
		pageSelector = '#pageName' + category + '1';
		imgFavId += '1';
	}

	var $page = $( pageSelector);
	var $header = $page.children( ":jqmData(role=header)");
	var $content = $page.children( ":jqmData(role=content)");

	$header.find( "h1").html( _( 'appName'));
	$header.find( "span.nameBack").html( _( 'homeBack'));

	var txt = '';
	try {
		var item = null;
		if( typeof refcat !== "undefined") {
			if( "b" == refcat) {
				refitem = gBoys[ refindex];
			} else if( "e" == refcat) {
				refitem = gGirls[ refindex];
//			} else {
//				refitem = gFavs[ refindex];
			}
			if( "b" == category) {
				item = refitem.altM[ index];
			} else {
				item = refitem.altF[ index];
			}
			if( item.charCodeAt( 0) < 60) {
				favorite = item.charCodeAt( 0) - 48;
				item = item.substr( 1);
			}
		} else if( "b" == category) {
			item = gBoys[ index];
			if( typeof item.fav !== "undefined") {
				favorite = item.fav;
			}
		} else if( "e" == category) {
			item = gGirls[ index];
			if( typeof item.fav !== "undefined") {
				favorite = item.fav;
			}
//		} else {
//			item = gFavs[ index];
		}

		if( typeof refcat !== "undefined") {
			$header.find( "h1").html( item);
			txt += '<h1>' + item + '</h1>';
			txt += '<p>' + _( 'detailGenericReference').replace(/%name%/g,item).replace(/%variant%/g,refitem.name) + '</p>';
		} else {
			$header.find( "h1").html( item.name);

			txt += '<h1>' + item.name + '</h1>';

			if(( typeof item.text === "undefined") || ('de-DE' != CInternationalization.lang_)){
				if( "b" == category) {
					txt += '<p>' + _( 'detailGenericBoy').replace(/%name%/g,item.name) + '</p>';
				} else if( "e" == category) {
					txt += '<p>' + _( 'detailGenericGirl').replace(/%name%/g,item.name) + '</p>';
				}
			} else {
				txt += '<p>' + item.text + '</p>';
				if(( typeof item.url !== "undefined") && (item.url != "")) {
					gWebViewTitle = item.name;
					gWebViewHref = item.url;
//++gPopUpNum;
//pagePopUp = 'pagePopup' + gPopUpNum + 'Web';
//pagePopUpUrl = item.url;
//txt += '<p style="text-align:right;"><a href="#' + pagePopUp + '" data-rel="popup" data-position-to="window" data-role="button" data-icon="arrow-r" data-iconpos="right" data-inline="true">' + _( 'detailMore') + '</a></p>';
					txt += '<p style="text-align:right;"><a href="#pageWebView" data-rel="dialog" data-role="button" data-icon="arrow-r" data-iconpos="right" data-inline="true">' + _( 'detailMore') + '</a></p>';
//txt += '<div data-role="popup" id="' + pagePopUp + '" data-overlay-theme="a" data-theme="' + category + '" data-corners="false" data-tolerance="15,15" class="ui-content">';
//txt += '<a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>';
//txt += '<iframe src="about:blank" width="100" height="100" seamless></iframe>';
//txt += '</div>';
				}
			}
		}

		txt += '<div class="ui-listview ui-listview-inset ui-corner-all ui-shadow">';
		txt += '<div class="ui-li ui-li-divider ui-bar-' + category + ' ui-corner-top">' + _( 'detailFav') + '</div>';
		txt += '<div class="ui-li ui-li-static ui-btn-up-d ui-corner-bottom"><center><span>';
		txt += '<a href="#" onClick="onFav(\''+imgFavId+'\',0,\''+category+'\','+index+',\''+refcat+'\','+refindex+');" border=0><img src="art/list-favno.png" border=0 style="border:0;width:40px;max-width:14%;margin-right:2em"></a>';
		for( var i = 1; i <= 5; ++i) {
			if( i <= favorite) {
				txt += '<a href="#" onClick="onFav(\''+imgFavId+'\',' + i + ',\''+category+'\','+index+',\''+refcat+'\','+refindex+');" border=0><img src="art/list-favset.png" id="' + imgFavId + i + '" border=0 style="border:0;width:40px;max-width:14%;"></a>';
			} else {
				txt += '<a href="#" onClick="onFav(\''+imgFavId+'\',' + i + ',\''+category+'\','+index+',\''+refcat+'\','+refindex+');" border=0><img src="art/list-favblank.png" id="' + imgFavId + i + '" border=0 style="border:0;width:40px;max-width:14%;"></a>';
			}
		}
		txt += '</span></center></div>';
		txt += '</div>';

		gList = "";
		if( typeof refcat !== "undefined") {
			var fav = 0;
			if( typeof refitem.fav !== "undefined") {
				fav = refitem.fav;
			}

			gList += "<li><a href='" + nextPage + "?category=" + refcat + "&index=" + refindex
				   + "' class='ui-li-poilink'>"
				   + "<h3 style='margin-top:0;'>" + refitem.name + "</h3>"
		 		   + "<img src='art/list-baby" + refcat + ".png' class='ui-li-poiicon'>"
				   + "<img src='art/list-fav" + fav + ".png' class='ui-li-favlink'>"
				   + "</a></li>";
		}
		if( typeof item.altM !== "undefined") {
			var copyAlt = [];
			$.each( item.altM, function( index_) {
				var fav_ = 0;
				var name_ = this;
				if( name_.charCodeAt( 0) < 60) {
					fav_ = name_.charCodeAt( 0) - 48;
					name_ = name_.substr( 1);
				}

				copyAlt.push({
					index: index_,
					name: name_,
					fav: fav_
				});
			});
			copyAlt.sort( function( left, right) {
				return (left.name > right.name) ? 1 : -1;
			});
			$.each( copyAlt, function( index_) {
				var showLink = false;
				var linkIndex = this.index;
				var fav = this.fav;
				var altName = this.name;

				$.each( gBoys, function( index__) {
					if( this.name == altName) {
						linkIndex = index__;
						if( typeof this.fav !== "undefined") {
							fav = this.fav;
						}
						showLink = true;
					}
				});

				gList += "<li><a href='" + nextPage + "?category=b&index=" + linkIndex;
				if( !showLink) {
					gList += "&refcategory=" + category + "&refindex=" + index;
				}
				gList += "' class='ui-li-poilink'>"
					   + "<h3 style='margin-top:0;'>" + altName + "</h3>"
			 		   + "<img src='art/list-babyb.png' class='ui-li-poiicon'>"
					   + "<img src='art/list-fav" + fav + ".png' class='ui-li-favlink'>"
					   + "</a></li>";
			});
		}
		if( typeof item.altF !== "undefined") {
			var copyAlt = [];
			$.each( item.altF, function( index_) {
				var fav_ = 0;
				var name_ = this;
				if( name_.charCodeAt( 0) < 60) {
					fav_ = name_.charCodeAt( 0) - 48;
					name_ = name_.substr( 1);
				}

				copyAlt.push({
					index: index_,
					name: name_,
					fav: fav_
				});
			});
			copyAlt.sort( function( left, right) {
				return (left.name > right.name) ? 1 : -1;
			});
			$.each( copyAlt, function( index_) {
				var showLink = false;
				var linkIndex = this.index;
				var fav = this.fav;
				var altName = this.name;

				$.each( gGirls, function( index__) {
					if( this.name == altName) {
						linkIndex = index__;
						if( typeof this.fav !== "undefined") {
							fav = this.fav;
						}
						showLink = true;
					}
				});

				gList += "<li><a href='" + nextPage + "?category=e&index=" + linkIndex;
				if( !showLink) {
					gList += "&refcategory=" + category + "&refindex=" + index;
				}
				gList += "' class='ui-li-poilink'>"
					   + "<h3 style='margin-top:0;'>" + altName + "</h3>"
			 		   + "<img src='art/list-babye.png' class='ui-li-poiicon'>"
					   + "<img src='art/list-fav" + fav + ".png' class='ui-li-favlink'>"
					   + "</a></li>";
			});
		}
		if( "" != gList) {
			gList = '<li data-role="list-divider">' + _( 'detailAlternate') + '</li>' + gList;
			txt += '<ul data-inset="true" data-role="listview" data-theme="d" data-dividertheme="' + category + '">' + gList + '</ul>';
		}

		gList = "";
		if( typeof item.ref !== "undefined") {
			var copyRef = [];
			$.each( item.ref, function() {
				copyRef.push({
					pos: this.pos,
					text: gSource[ this.source][ CInternationalization.lang_][ this.subsource].replace(/%pos%/g,this.pos).replace(/%year%/g,this.year)
				});
			});
			copyRef.sort( function( left, right) {
				return (left.pos > right.pos) ? 1 :
				       (left.pos == right.pos) ?
				       (left.text > right.text) ? 1 : -1 : -1;
			});
			$.each( copyRef, function() {
				gList += "<li><p style='white-space:normal;'>"
					   + this.text
					   + "</p></li>";
			});
		}
		if( "" != gList) {
			gList = '<li data-role="list-divider">' + _( 'detailSpread') + '</li>' + gList;
			txt += '<ul data-inset="true" data-role="listview" data-theme="d" data-dividertheme="' + category + '">' + gList + '</ul>';
		}

		$content.html( txt);
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}

	$page.page();
	$content.find( ":jqmData(role=listview)").listview();
	$content.find( ":jqmData(role=button)").button();
	$content.find( ":jqmData(role=popup)").popup();

	if( gShowIOS && !$header.find( "a:first").hasClass( 'myBackButton')) {
		$header.find( "a:first").addClass( 'myBackButton');
		$header.find( "a:first").find( '.ui-icon').remove();
		$header.find( "a:first").append( '<div class="ios-tip"><span>&nbsp;</span></div>');
	}
	if( gShowWP) {
		$header.find( "a").css( 'display', 'none');
	}

/*if(( pagePopUp != '') && !$( "#" + pagePopUp + " iframe").hasClass( 'myDone')) {
$( "#" + pagePopUp + " iframe").addClass( 'myDone');

$( "#" + pagePopUp + " iframe").attr( 'width', 0).attr( 'height', 0);
$( "#" + pagePopUp).on({
popupbeforeposition: function() {
var size = scale( 10000, 10000, 15, 1);
var w = size.width;
var h = size.height;

$( "#" + pagePopUp + " iframe").attr( 'width', w).attr( 'height', h).get(0).contentWindow.location.replace( pagePopUpUrl);
},
popupafterclose: function() {
$( "#" + pagePopUp + " iframe").attr( 'width', 0).attr( 'height', 0);
}
});
}*/

	options.dataUrl = urlObj.href;

	$.mobile.changePage( $page, options);
}

$( '#pageNameb1').live( "pageshow", function()
{
//	hueBoy();

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
	}
});

$( '#pageNameb2').live( "pageshow", function()
{
//	hueBoy();

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
	}
});

$( '#pageNamee1').live( "pageshow", function()
{
//	hueGirl();

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
	}
});

$( '#pageNamee2').live( "pageshow", function()
{
//	hueGirl();

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
	}
});

//----------------------------

$(document).on( "pageshow", '#pageWebView', function()
{
	try {
		$( "#pageWebView h1").html( gWebViewTitle);
		$( "#pageWebView iframe").get( 0).contentWindow.location.replace( gWebViewHref);

		$( window).resize();
	} catch( e) {
		if( gDebug) {
			alert( e);
		}
	}
});

$(document).on( "pagehide", '#pageWebView', function()
{
	try {
		$( "#pageWebView h1").html( "&nbsp;");
		$( "#pageWebView iframe").get( 0).contentWindow.location.replace( "about:blank");
	} catch( e) {
	}
});

//----------------------------
// eof

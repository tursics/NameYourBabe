//----------------------------
//---       index.js       ---
//----------------------------

// http://christian-helle.blogspot.de/2012/03/integrating-html5-and-javascript-with.html
// http://stackoverflow.com/questions/14630765/back-button-on-windows-phone-8

gSettingsFavs = [];
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

var gFeature = {
	canvas: true
};

//----------------------------

$( document).ready( function()
{
//	CInternationalization.init();

	$( window).resize( onResize);
});

//----------------------------

//$.extend( $.mobile,
//{
//	hashListeningEnabled: false
//});

//----------------------------

//$( document ).one( "mobileinit", function()
$( document).bind( "mobileinit", function()
{
	$.support.cors = true;
	$.mobile.allowCrossDomainPages = true;
//	$.fn.buttonMarkup.defaults.corners = false;
	$.mobile.listview.prototype.options.filterPlaceholder = _( 'searchPlaceholder');
	$.mobile.fixedToolbars.setTouchToggleEnabled( false);
});

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

		loadSettings();
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

//----------------------------

function onResize()
{
	onPageHomeResize();

	$( "#pageWebView iframe").css({
		height: $( window).height() - $( '#pageWebViewHeader').outerHeight(),
		width: $( window).width() * .995
	});
}

//----------------------------

function gotoPage( pageName)
{
	$.mobile.changePage( "#" + pageName);
}

//----------------------------

$(document).on( "pageshow", '#pageWebView', function()
{
	try {
		$( "#pageWebView h1").html( gWebViewTitle);
		$( "#pageWebView iframe").get( 0).contentWindow.location.replace( gWebViewHref);

		onResize();
	} catch( e) {
		if( gDebug) {
			alert( e);
		}
	}
});

//----------------------------

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

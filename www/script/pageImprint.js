//----------------------------
// pageCanvas.js
//----------------------------

//----------------------------

function onPageImprintHome()
{
	gWebViewTitle = _( 'imprintWebHome');
	gWebViewHref = 'http://tursics.de';
}

//----------------------------

function onPageImprintSupport()
{
	gWebViewTitle = _( 'imprintWebSupport');
	gWebViewHref = 'http://tursics.de/support';
}

//----------------------------

$( document).on( 'pageshow', '#pageImprint',  function()
{
	try {
		init();

		var txt = '';
		txt += '<h3>' + _( 'appName') + '</h3>';
		txt += '<p>' + _( 'homeSub') + '</p>';

		txt += '<ul data-role="listview" data-theme="a">';
		txt += '<li data-role="list-divider">&nbsp;</li>';
		if( gShowIOS) {
			var lang = (navigator.language) ? navigator.language : navigator.userLanguage; 
			lang = lang.toLowerCase();
			lang = lang.substr( 0, 2);
			if( 'uk' == lang) {
				lang = 'gb';
			} else if( 'en' == lang) {
				lang = 'us';
			} else if( 'zh' == lang) {
				lang = 'cn';
			} else if( 'vi' == lang) {
				lang = 'vn';
			} else if( 'sv' == lang) {
				lang = 'se';
			}
			txt += '<li data-icon="false" class="afterDivider"><a href="https://itunes.apple.com/' + lang + '/app/name-your-babe/id604279644?mt=8&uo=4">' + _( 'imprintTitleRateMe') + '</div></a></li>';
		} else {
//			txt += '<li data-icon="false" class="afterDivider"><a href="market://details?id=de.tursics.nameyourbabe">' + _( 'imprintTitleRateMe') + '</div></a></li>';
//		} else {
//			http://msdn.microsoft.com/de-de/library/windows/apps/hh394017%28v=vs.105%29.aspx
//		} else {
//			http://msdn.microsoft.com/de-DE/library/windows/apps/hh974767.aspx
		}
		txt += '<li data-icon="false"><a href="#pageWebView" data-rel="dialog" data-role="button" data-theme="b" data-inline="true" onClick="onPageImprintHome();return true;">' + _( 'imprintWebHome') + '</div></a></li>';
		txt += '<li data-icon="false"><a href="#pageWebView" data-rel="dialog" data-role="button" data-theme="b" data-inline="true" onClick="onPageImprintSupport();return true;">' + _( 'imprintWebSupport') + '</div></a></li>';
		txt += '<li data-icon="false"><a href="#pageCopyright">' + _( 'imprintTitleData') + '</div></a></li>';
		txt += '</ul><br>';

		txt += '<p>' + _( 'imprintCopyright') + '</p>';

		$( '#divImprint').html( txt);
		$( '#divImprint').trigger( "create");
		$( '#divImprint').trigger( 'updatelayout');

		$( "#h1Imprint").html( _( 'homeImprint'));
		$( "#imprintBack").html( '<img src="art/tabbar-ios-7-back.png" style="height:1.6em;margin-left:-1em;padding-right:2em;">');
		if( gShowWP) {
			$( "#imprintBack_").css( 'display', 'none');
		}
	} catch( e) {
		if( gShowWP) {
			window.external.notify( "applicationBarClear");
		}
	}

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
	}
});

//----------------------------
// eof

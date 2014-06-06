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
		txt += '<li data-icon="false" class="afterDivider"><a href="#pageWebView" data-rel="dialog" data-role="button" data-theme="b" data-inline="true" onClick="onPageImprintHome();return true;">' + _( 'imprintWebHome') + '</div></a></li>';
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

//----------------------------
// pageCopyright.js
//----------------------------

function onPageCopyright( name)
{
	gWebViewTitle = name;
	gWebViewHref = 'about:blank';

	if( 'CC BY 3.0 AT' == name) {
		gWebViewHref = 'https://creativecommons.org/licenses/by/3.0/at/deed.de';
	} else if( 'CC BY 3.0 DE' == name) {
		gWebViewHref = 'http://creativecommons.org/licenses/by/3.0/de/';
	} else if( 'CC0 1.0' == name) {
		gWebViewHref = 'http://creativecommons.org/publicdomain/zero/1.0/';
	} else if( 'DL DE BY 1.0' == name) {
		gWebViewHref = 'http://www.daten-deutschland.de/bibliothek/Datenlizenz_Deutschland/dl-de-by-1.0/';
	}
}

//----------------------------

$( document).on( 'pageshow', '#pageCopyright',  function()
{
	try {
		init();

		var txt = '';
		txt += '<ul data-role="listview" data-theme="a">';

		var license = [];
		var citation = [];

		license.push( 'CC0 1.0');
		citation.push( []);
		license.push( 'CC BY 3.0 AT');
		citation.push( []);
		license.push( 'CC BY 3.0 DE');
		citation.push( []);

		for( var ds = 0; ds < gDataSource.length; ++ds) {
			var strLicense = gDataSource[ ds]['license'];
			if( strLicense != '') {
				if( strLicense == 'CC BY') {
					strLicense = 'CC BY 3.0 DE';
				}

				var idx = license.indexOf( strLicense);
				if( 0 <= idx) {
					if( 0 > citation[ idx].indexOf( gDataSource[ ds]['citation'])) {
						citation[ idx].push( gDataSource[ ds]['citation']);
					}
				} else {
					license.push( strLicense);
					citation.push( [gDataSource[ ds]['citation']]);
				}
			}
		}


		txt += '<li data-role="list-divider"><br>'+_( 'imprintTitleData')+'</li>';
		for( var l = 0; l < license.length; ++l) {
			txt += '<li data-icon="false"';
			if( 0 == l) {
				txt += 'class="afterDivider"';
			}
			txt += '><a href="#pageWebView" data-rel="dialog" data-role="button" data-theme="b" data-inline="true" onClick="onPageCopyright(\''+license[l]+'\');return true;">';
			txt += license[l] + '<div class="small" style="padding-left:1em;white-space:normal;">';
			for( var c = 0; c < citation[l].length; ++c) {
				txt += citation[l][c] + '<br>';
			}
			txt += '</div>';
			txt += '</a></li>';
		}

		txt += '<li data-role="list-divider"><br>' + _( 'imprintTitleText') + '</li>';
		txt += '<li data-icon="false" class="afterDivider" style="font-weight:500;">';
		txt += _( 'imprintTitleWikipedia') + '<p class="small" style="white-space:normal;">' + _( 'imprintBodyWikipedia') + '</p>';
		txt += '</li>';

		txt += '</ul>';

		$( '#divCopyright').html( txt);
		$( '#divCopyright').trigger( "create");
		$( '#divCopyright').trigger( 'updatelayout');

		$( "#h1Copyright").html( _( 'canvasCopyright'));
		$( "#copyrightBack").html( '<img src="art/tabbar-ios-7-back.png" style="height:1.6em;margin-left:-1em;padding-right:2em;">');
		if( gShowWP) {
			$( "#copyrightBack_").css( 'display', 'none');
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
	}
});

//----------------------------
// eof

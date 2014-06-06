//----------------------------
// pageCanvas.js
//----------------------------

function initCanvas( buttonName, canvasName)
{
	if( gFeature.canvas) {
		txt = '';

		txt += '<ul data-role="listview" data-theme="a">';

		txt += '<li data-role="list-divider"><br>' + _( 'canvasFilter') + '</li>';
		txt += '<li data-icon="false" class="afterDivider"><a href="#pageFilter" class="ui-li-poilink"><img src="art/list-logo' + gSettings.filterGender + '.png" class="ui-li-poiicon">';
		if( 'm' == gSettings.filterGender) {
			txt += _( 'homeBoy');
		} else if( 'f' == gSettings.filterGender) {
			txt += _( 'homeGirl');
		} else {
			txt += _( 'homeBoyGirl');
		}
		txt += '<br>';
		if( '' == gSettings.filterNUTS) {
			txt += _( 'filterWorld');
		} else {
			var max = gDataSource.length;
			for( var i = 0; i < max; ++i) {
				if( gSettings.filterNUTS == gDataSource[i].nuts) {
					txt += gDataSource[i].name[CInternationalization.lang_];
					break;
				}
			}
		}
		txt += '</a></li>';

		txt += '<li data-role="list-divider">&nbsp;</li>';
		txt += '<li data-icon="false" class="afterDivider"><a href="#pageFavorites" class="ui-li-poilink"><img src="art/tabbar-ios-7-star.png" class="ui-li-poiicon">' + _( 'homeFav') +  '</a></li>';
		txt += '<li data-icon="false"><a href="#pageSearch"    class="ui-li-poilink"><img src="art/tabbar-ios-7-search.png" class="ui-li-poiicon">' + _( 'homeSearch') + '</span></a></li>';

//		txt += '<li data-icon="false"><a href="#pageShare">' + _( 'homeShare') + '</a></li>';
//		txt += '<li data-icon="false"><a href="#pageRate">' + _( 'homeRate') + '</a></li>';
//		txt += '<li data-icon="false"><a href="#pageHelp">' + _( 'canvasHelp') + '</a></li>';
		txt += '<li data-role="list-divider">&nbsp;</li>';
		txt += '<li data-icon="false" class="afterDivider"><a href="#pageSettings">' + _( 'canvasSettings') + '</a></li>';

		txt += '<li data-role="list-divider">&nbsp;</li>';
		txt += '<li data-icon="false" class="afterDivider"><a href="#pageImprint" class="ui-li-poilink"><img src="art/tabbar-ios-7-info.png" class="ui-li-poiicon">' + _( 'homeImprint') + '</a></li>';
		txt += '<li data-icon="false"><a href="#pageCopyright" class="ui-li-poilink"><img src="art/tabbar-ios-7-copyright.png" class="ui-li-poiicon">' + _( 'canvasCopyright') + '</a></li>';

		txt += '</ul>';

		$( '#'+canvasName).html( txt);
		$( '#'+canvasName).trigger( "create");
		$( '#'+canvasName).trigger( 'updatelayout');
	} else {
		$( '#'+buttonName).css( 'display', 'none');
	}
}

//----------------------------
// eof

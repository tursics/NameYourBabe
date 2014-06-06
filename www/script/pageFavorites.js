//----------------------------
// pageFavorites.js
//----------------------------

var gTemp = '';

//----------------------------

function onMarkFavorite( imgFavId, favorite, favName)
{
	for( var i = 1; i <= 5; ++i) {
		if( i <= favorite) {
			$( "#" + imgFavId + i).attr( "src", "art/list-favset.png");
		} else {
			$( "#" + imgFavId + i).attr( "src", "art/list-favblank.png");
		}
	}

	var item = {
		name: favName,
		fav: favorite
	};

	saveFavToStorage( item);
}

//----------------------------

function addFavItem()
{
	try {
		var fav = 0;
		if( typeof this.fav !== "undefined") {
			fav = this.fav;
		}

		var gender = 'b';
		var max = gDataName.length;
		for( var i = 0; i < max; ++i) {
			if( this.name == gDataName[i]['name']) {
				gender = gDataName[i]['gender'];
				break;
			}
		}

		gTemp += '<li data-icon="false"><a href="#name?given=' + this.name
			+ '" class="ui-li-poilink"><img src="art/list-logo' + gender + '.png" class="ui-li-poiicon">' + this.name
			+ '<span class="ui-li-count">'+'&#9733;&#9733;&#9733;&#9733;&#9733;'.substr(0,fav*7)+'&#9734;&#9734;&#9734;&#9734;&#9734;'.substr(fav*7)+'</span></a></li>';
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

//----------------------------

$( document).on( 'pageshow', '#pageFavorites',  function()
{
	try {
		init();

		var txt = '';

		if( 0 == gSettingsFavs.length) {
			txt += '<div style="padding:2em 2em 0 2em;">' + _( 'favoritesEmpty') + '</div>';
		} else {
			txt += '<ul data-role="listview" data-theme="a" style="margin-top:-1em;">';

			gSettingsFavs.sort( function( a,b) {
				if( a.fav == b.fav) {
					return a.name.localeCompare( b.name);
				}
				return a.fav < b.fav;
			});

			gTemp = '';
			$.each( gSettingsFavs, addFavItem);
			txt += gTemp;

			txt += '</ul>';
		}

		$( '#favContent').html( txt);
		$( '#favContent').trigger( "create");
		$( '#favContent').trigger( 'updatelayout');

		$( "#h1Fav").html( _( 'homeFav'));
		$( "#favBack").html( '<img src="art/tabbar-ios-7-back.png" style="height:1.6em;margin-left:-1em;padding-right:2em;">');
		if( gShowWP) {
			$( "#favBack_").css( 'display', 'none');
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
		window.external.notify( "applicationBarAddButton:"+ _( 'homeSearchShort') +":art/win-search.png:gotoPage:pageSearch");
	}
});

//----------------------------
// eof

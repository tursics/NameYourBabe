//----------------------------
// pageName.js
//----------------------------

$( document).bind( "pagebeforechange", function( e, data) {
	if( typeof data.toPage === "string") {
		var u = $.mobile.path.parseUrl( data.toPage);
		var re = /^#name/;
		if( u.hash.search(re) !== -1) {
			onGotoName( u, data.options);
			e.preventDefault();
		}
	}
});

//----------------------------

function onGotoName( urlObj, options)
{
	var name = urlObj.hash.split( 'given=')[1]; name = name && name.split( '&')[0];
	var pageSelector = urlObj.hash.replace( /\?.*$/, "" );
	var favorite = 0;
	var nextPage = '#name2';
	var imgFavId = 'imgFav';

	if( '#name1' == pageSelector) {
		pageSelector = '#pageName1';
		imgFavId += '1';
	} else if( '#name2' == pageSelector) {
		pageSelector = '#pageName2';
		nextPage = '#name1';
		imgFavId += '2';
	} else {
		pageSelector = '#pageName1';
		imgFavId += '1';
	}

	var $page = $( pageSelector);
	var $header = $page.children( ":jqmData(role=header)");
	var $content = $page.children( ":jqmData(role=content)");

	$header.find( "h1").html( _( 'appName'));
	$header.find( "span.nameBack").html( '&nbsp;');

	var txt = '';
	try {
		var item = null;

		var max = gDataName.length;
		for( var i = 0; i < max; ++i) {
			if( name == gDataName[i].name) {
				item = gDataName[i];
				break;
			}
		}

		max = gSettingsFavs.length;
		for( var i = 0; i < max; ++i) {
			if( name == gSettingsFavs[i].name) {
				if( typeof gSettingsFavs[i].fav !== "undefined") {
					favorite = gSettingsFavs[i].fav;
				}
			}
		}

		$header.find( "h1").html( item.name);

		txt += '<img src="art/list-logo' + item.gender + '.png" style="width:40px;height:40px;display:inline;float:right;">';
		txt += '<h3 style="margin:0;">' + item.name + '</h3>';

		if(( typeof item.text === "undefined") || ( item.text == "") || ('de-DE' != CInternationalization.lang_)) {
			if( 'm' == item.gender) {
				txt += '<p>' + _( 'detailGenericBoy').replace(/%name%/g,item.name) + '</p>';
			} else if( 'f' == item.gender) {
				txt += '<p>' + _( 'detailGenericGirl').replace(/%name%/g,item.name) + '</p>';
			} else {
				txt += '<p>' + _( 'detailGenericBoth').replace(/%name%/g,item.name) + '</p>';
			}
		} else {
			txt += '<p>' + item.text + '</p>';
			if(( typeof item.url !== "undefined") && (item.url != "")) {
				gWebViewTitle = item.name;
				gWebViewHref = item.url;
				txt += '<p style="text-align:right;"><a href="#pageWebView" data-rel="dialog" class="ui-link ui-btn ui-btn-inline ui-shadow ui-corner-all">' + _( 'detailMore') + '</a></p>';
			}
		}

		txt += '<ul data-role="listview" data-theme="a">';
		txt += '<li data-role="list-divider">' + _( 'detailFav') + '</li>';
		txt += '<li data-icon="false" class="afterDivider"><center><span>';
		txt += '<a href="#" onClick="onMarkFavorite(\''+imgFavId+'\',0,\''+name+'\');" border=0><img src="art/list-favno.png" border=0 style="border:0;width:40px;max-width:14%;margin-right:2em"></a>';
		for( var i = 1; i <= 5; ++i) {
			if( i <= favorite) {
				txt += '<a href="#" onClick="onMarkFavorite(\''+imgFavId+'\',' + i + ',\''+name+'\');" border=0><img src="art/list-favset.png" id="' + imgFavId + i + '" border=0 style="border:0;width:40px;max-width:14%;"></a>';
			} else {
				txt += '<a href="#" onClick="onMarkFavorite(\''+imgFavId+'\',' + i + ',\''+name+'\');" border=0><img src="art/list-favblank.png" id="' + imgFavId + i + '" border=0 style="border:0;width:40px;max-width:14%;"></a>';
			}
		}
		txt += '</span></center></li>';
		txt += '</ul>';

		gList = "";
		if(( typeof item.similar !== "undefined") && ('' != item.similar)) {
			var similarSplit = item.similar.split( ',');
			var similarList = [];

			$.each( similarSplit, function() {
				var gender_ = 'b';
				var favorite_ = 0;
				var max = gDataName.length;
				for( var i = 0; i < max; ++i) {
					if( this == gDataName[i].name) {
						gender_ = gDataName[i].gender;
						break;
					}
				}

				max = gSettingsFavs.length;
				for( var i = 0; i < max; ++i) {
					if( this == gSettingsFavs[i].name) {
						if( typeof gSettingsFavs[i].fav !== "undefined") {
							favorite_ = gSettingsFavs[i].fav;
							break;
						}
					}
				}

				similarList.push({
					name: this,
					gender: gender_,
					fav: favorite_
				});
			});
			similarList.sort( function( left, right) {
				if( left.fav == right.fav) {
					return (left.name > right.name) ? 1 : -1;
				}
				return left.fav < right.fav;
			});
			var needDivider = true;
			$.each( similarList, function() {
				if( isFilteredName( this)) {
					gList += '<li data-icon="false"' + (needDivider ? ' class="afterDivider"' : '') + '>'
						+ '<a href="' + nextPage + '?given=' + this.name
						+'" class="ui-li-poilink"><img src="art/list-logo' + this.gender + '.png" class="ui-li-poiicon">' + this.name
						+ '<span class="ui-li-count">'+'&#9733;&#9733;&#9733;&#9733;&#9733;'.substr(0,this.fav*7)+'&#9734;&#9734;&#9734;&#9734;&#9734;'.substr(this.fav*7)+'</span></a></li>';
					needDivider = false;
				}
			});
		}
		if( "" != gList) {
			txt += '<ul data-role="listview" data-theme="a">';
			txt += '<li data-role="list-divider"><br>' + _( 'detailAlternate') + '</li>';
			txt += gList + '</ul>';
		}

		gList = "";
		if( typeof item.charts !== "undefined") {
			var needDivider = true;
			$.each( item.charts, function() {
				var vec = this.split( '-');

				if( '' != gSettings.filterNUTS) {
					if( 0 != vec[3].search( gSettings.filterNUTS)) {
						return true;
					}
				}

				var max = gDataSource.length;
				var nuts = '';
				for( var i = 0; i < max; ++i) {
					if( vec[3] == gDataSource[i].nuts) {
						nuts = gDataSource[i].name[CInternationalization.lang_];
						break;
					}
				}

				gList += '<li data-icon="false"' + (needDivider ? ' class="afterDivider"' : '') + '><p style="white-space:normal;font-size:16px;margin:0;">'
					+ _( 'detailChartPos').replace(/%pos%/g,vec[0]).replace(/%year%/g,vec[1]).replace(/%nuts%/g,nuts)
					+ "</p></li>";
				needDivider = false;
			});
		}
		if( "" != gList) {
			txt += '<ul data-role="listview" data-theme="a">';
			txt += '<li data-role="list-divider"><br>' + _( 'detailSpread') + '</li>';
			txt += gList + '</ul>';
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
	$content.html( txt);
	$content.trigger( "create");

	$page.page();
	$content.find( ":jqmData(role=listview)").listview();
	$content.find( ":jqmData(role=button)").button();
	$content.find( ":jqmData(role=popup)").popup();

	$header.find( "a:first").html( '<img src="art/tabbar-ios-7-back.png" style="height:1.6em;margin-left:-1em;padding-right:2em;">');
	$header.find( "a:eq(1)").html( '<img src="art/tabbar-ios-7-home.png" style="height:1.6em;margin-right:-1em;padding-left:2em;">');
	if( gShowWP) {
		$header.find( "a").css( 'display', 'none');
	}

//	options.dataUrl = urlObj.href;

	$.mobile.changePage( $page, options);
}

//----------------------------

$( document).on( 'pageshow', '#pageName1',  function()
{
	if( gShowWP) {
		window.external.notify( "applicationBarClear");
	}
});

//----------------------------

$( document).on( 'pageshow', '#pageName2',  function()
{
	if( gShowWP) {
		window.external.notify( "applicationBarClear");
	}
});

//----------------------------
// eof

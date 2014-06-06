//----------------------------
// pageCanvas.js
//----------------------------

var gList = '';
var gListTheme = '';
var gListDiv = '';
var gListCount = 0;

//----------------------------

function addSearchItem()
{
	try {
//		if( gListCount < 100) {
			if( this.charts.length == 0) {
				return;
			}
			if( !isFilteredName( this)) {
				return;
			}

			++gListCount;
//			gList += '<li><a href="#name?given=' + this.name + '" class="ui-li-poilink ui-li-icongender' + this.gender + '">'
			gList += '<li data-icon="false"><a href="#name?given=' + this.name + '" class="ui-li-poilink">'
				+ '<img src="art/list-logo' + this.gender + '.png" class="ui-li-poiicon">'
				+ this.name + '</a></li>';
//		} else if( gListCount == 100) {
//			++gListCount;
//			gList += '<li data-role="list-divider"><br>' + _( 'searchTooMany') + '</li>';
//		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

//----------------------------

var gTimer = null;
var gTimerBlock = false;
var gTimerVal = '';

//----------------------------

function onFilterChanged()
{
	if( gTimerBlock) {
		return;
	}
	gTimerBlock = true;

	if( gTimerVal == $( '#searchField').val()) {
		return;
	}
	gTimerVal = $( '#searchField').val();

	alert( gTimerVal);
	gTimerBlock = false;
}

//----------------------------

function filterChanged()
{
	if( gTimer == null) {
//		gTimer = window.setInterval( onFilterChanged, 500);
	}
}

//----------------------------

$( document).on( 'pageshow', '#pageSearch',  function()
{
	var startDate = new Date();
	var startMS = startDate.getMilliseconds();

	try {
		init();

		var txt = '';
		txt += '<ul data-role="listview" id="filterSearch" data-theme="a" data-filter-theme="a" data-filter="true">';

		gList = '';
		gListDiv = '';
		gListCount = 0;

		var nameCopy = gDataName.slice();
		nameCopy.sort( function( left, right) {
			return left.name.localeCompare( right.name);
		});

		$.each( nameCopy, addSearchItem);

		txt += gList;
		txt += '</ul>';

		$( '#searchContent').html( txt);
		$( '#searchContent').trigger( "create");
		$( '#searchContent').trigger( 'updatelayout');

//		$( '#searchField').change( filterChanged);
//		$( '#searchField').keydown( filterChanged);

//		$( '#filterSearch').listview( 'option', 'filterCallback', searchFilterFunction);

		$( "#h1Search").html( _( 'homeSearch'));
		$( "#searchBack").html( '<img src="art/tabbar-ios-7-back.png" style="height:1.6em;margin-left:-1em;padding-right:2em;">');
		if( gShowWP) {
			$( "#searchBack_").css( 'display', 'none');
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
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
// eof

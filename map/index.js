/*
	use http://www.convertcsv.com/csv-to-json.htm
*/

var map = null;
var container = null;
var filterLevel = 'all';
var filterDataset = 'portals';

// -----------------------------------------------------------------------------

function initNokiaMap( elementName, lat, lon, zoom)
{
	// test key for
	// url: http://www.tursics.de/sample/vornamen/
	// until: 2014-07-02
	nokia.Settings.set( 'app_id', 'gbHZJd1LPxixPJOwPtgz');
	nokia.Settings.set( 'app_code', 'EC7vp6T4ERlNCLllgzzrow');
	nokia.Settings.set( 'defaultLanguage', 'de-DE');

	map = new nokia.maps.map.Display(
		document.getElementById( elementName), {
			components: [
				new nokia.maps.map.component.Behavior(),
				new nokia.maps.map.component.ZoomBar(),
				new nokia.maps.map.component.TypeSelector(),
				// ScaleBar Overview ZoomRectangle Positioning ContextMenu InfoBubbles PublicTransport Traffic
			],
			zoomLevel: zoom,
			center: [lat, lon],
			baseMapType: nokia.maps.map.Display.TERRAIN // NORMAL NORMAL_COMMUNITY SATELLITE SATELLITE_COMMUNITY SMARTMAP SMART_PT TERRAIN TRAFFIC
	});
//	map.removeComponent( map.getComponentById( "zoom.MouseWheel"));

	var infoBubbles = new nokia.maps.map.component.InfoBubbles();
	var TOUCH = nokia.maps.dom.Page.browser.touch;
	var CLICK = TOUCH ? 'tap' : 'click';

	container = new nokia.maps.map.Container();
	container.addListener( CLICK, function( evt) {
		infoBubbles.openBubble( evt.target.html, evt.target.coordinate);
	}, false);

	map.components.add( infoBubbles);
	map.objects.add( container);
}

// -----------------------------------------------------------------------------

function addMarker()
{
	try {
		var max = dataBasics.length;
		var cVoid = '#b7A6ad';
		var cGood = '#31a354';
		var cWell = '#31a354';
		var cPortal = '#2362a0';
		var cYellow = '#fec44f';
		var cDenied = '#f03b20';
		for( var i = 0; i < max; ++i) {
			if(( null != dataBasics[ i]['lat']) && (null != dataBasics[ i]['lon'])) {
				var bgColor = cVoid;
				var popStr = dataBasics[ i]['population'].toString();
				if( popStr.length > 3) {
					popStr = popStr.substr( 0, popStr.length - 3) + '.' + popStr.substr( popStr.length - 3);
				}
				if( popStr.length > 7) {
					popStr = popStr.substr( 0, popStr.length - 7) + '.' + popStr.substr( popStr.length - 7);
				}

				var str = '<div style="font-size:1.25em;">';

				str += '<div style="border-bottom:1px solid white;padding-bottom:0.5em;margin-bottom:0.5em;">';
				str += '<i class="fa fa-map-marker"></i> ' + dataBasics[ i]['name'] + '<br>';
				str += '<i class="fa fa-male"></i> ' + popStr + ' Einwohner<br>';
				str += '</div>';

				if( typeof dataBasics[ i]['linkOGD'] !== 'undefined') {
					bgColor = cPortal;
					str += '<i class="fa fa-check"></i> Hat ein <a href="' + dataBasics[ i]['linkOGD'] + '" target="_blank">Open Data Portal</a><br>';

					if( typeof dataBasics[ i]['linkOGDNames'] !== 'undefined') {
						bgColor = cGood;
						str += '<i class="fa fa-heart"></i> Enthält einen <a href="' + dataBasics[ i]['linkOGDNames'] + '" target="_blank">Vornamen-Datensatz</a><br>';

						if( typeof dataBasics[ i]['linkOGDLicense'] !== 'undefined') {
							var license = dataBasics[ i]['linkOGDLicense'];
							var good = false;

							if( 'CC 0' == license) {
								good = true;
							} else if( 'CC BY 3.0' == license) {
								good = true;
							} else if( 'DL DE 0 2.0' == license) {
								good = true;
							} else if( 'DL DE BY 2.0' == license) {
								good = true;
							}

							if( good) {
								str += '<i class="fa fa-heart"></i> Mit der Lizenz ' + license + '<br>';
							} else {
								bgColor = cWell;
								str += '<i class="fa fa-check"></i> Mit der Lizenz ' + license + '<br>';
							}
						}
					} else {
						str += '<i class="fa fa-times"></i> Kein Vornamen-Datensatz vorhanden<br>';

						if(( typeof dataBasics[ i]['linkWebNames'] !== 'undefined') && (dataBasics[ i]['linkWebNames'] != '')) {
							bgColor = cYellow;
							str += '<i class="fa fa-minus"></i> Vornamen auf der <a href="' + dataBasics[ i]['linkWebNames'] + '" target="_blank">Webseite</a><br>';
						}
					}
				} else if( typeof dataBasics[ i]['linkWebNames'] !== 'undefined') {
					bgColor = cYellow;
					str += '<i class="fa fa-times"></i> Hat kein Open Data Portal<br>';

					if( dataBasics[ i]['linkWebNames'] != '') {
						str += '<i class="fa fa-check"></i> Vornamen auf der <a href="' + dataBasics[ i]['linkWebNames'] + '" target="_blank">Webseite</a><br>';
					}
				} else {
					bgColor = cDenied;
					str += '<i class="fa fa-times"></i> Hat kein Open Data Portal<br>';

					if( typeof dataBasics[ i]['history'] === 'undefined') {
						continue;
					}
				}

				str += '<br>';

				if( typeof dataBasics[ i]['history'] !== 'undefined') {
					var historySize = dataBasics[ i]['history'].length;
					for( var h = 0; h < historySize; ++h) {
						str += '<div style="border-top:1px solid #aaaaaa;color:#aaaaaa;padding-top:0.5em;margin-top:0.5em;">';
						str += '<i class="fa fa-calendar"></i> ' + dataBasics[ i]['history'][ h]['date'] + '<br>';
						str += '<i class="fa fa-comment-o"></i> ' + dataBasics[ i]['history'][ h]['event'] + '</div>';
					}
				}
				str += '</div>';

				var marker = new nokia.maps.map.StandardMarker([dataBasics[ i]['lat'], dataBasics[ i]['lon']], {
					brush: {color: bgColor},
					html: str,
					nr: i
				});
				container.objects.add( marker);
			}
		}
	} catch( e) {
//		alert( e);
	}
}

// -----------------------------------------------------------------------------

function generateCharts()
{
	try {
		var arrayOGD = [];
		arrayOGD['DE'] = 0;
		arrayOGD['at'] = 0;
		arrayOGD['ch'] = 0;

		var arrayNames = [];
		arrayNames['DE'] = 0;
		arrayNames['at'] = 0;
		arrayNames['ch'] = 0;

		var useMunicipality = ('citizen' != $( 'input[name="choiceCalc"]:checked').val());

		var max = dataBasics.length;
		for( var i = 0; i < max; ++i) {
			var population = dataBasics[ i]['population'];
			var country = dataBasics[ i]['nuts'].substr( 0, 2);
			var hasOGD = (typeof dataBasics[ i]['linkOGD'] !== 'undefined');
			var hasOGDNames = (typeof dataBasics[ i]['linkOGDNames'] !== 'undefined');
			var hasWebNames = (typeof dataBasics[ i]['linkWebNames'] !== 'undefined');
			var countOGD = (typeof dataBasics[ i]['countOGD'] !== 'undefined') ? dataBasics[ i]['countOGD'] : true;
			var countNames = (typeof dataBasics[ i]['countNames'] !== 'undefined') ? dataBasics[ i]['countNames'] : true;
			var countMunicipality = (typeof dataBasics[ i]['municipality'] !== 'undefined') ? dataBasics[ i]['municipality'] : 1;

			if( countOGD && hasOGD) {
				arrayOGD[country] += useMunicipality ? countMunicipality : population;
			}
			if( countNames && (hasOGDNames || hasWebNames)) {
				arrayNames[country] += useMunicipality ? countMunicipality : population;
			}
		}

		var arrayResult = [];
		arrayResult['DE'] = 0;
		arrayResult['at'] = 0;
		arrayResult['ch'] = 0;

		var txtSources = '';
		if( 'ogd' == $( 'input[name="choiceSources"]:checked').val()) {
			arrayResult = arrayOGD;
			txtSources = 'findet man offene Daten in den OGD-Portalen';
		} else {
			arrayResult = arrayNames;
			txtSources = 'findet man Vornamenlisten in den OGD-Portalen';
		}

		var txt = 'Über wie viele Kommunen ' + txtSources + '?<br>';

		var arrayMax = [];
		if( useMunicipality) {
			arrayMax['DE'] = 11116;
			arrayMax['at'] = 2354;
			arrayMax['ch'] = 2551;
			txt += 'Hochgerechnet nach der Anzahl der Kommunen.';
		} else {
			arrayMax['DE'] = 80380000;
			arrayMax['at'] = 8504850;
			arrayMax['ch'] = 8112200;
			txt += 'Hochgerechnet nach der Einwohnerzahl der Kommunen.';
		}

		$( '#chart1').html( txt);
		$( '#chart1').trigger( "create");
		$( '#chart1').trigger( 'updatelayout');
		var chart1DE = Circles.create({
			id:'chart1DE',value:arrayResult['DE'],maxValue:arrayMax['DE'],
			colors:['#9ac9c6','#33a1df'],radius:50,width:10,duration:500,text:function(value){if(Math.round( value / arrayMax['DE'] * 100) < 10) {return '<span>'+Math.round( value / arrayMax['DE'] * 1000)/10+'%</span>';} else {return '<span>'+Math.round( value / arrayMax['DE'] * 100)+'%</span>';}},wrpClass:'circles-wrp',textClass:'circles-text',
		});
		var chart1AT = Circles.create({
			id:'chart1AT',value:arrayResult['at'],maxValue:arrayMax['at'],
			colors:['#9ac9c6','#33a1df'],radius:50,width:10,duration:500,text:function(value){if(Math.round( value / arrayMax['at'] * 100) < 10) {return '<span>'+Math.round( value / arrayMax['at'] * 1000)/10+'%</span>';} else {return '<span>'+Math.round( value / arrayMax['at'] * 100)+'%</span>';}},wrpClass:'circles-wrp',textClass:'circles-text',
		});
		var chart1CH = Circles.create({
			id:'chart1CH',value:arrayResult['ch'],maxValue:arrayMax['ch'],
			colors:['#9ac9c6','#33a1df'],radius:50,width:10,duration:500,text:function(value){if(Math.round( value / arrayMax['ch'] * 100) < 10) {return '<span>'+Math.round( value / arrayMax['ch'] * 1000)/10+'%</span>';} else {return '<span>'+Math.round( value / arrayMax['ch'] * 100)+'%</span>';}},wrpClass:'circles-wrp',textClass:'circles-text',
		});
	} catch( e) {
	}
}

// -----------------------------------------------------------------------------

var objectDefault = {
	getDataset: function() {
		var ret = [];
		return ret;
	},
	getListItem: function( nr) {
		return '<li>' + dataBasics[nr].name + '</li>';
	},
	sort: function( left, right) {
		return (dataBasics[left].name > dataBasics[right].name) ? 1 : -1;
	},
	getLegend: function() {
		return '';
	}
};

// -----------------------------------------------------------------------------

var objectAllPortals = {
	getDataset: function() {
		var ret = [];

		for( var i = 0; i < dataBasics.length; ++i) {
			if( 'DE' == dataBasics[i].nuts.substr( 0, 2)) {
				if( typeof dataBasics[i]['linkOGD'] !== "undefined") {
					ret[ ret.length] = i;
				}
			}
		}

		return ret;
	},
	getListItem: function( nr) {
		return '<li><a href="#" onClick="clickOnDataItem(\'' + nr + '\');" border=0><i class="fa fa-map-marker marker-green"></i>' + dataBasics[nr].name + '</a></li>';
	},
	sort: function( left, right) {
		return (dataBasics[left].name > dataBasics[right].name) ? 1 : -1;
	},
	getLegend: function() {
		return '<i class="fa fa-map-marker marker-green"></i>Hat ein Open Data Portal<br>';
	}
};

// -----------------------------------------------------------------------------

var objectNuts1Portals = {
	getDataset: function() {
		var ret = [];

		for( var i = 0; i < dataBasics.length; ++i) {
			if( 'DE' == dataBasics[i].nuts.substr( 0, 2)) {
				if( dataBasics[i].nuts.length == 3) {
					ret[ ret.length] = i;
				}
			}
		}

		return ret;
	},
	getListItem: function( nr) {
		var marker = 'red';
		if( typeof dataBasics[nr]['linkOGD'] !== "undefined") {
			marker = 'green';
		}
		return '<li><a href="#" onClick="clickOnDataItem(\'' + nr + '\');" border=0><i class="fa fa-map-marker marker-' + marker + '"></i>' + dataBasics[nr].name + '</a></li>';
	},
	sort: function( left, right) {
		return (dataBasics[left].name > dataBasics[right].name) ? 1 : -1;
	},
	getLegend: function() {
		return '<i class="fa fa-map-marker marker-green"></i>Hat ein Open Data Portal<br>'
		     + '<i class="fa fa-map-marker marker-red"></i>Hat kein Open Data Portal<br>';
	}
};

// -----------------------------------------------------------------------------

var objectCityPortals = {
	getDataset: function() {
		var ret = [];

		for( var i = 0; i < dataBasics.length; ++i) {
			if( 'DE' == dataBasics[i].nuts.substr( 0, 2)) {
				if(( dataBasics[i].nuts.length > 3) || (dataBasics[i].name == 'Berlin') || (dataBasics[i].name == 'Hamburg') || (dataBasics[i].name == 'Bremen')) {
					if( dataBasics[i].population >= 100000) {
						ret[ ret.length] = i;
					}
				}
			}
		}

		return ret;
	},
	getListItem: function( nr) {
		var marker = 'red';
		if( typeof dataBasics[nr]['linkOGD'] !== "undefined") {
			marker = 'green';
		}
		return '<li><a href="#" onClick="clickOnDataItem(\'' + nr + '\');" border=0><i class="fa fa-map-marker marker-' + marker + '"></i>' + dataBasics[nr].name + ' <span class="ui-li-count">' + dataBasics[nr].population + '</span></a></li>';
	},
	sort: function( left, right) {
		return (dataBasics[left].population < dataBasics[right].population) ? 1 : -1;
	},
	getLegend: function() {
		return '<i class="fa fa-map-marker marker-green"></i>Hat ein Open Data Portal<br>'
		     + '<i class="fa fa-map-marker marker-red"></i>Hat kein Open Data Portal<br>';
	}
};

// -----------------------------------------------------------------------------

function generateDataList()
{
	var txt = '';

	txt += '<div style="font-size:2em;font-weight:100;padding:0;">Die Daten</div>';

	txt += '<form>';
	txt += '<fieldset data-role="controlgroup">';
	txt += '<select name="filterLevel" id="filterLevel">';
	txt += '<option value="all"' + ('all' == filterLevel ? ' selected="selected"' : '') + '>Alle</option>';
//	txt += '<option value="bund"' + ('bund' == filterLevel ? ' selected="selected"' : '') + '>Obere Behörden</option>';
	txt += '<option value="nuts1"' + ('nuts1' == filterLevel ? ' selected="selected"' : '') + '>Bundesländer</option>';
	txt += '<option value="cities"' + ('cities' == filterLevel ? ' selected="selected"' : '') + '>Großstädte</option>';
	txt += '</select>';
	txt += '<select name="filterDataset" id="filterDataset">';
	txt += '<option value="portals"' + ('portals' == filterDataset ? ' selected="selected"' : '') + '>Open Data Portale</option>';
//	txt += '<option value="firstnames"' + ('firstnames' == filterDataset ? ' selected="selected"' : '') + '>Vornamen Datensätze</option>';
	txt += '</select>';
	txt += '</fieldset>';
	txt += '</form>';

	var obj = objectDefault;
	if(( 'all' == filterLevel) && ('portals' == filterDataset)) {
		obj = objectAllPortals;
	} else if(( 'nuts1' == filterLevel) && ('portals' == filterDataset)) {
		obj = objectNuts1Portals;
	} else if(( 'cities' == filterLevel) && ('portals' == filterDataset)) {
		obj = objectCityPortals;
	}

	txt += '<div id="dataInfo">';
	txt += obj.getLegend();
	txt += '</div>';

	var arr = obj.getDataset();
	arr.sort( obj.sort);

	txt += '<ul id="dataList" data-role="listview" data-inset="false">';
	txt += '<li data-role="list-divider">' + arr.length + ' Einträge</li>';

	for( var i = 0; i < arr.length; ++i) {
		txt += obj.getListItem( arr[ i]);
	}

	txt += '</ul>';

	$( '#mapDetailsDiv').html( txt);
	$( '#mapDetailsDiv').trigger( 'create');
	$( '#mapDetailsDiv').trigger( 'updatelayout');

	$( '#filterLevel').change( function() {
		filterLevel = $( this).val();
		generateDataList();
	});
	$( '#filterDataset').change( function() {
		filterDataset = $( this).val();
		generateDataList();
	});
}

// -----------------------------------------------------------------------------

function clickOnDataItem( nr)
{
	nr = parseInt( nr);
	map.set( 'zoomLevel', 10);
	map.set( 'center', [dataBasics[nr].lat, dataBasics[nr].lon]);

	var TOUCH = nokia.maps.dom.Page.browser.touch;
	var CLICK = TOUCH ? 'tap' : 'click';
	var len = container.objects.getLength();

	for( var i = 0; i < len; ++i) {
		if( nr === container.objects.get( i).nr) {
			container.dispatch(
				new nokia.maps.dom.Event({
					type: CLICK,
					target: container.objects.get( i)
				})
			);
			break;
		}
	}
}

// -----------------------------------------------------------------------------

function showPage( pageName)
{
	$( '#mapDetailsDiv').html( $( pageName).html());
//	$( pageName).popup( 'open');

	if( '#popupCharts' == pageName) {
		$( '#choiceSourceOGD').on( 'click', function( e) { generateCharts(); });
		$( '#choiceSourceNames').on( 'click', function( e) { generateCharts(); });
		$( '#choiceCalcCitizen').on( 'click', function( e) { generateCharts(); });
		$( '#choiceCalcMunicipality').on( 'click', function( e) { generateCharts(); });
	} else if( '#popupData' == pageName) {
		generateDataList();
	}
}

// -----------------------------------------------------------------------------

$( document).on( "pagecreate", "#pageMap", function()
{
	initNokiaMap( 'mapContainer', 52.516, 13.4795, 6);

 	$.mobile.selectmenu.prototype.options.nativeMenu = false;
 
 	addMarker();

	map.addListener( "displayready", function () {
		generateCharts();

		showPage( '#popupStart');
	});

	$( '#aPopupData').on( 'click', function( e) { showPage( '#popupData'); return false; });
	$( '#aPopupStart').on( 'click', function( e) { showPage( '#popupStart'); return false; });
	$( '#aPopupCharts').on( 'click', function( e) { showPage( '#popupCharts'); return false; });
	$( '#aPopupSamples').on( 'click', function( e) { showPage( '#popupSamples'); return false; });
	$( '#aPopupContests').on( 'click', function( e) { showPage( '#popupContests'); return false; });
	$( '#aPopupShare').on( 'click', function( e) { showPage( '#popupShare'); return false; });
	$( '#aPopupCopyright').on( 'click', function( e) { showPage( '#popupCopyright'); return false; });
});

// -----------------------------------------------------------------------------

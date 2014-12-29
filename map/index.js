/*
	use http://www.convertcsv.com/csv-to-json.htm
*/

var map = null;
var mapContainer = null;
var mapBubble = null;
var mapBubbles = null;
var filterCountry = 'DE';
var filterLevel = 'all';
var filterDataset = 'portals';

// -----------------------------------------------------------------------------

function initNokiaMap( elementName, lat, lon, zoom)
{
	// https://developer.here.com/myapps/create-eval
	// test key for
	// url: http://www.tursics.de/sample/vornamen/
	// until: 2014-07-02
//	nokia.Settings.set( 'app_id', 'gbHZJd1LPxixPJOwPtgz');
//	nokia.Settings.set( 'app_code', 'EC7vp6T4ERlNCLllgzzrow');
//	nokia.Settings.set( 'defaultLanguage', 'de-DE');

	// https://developer.here.com/myapps
	// PLAN: Free
	// url: http://www.tursics.de/sample/vornamen/
	// limit: 100,000 monthly transactions
	nokia.Settings.set( 'app_id', 'bgDmVjIXSo2vkcxMYcdb');
	nokia.Settings.set( 'app_code', '0Kn_z4FcPn2Wxpbz--j7xw');
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

	mapBubbles = new nokia.maps.map.component.InfoBubbles();
	var TOUCH = nokia.maps.dom.Page.browser.touch;
	var CLICK = TOUCH ? 'tap' : 'click';

	mapContainer = new nokia.maps.map.Container();
	mapContainer.addListener( CLICK, function( evt) {
		mapBubble = mapBubbles.openBubble( getBubbleHTML( evt.target.nr), evt.target.coordinate);
	}, false);

	map.components.add( mapBubbles);
	map.objects.add( mapContainer);
}

// -----------------------------------------------------------------------------

function getBubbleHTML( id)
{
	try {
		var str = '<div style="font-size:1.25em;">';
		str += '<div style="border-bottom:1px solid white;padding-bottom:0.5em;margin-bottom:0.5em;">';
		str += '<i class="fa fa-map-marker"></i> ' + dataBasics[ id]['name'] + '<br>';
		if( dataBasics[ id]['population'] > 0) {
			str += '<i class="fa fa-male"></i> ' + formatPopulation( dataBasics[ id]['population']) + ' Einwohner<br>';
		}
		str += '</div>';

		if( typeof dataBasics[ id]['linkOGD'] !== 'undefined') {
			str += '<i class="fa fa-check"></i> Hat ein <a href="' + dataBasics[ id]['linkOGD'] + '" target="_blank">Open Data Portal</a><br>';

/*			if( typeof dataBasics[ id]['linkOGDNames'] !== 'undefined') {
				str += '<i class="fa fa-heart"></i> Enthält einen <a href="' + dataBasics[ id]['linkOGDNames'] + '" target="_blank">Vornamen-Datensatz</a><br>';

				if( typeof dataBasics[ id]['linkOGDLicense'] !== 'undefined') {
					var license = dataBasics[ id]['linkOGDLicense'];
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
						str += '<i class="fa fa-check"></i> Mit der Lizenz ' + license + '<br>';
					}
				}
			} else {
				str += '<i class="fa fa-times"></i> Kein Vornamen-Datensatz vorhanden<br>';

				if(( typeof dataBasics[ id]['linkWebNames'] !== 'undefined') && (dataBasics[ id]['linkWebNames'] != '')) {
					str += '<i class="fa fa-minus"></i> Vornamen auf der <a href="' + dataBasics[ id]['linkWebNames'] + '" target="_blank">Webseite</a><br>';
				}
			}*/
/*		} else if( typeof dataBasics[ id]['linkWebNames'] !== 'undefined') {
			str += '<i class="fa fa-times"></i> Hat kein Open Data Portal<br>';

			if( dataBasics[ id]['linkWebNames'] != '') {
				str += '<i class="fa fa-check"></i> Vornamen auf der <a href="' + dataBasics[ id]['linkWebNames'] + '" target="_blank">Webseite</a><br>';
			}*/
		} else {
			str += '<i class="fa fa-times"></i> Hat kein Open Data Portal<br>';
		}

		if( typeof dataBasics[ id]['history'] !== 'undefined') {
			str += '<br>';

			var historySize = dataBasics[ id]['history'].length;
			for( var h = 0; h < historySize; ++h) {
				str += '<div style="border-top:1px solid #aaaaaa;color:#aaaaaa;padding-top:0.5em;margin-top:0.5em;">';
				str += '<i class="fa fa-calendar"></i> ' + dataBasics[ id]['history'][ h]['date'] + '<br>';
				str += '<i class="fa fa-comment-o"></i> ' + dataBasics[ id]['history'][ h]['event'] + '</div>';
			}
		}
		str += '</div>';

		return str;
	} catch( e) {
		return e.message;
	}
}

// -----------------------------------------------------------------------------

function formatPopulation( population)
{
	var str = population.toString();
	if( str.length > 3) {
		str = str.substr( 0, str.length - 3) + '.' + str.substr( str.length - 3);
	}
	if( str.length > 7) {
		str = str.substr( 0, str.length - 7) + '.' + str.substr( str.length - 7);
	}
	return str;
}

// -----------------------------------------------------------------------------
/*
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

				if( typeof dataBasics[ i]['linkOGD'] !== 'undefined') {
					bgColor = cPortal;

					if( typeof dataBasics[ i]['linkOGDNames'] !== 'undefined') {
						bgColor = cGood;

						if( typeof dataBasics[ i]['linkOGDLicense'] !== 'undefined') {
							var license = dataBasics[ i]['linkOGDLicense'];

							if( 'CC 0' == license) {
							} else if( 'CC BY 3.0' == license) {
							} else if( 'DL DE 0 2.0' == license) {
							} else if( 'DL DE BY 2.0' == license) {
							} else {
								bgColor = cWell;
							}
						}
					} else {
						if(( typeof dataBasics[ i]['linkWebNames'] !== 'undefined') && (dataBasics[ i]['linkWebNames'] != '')) {
							bgColor = cYellow;
						}
					}
				} else if( typeof dataBasics[ i]['linkWebNames'] !== 'undefined') {
					bgColor = cYellow;
				} else {
					bgColor = cDenied;

					if( typeof dataBasics[ i]['history'] === 'undefined') {
						continue;
					}
				}

				var marker = new nokia.maps.map.StandardMarker([dataBasics[ i]['lat'], dataBasics[ i]['lon']], {
					brush: {color: bgColor},
					nr: i
				});
				mapContainer.objects.add( marker);
			}
		}
	} catch( e) {
//		alert( e);
	}
}
*/
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
	},
	addMarker: function( vec) {
	},
	getCharts: function() {
		return '';
	},
	createCharts: function( vec) {
	}
};

// -----------------------------------------------------------------------------

var objectAllPortals = {
	getDataset: function() {
		var ret = [];

		for( var i = 0; i < dataBasics.length; ++i) {
			if( filterCountry == dataBasics[i].nuts.substr( 0, 2)) {
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
	},
	addMarker: function( vec) {
		var max = vec.length;
		var cGreen = '#31a354';
		for( var i = 0; i < max; ++i) {
			var id = vec[ i];
			var marker = new nokia.maps.map.StandardMarker([dataBasics[ id]['lat'], dataBasics[ id]['lon']], {
				brush: {color: cGreen},
				nr: id
			});
			mapContainer.objects.add( marker);
		}
	},
	getCharts: function() {
		return '';
	},
	createCharts: function( vec) {
	}
};

// -----------------------------------------------------------------------------

var objectNuts1Portals = {
	getDataset: function() {
		var ret = [];
		var group = 'state';

		for( var i = 0; i < dataBasics.length; ++i) {
			if( filterCountry == dataBasics[i].nuts.substr( 0, 2)) {
				if( 0 <= dataBasics[i].group.indexOf( group)) {
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
	},
	addMarker: function( vec) {
		var max = vec.length;
		var cGreen = '#31a354';
		var cRed = '#f03b20';
		for( var i = 0; i < max; ++i) {
			var id = vec[ i];
			var marker = new nokia.maps.map.StandardMarker([dataBasics[ id]['lat'], dataBasics[ id]['lon']], {
				brush: {color: (typeof dataBasics[id]['linkOGD'] !== "undefined") ? cGreen : cRed},
				nr: id
			});
			mapContainer.objects.add( marker);
		}
	},
	getCharts: function() {
		var txt = '';
		txt += '<div class="chart chart' + filterCountry + '" id="chartCount"></div>';
		txt += '<div class="chart chart' + filterCountry + '" id="chartPeople"></div>';

		return txt;
	},
	createCharts: function( vec) {
		var maxCount = vec.length;
		var maxPeople = 0;
		var valCount = 0;
		var valPeople = 0;
		var txtCount = '% der<br>Bundesländer';
		var txtPeople = '% der<br>Bevölkerung';
		if( 'CH' == filterCountry) {
			txtCount = '% der<br>Kantone';
		}
		for( var i = 0; i < maxCount; ++i) {
			var id = vec[ i];
			if( typeof dataBasics[id]['linkOGD'] !== "undefined") {
				++valCount;
				valPeople += dataBasics[id]['population'];
			}
		}
		for( var i = 0; i < dataBasics.length; ++i) {
			if( dataBasics[i]['nuts'] == filterCountry) {
				maxPeople = dataBasics[i]['population'];
			}
		}
		if( 0 == maxCount) {
			maxCount = 1;
		}
		var chartCount = Circles.create({
			id:'chartCount',value:valCount,maxValue:maxCount,
			colors:['#9ac9c6','#33a1df'],radius:50,width:10,duration:500,text:function(value){if(Math.round( value / maxCount * 100) < 10) {return '<span>'+Math.round( value / maxCount * 1000)/10+txtCount+'</span>';} else {return '<span>'+Math.round( value / maxCount * 100)+txtCount+'</span>';}},wrpClass:'circles-wrp',textClass:'circles-text',
		});
		var chartPeople = Circles.create({
			id:'chartPeople',value:valPeople,maxValue:maxPeople,
			colors:['#9ac9c6','#33a1df'],radius:50,width:10,duration:500,text:function(value){if(Math.round( value / maxPeople * 100) < 10) {return '<span>'+Math.round( value / maxPeople * 1000)/10+txtPeople+'</span>';} else {return '<span>'+Math.round( value / maxPeople * 100)+txtPeople+'</span>';}},wrpClass:'circles-wrp',textClass:'circles-text',
		});
	}
};

// -----------------------------------------------------------------------------

var objectDistrictPortals = {
	getDataset: function() {
		var ret = [];
		var group = 'district';

		for( var i = 0; i < dataBasics.length; ++i) {
			if( filterCountry == dataBasics[i].nuts.substr( 0, 2)) {
				if( typeof dataBasics[i]['linkOGD'] !== "undefined") {
					if( 0 <= dataBasics[i].group.indexOf( group)) {
						ret[ ret.length] = i;
					}
				}
			}
		}

		return ret;
	},
	getListItem: function( nr) {
		return '<li><a href="#" onClick="clickOnDataItem(\'' + nr + '\');" border=0><i class="fa fa-map-marker marker-green"></i>' + dataBasics[nr].name + '</a></li>';
	},
	sort: function( left, right) {
		return (dataBasics[left].population < dataBasics[right].population) ? 1 : -1;
	},
	getLegend: function() {
		return '<i class="fa fa-map-marker marker-green"></i>Hat ein Open Data Portal<br>'
	},
	addMarker: function( vec) {
		var max = vec.length;
		var cGreen = '#31a354';
		for( var i = 0; i < max; ++i) {
			var id = vec[ i];
			var marker = new nokia.maps.map.StandardMarker([dataBasics[ id]['lat'], dataBasics[ id]['lon']], {
				brush: {color: cGreen},
				nr: id
			});
			mapContainer.objects.add( marker);
		}
	},
	getCharts: function() {
		return '';
	},
	createCharts: function( vec) {
	}
};

// -----------------------------------------------------------------------------

var objectCityPortals = {
	getDataset: function() {
		var ret = [];
		var group = 'city';

		for( var i = 0; i < dataBasics.length; ++i) {
			if( filterCountry == dataBasics[i].nuts.substr( 0, 2)) {
				if( 0 <= dataBasics[i].group.indexOf( group)) {
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
		return '<li><a href="#" onClick="clickOnDataItem(\'' + nr + '\');" border=0><i class="fa fa-map-marker marker-' + marker + '"></i>' + dataBasics[nr].name + ' <span class="ui-li-count">' + formatPopulation( dataBasics[nr].population) + '</span></a></li>';
	},
	sort: function( left, right) {
		return (dataBasics[left].population < dataBasics[right].population) ? 1 : -1;
	},
	getLegend: function() {
		return '<i class="fa fa-map-marker marker-green"></i>Hat ein Open Data Portal<br>'
		     + '<i class="fa fa-map-marker marker-red"></i>Hat kein Open Data Portal<br>';
	},
	addMarker: function( vec) {
		var max = vec.length;
		var cGreen = '#31a354';
		var cRed = '#f03b20';
		for( var i = 0; i < max; ++i) {
			var id = vec[ i];
			var marker = new nokia.maps.map.StandardMarker([dataBasics[ id]['lat'], dataBasics[ id]['lon']], {
				brush: {color: (typeof dataBasics[id]['linkOGD'] !== "undefined") ? cGreen : cRed},
				nr: id
			});
			mapContainer.objects.add( marker);
		}
	},
	getCharts: function() {
		var txt = '';
		txt += '<div class="chart chart' + filterCountry + '" id="chartCount"></div>';
		txt += '<div class="chart chart' + filterCountry + '" id="chartPeople"></div>';

		return txt;
	},
	createCharts: function( vec) {
		var maxCount = vec.length;
		var maxPeople = 0;
		var valCount = 0;
		var valPeople = 0;
		var txtCount = '% der<br>Großstädte';
		var txtPeople = '% der<br>Bevölkerung';
		for( var i = 0; i < maxCount; ++i) {
			var id = vec[ i];
			if( typeof dataBasics[id]['linkOGD'] !== "undefined") {
				++valCount;
				valPeople += dataBasics[id]['population'];
			}
		}
		for( var i = 0; i < dataBasics.length; ++i) {
			if( dataBasics[i]['nuts'] == filterCountry) {
				maxPeople = dataBasics[i]['population'];
			}
		}
		if( 0 == maxCount) {
			maxCount = 1;
		}
		var chartCount = Circles.create({
			id:'chartCount',value:valCount,maxValue:maxCount,
			colors:['#9ac9c6','#33a1df'],radius:50,width:10,duration:500,text:function(value){if(Math.round( value / maxCount * 100) < 10) {return '<span>'+Math.round( value / maxCount * 1000)/10+txtCount+'</span>';} else {return '<span>'+Math.round( value / maxCount * 100)+txtCount+'</span>';}},wrpClass:'circles-wrp',textClass:'circles-text',
		});
		var chartPeople = Circles.create({
			id:'chartPeople',value:valPeople,maxValue:maxPeople,
			colors:['#9ac9c6','#33a1df'],radius:50,width:10,duration:500,text:function(value){if(Math.round( value / maxPeople * 100) < 10) {return '<span>'+Math.round( value / maxPeople * 1000)/10+txtPeople+'</span>';} else {return '<span>'+Math.round( value / maxPeople * 100)+txtPeople+'</span>';}},wrpClass:'circles-wrp',textClass:'circles-text',
		});
	}
};

// -----------------------------------------------------------------------------

var objectMunicipalPortals = {
	getDataset: function() {
		var ret = [];
		var group = 'city';

		for( var i = 0; i < dataBasics.length; ++i) {
			if( filterCountry == dataBasics[i].nuts.substr( 0, 2)) {
				if( 0 <= dataBasics[i].group.indexOf( group)) {
					if( typeof dataBasics[i]['linkOGD'] !== "undefined") {
						if( dataBasics[i].population < 100000) {
							ret[ ret.length] = i;
						}
					}
				}
			}
		}

		return ret;
	},
	getListItem: function( nr) {
		return '<li><a href="#" onClick="clickOnDataItem(\'' + nr + '\');" border=0><i class="fa fa-map-marker marker-green"></i>' + dataBasics[nr].name + ' <span class="ui-li-count">' + formatPopulation( dataBasics[nr].population) + '</span></a></li>';
	},
	sort: function( left, right) {
		return (dataBasics[left].population < dataBasics[right].population) ? 1 : -1;
	},
	getLegend: function() {
		return '<i class="fa fa-map-marker marker-green"></i>Hat ein Open Data Portal<br>'
	},
	addMarker: function( vec) {
		var max = vec.length;
		var cGreen = '#31a354';
		for( var i = 0; i < max; ++i) {
			var id = vec[ i];
			var marker = new nokia.maps.map.StandardMarker([dataBasics[ id]['lat'], dataBasics[ id]['lon']], {
				brush: {color: cGreen},
				nr: id
			});
			mapContainer.objects.add( marker);
		}
	},
	getCharts: function() {
		var txt = '';
		txt += '<div class="chart chart' + filterCountry + '" id="chartPeople"></div>';

		return txt;
	},
	createCharts: function( vec) {
		var maxCount = vec.length;
		var maxPeople = 0;
		var valPeople = 0;
		var txtPeople = '% der<br>Bevölkerung';
		for( var i = 0; i < maxCount; ++i) {
			var id = vec[ i];
			valPeople += dataBasics[id]['population'];
		}
		for( var i = 0; i < dataBasics.length; ++i) {
			if( dataBasics[i]['nuts'] == filterCountry) {
				maxPeople = dataBasics[i]['population'];
			}
		}
		var chartPeople = Circles.create({
			id:'chartPeople',value:valPeople,maxValue:maxPeople,
			colors:['#9ac9c6','#33a1df'],radius:50,width:10,duration:500,text:function(value){if(Math.round( value / maxPeople * 100) < 10) {return '<span>'+Math.round( value / maxPeople * 1000)/10+txtPeople+'</span>';} else {return '<span>'+Math.round( value / maxPeople * 100)+txtPeople+'</span>';}},wrpClass:'circles-wrp',textClass:'circles-text',
		});
	}
};

// -----------------------------------------------------------------------------

var objectOtherPortals = {
	getDataset: function() {
		var ret = [];
		var group = 'other';

		for( var i = 0; i < dataBasics.length; ++i) {
			if( filterCountry == dataBasics[i].nuts.substr( 0, 2)) {
				if( typeof dataBasics[i]['linkOGD'] !== "undefined") {
					if( 0 <= dataBasics[i].group.indexOf( group)) {
						ret[ ret.length] = i;
					}
				}
			}
		}

		return ret;
	},
	getListItem: function( nr) {
		return '<li><a href="#" onClick="clickOnDataItem(\'' + nr + '\');" border=0><i class="fa fa-map-marker marker-green"></i>' + dataBasics[nr].name + '</a></li>';
	},
	sort: function( left, right) {
		return (dataBasics[left].population < dataBasics[right].population) ? 1 : -1;
	},
	getLegend: function() {
		return '<i class="fa fa-map-marker marker-green"></i>Hat ein Open Data Portal<br>'
	},
	addMarker: function( vec) {
		var max = vec.length;
		var cGreen = '#31a354';
		for( var i = 0; i < max; ++i) {
			var id = vec[ i];
			var marker = new nokia.maps.map.StandardMarker([dataBasics[ id]['lat'], dataBasics[ id]['lon']], {
				brush: {color: cGreen},
				nr: id
			});
			mapContainer.objects.add( marker);
		}
	},
	getCharts: function() {
		return '';
	},
	createCharts: function( vec) {
	}
};

// -----------------------------------------------------------------------------

function geoSort( left, right)
{
	if( dataBasics[left].lat == dataBasics[right].lat) {
		return (dataBasics[left].lon < dataBasics[right].lon) ? 1 : -1;
	}

	return (dataBasics[left].lat < dataBasics[right].lat) ? 1 : -1;
}

// -----------------------------------------------------------------------------

function generateDataList()
{
	mapContainer.objects.clear();
	if( mapBubble) {
		mapBubbles.closeBubble( mapBubble);
		mapBubble = null;
	}

	var txt = '';

	txt += '<div style="padding:0;">Zeige auf der Karte:</div>';

	txt += '<form>';
	txt += '<fieldset data-role="controlgroup">';

	txt += '<select name="filterLevel" id="filterLevel">';
	txt += '<option value="all"' + ('all' == filterLevel ? ' selected="selected"' : '') + '>Alle</option>';
	txt += '<option value="bund"' + ('bund' == filterLevel ? ' selected="selected"' : '') + ' disabled="disabled">Obere Behörden</option>';
	if( 'CH' == filterCountry) {
		txt += '<option value="nuts1"' + ('nuts1' == filterLevel ? ' selected="selected"' : '') + '>Die Kantone mit</option>';
	} else {
		txt += '<option value="nuts1"' + ('nuts1' == filterLevel ? ' selected="selected"' : '') + '>Die Bundesländer mit</option>';
	}
	txt += '<option value="district"' + ('district' == filterLevel ? ' selected="selected"' : '') + '>Die Landkreise mit</option>';
	txt += '<option value="cities"' + ('cities' == filterLevel ? ' selected="selected"' : '') + '>Alle Großstädte mit</option>';
	txt += '<option value="municipal"' + ('municipal' == filterLevel ? ' selected="selected"' : '') + '>Kleinere Gemeinden mit</option>';
	txt += '<option value="other"' + ('other' == filterLevel ? ' selected="selected"' : '') + '>Andere Stellen mit</option>';
	txt += '</select>';

	txt += '<select name="filterDataset" id="filterDataset">';
	if( 'all' == filterLevel) {
		txt += '<option value="portals"' + ('portals' == filterDataset ? ' selected="selected"' : '') + '>Open Data Portale</option>';
	} else {
		txt += '<option value="portals"' + ('portals' == filterDataset ? ' selected="selected"' : '') + '>Open Data Portalen</option>';
	}
	txt += '<option value="firstnames"' + ('firstnames' == filterDataset ? ' selected="selected"' : '') + ' disabled="disabled">Vornamen Datensätze</option>';
	txt += '</select>';

	txt += '<select name="filterCountry" id="filterCountry">';
	txt += '<option value="DE"' + ('DE' == filterCountry ? ' selected="selected"' : '') + '>in Deutschland</option>';
	txt += '<option value="AT"' + ('AT' == filterCountry ? ' selected="selected"' : '') + '>in Österreich</option>';
	txt += '<option value="CH"' + ('CH' == filterCountry ? ' selected="selected"' : '') + '>in der Schweiz</option>';
	txt += '</select>';

	txt += '</fieldset>';
	txt += '</form>';

	var obj = objectDefault;
	if(( 'all' == filterLevel) && ('portals' == filterDataset)) {
		obj = objectAllPortals;
	} else if(( 'nuts1' == filterLevel) && ('portals' == filterDataset)) {
		obj = objectNuts1Portals;
	} else if(( 'district' == filterLevel) && ('portals' == filterDataset)) {
		obj = objectDistrictPortals;
	} else if(( 'cities' == filterLevel) && ('portals' == filterDataset)) {
		obj = objectCityPortals;
	} else if(( 'municipal' == filterLevel) && ('portals' == filterDataset)) {
		obj = objectMunicipalPortals;
	} else if(( 'other' == filterLevel) && ('portals' == filterDataset)) {
		obj = objectOtherPortals;
	}

	if( 'CH' == filterCountry) {
		txt += '<div style="color:crimson;margin:.4em 0 .4em 0;">Die Schweizer Daten sind noch nicht komplett evaluiert.</div>';
	}
	txt += '<div id="dataInfo">';
	txt += obj.getLegend();
	txt += '</div>';
	txt += '<div style="text-align:center;">';
	txt += obj.getCharts();
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

	$( '#filterCountry').change( function() {
		filterCountry = $( this).val();
		generateDataList();
	});
	$( '#filterLevel').change( function() {
		filterLevel = $( this).val();
		generateDataList();
	});
	$( '#filterDataset').change( function() {
		filterDataset = $( this).val();
		generateDataList();
	});

	arr.sort( geoSort);
	obj.addMarker( arr);
	obj.createCharts( arr);
}

// -----------------------------------------------------------------------------

function clickOnDataItem( nr)
{
	nr = parseInt( nr);
//	map.set( 'zoomLevel', 10);
	map.set( 'center', [dataBasics[nr].lat, dataBasics[nr].lon]);

	var TOUCH = nokia.maps.dom.Page.browser.touch;
	var CLICK = TOUCH ? 'tap' : 'click';
	var len = mapContainer.objects.getLength();

	for( var i = 0; i < len; ++i) {
		if( nr === mapContainer.objects.get( i).nr) {
			mapContainer.dispatch(
				new nokia.maps.dom.Event({
					type: CLICK,
					target: mapContainer.objects.get( i)
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

	if( '#popupData' == pageName) {
		generateDataList();
	}
}

// -----------------------------------------------------------------------------

$( document).on( "pagecreate", "#pageMap", function()
{
	initNokiaMap( 'mapContainer', 52.516, 13.4795, 6);

	$.mobile.selectmenu.prototype.options.nativeMenu = false;

	map.addListener( "displayready", function () {
		showPage( '#popupData');
	});

	$( '#aPopupData').on( 'click', function( e) { showPage( '#popupData'); return false; });
	$( '#aPopupSamples').on( 'click', function( e) { showPage( '#popupSamples'); return false; });
	$( '#aPopupContests').on( 'click', function( e) { showPage( '#popupContests'); return false; });
	$( '#aPopupShare').on( 'click', function( e) { showPage( '#popupShare'); return false; });
	$( '#aPopupCopyright').on( 'click', function( e) { showPage( '#popupCopyright'); return false; });
});

// -----------------------------------------------------------------------------

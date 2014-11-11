/*
	use http://www.convertcsv.com/csv-to-json.htm
*/

var map = null;
var container = null;

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
		var max = data.length;
		var cVoid = '#b7A6ad';
		var cGood = '#31a354';
		var cWell = '#31a354';
		var cPortal = '#2362a0';
		var cYellow = '#fec44f';
		var cDenied = '#f03b20';
		for( var i = 0; i < max; ++i) {
			if(( null != data[ i]['lat']) && (null != data[ i]['lon'])) {
				var bgColor = cVoid;
				var popStr = data[ i]['population'].toString();
				if( popStr.length > 3) {
					popStr = popStr.substr( 0, popStr.length - 3) + '.' + popStr.substr( popStr.length - 3);
				}
				if( popStr.length > 7) {
					popStr = popStr.substr( 0, popStr.length - 7) + '.' + popStr.substr( popStr.length - 7);
				}

				var str = '<div style="font-size:1.25em;">';

				str += '<div style="border-bottom:1px solid white;padding-bottom:0.5em;margin-bottom:0.5em;">';
				str += '<i class="fa fa-map-marker"></i> ' + data[ i]['name'] + ' (' + data[ i]['country'] + ')<br>';
				str += '<i class="fa fa-male"></i> ' + popStr + ' Einwohner<br>';
				str += '</div>';

				if( typeof data[ i]['linkOGD'] !== 'undefined') {
					bgColor = cPortal;
					str += '<i class="fa fa-check"></i> Hat ein <a href="' + data[ i]['linkOGD'] + '" target="_blank">Open Data Portal</a><br>';

					if( typeof data[ i]['linkOGDNames'] !== 'undefined') {
						bgColor = cGood;
						str += '<i class="fa fa-heart"></i> Enthält einen <a href="' + data[ i]['linkOGDNames'] + '" target="_blank">Vornamen-Datensatz</a><br>';

						if( typeof data[ i]['linkOGDLicense'] !== 'undefined') {
							var license = data[ i]['linkOGDLicense'];
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

						if(( typeof data[ i]['linkWebNames'] !== 'undefined') && (data[ i]['linkWebNames'] != '')) {
							bgColor = cYellow;
							str += '<i class="fa fa-minus"></i> Vornamen auf der <a href="' + data[ i]['linkWebNames'] + '" target="_blank">Webseite</a><br>';
						}
					}
				} else if( typeof data[ i]['linkWebNames'] !== 'undefined') {
					bgColor = cYellow;
					str += '<i class="fa fa-times"></i> Hat kein Open Data Portal<br>';

					if( data[ i]['linkWebNames'] != '') {
						str += '<i class="fa fa-check"></i> Vornamen auf der <a href="' + data[ i]['linkWebNames'] + '" target="_blank">Webseite</a><br>';
					}
				} else {
					bgColor = cDenied;
					str += '<i class="fa fa-times"></i> Hat kein Open Data Portal<br>';

					if( typeof data[ i]['history'] === 'undefined') {
						continue;
					}
				}

				str += '<br>';

				if( typeof data[ i]['history'] !== 'undefined') {
					var historySize = data[ i]['history'].length;
					for( var h = 0; h < historySize; ++h) {
						str += '<div style="border-top:1px solid #aaaaaa;color:#aaaaaa;padding-top:0.5em;margin-top:0.5em;">';
						str += '<i class="fa fa-calendar"></i> ' + data[ i]['history'][ h]['date'] + '<br>';
						str += '<i class="fa fa-comment-o"></i> ' + data[ i]['history'][ h]['event'] + '</div>';
					}
				}
				str += '</div>';

				var marker = new nokia.maps.map.StandardMarker([data[ i]['lat'], data[ i]['lon']], {
					brush: {color: bgColor},
					html: str
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
		arrayOGD['Deutschland'] = 0;
		arrayOGD['Österreich'] = 0;
		arrayOGD['Schweiz'] = 0;

		var arrayNames = [];
		arrayNames['Deutschland'] = 0;
		arrayNames['Österreich'] = 0;
		arrayNames['Schweiz'] = 0;

		var useMunicipality = ('citizen' != $( 'input[name="choiceCalc"]:checked').val());

		var max = data.length;
		for( var i = 0; i < max; ++i) {
			var population = data[ i]['population'];
			var country = data[ i]['country'];
			var hasOGD = (typeof data[ i]['linkOGD'] !== 'undefined');
			var hasOGDNames = (typeof data[ i]['linkOGDNames'] !== 'undefined');
			var hasWebNames = (typeof data[ i]['linkWebNames'] !== 'undefined');
			var countOGD = (typeof data[ i]['countOGD'] !== 'undefined') ? data[ i]['countOGD'] : true;
			var countNames = (typeof data[ i]['countNames'] !== 'undefined') ? data[ i]['countNames'] : true;
			var countMunicipality = (typeof data[ i]['municipality'] !== 'undefined') ? data[ i]['municipality'] : 1;

			if( countOGD && hasOGD) {
				arrayOGD[country] += useMunicipality ? countMunicipality : population;
			}
			if( countNames && (hasOGDNames || hasWebNames)) {
				arrayNames[country] += useMunicipality ? countMunicipality : population;
			}
		}

		var arrayResult = [];
		arrayResult['Deutschland'] = 0;
		arrayResult['Österreich'] = 0;
		arrayResult['Schweiz'] = 0;

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
			arrayMax['Deutschland'] = 11116;
			arrayMax['Österreich'] =   2354;
			arrayMax['Schweiz'] =      2551;
			txt += 'Hochgerechnet nach der Anzahl der Kommunen.';
		} else {
			arrayMax['Deutschland'] = 80380000;
			arrayMax['Österreich'] =   8504850;
			arrayMax['Schweiz'] =      8112200;
			txt += 'Hochgerechnet nach der Einwohnerzahl der Kommunen.';
		}

		$( '#chart1').html( txt);
		$( '#chart1').trigger( "create");
		$( '#chart1').trigger( 'updatelayout');
		var chart1DE = Circles.create({
			id:'chart1DE',value:arrayResult['Deutschland'],maxValue:arrayMax['Deutschland'],
			colors:['#9ac9c6','#33a1df'],radius:50,width:10,duration:500,text:function(value){if(Math.round( value / arrayMax['Deutschland'] * 100) < 10) {return '<span>'+Math.round( value / arrayMax['Deutschland'] * 1000)/10+'%</span>';} else {return '<span>'+Math.round( value / arrayMax['Deutschland'] * 100)+'%</span>';}},wrpClass:'circles-wrp',textClass:'circles-text',
		});
		var chart1AT = Circles.create({
			id:'chart1AT',value:arrayResult['Österreich'],maxValue:arrayMax['Österreich'],
			colors:['#9ac9c6','#33a1df'],radius:50,width:10,duration:500,text:function(value){if(Math.round( value / arrayMax['Österreich'] * 100) < 10) {return '<span>'+Math.round( value / arrayMax['Österreich'] * 1000)/10+'%</span>';} else {return '<span>'+Math.round( value / arrayMax['Österreich'] * 100)+'%</span>';}},wrpClass:'circles-wrp',textClass:'circles-text',
		});
		var chart1CH = Circles.create({
			id:'chart1CH',value:arrayResult['Schweiz'],maxValue:arrayMax['Schweiz'],
			colors:['#9ac9c6','#33a1df'],radius:50,width:10,duration:500,text:function(value){if(Math.round( value / arrayMax['Schweiz'] * 100) < 10) {return '<span>'+Math.round( value / arrayMax['Schweiz'] * 1000)/10+'%</span>';} else {return '<span>'+Math.round( value / arrayMax['Schweiz'] * 100)+'%</span>';}},wrpClass:'circles-wrp',textClass:'circles-text',
		});
	} catch( e) {
	}
}

// -----------------------------------------------------------------------------

function showPage( pageName)
{
	$( '#mapDetailsDiv').html( $( pageName).html());
//	$( pageName).popup( 'open');

	if( '#popupCharts') {
		$( '#choiceSourceOGD').on( 'click', function( e) { generateCharts(); });
		$( '#choiceSourceNames').on( 'click', function( e) { generateCharts(); });
		$( '#choiceCalcCitizen').on( 'click', function( e) { generateCharts(); });
		$( '#choiceCalcMunicipality').on( 'click', function( e) { generateCharts(); });
	}
}

// -----------------------------------------------------------------------------

$( document).on( "pagecreate", "#pageMap", function()
{
	initNokiaMap( 'mapContainer', 52.516, 13.4795, 6);

	addMarker();

	map.addListener( "displayready", function () {
		generateCharts();

		showPage( '#popupStart');
	});

	$( '#aPopupStart').on( 'click', function( e) { showPage( '#popupStart'); return false; });
	$( '#aPopupCharts').on( 'click', function( e) { showPage( '#popupCharts'); return false; });
	$( '#aPopupSamples').on( 'click', function( e) { showPage( '#popupSamples'); return false; });
	$( '#aPopupContests').on( 'click', function( e) { showPage( '#popupContests'); return false; });
	$( '#aPopupShare').on( 'click', function( e) { showPage( '#popupShare'); return false; });
	$( '#aPopupCopyright').on( 'click', function( e) { showPage( '#popupCopyright'); return false; });
});

// -----------------------------------------------------------------------------

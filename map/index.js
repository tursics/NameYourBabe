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
			baseMapType: nokia.maps.map.Display.SMARTMAP // NORMAL NORMAL_COMMUNITY SATELLITE SATELLITE_COMMUNITY  SMARTMAP SMART_PT TERRAIN TRAFFIC
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
		var cPortal = '#43a2ca';
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

				if( typeof this.data[ i]['linkOGD'] !== 'undefined') {
					bgColor = cPortal;
					str += '<i class="fa fa-check"></i> Hat ein <a href="' + this.data[ i]['linkOGD'] + '" target="_blank">Open Data Portal</a><br>';

					if( typeof this.data[ i]['linkOGDNames'] !== 'undefined') {
						bgColor = cGood;
						str += '<i class="fa fa-heart"></i> Enthält einen <a href="' + this.data[ i]['linkOGDNames'] + '" target="_blank">Vornamen-Datensatz</a><br>';

						if( typeof this.data[ i]['linkOGDLicense'] !== 'undefined') {
							var license = this.data[ i]['linkOGDLicense'];
							var good = false;

							if( 'CC BY 3.0' == license) {
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
					}
				} else if( typeof this.data[ i]['linkWebNames'] !== 'undefined') {
					bgColor = cYellow;
					str += '<i class="fa fa-times"></i> Hat kein Open Data Portal<br>';
					str += '<i class="fa fa-check"></i> Vornamen auf der <a href="' + this.data[ i]['linkWebNames'] + '" target="_blank">Webseite</a><br>';
				} else {
					bgColor = cDenied;
					str += '<i class="fa fa-times"></i> Hat kein Open Data Portal<br>';

					if( typeof this.data[ i]['history'] === 'undefined') {
						continue;
					}
				}

				str += '<br>';

				if( typeof this.data[ i]['history'] !== 'undefined') {
					var historySize = this.data[ i]['history'].length;
					for( var h = 0; h < historySize; ++h) {
						str += '<div style="border-top:1px solid #aaaaaa;color:#aaaaaa;padding-top:0.5em;margin-top:0.5em;">';
						str += '<i class="fa fa-calendar"></i> ' + this.data[ i]['history'][ h]['date'] + '<br>';
						str += '<i class="fa fa-comment-o"></i> ' + this.data[ i]['history'][ h]['event'] + '</div>';
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

$( document).on( "pagecreate", "#pageMap", function()
{
	initNokiaMap( 'mapContainer', 52.516, 13.4795, 6);

	addMarker();

	map.addListener( "displayready", function () {
		$( '#popupCopyright').popup( 'open');
//		sample1();
	});
});

// -----------------------------------------------------------------------------

//----------------------------
// pageSettings.js
//----------------------------

var gSettings = { /* defined in initialize_gSettings() */ };

//----------------------------

function initialize_gSettings( value)
{
	try {
		if( typeof value.filterNUTS !== "undefined") { gSettings.filterNUTS = value.filterNUTS; } else {
			gSettings.filterNUTS = '';
		}

		if( typeof value.filterGender !== "undefined") { gSettings.filterGender = value.filterGender; } else {
			gSettings.filterGender = 'b';
		}

		if( typeof value.enableShake !== "undefined") { gSettings.enableShake = value.enableShake; } else {
			gSettings.enableShake = true;
		}

		if( typeof value.enableSwipe !== "undefined") { gSettings.enableSwipe = value.enableSwipe; } else {
			gSettings.enableSwipe = true;
		}

		if( typeof value.language !== "undefined") { gSettings.language = value.language; } else {
			gSettings.language = 'auto';
		}

		if( 'auto' != gSettings.language) {
			CInternationalization.setLanguage( gSettings.language);
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

//----------------------------

function loadSettings()
{
	try {
		if( typeof Windows !== "undefined") {
//			Windows && Windows.Storage && Windows.Storage.ApplicationData
		}
		if( gShowWP) {
			window.external.notify( "localStorageGetItem:loadSettingsItem");
//			window.external.notify( "localStorageGetItem:gSettings:loadSettings_gSettings");
		} else
		if( typeof localStorage !== "undefined") {
			loadSettingsItem( localStorage.getItem( 'gFavs'));
			loadSettings_gSettings( localStorage.getItem( 'gSettings'));
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

//----------------------------

function saveFavToStorage( item)
{
	var found = -1;
	$.each( gSettingsFavs, function( index) {
		if( this.name == item.name) {
			found = index;
		}
	});

	if( found == -1) {
		if( item.fav > 0) {
			gSettingsFavs.push( item);
		}
	} else {
		if( item.fav > 0) {
			gSettingsFavs[ found] = item;
		} else {
			gSettingsFavs.splice( found, 1);
		}
	}

	saveSettings();
}

//----------------------------

function saveSettings()
{
	var value = gSettingsFavs;
	try {
		if( typeof localStorage !== "undefined") {
			if( typeof value == "object") {
				value = JSON.stringify( value);
			}
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}

	try {
		if( typeof localStorage !== "undefined") {
			localStorage.removeItem( 'gFavs');
		}
	} catch( e) {
	}

	try {
		if( gShowWP) {
			window.external.notify( "localStorageSetItem:" + value);
		} else
		if( typeof localStorage !== "undefined") {
			localStorage.setItem( 'gFavs', value);
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

//----------------------------

function saveSettings_gSettings()
{
	var value = gSettings;
	try {
		if( typeof localStorage !== "undefined") {
			if( typeof value == "object") {
				value = JSON.stringify( value);
			}
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}

	try {
		if( typeof localStorage !== "undefined") {
			localStorage.removeItem( 'gSettings');
		}
	} catch( e) {
	}

	try {
		if( gShowWP) {
			window.external.notify( "localStorageSetItem:" + value);
		} else
		if( typeof localStorage !== "undefined") {
			localStorage.setItem( 'gSettings', value);
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

//----------------------------

function loadSettingsItem(value)
{
	try {
		if(( null != value) && (value[ 0] == "{")) {
			value = JSON.parse( value);
		} else if(( null != value) && (value[ 0] == "[")) {
			value = JSON.parse( value);
		}
		if(( null != value) && ('' != value)) {
			gSettingsFavs = value;
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

//----------------------------

function loadSettings_gSettings( value)
{
	try {
		if(( null != value) && (value[ 0] == "{")) {
			value = JSON.parse( value);
		} else if(( null != value) && (value[ 0] == "[")) {
			value = JSON.parse( value);
		}
		if(( null != value) && ('' != value)) {
			initialize_gSettings( value);
		} else {
			value = {};
			initialize_gSettings( value);
		}
	} catch( e) {
		if( gDebug) {
			alert(e);
		}
	}
}

//----------------------------

function onPageSettingsHome()
{
	gWebViewTitle = _( 'settingsWebHome');
	gWebViewHref = 'http://tursics.de';
}

//----------------------------

function onPageSettingsSupport()
{
	gWebViewTitle = _( 'settingsWebSupport');
	gWebViewHref = 'http://tursics.de/support';
}

//----------------------------

$( document).on( 'pageshow', '#pageSettings',  function()
{
	try {
		init();

		var txt = '';

		txt += '<ul data-role="listview" data-theme="a">';
		txt += '<li data-role="list-divider"><br>' + _( 'settingsNewName') + '</li>';

		txt += '<li class="ui-field-contain afterDivider"><label for="flipSwipe">' + _( 'settingsSwipe') + '</label>';
		txt += '<select name="flipSwipe" id="flipSwipe" data-role="slider" data-mini="true">';
		txt += '<option value="off">' + _( 'settingsOff') + '</option>';
		txt += '<option value="on">' + _( 'settingsOn') + '</option>';
		txt += '</select>';
		txt += '</li>';

		txt += '<li class="ui-field-contain"><label for="flipShake">' + _( 'settingsShake') + '</label>';
		txt += '<select name="flipShake" id="flipShake" data-role="slider" data-mini="true">';
		txt += '<option value="off">' + _( 'settingsOff') + '</option>';
		txt += '<option value="on">' + _( 'settingsOn') + '</option>';
		txt += '</select>';
		txt += '</li>';

		txt += '<li data-role="list-divider"><br>' + _( 'settingsLanguage') + '</li>';
		txt += '<li class="ui-field-contain afterDivider" style="padding:0;">';
		txt += '<select name="choiceLang" id="choiceLang">';
		txt += '<option value="auto">' + _( 'settingsAutomatic') + '</option>';
		txt += '<option value="en">English</option>';
		txt += '<option value="de">Deutsch</option>';
//		txt += '<option value="ku">کوردی</option>';
		txt += '</select>';
		txt += '</li>';

		txt += '</ul>';

		$( '#divSettings').html( txt);
		$( '#divSettings').trigger( "create");
		$( '#divSettings').trigger( 'updatelayout');

		$( "#h1Settings").html( _( 'canvasSettings'));
		$( "#settingsBack").html( _( 'settingsDone'));
		if( gShowWP) {
			$( "#settingsBack_").css( 'display', 'none');
		}
	} catch( e) {
		if( gShowWP) {
			window.external.notify( "applicationBarClear");
		}
	}

	$('#flipSwipe').val( gSettings.enableSwipe ? 'on' : 'off').slider('refresh');
	$('#flipShake').val( gSettings.enableShake ? 'on' : 'off').slider('refresh');

	if( 'auto' == gSettings.language) {
		$('#choiceLang')[0].selectedIndex = 0;
		$('#choiceLang').selectmenu('refresh');
	} else if( 'en' == gSettings.language) {
		$('#choiceLang')[0].selectedIndex = 1;
		$('#choiceLang').selectmenu('refresh');
	} else if( 'de' == gSettings.language) {
		$('#choiceLang')[0].selectedIndex = 2;
		$('#choiceLang').selectmenu('refresh');
	}

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
	}
});

//----------------------------

$( document).on( 'pagehide', '#pageSettings',  function()
{
	gSettings.enableSwipe = ('on' == $('#flipSwipe').val());
	gSettings.enableShake = ('on' == $('#flipShake').val());
	gSettings.language = $('#choiceLang').val();

	if( 'auto' == gSettings.language) {
		CInternationalization.init();
	} else {
		CInternationalization.setLanguage( gSettings.language);
	}

	saveSettings_gSettings();
});

//----------------------------
// eof

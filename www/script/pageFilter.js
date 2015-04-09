//----------------------------
// pageFilter.js
//----------------------------

//----------------------------

function isFilteredName( nameObj)
{
//	if( nameObj.charts.length == 0) {
//		return false;
//	}
	if(( 'm' == gSettings.filterGender) && ('f' == nameObj.gender)) {
		return false;
	}
	if(( 'f' == gSettings.filterGender) && ('m' == nameObj.gender)) {
		return false;
	}
//	if( nameObj.name.length < 9) {
//		return false;
//	}
//	if( nameObj.name.length > 3) {
//		return false;
//	}
	if(( '' != gSettings.filterNUTS) && (typeof nameObj.charts !== "undefined")) {
		var found = false;
		$.each( nameObj.charts, function() {
			var vec = this.split( '-');
// ignore Nuremberg (license issues)
if( 'DE254' == vec[3]) {
	return true;
}
			if( 0 == vec[3].search( gSettings.filterNUTS)) {
				found = true;
				return;
			}
		});
		if( !found) {
			return false;
		}
	}
//	gSettings.filterNUTS = $('#choiceNUTS1').val();

	return true;
}

//----------------------------

function isFilteredNameId( nameId)
{
	return isFilteredName( gDataName[nameId]);
}

//----------------------------

$( document).on( 'pageshow', '#pageFilter',  function()
{
	try {
		init();

		var txt = '';

		txt += '<div style="color:#808080;">' + _( 'filterText') + '</div>';
		txt += '<ul data-role="listview" data-theme="a">';
		txt += '<li data-role="list-divider"><br></li>';
		txt += '<li class="ui-field-contain afterDivider" style="padding:0;">';
		txt += '<select name="choiceGender" id="choiceGender">';
		txt += '<option value="b">' + _( 'homeBoyGirl') + '</option>';
		txt += '<option value="m">' + _( 'homeBoy') + '</option>';
		txt += '<option value="f">' + _( 'homeGirl') + '</option>';
		txt += '</select>';
		txt += '</li>';

		var max = gDataSource.length;
		var nuts1 = [];
		for( var i = 0; i < max; ++i) {
			if( 2 == gDataSource[i].nuts.length) {
				nuts1.push({ val: gDataSource[i].nuts, txt: gDataSource[i].name[CInternationalization.lang_] });
			}
		}
		nuts1.sort( function( left, right) {
			return left.txt > right.txt;
		});

		txt += '<li data-role="list-divider"><br></li>';
		txt += '<li class="ui-field-contain afterDivider" style="padding:0;">';
		txt += '<select name="choiceNUTS1" id="choiceNUTS1">';
		txt += '<option value="">' + _( 'filterWorld') + '</option>';
		var maxnuts = nuts1.length;
		for( var i = 0; i < maxnuts; ++i) {
			txt += '<option value="' + nuts1[i].val + '">' + nuts1[i].txt + '</option>';
		}
		txt += '</select>';
		txt += '</li>';

		txt += '<li class="ui-field-contain" id="liNUTS2" style="padding:0;display:none;">';
		txt += '<select name="choiceNUTS2" id="choiceNUTS2">';
		txt += '</select>';
		txt += '</li>';

		txt += '<li class="ui-field-contain" id="liNUTS3" style="padding:0;display:none;">';
		txt += '<select name="choiceNUTS3" id="choiceNUTS3">';
		txt += '</select>';
		txt += '</li>';

		txt += '</ul>';

		$( '#divFilter').html( txt);
		$( '#divFilter').trigger( "create");
		$( '#divFilter').trigger( 'updatelayout');

		$( "#h1Filter").html( _( 'filterTitle'));
		$( "#filterBack").html( _( 'settingsDone'));
		if( gShowWP) {
			$( "#filterBack_").css( 'display', 'none');
		}

		$( "#choiceNUTS1").change( function() { updateNUTS2List(); });
		$( "#choiceNUTS2").change( function() { updateNUTS3List(); });
	} catch( e) {
		if( gShowWP) {
			window.external.notify( "applicationBarClear");
		}
	}

	if( 'b' == gSettings.filterGender) {
		$('#choiceGender')[0].selectedIndex = 0;
		$('#choiceGender').selectmenu('refresh');
	} else if( 'm' == gSettings.filterGender) {
		$('#choiceGender')[0].selectedIndex = 1;
		$('#choiceGender').selectmenu('refresh');
	} else if( 'f' == gSettings.filterGender) {
		$('#choiceGender')[0].selectedIndex = 2;
		$('#choiceGender').selectmenu('refresh');
	}

	for( var i = 0; i < maxnuts; ++i) {
		if( nuts1[i].val == gSettings.filterNUTS.substr( 0, 2)) {
			$('#choiceNUTS1')[0].selectedIndex = 1 + i;
			$('#choiceNUTS1').selectmenu('refresh');
			break;
		}
	}

	if( gShowWP) {
		window.external.notify( "applicationBarClear");
	}

	updateNUTS2List();
});

//----------------------------

function updateNUTS2List()
{
	fillNUTSList( $('#choiceNUTS1'), $( '#choiceNUTS2'), $( '#liNUTS2'));

	updateNUTS3List();
}

//----------------------------

function updateNUTS3List()
{
	fillNUTSList( $('#choiceNUTS2'), $( '#choiceNUTS3'), $( '#liNUTS3'));
}

//----------------------------

function fillNUTSList( objParent, objThis, objList)
{
	try {
		var max = gDataSource.length;
		var nutsParent = ( 0 < objParent.html().length ? objParent.val() : '');
		var nutsOptions = [];
		var nutsTitle = {};
		var nutsSel = nutsParent;

		var nutsLen = nutsParent.length + 1;
		if(( '' != nutsParent) && (0 == objParent[0].selectedIndex)) {
			nutsParent = '';
		}
		if(( 'AT' == nutsParent) || ('CH' == nutsParent)) {
			++nutsLen;
		} else if(( 'DE1' == nutsParent) || ('DE2' == nutsParent) || ('DE3' == nutsParent) || ('DE5' == nutsParent) || ('DEA' == nutsParent) || ('DED' == nutsParent) || ('AT13' == nutsParent)) {
			++nutsLen;
		} else if(( 'DE6' == nutsParent)) {
			nutsLen += 2;
		} else if(( 'AT31' == nutsParent) || ('AT32' == nutsParent)) {
			nutsLen += 5;
		}
		var nutsSetting = gSettings.filterNUTS.substr( 0, nutsLen);

		for( var i = 0; i < max; ++i) {
			if( nutsParent == gDataSource[i].nuts) {
				nutsTitle = { val: gDataSource[i].nuts, txt: gDataSource[i].name[CInternationalization.lang_] };
			}
			if(( nutsLen == gDataSource[i].nuts.length) && (nutsParent == gDataSource[i].nuts.substr( 0, nutsParent.length))) {
				nutsOptions.push({ val: gDataSource[i].nuts, txt: gDataSource[i].name[CInternationalization.lang_] });

				if( nutsSetting == gDataSource[i].nuts) {
					nutsSel = gDataSource[i].nuts;
				}
			}
		}
		nutsOptions.sort( function( left, right) {
			return left.txt > right.txt;
		});

		var maxnuts = nutsOptions.length;
		var txt = '';

		if(( '' != nutsParent) && (0 < maxnuts)) {
			txt += '<option value="' + nutsTitle.val + '"' + (nutsParent == nutsSel ? ' selected=""' : '') + '>' + nutsTitle.txt + '</option>';
			for( var i = 0; i < maxnuts; ++i) {
				txt += '<option value="' + nutsOptions[i].val + '"' + (nutsOptions[i].val == nutsSel ? ' selected=""' : '') + '>' + nutsOptions[i].txt + '</option>';
			}
		}

		objThis.html( txt);
		objThis.trigger( 'updatelayout');
		objThis.selectmenu('refresh');
		objList.css( 'display', 0 == txt.length ? 'none' : 'block');
	} catch( e) {
		console.log(e);
	}
}

//----------------------------

$( document).on( 'pagehide', '#pageFilter',  function()
{
	gSettings.filterGender = $('#choiceGender').val();
	gSettings.filterNUTS = $('#choiceNUTS1').val();
	if( 0 < $( '#choiceNUTS2').html().length) {
		gSettings.filterNUTS = $('#choiceNUTS2').val();
	}
	if( 0 < $( '#choiceNUTS3').html().length) {
		gSettings.filterNUTS = $('#choiceNUTS3').val();
	}

	gRandomNames = [];

	saveSettings_gSettings();
});

//----------------------------
// eof

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
	try {
		var max = gDataSource.length;
		var nuts1 = $('#choiceNUTS1').val();
		var nuts2 = [];
		var nutsTitle = {};
		var nutsSel = nuts1;

		var nutsLen = 3;
		if(( 'AT' == nuts1) || ('CH' == nuts1)) {
			++nutsLen;
		}
		var nutsSetting = gSettings.filterNUTS.substr( 0, nutsLen);

		for( var i = 0; i < max; ++i) {
			if( nuts1 == gDataSource[i].nuts) {
				nutsTitle = { val: gDataSource[i].nuts, txt: gDataSource[i].name[CInternationalization.lang_] };
			}
			if(( nutsLen == gDataSource[i].nuts.length) && (nuts1 == gDataSource[i].nuts.substr( 0, 2))) {
				nuts2.push({ val: gDataSource[i].nuts, txt: gDataSource[i].name[CInternationalization.lang_] });

				if( nutsSetting == gDataSource[i].nuts) {
					nutsSel = gDataSource[i].nuts;
				}
			}
		}
		nuts2.sort( function( left, right) {
			return left.txt > right.txt;
		});

		var maxnuts = nuts2.length;
		var txt = '';

		if( '' != nuts1) {
			txt += '<option value="' + nutsTitle.val + '"' + (nuts1 == nutsSel ? ' selected=""' : '') + '>' + nutsTitle.txt + '</option>';
			for( var i = 0; i < maxnuts; ++i) {
				txt += '<option value="' + nuts2[i].val + '"' + (nuts2[i].val == nutsSel ? ' selected=""' : '') + '>' + nuts2[i].txt + '</option>';
			}
		}

		$( '#choiceNUTS2').html( txt);
		$( '#choiceNUTS2').trigger( 'updatelayout');
		$( '#choiceNUTS2').selectmenu('refresh');
		$( "#liNUTS2").css( 'display', 0 == txt.length ? 'none' : 'block');
	} catch( e) {
		console.log(e);
	}
}

//----------------------------

$( document).on( 'pagehide', '#pageFilter',  function()
{
	gSettings.filterGender = $('#choiceGender').val();
	gSettings.filterNUTS = $('#choiceNUTS1').val();
	if( 2 == gSettings.filterNUTS.length) {
		gSettings.filterNUTS = $('#choiceNUTS2').val();
	}

	gRandomNames = [];

	saveSettings_gSettings();
});

//----------------------------
// eof

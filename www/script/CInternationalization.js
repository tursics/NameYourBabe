//--------------------------------
//--- CInternationalization.js ---
//--------------------------------

CInternationalization = new function()
{
	//------------------------
	this.init = function()
	{
		if( typeof gDict === "undefined") {
			alert( 'Include some dict files');
		}

		var user = (navigator.language) ? navigator.language : navigator.userLanguage; 
		user = user.toLowerCase();

		var langCode = 'en';

		if( 'zh-hant' == user) {
			user = 'zh-tw';
		} else if( 'zh-hk' == user) {
			user = 'zh-tw';
		}

		if( gDict[ user]) {
			langCode = user;
		} else if( gDict[ user.substr( 0, 2)]) {
			langCode = user.substr( 0, 2);
		}

		this.setLanguage( langCode);
	};

	//------------------------
	this.setLanguage = function( langCode)
	{
		this.dict_ = gDict[ langCode];
		this.lang_ = this.get( "appLang");
	};

	//------------------------
	this.get = function( text)
	{
		if( this.dict_[ text]) {
			return this.dict_[ text];
		}
		if( typeof gDict === "undefined") {
			alert( 'I18n not found "' + text + '".');
			return text;
		}
		if (gDict['en'][text]) {
		    return gDict['en'][text];
		}
		return text;
	};

	//------------------------
	this.dict_ = new Array();
	this.lang_ = '';

	//------------------------
};

//----------------------------
// _( 'Hello Dunder');

function _( text)
{
	return CInternationalization.get( text);
}

//----------------------------
// pluralise( 'carrot', 'carrots', carrots);

function pluralise( s, p, n)
{
	if( n != 1) {
		return _( p);
	}

	return _( s);
}

//----------------------------
// sprintf( pluralise( '%s carrot', '%s carrots', carrots), carrots);

function sprintf( s)
{
	var bits = s.split( '%');
	var out = bits[ 0];
	var re = /^([ds])(.*)$/;

	for( var i = 1; i < bits.length; ++i) {
		p = re.exec( bits[ i]);
		if( !p || arguments[ i] == null) {
			continue;
		}
		if( p[ 1] == 'd') {
			out += parseInt( arguments[ i], 10);
		} else if ( p[ 1] == 's') {
			out += arguments[ i];
		}
		out += p[ 2];
	}

	return out;
}

//----------------------------
// eof

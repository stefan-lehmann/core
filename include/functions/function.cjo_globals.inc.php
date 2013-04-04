<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     contejo
 * @subpackage  core
 * @version     2.7.x
 *
 * @author      Stefan Lehmann <sl@contejo.com>
 * @copyright   Copyright (c) 2008-2012 CONTEJO. All rights reserved. 
 * @link        http://contejo.com
 *
 * @license     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *  CONTEJO is free software. This version may have been modified pursuant to the
 *  GNU General Public License, and as distributed it includes or is derivative
 *  of works licensed under the GNU General Public License or other free or open
 *  source software licenses. See _copyright.txt for copyright notices and
 *  details.
 * @filesource
 */
 
/**
 * Gibt die Superglobale variable $varname des Array $_GET zurück und castet dessen Wert ggf.
 *
 * Falls die Variable nicht vorhanden ist, wird $default zurückgegeben
 */
function cjo_get($varname, $vartype = '', $default = '', $secure = NULL, $function = NULL) {
    $secure = $secure === NULL && !cjoProp::isBackend() ? true : $secure;    
    $data =  _cjo_array_key_cast($_GET, $varname, $vartype, $default);
    $data = $secure == true ? cjoAssistance::cleanInput($data) : $data;
	return $function = NULL ? $data : _cjo_call_user_func($function, $data);
}

/**
 * Gibt die Superglobale variable $varname des Array $_POST zurück und castet dessen Wert ggf.
 *
 * Falls die Variable nicht vorhanden ist, wird $default zurückgegeben
 */
function cjo_post($varname, $vartype = '', $default = '', $secure = NULL, $function = NULL) {
    $secure = $secure === NULL && !cjoProp::isBackend() ? true : $secure;
    $data =  _cjo_array_key_cast($_POST, $varname, $vartype, $default);
    $data = $secure == true ? cjoAssistance::cleanInput($data) : $data;
    return $function = NULL ? $data : _cjo_call_user_func($function, $data);
}

/**
 * Gibt die Superglobale variable $varname des Array $_REQUEST zurück und castet dessen Wert ggf.
 *
 * Falls die Variable nicht vorhanden ist, wird $default zurückgegeben
 */
function cjo_request($varname, $vartype = '', $default = '', $secure = NULL, $function = NULL) {
    $secure = $secure === NULL && !cjoProp::isBackend() ? true : $secure;    
    $data =  _cjo_array_key_cast($_REQUEST, $varname, $vartype, $default);
    $data = $secure == true ? cjoAssistance::cleanInput($data) : $data;
	return $function = NULL ? $data : _cjo_call_user_func($function, $data);
}

/**
 * Gibt die Superglobale variable $varname des Array $_SERVER zurück und castet dessen Wert ggf.
 *
 * Falls die Variable nicht vorhanden ist, wird $default zurückgegeben
 */
function cjo_server($varname, $vartype = '', $default = '') {
    return _cjo_array_key_cast($_SERVER, $varname, $vartype, $default);
}

/**
 * Gibt unterschiedliche System-Ids für Frontend und Backend zurück
 */
function cjo_get_sys_id($backend=false) {
    return cjoProp::isBackend() || $backend ? md5(cjoProp::getInstname()) : md5(cjoProp::getServerName());
}

/**
 * Gibt die Superglobale variable $varname des Array $_SESSION zurück und castet dessen Wert ggf.
 *
 * Falls die Variable nicht vorhanden ist, wird $default zurückgegeben
 */
function cjo_session($varname, $vartype = '', $default = '', $sys_id = false) {
    if (!$sys_id) $sys_id = cjo_get_sys_id();   
    if (isset ($_SESSION[$sys_id][$varname])) {
        return _cjo_cast_var($_SESSION[$sys_id][$varname], $vartype, $default);
    }
    return $default;
}

/**
 * Setzt den Wert einer Session Variable.
 *
 * Variablen werden Instanzabhängig gespeichert.
 */
function cjo_set_session($varname, $value, $sys_id = false) {
    
    if ($varname == 'INST') {
        if (cjo_session('INST', 'bool', $value)) return true;
        $value = (int) preg_replace('/\D/', '', sha1(rand(100000,9999999)));
    }
    if (!$sys_id) $sys_id = cjo_get_sys_id();
    $_SESSION[$sys_id][$varname] = $value;
}

/**
 * Löscht den Wert einer Session Variable.
 *
 * Variablen werden Instanzabhängig gelöscht.
 */
function cjo_unset_session($varname, $sys_id = false) {
    if (!$sys_id) $sys_id = cjo_get_sys_id();
    
    if (isset($_SESSION[$sys_id][$varname])) 
        unset ($_SESSION[$sys_id][$varname]);
}

/**
 * Setzt die Id der aktuellen Session neu.
 *
 */
function cjo_regenerate_session() {

    $sys_id = cjo_get_sys_id();
    $temp = $_SESSION[$sys_id];
    session_unset();
    session_regenerate_id();
    $_SESSION[$sys_id] = $temp;
    //unset($temp);
}    

/**
 * Löscht die Session der Instanz.
 */
function cjo_destroy_session() {
    session_destroy();
}

/**
 * Gibt die Superglobale variable $varname des Array $_COOKIE zurück und castet dessen Wert ggf.
 *
 * Falls die Variable nicht vorhanden ist, wird $default zurückgegeben
 */
function cjo_cookie($varname, $vartype = '', $default = '') {
    return _cjo_array_key_cast($_COOKIE, $varname, $vartype, $default);
}

/**
 * Gibt die Superglobale variable $varname des Array $_FILES zurück und castet dessen Wert ggf.
 *
 * Falls die Variable nicht vorhanden ist, wird $default zurückgegeben
 */
function cjo_files($varname, $vartype = '', $default = '') {
    return _cjo_array_key_cast($_FILES, $varname, $vartype, $default);
}

/**
 * Gibt die Superglobale variable $varname des Array $_ENV zurück und castet dessen Wert ggf.
 *
 * Falls die Variable nicht vorhanden ist, wird $default zurückgegeben
 */
function cjo_env($varname, $vartype = '', $default = '') {
    return _cjo_array_key_cast($_ENV, $varname, $vartype, $default);
}

/**
 * Durchsucht das Array $haystack nach dem Schlüssel $needle.
 *
 * Falls ein Wert gefunden wurde wird dieser nach
 * $vartype gecastet und anschließend zurückgegeben.
 *
 * Falls die Suche erfolglos endet, wird $default zurückgegeben
 *
 * @access private
 */
function _cjo_array_key_cast($haystack, $needle, $vartype, $default = '') {

    if (!is_array($haystack)) {
        throw new cjoException('Array expected for $haystack in _cjo_array_key_cast()!', E_USER_ERROR);
        exit();
    }

    if (!is_scalar($needle)) {
        throw new cjoException('Scalar expected for $needle in _cjo_array_key_cast()!', E_USER_ERROR);
        exit();
    }

    if ($needle != '' && array_key_exists($needle, $haystack)) {
        return _cjo_cast_var($haystack[$needle], $vartype, $default, 'found');
    }

    if ($default === '') {
        return _cjo_cast_var($default, $vartype, $default, 'default');
    }

    return $default;
}

/**
 * Castet die Variable $var zum Typ $vartype
 *
 * Mögliche Typen sind:
 *  - bool (auch boolean)
 *  - int (auch integer)
 *  - double
 *  - string
 *  - float
 *  - object
 *  - array
 *  - '' (nicht casten)
 *
 * @access private
 */
function _cjo_cast_var($var, $vartype, $default, $mode='default') {

    if (!is_string($vartype)) {
        throw new cjoException('String expected for $vartype in _cjo_cast_var()!', E_USER_ERROR);
        exit();
    }

    switch ($vartype) {

        case 'cjo-article-id':
            $var = (int) $var;
            if ($mode == 'found') {
                if (!OOArticle::isValid(OOArticle::getArticleById($var)))
                $var = (int) $default;
            }
            break;
        case 'cjo-lang-id':
        case 'cjo-clang-id':
            $var = (int) $var;
            if ($mode == 'found') {
                if (!cjoProp::getClang($var)) $var = (int) $default;
            }
            break;
        case 'cjo-template-id':
        case 'cjo-ctype-id':
        case 'cjo-slice-id':
        case 'cjo-module-id':
        case 'cjo-action-id':
        case 'cjo-media-id':
        case 'cjo-mediacategory-id':
        case 'cjo-user-id':
            $var = (int) $var;
            break;

        case 'bool'   :
        case 'boolean':
            $var = (boolean) $var;
            break;
        case 'int'    :
        case 'integer':
            $var = (int)     $var;
            break;
        case 'double' :
            $var = (double)  $var;
            break;
        case 'float'  :
        case 'real'   :
            $var = (float)   $var;
            break;
        case 'string' :
            $var = (string)  $var;
            break;
        case 'object' :
            $var = (object)  $var;
            break;
        case 'array'  :
            $var = (empty($var)) ? array() : (array) $var;
            break;
            // kein Cast, nichts tun
        case ''       : break;

        // Evtl Typo im vartype, deshalb hier fehlermeldung!
        default: throw new cjoException('Unexpected vartype "'. $vartype .'" in _cjo_cast_var()!', E_USER_ERROR); exit();
    }

    return $var;
}

/**
 * Generates an url friendly string.
 * @param string $name
 * @return string
 * @access public
 */
function cjo_url_friendly_string($string) {
    $string = str_replace(array(' ', ' -- ',' - ','.'), '-', trim($string));
    $string = html_entity_decode($string);
    $string = cjo_specialchars($string);     
    $string = preg_replace("/[^a-zA-Z\-0-9]/", "", $string);
    $string = preg_replace('/-{1,}/', '-', $string);   
    return $string;
}

function cjo_specialchars($value){

    $specials = array(' ', '&', 'á', 'Á', 'à', 'À', 'â', 'Â', 'å', 'Å', 'ã', 'Ã', 'ä', 'Ä', 'æ' , 'Æ' , 'ç', 'Ç', 'é', 'É', 'è', 'È', 'ê', 'Ê', 'ë', 'Ë', 'í', 'Í', 'ì', 'Ì', 'î', 'Î', 'ï', 'Ï', 'ñ', 'Ñ', 'ó', 'Ó', 'ò', 'Ò', 'ô', 'Ô', 'ø', 'Ø', 'õ', 'Õ', 'ö', 'Ö', 'ß' , 'ú', 'Ú', 'ù', 'Ù', 'û', 'Û', 'ü', 'Ü', 'ÿ', '´', '`','(',')','[',']','{','}','%',':','/');
    $save =     array('_', '+', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'ae', 'Ae', 'ae', 'AE', 'c', 'C', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'n', 'N', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'oe', 'Oe', 'ss', 'u', 'U', 'u', 'U', 'u', 'U', 'ue', 'Ue', 'y', '-', '-','','','','','','','','','');

    return strtolower(str_replace($specials,$save,$value));
}

function cjo_get_country_codes(){

    return array ('AF' => 'Afghanistan ',
				  'AX' => 'Aland Islands',
				  'AL' => 'Albania',
				  'DZ' => 'Algeria',
				  'AS' => 'American Samoa',
				  'AD' => 'Andorra',
				  'AO' => 'Angola',
				  'AI' => 'Anguilla',
				  'AG' => 'Antigua and Barbuda',
				  'AR' => 'Argentina',
				  'AM' => 'Armenia',
				  'AW' => 'Aruba',
				  'AU' => 'Australia',
				  'AT' => 'Austria',
				  'AZ' => 'Azerbaijan',
				  'BS' => 'Bahamas',
				  'BH' => 'Bahrain',
				  'BD' => 'Bangladesh',
				  'BB' => 'Barbados',
				  'BY' => 'Belarus',
				  'BE' => 'Belgium',
				  'BZ' => 'Belize',
				  'BJ' => 'Benin',
				  'BM' => 'Bermuda',
				  'BT' => 'Bhutan',
				  'BO' => 'Bolivia',
				  'BA' => 'Bosnia and Herzegovina',
				  'BW' => 'Botswana',
				  'BV' => 'Bouvet Island',
				  'BR' => 'Brazil',
				  'IO' => 'British Indian Ocean Territory',
				  'BN' => 'Brunei Darussalam',
				  'BG' => 'Bulgaria',
				  'BF' => 'Burkina Faso',
				  'BI' => 'Burundi',
				  'KH' => 'Cambodia',
				  'CM' => 'Cameroon',
				  'CA' => 'Canada',
				  'CV' => 'Cape Verde',
				  'KY' => 'Cayman Islands',
				  'CF' => 'Central African Republic',
				  'TD' => 'Chad',
				  'CL' => 'Chile',
				  'CN' => 'China',
				  'CX' => 'Christmas Island',
				  'CC' => 'Cocos (Keeling) Islands',
				  'CO' => 'Colombia',
				  'KM' => 'Comoros',
				  'CG' => 'Congo',
				  'CD' => 'Congo, Democratic Republic of the',
				  'CK' => 'Cook Islands',
				  'CR' => 'Costa Rica',
				  'CI' => 'Côte d\'Ivoire',
				  'HR' => 'Croatia',
				  'CU' => 'Cuba',
				  'CY' => 'Cyprus',
				  'CZ' => 'Czech Republic',
				  'DK' => 'Denmark',
				  'DE' => 'Deutschland',
				  'DJ' => 'Djibouti',
				  'DM' => 'Dominica',
				  'DO' => 'Dominican Republic',
				  'EC' => 'Ecuador',
				  'EG' => 'Egypt',
				  'SV' => 'El Salvador',
				  'GQ' => 'Equatorial Guinea',
				  'ER' => 'Eritrea',
				  'EE' => 'Estonia',
				  'ET' => 'Ethiopia',
				  'FK' => 'Falkland Islands (Malvinas)',
				  'FO' => 'Faroe Islands',
				  'FJ' => 'Fiji',
				  'FI' => 'Finland',
				  'FR' => 'France',
				  'GF' => 'French Guiana',
				  'PF' => 'French Polynesia',
				  'TF' => 'French Southern Territories',
				  'GA' => 'Gabon',
				  'GM' => 'Gambia',
				  'GE' => 'Georgia',
				  'GH' => 'Ghana',
				  'GI' => 'Gibraltar',
				  'GR' => 'Greece',
				  'GL' => 'Greenland',
				  'GD' => 'Grenada',
				  'GP' => 'Guadeloupe',
				  'GU' => 'Guam',
				  'GT' => 'Guatemala',
				  'GG' => 'Guernsey',
				  'GN' => 'Guinea',
				  'GW' => 'Guinea-Bissau',
				  'GY' => 'Guyana',
				  'HT' => 'Haiti',
				  'HM' => 'Heard Island and McDonald Islands',
				  'VA' => 'Holy See (Vatican City State)',
				  'HN' => 'Honduras',
				  'HK' => 'Hong Kong',
				  'HU' => 'Hungary',
				  'IS' => 'Iceland',
				  'IN' => 'India',
				  'ID' => 'Indonesia',
				  'IR' => 'Iran, Islamic Republic of',
				  'IQ' => 'Iraq',
				  'IE' => 'Ireland',
				  'IM' => 'Isle of Man',
				  'IL' => 'Israel',
				  'IT' => 'Italy',
				  'JM' => 'Jamaica',
				  'JP' => 'Japan',
				  'JE' => 'Jersey',
				  'JO' => 'Jordan',
				  'KZ' => 'Kazakhstan',
				  'KE' => 'Kenya',
				  'KI' => 'Kiribati',
				  'KP' => 'Korea, Democratic People\'s Republic of',
				  'KR' => 'Korea, Republic of',
				  'KW' => 'Kuwait',
				  'KG' => 'Kyrgyzstan',
				  'LA' => 'Lao People\'s Democratic Republic',
				  'LV' => 'Latvia',
				  'LB' => 'Lebanon',
				  'LS' => 'Lesotho',
				  'LR' => 'Liberia',
				  'LY' => 'Libyan Arab Jamahiriya',
				  'LI' => 'Liechtenstein',
				  'LT' => 'Lithuania',
				  'LU' => 'Luxembourg',
				  'MO' => 'Macao',
				  'MK' => 'Macedonia',
				  'MG' => 'Madagascar',
				  'MW' => 'Malawi',
				  'MY' => 'Malaysia',
				  'MV' => 'Maldives',
				  'ML' => 'Mali',
				  'MT' => 'Malta',
				  'MH' => 'Marshall Islands',
				  'MQ' => 'Martinique',
				  'MR' => 'Mauritania',
				  'MU' => 'Mauritius',
				  'YT' => 'Mayotte',
				  'MX' => 'Mexico',
				  'FM' => 'Micronesia, Federated States of',
				  'MD' => 'Moldova',
				  'MC' => 'Monaco',
				  'MN' => 'Mongolia',
				  'ME' => 'Montenegro',
				  'MS' => 'Montserrat',
				  'MA' => 'Morocco',
				  'MZ' => 'Mozambique',
				  'MM' => 'Myanmar',
				  'NA' => 'Namibia',
				  'NR' => 'Nauru',
				  'NP' => 'Nepal',
				  'NL' => 'Netherlands',
				  'AN' => 'Netherlands Antilles',
				  'NC' => 'New Caledonia',
				  'NZ' => 'New Zealand',
				  'NI' => 'Nicaragua',
				  'NE' => 'Niger',
				  'NG' => 'Nigeria',
				  'NU' => 'Niue',
				  'NF' => 'Norfolk Island',
				  'MP' => 'Northern Mariana Islands',
				  'NO' => 'Norway',
				  'OM' => 'Oman',
				  'PK' => 'Pakistan',
				  'PW' => 'Palau',
				  'PS' => 'Palestinian Territory, Occupied',
				  'PA' => 'Panama',
				  'PG' => 'Papua New Guinea',
				  'PY' => 'Paraguay',
				  'PE' => 'Peru',
				  'PH' => 'Philippines',
				  'PN' => 'Pitcairn',
				  'PL' => 'Poland',
				  'PT' => 'Portugal',
				  'PR' => 'Puerto Rico',
				  'QA' => 'Qatar',
				  'RE' => 'Reunion',
				  'RO' => 'Romania',
				  'RU' => 'Russian Federation',
				  'RW' => 'Rwanda',
				  'BL' => 'Saint Barthelemy',
				  'SH' => 'Saint Helena',
				  'KN' => 'Saint Kitts and Nevis',
				  'LC' => 'Saint Lucia',
				  'MF' => 'Saint Martin (French part)',
				  'PM' => 'Saint Pierre and Miquelon',
				  'VC' => 'Saint Vincent and the Grenadines',
				  'WS' => 'Samoa',
				  'SM' => 'San Marino',
				  'ST' => 'Sao Tome and Principe',
				  'SA' => 'Saudi Arabia',
				  'SN' => 'Senegal',
				  'RS' => 'Serbia',
				  'SC' => 'Seychelles',
				  'SL' => 'Sierra Leone',
				  'SG' => 'Singapore',
				  'SK' => 'Slovakia',
				  'SI' => 'Slovenia',
				  'SB' => 'Solomon Islands',
				  'SO' => 'Somalia',
				  'ZA' => 'South Africa',
				  'GS' => 'South Georgia and the South Sandwich Islands',
				  'ES' => 'Spain',
				  'LK' => 'Sri Lanka',
				  'SD' => 'Sudan',
				  'SR' => 'Suriname',
				  'SJ' => 'Svalbard and Jan Mayen',
				  'SZ' => 'Swaziland',
				  'SE' => 'Sweden',
				  'CH' => 'Switzerland',
				  'SY' => 'Syrian Arab Republic',
				  'TW' => 'Taiwan, Province of China',
				  'TJ' => 'Tajikistan',
				  'TZ' => 'Tanzania, United Republic of',
				  'TH' => 'Thailand',
				  'TL' => 'Timor-Leste',
				  'TG' => 'Togo',
				  'TK' => 'Tokelau',
				  'TO' => 'Tonga',
				  'TT' => 'Trinidad and Tobago',
				  'TN' => 'Tunisia',
				  'TR' => 'Turkey',
				  'TM' => 'Turkmenistan',
				  'TC' => 'Turks and Caicos Islands',
				  'TV' => 'Tuvalu',
				  'UG' => 'Uganda',
				  'UA' => 'Ukraine',
				  'AE' => 'United Arab Emirates',
    			  'GB' => 'United Kingdom',
				  'US' => 'United States',
				  'UM' => 'United States Minor Outlying Islands',
				  'UY' => 'Uruguay',
				  'UZ' => 'Uzbekistan',
				  'VU' => 'Vanuatu',
				  'VE' => 'Venezuela',
				  'VN' => 'Viet Nam',
				  'VG' => 'Virgin Islands, British',
				  'VI' => 'Virgin Islands, U.S.',
				  'WF' => 'Wallis and Futuna',
				  'EH' => 'Western Sahara',
				  'YE' => 'Yemen',
				  'ZM' => 'Zambia',
				  'ZW' => 'Zimbabwe');
}

/**
 * Calls a function with parameters. The $function parameter
 * must match the following pattern:
 * $function = array(function_name[,array(arg1,arg2,...)]).
 * If $data is a parameter of the function, '%s' can be
 * used as wild card. The index of an argument within the arguments-
 * array corresponds to the position of the parameter in the
 * functions definition. If the function has only one parameter
 * the pattern $function = array(function_name, arg) can be used.
 * If $data is the only parameter of the function to be called,
 * $function must only be the funtion name. If the function has no
 * parameter $function must be function name and $data must be NULL
 * @param $function
 * @param $data
 * @return unknown_type
 */
function _cjo_call_user_func($function, $data = NULL){

    if($function != NULL){
    	if(is_array($function)){
	    	if(!is_string($function[0])) return $data;

	    	if(function_exists($function[0])){
	    		if(!isset($function[1])) return $data;

	    		if(!is_array($function[1])){
	    			$function[1] = array($function[1]);
	    		}
	    		if($data !== NULL){
		    		for($i = 0; $i < count($function[1]); $i++){
		    			if($function[1][$i] === '%s'){
		    				$function[1][$i] = $data;
		    				break;
		    			}
		    			$data = call_user_func_array($function[0],$function[1]);
		    		}
	    		}
	    	}
    	} // end if is array $function
    	elseif(is_string($function)){
    		if(function_exists($function)){
    			if($data !== NULL){
    				$data = call_user_func($function, $data);
    			}else
    			$data = call_user_func($function, NULL);
    		}
    	}
    }

    return $data;
}

if (!function_exists('http_build_url'))
{
	define('HTTP_URL_REPLACE', 1);				// Replace every part of the first URL when there's one of the second URL
	define('HTTP_URL_JOIN_PATH', 2);			// Join relative paths
	define('HTTP_URL_JOIN_QUERY', 4);			// Join query strings
	define('HTTP_URL_STRIP_USER', 8);			// Strip any user authentication information
	define('HTTP_URL_STRIP_PASS', 16);			// Strip any password authentication information
	define('HTTP_URL_STRIP_AUTH', 32);			// Strip any authentication information
	define('HTTP_URL_STRIP_PORT', 64);			// Strip explicit port numbers
	define('HTTP_URL_STRIP_PATH', 128);			// Strip complete path
	define('HTTP_URL_STRIP_QUERY', 256);		// Strip query string
	define('HTTP_URL_STRIP_FRAGMENT', 512);		// Strip any fragments (#identifier)
	define('HTTP_URL_STRIP_ALL', 1024);			// Strip anything but scheme and host
	
	// Build an URL
	// The parts of the second URL will be merged into the first according to the flags argument. 
	// 
	// @param	mixed			(Part(s) of) an URL in form of a string or associative array like parse_url() returns
	// @param	mixed			Same as the first argument
	// @param	int				A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
	// @param	array			If set, it will be filled with the parts of the composed url like parse_url() would return 
	function http_build_url($url, $parts=array(), $flags=HTTP_URL_REPLACE, &$new_url=false)
	{
		$keys = array('user','pass','port','path','query','fragment');
		
		// HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
		if ($flags & HTTP_URL_STRIP_ALL)
		{
			$flags |= HTTP_URL_STRIP_USER;
			$flags |= HTTP_URL_STRIP_PASS;
			$flags |= HTTP_URL_STRIP_PORT;
			$flags |= HTTP_URL_STRIP_PATH;
			$flags |= HTTP_URL_STRIP_QUERY;
			$flags |= HTTP_URL_STRIP_FRAGMENT;
		}
		// HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
		else if ($flags & HTTP_URL_STRIP_AUTH)
		{
			$flags |= HTTP_URL_STRIP_USER;
			$flags |= HTTP_URL_STRIP_PASS;
		}
		
		// Parse the original URL
		$parse_url = parse_url($url);
		
		// Scheme and Host are always replaced
		if (isset($parts['scheme']))
			$parse_url['scheme'] = $parts['scheme'];
		if (isset($parts['host']))
			$parse_url['host'] = $parts['host'];
		
		// (If applicable) Replace the original URL with it's new parts
		if ($flags & HTTP_URL_REPLACE)
		{
			foreach ($keys as $key)
			{
				if (isset($parts[$key]))
					$parse_url[$key] = $parts[$key];
			}
		}
		else
		{
			// Join the original URL path with the new path
			if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH))
			{
				if (isset($parse_url['path']))
					$parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
				else
					$parse_url['path'] = $parts['path'];
			}
			
			// Join the original query string with the new query string
			if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY))
			{
				if (isset($parse_url['query']))
					$parse_url['query'] .= '&' . $parts['query'];
				else
					$parse_url['query'] = $parts['query'];
			}
		}
			
		// Strips all the applicable sections of the URL
		// Note: Scheme and Host are never stripped
		foreach ($keys as $key)
		{
			if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key)))
				unset($parse_url[$key]);
		}
		
		
		$new_url = $parse_url;
		
		return 
			 ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
			.((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') .'@' : '')
			.((isset($parse_url['host'])) ? $parse_url['host'] : '')
			.((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
			.((isset($parse_url['path'])) ? $parse_url['path'] : '')
			.((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
			.((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '')
		;
	}
}
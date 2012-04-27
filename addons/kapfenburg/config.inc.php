<?php

$mypage = "kapfenburg"; // only for this file

$CJO['ADDON']['page'][$mypage]          = $mypage; // pagename/foldername
$CJO['ADDON']['name'][$mypage]          = 'Kapfenburg';  // name
$CJO['ADDON']['author'][$mypage]        = 'Stefan Lehmann 2012';


require_once $CJO['ADDON_PATH'].'/'.$mypage.'/kapfenburg.inc.php';
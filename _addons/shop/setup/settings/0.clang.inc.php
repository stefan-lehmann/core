<?php

$mypage = 'shop';


// --- DYN

$CJO['ADDON']['settings'][$mypage]['PRODUCT_ADDED_MESSAGE'] = "<p>Der Artikel wurde dem Warenkorb hinzugefügt.</p>";

$CJO['ADDON']['settings'][$mypage]['ORDER_CONFIRM_SUBJECT'] = "Ihre Bestellung bei uns";
$CJO['ADDON']['settings'][$mypage]['ORDER_CONFIRM_MAIL'] =
"Sehr geehrte/r %customer%,

Vielen Dank für ihre Bestellung bei %shop_name%.
Ihre Bestellung ist bei uns unter der Bestellnummer %order_id% eingegangen.

Sie haben am %today% folgende Artikel bestellt:

-----------------------------------------------------------------------------

%product_list%

-----------------------------------------------------------------------------
Bestellwert:  %order_value%
Versankosten: %delivery_costs%

GESAMTSUMME:  %total_sum%

RECHNUNGSADRESSE
----------------
%address%

ZAHLUNGSMETHODE
----------------
%pay_method%

%pay_data%


IHRE BEMERKUNG
--------------
%order_comment%

Die bestellten Artikel werden an die folgende Adresse geliefert:

%supply_address%

Mit freundlichen Grüßen



";
$CJO['ADDON']['settings'][$mypage]['ORDER_SEND_SUBJECT'] = "Ihre Bestellung wurde versendet";
$CJO['ADDON']['settings'][$mypage]['ORDER_SEND_MAIL'] =
"Sehr geehrte/r %customer%,

Die von ihnen bei %shop_name% bestellten  Artikel wurden am %today% versand,
und sollten in Kürze bei ihnen eintreffen.

Mit freundlichen Grüßen


";

// --- /DYN
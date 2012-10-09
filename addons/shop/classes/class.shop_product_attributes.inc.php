<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  shop
 * @version     2.7.x
 *
 * @author      Matthias Schomacker <ms@raumsicht.com>
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
 * <strong><u>Class cjoShopProductAttributes</u></strong>
 *
 * This class provides methods to update, manipulate
 * and display product attributes.
 */

class cjoShopProductAttributes {

    protected static $mypage = 'shop';

    /**
     * Creates an attribute.
     * @param string $attribute name of the attribute
     * @return bool
     * @access public
     */
    public static function addAttribute($attribute) {

        global $CJO;

		$translate_id = false;
		$insert = new cjoSql();

		$translate_id = self::addTranslation($insert, $attribute);
		if ($translate_id == false) return false;

        $insert->flush();
        $insert->setTable(TBL_21_ATTRIBUTES);
        $insert->setValue("translate_id",  $translate_id);
        $insert->setValue("status",  1);
        $insert->addGlobalCreateFields();
        $insert->Insert();

        if ($insert->getError() != '') {
            cjoMessage::addError($insert->getError());
            return false;
        }
        return $insert->getLastId();
    }

    /**
     * Updates an attribute.
     * @param string $attribute name of the attribute
     * @param int $id id of the current attribute
     * @param int $clang language id of the attribute
     * @return bool
     * @access public
     */
    public static function updateAttribute($attribute, $id, $clang) {

        global $CJO;

		$update = new cjoSql();
		$translate_id = self::getTranslateId($update, $id, TBL_21_ATTRIBUTES);
        $update->setTable(TBL_21_ATTRIBUTES);
        $update->setWhere("id = '".$id."' AND translate_id = '".$translate_id."'");
        $update->addGlobalUpdateFields();
        $update->Update();

        if ($update->getError() != '') {
            cjoMessage::addError($update->getError());
            return false;
        }

		return self::updateTranslation($update, $attribute, $translate_id,  $clang);
    }

    /**
     * Removes an attribute and its values.
     * @param int $id id of the current attribute
     * @return bool
     * @access public
     */
    public static function removeAttribute($id) {

        global $I18N_21;

		$update = new cjoSql();

        $update->setTable(TBL_21_ATTRIBUTE_VALUES);
        $update->setWhere("attribute_id = '".$id."'");
        $update->setValue("status", 0);
        $update->Update();

        if ($update->getError() != '') {
            cjoMessage::addError($update->getError());
            return false;
        }

        $update->flush();
        $update->setTable(TBL_21_ATTRIBUTES);
        $update->setWhere("id = '".$id."'");
        $update->setValue("status", 0);
        $update->addGlobalUpdateFields();
        $update->Update();

        if ($update->getError() != '') {
            cjoMessage::addError($update->getError());
            return false;
        }

        cjoMessage::addSuccess($I18N_21->msg('msg_attribute_removed'));
        return true;
    }

    /**
     * Creates an attribute value.
     * @param string $value name of the attribute value
     * @param int $attribute_id id of the corresponding attribute
     * @param float $offset offset to default price, can be positive for additional costs, negative for discount or zero for no offset
     * @param int $prior sets the order of attribute values
     * @return bool
     * @access public
     */
    public static function addAttributeValue($value, $attribute_id, $offset=0, $prior=0) {

		$translate_id = false;
		$insert = new cjoSql();

		$translate_id = self::addTranslation($insert, $value);
		if ($translate_id == false) return false;

        $offset = cjoShopPrice::convToFloat($offset);
        $offset = $offset >= 0 ? '+'.$offset : $offset;

        $insert->flush();
        $insert->setTable(TBL_21_ATTRIBUTE_VALUES);
        $insert->setValue("translate_id",  $translate_id);
        $insert->setValue("attribute_id",  $attribute_id);
        $insert->setValue("offset", $offset);
        $insert->setValue("status", 1);
        $insert->setValue("prior", (int) $prior);
        $insert->insert();

        if ($insert->getError() != '') {
            cjoMessage::addError($insert->getError());
            return false;
        }
    }

    /**
     * Updates an attribute value.
     * @param string $value name of the attribute value
     * @param int $attribute_id id of the corresponding attribute
     * @param float $offset offset to default price, can be positive for additional costs, negative for discount or zero for no offset
     * @param int $prior sets the order of attribute values
     * @param int $id id of the current attribute value
     * @param int $clang language id of the attribute value
     * @return bool
     * @access public
     */
    public static function updateAttributeValue($value, $attribute_id, $offset=0, $prior=0, $id, $clang) {

        $update = new cjoSql();
		$translate_id = self::getTranslateId($update, $id, TBL_21_ATTRIBUTE_VALUES);
        $update->setTable(TBL_21_ATTRIBUTE_VALUES);
        $update->setWhere("id = '".$id."' AND translate_id = '".$translate_id."'");

        if (empty($value)) {
            $update->setValue("status", 0);
            $update->Update();

            if ($update->getError() != '') {
                cjoMessage::addError($update->getError());
                return false;
            }
            return true;
        }

        $offset = cjoShopPrice::convToFloat($offset);
        $offset = $offset >= 0 ? '+'.$offset : $offset;

        $update->setValue("attribute_id",  $attribute_id);
        $update->setValue("offset", $offset);
        $update->setValue("prior", (int) $prior);
        $update->setValue("status", 1);
        $update->Update();

        if ($update->getError() != '') {
            cjoMessage::addError($update->getError());
            return false;
        }

	    return self::updateTranslation($update, $value, $translate_id,  $clang);
    }

    /**
     * Returns the translation id of the current item.
     * @parem object $sql an sql object
     * @param int $id id of the current item
     * @param string $table db table of the current item
     * @return int|bool
     * @access public
     */
    public static function getTranslateId($sql, $id, $table) {

		$sql->setQuery("SELECT translate_id FROM ".$table." WHERE id='".$id."' LIMIT 2");
		$rows         = $sql->getRows();
		$translate_id = $sql->getValue('translate_id', 0);
		$sql->flush();
		return ($rows == 1) ? $translate_id : false;
    }

    /**
     * Creates a translation.
     * @parem object $insert an sql object
     * @param string $name default translation
     * @return int|bool
     * @access public
     */
    public static function addTranslation(&$insert, $name){

        global $CJO;

		$translate_id = false;

        foreach (array_keys($CJO['CLANG']) as $clang) {

            $insert->flush();
            $insert->setTable(TBL_21_ATTRIBUTE_TRANSLATE);
			if (!$translate_id) {
			    $translate_id = $insert->setNewId("translate_id");
			} else {
			    $insert->setValue("translate_id", $translate_id);
			}
            $insert->setValue("name",  $name);
            $insert->setValue("clang", $clang);
            $insert->Insert();

            if ($insert->getError() != '') {
                cjoMessage::addError($insert->getError());
                return false;
            }
        }
        return $translate_id;
    }

    /**
     * Updates the translation of the current item.
     * @param object $update
     * @param string $name
     * @param int $translate_id
     * @param int $clang
     * @return bool
     * @access public
     */
    public static function updateTranslation(&$update, $name, $translate_id,  $clang) {

        $update->flush();
        $update->setTable(TBL_21_ATTRIBUTE_TRANSLATE);
        $update->setWhere("translate_id = '".$translate_id."' AND clang = '".$clang."'");
        $update->setValue("name",  $name);
        $update->setValue("clang", $clang);
        $update->Update();

        if ($update->getError() != '') {
            cjoMessage::addError($update->getError());
            return false;
        }
        return true;
    }

/**
     * Returns the offsets belonging to the attribute
     * values of the parameter. Is required for price
     * calculating.
     *
     * @param string $attribute_string - a '|'-separated string
     * 									 with attribute values
     * @return array $result		   - the resultset of attribute value's
     * 									 offsets from db
     * @access public
     */
    public static function getAttributeOffsets($attribute_string, $format = true) {

    	if (empty($attribute_string)) return 0;

    	$attribute_ids = str_replace('|', ' OR id=',$attribute_string);
    	$sql = new cjoSql();
    	$qry = "SELECT SUM(offset) AS sum FROM ".TBL_21_ATTRIBUTE_VALUES." WHERE id='".$attribute_ids."'";
    	$result = $sql->getArray($qry);

    	return !empty($result) ? $result[0]['sum'] : 0;
    }

    /**
     * Returns a string with all attribute and attribute
     * values formatted for output with HTML.
     *
     * @param string $attribute_string  - a '|'-separated string
     * 									  with attribute values
     * @param int $clang				- the current language id
     * @return string
     * @access public
     */
    public static function getAttributesAndValues($attribute_string, $format=true){

    	global $CJO;

    	$clang = $CJO['CUR_CLANG'];

    	if (empty($attribute_string)) return '';

    	$attribute_ids = str_replace('|', ' OR b.id=', $attribute_string);

    	$sql = new cjoSql();
    	$qry = "SELECT
    				b.id AS id,
    				CONCAT(d.name ,': ', c.name) AS attribute
    			FROM "
    				.TBL_21_ATTRIBUTE_TRANSLATE." d
    				INNER JOIN ("
    					.TBL_21_ATTRIBUTES." a
           				INNER JOIN ("
                  			.TBL_21_ATTRIBUTE_VALUES." b
                			INNER JOIN "
                               .TBL_21_ATTRIBUTE_TRANSLATE." c
                            ON c.translate_id = b.translate_id)
           				ON a.id = b.attribute_id)
           			ON a.translate_id=d.translate_id
           		WHERE
           			c.clang='".$clang."'
           		AND
           			d.clang='".$clang."'
           		AND
           			(b.id='".$attribute_ids."')";

        

    	$results = $sql->getArray($qry);
    	$attributes = cjoAssistance::toArray($attribute_string);

    	// output attributes in the order they
    	// were saved
    	foreach($attributes as $attribute) {
    		foreach($results as $result) {

    			if ($result['id'] == $attribute) {
    				if ($format){
    					$string .= '<span>'.$result['attribute'].'</span>';
    				}
    				else {
    					if (empty($string)) {
   							$string = $result['attribute'];
    					} else {
							$string .= '\r\n'.$result['attribute'];
    					}
    				}
    				break;
    			}
    		}
    	}

    	return $string;
    }

    /**
     * Generates one or more selections of the enabled attributes.
     * @param string $enabled ids of the enabled attribute values separeted by pipes (e.g. "27|33|54")
     * @param int|bool $clang
     * @return array
     */
    public static function getAttributeSelections($enabled, $clang = false) {

        global $CJO, $I18N_21;

        if ($clang === false) $clang = $CJO['CUR_CLANG'];

        $output   = '';
        $results  = array();
        $selected = array();

        $sql = new cjoSql();
        $qry = "SELECT
        			t.name AS name,
        			n.id AS id
        		FROM ".TBL_21_ATTRIBUTES." n
        		LEFT JOIN ".TBL_21_ATTRIBUTE_TRANSLATE." t
                ON t.translate_id = n.translate_id
                WHERE t.clang='".$clang."' AND
                	  n.status = '1'";

        $attributes = $sql->getArray($qry);

        $qry = "SELECT
        			v.*,
            		IF(v.offset <> 0,
            				CONCAT(t.name,
            				       ' &nbsp;&nbsp; (',
            				       REPLACE(v.offset, '.', '".$CJO['ADDON']['settings'][self::$mypage]['CURRENCY']['DEFAULT_SEPARATOR']."'),
    						       ' ".$CJO['ADDON']['settings'][self::$mypage]['CURRENCY']['DEFAULT_SIGN']."',
        			  		       ')'),
        			  		        t.name)  AS name
        		FROM ".TBL_21_ATTRIBUTES." n
        		LEFT JOIN ".TBL_21_ATTRIBUTE_VALUES." v
        		ON n.id = v.attribute_id
        		LEFT JOIN ".TBL_21_ATTRIBUTE_TRANSLATE." t
                ON t.translate_id = v.translate_id
                WHERE t.clang='".$clang."' AND
                	  v.status = '1'
                ORDER BY v.prior, t.name";

        $sql->flush();
        $values = $sql->getArray($qry);

        foreach(cjoAssistance::toArray($enabled) as $id) {
            $selected[$id] = true;
            foreach($values as $value) {
                if ($id == $value['id']) {
                    $results[$value['attribute_id']] = array();
                    break;
                }
            }
        }

        if (empty($attributes)) {

            return sprintf('<div class="formular shop_attribute_modul_select">%s %s</div>',
                   		   $I18N_21->msg('no_product_attributes_defined', $link),
                           cjoAssistance::createBELink($I18N_21->msg('label_attribute_setup'),
                                                       array('page' => self::$mypage,
                                                       		 'subpage' => 'attributes',
                                                       		 'function' => '')));
        }

        foreach($attributes as $attribute) {

            $temp = array('id'      => $attribute['id'],
                          'name'    => $attribute['name'],
           		          'display' => 'display:none',
		                  'checked' => '',
                          'prior'   => $dprior);

            $select = new cjoSelect();
        	$select->setSize(5);
		    $select->setName('shop_attributes['.$attribute['id'].'][values][]');
		    $select->setMultiple(true);

			foreach($values as $value) {
			    if ($value['attribute_id'] != $attribute['id']) continue;
			    $select->addOption($value['name'], $value['id']);

			    if ($selected[$value['id']]) {
			        $select->setSelected($value['id']);
			        $temp['display'] = 'display:block';
			        $temp['checked'] = 'checked="checked"';
			        if ($temp['prior'] == '') {
			            $temp['prior'] = $selected[$value['id']];
			        }
			    }
			}
			$temp['select'] = $select->get();
            $results[$temp['id']] = $temp;
        }

        $select = new cjoSelect();
    	$select->setSize(1);
		$select->addOption('', '');
    	for ($i=1; $i<=20; $i++) { $select->addOption($i, $i); }

        $i = 0;
        foreach($results as $key => $result) {

            $i++;
            $select->resetSelected();
            $select->setSelected($i);
            $select->setName('shop_attributes['.$result['id'].'][prior]');

            $output .= sprintf("\r\n".'<div class="formular shop_attribute_modul_select">
								<input type="checkbox" %s name="shop_attributes[%s][name]" value="%s" /> <strong>%s</strong>
								<span style="%s">
									<span class="shop_attribute_prior">
										[translate_21: shop_attribute_prior] %s
									</span>
									%s
								</span> </div>',
			                    $result['checked'],
			                    $result['id'],
			                    $result['name'],
			                    $result['name'],
			                    $result['display'],
			                    $select->get(),
			                    $result['select']
			                   );
		}
        return $output;
    }

	/**
     * Generates attribute selectboxes of the enabled attribute values.
     * @param array $set settings from the to basket slice
     * @param int|bool $clang
     * @return array
     * @access public
     */
    public static function getFEAttributeSelections($set, $clang = false) {

        global $CJO;

        if (empty($set['attributes'])) return false;
        if ($clang === false) $clang = $CJO['CUR_CLANG'];
        
        $attribute_format  = (int) $CJO['ADDON']['settings'][self::$mypage]['ATTRIBUTE_FORMAT'];
		$exchange_ratio    = empty($CJO['ADDON']['settings'][self::$mypage]['CURRENCY']['CURR_RATIO']) 
		                   ? $CJO['ADDON']['settings'][self::$mypage]['CURRENCY']['CURR_RATIO'] : 1;
        $discount          = cjoShopPrice::convToFloat($set['discount']);                          
        $temp              = array();
        $attributes        = array();
        $set['attributes'] = cjoAssistance::toArray($set['attributes']);

        foreach($set['attributes'] as $value) {
            $temp[] = " \r\n"."v.id = '".$value."' ";
        }
        $add_where = implode(' OR ', $temp);

        $sub_qry = "SELECT
            			tn.name
            		FROM ".TBL_21_ATTRIBUTES." n
            		LEFT JOIN ".TBL_21_ATTRIBUTE_TRANSLATE." tn
                    ON tn.translate_id = n.translate_id
                    WHERE tn.clang='".$clang."' AND
                    	  n.id = v.attribute_id ";

        $qry = "SELECT
        			v.*,
            		t.name,
            		v.offset,
            		(".$sub_qry.") AS attribute
        		FROM ".TBL_21_ATTRIBUTE_VALUES." v
        		LEFT JOIN ".TBL_21_ATTRIBUTE_TRANSLATE." t
                ON t.translate_id = v.translate_id
                WHERE t.clang='".$clang."' AND
                	  v.status = 1 AND
                	 ( ".$add_where." )
                ORDER BY v.prior, t.name";

        $sql = new cjoSql();
        $values = $sql->getArray($qry);

		$select = new cjoSelect();
		$select->setSize(1);
		$select->setName($set['form_name'].'[attribute][]');

		$temp = array();

        foreach($set['attributes'] as $id) {
            
            foreach($values as $attribute) {
                
                if ($id != $attribute['id']) continue;
                
                $key = $attribute_format != 1 ? $attribute['attribute_id'] : 1;
                $price = (cjoShopPrice::convToFloat($set['price']) + cjoShopPrice::convToFloat($attribute['offset'])) * $exchange_ratio;
                $price = $price - ($exchange_ratio * $discount * $price / 100);
                $price = $price + ($exchange_ratio * $set['taxes'] * $price / 100);
                $price = cjoShopPrice::toCurrency($price);
                
                $set['amount_sel']->resetSelected();
                $set['amount_sel']->setName($set['form_name'].'[amount][]');

                if (!isset($temp[$key])) {
                    $temp[$key]['label']  = $attribute['attribute'];
                    $temp[$key]['select'] = clone($select);
                    $temp[$key]['table']  = array('<tr>
                                                      <th class="shop_attribute_name">[translate_21: shop_attribute_list_title]</th>
                                                      <th class="shop_final_price">[translate_21: shop_attribute_list_price]</th>
                                                      <th class="shop_order_amount">[translate_21: shop_attribute_list_amount]</th>
                                                   </tr>');
                }
                     
                switch($attribute_format) {
                           
                   case 1: $temp[$key]['select']->addOption($attribute['name'].' &nbsp; &nbsp; '.$price, $attribute['id']);
                           break;
                           
                   case 2: $set['amount_sel']->setName($set['form_name'].'[amount]['.$attribute['id'].']');
                           $temp[$key]['table'][] = sprintf('<tr>
                                                                <td class="shop_attribute_name">%s</td>
                                                                <td class="shop_final_price">%s</td>
                                                                <td class="shop_order_amount">%s</td>  
                                                             </tr>',
                                                             $attribute['name'],
                                                             $price,
                                                             $set['amount_sel']->get());
                           break;                           
                         
                    default:   
                        $price = '';
                        if ($attribute['offset'] != 0) {
                            $price = cjoShopPrice::convToFloat($attribute['offset']) * $exchange_ratio;
                            $price = round($price + ($exchange_ratio * $set['taxes'] * $price / 100), 2);
                            $price = ' &nbsp; ('.cjoShopPrice::toCurrency($price, false, true).')';
                        }  
                        $temp[$key]['select']->addOption($attribute['name'].$price, $attribute['id']);   
                }
            }
        }

        foreach($temp as $value) {
            $attributes['label'][] = $value['label'];
            $attributes['attributes'][] = $value['select']->hasOptions() 
                                        ? $value['select']->get() 
                                        : '<table class="shop_attributes">'.implode('',$value['table']).'</table>';
        }

        return $attributes;
    }

    /**
     * Returns a for HTML-output formatted string
     * with the attribute names and -values of a
     * product.
     *
     * @param string $string - the product name and
     * 						   the attribute value id's
     * @return string
     * @access public
     */
    public static function getNameAndAttributes($string) {
    	global $CJO;
    	$values = explode('&', $string);
    	$attribute_ids = str_replace('|', ' OR b.id=', $values[1]);
		$clang = $CJO['CUR_CLANG'];
        $attributes = '';
        
        if (!empty($values[1])) {
        		// get attribute names and values from db
            	$sql = new cjoSql();
            	$qry = "SELECT
            				b.id AS id,
            				d.name AS attribute_name,
            				c.name AS value_name
            			FROM "
            				.TBL_21_ATTRIBUTE_TRANSLATE." d
            				INNER JOIN ("
            					.TBL_21_ATTRIBUTES." a
                   				INNER JOIN ("
                          			.TBL_21_ATTRIBUTE_VALUES." b
                        			INNER JOIN "
                                       .TBL_21_ATTRIBUTE_TRANSLATE." c
                                    ON c.translate_id = b.translate_id)
                   				ON a.id = b.attribute_id)
                   			ON a.translate_id=d.translate_id
                   		WHERE
                   			c.clang='".$clang."'
                   		AND
                   			d.clang='".$clang."'
                   		AND
                   			(b.id='".$attribute_ids."')";
        
                // prepare attribute values
                $results = $sql->getArray($qry);
        
                $return = array();
        
                foreach($results as $result) {
                	if(empty($return[$result['attribute_name']]))
                		$return[$result['attribute_name']] = $result['value_name'];
                	else
                		$return[$result['attribute_name']] .= ' | '.$result['value_name'];
                }

            // build output string
            foreach($return as $key =>  $value) {
            	if (empty($attributes)) {
            		$attributes = $key.': '.$value;
            	} else {
            		$attributes .= '<br/>'.$key.': '.$value;
            	}
            }
        }
    	$values[0] ='<span class="large_item">'.$values[0].'</span><br/>';
    	return $values[0].$attributes;

    } // end function getNameAndAttributes

} // end class cjoShopProductAttributes

<?php 

class Kapfenburg {
    
    public static function transformDate ($event_start='',$event_end=''){
        if (empty($event_start) || empty($event_end)) return null;
        if ($event_start == $event_end){
            return strftime('<b class="day">%d.</b><br/>%m.%Y', $event_start);
        }
        else {
            return strftime('<b>%d.</b>%m.%y', $event_start). '<br/>bis<br/>' .strftime('<b>%d.</b>%m.%y', $event_end);
        }
    }
    
    public static function addDefaultProductToBasket($set) {
        
        $slice_id   = 4972;
        $session_id = $set['session_id'];
        
        if ($set['type'] == 'update') return false;
        
        $sql = new cjoSql();
        $qry = "SELECT *  FROM ".TBL_21_BASKET." WHERE session_id LIKE '".$session_id."' LIMIT 2";        
        $sql->setQuery($qry);

        if ($sql->getRows() > 1 || !isset($set['form_name']) || $set['slice_id'] == $slice_id) return false;
        
        $posted                  = array();
        $set                     = array();
        $set['slice_id']         = $slice_id;
        
        $slice = OOArticleSlice::getArticleSliceById($set['slice_id']);
 
        if (!OOArticleSlice::isValid($slice)) return false;

        $set['session_id']       = $session_id;
        $set['online']           = $slice->getValue(19);
        $set['amount']           = (int) $slice->getValue(1);
        $set['product_id']       = $slice->getValue(12);
        $set['price']            = $slice->getValue(2);
        $set['taxes']            = $slice->getValue(3);
        $set['discount']         = $slice->getValue(4);
        $set['product_title']    = $slice->getValue(8);
        $set['product_image']    = '';
        $set['attributes']       = $slice->getValue(6);
        $set['order_id']         = $slice->getValue(10);
        $set['count_down_stock'] = $slice->getValue(18);
        $set['out_of_stock']     = $slice->getValue(17);
        $set['show_in_stock']    = $slice->getValue(16);
        
        $posted['amount']        = 1;
        $posted['slice_id']      = $set['slice_id'];
        $posted['attribute']     = ''; 
        
        $set['md5']  = md5($set['session_id'].$posted['slice_id'].$posted['attribute']);
   
        if (!$set['online']) return false;
        
        $delete = new cjoSql();
        $delete->setTable(TBL_21_BASKET);
        $delete->setWhere("md5_id = '".$set['md5']."'");
        $delete->delete();

        cjoShopBasket::addToBasket($posted, $set);
    }
}

cjoExtension::registerExtension('SHOP_ADDED_TO_BASKET', 'Kapfenburg::addDefaultProductToBasket');
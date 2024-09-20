<?php
/**
 * Product Suggest
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @Author: Will Davies Vasconcelos <willvasconcelos@outlook.com>
 * @Version: 1.0
 * @Release Date: Monday, May 10 2018 PST
 * @Tested on Zen Cart v1.5.5 $
 */
	class zcProductSuggest extends base{
		public function SuggestProduct(){ #CONSTRUCTOR
			global $db;
			$items = array(); //default
			$limit = 10; //default
			$description_max_length = 100;
			if( isset( $_GET['keyword'] ) ){
				$_GET['keyword'] = trim( $_GET['keyword'] );
				if( strlen( $_GET['keyword'] ) > 0 ){
					if( isset($_GET['limit']) and (int)$_GET['limit'] > 0 and (int)$_GET['limit'] < 50 ){
						$limit = (int)$_GET['limit'];
					}
					
					if( isset($_GET['descLen']) and (int)$_GET['descLen'] > 10 and (int)$_GET['descLen'] < 1000 ){
						$description_max_length = (int)$_GET['descLen'];
					}
					
					if( $_GET['keyword'] != HEADER_SEARCH_DEFAULT_TEXT  && $_GET['keyword'] != KEYWORD_FORMAT_STRING ){
						$keywords = $_GET['keyword'];
						if( zen_not_null( $keywords ) ) {
							if ( !zen_parse_search_string( stripslashes( $keywords ), $search_keywords ) ) {
								$error = true;
							}
						}
					}
					
					$select_str = "SELECT DISTINCT pd.products_name AS name, SUBSTRING(pd.products_description,1," . $description_max_length . ") AS description, p.products_id AS id, p.products_image AS image ";
					
					$from_str = "FROM (" . TABLE_PRODUCTS . " p
								 LEFT JOIN " . TABLE_MANUFACTURERS . " m
								 USING(manufacturers_id), " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c )
								 LEFT JOIN " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " mtpd
								 ON mtpd.products_id= p2c.products_id
									AND mtpd.language_id = :languagesID
									LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " AS att ON p.products_id = att.products_id";
					
					$from_str = $db->bindVars($from_str, ':languagesID', $_SESSION['languages_id'], 'integer');
					$where_str = " WHERE (p.products_status = 1
								   AND p.products_id = pd.products_id
								   AND pd.language_id = :languagesID
								   AND p.products_id = p2c.products_id
								   AND p2c.categories_id = c.categories_id ";
					$where_str = $db->bindVars($where_str, ':languagesID', $_SESSION['languages_id'], 'integer');
					$error = true;
					
					if ( isset( $keywords ) && zen_not_null( $keywords ) ) {
						if ( zen_parse_search_string( stripslashes( $keywords ), $search_keywords ) ) {
							$where_str .= " AND (";
							for ( $i = 0, $n = count( $search_keywords ); $i < $n; $i++ ) {
								switch ( $search_keywords[$i] ) {
									case '(':
									case ')':
									case 'and':
									case 'or':
										$where_str .= " " . $search_keywords[$i] . " ";
										break;
									default:
										$where_str .= "(pd.products_name LIKE '%:keywords%'
														 OR p.products_model LIKE '%:keywords%'
														 OR m.manufacturers_name LIKE '%:keywords%'
														";
										#SEARCH THE SAME FIELDS WITHOUT DASHES ON KEYWORDS IN THE DATABASE
										$where_str .= " OR REPLACE(pd.products_name,'-','') LIKE '%:keywords%'
														OR REPLACE(p.products_model,'-','') LIKE '%:keywords%'
														OR REPLACE(m.manufacturers_name,'-','') LIKE '%:keywords%'
														";
										$where_str = $db->bindVars($where_str, ':keywords', $search_keywords[$i], 'noquotestring');
										#SEARCH THE SAME FIELDS WITHOUT DASHES ON KEYWORDS
										$where_str .= " OR pd.products_name LIKE '%:keywords%'
														OR p.products_model LIKE '%:keywords%'
														OR m.manufacturers_name LIKE '%:keywords%'";
										$where_str = $db->bindVars($where_str, ':keywords', str_replace("-","",$search_keywords[$i]), 'noquotestring');
										$where_str .= " OR pd.products_description LIKE '%:keywords%'";
										$where_str = $db->bindVars($where_str, ':keywords', $search_keywords[$i], 'noquotestring');
										$where_str .= ')';
									break;
								}
							}
							$where_str .= " ))";
							$error = false;
						}
					}
					
					if ( !$error ){
						$listing_sql = $select_str . $from_str . $where_str . ' ORDER BY pd.products_viewed DESC LIMIT ' . $limit;
						$result = $db->Execute( $listing_sql );
						$items = array();
						if( $result->RecordCount() > 0 ){
							$index = 0;
							while( !$result->EOF ){
								$items[$index]['id'] = $this->cleanString( $result->fields['id'] );
								$items[$index]['name'] = $this->cleanString( $result->fields['name'] );
								$items[$index]['image'] = $this->cleanString( $result->fields['image'] );
								$items[$index]['descr'] = $this->cleanString( trim( strip_tags( $result->fields['description'] ) ) );
								$items[$index]['uri'] = $this->translateURI( $result->fields['id'] );
								$index++;
								$result->MoveNext();
							}
						}
					}
				}
			}
			
			return json_encode($items);
		}
		
		function cleanString($str){
			$str = preg_replace('/[^A-Za-z0-9\-\.\,\'\;\&\$\ \/\_]/', '', $str); #remove special characters
			$str = preg_replace('/\s+/', ' ', $str); #remove multiple spaces
			return $str;
		}
		
		function translateURI($pID){
			global $db, $sniffer;
			$newUri = '';
			if($pID!='' and is_numeric($pID) and $pID > 0){
				if( $sniffer->table_exists( TABLE_CEON_URI_MAPPINGS ) and CEON_URI_MAPPING_ENABLED == 1 ){
					//IF USING CEON URI PLUGIN
					$sql = "SELECT `uri`
							FROM `" . TABLE_CEON_URI_MAPPINGS . "`
							WHERE `associated_db_id` = '" . (int)$pID . "'
							AND `current_uri` = 1
							AND `main_page` = 'product_info'";
					$rec = $db->Execute($sql);
					if(!$rec->EOF){
						$newUri = trim($rec->fields['uri'], "/");
					}
				}else{
					$newUri = zen_href_link(FILENAME_PRODUCT_INFO, 'products_id='. (int)$pID );
				}
			}
			return $newUri;
		}
	}

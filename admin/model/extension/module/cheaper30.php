<?php
class ModelExtensionModuleCheaper30 extends Model {
	
	public function createCheapering()
	{
			
		$res0 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module'");
		if(!$res0->num_rows){
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cheaper_module` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `module_id` int(11) NOT NULL,
				  `date` varchar(255) NOT NULL,
				  `product_id` varchar(255) NOT NULL,
				  `option` varchar(255) NOT NULL,
				  `price` int(11) NOT NULL,
				  `text` text NOT NULL,
				  `status` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
			");
		}
		
		$res1 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_fields'");
		if(!$res1->num_rows){
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cheaper_module_fields` (
				  `id` int(11) NOT NULL,
				  `module_id` int(11) NOT NULL,
				  `icon` varchar(255) NOT NULL,
				  `name` text NOT NULL,
				  `type` varchar(255) NOT NULL,
				  `regex` varchar(255) NOT NULL,
				  `valid` text NOT NULL,
				  `required` int(11) NOT NULL
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
			");
		}
		
		$res2 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_value'");
		if(!$res2->num_rows){
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cheaper_module_value` (
				  `id` int(11) NOT NULL,
				  `value_id` int(11) NOT NULL,
				  `module_id` int(11) NOT NULL,
				  `type` varchar(255) NOT NULL,
				  `text` text NOT NULL,
				  `sort` int(11) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			");
		} else {
			$test_value_id = $this->db->query("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_name='" . DB_PREFIX . "cheaper_module_value' AND COLUMN_NAME = 'value_id' AND table_schema = '" . DB_DATABASE . "'");
			
			if (!$test_value_id->rows){
				$this->db->query("ALTER TABLE " . DB_PREFIX . "cheaper_module_value ADD COLUMN `value_id` int(11) NOT NULL AFTER `id`");
			}
		}
		
		$res3 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_style'");
		if(!$res3->num_rows){
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cheaper_module_style` (
				  `module_id` int(11) NOT NULL,
				  `style` text NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			");
		}
		
		$res4 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_protection'");
		if(!$res4->num_rows){
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cheaper_module_protection` (
				  `module_id` int(11) NOT NULL,
				  `format` varchar(255) NOT NULL,
				  `text` text NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			");
		}
		
		$res5 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_email'");
		if(!$res5->num_rows){
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cheaper_module_email` (
				  `module_id` int(11) NOT NULL,
				  `email` varchar(64) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			");
		}
		
		$res6 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_position'");
		if(!$res6->num_rows){
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cheaper_module_position` (
				  `module_id` int(11) NOT NULL,
				  `id` int(11) NOT NULL,
				  `position` int(11) NOT NULL,
				  `column` int(11) NOT NULL,
				  `sort` int(11) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			");
		}
		
		$res7 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_position_sort'");
		if(!$res7->num_rows){
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cheaper_module_position_sort` (
				  `module_id` int(11) NOT NULL,
				  `position` int(11) NOT NULL,
				  `sort_order` int(11) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			");
		}
		
		$res8 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_formula'");
		if(!$res8->num_rows){
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cheaper_module_formula` (
				  `module_id` int(11) NOT NULL,
				  `formula_id` int(11) NOT NULL,
				  `show` int(11) NOT NULL,
				  `type` varchar(255) NOT NULL,
				  `value` text,
				  `sort_order` int(11) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			");
		}
		
		$res8 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_value_calculator'");
		if(!$res8->num_rows){
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cheaper_module_value_calculator` (
				  `module_id` int(11) NOT NULL,
				  `formula_id` int(11) NOT NULL,
				  `id` int(11) NOT NULL,
				  `value_id` int(11) NOT NULL,
				  `calc` varchar(11)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			");
		}
		
		$res9 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_condition'");
		if(!$res9->num_rows){
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cheaper_module_condition` (
				  `module_id` int(11) NOT NULL,
				  `formula_id` int(11) NOT NULL,
				  `sort_field` int(11) NOT NULL,
				  `row` int(11) NOT NULL,
				  `condition` varchar(255) NOT NULL,
				  `type` varchar(255) NOT NULL,
				  `value` text,
				  `sort_order` varchar(255) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			");
		}
		
		//$this->db->query("ALTER TABLE `" . DB_PREFIX . "cheaper_module` CHANGE `id` `id` INT NOT NULL");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "cheaper_module_fields` CHANGE `id` `id` INT NOT NULL");
		
		$test_primary_key = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_TYPE = 'PRIMARY KEY' AND TABLE_NAME = '" . DB_PREFIX . "cheaper_module_fields' AND TABLE_SCHEMA = '" . DB_DATABASE . "'");

		if ($test_primary_key->rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "cheaper_module_fields` DROP PRIMARY KEY;");
		}
	}
	
	function createTableSql($table){
		$res = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
		if(!$res->num_rows){
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $table . "` (
				  `module_id` int(11) NOT NULL,
				  `formula_id` int(11) NOT NULL,
				  `show` int(11) NOT NULL,
				  `type` varchar(255) NOT NULL,
				  `value` text,
				  `sort_order` int(11) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			");
		} else {
			$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . $table . "`");
		}
	}
	
	function dropTableSql($table){
		$this->db->query("DROP TABLE `" . DB_PREFIX . $table . "`");
	}
	
	public function getCheapering($module_id, $data){

		$res0 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module'");
		if($res0->num_rows){
			$sql = "SELECT * FROM " . DB_PREFIX . "cheaper_module WHERE `module_id` = '" . (int)$module_id . "'";
			
			if ($data['sort']) {
				if ($data['sort'] == 'date'){
					$sql .= " ORDER BY STR_TO_DATE(`" . $data['sort'] . "`, '%d.%m.%Y')";
				} else {
					$sql .= " ORDER BY " . $data['sort'];
				}
			} else {
				$sql .= " ORDER BY id";
			}
			
			if ($data['order'] && ($data['order'] == 'DESC')){
				$sql .= " DESC";
			} else {
				if ($data['sort']) {
					$sql .= " ASC";
				} else {
					$sql .= " DESC";
				}
			}
			
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}
			
			$query = $this->db->query($sql);
			
			return $query->rows;
		}
	}
	
	public function getCheaperingTotal($module_id, $status = 1){
		$res0 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module'");
		if($res0->num_rows){
			$query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "cheaper_module WHERE `module_id` = '" . (int)$module_id . "'" . (!$status ? " AND `status` = '0'" : ""));
			
			if ($query->row['total']){
				return $query->row['total'];
			} else {
				return 0;
			}
		}
	}
	
	public function getCheaperingTotalStatus(){
		$config = $this->config_version();
		$results = array();
		$res0 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module'");
		$extension = 'extension/';
		if ($config['version'] == '2.2' or $config['version'] == '2.1' or $config['version'] == '2.0'){
			$extension = '';
		}
		if($res0->num_rows){
			$query = $this->{$config['model_']}->getModulesByCode('cheaper30');
			if ($query){
				foreach ($query as $module){
					if ($module['module_id']){
						$results[] = array(
							'name' => $module['name'],
							'href' => (isset($this->session->data[$config['token']]) ? $this->url->link($extension . 'module/cheaper30', $config['token'] . '=' . $this->session->data[$config['token']] . '&module_id=' . $module['module_id'], true) : ''),
							'total' => $this->getCheaperingTotal($module['module_id'], 0),
						);
					}
				}
			}
		}
		return $results;
	}
	
	public function getCheaperingStyle($module_id) {
		
		$res0 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_style'");
		if($res0->num_rows){
			$query = $this->db->query("SELECT style FROM " . DB_PREFIX . "cheaper_module_style WHERE `module_id`='" . (int)$module_id . "'"); 
			
			if (isset($query->row['style'])){
				return json_decode($query->row['style']);
			} else {
				return false;
			}
		}
	}
	
	public function getCheaperingEmail($module_id) {
		
		$emails = array();
		
		$res0 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_email'");
		if($res0->num_rows){
			$query = $this->db->query("SELECT email FROM " . DB_PREFIX . "cheaper_module_email WHERE `module_id`='" . (int)$module_id . "'"); 
			
			if ($query->rows){
				foreach ($query->rows as $email){
					$emails[] = $email['email'];
				}
			}
		}
		
		return $emails;
	}
	
	public function deleteCheaperingEmail($module_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "cheaper_module_email` WHERE `module_id` = '" . (int)$module_id . "'");
	}
	
	public function getCheaperingProtection($module_id) {
		
		$results = array();
		
		$res0 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_protection'");
		
		if($res0->num_rows){
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_protection WHERE `module_id`='" . (int)$module_id . "'"); 

			if ($query->num_rows){
				foreach ($query->rows as $result){
					$results = array(
						'format' => $result['format'],
						'protection_text' => json_decode($result['text'],true),
					);
				}
			}
		}
		return $results;
	}
	
	function emptyPosition($module_id){
		$query_position = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_position WHERE `module_id`='" . (int)$module_id . "'");
		$empty_position = $query_position->num_rows;
		
		return $empty_position;
	}
	
	function getCheaperingPosition($module_id){
		$positions = array();
		
		$query_position = $this->db->query("SELECT position,sort_order FROM " . DB_PREFIX . "cheaper_module_position_sort WHERE `module_id`='" . (int)$module_id . "'");
		
		if ($query_position->num_rows){
			foreach($query_position->rows as $result){
				$positions[$result['position']] = $result['sort_order'];
			}
		}
		
		return $positions;
	}
	
	function getTotalFormula($module_id){
		$total = 1;
		$query_formula = $this->db->query("SELECT COUNT(DISTINCT formula_id) as total FROM " . DB_PREFIX . "cheaper_module_formula WHERE `module_id`='" . (int)$module_id . "' ORDER BY formula_id,sort_order ASC");
		if ($query_formula->num_rows){
			$total = $query_formula->row['total'];
		}
		return $total;
	}
	
	function getFormula($module_id, $temp){
		$result = array();
		
		$query_formula = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_formula" . $temp . " WHERE `module_id`='" . (int)$module_id . "' ORDER BY formula_id,sort_order ASC");
		
		if ($query_formula->num_rows){
			foreach ($query_formula->rows as $formula){
				if ($formula['type'] == 'field' || $formula['type'] == 'list'){
					$query_field = $this->db->query("SELECT DISTINCT name FROM " . DB_PREFIX . "cheaper_module_fields WHERE `module_id`='" . (int)$module_id . "' AND `id`='" . (int)$formula['value'] . "'");
					
					if (isset($query_field->row['name'])){
						$values = json_decode($query_field->row['name'],true);
						$value = $values[$this->config->get('config_language_id')];
					} else {
						$value = '';
					}
					$field_id = $formula['value'];
				} else {
					$value = $formula['value'];
					$field_id = 0;
				}
				
				$conditions = $this->getCondition($module_id, $formula['formula_id'], $formula['sort_order']);
				
				$result[$formula['formula_id']][$formula['sort_order']] = array(
					'type' => $formula['type'],
					'value' => $value,
					'show' => $formula['show'],
					'field_id' => $field_id,
					'conditions' => $conditions
				);
			}
		}
		
		return $result;
	}
	
	function getCondition($module_id, $formula_id, $sort_field){
		$result = array();
		
		$query_condition = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_condition WHERE `module_id`='" . (int)$module_id . "' AND `formula_id`='" . (int)$formula_id . "' AND `sort_field`='" . (int)$sort_field . "' ORDER BY formula_id ASC,sort_field ASC,`row` ASC,FIELD(`condition`,'if','to','else'),sort_order ASC");
		
		if ($query_condition->num_rows){
			foreach ($query_condition->rows as $condition){
				if ($condition['type'] == 'field'){
					$query_field = $this->db->query("SELECT DISTINCT name FROM " . DB_PREFIX . "cheaper_module_fields WHERE `module_id`='" . (int)$module_id . "' AND `id`='" . (int)$condition['value'] . "'");
					
					if (isset($query_field->row['name'])){
						$values = json_decode($query_field->row['name'],true);
						$value = $values[$this->config->get('config_language_id')];
					} else {
						$value = '';
					}
					$field_id = $condition['value'];
				} elseif ($condition['type'] == 'number') {
					$value = [];
					foreach (json_decode($condition['value'],true) as $valu){
						foreach ($valu as $language_id => $val){
							$value[$language_id] = $val;
						}
					}
					
					
					$field_id = 0;
				} else {
					$value = $condition['value'];
					$field_id = 0;
				}
				
				$result[$condition['sort_field']][$condition['row']][$condition['condition']][] = array(
					'type' => $condition['type'],
					'value' => $value,
					'field_id' => $field_id,
					'sort_order' => $condition['sort_order']
				);
			}
		}
		
		return $result;
		
	}
	
	public function getCheaperingFields(){
		$empty_position = 0;
		$position = 1;
		
		$query = $this->db->query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_NAME = '" . DB_PREFIX . "cheaper_module_value' AND COLUMN_NAME = 'sort'");
			
		if (!$query->num_rows){
			$this->db->query("ALTER TABLE " . DB_PREFIX . "cheaper_module_value ADD COLUMN sort int(11) NOT NULL AFTER `text`;");
		}
		
		if (isset($this->request->get['module_id'])){
			$module_id = " WHERE cm.module_id='" . (int)$this->request->get['module_id'] . "'";
			$empty_position = $this->emptyPosition($this->request->get['module_id']);
			$mod_id = " AND cmp.module_id='" . (int)$this->request->get['module_id'] . "'";
		} else {
			$module_id = "";
			$mod_id = "";
		}
		
		$this->load->language('extension/module/code');
		
		$res0 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cheaper_module_fields'");
		
		$results = array();
		if($res0->num_rows){
			
			$res_temp = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_position cm " . $module_id);
			
			if (!$res_temp->num_rows){
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_fields cm" . $module_id . " ORDER BY id ASC");
			} else {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_fields cm LEFT JOIN " . DB_PREFIX . "cheaper_module_position cmp ON (cm.id = cmp.id)" . $module_id . $mod_id . " ORDER BY cmp.position ASC,cmp.column ASC, cmp.sort, cm.id ASC");
				
			}
			
			if ($query->num_rows) {
				foreach ($query->rows as $result) {
					
					$query_value = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_value cm " . $module_id . " AND cm.`id`='" . (int)$result['id'] . "' ORDER BY cm.sort ASC");
					
					$select_value = array();
					if ($query_value->num_rows){
						foreach ($query_value->rows as $value){
							if (isset($value['value_id'])){
								$calc = array();
								
								$query_value_calc = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_value_calculator cm " . $module_id . " AND cm.`id`='" . (int)$value['id'] . "' AND cm.`value_id`='" . (int)$value['value_id'] . "'");
								
								if ($query_value_calc->num_rows){
									foreach ($query_value_calc->rows as $cal){
										$calc[$cal['formula_id']] = $cal['calc'];
									}
								}
								
								$select_value[$value['id']][] = array(
									'name' => json_decode($value['text'], true),
									'value' => $calc
								);
							}
						}
					}
					
					$position = 1; $column = 1;
					if ($empty_position && $module_id){
						$query_position = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_position cm " . $module_id . " AND cm.id='" . (int)$result['id'] . "'");
						if (isset($query_position->row['position'])) {
							$position = $query_position->row['position'];
						}
						if (isset($query_position->row['column'])) {
							$column = $query_position->row['column'];
						}
					}
					
					$formula_id = 1;
					$query_formula_id = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "cheaper_module_formula WHERE `module_id` = '" . (int)$module_id . "' AND `value` = '" . (int)$result['id'] . "' AND `type` = 'list'");
					
					$show = 0;
					if ($query_formula_id->num_rows){
						$formula_id = $query_formula_id->rows['formula_id'];
						$show = $query_formula_id->rows['show'];
					}
					
					$results[] = array(
						'id'   			=> $result['id'],
						'icon' 			=> isset($result['icon']) ? json_decode($result['icon'],true) : '',
						'name' 			=> json_decode($result['name'],true),
						'show'   		=> $show,
						'type' 			=> $result['type'],
						'position' 		=> $position,
						'formula_id' 	=> $formula_id,
						'column' 		=> $column,
						/*'sort' 			=> (!isset($result['sort']) ? $result['sort_order'] : $result['sort']),*/
						'placeholder' 	=> $this->language->get('text_' . $result['regex']),
						'regex' 		=> $result['regex'],
						'valid' 		=> json_decode($result['valid'],true),
						'required' 		=> $result['required'],
						'query_value' 	=> $select_value,
					);
				}
				
			}
		}
		
		return $results;
	}
	
	public function insertCheapering($data, $module_id = false){
		
		$this->EmptyCheaperingModule($module_id);
		
		$this->EmptyCheaperingPosition($module_id);
		$this->EmptyCheaperingFormula($module_id);
		
		if (isset($data['field_value'])) {
			if ($data['field_value']) {
				
				foreach ($data['field_value'] as $id => $result){
					if (isset($result['name'])) {
						$regex = "";
						if (isset($result['regex']) and $result['regex']) {
							$regex = ",`regex` = '" . $this->db->escape($result['regex']) . "'";
						}
						$valid = "";
						
						if (isset($result['valid']) and $result['valid']) {
							$valid = ",`valid` = '" . $this->db->escape(json_encode($result['valid'])) . "'";
						}
						
						
						$control_query_id = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_fields WHERE `id` = '" . (int)$id . "' AND `module_id` = '" . (int)$module_id . "'");
						
						if ($control_query_id->num_rows){
							$this->db->query("UPDATE " . DB_PREFIX . "cheaper_module_fields SET `icon` = '" . $this->db->escape(json_encode($result['icon'])) . "',`name` = '" . $this->db->escape(json_encode($result['name'])) . "',`type` = '" . $this->db->escape($result['type']) . "'" . $regex . $valid . ",`required` = '" . (isset($result['required']) ? (int)$result['required'] : '0') . "' WHERE `id` = '" . (int)$id . "' AND `module_id` = '" . (int)$module_id . "'");
							
							$last_id = $id;
							
						} else {
							$this->db->query("INSERT INTO " . DB_PREFIX . "cheaper_module_fields SET `id` = '" . (int)$id . "',`module_id` = '" . (int)$module_id . "',`icon` = '" . $this->db->escape(json_encode($result['icon'])) . "',`name` = '" . $this->db->escape(json_encode($result['name'])) . "',`type` = '" . $this->db->escape($result['type']) . "'" . $regex . $valid . ",`required` = '" . (isset($result['required']) ? (int)$result['required'] : '0') . "'");
							
							$last_id = $this->db->getLastId();
						}
						
						
						if (isset($result['!select!'])){$type = 'select';}
						if (isset($result['!radio!'])){$type = 'radio';}
						if (isset($result['!checked!'])){$type = 'checked';}
						
						if (isset($type) and isset($result['!' . $type . '!']) and $type == $result['type']){
							foreach ($result['!' . $type . '!'] as $sort => $resul){

									$this->db->query("INSERT INTO " . DB_PREFIX . "cheaper_module_value SET `id` = '" . (int)$id . "', `value_id` = '" . (int)$sort . "', `module_id` = '" . (int)$module_id . "',`type` = '" . $this->db->escape($type) . "',`text` = '" . $this->db->escape(json_encode($resul)) . "', `sort` = '" . (int)$sort . "'");

								
								if (isset($this->request->post['field_value_calc']) && $this->request->post['field_value_calc']){
									foreach ($this->request->post['field_value_calc'] as $formula_id => $calc){
										
										if (isset($calc[$id][$sort]['calc'])){
											$value_id = $calc[$id][$sort]['calc'];
											
											//if ($value_id){
												$this->db->query("INSERT INTO " . DB_PREFIX . "cheaper_module_value_calculator  SET `module_id` = '" . (int)$module_id . "', `formula_id` = '" . (int)$formula_id . "', `id` = '" . (int)$id . "', `value_id` = '" . (int)$sort . "', `calc` = '" . $value_id . "'");
											//}
										}
									}
								}
							}
						}
						
						$control_query_id = $this->db->query("SELECT * FROM " . DB_PREFIX . "cheaper_module_position WHERE `id` = '" . (int)$id . "' AND `module_id` = '" . (int)$module_id . "'");
						
						if (isset($data['field_column'][$id])){
							
							$arr_positions = explode('.', $data['field_column'][$id]);
							$position = $arr_positions[0];
							$column = $arr_positions[1];
							$sort_field = $arr_positions[2];
						
							if ($control_query_id->num_rows){
								$this->db->query("UPDATE " . DB_PREFIX . "cheaper_module_position  SET `position` = '" . (int)$position . "', `column` = '" . (int)$column . "', `sort` = '" . (int)$sort_field . "' WHERE `id` = '" . (int)$id . "' AND `module_id` = '" . (int)$module_id . "'");
							} else {
								$this->db->query("INSERT INTO " . DB_PREFIX . "cheaper_module_position  SET `module_id` = '" . (int)$module_id . "', `id` = '" . (int)$id . "', `position` = '" . (int)$position . "', `column` = '" . (int)$column . "', `sort` = '" . (int)$sort_field . "'");
							}
							
						} else {
							
							if ($control_query_id->num_rows){
								$this->db->query("UPDATE " . DB_PREFIX . "cheaper_module_position  SET `position` = '1', `column` = '1', `sort` = '" . (int)$id . "' WHERE `id` = '" . (int)$id . "' AND `module_id` = '" . (int)$module_id . "'");
							} else {
								$this->db->query("INSERT INTO " . DB_PREFIX . "cheaper_module_position  SET `module_id` = '" . (int)$module_id . "', `id` = '" . (int)$id . "', `position` = '1', `column` = '1', `sort` = '" . (int)$id . "'");
							}
							
						}
					}
				}
			}
		}
		
		if (isset($data['field_formula'])){
			
			$this->pasteFormula($module_id, $data['field_formula'], 'cheaper_module_formula');

		}
		//print_r($data['field_condition']);
		if (isset($data['field_condition'])){
			foreach ($data['field_condition'] as $formula_id => $formula){
				foreach ($formula as $sort_field => $sorts){
					foreach ($sorts as $row => $rows){
						foreach ($rows as $condition => $conditions){
							foreach ($conditions as $type => $results){
								if ($type == 'number'){
									foreach ($results as $id_symbol => $values){

										$value = [];
										foreach ($values as $language_id => $val){
											$value[$language_id] = $val;
										}
										$this->db->query("INSERT INTO " . DB_PREFIX . "cheaper_module_condition SET `module_id` = '" . (int)$module_id . "', `formula_id` = '" . (int)$formula_id . "', `sort_field` = '" . (int)$sort_field . "', `row` = '" . (int)$row . "', `condition` = '" . $this->db->escape(str_replace('\'','',$condition)) . "', `type` = '" . $this->db->escape(str_replace('\'','',$type)) . "', `value` = '" . $this->db->escape(json_encode($value,true)) . "', `sort_order` = '" . $this->db->escape($id_symbol) . "'");


									}
								} else {
									foreach ($results as $id_symbol => $values){
										foreach ($values as $key => $sort_order){
											$this->db->query("INSERT INTO " . DB_PREFIX . "cheaper_module_condition SET `module_id` = '" . (int)$module_id . "', `formula_id` = '" . (int)$formula_id . "', `sort_field` = '" . (int)$sort_field . "', `row` = '" . (int)$row . "', `condition` = '" . $this->db->escape(str_replace('\'','',$condition)) . "', `type` = '" . $this->db->escape(str_replace('\'','',$type)) . "', `value` = '" . $this->db->escape(str_replace('\'','',$id_symbol)) . "', `sort_order` = '" . $this->db->escape($sort_order) . "'");
										}
									}
								}
									
							}
						}
					}
				}
			}
		}
		
		if (isset($data['sort_position'])){
			foreach ($data['sort_position'] as $position => $sort_order){
				$this->db->query("INSERT INTO " . DB_PREFIX . "cheaper_module_position_sort  SET `module_id` = '" . (int)$module_id . "', `position` = '" . (int)$position . "', `sort_order` = '" . (int)$sort_order . "'");
			}
		}
		
		if (isset($data['style'])) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "cheaper_module_style` WHERE `module_id` = '" . (int)$module_id . "'");
			
			$this->db->query("INSERT INTO `" . DB_PREFIX . "cheaper_module_style` SET `module_id` = '" . (int)$module_id . "',`style` = '" . $this->db->escape(json_encode($data['style'])) . "'");
		}
		if (isset($data['format'])){
			$this->db->query("DELETE FROM `" . DB_PREFIX . "cheaper_module_protection` WHERE `module_id` = '" . (int)$module_id . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "cheaper_module_protection SET `module_id` = '" . (int)$module_id . "', `format` = '" . $this->db->escape($data['format']) . "',`text` = '" . $this->db->escape(json_encode($data['protection_text'], true)) . "'");
		}
		
		$this->deleteCheaperingEmail($module_id);
		if (isset($data['cheaper_email'])){
			if ($data['type_email'] == 1){
				foreach ($data['cheaper_email'] as $email){
					$this->db->query("INSERT INTO " . DB_PREFIX . "cheaper_module_email SET `module_id` = '" . (int)$module_id . "', `email` = '" . $this->db->escape($email) . "'");
				}
			}
		}
		
	}
	
	public function getMaxId($module_id) {
		$query = $this->db->query("SELECT MAX(id) AS max_id FROM `" . DB_PREFIX . "cheaper_module_fields` WHERE `module_id` = '" . (int)$module_id . "'");
		
		if ($query->rows){
			return $query->row['max_id'];
		} else {
			return 0;
		}
		
	}
	
	public function EmptyCheaperingModule($module_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "cheaper_module_fields` WHERE `module_id` = '" . (int)$module_id . "'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "cheaper_module_value WHERE `module_id` = '" . (int)$module_id . "'");
	}
	
	public function EmptyCheaperingPosition($module_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "cheaper_module_position` WHERE `module_id` = '" . (int)$module_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "cheaper_module_position_sort` WHERE `module_id` = '" . (int)$module_id . "'");
	}
	
	public function EmptyCheaperingFormula($module_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "cheaper_module_formula` WHERE `module_id` = '" . (int)$module_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "cheaper_module_value_calculator` WHERE `module_id` = '" . (int)$module_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "cheaper_module_condition` WHERE `module_id` = '" . (int)$module_id . "'");
	}
	
	public function deleteCheapering($id, $module_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "cheaper_module` WHERE `id` = '" . (int)$id . "' AND `module_id` = '" . (int)$module_id . "'");
		
	}
	
	public function deletelistcheapering($id, $module_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "cheaper_module` WHERE `id` = '" . (int)$id . "' AND `module_id` = '" . (int)$module_id . "'");
		
	}
	
	public function updatestatuscheapering($id, $status, $module_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "cheaper_module SET `status` = '" . (int)$status . "' WHERE `id` = '" . (int)$id . "' AND `module_id` = '" . (int)$module_id . "'");
	}
	
	public function getOptions($id_option, $product_id, $quantity) {
		$option_price = 0;
		$option_points = 0;
		$option_weight = 0;
		
		$option_data = array();

		foreach (json_decode($id_option) as $product_option_id => $value) {
			$option_query = $this->db->query("SELECT po.product_option_id, po.option_id, od.name, o.type FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_option_id = '" . (int)$product_option_id . "' AND po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");

			if ($option_query->num_rows) {
				if ($option_query->row['type'] == 'select' || $option_query->row['type'] == 'radio' || $option_query->row['type'] == 'image') {
					$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$value . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($option_value_query->num_rows) {
						if ($option_value_query->row['price_prefix'] == '+') {
							$option_price += $option_value_query->row['price'];
						} elseif ($option_value_query->row['price_prefix'] == '-') {
							$option_price -= $option_value_query->row['price'];
						}

						if ($option_value_query->row['points_prefix'] == '+') {
							$option_points += $option_value_query->row['points'];
						} elseif ($option_value_query->row['points_prefix'] == '-') {
							$option_points -= $option_value_query->row['points'];
						}

						if ($option_value_query->row['weight_prefix'] == '+') {
							$option_weight += $option_value_query->row['weight'];
						} elseif ($option_value_query->row['weight_prefix'] == '-') {
							$option_weight -= $option_value_query->row['weight'];
						}

						if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $quantity))) {
							$stock = false;
						}

						$option_data[] = array(
							'product_option_id'       => $product_option_id,
							'product_option_value_id' => $value,
							'option_id'               => $option_query->row['option_id'],
							'option_value_id'         => $option_value_query->row['option_value_id'],
							'name'                    => $option_query->row['name'],
							'value'                   => $option_value_query->row['name'],
							'type'                    => $option_query->row['type'],
							'quantity'                => $option_value_query->row['quantity'],
							'subtract'                => $option_value_query->row['subtract'],
							'price'                   => $option_value_query->row['price'],
							'price_prefix'            => $option_value_query->row['price_prefix'],
							'points'                  => $option_value_query->row['points'],
							'points_prefix'           => $option_value_query->row['points_prefix'],
							'weight'                  => $option_value_query->row['weight'],
							'weight_prefix'           => $option_value_query->row['weight_prefix']
						);
					}
				} elseif ($option_query->row['type'] == 'checkbox' && is_array($value)) {
					foreach ($value as $product_option_value_id) {
						$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

						if ($option_value_query->num_rows) {
							if ($option_value_query->row['price_prefix'] == '+') {
								$option_price += $option_value_query->row['price'];
							} elseif ($option_value_query->row['price_prefix'] == '-') {
								$option_price -= $option_value_query->row['price'];
							}

							if ($option_value_query->row['points_prefix'] == '+') {
								$option_points += $option_value_query->row['points'];
							} elseif ($option_value_query->row['points_prefix'] == '-') {
								$option_points -= $option_value_query->row['points'];
							}

							if ($option_value_query->row['weight_prefix'] == '+') {
								$option_weight += $option_value_query->row['weight'];
							} elseif ($option_value_query->row['weight_prefix'] == '-') {
								$option_weight -= $option_value_query->row['weight'];
							}

							if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $quantity))) {
								$stock = false;
							}

							$option_data[] = array(
								'product_option_id'       => $product_option_id,
								'product_option_value_id' => $product_option_value_id,
								'option_id'               => $option_query->row['option_id'],
								'option_value_id'         => $option_value_query->row['option_value_id'],
								'name'                    => $option_query->row['name'],
								'value'                   => $option_value_query->row['name'],
								'type'                    => $option_query->row['type'],
								'quantity'                => $option_value_query->row['quantity'],
								'subtract'                => $option_value_query->row['subtract'],
								'price'                   => $option_value_query->row['price'],
								'price_prefix'            => $option_value_query->row['price_prefix'],
								'points'                  => $option_value_query->row['points'],
								'points_prefix'           => $option_value_query->row['points_prefix'],
								'weight'                  => $option_value_query->row['weight'],
								'weight_prefix'           => $option_value_query->row['weight_prefix']
							);
						}
					}
				} elseif ($option_query->row['type'] == 'text' || $option_query->row['type'] == 'textarea' || $option_query->row['type'] == 'file' || $option_query->row['type'] == 'date' || $option_query->row['type'] == 'datetime' || $option_query->row['type'] == 'time') {
					$option_data[] = array(
						'product_option_id'       => $product_option_id,
						'product_option_value_id' => '',
						'option_id'               => $option_query->row['option_id'],
						'option_value_id'         => '',
						'name'                    => $option_query->row['name'],
						'value'                   => $value,
						'type'                    => $option_query->row['type'],
						'quantity'                => '',
						'subtract'                => '',
						'price'                   => '',
						'price_prefix'            => '',
						'points'                  => '',
						'points_prefix'           => '',
						'weight'                  => '',
						'weight_prefix'           => ''
					);
				}
			}
		}
		return $option_data;
	}
	
	public function pasteFormula($module_id, $field_formula, $table){
		foreach ($field_formula as $formula_id => $formula){
			foreach ($formula as $type => $typ){
				foreach ($typ as $value => $val){
					foreach ($val as $key => $sort_order){
						$type = str_replace('\'','',str_replace('"','',$type));
						$value = str_replace('\'','',str_replace('"','',$value));
						$show = 0;
						if (isset($this->request->post['formula_field_show'][$formula_id][$sort_order])) {
							$show = $this->request->post['formula_field_show'][$formula_id][$sort_order];
						}
						
						$this->db->query("INSERT INTO " . DB_PREFIX . $table . "  SET `module_id` = '" . (int)$module_id . "', `formula_id` = '" . (int)$formula_id . "', `show` = '" . (int)$show . "', `type` = '" . $this->db->escape($type) . "', `value` = '" . $this->db->escape($value) . "', `sort_order` = '" . (int)$sort_order . "'");
					}
				}
			}
		}
	}
	
	public function addDefaultValuesArray($default = false, $languages_code = array(), $all_text_items = array()) {
		
		$result = array();
		foreach ($languages_code as $language_code => $language_id){
			foreach (array('text_module_name','text_module_dlina_stroki','text_module_phone','text_module_email','text_module_email_client','text_module_desired_price','text_module_cheaper_find','text_module_question','text_module_age','text_module_30_years','text_module_30_40_years','text_module_40_50_years','text_module_50_years','text_module_like_shop','text_module_range_of','text_module_like_price','text_module_good_product','text_module_good_service','text_module_change_shop','text_module_time_call','text_summa_credit','text_stavka_credit','text_srok_credit','text_ejemes_credit','text_pereplata_credit','text_obchaya_summa_credit','text_required_name') as $item){
				if (isset($all_text_items[$item][$language_id])){$result[$item][$language_id] = $all_text_items[$item][$language_id];} else {
					$result[$item][$language_id] = '';
				}
			}
		}
		
		if ($default == 'cheaper'){
			return array(
				'1' => array(
					'id' => '1',
					'icon' => 'user',
					'name' => $result['text_module_name'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('text_module_name'),
					'regex' => 'minlength',
					'valid' => 2,
					'required' => 0,
					'sort_order' => 0
				),
				'2' => array(
					'id' => '2',
					'icon' => 'phone',
					'name' => $result['text_module_phone'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => 'text_phoneUS',
					'regex' => 'phoneUS',
					'valid' => array(
						'ru-ru' => '+7 (999) 999-99-99',
						'en-gb' => '+1-999-999 99 99',
						'uk-ua' => '+38 (099) 999 99 99'
					),
					'required' => 1,
					'sort_order' => 1,
				),
				'3' => array(
					'id' => '3',
					'icon' => 'envelope-o',
					'name' => $result['text_module_email'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('text_module_email_client'),
					'regex' => 'email',
					'valid' => '',
					'required' => 0,
					'sort_order' => 2,
				),
				'4' => array(
					'id' => '4',
					'icon' => 'rub',
					'name' => $result['text_module_desired_price'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => 'text_number',
					'regex' => 'number',
					'valid' => '',
					'required' => 0,
					'sort_order' => 3
				),
				'5' => array(
					'id' => '5',
					'icon' => 'link',
					'name' => $result['text_module_cheaper_find'],
					'type' => 'textarea',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => 'text_url',
					'regex' => 'url',
					'valid' => '',
					'required' => 1,
					'sort_order' => 4
				)
			);
		}
		if ($default == 'question'){
			return array(
				'1' => array(
					'id' => '1',
					'icon' => 'user',
					'name' => $result['text_module_name'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('text_module_dlina_stroki'),
					'regex' => 'minlength',
					'valid' => 2,
					'required' => 0,
					'sort_order' => 0
				),
				'2' => array(
					'id' => '2',
					'icon' => 'phone',
					'name' => $result['text_module_phone'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => 'text_phoneUS',
					'regex' => 'phoneUS',
					'valid' => array(
						'ru-ru' => '+7 (999) 999-99-99',
						'en-gb' => '+1-999-999 99 99',
						'uk-ua' => '+38 (099) 999 99 99'
					),
					'required' => 1,
					'sort_order' => 1,
				),
				'3' => array(
					'id' => '3',
					'icon' => 'envelope-o',
					'name' => $result['text_module_email'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('text_module_email_client'),
					'regex' => 'email',
					'valid' => '',
					'required' => 0,
					'sort_order' => 2,
				),
				'4' => array(
					'id' => '4',
					'icon' => 'question',
					'name' => $result['text_module_question'],
					'type' => 'textarea',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => 'text_number',
					'regex' => 'rangelength',
					'valid' => array(
						'0' => '5',
						'1' => '300'
					),
					'required' => 0,
					'sort_order' => 3
				)
			);
		}
		if ($default == 'survey'){
			return array(
				'1' => array(
					'id' => '1',
					'icon' => 'user',
					'name' => $result['text_module_age'],
					'type' => 'radio',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => '',
					'regex' => '',
					'valid' => '',
					'required' => 0,
					'sort_order' => 0,
					'query_value' 	=> array(
						'1' => array(
							'0' => array(
								'name' => $result['text_module_30_years']
							),
							'1' => array(
								'name' => $result['text_module_30_40_years']
							),
							'2' => array(
								'name' => $result['text_module_40_50_years']
							),
							'3' => array(
								'name' => $result['text_module_50_years']
							),
						),
					),
				),
				'2' => array(
					'id' => '2',
					'icon' => 'thumbs-o-up',
					'name' => $result['text_module_like_shop'],
					'type' => 'checked',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => '',
					'regex' => '',
					'valid' => '',
					'required' => 0,
					'sort_order' => 1,
					'query_value' 	=> array(
						'2' => array(
							'0' => array(
								'name' => $result['text_module_range_of']
							),
							'1' => array(
								'name' => $result['text_module_like_price']
							),
							'2' => array(
								'name' => $result['text_module_good_product']
							),
							'3' => array(
								'name' => $result['text_module_good_service']
							),
						),
					),
				),
				'3' => array(
					'id' => '3',
					'icon' => 'comments-o',
					'name' => $result['text_module_change_shop'],
					'type' => 'textarea',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('text_module_change_shop'),
					'regex' => 'rangelength',
					'valid' => array(
						'0' => '3',
						'1' => '300'
					),
					'required' => 0,
					'sort_order' => 2,
					'query_value' => array()
				)
			);
		}
		if ($default == 'callback'){
			return array(
				'1' => array(
					'id' => '1',
					'icon' => 'user',
					'name' => $result['text_module_name'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('text_module_dlina_stroki'),
					'regex' => 'minlength',
					'valid' => 2,
					'required' => 0,
					'sort_order' => 0
				),
				'2' => array(
					'id' => '2',
					'icon' => 'phone',
					'name' => $result['text_module_phone'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => 'text_phoneUS',
					'regex' => 'phoneUS',
					'valid' => array(
						'ru-ru' => '+7 (999) 999-99-99',
						'en-gb' => '+1-999-999 99 99',
						'uk-ua' => '+38 (099) 999 99 99'
					),
					'required' => 1,
					'sort_order' => 1,
				),
				'3' => array(
					'id' => '3',
					'icon' => 'envelope-o',
					'name' => $result['text_module_email'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('text_module_email_client'),
					'regex' => 'email',
					'valid' => '',
					'required' => 0,
					'sort_order' => 2,
				),
				'4' => array(
					'id' => '4',
					'icon' => 'clock-o',
					'name' => $result['text_module_time_call'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => 'text_time',
					'regex' => 'datetime',
					'valid' => '',
					'required' => 0,
					'sort_order' => 3,
				),
			);
		}
		if ($default == 'credit'){
			return array(
				'1' => array(
					'id' => '11',
					'icon' => 'user',
					'name' => $result['text_required_name'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('entry_name_fields'),
					'regex' => '',
					'valid' => '',
					'required' => 0,
					'query_value' => Array(),
				),
				'2' => array(
					'id' => '12',
					'icon' => 'phone',
					'name' => $result['text_module_phone'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => 'text_phoneUS',
					'regex' => 'phoneUS',
					'valid' => array(
						'ru-ru' => '+7 (999) 999-99-99',
						'en-gb' => '+1-999-999 99 99',
						'uk-ua' => '+38 (099) 999 99 99'
					),
					'required' => 1,
					'query_value' => Array(),
				),
				'3' => array(
					'id' => '3',
					'icon' => '',
					'name' => $result['text_summa_credit'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('entry_name_fields'),
					'regex' => 'range',
					'valid' => array(
						'0' => '3000',
						'1' => '300000'
					),
					'required' => 1,
					'query_value' => Array(),
				),
				'4' => array(
					'id' => '5',
					'icon' => '',
					'name' => $result['text_stavka_credit'],
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('entry_name_fields'),
					'regex' => '',
					'valid' => '',
					'required' => 1,
					'query_value' => Array(),
				),
				'5' => array(
					'id' => '8',
					'icon' => '',
					'name' => $result['text_srok_credit'],
					'type' => 'select',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('entry_name_fields'),
					'regex' => '',
					'valid' => '',
					'required' => 1,
					'query_value' => Array( 
						'8' => Array( 
							'0' => Array('name' => Array ('1' => $this->language->get('text_value_credit_1')), 'value' => Array('3' => '','2' => '1', '1' => '1')),
							'1' => Array('name' => Array ('1' => $this->language->get('text_value_credit_2')), 'value' => Array('3' => '','2' => '3', '1' => '3')),
							'2' => Array('name' => Array ('1' => $this->language->get('text_value_credit_3')), 'value' => Array('3' => '','2' => '6', '1' => '6')),
							'3' => Array('name' => Array ('1' => $this->language->get('text_value_credit_4')), 'value' => Array('3' => '','2' => '9', '1' => '9')),
							'4' => Array('name' => Array ('1' => $this->language->get('text_value_credit_5')), 'value' => Array('3' => '','2' => '12', '1' => '12')),
							'5' => Array('name' => Array ('1' => $this->language->get('text_value_credit_6')), 'value' => Array('3' => '','2' => '24', '1' => '24')),
							'6' => Array('name' => Array ('1' => $this->language->get('text_value_credit_7')), 'value' => Array('3' => '','2' => '36', '1' => '36')),
							'7' => Array('name' => Array ('1' => $this->language->get('text_value_credit_8')), 'value' => Array('3' => '','2' => '60', '1' => '60')),
							'8' => Array('name' => Array ('1' => $this->language->get('text_value_credit_9')), 'value' => Array('3' => '','2' => '120', '1' => '120')) 
						) 
					) 
				),
			);
		}
		if ($default == 'kirpish'){
			return array(
				'1' => array(
					'id' => '13',
					'icon' => '',
					'name' => array( '1' => 'Размеры кирпича', '2' => 'Brick sizes'),
					'show' => 0,
					'type' => select,
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('entry_name_fields'),
					'regex' => '',
					'valid' => '',
					'required' => 0, 
					'query_value' => array( 
						'13' => array( 
							'0' => array( 
								'name' => array( 
									'1' => '250x85x65 Евро',
									'2' => '250x85x65 Euro'
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => 0.00240975, 
									'5' => 0.00212,
									'4' => 0.0018525, 
									'3' => 0.001751562, 
									'2' => 0.00138125,
									'1' => 0.708333333,
								) 
							),
							'1' => array( 
								'name' => array( 
									'1' => '250x120x50 Слим',
									'2' => '250x120x50 Slim',
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => 0.002646,
									'5' => 0.002325375,
									'4' => 0.002028,
									'3' => 0.001915392,
									'2' => 0.0015,
									'1' => 1
								) 
							),
							'2' => array( 
								'name' => array( 
									'1' => '250x120x65 Одинарный',
									'2' => '250x120x65 Single'
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => 0.03213,
									'5' => 0.002862,
									'4' => 0.002535,
									'3' => 0.002410752,
									'2' => 0.00195,
									'1' => 1
								) 
							),
							'3' => array( 
								'name' => array( 
									'1' => '250x120x88 Полуторный',
									'2' => '250x120x88 One and a half',
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => 0.0040824,
									'5' => 0.003684825,
									'4' => 0.0033124,
									'3' => 0.003170304,
									'2' => 0.00264,
									'1' => 1
								) 
							),
							'4' => array( 
								'name' => array( 
									'1' => '250x120x130 Двойной',
									'2' => '250x120x130 Double'
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => 0.00567,
									'5' => 0.005187375,
									'4' => 0.004732,
									'3' => 0.004557312,
									'2' => 0.0039,
									'1' => 1
								) 
							),
							'5' => array( 
								'name' => array( 
									'1' => '250x120x140 Двойной',
									'2' => '250x120x140 Double'
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => 0.006048,
									'5' => 0.005545125,
									'4' => 0.00507,
									'3' => 0.004887552,
									'2' => 0.0042,
									'1' => 1 
								) 
							) 
						) 	
					) 
				),
				'2' => array( 
					'id' => 14, 
					'icon' => '',
					'name' => array( 
						'1' => 'Вид кладки',
						'2' => 'Type of masonry'
					),
					'show' => 0,
					'type' => select,
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('entry_name_fields'),
					'regex' => '',
					'valid' => '',
					'required' => 0,
					'query_value' => array( 
						'14' => array( 
							'0' => array( 
								'name' => array( 
									'1' => 'В полкирпича',
									'2' => 'Half a brick'
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => '',
									'5' => '',
									'4' => '',
									'3' => '',
									'2' => '',
									'1' => 0.12
								) 
							),
							'1' => array( 
								'name' => array( 
									'1' => 'В один кирпич',
									'2' => 'In one brick'
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => '',
									'5' => '',
									'4' => '',
									'3' => '',
									'2' => '',
									'1' => 0.25
								) 
							),
							'2' => array( 
								'name' => array( 
									'1' => 'В полтора кирпича',
									'2' => 'One and a half bricks'
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => '',
									'5' => '',
									'4' => '',
									'3' => '',
									'2' => '',
									'1' => 0.37
								) 
							),
							'3' => array( 
								'name' => array( 
									'1' => 'В два кирпича',
									'2' => 'Two bricks'
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => '',
									'5' => '',
									'4' => '',
									'3' => '',
									'2' => '',
									'1' => 0.5
								) 
							),
							'4' => array( 
								'name' => array( 
									'1' => 'В два с половиной кирпича',
									'2' => 'Two and a half bricks'
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => '',
									'5' => '',
									'4' => '',
									'3' => '',
									'2' => '',
									'1' => 0.62
								) 
							) 
						) 
					) 
				),
				'3' => array( 
					'id' => 15, 
					'icon' => '',
					'name' => array( 
						'1' => 'Периметр стен',
						'2' => 'Wall perimeter'
					),
					'show' => 0,
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('entry_name_fields'),
					'regex' => '',
					'valid' => '',
					'required' => 0,
					'query_value' => array( ) 
				),
				'4' => array( 
					'id' => 16,
					'icon' => '',
					'name' => array( 
						'1' => 'Высота стен',
						'2' => 'Wall height'
					),
					'show' => 0,
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('entry_name_fields'),
					'regex' => '',
					'valid' => '',
					'required' => 0,
					'query_value' => array( ) 
				),
				'5' => array( 
					'id' => 17,
					'icon' => '',
					'name' => array( 
						'1' => 'Толщина раствора в кладке',
						'2' => 'Thickness of mortar in masonry'
					),
					'show' => 0,
					'type' => 'select',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('entry_name_fields'),
					'regex' => '',
					'valid' => '',
					'required' => 0,
					'query_value' => array( 
						'17' => array( 
							'0' => array(
								'name' => array( 
									'1' => 'Раствор 8 мм',
									'2' => 'Solution 8 mm'
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => '',
									'5' => '',
									'4' => '',
									'3' => '',
									'2' => '',
									'1' => '',
								) 
							),
							'1' => array( 
								'name' => array( 
									'1' => 'Раствор 10 мм',
									'2' => 'Solution 10 mm'
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => '',
									'5' => '',
									'4' => '',
									'3' => '',
									'2' => '',
									'1' => '',
								) 
							),
							'2' => array( 
								'name' => array( 
									'1' => 'Раствор 15 мм',
									'2' => 'Solution 15 mm'
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => '',
									'5' => '',
									'4' => '',
									'3' => '',
									'2' => '',
									'1' => ''
								) 
							),
							'3' => array( 
								'name' => array( 
									'1' => 'Раствор 20 мм',
									'2' => 'Solution 20 mm'
								),
								'value' => array( 
									'8' => '',
									'7' => '',
									'6' => '',
									'5' => '',
									'4' => '',
									'3' => '',
									'2' => '',
									'1' => ''
								)
							)
						)
					)
				)
			);
		}
		if ($default == 'zabor'){
			return array(
				'1' => array(
					'id' => 13,
					'icon' => '',
					'name' => array(
						'1' => 'Периметр участка, м',
						'2' => 'Site perimeter, m'
					),
					'show' => 0,
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('entry_name_fields'),
					'regex' => '',
					'valid' => '',
					'required' => 0,
					'query_value' => array()
				),
				'2' => array(
					'id' => 14,
					'icon' => '',
					'name' => array(
						'1' => 'Высота забора, м',
						'2' => 'Fence height, m'
					),
					'show' => 0,
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('entry_name_fields'),
					'regex' => '',
					'valid' => '',
					'required' => 0,
					'query_value' => array()
				),
				'3' => array(
					'id' => 15,
					'icon' => '',
					'name' => array(
						'1' => 'Ширина штакетника, мм',
						'2' => 'Picket fence width, mm'
					),
					'show' => 0,
					'type' => 'select',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('entry_name_fields'),
					'regex' => '',
					'valid' => '',
					'required' => 0,
					'query_value' => array(
						'15' => array(
							'0' => array(
								'name' => array(
									'1' => 80,
									'2' => 80
								),
								'value' => array(
									'2' => '',
									'1' => 80
								)
							),
							'1' => array(
								'name' => array(
									'1' => 90,
									'2' => 90
								),
								'value' => array(
									'2' => '',
									'1' => 90
								)
							),
							'2' => array(
								'name' => array(
									'1' => 100,
									'2' => 100
								),
								'value' => array(
									'2' => '',
									'1' => 100
								)
							),
							'3' => array(
								'name' => array(
									'1' => 110,
									'2' => 110
								),
								'value' => array(
									'2' => '',
									'1' => 110
								)
							),
							'4' => array(
								'name' => array(
									'1' => 120,
									'2' => 120
								),
								'value' => array(
									'2' => '',
									'1' => 120
								)
							)
						),
					)
				),
				'4' => array( 
					'id' => 16,
					'icon' => '',
					'name' => array( 
						'1' => 'Расстояние между штакетниками, мм',
						'2' => 'Distance between picket fences, mm'
					),
					'show' => 0,
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('entry_name_fields'),
					'regex' => '',
					'valid' => '',
					'required' => 0,
					'query_value' => array()
				),
				'5' => array(
					'id' => 17,
					'icon' => '',
					'name' => array(
						'1' => 'Цена за 1 штакетник, руб',
						'2' => 'Price for 1 picket fence, rub'
					),
					'show' => 0,
					'type' => 'text',
					'position' => 1,
					'formula_id' => 1,
					'column' => 1,
					'placeholder' => $this->language->get('entry_name_fields'),
					'regex' => '',
					'valid' => '',
					'required' => 0,
					'query_value' => array()
				)
			);
		}
	}
	
	public function formula($default){
		if ($default == 'credit'){
			return array(
				'1' => array(
					'1' => array('type' => 'symbol', 'value' => $this->language->get('text_ejemes_credit'), 'field_id' => 0, 'show' => 1),
					'2' => array('type' => 'symbol', 'value' => '=', 'field_id' => 0, 'show' => 0),
					'3' => array('type' => 'function', 'value' => 'round', 'field_id' => 0, 'show' => 0),
					'4' => array('type' => 'symbol', 'value' => '(', 'field_id' => 0, 'show' => 0),
					'5' => array('type' => 'field', 'value' => $this->language->get('text_summa_credit'), 'field_id' => 3, 'show' => 0),
					'6' => array('type' => 'symbol', 'value' => '*', 'field_id' => 0, 'show' => 0),
					'7' => array('type' => 'symbol', 'value' => '(', 'field_id' => 0, 'show' => 0),
					'8' => array('type' => 'field', 'value' => $this->language->get('text_stavka_credit'), 'field_id' => 5, 'show' => 0),
					'9' => array('type' => 'symbol', 'value' => '/', 'field_id' => 0, 'show' => 0),
					'10' => array('type' => 'symbol', 'value' => '100', 'field_id' => 0, 'show' => 0),
					'11' => array('type' => 'symbol', 'value' => '/', 'field_id' => 0, 'show' => 0),
					'12' => array('type' => 'symbol', 'value' => '12', 'field_id' => 0, 'show' => 0),
					'13' => array('type' => 'symbol', 'value' => '*', 'field_id' => 0, 'show' => 0),
					'14' => array('type' => 'symbol', 'value' => '(', 'field_id' => 0, 'show' => 0),
					'15' => array('type' => 'symbol', 'value' => '(', 'field_id' => 0, 'show' => 0),
					'16' => array('type' => 'function', 'value' => 'pow', 'field_id' => 0, 'show' => 0),
					'17' => array('type' => 'symbol', 'value' => '(', 'field_id' => 0, 'show' => 0),
					'18' => array('type' => 'symbol', 'value' => '(', 'field_id' => 0, 'show' => 0),
					'19' => array('type' => 'symbol', 'value' => '1', 'field_id' => 0, 'show' => 0),
					'20' => array('type' => 'symbol', 'value' => '+', 'field_id' => 0, 'show' => 0),
					'21' => array('type' => 'field', 'value' => $this->language->get('text_stavka_credit'), 'field_id' => 5, 'show' => 0),
					'22' => array('type' => 'symbol', 'value' => '/', 'field_id' => 0, 'show' => 0),
					'23' => array('type' => 'symbol', 'value' => '100', 'field_id' => 0, 'show' => 0),
					'24' => array('type' => 'symbol', 'value' => '/', 'field_id' => 0, 'show' => 0),
					'25' => array('type' => 'symbol', 'value' => '12', 'field_id' => 0, 'show' => 0),
					'26' => array('type' => 'symbol', 'value' => ')', 'field_id' => 0, 'show' => 0),
					'27' => array('type' => 'symbol', 'value' => ',', 'field_id' => 0, 'show' => 0),
					'28' => array('type' => 'list', 'value' => $this->language->get('text_srok_credit'), 'field_id' => 8, 'show' => 0),
					'29' => array('type' => 'symbol', 'value' => ')', 'field_id' => 0, 'show' => 0),
					'30' => array('type' => 'symbol', 'value' => ')', 'field_id' => 0, 'show' => 0),
					'31' => array('type' => 'symbol', 'value' => ')', 'field_id' => 0, 'show' => 0),
					'32' => array('type' => 'symbol', 'value' => '/', 'field_id' => 0, 'show' => 0),
					'33' => array('type' => 'symbol', 'value' => '(', 'field_id' => 0, 'show' => 0),
					'34' => array('type' => 'function', 'value' => 'pow', 'field_id' => 0, 'show' => 0),
					'35' => array('type' => 'symbol', 'value' => '(', 'field_id' => 0, 'show' => 0),
					'36' => array('type' => 'symbol', 'value' => '(', 'field_id' => 0, 'show' => 0),
					'37' => array('type' => 'symbol', 'value' => '1', 'field_id' => 0, 'show' => 0),
					'38' => array('type' => 'symbol', 'value' => '+', 'field_id' => 0, 'show' => 0),
					'39' => array('type' => 'field', 'value' => $this->language->get('text_stavka_credit'), 'field_id' => 5, 'show' => 0),
					'40' => array('type' => 'symbol', 'value' => '/', 'field_id' => 0, 'show' => 0),
					'41' => array('type' => 'symbol', 'value' => '100', 'field_id' => 0, 'show' => 0),
					'42' => array('type' => 'symbol', 'value' => '/', 'field_id' => 0, 'show' => 0),
					'43' => array('type' => 'symbol', 'value' => '12', 'field_id' => 0, 'show' => 0),
					'44' => array('type' => 'symbol', 'value' => ')', 'field_id' => 0, 'show' => 0),
					'45' => array('type' => 'symbol', 'value' => ',', 'field_id' => 0, 'show' => 0),
					'46' => array('type' => 'list', 'value' => $this->language->get('text_srok_credit'), 'field_id' => 8, 'show' => 0),
					'47' => array('type' => 'symbol', 'value' => ')', 'field_id' => 0, 'show' => 0),
					'48' => array('type' => 'symbol', 'value' => '-', 'field_id' => 0, 'show' => 0),
					'49' => array('type' => 'symbol', 'value' => '1', 'field_id' => 0, 'show' => 0),
					'50' => array('type' => 'symbol', 'value' => ')', 'field_id' => 0, 'show' => 0),
					'51' => array('type' => 'symbol', 'value' => ')', 'field_id' => 0, 'show' => 0),
					'52' => array('type' => 'symbol', 'value' => ')', 'field_id' => 0, 'show' => 0),
				),
				'2' => array(
					'1' => array('type' => 'symbol', 'value' => $this->language->get('text_pereplata_credit'), 'field_id' => 0, 'show' => 1),
					'2' => array('type' => 'symbol', 'value' => '=', 'field_id' => 0, 'show' => 0),
					'3' => array('type' => 'symbol', 'value' => $this->language->get('text_ejemes_credit'), 'field_id' => 0, 'show' => 0),
					'4' => array('type' => 'symbol', 'value' => '*', 'field_id' => 0, 'show' => 0),
					'5' => array('type' => 'list', 'value' => $this->language->get('text_srok_credit'), 'field_id' => 8, 'show' => 0),
					'6' => array('type' => 'symbol', 'value' => '-', 'field_id' => 0, 'show' => 0),
					'7' => array('type' => 'field', 'value' => $this->language->get('text_summa_credit'), 'field_id' => 3, 'show' => 0),
				),
				'3' => array(	
					'1' => array('type' => 'symbol', 'value' => $this->language->get('text_obchaya_summa_credit'), 'field_id' => 0, 'show' => 1),
					'2' => array('type' => 'symbol', 'value' => '=', 'field_id' => 0, 'show' => 0),
					'3' => array('type' => 'field', 'value' => $this->language->get('text_summa_credit'), 'field_id' => 3, 'show' => 0),
					'4' => array('type' => 'symbol', 'value' => '+', 'field_id' => 0, 'show' => 0),
					'5' => array('type' => 'symbol', 'value' => $this->language->get('text_pereplata_credit'), 'field_id' => 0, 'show' => 0),
				)
			);
		}
		if ($default == 'kirpish'){
			return array(
				'1' => array(
					'1' => array(
						'type' => 'symbol',
						'value' => 'Общий объем стен, м3',
						'show' => 1,
						'field_id' => 0,
						'conditions' => array()
					),
					'2' => array(
						'type' => 'symbol',
						'value' => '=',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'3' => array(
						'type' => 'function',
						'value' => 'toFixed',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'4' => array(
						'type' => 'symbol',
						'value' => '(',
						'show' => 0, 
						'field_id' => 0,
						'conditions' => array() 
					),
					'5' => array(
						'type' => 'symbol',
						'value' => '(',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'6' => array(
						'type' => 'field',
						'value' => 'Периметр стен',
						'show' => 0,
						'field_id' => 15,
						'conditions' => array() 
					),
					'7' => array(
						'type' => 'symbol',
						'value' => '*',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'8' => array(
						'type' => 'field',
						'value' => 'Высота стен',
						'show' => 0,
						'field_id' => 16,
						'conditions' => array() 
					),
					'9' => array(
						'type' => 'symbol',
						'value' => '*',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'10' => array(
						'type' => 'list',
						'value' => 'Вид кладки',
						'show' => 0,
						'field_id' => 14,
						'conditions' => array() 
					),
					'11' => array(
						'type' => 'symbol',
						'value' => '*',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'12' => array(
						'type' => 'list',
						'value' => 'Размеры кирпича',
						'show' => 0,
						'field_id' => 13,
						'conditions' => array() 
					),
					'13' => array(
						'type' => 'symbol',
						'value' => ')',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'14' => array(
						'type' => 'symbol',
						'value' => ',', 
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'15' => array(
						'type' => 'symbol',
						'value' => 2,
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'16' => array(
						'type' => 'symbol',
						'value' => ')',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					) 
				),
				'2' => array(
					'1' => array(
						'type' => 'symbol',
						'value' => 'Кол-во кирпича на 1 м3 (без шва)',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'2' => array(
						'type' => 'symbol',
						'value' => '=',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'3' => array(
						'type' => 'function',
						'value' => 'ceil',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'4' => array(
						'type' => 'symbol',
						'value' => '(',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'5' => array(
						'type' => 'symbol',
						'value' => 1,
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'6' => array(
						'type' => 'symbol',
						'value' => '/',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'7' => array(
						'type' => 'list',
						'value' => 'Размеры кирпича',
						'show' => 0,
						'field_id' => 13,
						'conditions' => array() 
					),
					'8' => array(
						'type' => 'symbol',
						'value' => ')',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
				),
				'3' => array(
					'1' => array(
						'type' => 'symbol',
						'value' => 'Кол-во кирпича на 1 м3 (шов 8 мм)',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'2' => array(
						'type' => 'symbol',
						'value' => '=',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'3' => array(
						'type' => 'function',
						'value' => 'ceil',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'4' => array(
						'type' => 'symbol', 
						'value' => '(',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'5' => array(
						'type' => 'symbol', 
						'value' => 1,
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'6' => array(
						'type' => 'symbol',
						'value' => '/',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'7' => array(
						'type' => 'list',
						'value' => 'Размеры кирпича',
						'show' => 0,
						'field_id' => 13,
						'conditions' => array() 
					),
					'8' => array(
						'type' => 'symbol',
						'value' => ')',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					) 
				),
				'4' => array(
					'1' => array(
						'type' => 'symbol',
						'value' => 'Кол-во кирпича на 1 м3 (шов 10 мм)',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'2' => array(
						'type' => 'symbol',
						'value' => '=',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'3' => array(
						'type' => 'function',
						'value' => 'ceil',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'4' => array(
						'type' => 'symbol',
						'value' => '(',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'5' => array(
						'type' => 'symbol',
						'value' => 1,
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'6' => array(
						'type' => 'symbol',
						'value' => '/',
						'show' => 0,
						'field_id' => 0, 
						'conditions' => array() 
					),
					'7' => array(
						'type' => 'list',
						'value' => 'Размеры кирпича',
						'show' => 0,
						'field_id' => '13',
						'conditions' => array() 
					),
					'8' => array(
						'type' => 'symbol',
						'value' => ')',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					) 
				),
				'5' => array(
					'1' => array(
						'type' => 'symbol',
						'value' => 'Кол-во кирпича на 1 м3 (шов 15 мм)',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'2' => array(
						'type' => 'symbol',
						'value' => '=',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'3' => array(
						'type' => 'function',
						'value' => 'ceil',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'4' => array(
						'type' => 'symbol',
						'value' => '(',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'5' => array(
						'type' => 'symbol',
						'value' => 1,
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'6' => array(
						'type' => 'symbol',
						'value' => '/',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'7' => array(
						'type' => 'list',
						'value' => 'Размеры кирпича',
						'show' => 0,
						'field_id' => 13,
						'conditions' => array() 
					),
					'8' => array(
						'type' => 'symbol',
						'value' => ')',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					)
				),
				'6' => array(
					'1' => array(
						'type' => 'symbol',
						'value' => 'Кол-во кирпича на 1 м3 (шов 20 мм)',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'2' => array(
						'type' => 'symbol',
						'value' => '=',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'3' => array(
						'type' => 'function',
						'value' => 'ceil',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'4' => array(
						'type' => 'symbol',
						'value' => '(',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'5' => array(
						'type' => 'symbol',
						'value' => 1,
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'6' => array(
						'type' => 'symbol',
						'value' => '/',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'7' => array(
						'type' => 'list',
						'value' => 'Размеры кирпича',
						'show' => 0,
						'field_id' => 13,
						'conditions' => array() 
					),
					'8' => array(
						'type' => 'symbol',
						'value' => ')',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					) 
				),
				'7' => array(
					'1' => array(
						'type' => 'symbol',
						'value' => 'Кол-во кирпича на 1 м3, шт',
						'show' => 1,
						'field_id' => 0,
						'conditions' => array() 
					),
					'2' => array(
						'type' => 'symbol',
						'value' => '=',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array() 
					),
					'3' => array(
						'type' => 'condition',
						'value' => 'conditions',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array(
							'3' => array(
								'1' => array(
									'if' => array(
										'0' => array(
											'type' => 'field',
											'value' => 'Толщина раствора в кладке',
											'field_id' => 17,
											'sort_order' => 1 
										),
										'1' => array(
											'type' => 'symbol',
											'value' => '=',
											'field_id' => 0,
											'sort_order' => 2 
										),
										'2' => array(
											'type' => 'number',
											'value' => array(
												'1' => 'Раствор 8 мм',
												'2' => 'Solution 8 mm' 
											),
											'field_id' => 0,
											'sort_order' => 4 
										)
									),
									'to' => array(
										'0' => array(
											'type' => 'symbol',
											'value' => 'Кол-во кирпича на 1 м3 (шов 8 мм)',
											'field_id' => 0,
											'sort_order' => 1 
										) 
									) 
								),
								'2' => array(
									'if' => array(
										'0' => array(
											'type' => 'field',
											'value' => 'Толщина раствора в кладке',
											'field_id' => 17,
											'sort_order' => 1
										),
										'1' => array(
											'type' => 'symbol',
											'value' => '=',
											'field_id' => 0,
											'sort_order' => 2 
										),
										'2' => array(
											'type' => 'number',
											'value' => array(
												'1' => 'Раствор 10 мм',
												'2' => 'Solution 10 mm'
											),
											'field_id' => 0,
											'sort_order' => 4 
										) 
									),
									'to' => array(
										'0' => array(
											'type' => 'symbol',
											'value' => 'Кол-во кирпича на 1 м3 (шов 10 мм)',
											'field_id' => 0,
											'sort_order' => 1 
										) 
									) 
								),
								'3' => array(
									'if' => array(
										'0' => array(
											'type' => 'field',
											'value' => 'Толщина раствора в кладке',
											'field_id' => 17,
											'sort_order' => 1
										),
										'1' => array(
											'type' => 'symbol',
											'value' => '=',
											'field_id' => 0,
											'sort_order' => 2
										),
										'2' => array(
											'type' => 'number',
											'value' => array(
												'1' => 'Раствор 15 мм',
												'2' => 'Solution 15 mm'
											),
											'field_id' => 0,
											'sort_order' => 4 
										)
									),
									'to' => array(
										'0' => array(
											'type' => 'symbol', 
											'value' => 'Кол-во кирпича на 1 м3 (шов 15 мм)', 
											'field_id' => 0, 
											'sort_order' => 1 
										) 
									) 
								),
								'4' => array(
									'if' => array(
										'0' => array(
											'type' => 'field',
											'value' => 'Толщина раствора в кладке', 
											'field_id' => 17, 
											'sort_order' => 1 
										),
										'1' => array(
											'type' => 'symbol', 
											'value' => '=', 
											'field_id' => 0, 
											'sort_order' => 2 
										),
										'2' => array(
											'type' => 'number', 
											'value' => array(
												'1' => 'Раствор 20 мм', 
												'2' => 'Solution 20 mm' 
											),
											'field_id' => 0, 
											'sort_order' => 4 
										)
									),
									'to' => array(
										'0' => array(
											'type' => 'symbol', 
											'value' => 'Кол-во кирпича на 1 м3 (шов 20 мм)', 
											'field_id' => 0, 
											'sort_order' => 1 
										) 
									) 
								) 
							) 
						) 
					) 
				),
				'8' => array(
					'1' => array(
						'type' => 'symbol', 
						'value' => 'Общий расход кирпича, шт', 
						'show' => 1, 
						'field_id' => 0, 
						'conditions' => array() 
					),
					'2' => array(
						'type' => 'symbol', 
						'value' => '=', 
						'show' => 0, 
						'field_id' => 0, 
						'conditions' => array() 
					),
					'3' => array(
						'type' => 'function', 
						'value' => 'ceil', 
						'show' => 0, 
						'field_id' => 0, 
						'conditions' => array()
					),
					'4' => array(
						'type' => 'symbol', 
						'value' => '(', 
						'show' => 0, 
						'field_id' => 0, 
						'conditions' => array() 
					),
					'5' => array(
						'type' => 'symbol', 
						'value' => 'Общий объем стен, м3', 
						'show' => 0, 
						'field_id' => 0, 
						'conditions' => array() 
					),
					'6' => array(
						'type' => 'symbol', 
						'value' => '*', 
						'show' => 0, 
						'field_id' => 0, 
						'conditions' => array() 
					),
					'7' => array(
						'type' => 'symbol',
						'value' => 'Кол-во кирпича на 1 м3, шт', 
						'show' => 0, 
						'field_id' => 0, 
						'conditions' => array() 
					),
					'8' => array(
						'type' => 'symbol',
						'value' => ')', 
						'show' => 0, 
						'field_id' => 0, 
						'conditions' => array() 
					)
				)
			);
		}
		if ($default == 'zabor'){
			return array(
				'1' => array(
					'1' => array(
						'type' => 'symbol',
						'value' => 'Кол-во штакетника, шт',
						'show' => 1,
						'field_id' => 0,
						'conditions' => array()
					),
					'2' => array(
						'type' => 'symbol',
						'value' => '=',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'3' => array(
						'type' => 'function',
						'value' => 'ceil',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'4' => array(
						'type' => 'symbol',
						'value' => '(',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'5' => array(
						'type' => 'field',
						'value' => 'Периметр участка, м',
						'show' => 0,
						'field_id' => 13,
						'conditions' => array()
					),
					'6' => array(
						'type' => 'symbol',
						'value' => '/',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'7' => array(
						'type' => 'symbol',
						'value' => '(',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'8' => array(
						'type' => 'symbol',
						'value' => '(',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'9' => array(
						'type' => 'list',
						'value' => 'Ширина штакетника, мм',
						'show' => 0,
						'field_id' => 15,
						'conditions' => array()
					),
					'10' => array(
						'type' => 'symbol',
						'value' => '+',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'11' => array(
						'type' => 'field',
						'value' => 'Расстояние между штакетниками, мм',
						'show' => 0,
						'field_id' => 16,
						'conditions' => array()
					),
					'12' => array(
						'type' => 'symbol',
						'value' => ')',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'13' => array(
						'type' => 'symbol',
						'value' => '/',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'14' => array(
						'type' => 'symbol',
						'value' => 100,
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'15' => array(
						'type' => 'symbol',
						'value' => ')',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'16' => array(
						'type' => 'symbol',
						'value' => ')',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					)
				),
				'2' => array(
					'1' => array(
						'type' => 'symbol',
						'value' => 'Стоимость штакетника, руб',
						'show' => 1,
						'field_id' => 0,
						'conditions' => array()
					),
					'2' => array(
						'type' => 'symbol',
						'value' => '=',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'3' => array(
						'type' => 'symbol',
						'value' => 'Кол-во штакетника, шт',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'4' => array(
						'type' => 'symbol',
						'value' => '*',
						'show' => 0,
						'field_id' => 0,
						'conditions' => array()
					),
					'5' => array(
						'type' => 'field',
						'value' => 'Цена за 1 штакетник, руб',
						'show' => 0,
						'field_id' => 17,
						'conditions' => array()
					)
				)
			);
		}
	}
	
	public function functions(){
		return array(
			'round'	=> array(
				'onсlick' => 'addOneFunction',
				'help' => 'Возвращает число, округлённое к ближайшему целому',
			),
			'ceil'	=> array(
				'onсlick' => 'addOneFunction',
				'help' => 'Округляет число до ближайшего большего целого',
			),
			'floor'	=> array(
				'onсlick' => 'addOneFunction',
				'help' => 'Округляет число до ближайшего меньшего целого',
			),
			'toFixed'	=> array(
				'onсlick' => 'addDouFunction',
				'help' => 'Округляет число до указанного кол-ва знаков после зяпятой',
			),
			'pow'	=> array(
				'onсlick' => 'addDouFunction',
				'help' => 'Возведение числа в степень',
			),
			'sqrt'	=> array(
				'onсlick' => 'addDouFunction',
				'help' => 'Квадратный корень',
			),
			'nth_root'	=> array(
				'onсlick' => 'addDouFunction',
				'help' => 'Корень N степени',
			),
			'min'	=> array(
				'onсlick' => 'addDouFunction',
				'help' => 'Возвращает наименьшее из нескольких чисел через запятую',
			),
			'max'	=> array(
				'onсlick' => 'addDouFunction',
				'help' => 'Возвращает наибольшее из нескольких чисел через запятую',
			),
			'medium'	=> array(
				'onсlick' => 'addDouFunction',
				'help' => 'Возвращает среднее из нескольких чисел через запятую',
			),
			'strlen'	=> array(
				'onсlick' => 'addOneFunction',
				'help' => 'Возвращает количество символов в строке',
			),
			'ln'	=> array(
				'onсlick' => 'addOneFunction',
				'help' => 'Натуральный логарифм',
			),
			'lg'	=> array(
				'onсlick' => 'addOneFunction',
				'help' => 'Десятичный логарифм',
			),
			'log'	=> array(
				'onсlick' => 'addDouFunction',
				'help' => 'Логарифм числа по произвольному основанию',
			),
			'sin'	=> array(
				'onсlick' => 'addOneFunction',
				'help' => 'Синус угла в радианах',
			),
			'cos'	=> array(
				'onсlick' => 'addOneFunction',
				'help' => 'Косинус угла в радианах',
			),
			'tan'	=> array(
				'onсlick' => 'addOneFunction',
				'help' => 'Тангенс угла в радианах',
			),
			'ctg'	=> array(
				'onсlick' => 'addOneFunction',
				'help' => 'Котангенс угла в радианах',
			),
			'mod'	=> array(
				'onсlick' => 'addDouFunction',
				'help' => 'Остаток от деления двух чисел',
			),
		);
	}
	
	function operations(){
		$this->load->language('extension/module/cheaper30');
		
		return array(
			$this->language->get('text_ravno') => '=',
			$this->language->get('text_no_ravno') => '!=',
			$this->language->get('text_bolshe') => '>',
			$this->language->get('text_menshe') => '<',
			$this->language->get('text_bolshe_ravno') => '>=',
			$this->language->get('text_menshe_ravno') => '<=',
		);
	}
	
	public function config_version(){
		$data = array();
		
		$this->load->model('extension/module/cheaper30');
		
		$config_version = substr(VERSION, 0, 3);
		$data['version'] = $config_version;
		if ($config_version == '3.0' or $config_version == '3.1'){
			$data['module'] = 'module_';
			$data['token'] = 'user_token';
			$data['extension'] = 'marketplace/extension';
			$data['cancel'] = (isset($this->session->data['user_token']) ? $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true) : '');
			$data['action'] = (isset($this->session->data['user_token']) ? $this->url->link('extension/module/cheaper30', 'user_token=' . $this->session->data['user_token'], true) : '');
			$data['action_module_id'] = (isset($this->session->data['user_token']) ? $this->url->link('extension/module/cheaper30', 'user_token=' . $this->session->data['user_token'] . (isset($this->request->get['module_id']) ?  '&module_id=' . $this->request->get['module_id'] : ''), true) : '');
			$this->load->model('setting/module');
			$data['model_'] = 'model_setting_module';
			$this->load->model('setting/extension');
			$data['model_extension_'] = 'model_setting_extension';
		} else {
			$data['module'] = '';
			$data['token'] = 'token';
			$data['extension'] = 'extension/extension';
			$data['cancel'] = (isset($this->session->data['token']) ? $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true) : '');
			$data['action'] = (isset($this->session->data['token']) ? $this->url->link('extension/module/cheaper30', 'token=' . $this->session->data['token'], true) : '');
			$data['action_module_id'] = (isset($this->session->data['token']) ? $this->url->link('extension/module/cheaper30', 'token=' . $this->session->data['token'] . (isset($this->request->get['module_id']) ?  '&module_id=' . $this->request->get['module_id'] : ''), true) : '');
			$this->load->model('extension/module');
			$data['model_'] = 'model_extension_module';
			$this->load->model('extension/extension');
			$data['model_extension_'] = 'model_extension_extension';
		}
		if ($config_version == '2.2' or $config_version == '2.1' or $config_version == '2.0'){
			$data['extension'] = 'extension/module';
			$data['cancel'] = (isset($this->session->data['token']) ? $this->url->link('extension/module', 'token=' . $this->session->data['token'], true) : '');
			$data['action'] = (isset($this->session->data['token']) ? $this->url->link('module/cheaper30', 'token=' . $this->session->data['token'], true) : '');
			$data['action_module_id'] = (isset($this->session->data['token']) ? $this->url->link('module/cheaper30', 'token=' . $this->session->data['token'] . (isset($this->request->get['module_id']) ?  '&module_id=' . $this->request->get['module_id'] : ''), true) : '');
		}
		if ($config_version == '2.3'){
			$data['module'] = 'module_';
		}
		return $data;
	}
	
}
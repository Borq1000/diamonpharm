<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

class ControllerExtensionModuleSyncMS extends Controller {

  private $cron;
  private $redirectURL;
  private $logText;
  private $version;
  private $lock = null;

  private function SetVersion() {
    if (preg_match('/^2.1.*/', VERSION)) {
      $this->version = "2.1";
    } elseif (preg_match('/^2.3.*/', VERSION)) {
      $this->version = "2.3";
    } else {
      $this->version = "3";
    }
  }

  private function GetStartVars($settingsKey = null)
  {
    $this->SetVersion();
    if ($this->version == '2.1' || $this->version == '2.3') {
      $config = "syncms_";
      $token = "token";
    } else {
      $config = "module_syncms_";
      $token = "user_token";
    }

    $startVars = [];
    if ($this->cron) {
      $startVars['redirectURL'] = '';
    } else {
      if ($this->version == '2.1')
        $startVars['redirectURL'] = $this->config->get($config . 'admin_url') . 'index.php?route=module/syncms&' . $token . '=' . $this->config->get($config . "user_token");
      else
        $startVars['redirectURL'] = $this->config->get($config . 'admin_url') . 'index.php?route=extension/module/syncms&' . $token . '=' . $this->config->get($config . "user_token");
    }
    
    $this->redirectURL = $startVars['redirectURL'];

    $startVars['status'] = $this->config->get($config . 'status');

    if ($startVars['status'] == 0) {
      $this->Output('Ошибка! Модуль выключен', 'error_warning');
      exit();
    }

    if ($this->config->get($config . 'token') != '') {
      $startVars['headers'] = array("Authorization:Bearer " . $this->config->get($config . 'token'), "Content-Type: application/json");
    } else {
      $startVars['headers'] = array("Authorization:Basic " . base64_encode($this->config->get($config . 'login') . ':' . $this->config->get($config . 'password')), "Content-Type: application/json");
    } 

    if ($settingsKey != null) {
      foreach ($settingsKey as $key) {
        if ($this->config->get($config . $key) != '') {
          if (is_array($this->config->get($config . $key))) {
            $startVars[$key] = array_map("htmlspecialchars_decode", $this->config->get($config . $key));
          } else {
            $startVars[$key] = htmlspecialchars_decode($this->config->get($config . $key, ENT_COMPAT));
          }
        }
      }
    }
    
    return $startVars;
  }

  private function CreateLock($name)
  {
    if ($this->version == '2.1')
      $lock = DIR_APPLICATION . 'controller/module/' . $name . '.lock';
    else
      $lock = DIR_APPLICATION . 'controller/extension/module/' . $name . '.lock';
    if (file_exists($lock)) {
      if ((time() - filectime($lock)) / 60 < 15) {
        $this->Output('Ошибка! Данная синхронизация уже выполняется!', 'error_warning');
        exit();
        
      } else {
        unlink($lock);
      }
    }
    touch($lock);
    $this->lock = $lock;
  }

  private function CheckResponse($response, $ch, $text="", $exit=true)
  {
    //Обработка ошибок curl
    if (gettype($response) == "array") {
      if ($response == []) {
        if ($exit) {
          $this->Output("Ошибка! Мой Склад не отвечает на запрос", 'error_warning');
          exit();
        } else {
          $this->logText .= "Ошибка! Мой Склад не отвечает на запрос\n";
          return false;
        }
      }
      // Массив
      if (isset($response['errors'])) {
        if ($response['errors'][0]['code'] == '1073' || $response['errors'][0]['code'] == '1049') {
          while (isset($response['errors'])) {
            sleep(3);
            $response = json_decode(curl_exec($ch), true);
          }
          return $response;

        } else {
          if ($exit) {
            $this->Output($text . $response['errors'][0]['error'], 'error_warning');
            exit();
          } else {
            $this->logText .= $text . $response['errors'][0]['error'].  "\n";
            return false;
          }
        }
      }
    } else {
      // Строка
      if ($response == '') {
        if ($exit) {
          $this->Output("Ошибка! Мой Склад не отвечает на запрос", 'error_warning');
          exit();
        } else {
          $this->logText .= "Ошибка! Мой Склад не отвечает на запрос\n";
          return false;
        }
      }
      if (strpos($response, '"errors"') !== false) {
        if (strpos($response, '"code" : 1073') !== false || strpos($response, '"code" : 1049') !== false || strpos($response, '"code":1073') !== false || strpos($response, '"code":1049') !== false) {
          while (strpos($response, '"errors"') !== false) {
            sleep(3);
            $response = curl_exec($ch);
          }
          return $response;
        } else {
          if (preg_match("/\"error\" : \"(.+?)\"/", $response, $matches) === 1) {
            if ($exit) {
              $this->Output($text . $matches[1], 'error_warning');
              exit();
            } else {
              $this->logText .= $text . $matches[1] . "\n";
              return false;
            }
          }
          if (preg_match("/\"error\":\"(.+?)\"/", $response, $matches) === 1) {
            if ($exit) {
              $this->Output($text . $matches[1], 'error_warning');
              exit();
            } else {
              $this->logText .= $text . $matches[1] . "\n";
              return false;
            }
          }
        }
      }
    }

    return $response;
  }


  private function GetDataMS($row, $needData, $dataName, $dataMS)
  {
    foreach ($dataName as $key => $value) {
      if (!isset($dataMS[$value])) {
        $dataMS[$value] = [];
      }
    }

    foreach ($needData as $key => $value) {
      if (isset($row[$value])) {
        switch (gettype($row[$value])) {
          case "string":
            if ($dataName[$key] == 'description') {
              array_push($dataMS[$dataName[$key]], str_replace('\n', '<br>', $this->db->escape($row[$value])));
              break;
            }
            array_push($dataMS[$dataName[$key]], $this->db->escape($row[$value]));
            break;
          case "integer":
            if ($dataName[$key] == 'price') {
               array_push($dataMS[$dataName[$key]], (float)$row[$value] / 100);
               break;
            }
            array_push($dataMS[$dataName[$key]], (int)$row[$value]);         
            break;
          case "double":
            if ($dataName[$key] == 'price') {
               array_push($dataMS[$dataName[$key]], (float)$row[$value] / 100);
               break;
            }
            array_push($dataMS[$dataName[$key]], (float)$row[$value]);
            break;
        }
      } else {
        if ($dataName[$key] != 'price' && $dataName[$key] != 'quantity' && $dataName[$key] != 'weight') {
          array_push($dataMS[$dataName[$key]], "");
        } else {
          array_push($dataMS[$dataName[$key]], 0);
        } 
      }
    }

    return $dataMS;
  }


  private function GetDataSQL($query, $needData)
  {
    foreach ($needData as $key => $value) {
      $dataSQL[$value] = [];
    }
    
    foreach ($query->rows as $key => $value) {
      $i = 0;
      foreach ($value as $key1 => $value1) {
        if (is_null($value1)) {
          $dataSQL[$needData[$i]][$key] = "";
          $i++;
          continue;
        }
        switch (gettype($value1)) {
          case "string":
            $value1 = html_entity_decode(str_replace("&nbsp;", " ", htmlentities($value1)));
            $dataSQL[$needData[$i]][$key] = trim($this->db->escape(htmlspecialchars_decode($value1, ENT_COMPAT)));
            break;
          case "integer":
            $dataSQL[$needData[$i]][$key] = (int)htmlspecialchars_decode($value1, ENT_COMPAT);
            break;
          case "double":
            $dataSQL[$needData[$i]][$key] = (float)htmlspecialchars_decode($value1, ENT_COMPAT);
            break;
        }
        $i++;
      }
    }

    return $dataSQL;
  }


  private function GetCategoryFast($pathName, $responseCategory)
  {
    if ($pathName != '') {
      $pathName = str_replace("\'", "'", $pathName);

      $categoryOffset = 0;
      for ($i = 0; $i < 20; $i++) { 
        $pos = strripos($pathName, '/', -1 * $categoryOffset);
        if ($pos === false) {
          $category = $pathName;
          break;
        }
        $subCategoryMS = substr($pathName, $pos + 1);
        $categoryOffset = (strlen($pathName) - $pos) + 1;
        if (strpos($responseCategory, '"name" : "' . $subCategoryMS . '"') != false) {
          $subCategoryMS = $subCategoryMS;
          $category = $subCategoryMS;
          break;
        }
      }
    } else {
      $category = "";
    }

    $category = str_replace("'", "\'", $category);
    
    return $category;
  }


  private function GetMetaTag($tag, $replaceVar, $replaceData)
  {
    foreach ($replaceData as $key => $value) {
      $tag = str_replace(sprintf("[%s]", $replaceVar[$key]), $value, $tag);
    }
    
    return $tag;
  }


  private function CurlInit($headers)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_ENCODING , "gzip ''");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    return $ch;
  }


  private function Translit($s) {
    $s = (string) $s;
    $s = strip_tags($s);
    $s = str_replace(array("\n", "\r"), " ", $s);
    $s = preg_replace("/\s+/", ' ', $s);
    $s = trim($s);
    $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s);
    $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
    $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s);
    $s = str_replace(" ", "-", $s);
    return $s;
  }


  private function InitialDataMS($needData)
  {
    $dataMS = [];
    foreach ($needData as $key => $value) {
      $dataMS[$value] = [];
    }
    return $dataMS;
  }


  private function GetBundleQuantity($dataMS, $ch)
  {
    //Занесение количества товаров в массив
    foreach ($dataMS['quantity'] as $key => $value) {
      if (gettype($value) == 'string') {
        curl_setopt($ch, CURLOPT_URL, $value);
        $responseComponents = json_decode(curl_exec($ch), true);
        $responseComponents = $this->CheckResponse($responseComponents, $ch);
        $ratio = [];
        foreach ($responseComponents['rows'] as $value1) {
          $componentId = $value1['assortment']['meta']['href'];
          $componentId = strrchr($componentId, '/');
          $componentId = substr($componentId, 1);
          $componentQuantity = $value1['quantity'];
          $componentIndex = array_search($componentId, $dataMS['id'], true);
          if ($componentIndex === false) {
            curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=id=' . $componentId);
            $responseProd = json_decode(curl_exec($ch), true);
            $responseProd = $this->CheckResponse($responseProd, $ch);
            if (isset($responseProd['rows'][0]['quantity'])) {
              if ($responseProd['rows'][0]['quantity'] < 0) {
                array_push($ratio, 0);
              } else {
                array_push($ratio, floor($responseProd['rows'][0]['quantity'] / $componentQuantity));
              }
            } else {
              array_push($ratio, 0);
            }   
            
          } else {
            if ($dataMS['quantity'][$componentIndex] < 0) {
              array_push($ratio, 0);
            } else {
              array_push($ratio, floor($dataMS['quantity'][$componentIndex] / $componentQuantity));
            }
            
          }
        }
        $dataMS['quantity'][$key] = min($ratio);
      }
    }

    return $dataMS;
  }


  private function GetLanguageId()
  {
    $query = $this->db->query("SELECT language_id FROM " . DB_PREFIX . "language WHERE code = '" . $this->config->get("config_admin_language") . "' ");
    return $query->row['language_id'];
  }
  

  private function DuplicateCheck($data, $type, $bindingName=null)
  {
    $duplicate = [];
    if (count($data[0]) != count(array_unique($data[0]))) {
      if ($type == "product") {
        if ($bindingName == 0) {
          // Только binding
          foreach (array_count_values($data[0]) as $key => $value) {
            if ($value > 1) {
              if ($key == '')
                $duplicate[] = '[не заполнено]';
              else
                $duplicate[] = $key;
            }
          }
        } else {
          // Binding + наименование
          foreach (array_count_values($data[0]) as $key => $value) {
            $names = [];
            foreach (array_keys($data[0], $key) as $value1) {
              if (in_array($data[1][$value1], $names, true)) {
                $duplicate[] = [$data[0][$value1], $data[1][$value1]];
                break;
              } else {
                array_push($names, $data[1][$value1]);
              }
            }
          }
        }
      } else {
        // Категории
        foreach (array_count_values($data[0]) as $key => $value) {
          if ($value > 1) {
            $duplicate[] = $key;
          }
        }
      }
    }

    return $duplicate;
  }

  private function GetFromGroups($groups, $type, $ch)
  {
    if ($type == "category") {
      if (isset($groups)) {
        $fromGroup = [];
        foreach (explode(";", $groups) as $key => $value) {
          $fromGroup[] = trim($value);
        }
      }

      return $fromGroup;
    } else {
      if (isset($groups)) {
        $fromGroup = [];
        $msGroups = [];
        $url = 'https://api.moysklad.ru/api/remap/1.2/entity/productfolder?filter=';
        foreach (explode(";", $groups) as $key => $value) {
          $value = trim($value);
          $msGroups[] = urlencode($value);
        }

        $url .= "name=" . implode(";name=", $msGroups);

        curl_setopt($ch, CURLOPT_URL, $url);
        $response = json_decode(curl_exec($ch), true);
        $response = $this->CheckResponse($response, $ch);

        foreach ($response['rows'] as $key => $value) {
          $fromGroup[] = $value['meta']['href'];
        }
      }

      return $fromGroup;
    }

    return [];
  }

  private function CheckFromGroup($fromGroup, $pathName, $name, $type, $reverse)
  {
    $skip = true;
    foreach ($fromGroup as $groupKey => $groupValue) {
      $escaped = str_replace(["\\", "/", "-", ")", "(", ".", "?", "&", "^", "+", "*", "{", "}"], ["\\\\", "\/", "\-", "\)", "\(", "\.", "\?", "\&", "\^", "\+", "\*", "\{", "\}"], $groupValue);
      if (preg_match("/\/{$escaped}\//", $pathName)) {
        $skip = false;
        break;
      } elseif (preg_match("/^{$escaped}\//", $pathName)) {
        $skip = false;
        break;
      } elseif (preg_match("/\/{$escaped}$/", $pathName)) {
        $skip = false;
        break;
      } elseif ($pathName == $groupValue) {
        $skip = false;
        break;
      } elseif ($type == 'category' && $name == $groupValue) {
        $skip = false;
        break;
      }
    }

    if ($reverse) {
      return !$skip;
    } else {
      return $skip;
    }
  }

  private function Output($text, $type, $redirect=true)
  {
    if ($this->lock != null)
      unlink($this->lock);

    if ($type == 'error_warning') {
      $this->logText .= $text;
      if ($redirect)
        $this->LogWrite();
      else
        $this->logText .= PHP_EOL;
    }

    if ($this->cron) {
      echo $text;
    } else {
      $textEscape = $this->db->escape($text);
      if ($this->version == '2.1' || $this->version == "2.3") {
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` = 'syncms_{$type}'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "setting (`code`, `key`, `value`) VALUES ('syncms', 'syncms_{$type}', '{$textEscape}')");
      } else {
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` = 'module_syncms_{$type}'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "setting (`code`, `key`, `value`) VALUES ('module_syncms', 'module_syncms_{$type}', '{$textEscape}')");
      }
      
      $this->session->data[$type] = $text;
      if ($redirect)
        $this->response->redirect($this->redirectURL); 
    }
  }

  private function repairCategories($parent_id = 0) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category WHERE parent_id = '" . (int)$parent_id . "'");

    foreach ($query->rows as $category) {
      // Delete the path below the current one
      $this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category['category_id'] . "'");

      // Fix for records with no paths
      $level = 0;

      $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$parent_id . "' ORDER BY level ASC");

      foreach ($query->rows as $result) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

        $level++;
      }

      $this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$category['category_id'] . "', level = '" . (int)$level . "'");

      $this->repairCategories($category['category_id']);
    }
  }

  //=========================Категории=========================

  public function CategoryAdd() {
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }

    $this->logText = date('H:i:s d.m.Y') . ' Добавление категорий' . PHP_EOL;

    $this->db->query("set session wait_timeout=28800");
    $startVars = $this->GetStartVars(["cat_meta_title", "cat_meta_desc", "cat_meta_keyword", "cat_offset", "meta_cat_update", "from_group", "category_binding", "cat_url_update", "not_from_group", "category_time_limit"]);

    // Измененные за последние минуты
    if (isset($startVars['category_time_limit'])) {
      $minuteLimit = $startVars['category_time_limit'];

      if (strpos($minuteLimit, '.') !== false || $minuteLimit < 0) {
        $this->Output('Ошибка! Количество минут должно быть целым неотрицательным числом', 'error_warning');
        exit();
      }

      $dateLimit = new DateTime();
      $dateLimit->modify("- $minuteLimit minutes");
      $dateLimit = $dateLimit->format("Y-m-d H:i:s");
    }

    // Мета-теги
    if ($startVars['meta_cat_update'] == 1) {
      $metaTitle = (isset($startVars['cat_meta_title']) ? $this->db->escape($startVars['cat_meta_title']) : '');
      $metaDesc = (isset($startVars['cat_meta_desc']) ? $this->db->escape($startVars['cat_meta_desc']) : '');
      $metaKeyword = (isset($startVars['cat_meta_keyword']) ? $this->db->escape($startVars['cat_meta_keyword']) : '');
    }
    
    $languageId = $this->GetLanguageId();

    $dataMS = $this->InitialDataMS(['name', 'id', 'parent_name', 'parent_id']);

    $ch = $this->CurlInit($startVars['headers']);

    // Определенные категории
    if (isset($startVars['from_group'])) {
      $fromGroup = $this->GetFromGroups($startVars['from_group'], "category", $ch);
    }
    if (isset($startVars['not_from_group'])) {
      $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "category", $ch);
    }

    if ($this->cron || $startVars['cat_offset'] == 0) {
      $offset = 0;
    } else {
      $offset = $startVars['cat_offset'] - 1;
    }

    $key = 0;
    do {
      //Получение категорий по curl

      $url = 'https://api.moysklad.ru/api/remap/1.2/entity/productfolder?offset=' . $offset . '&limit=100&expand=productFolder';

      if (isset($startVars['category_time_limit'])) {
        $url .= "&filter=updated" . urlencode(">=" . $dateLimit);
      }

      curl_setopt($ch, CURLOPT_URL, $url);

      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      //Занесение данных в массивы
      foreach ($response['rows'] as $row) {
        // Синхронизация категорий и товаров из группы
        if (isset($startVars['from_group'])) {
          if ($this->CheckFromGroup($fromGroup, $row['pathName'], $row['name'], 'category', false))
            continue;
        }
        if (isset($startVars['not_from_group'])) {
          if ($this->CheckFromGroup($notFromGroup, $row['pathName'], $row['name'], 'category', true))
            continue;
        }

        $dataMS = $this->GetDataMS($row, ['name', 'id'], ['name', 'id'], $dataMS);
        if (isset($row['productFolder'])) {
          $dataMS['parent_name'][$key] = $this->db->escape($row["productFolder"]['name']);
          $dataMS['parent_id'][$key] = $row["productFolder"]['id'];
        } else {
          $dataMS['parent_name'][$key] = "";
          $dataMS['parent_id'][$key] = "";
        }

        $key++;
      }

      if (!$this->cron && $startVars['cat_offset'] != 0) {
        if ($offset >= $startVars['cat_offset'] + 899)
          break;
      }

      $offset += 100;
    } while (isset($response['meta']['nextHref']));

    curl_close($ch);

    if ($startVars['category_binding'] == "name") {
      //Добавление в массивы имен и id всех категорий в БД
      $query = $this->db->query("SELECT name, category_id FROM " . DB_PREFIX . "category_description");
      $dataSQL = $this->GetDataSQL($query, ['name', 'category_id']);

      //Удаление категорий, которые уже есть в БД
      foreach ($dataMS['name'] as $key => $value) {
        $find = array_search($value, $dataSQL['name'], true);
        if ($find !== false) {
          unset($dataMS['name'][$key], $dataMS['id'][$key], $dataMS['parent_name'][$key], $dataMS['parent_id'][$key]);
        }
      }
    } else {
      // Проверка существования столбца syncms_id
      $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category` LIKE 'syncms_id'");
      if (!isset($query->row['Field'])) {
        $query = $this->db->query("ALTER TABLE " . DB_PREFIX . "category 
          ADD COLUMN syncms_id VARCHAR(255)");
      }

      $query = $this->db->query("SELECT syncms_id, category_id FROM " . DB_PREFIX . "category");
      $dataSQL = $this->GetDataSQL($query, ['syncms_id', 'category_id']);

      // Удаление категорий, которые уже есть в БД
      foreach ($dataMS['id'] as $key => $value) {
        $find = array_search($value, $dataSQL['syncms_id'], true);
        if ($find !== false) {
          unset($dataMS['name'][$key], $dataMS['id'][$key], $dataMS['parent_name'][$key], $dataMS['parent_id'][$key]);
        }
      }
    }

    $dataMS['name'] = array_values($dataMS['name']);
    $dataMS['id'] = array_values($dataMS['id']);
    $dataMS['parent_name'] = array_values($dataMS['parent_name']);
    $dataMS['parent_id'] = array_values($dataMS['parent_id']);

    // Формирование запросов
    if (count($dataSQL['category_id']) == 0) {
      $lastId = 0;
    } else {  
      $lastId = max($dataSQL['category_id']);
    }
    
    $date_added = date('Y-m-d H:i:s');
    $insertCategory = [];
    $insertCategoryDescription = [];
    $insertCategoryToStore = [];
    $insertSeoUrl = [];
    $insertWhere = [];
    $added = [];

    $lastCategoryId = $lastId + 1;

    // URL
    if ($this->version == '2.1' || $this->version == "2.3") {
      $query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE `query` LIKE '%category_id=%'");
    } else {
      $query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE `query` LIKE '%category_id=%'");
    }
    
    $keywords = $this->GetDataSQL($query, ['keyword'])["keyword"];

    foreach ($dataMS['name'] as $key => $value) {
      $lastId++;
      array_push($insertWhere, $lastId);
      $keyword = $this->Translit($value);

      if (in_array($keyword, $keywords, true)) {
        $keyword .= "-" . $lastId;
      } else {
        array_push($keywords, $keyword);
      }
      
      // Category
      if ($startVars['category_binding'] == "name") {
        // Наименование
        if ($dataMS['parent_name'][$key] != "") {
          $parentKey = array_search($dataMS['parent_name'][$key], $dataSQL['name'], true);
          if ($parentKey !== false) {
            $parentId = $dataSQL['category_id'][$parentKey];
          } else {
            $parentKey = array_search($dataMS['parent_name'][$key], $dataMS['name'], true);
            if ($parentKey !== false)
              $parentId = $lastCategoryId + $parentKey;
            else
              $parentId = 0;
          }
        } else {
          $parentId = 0;
        }
        array_push($insertCategory, "('$lastId', '$parentId', '1', '1', '1', '1', '$date_added', '$date_added')");
      } else {
        // MS id
        if ($dataMS['parent_id'][$key] != "") {
          $parentKey = array_search($dataMS['parent_id'][$key], $dataSQL['syncms_id'], true);
          if ($parentKey !== false) {
            $parentId = $dataSQL['category_id'][$parentKey];
          } else {
            $parentKey = array_search($dataMS['parent_id'][$key], $dataMS['id'], true);
            if ($parentKey !== false)
              $parentId = $lastCategoryId + $parentKey;
            else
              $parentId = 0;
          }
        } else {
          $parentId = 0;
        }
        
        array_push($insertCategory, sprintf("('%s', '$parentId', '1', '1', '1', '1', '%s', '%s', '%s')", $lastId, $date_added, $date_added, $dataMS['id'][$key]));
      }
      
      if ($startVars['meta_cat_update'] == 1) {
        $metaTitleNew = $this->GetMetaTag($metaTitle, ['name'], [$value]);
        $metaDescNew = $this->GetMetaTag($metaDesc, ['name'], [$value]);
        $metaKeywordNew = $this->GetMetaTag($metaKeyword, ['name'], [$value]);
      } else {
        $metaTitleNew = "";
        $metaDescNew = "";
        $metaKeywordNew = "";
      }

      // Category Description
      array_push($insertCategoryDescription, sprintf("('%s', '%s', '%s', '%s', '%s', '%s')", $lastId, $languageId, htmlspecialchars($value, ENT_COMPAT), htmlspecialchars($metaTitleNew, ENT_COMPAT), htmlspecialchars($metaDescNew, ENT_COMPAT), htmlspecialchars($metaKeywordNew, ENT_COMPAT)));
      array_push($added, $value);
      array_push($insertCategoryToStore, "('$lastId', '0')");
      
      // URL
      if ($startVars['cat_url_update'] == 1) {
        if ($this->version == '2.1' || $this->version == "2.3") {
          array_push($insertSeoUrl, "('category_id=$lastId', '$keyword')");
        } else {
          array_push($insertSeoUrl, "('0', '$languageId', 'category_id=$lastId', '$keyword')"); 
        }
      }
    }

    //Отправление запросов
    $categoryAddedNum = count($insertCategory);
    $insertCategory = implode(', ', $insertCategory);
    $insertCategoryDescription = implode(', ', $insertCategoryDescription);
    $insertCategoryToStore = implode(', ', $insertCategoryToStore);
    $insertSeoUrl = implode(', ', $insertSeoUrl);

    if ($insertCategory != "") {
      if ($startVars['category_binding'] == "name") {
        $this->db->query("INSERT INTO " . DB_PREFIX . "category (`category_id`, `parent_id`, `top`, `column`, `sort_order`, `status`, `date_added`, `date_modified`) VALUES $insertCategory");
      } else {
        $this->db->query("INSERT INTO " . DB_PREFIX . "category (`category_id`, `parent_id`, `top`, `column`, `sort_order`, `status`, `date_added`, `date_modified`, `syncms_id`) VALUES $insertCategory");
      }
      
      $this->db->query("INSERT INTO " . DB_PREFIX . "category_description (`category_id`, `language_id`, `name`, `meta_title`, `meta_description`, `meta_keyword`) VALUES $insertCategoryDescription");
      $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store (`category_id`, `store_id`) VALUES $insertCategoryToStore");

      if ($startVars['cat_url_update'] == 1) {
        if ($this->version == '2.1' || $this->version == "2.3") {
          $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias (`query`, `keyword`) VALUES $insertSeoUrl");
        } else {
          $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url (`store_id`, `language_id`, `query`, `keyword`) VALUES $insertSeoUrl");
        }
      }
    }

    $this->repairCategories();

    $this->logText .= 'Добавлено категорий: ' . $categoryAddedNum . PHP_EOL;
    foreach ($added as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    $this->LogWrite();
    $this->Output('Успешно. Категорий добавлено: ' . $categoryAddedNum, 'success');
  }


  public function CategoryUpdate() {
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }
    
    $this->logText = date('H:i:s d.m.Y') . ' Обновление категорий' . PHP_EOL;
    $this->db->query("set session wait_timeout=28800");
    $startVars = $this->GetStartVars(["meta_cat_update", "cat_meta_title", "cat_meta_desc", "cat_meta_keyword", "cat_offset", "from_group", "category_binding", "not_from_group", "category_time_limit"]);

    // Измененные за последние минуты
    if (isset($startVars['category_time_limit'])) {
      $minuteLimit = $startVars['category_time_limit'];

      if (strpos($minuteLimit, '.') !== false || $minuteLimit < 0) {
        $this->Output('Ошибка! Количество минут должно быть целым неотрицательным числом', 'error_warning');
        exit();
      }

      $dateLimit = new DateTime();
      $dateLimit->modify("- $minuteLimit minutes");
      $dateLimit = $dateLimit->format("Y-m-d H:i:s");
    }

    if ($startVars['meta_cat_update'] == 1) {
      $metaTitle = (isset($startVars['cat_meta_title']) ? $this->db->escape($startVars['cat_meta_title']) : '');
      $metaDesc = (isset($startVars['cat_meta_desc']) ? $this->db->escape($startVars['cat_meta_desc']) : '');
      $metaKeyword = (isset($startVars['cat_meta_keyword']) ? $this->db->escape($startVars['cat_meta_keyword']) : '');
    }

    $dataMS = $this->InitialDataMS(['name', 'pathName', 'category', 'skip', 'id', 'parent_id']);

    $ch = $this->CurlInit($startVars['headers']);

    // Определенные категории
    if (isset($startVars['from_group'])) {
      $fromGroup = $this->GetFromGroups($startVars['from_group'], "category", $ch);
    }
    if (isset($startVars['not_from_group'])) {
      $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "category", $ch);
    }

    if ($this->cron || $startVars['cat_offset'] == 0) {
      $offset = 0;
    } else {
      $offset = $startVars['cat_offset'] - 1;
    }

    $key = 0;
    do {
      //Получение категорий через curl
      $url = 'https://api.moysklad.ru/api/remap/1.2/entity/productfolder?offset=' . $offset;
      if (isset($startVars['category_time_limit'])) {
        $url .= "&filter=updated" . urlencode(">=" . $dateLimit);
      }
      curl_setopt($ch, CURLOPT_URL, $url);

      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      $responseCategory = curl_exec($ch);      
      $responseCategory = $this->CheckResponse($responseCategory, $ch);

      //Занесение данных Мой Склад в массивы
      foreach ($response['rows'] as $row) {
        $dataMS['skip'][$key] = false;

        // Синхронизация категорий и товаров из группы
        if (isset($startVars['from_group'])) {
          if ($this->CheckFromGroup($fromGroup, $row['pathName'], $row['name'], 'category', false))
            $dataMS['skip'][$key] = true;
        }
        if (isset($startVars['not_from_group'])) {
          if ($this->CheckFromGroup($notFromGroup, $row['pathName'], $row['name'], 'category', true))
            $dataMS['skip'][$key] = true;
        }

        $dataMS = $this->GetDataMS($row, ['name', 'pathName', 'id'], ['name', 'pathName', 'id'], $dataMS);
        if ($startVars['category_binding'] == "name") {
          $dataMS['category'][$key] = $this->GetCategoryFast($dataMS['pathName'][$key], $responseCategory);
        } else {
          if (isset($row['productFolder'])) {
            $dataMS['parent_id'][$key] = substr($row['productFolder']['meta']['href'], strrpos($row['productFolder']['meta']['href'], '/') + 1);
          } else {
            $dataMS['parent_id'][$key] = 0;
          }
        }

        $key++;
      }

      if (!$this->cron && $startVars['cat_offset'] != 0) {
        break;
      }

      $offset += 1000;
    } while (isset($response['meta']['nextHref']));

    curl_close($ch);

    $languageId = $this->GetLanguageId();

    if ($startVars['category_binding'] == "name") {
      $duplicate = $this->DuplicateCheck([$dataMS['name']], 'category');
      if ($duplicate != []) {
        foreach ($duplicate as $value) {
          foreach (array_keys($dataMS['name'], $value) as $key) {
            unset($dataMS['name'][$key], $dataMS['pathName'][$key], $dataMS['category'][$key], $dataMS['skip'][$key], $dataMS['id'][$key], $dataMS['parent_id'][$key]);
          }
        }

        $duplicate = implode("; ", $duplicate);
        $this->Output('В Моем Складе есть категории-дубликаты по полю name: ' . $duplicate, 'error_warning', false);
      }

      $query = $this->db->query("SELECT category_id, name FROM " . DB_PREFIX . "category_description WHERE `language_id` = $languageId");
      $dataSQL = $this->GetDataSQL($query, ['category_id', 'name']);

      $duplicate = $this->DuplicateCheck([$dataSQL['name']], 'category');
      if ($duplicate != []) {
        foreach ($duplicate as $value) {
          foreach (array_keys($dataSQL['name'], $value) as $key) {
            unset($dataSQL['category_id'][$key], $dataSQL['name'][$key]);
          }
        }

        $duplicate = implode("; ", $duplicate);
        $this->Output('В Опенкарт есть категории-дубликаты по полю name: ' . $duplicate, 'error_warning', false);
      }
    } else {
      // Проверка существования столбца syncms_id
      $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category` LIKE 'syncms_id'");
      if (!isset($query->row['Field'])) {
        $query = $this->db->query("ALTER TABLE " . DB_PREFIX . "category 
          ADD COLUMN syncms_id VARCHAR(255)");
      }

      $query = $this->db->query("SELECT category_id, syncms_id 
        FROM " . DB_PREFIX . "category");
      $dataSQL = $this->GetDataSQL($query, ['category_id', 'syncms_id', 'name']);
    }

    //Определение id категорий в БД 
    $dataMS['oc_id'] = [];
    $dataMS['oc_parent_id'] = [];

    if ($startVars['category_binding'] == "name") {
      foreach ($dataMS['name'] as $key => $value) {
        $keySQL = array_search($value, $dataSQL['name'], true);
        if (is_int($keySQL)) {
          array_push($dataMS['oc_id'], $dataSQL['category_id'][$keySQL]);
        } else {
          unset($dataMS['name'][$key]);
          unset($dataMS['category'][$key]);
          unset($dataMS['skip'][$key]);
        }
      }
    } else {
      foreach ($dataMS['id'] as $key => $value) {
        $keySQL = array_search($value, $dataSQL['syncms_id'], true);
        $parentKeySQL = array_search($dataMS['parent_id'][$key], $dataSQL['syncms_id'], true);
        if ($keySQL !== false) {
          array_push($dataMS['oc_id'], $dataSQL['category_id'][$keySQL]);
          if ($parentKeySQL !== false) {
            array_push($dataMS['oc_parent_id'], $dataSQL['category_id'][$parentKeySQL]);
          } else {
            array_push($dataMS['oc_parent_id'], 0);
          }
        } else {
          unset($dataMS['name'][$key], $dataMS['id'][$key], $dataMS['parent_id'][$key], $dataMS['skip'][$key]);
        }
      }
    }
    
    $dataMS['name'] = array_values($dataMS['name']);
    $dataMS['category'] = array_values($dataMS['category']);
    $dataMS['skip'] = array_values($dataMS['skip']);
    $dataMS['id'] = array_values($dataMS['id']);
    $dataMS['parent_id'] = array_values($dataMS['parent_id']);

    if ($startVars['category_binding'] == "name") {
      //Определение id родительской категории в БД
      foreach ($dataMS['category'] as $value) {
        $key = array_search($value, $dataSQL['name'], true);
        if ($key !== false) {
          array_push($dataMS['oc_parent_id'], $dataSQL['category_id'][$key]);
        } else {
          array_push($dataMS['oc_parent_id'], 0);
        }
      }
    }

    //Получение id родительских категорий в БД
    $implodeIdMS = "'" . implode("', '", $dataMS['oc_id']) . "'";
    $query = $this->db->query("SELECT parent_id, category_id, 
      " . DB_PREFIX . "category_description.meta_title AS meta_title,
      " . DB_PREFIX . "category_description.meta_description AS meta_description,
      " . DB_PREFIX . "category_description.meta_keyword AS meta_keyword
      FROM " . DB_PREFIX . "category
      INNER JOIN " . DB_PREFIX . "category_description USING (`category_id`)  
      WHERE `language_id` = $languageId AND `category_id` IN ($implodeIdMS) ORDER BY FIELD (category_id, $implodeIdMS)");
    $dataSQL = $this->GetDataSQL($query, ['parent_id', 'category_id', 'meta_title', 'meta_description', 'meta_keyword']);

    //Составление запросов
    $updateCategoryCase1 = '';
    $updateCategoryCase2 = '';
    $updateCategoryDescriptionCase1 = '';
    $updateCategoryDescriptionCase2 = '';
    $updateCategoryDescriptionCase3 = '';
    $whereDescription = [];
    $updateWhere = [];
    $dateModified = date("Y-m-d H:i:s");
    $added = [];

    foreach ($dataMS['oc_id'] as $key => $value) {
      $value1 = array_search($value, $dataSQL['category_id']);
      if (is_int($value1)) {

        if ($dataMS['skip'][$key] === true) {
          continue;
        }

        //Таблица category
        if ($dataSQL['parent_id'][$value1] != $dataMS['oc_parent_id'][$key]) {
          $updateCategoryCase1 .= sprintf("WHEN `category_id` = %s THEN '%s' ", $dataMS['oc_id'][$key], $dataMS['oc_parent_id'][$key]);
          $updateCategoryCase2 .= sprintf("WHEN `category_id` = %s THEN '%s' ", $dataMS['oc_id'][$key], $dateModified);
          array_push($updateWhere, $dataMS['oc_id'][$key]);
          array_push($added, $dataMS['name'][$key]);
        }

        if ($startVars['meta_cat_update'] == 1) {
          $metaTitleNew = $this->GetMetaTag($metaTitle, ['name'], [$dataMS['name'][$key]]);
          $metaDescNew = $this->GetMetaTag($metaDesc, ['name'], [$dataMS['name'][$key]]);
          $metaKeywordNew = $this->GetMetaTag($metaKeyword, ['name'], [$dataMS['name'][$key]]);

          $dataSQL['meta_title'][$value1] = $this->db->escape(htmlspecialchars_decode($dataSQL['meta_title'][$value1], ENT_COMPAT));
          $dataSQL['meta_description'][$value1] = $this->db->escape(htmlspecialchars_decode($dataSQL['meta_description'][$value1], ENT_COMPAT));
          $dataSQL['meta_keyword'][$value1] = $this->db->escape(htmlspecialchars_decode($dataSQL['meta_keyword'][$value1], ENT_COMPAT));

          if ($dataSQL['meta_title'][$value1] != $metaTitleNew || $dataSQL['meta_description'][$value1] != $metaDescNew || $dataSQL['meta_keyword'][$value1] != $metaKeywordNew) {
            $updateCategoryDescriptionCase1 .= sprintf("WHEN `category_id` = '%s' THEN '%s' ", $dataMS['oc_id'][$key], htmlspecialchars($metaTitleNew, ENT_COMPAT));
            $updateCategoryDescriptionCase2 .= sprintf("WHEN `category_id` = '%s' THEN '%s' ", $dataMS['oc_id'][$key], htmlspecialchars($metaDescNew, ENT_COMPAT));
            $updateCategoryDescriptionCase3 .= sprintf("WHEN `category_id` = '%s' THEN '%s' ", $dataMS['oc_id'][$key], htmlspecialchars($metaKeywordNew, ENT_COMPAT));
            
            array_push($whereDescription, $dataMS['oc_id'][$key]);
            array_push($added, $dataMS['name'][$key]);
          }
        }
      }
    }

    //Отправка запросов
    if ($updateCategoryCase1 != "") {
      $updateWhere = implode(", ", $updateWhere);
      $this->db->query("UPDATE " . DB_PREFIX . "category SET 
      parent_id = CASE " . $updateCategoryCase1 . "END,
      date_modified = CASE " . $updateCategoryCase2 . "END
      WHERE `category_id` IN ($updateWhere)");
    }

    $this->repairCategories();

    if ($updateCategoryDescriptionCase1 != '') {
      $whereDescription = implode(", ", $whereDescription);
      $this->db->query("UPDATE " . DB_PREFIX . "category_description SET 
      meta_title = CASE " . $updateCategoryDescriptionCase1 . "END,
      meta_description = CASE " . $updateCategoryDescriptionCase2 . "END,
      meta_keyword = CASE " . $updateCategoryDescriptionCase3 . "END
      WHERE `category_id` IN ($whereDescription)");
    }

    $added = array_unique($added);
    
    $this->logText .= 'Обновлено категорий: ' . count($added) . PHP_EOL;
    foreach ($added as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    
    $this->LogWrite();
    $this->Output('Успешно. Категорий обновлено: ' . count($added), 'success');
  }


  public function SyncAbsenceCategory()
  {
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }

    $this->logText = date('H:i:s d.m.Y') . ' Обновление/удаление лишних категорий' . PHP_EOL;

    $this->db->query("set session wait_timeout=28800");
    $startVars = $this->GetStartVars(['category_binding', 'from_group', 'not_from_group']);

    $languageId = $this->GetLanguageId();

    $ch = $this->CurlInit($startVars['headers']);
    
    // Определенные категории
    if (isset($startVars['from_group'])) {
      $fromGroup = $this->GetFromGroups($startVars['from_group'], "category", $ch);
    }
    if (isset($startVars['not_from_group'])) {
      $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "category", $ch);
    }

    $dataMS = $this->InitialDataMS(['name', 'id']);

    // Получение данных по curl
    $offset = 0;
    do { 
      curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/entity/productfolder?offset=' . $offset);
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      foreach ($response['rows'] as $key => $row) {

        // Синхронизация категорий и товаров из группы
        if (isset($startVars['from_group'])) {
          if ($this->CheckFromGroup($fromGroup, $row['pathName'], $row['name'], 'category', false))
            continue;
        }
        if (isset($startVars['not_from_group'])) {
          if ($this->CheckFromGroup($notFromGroup, $row['pathName'], $row['name'], 'category', true))
            continue;
        }

        $dataMS = $this->GetDataMS($row, ['id', 'name'], ['id', 'name'], $dataMS);
      }

      $offset += 1000;
    } while (isset($response['meta']['nextHref']));

    curl_close($ch);

    $deleteWhere = [];
    $absenceNum = 0;
    $deleted = [];

    if ($startVars['category_binding'] == "name") {
      $query = $this->db->query("SELECT name, category_id FROM " . DB_PREFIX . "category_description WHERE `language_id` = $languageId");
      $dataSQL = $this->GetDataSQL($query, ['name', 'category_id']);

      //Удаление категорий, которые уже есть в БД
      foreach ($dataSQL['name'] as $key => $value) {
        $find = array_search($value, $dataMS['name'], true);
        if ($find === false) {
          $deleteWhere[] = $dataSQL['category_id'][$key];
          $deleteSeoUrl[] = "category_id={$dataSQL['category_id'][$key]}";
          $deleted[] = $value;
        }
      }
    } else {
      // Проверка существования столбца syncms_id
      $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category` LIKE 'syncms_id'");
      if (!isset($query->row['Field'])) {
        $query = $this->db->query("ALTER TABLE " . DB_PREFIX . "category 
          ADD COLUMN syncms_id VARCHAR(255)");
      }

      $query = $this->db->query("SELECT syncms_id, c.category_id, name 
        FROM " . DB_PREFIX . "category c
        INNER JOIN " . DB_PREFIX . "category_description USING (`category_id`)
        WHERE `language_id` = $languageId");
      $dataSQL = $this->GetDataSQL($query, ['syncms_id', 'category_id', 'name']);

      // Удаление категорий, которые уже есть в БД
      foreach ($dataSQL['syncms_id'] as $key => $value) {
        $find = array_search($value, $dataMS['id'], true);
        if ($find === false) {
          $deleteWhere[] = $dataSQL['category_id'][$key];
          $deleteSeoUrl[] = "category_id={$dataSQL['category_id'][$key]}";
          $deleted[] = $dataSQL['name'][$key];
        }
      }
    }

    if (count($deleteWhere) != 0) {
      $deleteWhere = "'" . implode("', '", $deleteWhere) . "'";
      $deleteSeoUrl = "'" . implode("', '", $deleteSeoUrl) . "'";

      $this->db->query("DELETE " . DB_PREFIX . "category, 
       " . DB_PREFIX . "category_description,
       " . DB_PREFIX . "category_to_store,
       " . DB_PREFIX . "product_to_category,
       " . DB_PREFIX . "category_path,
       " . DB_PREFIX . "category_filter,
       " . DB_PREFIX . "category_to_layout,
       " . DB_PREFIX . "coupon_category
       FROM " . DB_PREFIX . "category
       LEFT JOIN " . DB_PREFIX . "category_description USING (`category_id`)
       LEFT JOIN " . DB_PREFIX . "category_to_store USING (`category_id`)
       LEFT JOIN " . DB_PREFIX . "product_to_category USING (`category_id`)
       LEFT JOIN " . DB_PREFIX . "category_path USING (`category_id`)
       LEFT JOIN " . DB_PREFIX . "category_filter USING (`category_id`)
       LEFT JOIN " . DB_PREFIX . "category_to_layout USING (`category_id`)
       LEFT JOIN " . DB_PREFIX . "coupon_category USING (`category_id`)
       WHERE `category_id` IN ($deleteWhere)");

      if ($this->version == '2.1' || $this->version == '2.3') {
       $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE `query` IN ($deleteSeoUrl)");
      } else {
       $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE `query` IN ($deleteSeoUrl)");
      }
    }
    
    $deleted = array_unique($deleted);
    $this->logText .= 'Удалено/обновлено категорий: ' . count($deleted) . PHP_EOL;
    foreach ($deleted as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    
    $this->LogWrite();
    $this->Output('Успешно. Удалено категорий: ' . count($deleted), 'success');
  }

  //=================Товары================


  public function ProductAdd() {
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }

    $this->logText = date('H:i:s d.m.Y') . ' Добавление товаров' . PHP_EOL;

    $this->db->query("set session wait_timeout=28800");
    $startVars = $this->GetStartVars(["binding", "sale_price", "product_offset", "binding_name", "prod_meta_title", "prod_meta_desc", "prod_meta_keyword", "meta_prod_update", "url_update", "cat_update", "desc_update", "stock_store", "sku_update", "weight_update", "weight_unity", "manufacturer_update", "manufacturer", "stock_status", "stock_status_update", "from_group", "subtract_update", "subtract", "category_binding", "client_price", "prod_parent_cat", "special_price", "ean_update", "empty_field", "add_out_of_stock", "not_from_group", "certain_products", "product_time_limit"]);

    // Измененные за последние минуты
    if (isset($startVars['product_time_limit'])) {
      $minuteLimit = $startVars['product_time_limit'];

      if (strpos($minuteLimit, '.') !== false || $minuteLimit < 0) {
        $this->Output('Ошибка! Количество минут должно быть целым неотрицательным числом', 'error_warning');
        exit();
      }

      $dateLimit = new DateTime();
      $dateLimit->modify("- $minuteLimit minutes");
      $dateLimit = $dateLimit->format("Y-m-d H:i:s");
    }
    
    if (!isset($startVars['manufacturer'])) {
      $startVars['manufacturer'] = '';
    }

    if ($startVars['meta_prod_update'] == 1) {
      $metaTitle = (isset($startVars['prod_meta_title']) ? $this->db->escape($startVars['prod_meta_title']) : '');
      $metaDesc = (isset($startVars['prod_meta_desc']) ? $this->db->escape($startVars['prod_meta_desc']) : '');
      $metaKeyword = (isset($startVars['prod_meta_keyword']) ? $this->db->escape($startVars['prod_meta_keyword']) : '');
    }

    $languageId = $this->GetLanguageId();

    $dataMS = $this->InitialDataMS(['binding', 'name', 'id', 'description', 'quantity', 'pathName', 'price', 'category', 'sku', 'weight', 'manufacturer', 'manufacturer_id', 'client_price', 'categories', 'special_price', 'ean']);

    $ch = $this->CurlInit($startVars['headers']);

    // Определенные категории
    if (isset($startVars['from_group'])) {
      $fromGroup = $this->GetFromGroups($startVars['from_group'], "product", $ch);
      if (isset($startVars['not_from_group'])) {
        $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "category", $ch);
      }
    } else {
      if (isset($startVars['not_from_group'])) {
        $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "product", $ch);
      }
    }

    $bindingOC = substr($startVars['binding'], 0, strpos($startVars['binding'], '_'));
    $bindingMS = substr($startVars['binding'], strpos($startVars['binding'], '_') + 1);

    // Отдельные цены
    if (isset($startVars['client_price'])) {
      $query = $this->db->query("SELECT customer_group_id, name 
        FROM " . DB_PREFIX . "customer_group_description
        WHERE `language_id` = {$languageId}");
      $customers = $this->GetDataSQL($query, ['customer_group_id', 'name']);

      $clientPrices = [];
      foreach (explode(";", $startVars['client_price']) as $value) {
        $explode = explode(" - ", $value);
        $customerKey = array_search(trim($explode[0]), $customers['name'], true);
        if ($customerKey !== false) {
          $clientPrices[trim($explode[1])][] = $customers['customer_group_id'][$customerKey];
        }
      }
    }

    // Цены по акции
    if (isset($startVars['special_price'])) {
      $query = $this->db->query("SELECT customer_group_id, name 
        FROM " . DB_PREFIX . "customer_group_description
        WHERE `language_id` = {$languageId}");
      $customers = $this->GetDataSQL($query, ['customer_group_id', 'name']);

      $specialPrices = [];
      foreach (explode(";", $startVars['special_price']) as $value) {
        $explode = explode(" - ", $value);
        $customerKey = array_search(trim($explode[0]), $customers['name'], true);
        if ($customerKey !== false) {
          $specialPrices[trim($explode[1])][] = $customers['customer_group_id'][$customerKey];
        }
      }
    }

    //Получение категорий по curl
    curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/entity/productfolder');
    $responseCategory = curl_exec($ch);
    $responseCategory = $this->CheckResponse($responseCategory, $ch);

    // Добалять товары в родительские категории
    if ($startVars['prod_parent_cat'] == 1) {
      $categoryMS = $this->InitialDataMS(['name', 'id', 'parent_name', 'parent_id']);
      $offset = 0;
      $key = 0;
      do {
        //Получение категорий по curl
        curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/entity/productfolder?offset=' . $offset . '&limit=100&expand=productFolder');

        $response = json_decode(curl_exec($ch), true);
        $response = $this->CheckResponse($response, $ch);

        //Занесение данных в массивы
        foreach ($response['rows'] as $row) {
          $categoryMS = $this->GetDataMS($row, ['name', 'id'], ['name', 'id'], $categoryMS);
          if (isset($row['productFolder'])) {
            $categoryMS['parent_name'][$key] = $this->db->escape($row["productFolder"]['name']);
            $categoryMS['parent_id'][$key] = $row["productFolder"]['id'];
          } else {
            $categoryMS['parent_name'][$key] = "";
            $categoryMS['parent_id'][$key] = "";
          }

          $key++;
        }

        $offset += 100;
      } while (isset($response['meta']['nextHref']));
    }

    // Определенные товары
    $certainProductsHref = "";
    if (isset($startVars['certain_products'])) {
      curl_setopt($ch, CURLOPT_URL, "https://api.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes");
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);
      foreach ($response['rows'] as $key => $value) {
        if ($value['name'] == $startVars['certain_products'] && $value['type'] == 'boolean') {
          $certainProductsHref = $value['meta']['href'];
          break;
        }
      }
    }

    if ($this->cron || $startVars['product_offset'] == 0) {
      $offset = 0;
    } else {
      $offset = $startVars['product_offset'] - 1;
    }
    $key = 0;
    do {
      if (!isset($startVars['stock_store'])) {
        $url = 'https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=product;type=bundle;type=consignment&offset=' . $offset;
      } else {
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=product;type=bundle;type=consignment&offset=" . $offset . "&filter=";
        foreach ($startVars['stock_store'] as $storeUrl) {
          $url .= "stockStore=" . $storeUrl . ";";
        }
      }

      // Определенные группы
      if (isset($startVars['from_group'])) {
        if (empty($fromGroup)) {
          continue;
        }
        $url .= '&filter=productFolder=' . implode(";productFolder=", $fromGroup);
      }

      // Определенные группы
      if (isset($startVars['not_from_group']) && !isset($startVars['from_group'])) {
        if (!empty($notFromGroup))
          $url .= '&filter=productFolder!=' . implode(";productFolder!=", $notFromGroup);
      }

      // Игнорировать товары с незаполненным
      if ($startVars['empty_field'] != 1) {
        $url .= "&filter={$bindingMS}!=";
      }

      // Определенные товары
      if ($certainProductsHref != '') {
        $url .= "&filter={$certainProductsHref}=true";
      }

      if (isset($startVars['product_time_limit'])) {
        $url .= "&filter=updated" . urlencode(">=" . $dateLimit);
      }

      curl_setopt($ch, CURLOPT_URL, $url);

      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      //Занесение данных Мой Склад в массивы
      foreach ($response['rows'] as $row) {
        // Определенные группы
        if (isset($startVars['not_from_group']) && isset($startVars['from_group'])) {
          if ($this->CheckFromGroup($notFromGroup, $row['pathName'], $row['name'], 'product', true)) {
            continue;
          }
        }

        $dataMS = $this->GetDataMS($row, [$bindingMS, 'name', 'id', 'description', 'quantity', 'pathName', 'article', 'weight'], ['binding', 'name', 'id', 'description', 'quantity', 'pathName', 'sku', 'weight'], $dataMS);

        if (isset($row['components'])) {
          $dataMS['quantity'][$key] = $this->db->escape($row['components']['meta']['href']);
        }

        if (isset($row['salePrices'][$startVars['sale_price']])) {
          $dataMS = $this->GetDataMS($row['salePrices'][$startVars['sale_price']], ['value'], ['price'], $dataMS);
        } else {
          $dataMS = $this->GetDataMS($row['salePrices'][0], ['value'], ['price'], $dataMS);
        }

        if ($startVars['category_binding'] == "name") {
          $dataMS['category'][$key] = $this->GetCategoryFast($dataMS['pathName'][$key], $responseCategory);
        } else {
          if (isset($row['productFolder'])) {
            $dataMS['category'][$key] = substr($row['productFolder']['meta']['href'], strrpos($row['productFolder']['meta']['href'], '/') + 1);
          } else {
            $dataMS['category'][$key] = null;
          }
        }

        // Добавление товара в родительские категории
        if ($startVars['prod_parent_cat'] == 1) {
          $dataMS['categories'][$key][] = $dataMS['category'][$key];
          if ($startVars['category_binding'] == "name") {
            $catKey = array_search($dataMS['category'][$key], $categoryMS["name"], true);
            while ($catKey !== false && $categoryMS['parent_name'][$catKey] != '') {
              $dataMS['categories'][$key][] = $categoryMS['parent_name'][$catKey];
              $catKey = array_search($categoryMS['parent_name'][$catKey], $categoryMS['name'], true);
            }
          } else {
            $catKey = array_search($dataMS['category'][$key], $categoryMS["id"], true);
            while ($catKey !== false && $categoryMS['parent_id'][$catKey] != '') {
              $dataMS['categories'][$key][] = $categoryMS['parent_id'][$catKey];
              $catKey = array_search($categoryMS['parent_id'][$catKey], $categoryMS['id'], true);
            }
          }
        }

        // Производители
        if (isset($startVars['manufacturer_update'])) {
          $dataMS['manufacturer'][$key] = '';
          if (isset($row['attributes'])) {
            foreach ($row['attributes'] as $key1 => $value1) {
              if ($value1['name'] == $startVars['manufacturer']) {
                if ($value1['type'] == "string" || $value1['type'] == "text") {
                  $dataMS['manufacturer'][$key] = $this->db->escape($value1['value']);
                  break;
                } elseif ($value1['type'] == "customentity" || 
                          $value1['type'] == "counterparty") {
                  $dataMS['manufacturer'][$key] = $this->db->escape($value1['value']['name']);
                  break;
                }
              }
            }
          }
        }

        // Отдельные цены
        if (isset($startVars['client_price'])) {
          $prices = [];
          foreach ($row['salePrices'] as $salePrice) {
            if (isset($clientPrices[$salePrice['priceType']['name']]) && $salePrice['value'] != 0) {
              foreach ($clientPrices[$salePrice['priceType']['name']] as $clientId) {
                $prices[$clientId] = $salePrice['value'] / 100;
              }
            }
          }
          if (count($prices) != 0)
            $dataMS['client_price'][] = $prices;
          else
            $dataMS['client_price'][] = "";
        }

        // Цены по акции
        if (isset($startVars['special_price'])) {
          $prices = [];
          foreach ($row['salePrices'] as $salePrice) {
            if (isset($specialPrices[$salePrice['priceType']['name']]) && $salePrice['value'] != 0) {
              foreach ($specialPrices[$salePrice['priceType']['name']] as $clientId) {
                $prices[$clientId] = $salePrice['value'] / 100;
              }
            }
          }
          if (count($prices) != 0)
            $dataMS['special_price'][] = $prices;
          else
            $dataMS['special_price'][] = "";
        }

        // EAN
        if (isset($startVars['ean_update'])) {
          $dataMS['ean'][$key] = '';
          if (isset($row['barcodes'])) {
            foreach ($row['barcodes'] as $barcodeValue) {
              if (isset($barcodeValue['ean13'])) {
                $dataMS['ean'][$key] = $barcodeValue['ean13'];
              }
            }
          }
        }

        $key++;
      }

      if (!$this->cron && $startVars['product_offset'] != 0) {
        break;
      } 

      $offset += 1000;
    } while (isset($response['meta']['nextHref']));

    // Удаление товаров, которые уже есть в БД
    $bindingMSImplode = htmlspecialchars("'" . implode("', '", $dataMS['binding']) . "'", ENT_COMPAT);
    $query = $this->db->query("SELECT name, $bindingOC FROM " . DB_PREFIX . "product 
      INNER JOIN " . DB_PREFIX . "product_description USING (`product_id`) 
      WHERE `$bindingOC` IN ($bindingMSImplode)");

    $dataSQL = $this->GetDataSQL($query, ['name', 'binding']);

    if ($startVars['binding_name'] == 1) {
      // Модель + наименование
      foreach ($dataMS['binding'] as $key => $value) {
        foreach (array_keys($dataSQL['binding'], $value) as $value1) {
           if ($dataMS['name'][$key] == $dataSQL['name'][$value1]) {
            unset($dataMS['id'][$key], $dataMS['binding'][$key], $dataMS['name'][$key], $dataMS['description'][$key], $dataMS['quantity'][$key], $dataMS['price'][$key], $dataMS['category'][$key], $dataMS['sku'][$key], $dataMS['weight'][$key], $dataMS['manufacturer'][$key], $dataMS['client_price'][$key], $dataMS['categories'][$key], $dataMS['special_price'][$key], $dataMS['ean'][$key]);
            break;
           }
        } 
      }
    } else {
      // Только модель
      foreach ($dataMS['binding'] as $key => $value) {
        if (in_array($value, $dataSQL['binding'], true)) {
          unset($dataMS['id'][$key], $dataMS['binding'][$key], $dataMS['name'][$key], $dataMS['description'][$key], 
              $dataMS['quantity'][$key], $dataMS['price'][$key], $dataMS['category'][$key], $dataMS['sku'][$key], $dataMS['weight'][$key], $dataMS['manufacturer'][$key], $dataMS['client_price'][$key], $dataMS['categories'][$key], $dataMS['special_price'][$key], $dataMS['ean'][$key]);
        }
      }
    }
    
    $dataMS['id'] = array_values($dataMS['id']);
    $dataMS['binding'] = array_values($dataMS['binding']);
    $dataMS['name'] = array_values($dataMS['name']);
    $dataMS['description'] = array_values($dataMS['description']);
    $dataMS['quantity'] = array_values($dataMS['quantity']);
    $dataMS['price'] = array_values($dataMS['price']);
    $dataMS['category'] = array_values($dataMS['category']);
    $dataMS['sku'] = array_values($dataMS['sku']);
    $dataMS['weight'] = array_values($dataMS['weight']);
    $dataMS['manufacturer'] = array_values($dataMS['manufacturer']);
    $dataMS['client_price'] = array_values($dataMS['client_price']);
    $dataMS['categories'] = array_values($dataMS['categories']);
    $dataMS['special_price'] = array_values($dataMS['special_price']);
    $dataMS['ean'] = array_values($dataMS['ean']);

    $dataMS = $this->GetBundleQuantity($dataMS, $ch);
    curl_close($ch);
    
    if ($startVars["category_binding"] == "name") {
      // Получение всех имен категорий в БД
      $query = $this->db->query("SELECT category_id, name  FROM " . DB_PREFIX . "category_description");
      $dataSQL = $this->GetDataSQL($query, ['category_id', 'name']);

      //Определение id категорий в БД
      foreach ($dataMS['category'] as $key => $value) {
        $sqlKey = array_search($value, $dataSQL['name'], true);
        if (is_int($sqlKey)) {
          $dataMS['oc_category_id'][$key] = $dataSQL['category_id'][$sqlKey];
        } else {
          $dataMS['oc_category_id'][$key] = '';
        }

        if ($startVars['prod_parent_cat'] == 1) {
          $dataMS['categories_id'][$key] = [];
          if ($dataMS['categories'][$key] != []) {
            foreach ($dataMS['categories'][$key] as $catName) {
              $sqlKey = array_search($catName, $dataSQL['name'], true);
              if (is_int($sqlKey)) {
                $dataMS['categories_id'][$key][] = $dataSQL['category_id'][$sqlKey];
              }
            }
          }
        }
      }
    } else {
      // Проверка существования столбца syncms_id
      $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category` LIKE 'syncms_id'");
      if (!isset($query->row['Field'])) {
        $query = $this->db->query("ALTER TABLE " . DB_PREFIX . "category 
          ADD COLUMN syncms_id VARCHAR(255)");
      }

      // Получение всех имен категорий в БД
      $query = $this->db->query("SELECT category_id, syncms_id  FROM " . DB_PREFIX . "category");
      $dataSQL = $this->GetDataSQL($query, ['category_id', 'syncms_id']);

      //Определение id категорий в БД
      foreach ($dataMS['category'] as $key => $value) {
        $sqlKey = array_search($value, $dataSQL['syncms_id'], true);
        if (is_int($sqlKey)) {
          $dataMS['oc_category_id'][$key] = $dataSQL['category_id'][$sqlKey];
        } else {
          $dataMS['oc_category_id'][$key] = '';
        }

        if ($startVars['prod_parent_cat'] == 1) {
          $dataMS['categories_id'][$key] = [];
          if ($dataMS['categories'][$key] != []) {
            foreach ($dataMS['categories'][$key] as $catId) {
              $sqlKey = array_search($catId, $dataSQL['syncms_id'], true);
              if (is_int($sqlKey)) {
                $dataMS['categories_id'][$key][] = $dataSQL['category_id'][$sqlKey];
              }
            }
          }
        }
      }
    }

    // Производители
    $query = $this->db->query("SELECT manufacturer_id, name  FROM " . DB_PREFIX . "manufacturer");
    $manufacturerDataSQL = $this->GetDataSQL($query, ['manufacturer_id', 'name']);

    // Формирование запросов
    $query = $this->db->query("SELECT MAX(product_id) FROM " . DB_PREFIX . "product");
    $lastId = $query->row['MAX(product_id)'];
    $date_added = date('Y-m-d H:i:s');

    if (isset($startVars['stock_status_update'])) {
      $stockStatusId = $startVars['stock_status'];
    } else {
      $stockStatusId = 0;
    }
    if (isset($startVars['subtract_update'])) {
      $subtract = $startVars['subtract'];
    } else {
      $subtract = 1;
    }

    $insertProduct = [];
    $insertProductDescription = [];
    $insertProductToCategory = [];
    $insertProductToStore = [];
    $insertSeoUrl = [];
    $added = [];
    $manufacturerAdded = ['id' => [], 'name' => []];
    $insertManufacturer = [];
    $insertManufacturerToStore = [];
    $insertManufacturerURL = [];

    // Отдельные цены
    $insertDiscount = [];
    $deleteDiscount = [];

    // Цены по акции
    $insertSpecial = [];
    $deleteSpecial = [];

    // Проверка существования таблицы manufacturer_description
    if (isset($startVars['manufacturer_update'])) {
      $query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "` LIKE '" . DB_PREFIX . "manufacturer_description'");
      if (count($query->rows) != 0) {
        $ocstore = true;
        $insertManufacturerDesc = [];
      } else {
        $ocstore = false;
      }
    }

    if (!isset($startVars['weight_update'])) {
      $startVars['weight_unity'] = 0;
    }

    if (count($manufacturerDataSQL['manufacturer_id']) == 0) {
      $lastManufacturerId = 0;
    } else {
      $lastManufacturerId = max($manufacturerDataSQL['manufacturer_id']);
    }

    // Проверка существования столбца main_category
    $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product_to_category` LIKE 'main_category'");
    if (isset($query->row['Field'])) {
      $mainCategory = true;
    } else {
      $mainCategory = false;
    }

    // URL
    if ($this->version == '2.1' || $this->version == "2.3") {
      $query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE `query` LIKE '%product_id=%'");
    } else {
      $query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE `query` LIKE '%product_id=%'");
    }

    $keywords = $this->GetDataSQL($query, ['keyword'])["keyword"];

    foreach ($dataMS['binding'] as $key => $value) {

      if ($startVars['add_out_of_stock'] == 1 && $dataMS['quantity'][$key] <= 0)
        continue;

      $lastId++;

      $keyword = $this->Translit($dataMS['name'][$key]);

      if (in_array($keyword, $keywords, true)) {
        $keyword .= "-" . $lastId;
      } else {
        array_push($keywords, $keyword);
      }

      if (!isset($startVars['sku_update'])) {
        $dataMS['sku'][$key] = '';
      }
      if (!isset($startVars['ean_update'])) {
        $dataMS['ean'][$key] = '';
      }
      if (!isset($startVars['weight_update'])) {
        $dataMS['weight'][$key] = 0;
      }

      // Производители
      if (isset($startVars['manufacturer_update']) && $dataMS['manufacturer'][$key] != '') {
        $manufacturerKey = array_search($dataMS['manufacturer'][$key], $manufacturerDataSQL['name'], true);
        $manufacturerAddedKey = array_search($dataMS['manufacturer'][$key], $manufacturerAdded['name'], true);
        if ($manufacturerKey !== false) {
          $dataMS['manufacturer_id'][$key] = $manufacturerDataSQL['manufacturer_id'][$manufacturerKey];
        } else {
          if ($manufacturerAddedKey !== false) {
            $dataMS['manufacturer_id'][$key] = $manufacturerAdded['id'][$manufacturerAddedKey];
          } else {
            $lastManufacturerId++;
            $dataMS['manufacturer_id'][$key] = $lastManufacturerId;
            array_push($manufacturerAdded['id'], $lastManufacturerId);
            array_push($manufacturerAdded['name'], $dataMS['manufacturer'][$key]);
            array_push($insertManufacturer, sprintf("('%s', '%s', '0')", $lastManufacturerId, $dataMS['manufacturer'][$key]));
            array_push($insertManufacturerToStore, sprintf("('%s', '0')", $lastManufacturerId));
            $manufacturerKeyword = $this->Translit($dataMS['manufacturer'][$key]);

            if ($this->version == '2.1' || $this->version == '2.3') {
              array_push($insertManufacturerURL, "('manufacturer_id=$lastManufacturerId', '$manufacturerKeyword')");
            } else {
              array_push($insertManufacturerURL, "('0', '$languageId', 'manufacturer_id=$lastManufacturerId', '$manufacturerKeyword')");
            }
            
            if ($ocstore) {
              if ($this->version == '2.1' || $this->version == '2.3') {
                array_push($insertManufacturerDesc, sprintf("('%s', '%s', '%s')", $lastManufacturerId, $languageId, $dataMS['manufacturer'][$key]));
              } else {
                array_push($insertManufacturerDesc, sprintf("('%s', '%s')", $lastManufacturerId, $languageId));
              }
            }
          }
        }
      } else {
        $dataMS['manufacturer_id'][$key] = 0;
      }

      // Товары
      if ($bindingOC != 'sku') {
        array_push($insertProduct, sprintf("('%s', '%s', '%s', '%s', '1', '%s', '%s', '%s', '%s', '%s', '%s', '$stockStatusId', '$subtract', '%s')", $lastId, htmlspecialchars($value, ENT_COMPAT), $dataMS['quantity'][$key], $dataMS['price'][$key], $date_added, $date_added, $dataMS['sku'][$key], $dataMS['weight'][$key], $startVars['weight_unity'], $dataMS['manufacturer_id'][$key], $dataMS['ean'][$key]));
      } else {
        array_push($insertProduct, sprintf("('%s', '%s', '%s', '%s', '1', '%s', '%s', '%s', '%s', '%s', '$stockStatusId', '$subtract', '%s')", $lastId, htmlspecialchars($value, ENT_COMPAT), $dataMS['quantity'][$key], $dataMS['price'][$key], $date_added, $date_added, $dataMS['weight'][$key], $startVars['weight_unity'], $dataMS['manufacturer_id'][$key], $dataMS['ean'][$key]));
      }
      
      if ($startVars['meta_prod_update'] == 1) {
        $metaTitleNew = $this->GetMetaTag($metaTitle, ['name', 'price'], [$dataMS['name'][$key], $dataMS['price'][$key]]);
        $metaDescNew = $this->GetMetaTag($metaDesc, ['name', 'price'], [$dataMS['name'][$key], $dataMS['price'][$key]]);
        $metaKeywordNew = $this->GetMetaTag($metaKeyword, ['name', 'price'], [$dataMS['name'][$key], $dataMS['price'][$key]]);
      } else {
        $metaTitleNew = "";
        $metaDescNew = "";
        $metaKeywordNew = "";
      }

      if (!isset($startVars['desc_update'])) {
        $dataMS['description'][$key] = '';
      }

      array_push($insertProductDescription, sprintf("('%s', '%s', '%s', '%s', '%s', '%s', '%s')", $lastId, $languageId, htmlspecialchars($dataMS['name'][$key], ENT_COMPAT), htmlspecialchars($dataMS['description'][$key], ENT_COMPAT), htmlspecialchars($metaTitleNew, ENT_COMPAT), htmlspecialchars($metaDescNew, ENT_COMPAT), htmlspecialchars($metaKeywordNew, ENT_COMPAT)));

      if (isset($startVars['cat_update'])) {
        if ($startVars['prod_parent_cat'] == 1) {
          foreach ($dataMS['categories_id'][$key] as $catId) {
            if ($catId == $dataMS['oc_category_id'][$key]) {
              if ($mainCategory) {
                array_push($insertProductToCategory, sprintf("('%s', '%s', '1')", $lastId, $catId));
              } else {
                array_push($insertProductToCategory, sprintf("('%s', '%s')", $lastId, $catId));
              } 
            } else {
              if ($mainCategory) {
                array_push($insertProductToCategory, sprintf("('%s', '%s', '0')", $lastId, $catId));
              } else {
                array_push($insertProductToCategory, sprintf("('%s', '%s')", $lastId, $catId));
              } 
            }
          }
        } else {
          if ($mainCategory) {
            array_push($insertProductToCategory, sprintf("('%s', '%s', '1')", $lastId, $dataMS['oc_category_id'][$key]));
          } else {
            array_push($insertProductToCategory, sprintf("('%s', '%s')", $lastId, $dataMS['oc_category_id'][$key]));
          }
        }
      } 

      array_push($insertProductToStore, "('$lastId')");
      if ($startVars["url_update"] == 1) {
        if ($this->version == '2.1' || $this->version == "2.3") {
          array_push($insertSeoUrl, "('product_id=$lastId', '$keyword')");
        } else {
          array_push($insertSeoUrl, "('0', '$languageId', 'product_id=$lastId', '$keyword')");
        }
      }

      // Отдельные цены
      if (isset($startVars['client_price'])) {
        if ($dataMS['client_price'][$key] != '') {
          foreach ($dataMS['client_price'][$key] as $priceKey => $priceValue) {
            $insertDiscount[] = sprintf("('%s', '%s', '%s', '1')", $lastId, $priceKey, $priceValue);
            $deleteDiscount[] = $lastId;
          }
        }
      }

      // Цены по акции
      if (isset($startVars['special_price'])) {
        if ($dataMS['special_price'][$key] != '') {
          foreach ($dataMS['special_price'][$key] as $priceKey => $priceValue) {
            $insertSpecial[] = sprintf("('%s', '%s', '%s')", $lastId, $priceKey, $priceValue);
            $deleteSpecial[] = $lastId;
          }
        }
      }

      array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
    }


    //Отправление запросов
    $productAddedNum = count($insertProduct);
    $insertProduct = implode(', ', $insertProduct);
    $insertProductDescription = implode(', ', $insertProductDescription);
    $insertProductToStore = implode(', ', $insertProductToStore);
    $insertProductToCategory = implode(', ', $insertProductToCategory);
    $insertSeoUrl = implode(', ', $insertSeoUrl);

    if (count($insertManufacturer) != 0) {
      $insertManufacturer = implode(", ", $insertManufacturer);
      $insertManufacturerToStore = implode(", ", $insertManufacturerToStore);
      $insertManufacturerURL = implode(", ", $insertManufacturerURL);
      $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer (`manufacturer_id`, `name`, `sort_order`) VALUES $insertManufacturer");
      $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store (`manufacturer_id`, `store_id`) VALUES $insertManufacturerToStore");

      if ($this->version == '2.1' || $this->version == '2.3') {
        $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias (`query`, `keyword`) VALUES $insertManufacturerURL");
      } else {
        $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url (`store_id`, `language_id`, `query`, `keyword`) VALUES $insertManufacturerURL");
      }
      
      if ($ocstore) {
        $insertManufacturerDesc = implode(", ", $insertManufacturerDesc);
        if ($this->version == '2.1' || $this->version == '2.3') {
          $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_description (`manufacturer_id`, `language_id`, `name`) VALUES $insertManufacturerDesc");
        } else {
          $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_description (`manufacturer_id`, `language_id`) VALUES $insertManufacturerDesc");
        }
      }
    }

    if ($insertProduct != "") {
      if ($bindingOC != 'sku') {
        $this->db->query("INSERT INTO " . DB_PREFIX . "product (`product_id`, `$bindingOC`, `quantity`, `price`, `status`, `date_added`, `date_modified`, `sku`, `weight`, `weight_class_id`, `manufacturer_id`, `stock_status_id`, `subtract`, `ean`) VALUES $insertProduct");
      } else {
        $this->db->query("INSERT INTO " . DB_PREFIX . "product (`product_id`, `$bindingOC`, `quantity`, `price`, `status`, `date_added`, `date_modified`, `weight`, `weight_class_id`, `manufacturer_id`, `stock_status_id`, `subtract`, `ean`) VALUES $insertProduct");
      }
      
      $this->db->query("INSERT INTO " . DB_PREFIX . "product_description (`product_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES $insertProductDescription");
      $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store (`product_id`) VALUES $insertProductToStore");
      if (isset($startVars['cat_update']) && $insertProductToCategory != '') {
        if ($mainCategory) {
          $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category (`product_id`, `category_id`, `main_category`) VALUES $insertProductToCategory");
        } else {
          $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category (`product_id`, `category_id`) VALUES $insertProductToCategory");
        }
      }
      if ($startVars["url_update"] == 1) {
        if ($this->version == '2.1' || $this->version == "2.3") {
          $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias (`query`, `keyword`) VALUES $insertSeoUrl");
        } else {
          $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url (`store_id`, `language_id`, `query`, `keyword`) VALUES $insertSeoUrl");
        }    
      }
    }
    
    // Отдельные цены
    if (isset($startVars['client_price'])) {
      if (count($insertDiscount) != 0) {
        $insertDiscount = implode(", ", $insertDiscount);
        $deleteDiscount = "'" . implode("', '", $deleteDiscount) . "'";
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id IN ($deleteDiscount)");
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount (`product_id`, `customer_group_id`, `price`, `quantity`) VALUES $insertDiscount");
      }
    }

    // Цены по акции
    if (isset($startVars['special_price'])) {
      if (count($insertSpecial) != 0) {
        $insertSpecial = implode(", ", $insertSpecial);
        $deleteSpecial = "'" . implode("', '", $deleteSpecial) . "'";
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id IN ($deleteSpecial)");
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_special (`product_id`, `customer_group_id`, `price`) VALUES $insertSpecial");
      }
    }

    $added = array_unique($added);
    
    $this->logText .= 'Добавлено товаров: ' . $productAddedNum . PHP_EOL;
    foreach ($added as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    
    $this->LogWrite();
    $this->Output('Успешно. Товаров добавлено: ' . $productAddedNum, 'success');
  }


  public function ProductUpdate() {
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }

    $this->logText = date('H:i:s d.m.Y') . ' Обновление товаров' . PHP_EOL;
    $this->db->query("set session wait_timeout=28800");
    $startVars = $this->GetStartVars(["binding", "sale_price", "product_offset", "binding_name", "prod_meta_title", "prod_meta_desc", "prod_meta_keyword", "meta_prod_update", "desc_update", "name_update", "cat_update", "sku_update", "weight_update", "weight_unity", "manufacturer_update", "manufacturer", "stock_status", "stock_status_update", "from_group", "subtract_update", "subtract", "category_binding", "prod_parent_cat", "ean_update", "empty_field", "not_from_group", "product_time_limit"]);

    // Измененные за последние минуты
    if (isset($startVars['product_time_limit'])) {
      $minuteLimit = $startVars['product_time_limit'];

      if (strpos($minuteLimit, '.') !== false || $minuteLimit < 0) {
        $this->Output('Ошибка! Количество минут должно быть целым неотрицательным числом', 'error_warning');
        exit();
      }

      $dateLimit = new DateTime();
      $dateLimit->modify("- $minuteLimit minutes");
      $dateLimit = $dateLimit->format("Y-m-d H:i:s");
    }

    if (!isset($startVars['manufacturer'])) {
      $startVars['manufacturer'] = '';
    }
    
    $bindingOC = substr($startVars['binding'], 0, strpos($startVars['binding'], '_'));
    $bindingMS = substr($startVars['binding'], strpos($startVars['binding'], '_') + 1);

    $languageId = $this->GetLanguageId();

    $dataMS = $this->InitialDataMS(['binding', 'name', 'description', 'pathName', 'price', 'category', 'sku', 'weight', 'manufacturer', 'manufacturer_id', 'product_id', 'category_id', 'categories', 'ean']);

    if ($startVars['meta_prod_update'] == 1) {
      $metaTitle = (isset($startVars['prod_meta_title']) ? $this->db->escape($startVars['prod_meta_title']) : '');
      $metaDesc = (isset($startVars['prod_meta_desc']) ? $this->db->escape($startVars['prod_meta_desc']) : '');
      $metaKeyword = (isset($startVars['prod_meta_keyword']) ? $this->db->escape($startVars['prod_meta_keyword']) : '');
    }

    $ch = $this->CurlInit($startVars['headers']);

    // Определенные категории
    if (isset($startVars['from_group'])) {
      $fromGroup = $this->GetFromGroups($startVars['from_group'], "product", $ch);
      if (isset($startVars['not_from_group'])) {
        $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "category", $ch);
      }
    } else {
      if (isset($startVars['not_from_group'])) {
        $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "product", $ch);
      }
    }

    // Добалять товары в родительские категории
    if ($startVars['prod_parent_cat'] == 1) {
      $categoryMS = $this->InitialDataMS(['name', 'id', 'parent_name', 'parent_id']);
      $offset = 0;
      $key = 0;
      do {
        //Получение категорий по curl
        curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/entity/productfolder?offset=' . $offset . '&limit=100&expand=productFolder');

        $response = json_decode(curl_exec($ch), true);
        $response = $this->CheckResponse($response, $ch);

        //Занесение данных в массивы
        foreach ($response['rows'] as $row) {
          $categoryMS = $this->GetDataMS($row, ['name', 'id'], ['name', 'id'], $categoryMS);
          if (isset($row['productFolder'])) {
            $categoryMS['parent_name'][$key] = $this->db->escape($row["productFolder"]['name']);
            $categoryMS['parent_id'][$key] = $row["productFolder"]['id'];
          } else {
            $categoryMS['parent_name'][$key] = "";
            $categoryMS['parent_id'][$key] = "";
          }

          $key++;
        }

        $offset += 100;
      } while (isset($response['meta']['nextHref']));
    }

    if ($this->cron || $startVars['product_offset'] == 0) {
      $offset = 0;
    } else {
      $offset = $startVars['product_offset'] - 1;
    }

    $key = 0;
    do {
      //Получение товаров по curl
      $url = 'https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=product;type=bundle&offset=' . $offset;
      
      // Определенные группы
      if (isset($startVars['from_group'])) {
        if (empty($fromGroup)) {
          continue;
        }
        $url .= '&filter=productFolder=' . implode(";productFolder=", $fromGroup);
      }

      // Определенные группы
      if (isset($startVars['not_from_group']) && !isset($startVars['from_group'])) {
        if (!empty($notFromGroup))
          $url .= '&filter=productFolder!=' . implode(";productFolder!=", $notFromGroup);
      }

      // Игнорировать товары с незаполненным
      if ($startVars['empty_field'] != 1) {
        $url .= "&filter={$bindingMS}!=";
      }

      if (isset($startVars['product_time_limit'])) {
        $url .= "&filter=updated" . urlencode(">=" . $dateLimit);
      }

      curl_setopt($ch, CURLOPT_URL, $url);

      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      //Получение категорий по curl
      curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/entity/productfolder');
      $responseCategory = curl_exec($ch);
      $responseCategory = $this->CheckResponse($responseCategory, $ch);
      
      //Занесение данных Мой Склад в массивы
      foreach ($response['rows'] as $row) {
        // Определенные группы
        if (isset($startVars['not_from_group']) && isset($startVars['from_group'])) {
          if ($this->CheckFromGroup($notFromGroup, $row['pathName'], $row['name'], 'product', true)) {
            continue;
          }
        }

        $dataMS = $this->GetDataMS($row, [$bindingMS, 'name', 'description', 'pathName', 'article', 'weight'], ['binding', 'name', 'description', 'pathName', 'sku', 'weight'], $dataMS);
        if (isset($row['salePrices'][$startVars['sale_price']])) {
          $dataMS = $this->GetDataMS($row['salePrices'][$startVars['sale_price']], ['value'], ['price'], $dataMS);
        } else {
          $dataMS = $this->GetDataMS($row['salePrices'][0], ['value'], ['price'], $dataMS);
        }

        if ($startVars["category_binding"] == "name") {
          $dataMS['category'][$key] = $this->GetCategoryFast($dataMS['pathName'][$key], $responseCategory);
        } else {
          if (isset($row['productFolder'])) {
            $dataMS['category'][$key] = substr($row['productFolder']['meta']['href'], strrpos($row['productFolder']['meta']['href'], '/') + 1);
          } else {
            $dataMS['category'][$key] = null;
          }
        }

        // Добавление товара в родительские категории
        if ($startVars['prod_parent_cat'] == 1) {
          $dataMS['categories'][$key][] = $dataMS['category'][$key];
          if ($startVars['category_binding'] == "name") {
            $catKey = array_search($dataMS['category'][$key], $categoryMS["name"], true);
            while ($catKey !== false && $categoryMS['parent_name'][$catKey] != '') {
              $dataMS['categories'][$key][] = $categoryMS['parent_name'][$catKey];
              $catKey = array_search($categoryMS['parent_name'][$catKey], $categoryMS['name'], true);
            }
          } else {
            $catKey = array_search($dataMS['category'][$key], $categoryMS["id"], true);
            while ($catKey !== false && $categoryMS['parent_id'][$catKey] != '') {
              $dataMS['categories'][$key][] = $categoryMS['parent_id'][$catKey];
              $catKey = array_search($categoryMS['parent_id'][$catKey], $categoryMS['id'], true);
            }
          }
        }

        // Производители
        if (isset($startVars['manufacturer_update'])) {
          $dataMS['manufacturer'][$key] = '';
          if (isset($row['attributes'])) {
            foreach ($row['attributes'] as $key1 => $value1) {
              if ($value1['name'] == $startVars['manufacturer']) {
                if ($value1['type'] == "string" || $value1['type'] == "text") {
                  $dataMS['manufacturer'][$key] = $this->db->escape($value1['value']);
                  break;
                } elseif ($value1['type'] == "customentity" || 
                          $value1['type'] == "counterparty") {
                  $dataMS['manufacturer'][$key] = $this->db->escape($value1['value']['name']);
                  break;
                }
              }
            }
          }
        }

        // EAN
        if (isset($startVars['ean_update'])) {
          $dataMS['ean'][$key] = '';
          if (isset($row['barcodes'])) {
            foreach ($row['barcodes'] as $barcodeValue) {
              if (isset($barcodeValue['ean13'])) {
                $dataMS['ean'][$key] = $barcodeValue['ean13'];
              }
            }
          }
        }

        $key++;
      }
      
      if (!$this->cron && $startVars['product_offset'] != 0) {
        break;
      }

      $offset += 1000;

    } while (isset($response['meta']['nextHref']));

    curl_close($ch);

    $duplicate = $this->DuplicateCheck([$dataMS['binding'], $dataMS['name']], 'product', $startVars['binding_name']);
    if ($duplicate != []) {
      if ($startVars['binding_name'] == 1) {
        foreach ($duplicate as &$value) {
          foreach (array_keys($dataMS['binding'], $value[0]) as $key) {
            if ($dataMS['name'][$key] == $value[1]) {
              unset($dataMS['binding'][$key], $dataMS['name'][$key], $dataMS['description'][$key], $dataMS['pathName'][$key], $dataMS['price'][$key], $dataMS['category'][$key], $dataMS['sku'][$key], $dataMS['weight'][$key], $dataMS['manufacturer'][$key], $dataMS['manufacturer_id'][$key], $dataMS['product_id'][$key], $dataMS['category_id'][$key], $dataMS['categories'][$key], $dataMS['ean'][$key]);
            }
          }
          $value = implode(" ", $value);
        }
      } else {
        foreach ($duplicate as $value) {
          foreach (array_keys($dataMS['binding'], $value) as $key) {
            unset($dataMS['binding'][$key], $dataMS['name'][$key], $dataMS['description'][$key], $dataMS['pathName'][$key], $dataMS['price'][$key], $dataMS['category'][$key], $dataMS['sku'][$key], $dataMS['weight'][$key], $dataMS['manufacturer'][$key], $dataMS['manufacturer_id'][$key], $dataMS['product_id'][$key], $dataMS['category_id'][$key], $dataMS['categories'][$key], $dataMS['ean'][$key]);
          }
        }
      }

      $duplicate = implode("; ", $duplicate);
      $this->Output("В Моем Складе есть товары-дубликаты по полю {$bindingMS}: " . $duplicate, 'error_warning', false);
    }

    if ($startVars["category_binding"] == "ms_id") {
      // Проверка существования столбца syncms_id
      $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category` LIKE 'syncms_id'");
      if (!isset($query->row['Field'])) {
        $query = $this->db->query("ALTER TABLE " . DB_PREFIX . "category 
          ADD COLUMN syncms_id VARCHAR(255)");
      }
    }

    $implodeBindingMS = htmlspecialchars("'" . implode("', '", $dataMS['binding']) . "'", ENT_COMPAT);
    if (isset($startVars['cat_update'])) {
      if ($startVars['prod_parent_cat'] == 1) {
        // Родительские категории
        $query = $this->db->query("SELECT product_id, stock_status_id, subtract, manufacturer_id, $bindingOC, price, " . ($bindingOC != 'sku' ? "sku, " : "") . "weight, weight_class_id, pd.name AS product_name, pd.meta_title AS meta_title, pd.meta_description AS meta_description, pd.meta_keyword AS meta_keyword, pd.description AS product_description, m.name AS manufacturer_name, ean
        FROM " . DB_PREFIX . "product 
        INNER JOIN " . DB_PREFIX . "product_description pd USING (`product_id`) 
        LEFT JOIN " . DB_PREFIX . "manufacturer m USING (`manufacturer_id`)  
        WHERE `$bindingOC` IN ($implodeBindingMS) AND pd.language_id = '$languageId' ORDER BY FIELD ($bindingOC, $implodeBindingMS)
        ");
      } else {
        $query = $this->db->query("SELECT product_id, stock_status_id, subtract, manufacturer_id, $bindingOC, price, p2c.category_id, " . ($startVars['category_binding'] == 'ms_id' ? "syncms_id, " : "") . ($bindingOC != 'sku' ? "sku, " : "") . "weight, weight_class_id, pd.name AS product_name, pd.meta_title AS meta_title, pd.meta_description AS meta_description, pd.meta_keyword AS meta_keyword, pd.description AS product_description, m.name AS manufacturer_name, cd.name AS category_name, ean 
        FROM " . DB_PREFIX . "product 
        INNER JOIN " . DB_PREFIX . "product_description pd USING (`product_id`) 
        LEFT JOIN " . DB_PREFIX . "product_to_category p2c USING (`product_id`) 
        LEFT JOIN " . DB_PREFIX . "category_description cd ON p2c.category_id = cd.category_id AND cd.language_id = '$languageId'
        LEFT JOIN " . DB_PREFIX . "category c ON p2c.category_id = c.category_id
        LEFT JOIN " . DB_PREFIX . "manufacturer m USING (`manufacturer_id`)  
        WHERE `$bindingOC` IN ($implodeBindingMS) AND pd.language_id = '$languageId' GROUP BY product_id ORDER BY FIELD ($bindingOC, $implodeBindingMS)");
      }
    } else {
      $query = $this->db->query("SELECT product_id, stock_status_id, subtract, manufacturer_id, $bindingOC, price, " . ($bindingOC != 'sku' ? "sku, " : "") . "weight, weight_class_id, pd.name AS product_name, pd.meta_title AS meta_title, pd.meta_description AS meta_description, pd.meta_keyword AS meta_keyword, pd.description AS product_description, m.name AS manufacturer_name, ean
      FROM " . DB_PREFIX . "product 
      INNER JOIN " . DB_PREFIX . "product_description pd USING (`product_id`) 
      LEFT JOIN " . DB_PREFIX . "manufacturer m USING (`manufacturer_id`)  
      WHERE `$bindingOC` IN ($implodeBindingMS) AND pd.language_id = '$languageId' ORDER BY FIELD ($bindingOC, $implodeBindingMS)
      ");
    }

    $needFields = ['product_id', 'stock_status_id', 'subtract', 'manufacturer_id', 'binding', 'price', 'category_id', 'syncms_id', 'sku', 'weight', 'weight_class_id', 'name', 'meta_title', 'meta_description', 'meta_keyword', 'product_description', 'manufacturer_name', 'category_name', 'ean'];

    if ($bindingOC == 'sku') {
      unset($needFields[array_search('sku', $needFields, true)]);
    }
    if (!isset($startVars['cat_update'])) {
      unset($needFields[array_search('category_id', $needFields, true)]);
      unset($needFields[array_search('category_name', $needFields, true)]);
      unset($needFields[array_search('syncms_id', $needFields, true)]);
    } else {
      if ($startVars['prod_parent_cat'] == 1) {
        unset($needFields[array_search('category_id', $needFields, true)]);
        unset($needFields[array_search('category_name', $needFields, true)]);
        unset($needFields[array_search('syncms_id', $needFields, true)]);
      } else {
        if ($startVars['category_binding'] != "ms_id")
          unset($needFields[array_search('syncms_id', $needFields, true)]);
      }
    }

    $needFields = array_values($needFields);

    $dataSQL = $this->GetDataSQL($query, $needFields);

    // Проверка существования столбца main_category
    $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product_to_category` LIKE 'main_category'");
    if (isset($query->row['Field'])) {
      $mainCategory = true;
    } else {
      $mainCategory = false;
    }

    $duplicate = $this->DuplicateCheck([$dataSQL['binding'], $dataSQL['name']], 'product', $startVars['binding_name']);
    if ($duplicate != []) {
      if ($startVars['binding_name'] == 1) {
        foreach ($duplicate as &$value) {
          foreach (array_keys($dataSQL['binding'], $value[0]) as $key) {
            if ($dataSQL['name'][$key] == $value[1]) {
              foreach ($needFields as $field) {
                unset($dataSQL[$field][$key]);
              }
            }
          }
          $value = implode(" ", $value);
        }
      } else {
        foreach ($duplicate as $value) {
          foreach (array_keys($dataSQL['binding'], $value) as $key) {
            foreach ($needFields as $field) {
              unset($dataSQL[$field][$key]);
            }
          }
        }
      }

      $duplicate = implode("; ", $duplicate);
      $this->Output("В Опенкарт есть товары-дубликаты по полю {$bindingOC}: " . $duplicate, 'error_warning', false);
    }

    if ($startVars["category_binding"] == "name") {
      $query = $this->db->query("SELECT category_id, name FROM " . DB_PREFIX . "category_description");
      $categoryDataSQL = $this->GetDataSQL($query, ['category_id', 'name']);
    } else {
      $query = $this->db->query("SELECT category_id, syncms_id FROM " . DB_PREFIX . "category");
      $categoryDataSQL = $this->GetDataSQL($query, ['category_id', 'syncms_id']);
    }

    $query = $this->db->query("SELECT manufacturer_id, name FROM " . DB_PREFIX . "manufacturer");
    $manufacturerDataSQL = $this->GetDataSQL($query, ['manufacturer_id', 'name']);
    if (count($manufacturerDataSQL['manufacturer_id']) == 0) {
      $lastManufacturerId = 0;
    } else {
      $lastManufacturerId = max($manufacturerDataSQL['manufacturer_id']);
    }

    $dateModified = date("Y-m-d H:i:s");

    // Родительские категории
    if ($startVars['prod_parent_cat'] == 1) {
      if ($startVars["category_binding"] == "name") {
        //Определение id категорий в БД
        foreach ($dataMS['category'] as $key => $value) {
          $dataMS['categories_id'][$key] = [];
          if ($dataMS['categories'][$key] != []) {
            foreach ($dataMS['categories'][$key] as $catName) {
              $sqlKey = array_search($catName, $categoryDataSQL['name'], true);
              if (is_int($sqlKey)) {
                $dataMS['categories_id'][$key][] = $categoryDataSQL['category_id'][$sqlKey];
              }
            }
          }
        }
      } else {
        //Определение id категорий в БД
        foreach ($dataMS['category'] as $key => $value) {
          $dataMS['categories_id'][$key] = [];
          if ($dataMS['categories'][$key] != []) {
            foreach ($dataMS['categories'][$key] as $catId) {
              $sqlKey = array_search($catId, $categoryDataSQL['syncms_id'], true);
              if (is_int($sqlKey)) {
                $dataMS['categories_id'][$key][] = $categoryDataSQL['category_id'][$sqlKey];
              }
            }
          }
        }
      }

      $prodCatDataSQL = [];

      $query = $this->db->query("SELECT category_id, product_id FROM " . DB_PREFIX . "product_to_category");
      $prodCatDataTmpSQL = $this->GetDataSQL($query, ['category_id', 'product_id']);
      
      foreach ($prodCatDataTmpSQL['product_id'] as $key => $value) {
        $prodCatDataSQL[$value][] = $prodCatDataTmpSQL['category_id'][$key];
      }

      if ($mainCategory) {
        $query = $this->db->query("SELECT product_id, category_id FROM " . DB_PREFIX . "product_to_category WHERE main_category = 1");
        $catMainSQL = $this->GetDataSQL($query, ['product_id', 'category_id']);
      }
    }

    $insertManufacturer = [];
    $insertManufacturerURL = [];
    $insertManufacturerToStore = [];
    $manufacturerAdded = ['id' => [], 'name' => []];

    // Проверка существования таблицы manufacturer_description
    if (isset($startVars['manufacturer_update'])) {
      $query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "` LIKE '" . DB_PREFIX . "manufacturer_description'");
      if (count($query->rows) != 0) {
        $ocstore = true;
        $insertManufacturerDesc = [];
      } else {
        $ocstore = false;
      }
    }

    $updateSKU = '';
    $updateDateModified = [];
    $updateWeight = '';
    $updateWeightUnity = '';
    $updateManufacturer = "";
    $updateStockStatus = "";
    $updateSubtract = "";
    $updateEan = "";
    $updateWhereProduct = [];

    $updateName = '';
    $updateDescription = '';
    $updateMetaTitle = '';
    $updateMetaDescription = '';
    $updateMetaKeyword = '';
    $updateWhereDescription = [];

    $insertProductToCategory = [];
    $deleteProductToCategory = [];

    $added = [];
    $productUpdatedNum = [];

    $keyMS = [];
    $keySQL = [];
    if ($startVars['binding_name'] == 1) {
      // Модель + Наименование
      foreach ($dataMS['binding'] as $key => $value) {
        foreach (array_keys($dataSQL['binding'], $value) as $value1) {
          if ($dataMS['name'][$key] == $dataSQL['name'][$value1]) {
            $dataMS['product_id'][$key] = $dataSQL['product_id'][$value1];
            array_push($keyMS, $key);
            array_push($keySQL, $value1);
            break;
          }
        }
      }
    } else {
      // Только модель
      foreach ($dataMS['binding'] as $key => $value) {
        $value1 = array_search($value, $dataSQL['binding'], true);
        if (is_int($value1)) {
          $dataMS['product_id'][$key] = $dataSQL['product_id'][$value1];
          array_push($keyMS, $key);
          array_push($keySQL, $value1);
        }
      }
    }

    foreach ($keyMS as $key => $value) {
      // Обновлять EAN
      if (isset($startVars['ean_update'])) {
        if ($dataSQL['ean'][$keySQL[$key]] != $dataMS['ean'][$value]) {
          $updateEan .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], $dataMS['ean'][$value]);
          array_push($updateDateModified, sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], $dateModified));
          array_push($updateWhereProduct, $dataMS['product_id'][$value]);
          array_push($productUpdatedNum, $dataMS['product_id'][$value]);
          array_push($added, sprintf('%s_%s', $dataMS['binding'][$value], $dataMS['name'][$value]));
        } 
      }

      // Обновлять статус при отсутствии на складе
      if (isset($startVars['stock_status_update'])) {
        if ($dataSQL['stock_status_id'][$keySQL[$key]] != $startVars['stock_status']) {
          $updateStockStatus .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], $startVars['stock_status']);
          array_push($updateDateModified, sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], $dateModified));
          array_push($updateWhereProduct, $dataMS['product_id'][$value]);
          array_push($productUpdatedNum, $dataMS['product_id'][$value]);
          array_push($added, sprintf('%s_%s', $dataMS['binding'][$value], $dataMS['name'][$value]));
        } 
      }

      // Обновлять Вычитать со склада
      if (isset($startVars['subtract_update'])) {
        if ($dataSQL['subtract'][$keySQL[$key]] != $startVars['subtract']) {
          $updateSubtract .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], $startVars['subtract']);
          array_push($updateDateModified, sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], $dateModified));
          array_push($updateWhereProduct, $dataMS['product_id'][$value]);
          array_push($productUpdatedNum, $dataMS['product_id'][$value]);
          array_push($added, sprintf('%s_%s', $dataMS['binding'][$value], $dataMS['name'][$value]));
        } 
      }      

      // Обновлять артикул
      if (isset($startVars['sku_update']) && $bindingOC != 'sku') {
        if ($dataSQL['sku'][$keySQL[$key]] != $dataMS['sku'][$value]) {
          $updateSKU .= sprintf("WHEN `product_id` = '%s' THEN '%s'", $dataMS['product_id'][$value], htmlspecialchars($dataMS['sku'][$value], ENT_COMPAT));
          array_push($updateDateModified, sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], $dateModified));
          array_push($updateWhereProduct, $dataMS['product_id'][$value]);
          array_push($productUpdatedNum, $dataMS['product_id'][$value]);
          array_push($added, sprintf('%s_%s', $dataMS['binding'][$value], $dataMS['name'][$value])); 
        }
      }

      // Обновлять вес
      if (isset($startVars['weight_update'])) {
        if ($dataSQL['weight'][$keySQL[$key]] != $dataMS['weight'][$value]
        || $dataSQL['weight_class_id'][$keySQL[$key]] != $startVars['weight_unity']) {
          array_push($updateDateModified, sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], $dateModified));
          $updateWeight .= sprintf("WHEN `product_id` = '%s' THEN '%s'", $dataMS['product_id'][$value], $dataMS['weight'][$value]);
          $updateWeightUnity .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], $startVars['weight_unity']);

          array_push($updateWhereProduct, $dataMS['product_id'][$value]);
          array_push($productUpdatedNum, $dataMS['product_id'][$value]);
          array_push($added, sprintf('%s_%s', $dataMS['binding'][$value], $dataMS['name'][$value])); 
        }
      }

      // Обновлять производителя
      if (isset($startVars['manufacturer_update'])) {
        if ($dataSQL['manufacturer_name'][$keySQL[$key]] != $dataMS['manufacturer'][$value]) {
          $manufacturerKey = array_search($dataMS['manufacturer'][$value], $manufacturerDataSQL['name'], true);
          $manufacturerAddedKey = array_search($dataMS['manufacturer'][$value], $manufacturerAdded['name'], true);
          if ($manufacturerKey !== false) {
            $dataMS['manufacturer_id'][$value] = $manufacturerDataSQL['manufacturer_id'][$manufacturerKey];
          } elseif ($dataMS['manufacturer'][$value] == '') {
            $dataMS['manufacturer_id'][$value] = 0;
          } else {
            if ($manufacturerAddedKey !== false) {
              $dataMS['manufacturer_id'][$value] = $manufacturerAdded['id'][$manufacturerAddedKey];
            } else {
              $lastManufacturerId++;
              $dataMS['manufacturer_id'][$value] = $lastManufacturerId;
              array_push($manufacturerAdded['id'], $lastManufacturerId);
              array_push($manufacturerAdded['name'], $dataMS['manufacturer'][$value]);

              array_push($insertManufacturer, sprintf("('%s', '%s', '0')", $lastManufacturerId, $dataMS['manufacturer'][$value]));
              array_push($insertManufacturerToStore, sprintf("('%s', '0')", $lastManufacturerId));
              $manufacturerKeyword = $this->Translit($dataMS['manufacturer'][$value]);

              if ($this->version == '2.1' || $this->version == '2.3') {
                array_push($insertManufacturerURL, "('manufacturer_id=$lastManufacturerId', '$manufacturerKeyword')");
              } else {
                array_push($insertManufacturerURL, "('0', '$languageId', 'manufacturer_id=$lastManufacturerId', '$manufacturerKeyword')");
              }
              if ($ocstore) {
                if ($this->version == '2.1' || $this->version == '2.3') {
                  array_push($insertManufacturerDesc, sprintf("('%s', '%s', '%s')", $lastManufacturerId, $languageId, $dataMS['manufacturer'][$value]));
                } else {
                  array_push($insertManufacturerDesc, sprintf("('%s', '%s')", $lastManufacturerId, $languageId));
                }
              }
            }
          }

          array_push($updateDateModified, sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], $dateModified));
          $updateManufacturer .= sprintf("WHEN `product_id` = '%s' THEN '%s'", $dataMS['product_id'][$value], $dataMS['manufacturer_id'][$value]);
          array_push($updateWhereProduct, $dataMS['product_id'][$value]);
          array_push($productUpdatedNum, $dataMS['product_id'][$value]);
          array_push($added, sprintf('%s_%s', $dataMS['binding'][$value], $dataMS['name'][$value])); 
        }
      }
        
      // Обновлять Meta
      if ($startVars['meta_prod_update'] == 1) {
        $metaTitleNew = $this->GetMetaTag($metaTitle, ['name', 'price'], [$dataMS['name'][$value], $dataMS['price'][$value]]);
        $metaDescNew = $this->GetMetaTag($metaDesc, ['name', 'price'], [$dataMS['name'][$value], $dataMS['price'][$value]]);
        $metaKeywordNew = $this->GetMetaTag($metaKeyword, ['name', 'price'], [$dataMS['name'][$value], $dataMS['price'][$value]]);

        if ($dataSQL['meta_title'][$keySQL[$key]] != $metaTitleNew 
        || $dataSQL['meta_description'][$keySQL[$key]] != $metaDescNew 
        || $dataSQL['meta_keyword'][$keySQL[$key]] != $metaKeywordNew) {
          $updateMetaTitle .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], htmlspecialchars($metaTitleNew, ENT_COMPAT));
          $updateMetaDescription .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], htmlspecialchars($metaDescNew, ENT_COMPAT));
          $updateMetaKeyword .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], htmlspecialchars($metaKeywordNew, ENT_COMPAT));
          array_push($updateWhereDescription, $dataMS['product_id'][$value]);
          array_push($productUpdatedNum, $dataMS['product_id'][$value]);
          array_push($added, sprintf('%s_%s', $dataMS['binding'][$value], $dataMS['name'][$value])); 
        }
      }

      // Обновлять Описания
      if (isset($startVars['desc_update'])) {
        if ($dataSQL['product_description'][$keySQL[$key]] != $dataMS['description'][$value]) {
          $updateDescription .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], htmlspecialchars($dataMS['description'][$value], ENT_COMPAT));
          array_push($updateWhereDescription, $dataMS['product_id'][$value]);
          array_push($productUpdatedNum, $dataMS['product_id'][$value]);
          array_push($added, sprintf('%s_%s', $dataMS['binding'][$value], $dataMS['name'][$value]));
        } 
      }

      // Обновлять Наименования
      if (isset($startVars['name_update'])) {
        if ($dataSQL['name'][$keySQL[$key]] != $dataMS['name'][$value]) {
          $updateName .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$value], htmlspecialchars($dataMS['name'][$value], ENT_COMPAT));
          array_push($updateWhereDescription, $dataMS['product_id'][$value]);
          array_push($productUpdatedNum, $dataMS['product_id'][$value]);
          array_push($added, sprintf('%s_%s', $dataMS['binding'][$value], $dataMS['name'][$value]));
        } 
      }

      // Обновлять категорию
      if (isset($startVars['cat_update'])) {
        if ($startVars['prod_parent_cat'] == 1) {
          $sqlKey = array_search($dataMS['category'][$value], $categoryDataSQL[($startVars['category_binding'] == 'ms_id' ? "syncms_id" : "name")], true);
          if ($sqlKey !== false)
            $dataMS['category_id'][$value] = $categoryDataSQL['category_id'][$sqlKey];
          else
            $dataMS['category_id'][$value] = '';

          $updateCat = false;
          if (isset($prodCatDataSQL[$dataMS['product_id'][$value]])) {
            sort($dataMS['categories_id'][$value]);
            sort($prodCatDataSQL[$dataMS['product_id'][$value]]);
            if ($dataMS['categories_id'][$value] != $prodCatDataSQL[$dataMS['product_id'][$value]]) {
              $updateCat = true;
            }
          } else {
            if ($dataMS['categories_id'][$value] != []) {
              $updateCat = true;
            }
          }

          if ($mainCategory) {
            $sqlKey = array_search($dataMS['product_id'][$value], $catMainSQL["product_id"], true);
            if ($sqlKey !== false && $dataMS['category_id'][$value] != $catMainSQL['category_id'][$sqlKey])
              $updateCat = true;
          }  
            
          if ($updateCat) {
            foreach ($dataMS['categories_id'][$value] as $catId) {
              if ($catId == $dataMS['category_id'][$value]) {
                if ($mainCategory) {
                  array_push($insertProductToCategory, sprintf("('%s', '%s', '1')", $dataMS['product_id'][$value], $catId));
                } else {
                  array_push($insertProductToCategory, sprintf("('%s', '%s')", $dataMS['product_id'][$value], $catId));
                } 
              } else {
                if ($mainCategory) {
                  array_push($insertProductToCategory, sprintf("('%s', '%s', '0')", $dataMS['product_id'][$value], $catId));
                } else {
                  array_push($insertProductToCategory, sprintf("('%s', '%s')", $dataMS['product_id'][$value], $catId));
                }
              }
            }

            array_push($deleteProductToCategory, $dataMS['product_id'][$value]); 
            array_push($productUpdatedNum, $dataMS['product_id'][$value]);
            array_push($added, sprintf('%s_%s', $dataMS['binding'][$value], $dataMS['name'][$value]));
          }
        } else {
          if ($dataSQL[($startVars['category_binding'] == 'ms_id' ? "syncms_id" : "category_name")][$keySQL[$key]] != $dataMS['category'][$value]) {
            $sqlKey = array_search($dataMS['category'][$value], $categoryDataSQL[($startVars['category_binding'] == 'ms_id' ? "syncms_id" : "name")], true);
            if ($sqlKey !== false) {
              $dataMS['category_id'][$value] = $categoryDataSQL['category_id'][$sqlKey];
              if ($mainCategory) {
                array_push($insertProductToCategory, sprintf("('%s', '%s', '1')", $dataMS['product_id'][$value], $dataMS['category_id'][$value]));
              } else {
                array_push($insertProductToCategory, sprintf("('%s', '%s')", $dataMS['product_id'][$value], $dataMS['category_id'][$value]));
              }
            } else {
              $dataMS['category_id'][$value] = '';
            } 

            array_push($deleteProductToCategory, $dataMS['product_id'][$value]); 
            array_push($productUpdatedNum, $dataMS['product_id'][$value]);
            array_push($added, sprintf('%s_%s', $dataMS['binding'][$value], $dataMS['name'][$value]));
          }
        }
      }
    }

    if (count($updateWhereDescription) != 0) {
      $updateWhereDescription = array_unique($updateWhereDescription);
      $updateWhereDescription = implode(", ", $updateWhereDescription);
      $this->db->query(str_replace(", WHERE", " WHERE", "UPDATE " . DB_PREFIX . "product_description SET "
        . ($updateName != '' ? "name = CASE " . $updateName . " ELSE `name` END, " : '')
        . ($updateDescription != '' ? "description = CASE " . $updateDescription . " ELSE `description` END, " : '')
        . ($updateMetaTitle != '' ? "meta_title = CASE " . $updateMetaTitle . " ELSE `meta_title` END, " : '')
        . ($updateMetaDescription != '' ? "meta_description = CASE " . $updateMetaDescription . " ELSE `meta_description` END, " : '')
        . ($updateMetaKeyword != '' ? "meta_keyword = CASE " . $updateMetaKeyword . " ELSE `meta_keyword` END, " : '') .
        "WHERE `product_id` IN ($updateWhereDescription)"));
    }

    if (count($insertManufacturer) != 0) {
      $insertManufacturer = implode(", ", $insertManufacturer);
      $insertManufacturerToStore = implode(", ", $insertManufacturerToStore);
      $insertManufacturerURL = implode(", ", $insertManufacturerURL);
      $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer (`manufacturer_id`, `name`, `sort_order`) VALUES $insertManufacturer");
      $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store (`manufacturer_id`, `store_id`) VALUES $insertManufacturerToStore");

      if ($this->version == '2.1' || $this->version == '2.3') {
        $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias (`query`, `keyword`) VALUES $insertManufacturerURL");
      } else {
        $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url (`store_id`, `language_id`, `query`, `keyword`) VALUES $insertManufacturerURL");
      }
      
      if ($ocstore) {
        $insertManufacturerDesc = implode(", ", $insertManufacturerDesc);
        if ($this->version == '2.1' || $this->version == '2.3') {
          $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_description (`manufacturer_id`, `language_id`, `name`) VALUES $insertManufacturerDesc");
        } else {
          $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_description (`manufacturer_id`, `language_id`) VALUES $insertManufacturerDesc");
        }
      }
    }

    if (count($updateWhereProduct) != 0) {
      $updateWhereProduct = array_unique($updateWhereProduct);
      $updateDateModified = array_unique($updateDateModified);
      $updateDateModified = implode(" ", $updateDateModified);
      $updateWhereProduct = implode(", ", $updateWhereProduct);
      $this->db->query("UPDATE " . DB_PREFIX . "product SET " 
      . ($updateSKU != '' ? "sku = CASE " . $updateSKU . " ELSE `sku` END, " : '')
      . ($updateManufacturer != '' ? "manufacturer_id = CASE " . $updateManufacturer . " ELSE `manufacturer_id` END, " : '')
      . ($updateWeight != '' ? "weight = CASE " . $updateWeight . " ELSE `weight` END, " : '')
      . ($updateStockStatus != '' ? "stock_status_id = CASE " . $updateStockStatus . " ELSE `stock_status_id` END, " : '')
      . ($updateSubtract != '' ? "subtract = CASE " . $updateSubtract . " ELSE `subtract` END, " : '')
      . ($updateEan != '' ? "ean = CASE " . $updateEan . " ELSE `ean` END, " : '')
      . ($updateWeightUnity != '' ? "weight_class_id = CASE " . $updateWeightUnity . " ELSE `weight_class_id` END, " : '') .
      "date_modified = CASE " . $updateDateModified . " ELSE `date_modified` END
      WHERE `product_id` IN ($updateWhereProduct)");
    }

    if (count($deleteProductToCategory) != 0) {
      $insertProductToCategory = implode(", ", $insertProductToCategory);
      $deleteProductToCategory = "'" . implode("', '", $deleteProductToCategory) . "'";
      $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE `product_id` IN ($deleteProductToCategory)");

      if ($insertProductToCategory != '') {
        if ($mainCategory) {
          $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category (`product_id`, `category_id`, `main_category`) VALUES $insertProductToCategory");
        } else {
          $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category (`product_id`, `category_id`) VALUES $insertProductToCategory");
        }
      }
    }

    $productUpdatedNum = array_unique($productUpdatedNum);

    $added = array_unique($added);
    $this->logText .= 'Обновлено товаров: ' . count($productUpdatedNum) . PHP_EOL;
    foreach ($added as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    
    $this->LogWrite();
    $this->Output("Успешно. Товаров обновлено: " . count($productUpdatedNum), 'success');
  }


  public function StockUpdate() {
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }

    $this->logText = date('H:i:s d.m.Y') . ' Обновление остатков товаров' . PHP_EOL;

    $this->db->query("set session wait_timeout=28800");
    $startVars = $this->GetStartVars(['binding', 'product_offset', 'binding_name', 'stock_store', 'sum_option', 'from_group', 'empty_field', 'not_from_group']);

    $bindingOC = substr($startVars['binding'], 0, strpos($startVars['binding'], '_'));
    $bindingMS = substr($startVars['binding'], strpos($startVars['binding'], '_') + 1);

    $dataMS = $this->InitialDataMS(['binding', 'name', 'id', 'quantity']);
    $ch = $this->CurlInit($startVars['headers']);

    // Определенные категории
    if (isset($startVars['from_group'])) {
      $fromGroup = $this->GetFromGroups($startVars['from_group'], "product", $ch);
      if (isset($startVars['not_from_group'])) {
        $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "category", $ch);
      }
    } else {
      if (isset($startVars['not_from_group'])) {
        $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "product", $ch);
      }
    }

    if ($this->cron || $startVars['product_offset'] == 0) {
      $offset = 0;
    } else {
      $offset = $startVars['product_offset'] - 1;
    }

    $key = 0;
    do {
      if (!isset($startVars['stock_store'])) {
        $url = 'https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=product;type=bundle;type=consignment&offset=' . $offset;
      } else {
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=product;type=bundle;type=consignment&offset=" . $offset . "&filter=";
        foreach ($startVars['stock_store'] as $storeUrl) {
          $url .= "stockStore=" . $storeUrl . ";";
        }
      }

      // Определенные группы
      if (isset($startVars['from_group'])) {
        if (empty($fromGroup)) {
          continue;
        }
        $url .= '&filter=productFolder=' . implode(";productFolder=", $fromGroup);
      }

      // Определенные группы
      if (isset($startVars['not_from_group']) && !isset($startVars['from_group'])) {
        if (!empty($notFromGroup))
          $url .= '&filter=productFolder!=' . implode(";productFolder!=", $notFromGroup);
      }

      // Игнорировать товары с незаполненным
      if ($startVars['empty_field'] != 1) {
        $url .= "&filter={$bindingMS}!=";
      }

      curl_setopt($ch, CURLOPT_URL, $url);
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      //Занесение данных Мой Склад в массивы
      foreach ($response['rows'] as $row) {
        // Определенные группы
        if (isset($startVars['not_from_group']) && isset($startVars['from_group'])) {
          if ($this->CheckFromGroup($notFromGroup, $row['pathName'], $row['name'], 'product', true)) {
            continue;
          }
        }

        $dataMS = $this->GetDataMS($row, [$bindingMS, 'name', 'id', 'quantity'], ['binding', 'name', 'id', 'quantity'], $dataMS);
        if (isset($row['components'])) {
          $dataMS['quantity'][$key] = $this->db->escape($row['components']['meta']['href']);
        }

        $key++;
      }
      
      if (!$this->cron && $startVars['product_offset'] != 0) {
        break;
      }
      $offset += 1000;
    } while (isset($response['meta']['nextHref']));

    $dataMS = $this->GetBundleQuantity($dataMS, $ch);

    curl_close($ch);

    $duplicate = $this->DuplicateCheck([$dataMS['binding'], $dataMS['name']], 'product', $startVars['binding_name']);
    if ($duplicate != []) {
      if ($startVars['binding_name'] == 1) {
        foreach ($duplicate as &$value) {
          foreach (array_keys($dataMS['binding'], $value[0]) as $key) {
            if ($dataMS['name'][$key] == $value[1]) {
              unset($dataMS['binding'][$key], $dataMS['name'][$key], $dataMS['id'][$key], $dataMS['quantity'][$key]);
            }
          }
          $value = implode(" ", $value);
        }
      } else {
        foreach ($duplicate as $value) {
          foreach (array_keys($dataMS['binding'], $value) as $key) {
            unset($dataMS['binding'][$key], $dataMS['name'][$key], $dataMS['id'][$key], $dataMS['quantity'][$key]);
          }
        }
      }

      $duplicate = implode("; ", $duplicate);
      $this->Output("В Моем Складе есть товары-дубликаты по полю {$bindingMS}: " . $duplicate, 'error_warning', false);
    }

    $languageId = $this->GetLanguageId();

    // Получение количества товаров из БД
    $implodeBindingMS = htmlspecialchars("'" . implode("', '", $dataMS['binding']) . "'", ENT_COMPAT);
    $query = $this->db->query("SELECT product_id, $bindingOC, name, quantity 
    FROM " . DB_PREFIX . "product 
    INNER JOIN " . DB_PREFIX . "product_description pd USING (`product_id`) 
    WHERE `$bindingOC` IN ($implodeBindingMS) AND pd.language_id = $languageId ORDER BY FIELD ($bindingOC, $implodeBindingMS)");

    $dataSQL = $this->GetDataSQL($query, ['product_id', 'binding', 'name', 'quantity']);

    $duplicate = $this->DuplicateCheck([$dataSQL['binding'], $dataSQL['name']], 'product', $startVars['binding_name']);
    if ($duplicate != []) {
      if ($startVars['binding_name'] == 1) {
        foreach ($duplicate as &$value) {
          foreach (array_keys($dataSQL['binding'], $value[0]) as $key) {
            if ($dataSQL['name'][$key] == $value[1]) {
              unset($dataSQL['product_id'][$key], $dataSQL['binding'][$key], $dataSQL['name'][$key], $dataSQL['quantity'][$key]);
            }
          }
          $value = implode(" ", $value);
        }
      } else {
        foreach ($duplicate as $value) {
          foreach (array_keys($dataSQL['binding'], $value) as $key) {
            unset($dataSQL['product_id'][$key], $dataSQL['binding'][$key], $dataSQL['name'][$key], $dataSQL['quantity'][$key]);
          }
        }
      }

      $duplicate = implode("; ", $duplicate);
      $this->Output("В Опенкарт есть товары-дубликаты по полю {$bindingOC}: " . $duplicate, 'error_warning', false);
    }

    // Суммирование остатков опций
    if ($startVars['sum_option'] == 1) {
      $query = $this->db->query("SELECT pov.product_id, SUM(pov.quantity) quantity FROM " . DB_PREFIX . "product_option_value pov INNER JOIN " . DB_PREFIX . "product_option po USING (product_option_id) INNER JOIN " . DB_PREFIX . "product p ON pov.product_id = p.product_id WHERE p.{$bindingOC} IN ({$implodeBindingMS}) AND po.required = 1 GROUP BY pov.product_id, pov.option_id");

      foreach ($query->rows as $key => $value) {
        $dataOptionSQL[$value['product_id']][] = $value['quantity'];
      }
    }

    $updateQuantity = '';
    $updateDateModified = '';
    $updateWhere = [];
    $dateModified = date("Y-m-d H:i:s");
    $added = [];
    $dataMS['product_id'] = [];

    if ($startVars['binding_name'] == 1) {
      // Модель + Наименование
      foreach ($dataMS['binding'] as $key => $value) {
        foreach (array_keys($dataSQL['binding'], $value) as $value1) {
          if ($dataMS['name'][$key] == $dataSQL['name'][$value1]) {
            $dataMS['product_id'][$key] = $dataSQL['product_id'][$value1];

            // Суммирование остатков опций
            if ($startVars['sum_option'] == 1) {
              if (isset($dataOptionSQL[$dataMS['product_id'][$key]]) != 0) {
                $dataMS['quantity'][$key] = min($dataOptionSQL[$dataMS['product_id'][$key]]);
              }
            }

            // Сравнение количества
            if ($dataMS['quantity'][$key] != $dataSQL['quantity'][$value1]) {
            $updateQuantity .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$key], $dataMS['quantity'][$key]);
            $updateDateModified .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$key], $dateModified);
            array_push($updateWhere, $dataMS['product_id'][$key]);
            array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
            }

            break;
          }
        } 
      }
    } else {
      // Только модель
      foreach ($dataMS['binding'] as $key => $value) {
        $value1 = array_search($value, $dataSQL['binding'], true);
        if (is_int($value1)) {
          $dataMS['product_id'][$key] = $dataSQL['product_id'][$value1];

          // Суммирование остатков опций
          if ($startVars['sum_option'] == 1) {
            if (isset($dataOptionSQL[$dataMS['product_id'][$key]]) != 0) {
              $dataMS['quantity'][$key] = min($dataOptionSQL[$dataMS['product_id'][$key]]);
            }
          }

          // Сравнение количества
          if ($dataMS['quantity'][$key] != $dataSQL['quantity'][$value1]) {
            $updateQuantity .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$key], $dataMS['quantity'][$key]);
            $updateDateModified .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$key], $dateModified);
            array_push($updateWhere, $dataMS['product_id'][$key]); 
            array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
          }
        }
      }
    }
    
    $stockUpdatedNum = count($updateWhere);

    // Отправление SQL запроса
    if ($updateQuantity != '') {
      $updateWhere = implode(", ", $updateWhere);
      $this->db->query("UPDATE " . DB_PREFIX . "product SET 
        quantity = CASE " . $updateQuantity . "END,
        date_modified = CASE " . $updateDateModified . "END
        WHERE `product_id` IN ($updateWhere)");
    }

    $added = array_unique($added);
    
    $this->logText .= 'Товаров с обновленными остатками: ' . $stockUpdatedNum . PHP_EOL;
    foreach ($added as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    
    $this->LogWrite();
    $this->Output('Успешно. Количество Товаров с обновленными остатками: ' . $stockUpdatedNum, 'success');
  }


  public function PriceUpdate() {
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }

    $this->db->query("set session wait_timeout=28800");

    $this->logText = date('H:i:s d.m.Y') . ' Обновление цен товаров' . PHP_EOL;

    $startVars = $this->GetStartVars(['binding', 'product_offset', 'binding_name', 'sale_price', 'from_group', 'client_price', 'special_price', 'empty_field', 'not_from_group', 'product_time_limit']);

    // Измененные за последние минуты
    if (isset($startVars['product_time_limit'])) {
      $minuteLimit = $startVars['product_time_limit'];

      if (strpos($minuteLimit, '.') !== false || $minuteLimit < 0) {
        $this->Output('Ошибка! Количество минут должно быть целым неотрицательным числом', 'error_warning');
        exit();
      }

      $dateLimit = new DateTime();
      $dateLimit->modify("- $minuteLimit minutes");
      $dateLimit = $dateLimit->format("Y-m-d H:i:s");
    }

    $bindingOC = substr($startVars['binding'], 0, strpos($startVars['binding'], '_'));
    $bindingMS = substr($startVars['binding'], strpos($startVars['binding'], '_') + 1);

    $dataMS = $this->InitialDataMS(['binding', 'name', 'price', 'client_price', 'special_price']);

    $ch = $this->CurlInit($startVars['headers']);

    // Определенные категории
    if (isset($startVars['from_group'])) {
      $fromGroup = $this->GetFromGroups($startVars['from_group'], "product", $ch);
      if (isset($startVars['not_from_group'])) {
        $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "category", $ch);
      }
    } else {
      if (isset($startVars['not_from_group'])) {
        $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "product", $ch);
      }
    }

    if ($this->cron || $startVars['product_offset'] == 0) {
      $offset = 0;
    } else {
      $offset = $startVars['product_offset'] - 1;
    }

    $languageId = $this->GetLanguageId();

    // Отдельные цены
    if (isset($startVars['client_price'])) {
      $query = $this->db->query("SELECT customer_group_id, name 
        FROM " . DB_PREFIX . "customer_group_description
        WHERE `language_id` = {$languageId}");
      $customers = $this->GetDataSQL($query, ['customer_group_id', 'name']);

      $clientPrices = [];
      foreach (explode(";", $startVars['client_price']) as $value) {
        $explode = explode(" - ", $value);
        $customerKey = array_search(trim($explode[0]), $customers['name'], true);
        if ($customerKey !== false) {
          $clientPrices[trim($explode[1])][] = $customers['customer_group_id'][$customerKey];
        }
      }
    }

    // Цены по акции
    if (isset($startVars['special_price'])) {
      $query = $this->db->query("SELECT customer_group_id, name 
        FROM " . DB_PREFIX . "customer_group_description
        WHERE `language_id` = {$languageId}");
      $customers = $this->GetDataSQL($query, ['customer_group_id', 'name']);

      $specialPrices = [];
      foreach (explode(";", $startVars['special_price']) as $value) {
        $explode = explode(" - ", $value);
        $customerKey = array_search(trim($explode[0]), $customers['name'], true);
        if ($customerKey !== false) {
          $specialPrices[trim($explode[1])][] = $customers['customer_group_id'][$customerKey];
        }
      }
    }

    do {
      // Получение данных по curl
      $url = 'https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=product;type=bundle&offset=' . $offset;

      // Определенные группы
      if (isset($startVars['from_group'])) {
        if (empty($fromGroup)) {
          continue;
        }
        $url .= '&filter=productFolder=' . implode(";productFolder=", $fromGroup);
      }

      // Определенные группы
      if (isset($startVars['not_from_group']) && !isset($startVars['from_group'])) {
        if (!empty($notFromGroup))
          $url .= '&filter=productFolder!=' . implode(";productFolder!=", $notFromGroup);
      }

      // Игнорировать товары с незаполненным
      if ($startVars['empty_field'] != 1) {
        $url .= "&filter={$bindingMS}!=";
      }

      if (isset($startVars['product_time_limit'])) {
        $url .= "&filter=updated" . urlencode(">=" . $dateLimit);
      }
      
      curl_setopt($ch, CURLOPT_URL, $url);
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      //Занесение данных Мой Склад в массивы
      foreach ($response['rows'] as $row) {
        // Определенные группы
        if (isset($startVars['not_from_group']) && isset($startVars['from_group'])) {
          if ($this->CheckFromGroup($notFromGroup, $row['pathName'], $row['name'], 'product', true)) {
            continue;
          }
        }

        $dataMS = $this->GetDataMS($row, [$bindingMS, 'name'], ['binding', 'name'], $dataMS);

        if (isset($row['salePrices'][$startVars['sale_price']])) {
          $dataMS = $this->GetDataMS($row['salePrices'][$startVars['sale_price']], ['value'], ['price'], $dataMS);
        } else {
          $dataMS = $this->GetDataMS($row['salePrices'][0], ['value'], ['price'], $dataMS);
        }

        // Отдельные цены
        if (isset($startVars['client_price'])) {
          $prices = [];
          foreach ($row['salePrices'] as $salePrice) {
            if (isset($clientPrices[$salePrice['priceType']['name']]) && $salePrice['value'] != 0) {
              foreach ($clientPrices[$salePrice['priceType']['name']] as $clientId) {
                $prices[$clientId] = $salePrice['value'] / 100;
              }
            }
          }
          if (count($prices) != 0)
            $dataMS['client_price'][] = $prices;
          else
            $dataMS['client_price'][] = "";
        }

        // Цены по акции
        if (isset($startVars['special_price'])) {
          $prices = [];
          foreach ($row['salePrices'] as $salePrice) {
            if (isset($specialPrices[$salePrice['priceType']['name']]) && $salePrice['value'] != 0) {
              foreach ($specialPrices[$salePrice['priceType']['name']] as $clientId) {
                $prices[$clientId] = $salePrice['value'] / 100;
              }
            }
          }
          if (count($prices) != 0)
            $dataMS['special_price'][] = $prices;
          else
            $dataMS['special_price'][] = "";
        }
      }

      if (!$this->cron && $startVars['product_offset'] != 0) {
        break;
      }
      $offset += 1000;
    } while (isset($response['meta']['nextHref']));

    curl_close($ch);

    $duplicate = $this->DuplicateCheck([$dataMS['binding'], $dataMS['name']], 'product', $startVars['binding_name']);
    if ($duplicate != []) {
      if ($startVars['binding_name'] == 1) {
        foreach ($duplicate as &$value) {
          foreach (array_keys($dataMS['binding'], $value[0]) as $key) {
            if ($dataMS['name'][$key] == $value[1]) {
              unset($dataMS['binding'][$key], $dataMS['name'][$key], $dataMS['price'][$key], $dataMS['client_price'][$key], $dataMS['special_price'][$key]);
            }
          }
          $value = implode(" ", $value);
        }
      } else {
        foreach ($duplicate as $value) {
          foreach (array_keys($dataMS['binding'], $value) as $key) {
            unset($dataMS['binding'][$key], $dataMS['name'][$key], $dataMS['price'][$key], $dataMS['client_price'][$key], $dataMS['special_price'][$key]);
          }
        }
      }

      $duplicate = implode("; ", $duplicate);
      $this->Output("В Моем Складе есть товары-дубликаты по полю {$bindingMS}: " . $duplicate, 'error_warning', false);
    }

    // Получение количества товаров из БД
    $implodeBindingMS = htmlspecialchars("'" . implode("', '", $dataMS['binding']) . "'", ENT_COMPAT);
    $query = $this->db->query("SELECT product_id, $bindingOC, name, price 
      FROM " . DB_PREFIX . "product 
      INNER JOIN " . DB_PREFIX . "product_description pd USING (`product_id`) 
      WHERE `$bindingOC` IN ($implodeBindingMS) AND pd.language_id = $languageId ORDER BY FIELD ($bindingOC, $implodeBindingMS)");

    $dataSQL = $this->GetDataSQL($query, ['product_id', 'binding', 'name', 'price']);

    $duplicate = $this->DuplicateCheck([$dataSQL['binding'], $dataSQL['name']], 'product', $startVars['binding_name']);
    if ($duplicate != []) {
      if ($startVars['binding_name'] == 1) {
        foreach ($duplicate as &$value) {
          foreach (array_keys($dataSQL['binding'], $value[0]) as $key) {
            if ($dataSQL['name'][$key] == $value[1]) {
              unset($dataSQL['product_id'][$key], $dataSQL['binding'][$key], $dataSQL['name'][$key], $dataSQL['price'][$key]);
            }
          }
          $value = implode(" ", $value);
        }
      } else {
        foreach ($duplicate as $value) {
          foreach (array_keys($dataSQL['binding'], $value) as $key) {
            unset($dataSQL['product_id'][$key], $dataSQL['binding'][$key], $dataSQL['name'][$key], $dataSQL['price'][$key]);
          }
        }
      }

      $duplicate = implode("; ", $duplicate);
      $this->Output("В Опенкарт есть товары-дубликаты по полю {$bindingOC}: " . $duplicate, 'error_warning', false);
    }

    // Отдельные цены
    if (isset($startVars['client_price'])) {
      $query = $this->db->query("SELECT product_id, customer_group_id, price 
        FROM " . DB_PREFIX . "product_discount");
      $discountSQL = $this->GetDataSQL($query, ['product_id', 'customer_group_id', 'price']);
      $clientPriceSQL = [];
      foreach ($discountSQL['product_id'] as $key => $value) {
        $clientPriceSQL[$value][$discountSQL['customer_group_id'][$key]] = $discountSQL['price'][$key];
      }
    }

    // Цены по акции
    if (isset($startVars['special_price'])) {
      $query = $this->db->query("SELECT product_id, customer_group_id, price 
        FROM " . DB_PREFIX . "product_special");
      $discountSQL = $this->GetDataSQL($query, ['product_id', 'customer_group_id', 'price']);
      $specialPriceSQL = [];
      foreach ($discountSQL['product_id'] as $key => $value) {
        $specialPriceSQL[$value][$discountSQL['customer_group_id'][$key]] = $discountSQL['price'][$key];
      }
    }

    $updatePrice = '';
    $updateDateModified = '';
    $updateWhere = [];
    $dateModified = date("Y-m-d H:i:s");
    $added = [];
    $dataMS['product_id'] = [];

    // Отдельные цены
    $insertDiscount = [];
    $deleteDiscount = [];

    // Цены по акции
    $insertSpecial = [];
    $deleteSpecial = [];

    if ($startVars['binding_name'] == 1) {
      // Модель + Наименование
      foreach ($dataMS['binding'] as $key => $value) {
        foreach (array_keys($dataSQL['binding'], $value) as $value1) {
          if ($dataMS['name'][$key] == $dataSQL['name'][$value1]) {
            $dataMS['product_id'][$key] = $dataSQL['product_id'][$value1];
            if ($dataMS['price'][$key] != $dataSQL['price'][$value1]) {
              $updatePrice .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$key], $dataMS['price'][$key]);
              $updateDateModified .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$key], $dateModified);
              array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
              array_push($updateWhere, $dataMS['product_id'][$key]); 
            }

            // Отдельные цены
            if (isset($startVars['client_price'])) {
              if (isset($clientPriceSQL[$dataMS['product_id'][$key]])) {
                if ($dataMS['client_price'][$key] != '') {
                  if ($clientPriceSQL[$dataMS['product_id'][$key]] != $dataMS['client_price'][$key]) {
                    foreach ($dataMS['client_price'][$key] as $priceKey => $priceValue) {
                      $insertDiscount[] = sprintf("('%s', '%s', '%s', '1')", $dataMS['product_id'][$key], $priceKey, $priceValue);
                      $deleteDiscount[] = $dataMS['product_id'][$key];
                    }
                    array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
                  }
                } else {
                  $deleteDiscount[] = $dataMS['product_id'][$key];
                  array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
                }
              } else {
                if ($dataMS['client_price'][$key] != '') {
                  foreach ($dataMS['client_price'][$key] as $priceKey => $priceValue) {
                    $insertDiscount[] = sprintf("('%s', '%s', '%s', '1')", $dataMS['product_id'][$key], $priceKey, $priceValue);
                    $deleteDiscount[] = $dataMS['product_id'][$key];
                  }
                  array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
                }
              }
            }

            // Цены по акции
            if (isset($startVars['special_price'])) {
              if (isset($specialPriceSQL[$dataMS['product_id'][$key]])) {
                if ($dataMS['special_price'][$key] != '') {
                  if ($specialPriceSQL[$dataMS['product_id'][$key]] != $dataMS['special_price'][$key]) {
                    foreach ($dataMS['special_price'][$key] as $priceKey => $priceValue) {
                      $insertSpecial[] = sprintf("('%s', '%s', '%s')", $dataMS['product_id'][$key], $priceKey, $priceValue);
                      $deleteSpecial[] = $dataMS['product_id'][$key];
                    }
                    array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
                  }
                } else {
                  $deleteSpecial[] = $dataMS['product_id'][$key];
                  array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
                }
              } else {
                if ($dataMS['special_price'][$key] != '') {
                  foreach ($dataMS['special_price'][$key] as $priceKey => $priceValue) {
                    $insertSpecial[] = sprintf("('%s', '%s', '%s')", $dataMS['product_id'][$key], $priceKey, $priceValue);
                    $deleteSpecial[] = $dataMS['product_id'][$key];
                  }
                  array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
                }
              }
            }
            
            break;
          }
        } 
      }
    } else {
      // Только модель
      foreach ($dataMS['binding'] as $key => $value) {
        $value1 = array_search($value, $dataSQL['binding'], true);
        if (is_int($value1)) {
          $dataMS['product_id'][$key] = $dataSQL['product_id'][$value1];
          if ($dataMS['price'][$key] != $dataSQL['price'][$value1]) {
            $updatePrice .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$key], $dataMS['price'][$key]);
            $updateDateModified .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$key], $dateModified);
            array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
            array_push($updateWhere, $dataMS['product_id'][$key]); 
          }

          // Отдельные цены
          if (isset($startVars['client_price'])) {
            if (isset($clientPriceSQL[$dataMS['product_id'][$key]])) {
              if ($dataMS['client_price'][$key] != '') {
                if ($clientPriceSQL[$dataMS['product_id'][$key]] != $dataMS['client_price'][$key]) {
                  foreach ($dataMS['client_price'][$key] as $priceKey => $priceValue) {
                    $insertDiscount[] = sprintf("('%s', '%s', '%s', '1')", $dataMS['product_id'][$key], $priceKey, $priceValue);
                    $deleteDiscount[] = $dataMS['product_id'][$key];
                  }
                  array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
                }
              } else {
                $deleteDiscount[] = $dataMS['product_id'][$key];
                array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
              }
            } else {
              if ($dataMS['client_price'][$key] != '') {
                foreach ($dataMS['client_price'][$key] as $priceKey => $priceValue) {
                  $insertDiscount[] = sprintf("('%s', '%s', '%s', '1')", $dataMS['product_id'][$key], $priceKey, $priceValue);
                  $deleteDiscount[] = $dataMS['product_id'][$key];
                }
                array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
              }
            }
          }

          // Цены по акции
          if (isset($startVars['special_price'])) {
            if (isset($specialPriceSQL[$dataMS['product_id'][$key]])) {
              if ($dataMS['special_price'][$key] != '') {
                if ($specialPriceSQL[$dataMS['product_id'][$key]] != $dataMS['special_price'][$key]) {
                  foreach ($dataMS['special_price'][$key] as $priceKey => $priceValue) {
                    $insertSpecial[] = sprintf("('%s', '%s', '%s')", $dataMS['product_id'][$key], $priceKey, $priceValue);
                    $deleteSpecial[] = $dataMS['product_id'][$key];
                  }
                  array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
                }
              } else {
                $deleteSpecial[] = $dataMS['product_id'][$key];
                array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
              }
            } else {
              if ($dataMS['special_price'][$key] != '') {
                foreach ($dataMS['special_price'][$key] as $priceKey => $priceValue) {
                  $insertSpecial[] = sprintf("('%s', '%s', '%s')", $dataMS['product_id'][$key], $priceKey, $priceValue);
                  $deleteSpecial[] = $dataMS['product_id'][$key];
                }
                array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
              }
            }
          }
        }
      }
    }

    // Отправление SQL запроса
    if ($updatePrice != '') {
      $updateWhere = implode(", ", $updateWhere);
      $this->db->query("UPDATE " . DB_PREFIX . "product SET 
        price = CASE " . $updatePrice . "END,
        date_modified = CASE " . $updateDateModified . "END
        WHERE `product_id` IN ($updateWhere)");
    }

    // Отдельные цены
    if (isset($startVars['client_price'])) {
      if (count($deleteDiscount) != 0) {
        $deleteDiscount = implode(", ", $deleteDiscount);
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE `product_id` IN ($deleteDiscount)");
      }
      if (count($insertDiscount) != 0) {
        $insertDiscount = implode(", ", $insertDiscount);
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount (`product_id`, `customer_group_id`, `price`, `quantity`) VALUES $insertDiscount");
      }
    }

    // Цены по акции
    if (isset($startVars['special_price'])) {
      if (count($deleteSpecial) != 0) {
        $deleteSpecial = implode(", ", $deleteSpecial);
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE `product_id` IN ($deleteSpecial)");
      }
      if (count($insertSpecial) != 0) {
        $insertSpecial = implode(", ", $insertSpecial);
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_special (`product_id`, `customer_group_id`, `price`) VALUES $insertSpecial");
      }
    }

    $added = array_unique($added);

    $this->logText .= 'Товаров с обновленными ценами: ' . count($added) . PHP_EOL;
    foreach ($added as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    
    $this->LogWrite();
    $this->Output('Успешно. Количество Товаров с обновленными ценами: ' . count($added), 'success');
  }


  public function SyncAbsenceProducts()
  {
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }

    $this->logText = date('H:i:s d.m.Y') . ' Обновление/удаление лишних товаров' . PHP_EOL;

    $this->db->query("set session wait_timeout=28800");
    $startVars = $this->GetStartVars(['binding', 'absence_products', 'binding_name', 'from_group', 'empty_field', 'not_from_group', 'certain_products']);

    $bindingOC = substr($startVars['binding'], 0, strpos($startVars['binding'], '_'));
    $bindingMS = substr($startVars['binding'], strpos($startVars['binding'], '_') + 1);

    $dataMS = $this->InitialDataMS(['binding', 'name']);

    $ch = $this->CurlInit($startVars['headers']);
    
    // Определенные категории
    if (isset($startVars['from_group'])) {
      $fromGroup = $this->GetFromGroups($startVars['from_group'], "product", $ch);
      if (isset($startVars['not_from_group'])) {
        $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "category", $ch);
      }
    } else {
      if (isset($startVars['not_from_group'])) {
        $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "product", $ch);
      }
    }

    // Определенные товары
    $certainProductsHref = "";
    if (isset($startVars['certain_products'])) {
      curl_setopt($ch, CURLOPT_URL, "https://api.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes");
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);
      foreach ($response['rows'] as $key => $value) {
        if ($value['name'] == $startVars['certain_products'] && $value['type'] == 'boolean') {
          $certainProductsHref = $value['meta']['href'];
          break;
        }
      }
    }

    // Получение данных по curl
    $offset = 0;
    do { 
      $url = 'https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=product;type=bundle&offset=' . $offset;

      // Определенные группы
      if (isset($startVars['from_group'])) {
        if (empty($fromGroup)) {
          continue;
        }
        $url .= '&filter=productFolder=' . implode(";productFolder=", $fromGroup);
      }

      // Определенные группы
      if (isset($startVars['not_from_group']) && !isset($startVars['from_group'])) {
        if (!empty($notFromGroup))
          $url .= '&filter=productFolder!=' . implode(";productFolder!=", $notFromGroup);
      }
      
      // Игнорировать товары с незаполненным
      if ($startVars['empty_field'] != 1) {
        $url .= "&filter={$bindingMS}!=";
      }

      // Определенные товары
      if ($certainProductsHref != '') {
        $url .= "&filter={$certainProductsHref}=true";
      }

      curl_setopt($ch, CURLOPT_URL, $url);
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      foreach ($response['rows'] as $key => $row) {
        // Определенные группы
        if (isset($startVars['not_from_group']) && isset($startVars['from_group'])) {
          if ($this->CheckFromGroup($notFromGroup, $row['pathName'], $row['name'], 'product', true)) {
            continue;
          }
        }
        
        $dataMS = $this->GetDataMS($row, [$bindingMS, 'name'], ['binding', 'name'], $dataMS);
      }

      $offset += 1000;
    } while (isset($response['meta']['nextHref']));

    curl_close($ch);

    $updateQuantity = '';
    $updateDateModified = '';
    $updateStockStatus = '';
    $updateWhere = [];
    $absenceNum = 0;
    $dateModified = date("Y-m-d H:i:s");
    $deleteSeoUrl = [];
    $added = [];
    $query = $this->db->query("SELECT product_id, $bindingOC, name, quantity, stock_status_id FROM " . DB_PREFIX . "product 
      INNER JOIN " . DB_PREFIX . "product_description USING (`product_id`)");

    $dataSQL = $this->GetDataSQL($query, ['product_id', 'binding', 'name', 'quantity', 'stock_status_id']);

    if ($startVars['binding_name'] == 1) {
      foreach ($dataSQL['binding'] as $key => $value) {
        $find = false;
        foreach (array_keys($dataMS['binding'], $value) as $value1) {
          if ($dataSQL['name'][$key] == $dataMS['name'][$value1]) {
            $find = true;
            break;
          }
        }
        if (!$find) {
          if ($startVars['absence_products'] == 0) {
            $absenceNum++;
            array_push($updateWhere, $dataSQL['product_id'][$key]);
            array_push($added, sprintf('%s_%s', $value, $dataSQL['name'][$key]));
            array_push($deleteSeoUrl, sprintf("product_id=%s", $dataSQL['product_id'][$key]));
          } else if ($startVars['absence_products'] == 1) {
            if ($dataSQL['quantity'][$key] != 0) {
                $updateQuantity .= sprintf("WHEN `product_id` = '%s' THEN '0' ", $dataSQL['product_id'][$key]);
                $updateDateModified .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataSQL['product_id'][$key], $dateModified);
                $absenceNum++;
                array_push($added, sprintf('%s_%s', $value, $dataSQL['name'][$key]));
                array_push($updateWhere, $dataSQL['product_id'][$key]);
            }
          } else {
            if ($dataSQL['quantity'][$key] != 0 || $dataSQL['stock_status_id'][$key] != 5) {
              $updateQuantity .= sprintf("WHEN `product_id` = '%s' THEN '0' ", $dataSQL['product_id'][$key]);
              $updateStockStatus .= sprintf("WHEN `product_id` = '%s' THEN '5' ", $dataSQL['product_id'][$key]);
              $updateDateModified .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataSQL['product_id'][$key], $dateModified);
              $absenceNum++;
              array_push($added, sprintf('%s_%s', $value, $dataSQL['name'][$key]));
              array_push($updateWhere, $dataSQL['product_id'][$key]);
            }
          }
        } 
      }
    } else {
      foreach ($dataSQL['binding'] as $key => $value) {
        $value1 = array_search($value, $dataMS['binding'], true);
        if ($value1 === false) {
          if ($startVars['absence_products'] == 0) {
            $absenceNum++;
            array_push($updateWhere, $dataSQL['product_id'][$key]);
            array_push($added, sprintf('%s_%s', $value, $dataSQL['name'][$key]));
            array_push($deleteSeoUrl, sprintf("product_id=%s", $dataSQL['product_id'][$key]));
          } else if ($startVars['absence_products'] == 1) {
            if ($dataSQL['quantity'][$key] != 0) {
                $updateQuantity .= sprintf("WHEN `product_id` = '%s' THEN '0' ", $dataSQL['product_id'][$key]);
                $updateDateModified .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataSQL['product_id'][$key], $dateModified);
                $absenceNum++;
                array_push($added, sprintf('%s_%s', $value, $dataSQL['name'][$key]));
                array_push($updateWhere, $dataSQL['product_id'][$key]);
            }
          } else {
            if ($dataSQL['quantity'][$key] != 0 || $dataSQL['stock_status_id'][$key] != 5) {
              $updateQuantity .= sprintf("WHEN `product_id` = '%s' THEN '0' ", $dataSQL['product_id'][$key]);
              $updateStockStatus .= sprintf("WHEN `product_id` = '%s' THEN '5' ", $dataSQL['product_id'][$key]);
              $updateDateModified .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataSQL['product_id'][$key], $dateModified);
              $absenceNum++;
              array_push($added, sprintf('%s_%s', $value, $dataSQL['name'][$key]));
              array_push($updateWhere, $dataSQL['product_id'][$key]);
            }
          }
        }
      }
    }
    
    if ($startVars['absence_products'] == 0) {
      $updateWhere = "'" . implode("', '", $updateWhere) . "'";
      $deleteSeoUrl = "'" . implode("', '", $deleteSeoUrl) . "'";
      $this->db->query("DELETE " . DB_PREFIX . "product, 
        " . DB_PREFIX . "product_description,
        " . DB_PREFIX . "product_to_store,
        " . DB_PREFIX . "product_to_category,
        " . DB_PREFIX . "product_image, 
        " . DB_PREFIX . "product_discount, 
        " . DB_PREFIX . "product_filter, 
        " . DB_PREFIX . "product_attribute, 
        " . DB_PREFIX . "product_option,
        " . DB_PREFIX . "product_option_value, 
        " . DB_PREFIX . "product_related, 
        " . DB_PREFIX . "product_special, 
        " . DB_PREFIX . "product_to_download, 
        " . DB_PREFIX . "product_to_layout, 
        " . DB_PREFIX . "product_recurring, 
        " . DB_PREFIX . "review, 
        " . DB_PREFIX . "coupon_product
        FROM " . DB_PREFIX . "product
        LEFT JOIN " . DB_PREFIX . "product_description USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "product_to_store USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "product_to_category USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "product_image USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "product_discount USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "product_filter USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "product_attribute USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "product_option USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "product_option_value USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "product_related USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "product_special USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "product_to_download USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "product_to_layout USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "product_recurring USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "review USING (`product_id`)
        LEFT JOIN " . DB_PREFIX . "coupon_product USING (`product_id`)
        WHERE `product_id` IN ($updateWhere)");

       if ($this->version == '2.1' || $this->version == '2.3') {
        $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE `query` IN ($deleteSeoUrl)");
       } else {
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE `query` IN ($deleteSeoUrl)");
       }
    } else {
      if ($updateQuantity != '') {
        $updateWhere = array_unique($updateWhere);
        $updateWhere = "'" . implode("', '", $updateWhere) . "'";
        if ($startVars['absence_products'] == 1) {
          $this->db->query("UPDATE " . DB_PREFIX . "product SET 
          quantity = CASE " . $updateQuantity . "ELSE `quantity` END,
          date_modified = CASE " . $updateDateModified . "ELSE `date_modified` END
          WHERE `product_id` IN ($updateWhere)");
        } else {
          $this->db->query("UPDATE " . DB_PREFIX . "product SET 
          quantity = CASE " . $updateQuantity . "ELSE `quantity` END,
          stock_status_id = CASE " . $updateStockStatus . "ELSE `stock_status_id` END,
          date_modified = CASE " . $updateDateModified . "ELSE `date_modified` END
          WHERE `product_id` IN ($updateWhere)");
        }
      }
    }
    
    $this->logText .= 'Удалено/обновлено товаров: ' . $absenceNum . PHP_EOL;
    foreach ($added as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    
    $this->LogWrite();

    if ($startVars['absence_products'] == 0) {
      $this->Output('Успешно. Удалено товаров: ' . $absenceNum, 'success');
    } else {
      $this->Output('Успешно. Товаров с обновленными остатками: ' . $absenceNum, 'success');
    } 
  }


  //=================Атрибуты================


  public function SyncModification() {
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }

    $this->logText = date('H:i:s d.m.Y') . ' Добавление/обновление атрибутов и опций товаров' . PHP_EOL;
    $this->db->query("set session wait_timeout=28800");
    $startVars = $this->GetStartVars(['binding', 'product_offset', 'binding_name', 'sale_price', 'sum_option', 'diff_option_price', 'from_group', 'attr_from_fields', 'empty_field', 'stock_store', 'attr_group', 'not_from_group', 'subtract_option', 'product_time_limit', 'delete_option', 'delete_attribute']);

    $bindingOC = substr($startVars['binding'], 0, strpos($startVars['binding'], '_'));
    $bindingMS = substr($startVars['binding'], strpos($startVars['binding'], '_') + 1);

    $languageId = $this->GetLanguageId();

    // Объявление массивов для хранения данных Мой Склад
    $dataMS = $this->InitialDataMS(['name', 'value', 'product_name', 'binding', 'price', 'quantity', 'description']);

    // Товары
    $ch = $this->CurlInit($startVars['headers']);

    // Определенные категории
    if (isset($startVars['from_group'])) {
      $fromGroup = $this->GetFromGroups($startVars['from_group'], "product", $ch);
      if (isset($startVars['not_from_group'])) {
        $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "category", $ch);
      }
    } else {
      if (isset($startVars['not_from_group'])) {
        $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "product", $ch);
      }
    }

    if (isset($startVars['attr_group'])) {
      $attrGroup = $startVars['attr_group'];
    } else {
      $attrGroup = "Характеристики";
    }

    $offset = 0;
    $productsMS = $this->InitialDataMS(['id', 'name', 'binding', 'price']);

    if (isset($startVars['attr_from_fields'])) {
      $attrFieldsName = explode(";", $startVars['attr_from_fields']);
      $attrFieldsName = array_map('trim', $attrFieldsName);
    }

    if ($this->cron || $startVars['product_offset'] == 0) {
      $offset = 0;
    } else {
      $offset = $startVars['product_offset'] - 1;
    }

    $key = 0;
    do {
      // Получение данных по curl
      $url = 'https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=product;type=bundle&offset=' . $offset;

      // Определенные группы
      if (isset($startVars['from_group'])) {
        if (empty($fromGroup)) {
          continue;
        }
        $url .= '&filter=productFolder=' . implode(";productFolder=", $fromGroup);
      }

      // Определенные группы
      if (isset($startVars['not_from_group']) && !isset($startVars['from_group'])) {
        if (!empty($notFromGroup))
          $url .= '&filter=productFolder!=' . implode(";productFolder!=", $notFromGroup);
      }

      // Игнорировать товары с незаполненным
      if ($startVars['empty_field'] != 1) {
        $url .= "&filter={$bindingMS}!=";
      }
      
      curl_setopt($ch, CURLOPT_URL, $url);
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      foreach ($response['rows'] as $row) {
        // Определенные группы
        if (isset($startVars['not_from_group']) && isset($startVars['from_group'])) {
          if ($this->CheckFromGroup($notFromGroup, $row['pathName'], $row['name'], 'product', true)) {
            continue;
          }
        }

        $productsMS = $this->GetDataMS($row, [$bindingMS, 'name', 'id'], ['binding', 'name', 'id'], $productsMS);
        if (isset($row['salePrices'][$startVars['sale_price']])) {
          $productsMS = $this->GetDataMS($row['salePrices'][$startVars['sale_price']], ['value'], ['price'], $productsMS);
        } else {
          $productsMS = $this->GetDataMS($row['salePrices'][0], ['value'], ['price'], $productsMS);
        }

        // Атрибуты из доп. полей
        $attributesKey = [];
        if (isset($startVars['attr_from_fields'])) {
          if (isset($row['attributes'])) {
            foreach ($row['attributes'] as $key1 => $value1) {
              if (in_array($value1['name'], $attrFieldsName, true)) {
                $dataMS = $this->GetDataMS($row, [$bindingMS, 'name'], ['binding', 'product_name'], $dataMS);
                $dataMS['name'][$key] = $this->db->escape($value1['name']);
                $dataMS['description'][$key] = "Атрибут";
                $dataMS['price'][$key] = 0;
                $dataMS['quantity'][$key] = 0;
                if ($value1['type'] == "customentity") {
                  $dataMS['value'][$key] = $this->db->escape($value1['value']['name']);
                } else {
                  $dataMS['value'][$key] = $this->db->escape($value1['value']);
                }

                $attributesKey[] = $key;
                $key++;
              }
            }
          }
        }

        $productsMS['attributes'][] = $attributesKey;
      }

      if (!$this->cron && $startVars['product_offset'] != 0) {
        break;
      }

      $offset += 1000;
    } while (isset($response['meta']['nextHref']));   

    // Проверка на дубликаты
    $duplicate = $this->DuplicateCheck([$productsMS['binding'], $productsMS['name']], 'product', $startVars['binding_name']);
    if ($duplicate != []) {
      if ($startVars['binding_name'] == 1) {
        foreach ($duplicate as &$value) {
          foreach (array_keys($productsMS['binding'], $value[0]) as $duplicateKey) {
            if ($productsMS['name'][$duplicateKey] == $value[1]) {
              foreach ($productsMS['attributes'][$duplicateKey] as $attrkey) {
                unset($dataMS['binding'][$attrkey], $dataMS['product_name'][$attrkey], $dataMS['name'][$attrkey], $dataMS['description'][$attrkey], $dataMS['description'][$attrkey], $dataMS['price'][$attrkey], $dataMS['quantity'][$attrkey], $dataMS['value'][$attrkey]);
              }
              unset($productsMS['id'][$duplicateKey], $productsMS['name'][$duplicateKey], $productsMS['binding'][$duplicateKey], $productsMS['price'][$duplicateKey], $productsMS['attributes'][$duplicateKey]);
            }
          }
          $value = implode(" ", $value);
        }
      } else {
        foreach ($duplicate as $value) {
          foreach (array_keys($productsMS['binding'], $value) as $duplicateKey) {
            foreach ($productsMS['attributes'][$duplicateKey] as $attrkey) {
              unset($dataMS['binding'][$attrkey], $dataMS['product_name'][$attrkey], $dataMS['name'][$attrkey], $dataMS['description'][$attrkey], $dataMS['description'][$attrkey], $dataMS['price'][$attrkey], $dataMS['quantity'][$attrkey], $dataMS['value'][$attrkey]);
            }
            unset($productsMS['id'][$duplicateKey], $productsMS['name'][$duplicateKey], $productsMS['binding'][$duplicateKey], $productsMS['price'][$duplicateKey], $productsMS['attributes'][$duplicateKey]);
          }
        }
      }

      $duplicate = implode("; ", $duplicate);
      $this->Output("В Моем Складе есть товары-дубликаты по полю {$bindingMS}: " . $duplicate, 'error_warning', false);
    }

    // Модификации
    $offset = 0;
    do {
      // Получение данных по curl
      if (!isset($startVars['stock_store'])) {
        $url = 'https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=variant&offset=' . $offset;
      } else {
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=variant&offset=" . $offset . "&filter=";
        foreach ($startVars['stock_store'] as $storeUrl) {
          $url .= "stockStore=" . $storeUrl . ";";
        }
      }

      // Определенные группы
      if (isset($startVars['from_group'])) {
        if (empty($fromGroup)) {
          continue;
        }
        $url .= '&filter=productFolder=' . implode(";productFolder=", $fromGroup);
      }

      curl_setopt($ch, CURLOPT_URL, $url);
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      foreach ($response['rows'] as $value) {
        $productId = substr($value['product']['meta']['href'], strrpos($value['product']['meta']['href'], '/') + 1);
        $productSearchKey = array_search($productId, $productsMS['id'], true);

        if ($productSearchKey === false) {
          continue;
        }

        foreach ($value['characteristics'] as $key1 => $chValue) {
          $dataMS = $this->GetDataMS($value, ['description'], ['description'], $dataMS);
          $dataMS = $this->GetDataMS($chValue, ['name', 'value'], ['name', 'value'], $dataMS);

          $dataMS['product_name'][$key] = $productsMS['name'][$productSearchKey];
          $dataMS['binding'][$key] = $productsMS['binding'][$productSearchKey];
          $dataMS['quantity'][$key] = $value['quantity'];

          if (isset($value['salePrices'][$startVars['sale_price']])) {
            $dataMS = $this->GetDataMS($value['salePrices'][$startVars['sale_price']], ['value'], ['price'], $dataMS);
          } else {
            $dataMS = $this->GetDataMS($value['salePrices'][0], ['value'], ['price'], $dataMS);
          }

          // Если цены равны, то цена опции = 0
          if ($startVars['diff_option_price'] == 1) {
            $dataMS['price'][$key] -= $productsMS['price'][$productSearchKey];
          }
          
          $key++;
        }
      }

      $offset += 1000;
    } while (isset($response['meta']['nextHref']));

    curl_close($ch);

    // Определение всех товаров, имеющихся в БД
    if ($dataMS['binding'] == []) {
      $this->logText .= 'Товаров с добавленными/обновленными атрибутами и опциями: 0' . PHP_EOL;
      
      $this->LogWrite();
      $this->Output('Успешно. Количество товаров с добавленными/обновленными атрибутами и опциями: 0', 'success');
      exit();
    }

    $implodeBindingMS = htmlspecialchars("'" . implode("', '", $dataMS['binding']) . "'", ENT_COMPAT);
    $query = $this->db->query("SELECT product_id, $bindingOC, name FROM " . DB_PREFIX . "product 
      INNER JOIN " . DB_PREFIX . "product_description USING (`product_id`)
      WHERE `$bindingOC` IN ($implodeBindingMS) AND `language_id` = {$languageId} ORDER BY FIELD ($bindingOC, $implodeBindingMS)");

    $dataSQL = $this->GetDataSQL($query, ['product_id', 'binding', 'name']);
    $duplicate = $this->DuplicateCheck([$dataSQL['binding'], $dataSQL['name']], 'product', $startVars['binding_name']);
    if ($duplicate != []) {
      if ($startVars['binding_name'] == 1) {
        foreach ($duplicate as &$value) {
          foreach (array_keys($dataSQL['binding'], $value[0]) as $key) {
            if ($dataSQL['name'][$key] == $value[1]) {
              unset($dataSQL['product_id'][$key], $dataSQL['binding'][$key], $dataSQL['name'][$key]);
            }
          }
          $value = implode(" ", $value);
        }
      } else {
        foreach ($duplicate as $value) {
          foreach (array_keys($dataSQL['binding'], $value) as $key) {
            unset($dataSQL['product_id'][$key], $dataSQL['binding'][$key], $dataSQL['name'][$key]);
          }
        }
      }

      $duplicate = implode("; ", $duplicate);
      $this->Output("В Опенкарт есть товары-дубликаты по полю {$bindingOC}: " . $duplicate, 'error_warning', false);
    }

    $dataMS['product_id'] = [];
    if ($startVars['binding_name'] == 1) {
      // binding + name
      foreach ($dataMS['binding'] as $key => $value) {
        $find = false;
        foreach (array_keys($dataSQL['binding'], $value) as $value1) {
          if ($dataMS['product_name'][$key] == $dataSQL['name'][$value1]) {
            $find = true;
            $dataMS['product_id'][$key] = $dataSQL['product_id'][$value1];
            break;
          }
        } 
        if (!$find) {
          unset($dataMS['binding'][$key]);
          unset($dataMS['product_name'][$key]);
          unset($dataMS['name'][$key]);
          unset($dataMS['value'][$key]);
          unset($dataMS['price'][$key]);
          unset($dataMS['quantity'][$key]);
          unset($dataMS['description'][$key]);
        }
      }
    } else {
      // Только модель
      foreach ($dataMS['binding'] as $key => $value) {
        $value1 = array_search($value, $dataSQL['binding'], true);
        if (is_int($value1)) {
          $dataMS['product_id'][$key] = $dataSQL['product_id'][$value1];
        } else {
          unset($dataMS['binding'][$key]);
          unset($dataMS['product_name'][$key]);
          unset($dataMS['name'][$key]);
          unset($dataMS['value'][$key]);
          unset($dataMS['price'][$key]);
          unset($dataMS['quantity'][$key]);
          unset($dataMS['description'][$key]);
        }
      }
    }
    
    $dataMS['binding'] = array_values($dataMS['binding']);
    $dataMS['product_name'] = array_values($dataMS['product_name']);
    $dataMS['name'] = array_values($dataMS['name']);
    $dataMS['value'] = array_values($dataMS['value']);
    $dataMS['price'] = array_values($dataMS['price']);
    $dataMS['quantity'] = array_values($dataMS['quantity']);
    $dataMS['description'] = array_values($dataMS['description']);
    $dataMS['product_id'] = array_values($dataMS['product_id']);

    // Добавление группы атрибутов
    $query = $this->db->query("SELECT attribute_group_id, name FROM " . DB_PREFIX . "attribute_group_description WHERE `language_id` = {$languageId}");

    $dataSQL = $this->GetDataSQL($query, ['attribute_group_id', 'name']);

    // Определение id группы атрибутов
    $groupIndex = array_search($attrGroup, $dataSQL['name'], true);
    if (is_int($groupIndex)) {
      $groupId = $dataSQL['attribute_group_id'][$groupIndex];
    } else {
      if (count($dataSQL['attribute_group_id']) > 0) {
        $groupId = max($dataSQL['attribute_group_id']) + 1;
      } else {
        $groupId = 1;
      }
      
      // Добавление группы атрибутов если она не добавлена
      $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group (`attribute_group_id`, `sort_order`) VALUES ('$groupId', '0')");
      $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group_description (`attribute_group_id`, `language_id`, `name`) VALUES ('$groupId', '$languageId', '$attrGroup')");
    }

    // Определение id последнего атрибута
    $query = $this->db->query("SELECT attribute_id, name FROM " . DB_PREFIX . "attribute_description WHERE `language_id` = {$languageId}");
    $dataSQLAttr = $this->GetDataSQL($query, ['attribute_id', 'name']);

    if (count($dataSQLAttr['attribute_id']) == 0) {
      $lastAttrId = 0;
    } else {
      $lastAttrId = max($dataSQLAttr['attribute_id']);
    }

    // Определение id последнего значения опции
    $query = $this->db->query("SELECT option_id, name FROM " . DB_PREFIX . "option_description WHERE `language_id` = {$languageId}");
    $dataSQLOpt = $this->GetDataSQL($query, ['option_id', 'name']);

    if (count($dataSQLOpt['option_id']) == 0) {
      $lastOptId = 0;
    } else {
      $lastOptId = max($dataSQLOpt['option_id']);
    }

    // Определение id последнего значения опции
    $query = $this->db->query("SELECT option_value_id, option_id, ovd.name AS value_name, od.name AS option_name
      FROM " . DB_PREFIX . "option_value_description ovd 
      INNER JOIN " . DB_PREFIX . "option_description od USING (`option_id`) WHERE ovd.language_id = {$languageId} AND od.language_id = {$languageId}");
    $dataSQLOptVal = $this->GetDataSQL($query, ['option_value_id', 'option_id', 'value_name', 'option_name']);

    if (count($dataSQLOptVal['option_value_id']) == 0) {
      $lastOptValId = 0;
    } else {
      $lastOptValId = max($dataSQLOptVal['option_value_id']);
    }

    // Если атрибута нет в БД, то добавление/обновление атрибутов не происходит
    $attrNameAdded = [];
    $insertAttr = [];
    $insertAttrDescription = [];
    $attrId = [];

    $optNameAdded = [];
    $insertOpt = [];
    $insertOptDescription = [];
    $optId = [];
    $optValNameAdded = [];
    $insertOptVal = [];
    $insertOptValDescription = [];
    $optValId = [];
    $added = [];

    $prodAndOptIdMS = [];
    $prodAndOptValIdMS = [];
    $prodAndAttrIdMS = [];
    
    foreach ($dataMS['name'] as $key => $value) {
      // Формирование запросов на добавление опции (если опция не добавлена)
      if ($dataMS['description'][$key] != 'Атрибут') {
        // option
        $keySQL = array_search($value, $dataSQLOpt['name'], true);
        if (is_bool($keySQL)) {
          if (!isset($optNameAdded[$value])) {
            $lastOptId++;
            $optNameAdded[$value] = $lastOptId;
            array_push($optId, $lastOptId);
            $prodAndOptIdMS[] = sprintf("%s_%s", $dataMS['product_id'][$key], $lastOptId);
            array_push($insertOpt, "('$lastOptId', 'select', '0')");
            array_push($insertOptDescription, "('$lastOptId', '$languageId', '$value')");
          } else {
            array_push($optId, $optNameAdded[$value]);
            $prodAndOptIdMS[] = sprintf("%s_%s", $dataMS['product_id'][$key], $optNameAdded[$value]);
          }
        } else {
          array_push($optId, $dataSQLOpt['option_id'][$keySQL]);
          $prodAndOptIdMS[] = sprintf("%s_%s", $dataMS['product_id'][$key], $dataSQLOpt['option_id'][$keySQL]);
        }

        // option_value
        $find = false;
        foreach (array_keys($dataSQLOptVal['value_name'], $dataMS['value'][$key]) as $value1) {
          if ($value == $dataSQLOptVal['option_name'][$value1]) {
            $find = true;
            array_push($optValId, $dataSQLOptVal['option_value_id'][$value1]);
            $prodAndOptValIdMS[] = sprintf("%s_%s", $dataMS['product_id'][$key], $dataSQLOptVal['option_value_id'][$value1]);
            break;
          }
        }
        if (!$find) {
          if (!isset($optValNameAdded[sprintf("%s_%s", $value, $dataMS['value'][$key])])) {
            $lastOptValId++;
            $optValNameAdded[sprintf("%s_%s", $value, $dataMS['value'][$key])] = $lastOptValId;
            array_push($optValId, $lastOptValId);
            $prodAndOptValIdMS[] = sprintf("%s_%s", $dataMS['product_id'][$key], $lastOptValId);
            array_push($insertOptVal, sprintf("('%s', '%s', '0')", $lastOptValId, $optId[$key]));
            array_push($insertOptValDescription, sprintf("('%s', '$languageId', '%s', '%s')", $lastOptValId, $optId[$key], $dataMS['value'][$key]));
          } else {
            array_push($optValId, $optValNameAdded[sprintf("%s_%s", $value, $dataMS['value'][$key])]);
            $prodAndOptValIdMS[] = sprintf("%s_%s", $dataMS['product_id'][$key], $optValNameAdded[sprintf("%s_%s", $value, $dataMS['value'][$key])]);
          }
        }
        array_push($attrId, 0);

      } else {
        // Формирование запросов на добавление атрибутов (если атрибут не добавлен)
        $keySQL = array_search($value, $dataSQLAttr['name'], true);
        if (is_bool($keySQL)) {
          if (!isset($attrNameAdded[$value])) {
            $lastAttrId++;
            $attrNameAdded[$value] = $lastAttrId;
            array_push($attrId, $lastAttrId);
            $prodAndAttrIdMS[] = sprintf("%s_%s", $dataMS['product_id'][$key], $lastAttrId);
            array_push($insertAttr, "('$lastAttrId', '$groupId', '0')");
            array_push($insertAttrDescription, "('$lastAttrId', '$languageId', '$value')");
          } else {
            array_push($attrId, $attrNameAdded[$value]);
            $prodAndAttrIdMS[] = sprintf("%s_%s", $dataMS['product_id'][$key], $attrNameAdded[$value]);
          }
        } else {
          array_push($attrId, $dataSQLAttr['attribute_id'][$keySQL]);
          $prodAndAttrIdMS[] = sprintf("%s_%s", $dataMS['product_id'][$key], $dataSQLAttr['attribute_id'][$keySQL]);
        }
        array_push($optId, 0);
        array_push($optValId, 0);
      } 
    }

    $prodAndOptIdMS = array_unique($prodAndOptIdMS);
    $prodAndOptValIdMS = array_unique($prodAndOptValIdMS);
    $prodAndAttrIdMS = array_unique($prodAndAttrIdMS);

    // Отправление запросов на добавление атрибутов
    if (count($insertAttr) != 0) {
      $insertAttr = implode(", " , $insertAttr);
      $insertAttrDescription = implode(", " , $insertAttrDescription);
      
      $this->db->query("INSERT INTO " . DB_PREFIX . "attribute (`attribute_id`, `attribute_group_id`, `sort_order`) VALUES $insertAttr");
      $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description (`attribute_id`, `language_id`, `name`) VALUES $insertAttrDescription");
    }

    // Отправление запросов на добавление опций
    if (count($insertOpt) != 0) {
      $insertOpt = implode(", " , $insertOpt);
      $insertOptDescription = implode(", " , $insertOptDescription);

      $this->db->query("INSERT INTO `" . DB_PREFIX . "option` (`option_id`, `type`, `sort_order`) VALUES $insertOpt");
      $this->db->query("INSERT INTO " . DB_PREFIX . "option_description (`option_id`, `language_id`, `name`) VALUES $insertOptDescription");
    }

    if (count($insertOptVal) != 0) {
      $insertOptVal = implode(", " , $insertOptVal);
      $insertOptValDescription = implode(", " , $insertOptValDescription);

      $this->db->query("INSERT INTO " . DB_PREFIX . "option_value (`option_value_id`, `option_id`, `sort_order`) VALUES $insertOptVal");
      $this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description (`option_value_id`, `language_id`, `option_id`, `name`) VALUES $insertOptValDescription");
    }

    $implodeProductId = "'" . implode("', '", $dataMS['product_id']) . "'";

    // Определение product_option_id
    $query = $this->db->query("SELECT product_option_id, product_id, option_id FROM " . DB_PREFIX . "product_option WHERE product_id IN ($implodeProductId)");

    $allProductOpt = [];
    $allProductOptId = [];
    $deleteProdOpt = [];
    foreach ($query->rows as $key => $value) {
      array_push($allProductOpt, sprintf("%s_%s", $value['product_id'], $value['option_id']));
      array_push($allProductOptId, $this->db->escape($value['product_option_id']));
      if ($startVars['delete_option'] == 1 && !in_array($value['product_id'] . "_" . $value['option_id'], $prodAndOptIdMS)) {
        $deleteProdOpt[] = $value['product_option_id'];
      }
    }

    $query = $this->db->query("SELECT MAX(product_option_id) FROM " . DB_PREFIX . "product_option");
    if (count($query->rows) != 0) {
      $lastProductOptId = $query->row['MAX(product_option_id)'];
    } else {
      $lastProductOptId = 0;
    }

    $productOptId = [];
    $insertProductOpt = [];
    $prodOptAdded = [];

    foreach ($optId as $key => $value) {
      if ($value == 0) {
        array_push($productOptId, 0);
        continue;
      }

      if (!isset($prodOptAdded[sprintf("%s_%s", $dataMS['product_id'][$key], $value)])) {
        $keySQL = array_search(sprintf("%s_%s", $dataMS['product_id'][$key], $value), $allProductOpt, true);
        if ($keySQL !== false) {
          array_push($productOptId, $allProductOptId[$keySQL]);
        } else {
          $lastProductOptId++;
          $prodOptAdded[sprintf("%s_%s", $dataMS['product_id'][$key], $value)] = $lastProductOptId;
          array_push($productOptId, $lastProductOptId);
          array_push($insertProductOpt, sprintf("('%s', '%s', '%s', '', '1')", $lastProductOptId, $dataMS['product_id'][$key], $value));
        }
      } else {
        array_push($productOptId, $prodOptAdded[sprintf("%s_%s", $dataMS['product_id'][$key], $value)]);
      }
    }

    if (count($insertProductOpt) != 0) {
      $insertProductOpt = implode(", " , $insertProductOpt);
      $this->db->query("INSERT INTO " . DB_PREFIX . "product_option (`product_option_id`, `product_id`, `option_id`, `value`, `required`) VALUES $insertProductOpt");
    }

    // Определение всех значений и id атрибутов и id товаров, которые имеются в БД
    $query = $this->db->query("SELECT product_id, attribute_id, text FROM " . DB_PREFIX . "product_attribute WHERE product_id IN ($implodeProductId)");

    $allProductAttr = [];
    $allTextAttr = [];
    $deleteProdAttr = [];
    foreach ($query->rows as $key => $value) {
      array_push($allProductAttr, sprintf("%s_%s", $value['product_id'], $value['attribute_id']));
      array_push($allTextAttr, htmlspecialchars_decode($this->db->escape($value['text'], ENT_COMPAT)));
      if ($startVars['delete_attribute'] == 1 && !in_array($value['product_id'] . "_" . $value['attribute_id'], $prodAndAttrIdMS)) {
        $deleteProdAttr[] = $value['product_id'] . "_" . $value['attribute_id'];
      }
    }

    // Определение всех значений и id опций и id товаров, которые имеются в БД
    $query = $this->db->query("SELECT product_option_value_id, product_id, option_value_id, quantity, price FROM " . DB_PREFIX . "product_option_value WHERE product_id IN ($implodeProductId)");

    $allProductOptValue = [];
    $allPriceOpt = [];
    $allQuantOpt = [];
    $deleteProdOptVal = [];
    foreach ($query->rows as $key => $value) {
      array_push($allProductOptValue, sprintf("%s_%s", $value['product_id'], $value['option_value_id']));
      array_push($allPriceOpt, $this->db->escape($value['price']));
      array_push($allQuantOpt, $this->db->escape($value['quantity']));
      if ($startVars['delete_option'] == 1 && !in_array($value['product_id'] . "_" . $value['option_value_id'], $prodAndOptValIdMS)) {
        $deleteProdOptVal[] = $value['product_option_value_id'];
      }
    }

    if (count($deleteProdAttr) != 0) {
      $deleteProdAttr = "'" . implode("', '", $deleteProdAttr) . "'";
      $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE CONCAT(product_id, '_', attribute_id) IN ($deleteProdAttr)");
    }

    if (count($deleteProdOpt) != 0) {
      $deleteProdOpt = "'" . implode("', '", $deleteProdOpt) . "'";
      $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_option_id IN ($deleteProdOpt)");
    }

    if (count($deleteProdOptVal) != 0) {
      $deleteProdOptVal = "'" . implode("', '", $deleteProdOptVal) . "'";
      $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_option_value_id IN ($deleteProdOptVal)");
    }

    // Формирование запросов для обноваления/добавления записей 
    // в таблицу product_attribute/product_option_value
    $modifUpdatedNum = [];
    $updateProductAttrCase = "";
    $insertProductAttr = [];
    $prodAndAttrId = [];

    $updateProductOptCase1 = "";
    $updateProductOptCase2 = "";
    $insertProductOpt = [];
    $prodAndOptId = [];
    $added = [];

    foreach ($dataMS['value'] as $key => $value) {
      if ($attrId[$key] == 0) {
        if (in_array(sprintf("%s_%s", $dataMS['product_id'][$key], $optValId[$key]), $prodAndOptId, true)) {
          $copyKey = array_search(sprintf("%s_%s", $dataMS['product_id'][$key], $optValId[$key]), $prodAndOptId, true);
          $dataMS['quantity'][$copyKey] += $dataMS['quantity'][$key];
        }
        array_push($prodAndOptId, sprintf("%s_%s", $dataMS['product_id'][$key], $optValId[$key]));
      }
    }

    $prodAndOptId = [];

    // Суммирование опций
    $prodQuantityId = [];
    $prodQuantity = [];

    foreach ($dataMS['value'] as $key => $value) {
      // Опции
      if ($attrId[$key] == 0) {
        $prodValKey = array_search(sprintf("%s_%s", $dataMS['product_id'][$key], $optValId[$key]), $prodAndOptId, true);
        if ($prodValKey !== false) {
          continue;
        }

        $prodAndOptId[$key] = sprintf("%s_%s", $dataMS['product_id'][$key], $optValId[$key]);
        $keySQL = array_search(sprintf("%s_%s", $dataMS['product_id'][$key], $optValId[$key]), $allProductOptValue, true);
        if (is_int($keySQL)) {
          if ($dataMS['price'][$key] != $allPriceOpt[$keySQL] || $dataMS['quantity'][$key] != $allQuantOpt[$keySQL]) {
            
            if ($startVars['sum_option'] == 1 && $dataMS['quantity'][$key] != $allQuantOpt[$keySQL]) {
              array_push($prodQuantityId, $dataMS['product_id'][$key]);
            }

            $updateProductOptCase1 .= sprintf("WHEN `product_id` = '%s' AND `option_value_id` = '%s' THEN '%s' ", $dataMS['product_id'][$key], $optValId[$key], $dataMS['price'][$key]);
            $updateProductOptCase2 .= sprintf("WHEN `product_id` = '%s' AND `option_value_id` = '%s' THEN '%s' ", $dataMS['product_id'][$key], $optValId[$key], $dataMS['quantity'][$key]);
            
            array_push($modifUpdatedNum, $dataMS['product_name'][$key]);
            array_push($added, sprintf('%s_%s', $dataMS['binding'][$key], $dataMS['product_name'][$key]));
          }
        } else {
          if ($startVars['sum_option'] == 1) {
            array_push($prodQuantityId, $dataMS['product_id'][$key]);
          }

          if ($startVars['subtract_option'] == 1) {
            $subtract = 1;
          } else {
            $subtract = 0;
          }
          array_push($insertProductOpt, sprintf("('%s', '%s', '%s', '%s', '%s', '%s', '%s', '+', '0', '+', '0', '+')", $productOptId[$key], $dataMS['product_id'][$key], $optId[$key], $optValId[$key], $dataMS['quantity'][$key], $subtract, $dataMS['price'][$key]));
          array_push($modifUpdatedNum, $dataMS['product_name'][$key]);
          array_push($added, sprintf('%s_%s', $dataMS['binding'][$key], $dataMS['product_name'][$key]));
        }

      // Атрибуты
      } else {
        if (in_array(sprintf("%s_%s", $dataMS['product_id'][$key], $attrId[$key]), $prodAndAttrId, true)) {
          continue;
        }
        array_push($prodAndAttrId, sprintf("%s_%s", $dataMS['product_id'][$key], $attrId[$key]));
        $keySQL = array_search(sprintf("%s_%s", $dataMS['product_id'][$key], $attrId[$key]), $allProductAttr, true);
        if (is_int($keySQL)) {
          if ($dataMS['value'][$key] != $allTextAttr[$keySQL]) {
            $updateProductAttrCase .= sprintf("WHEN `product_id` = '%s' AND `attribute_id` = '%s' THEN '%s' ", $dataMS['product_id'][$key], $attrId[$key], htmlspecialchars($dataMS['value'][$key], ENT_COMPAT));
            array_push($modifUpdatedNum, $dataMS['product_name'][$key]);
            array_push($added, sprintf('%s_%s', $dataMS['binding'][$key], $dataMS['product_name'][$key]));
          }
        } else {
          array_push($insertProductAttr, sprintf("('%s', '%s', '$languageId', '%s')", $dataMS['product_id'][$key], $attrId[$key], htmlspecialchars($dataMS['value'][$key], ENT_COMPAT)));
          array_push($modifUpdatedNum, $dataMS['product_name'][$key]);
          array_push($added, sprintf('%s_%s', $dataMS['binding'][$key], $dataMS['product_name'][$key]));
        }
      }
    }

    // Отправление запросов для обновления/добавления записей 
    // в таблицу product_attribute и product_option_value
    $insertProductAttr = implode(", ", $insertProductAttr);

    if ($updateProductAttrCase != '') {
      $this->db->query("UPDATE " . DB_PREFIX . "product_attribute SET 
      text = CASE " . $updateProductAttrCase . "ELSE `text` END");
    }

    if ($insertProductAttr != '') {
      $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute (`product_id`, `attribute_id`, `language_id`, `text`) VALUES $insertProductAttr");
    }

    $insertProductOpt = implode(", ", $insertProductOpt);
    if ($updateProductOptCase1 != '') {
      $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET 
      price = CASE " . $updateProductOptCase1 . "ELSE `price` END,
      quantity = CASE " . $updateProductOptCase2 . "ELSE `quantity` END");
    }

    if ($insertProductOpt != '') {
      $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value (`product_option_id`, `product_id`, `option_id`, `option_value_id`, `quantity`, `subtract`, `price`, `price_prefix`, `points`, `points_prefix`, `weight`, `weight_prefix`) VALUES $insertProductOpt");
    }

    // Суммирование остатков опций
    $prodQuantityId = array_unique($prodQuantityId);
    if ($startVars['sum_option'] == 1 && count($prodQuantityId) != 0) {
      $updateProductQuantity = '';
      $implodeProdQuantityId = implode(', ', $prodQuantityId);

      // Суммирование остатков опций
      if ($startVars['sum_option'] == 1) {
        $query = $this->db->query("SELECT pov.product_id, SUM(pov.quantity) quantity FROM " . DB_PREFIX . "product_option_value pov INNER JOIN " . DB_PREFIX . "product_option po USING (product_option_id) WHERE pov.product_id IN ({$implodeProdQuantityId}) AND po.required = 1 GROUP BY pov.product_id, pov.option_id");

        foreach ($query->rows as $key => $value) {
          $dataOptionSQL[$value['product_id']][] = $value['quantity'];
        }
      }

      foreach ($prodQuantityId as $key => $value) {
        if (isset($dataOptionSQL[$value])) {
          $prodQuantity[$key] = min($dataOptionSQL[$value]);
        }

        $updateProductQuantity .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $value, $prodQuantity[$key]);
      }

      if ($updateProductQuantity != '') {
        $this->db->query("UPDATE " . DB_PREFIX . "product SET 
        quantity = CASE " . $updateProductQuantity . "END WHERE `product_id` IN ($implodeProdQuantityId)");
      }
    }

    $modifUpdatedNum = array_unique($modifUpdatedNum);
    $added = array_unique($added);
    
    $this->logText .= 'Товаров с добавленными/обновленными атрибутами и опциями: ' . count($modifUpdatedNum) . PHP_EOL;
    foreach ($added as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    
    $this->LogWrite();
    $this->Output('Успешно. Количество товаров с добавленными/обновленными атрибутами и опциями: ' . count($modifUpdatedNum), 'success');
  }


  //=================Изображения================


  public function SyncImage() { 
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }

    $this->logText = date('H:i:s d.m.Y') . ' Добавление/обновление изображений товаров' . PHP_EOL;

    $this->db->query("set session wait_timeout=28800");
    $startVars = $this->GetStartVars(['binding', 'product_offset', 'binding_name', 'from_group', 'delete_img', 'empty_field', 'not_from_group', 'product_time_limit']);

    // Измененные за последние минуты
    if (isset($startVars['product_time_limit'])) {
      $minuteLimit = $startVars['product_time_limit'];

      if (strpos($minuteLimit, '.') !== false || $minuteLimit < 0) {
        $this->Output('Ошибка! Количество минут должно быть целым неотрицательным числом', 'error_warning');
        exit();
      }

      $dateLimit = new DateTime();
      $dateLimit->modify("- $minuteLimit minutes");
      $dateLimit = $dateLimit->format("Y-m-d H:i:s");
    }

    $bindingOC = substr($startVars['binding'], 0, strpos($startVars['binding'], '_'));
    $bindingMS = substr($startVars['binding'], strpos($startVars['binding'], '_') + 1);

    $languageId = $this->GetLanguageId();
    
    // Создание директории для изображений, если она не создана
    if (!is_dir(DIR_IMAGE . 'catalog/demo')) {
      mkdir(DIR_IMAGE . 'catalog/demo');
    }
    if (!is_dir(DIR_IMAGE . 'catalog/demo/syncms')) {
      mkdir(DIR_IMAGE . 'catalog/demo/syncms');
    }

    $dataMS = $this->InitialDataMS(['binding', 'name', 'image', 'images', 'result_href']);

    if ($this->cron || $startVars['product_offset'] == 0) {
      $offset = 0;
    } else {
      $offset = $startVars['product_offset'] - 1;
    }

    $ch = $this->CurlInit($startVars['headers']);

    // Определенные категории
    if (isset($startVars['from_group'])) {
      $fromGroup = $this->GetFromGroups($startVars['from_group'], "product", $ch);
      if (isset($startVars['not_from_group'])) {
        $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "category", $ch);
      }
    } else {
      if (isset($startVars['not_from_group'])) {
        $notFromGroup = $this->GetFromGroups($startVars['not_from_group'], "product", $ch);
      }
    }

    $key = 0;

    do {
      $url = 'https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=product;type=bundle&offset=' . $offset . '&limit=100&expand=images';

      // Определенные группы
      if (isset($startVars['from_group'])) {
        if (empty($fromGroup)) {
          continue;
        }
        $url .= '&filter=productFolder=' . implode(";productFolder=", $fromGroup);
      }

      // Определенные группы
      if (isset($startVars['not_from_group']) && !isset($startVars['from_group'])) {
        if (!empty($notFromGroup))
          $url .= '&filter=productFolder!=' . implode(";productFolder!=", $notFromGroup);
      }

      // Игнорировать товары с незаполненным
      if ($startVars['empty_field'] != 1) {
        $url .= "&filter={$bindingMS}!=";
      }

      if (isset($startVars['product_time_limit'])) {
        $url .= "&filter=updated" . urlencode(">=" . $dateLimit);
      }

      curl_setopt($ch, CURLOPT_URL, $url);
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);     

      foreach ($response['rows'] as $row) {
        // Определенные группы
        if (isset($startVars['not_from_group']) && isset($startVars['from_group'])) {
          if ($this->CheckFromGroup($notFromGroup, $row['pathName'], $row['name'], 'product', true)) {
            continue;
          }
        }

        $dataMS = $this->GetDataMS($row, [$bindingMS, 'name'], ['binding', 'name'],$dataMS);

        if ($row['images']['meta']['size'] != 0) {
          foreach ($row['images']['rows'] as $keyImage => $valueImage) {
            if (isset($valueImage['filename']) && $valueImage['filename'] != '') {
               $imgName = $this->db->escape($valueImage['filename']);
               if (strrpos($imgName, ".") !== false)
                $imgFormat = substr($imgName, strrpos($imgName, "."));
               else
                $imgFormat = ".png";
               $imgName = sprintf("%s_%s%s", $row["id"], $keyImage, $imgFormat);
            } else {
               $imgName = sprintf("%s_%s.jpg", $row["id"], $keyImage);
            }

            $imgName = str_replace(array(":", "/", "\\", "%", "'"), "", $imgName);
            $sizeMS = $valueImage['size'];
            $path = DIR_IMAGE . 'catalog/demo/syncms/' . $imgName;

            if ($keyImage == 0) {
              $dataMS['image'][$key] = "catalog/demo/syncms/" . $imgName;
            } else {
              $dataMS['images'][$key][] = "catalog/demo/syncms/" . $imgName;
            }
            
            if (!file_exists($path) || filesize($path) != $sizeMS) {
              $dataMS['result_href'][$key][$keyImage] = $valueImage['meta']['downloadHref'];
            }
          }
        } else {
          $dataMS['image'][$key] = "";
        }

        $key++;
      }

      if (!$this->cron && $startVars['product_offset'] != 0) {
        if ($offset >= $startVars['product_offset'] + 899)
          break;
      }

      $offset += 100;
    } while (isset($response['meta']['nextHref']));

    // Проверка на дубликаты
    $duplicate = $this->DuplicateCheck([$dataMS['binding'], $dataMS['name']], 'product', $startVars['binding_name']);
    if ($duplicate != []) {
      if ($startVars['binding_name'] == 1) {
        foreach ($duplicate as &$value) {
          foreach (array_keys($dataMS['binding'], $value[0]) as $key) {
            if ($dataMS['name'][$key] == $value[1]) {
              unset($dataMS['binding'][$key], $dataMS['name'][$key], $dataMS['image'][$key], $dataMS['images'][$key], $dataMS['result_href'][$key]);
            }
          }
          $value = implode(" ", $value);
        }
      } else {
        foreach ($duplicate as $value) {
          foreach (array_keys($dataMS['binding'], $value) as $key) {
           unset($dataMS['binding'][$key], $dataMS['name'][$key], $dataMS['image'][$key], $dataMS['images'][$key], $dataMS['result_href'][$key]);
          }
        }
      }

      $duplicate = implode("; ", $duplicate);
      $this->Output("В Моем Складе есть товары-дубликаты по полю {$bindingMS}: " . $duplicate, 'error_warning', false);
    }

    $implodeBindingMS = htmlspecialchars("'" . implode("', '", $dataMS['binding']) . "'", ENT_COMPAT);
    $query = $this->db->query("SELECT name, $bindingOC, image, product_id FROM " . DB_PREFIX . "product
     INNER JOIN " . DB_PREFIX . "product_description USING (`product_id`) 
     WHERE `$bindingOC` IN ($implodeBindingMS) AND `language_id` = {$languageId} ORDER BY FIELD (`$bindingOC`, $implodeBindingMS)");
    $dataSQL = $this->GetDataSQL($query, ['name', 'binding', 'image', 'product_id']);

    // Проверка на дубликаты
    $duplicate = $this->DuplicateCheck([$dataSQL['binding'], $dataSQL['name']], 'product', $startVars['binding_name']);
    if ($duplicate != []) {
      if ($startVars['binding_name'] == 1) {
        foreach ($duplicate as &$value) {
          foreach (array_keys($dataSQL['binding'], $value[0]) as $key) {
            if ($dataSQL['name'][$key] == $value[1]) {
              unset($dataSQL['product_id'][$key], $dataSQL['binding'][$key], $dataSQL['name'][$key], $dataSQL['image'][$key]);
            }
          }
          $value = implode(" ", $value);
        }
      } else {
        foreach ($duplicate as $value) {
          foreach (array_keys($dataSQL['binding'], $value) as $key) {
            unset($dataSQL['product_id'][$key], $dataSQL['binding'][$key], $dataSQL['name'][$key], $dataSQL['image'][$key]);
          }
        }
      }

      $duplicate = implode("; ", $duplicate);
      $this->Output("В Опенкарт есть товары-дубликаты по полю {$bindingOC}: " . $duplicate, 'error_warning', false);
    }

    $query = $this->db->query("SELECT product_id, image, sort_order FROM " . DB_PREFIX . "product_image");
    $dataImagesSQL = $this->GetDataSQL($query, ['product_id', 'image', 'sort_order']);

    foreach ($dataImagesSQL['product_id'] as $key => $value) {
      $dataImagesSQL['images'][$value][$dataImagesSQL['sort_order'][$key]] = $dataImagesSQL['image'][$key];
    }

    $updateProductCase = '';
    $updateWhere = [];
    $insertProductImage = [];
    $deleteWhere = [];
    $dataMS['product_id'] = [];
    $added = [];

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    if ($startVars['binding_name'] == 1) {
      // binding + name
      foreach ($dataMS['binding'] as $key => $value) {
        foreach (array_keys($dataSQL['binding'], $value) as $value1) {
          if ($dataMS['name'][$key] == $dataSQL['name'][$value1]) {
            $dataMS['product_id'][$key] = $dataSQL['product_id'][$value1];

            if (isset($dataMS['result_href'][$key])) {
              foreach ($dataMS['result_href'][$key] as $hrefKey => $hrefValue) {
                curl_setopt($ch, CURLOPT_URL, $hrefValue);
                if ($hrefKey == 0)
                  file_put_contents(DIR_IMAGE . $dataMS['image'][$key], curl_exec($ch));
                else
                  file_put_contents(DIR_IMAGE . $dataMS['images'][$key][$hrefKey-1], curl_exec($ch));
              }
            }

            // Первое изображение
            if ($dataMS['image'][$key] != $dataSQL['image'][$value1]) {
              $delete = true;
              if ($startVars['delete_img'] == 0 && $dataMS['image'][$key] == '')
                $delete = false;
              if ($delete) {
                $updateProductCase .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$key], $dataMS['image'][$key]);
                array_push($updateWhere, $dataMS['product_id'][$key]);
                array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
              }
            }

            // Остальные изображения
            if (isset($dataMS['images'][$key])) {
              if (isset($dataImagesSQL['images'][$dataMS['product_id'][$key]]) && 
                  $dataMS['images'][$key] == $dataImagesSQL['images'][$dataMS['product_id'][$key]]) {
                continue;
              }

              foreach ($dataMS['images'][$key] as $imageKey => $image) {
                array_push($insertProductImage, sprintf("('%s', '%s', '%s')", $dataMS['product_id'][$key], $image, $imageKey));
                array_push($deleteWhere, $dataMS['product_id'][$key]);
                array_push($added, sprintf('%s_%s', $dataMS['binding'][$key], $dataMS['name'][$key]));
              } 
            } else {
              if (isset($dataImagesSQL['images'][$dataMS['product_id'][$key]]) &&
                  $startVars['delete_img'] == 1) {
                array_push($deleteWhere, $dataMS['product_id'][$key]);
                array_push($added, sprintf('%s_%s', $dataMS['binding'][$key], $dataMS['name'][$key]));
              }
            }

            break;
          }
        } 
      }
    } else {
      // Только модель
      foreach ($dataMS['binding'] as $key => $value) {
        $value1 = array_search($value, $dataSQL['binding'], true);
        if (is_int($value1)) {
          $dataMS['product_id'][$key] = $dataSQL['product_id'][$value1];
          
          if (isset($dataMS['result_href'][$key])) {
            foreach ($dataMS['result_href'][$key] as $hrefKey => $hrefValue) {
              curl_setopt($ch, CURLOPT_URL, $hrefValue);
              if ($hrefKey == 0)
                file_put_contents(DIR_IMAGE . $dataMS['image'][$key], curl_exec($ch));
              else
                file_put_contents(DIR_IMAGE . $dataMS['images'][$key][$hrefKey-1], curl_exec($ch));
            }
          }

          // Первое изображения
          if ($dataMS['image'][$key] != $dataSQL['image'][$value1]) {
            $delete = true;
            if ($startVars['delete_img'] == 0 && $dataMS['image'][$key] == '')
              $delete = false;
            if ($delete) {
              $updateProductCase .= sprintf("WHEN `product_id` = '%s' THEN '%s' ", $dataMS['product_id'][$key], $dataMS['image'][$key]);
              array_push($updateWhere, $dataMS['product_id'][$key]);
              array_push($added, sprintf('%s_%s', $value, $dataMS['name'][$key]));
            }
          }

          // Остальные изображения
          if (isset($dataMS['images'][$key])) {
            if (isset($dataImagesSQL['images'][$dataMS['product_id'][$key]]) && 
                $dataMS['images'][$key] == $dataImagesSQL['images'][$dataMS['product_id'][$key]]) {
              continue;
            }

            foreach ($dataMS['images'][$key] as $imageKey => $image) {
              array_push($insertProductImage, sprintf("('%s', '%s', '%s')", $dataMS['product_id'][$key], $image, $imageKey));
              array_push($deleteWhere, $dataMS['product_id'][$key]);
              array_push($added, sprintf('%s_%s', $dataMS['binding'][$key], $dataMS['name'][$key]));
            } 
          } else {
            if (isset($dataImagesSQL['images'][$dataMS['product_id'][$key]]) &&
                $startVars['delete_img'] == 1) {
              array_push($deleteWhere, $dataMS['product_id'][$key]);
              array_push($added, sprintf('%s_%s', $dataMS['binding'][$key], $dataMS['name'][$key]));
            }
          }
        }
      }
    }

    curl_close($ch);

    // Отправление запроса на обновление изображения
    if ($updateProductCase != '') {
      $updateWhere = "'" . implode("', '", $updateWhere) . "'";
      $this->db->query("UPDATE " . DB_PREFIX . "product SET 
      image = CASE " . $updateProductCase . "END
      WHERE `product_id` IN ($updateWhere)");
    }

    if (count($deleteWhere) != 0) {
      $deleteWhere = array_unique($deleteWhere);
      $deleteWhere = implode(", ", $deleteWhere);
      $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE `product_id` IN ($deleteWhere)");
    }

    // Отправление запроса на добавление нескольких изображений
    if (count($insertProductImage) != 0) {
      $insertProductImage = implode(", ", $insertProductImage);
      $this->db->query("INSERT INTO " . DB_PREFIX . "product_image (`product_id`, `image`, `sort_order`) VALUES $insertProductImage");
    }
    
    $added = array_unique($added);

    $this->logText .= 'Товаров с добавленными/обновленными изображениями: ' . count($added) . PHP_EOL;
    foreach ($added as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    
    $this->LogWrite();
    $this->Output('Успешно. Количество товаров с обновленными изображениями: ' . count($added), 'success');
  }


  public function OrderAdd() {
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }

    $this->db->query("set session wait_timeout=28800");

    $this->logText = date('H:i:s d.m.Y') . ' Добавление заказов' . PHP_EOL;

    $startVars = $this->GetStartVars(['organization', 'store', 'binding_name', 'order_prefix', 'binding', 'order_binding', 'product_reserve', 'shipping_add', 'conduct_order', 'order_day_limit', 'quick_add', 'nds', 'saleschannel', 'default_email', 'agent_type', 'comment_info', 'not_skip_order', 'shipping_binding']);

    $this->CreateLock("OrderAdd");

    $defaultEmail = [];
    if (isset($startVars['default_email'])) {
      $defaultEmail = explode(";", $startVars['default_email']);
      $defaultEmail = array_map('trim', $defaultEmail);
    }

    if (isset($startVars['order_day_limit'])) {
      $dayNumLimit = $startVars['order_day_limit'];

      if (strpos($dayNumLimit, '.') !== false || $dayNumLimit < 0) {
        $this->Output('Ошибка! Количество дней должно быть целым неотрицательным числом', 'error_warning');
        exit();
      }

      $dateLimit = new DateTime();
      $dateLimit->modify("- $dayNumLimit day");
      $dateLimit = $dateLimit->format("Y-m-d");
    }

    if ($startVars['order_binding'] == 'number')
      $orderBinding = 'name';
    else
      $orderBinding = 'description';

    $bindingOC = substr($startVars['binding'], 0, strpos($startVars['binding'], '_'));
    $bindingMS = substr($startVars['binding'], strpos($startVars['binding'], '_') + 1);

    if (!isset($startVars['order_prefix'])) {
      $startVars['order_prefix'] = '';
    }
    $dataMS = $this->InitialDataMS(['name', 'order_id', 'agent_name', 'agent_email', 'agent_phone', 'agent_href', 'description']);

    $ch = $this->CurlInit($startVars['headers']);

    // Заказы
    $offset = 0;
    $key = 0;
    do {
      $url = "https://api.moysklad.ru/api/remap/1.2/entity/customerorder?offset=" . $offset;
      
      if ($startVars['order_prefix'] != '') {
        $url .= "&filter=" . $orderBinding . "~=" . urlencode($startVars['order_prefix']);
      } elseif ($orderBinding == 'description') {
        $url .= "&filter=" . $orderBinding . "!=";
      }
      if (isset($startVars['order_day_limit'])) {
        $url .= "&filter=moment%3E%3D" . $dateLimit;
      }

      curl_setopt($ch, CURLOPT_URL, $url);
      
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch, "Получение заказов: ");

      foreach ($response['rows'] as $value) {
        $dataMS = $this->GetDataMS($value, ['name'], ['name'], $dataMS);
        if ($orderBinding == "name") {
          $dataMS = $this->GetDataMS($value, ['description'], ['description'], $dataMS);
        } else {
          $space = strpos($value['description'], " ", strlen($startVars['order_prefix']));
          if ($space !== false) {
            $value['description'] = mb_substr($value['description'], 0, mb_strpos($value['description'], " ", mb_strlen($startVars['order_prefix'])));
          } 
          $lineBreak = strpos($value['description'], "\n", strlen($startVars['order_prefix']));
          if ($lineBreak !== false) {
            $value['description'] = mb_substr($value['description'], 0, mb_strpos($value['description'], "\n", mb_strlen($startVars['order_prefix'])));
          } 

          if ($space !== false || $lineBreak !== false) {
            $dataMS['description'][$key] = $this->db->escape($value['description']);
          } else {
            $dataMS = $this->GetDataMS($value, ['description'], ['description'], $dataMS);
          }
        }
        $key++;
      }
      $offset += 1000;
    } while (isset($response['meta']['nextHref']));

    // Организация
    $metaOrg = ['href' => $startVars['organization'], 'type' => 'organization', 'mediaType' => 'application/json'];

    // Склад
    $metaStore = ['href' => $startVars['store'], 'type' => 'store', 'mediaType' => 'application/json'];

    // Канал продаж
    if ($startVars['saleschannel'] != '0')
      $metaSalesChannel = ['href' => $startVars['saleschannel'], 'type' => 'saleschannel', 'mediaType' => 'application/json'];

    if (!isset($startVars['quick_add'])) {
      // Контрагент
      $offset = 0;
      do {
        curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/counterparty?offset=" . $offset);
        $response = json_decode(curl_exec($ch), true);
        $response = $this->CheckResponse($response, $ch);
        foreach ($response['rows'] as $key => $value) {
          $dataMS = $this->GetDataMS($value, ['name', 'email', 'phone'], ['agent_name', 'agent_email', 'agent_phone'], $dataMS);
          $dataMS = $this->GetDataMS($value['meta'], ['href'], ['agent_href'], $dataMS);
        }
        $offset += 1000;
      } while (isset($response['meta']['nextHref']));
    
      // Доставка
      $serviceDataMS = $this->InitialDataMS(['service_name', 'service_code', 'service_href']);
      $offset = 0;
      do {
        curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/service?offset=" . $offset);
        $response = json_decode(curl_exec($ch), true);
        $response = $this->CheckResponse($response, $ch);
        foreach ($response['rows'] as $key => $value) {
          $serviceDataMS = $this->GetDataMS($value, ['name', 'code'], ['service_name', 'service_code'], $serviceDataMS);
          $serviceDataMS = $this->GetDataMS($value['meta'], ['href'], ['service_href'], $serviceDataMS);
        }
        $offset += 1000;
      } while (isset($response['meta']['nextHref']));
    }

    // Статусы
    $statusDataMS = $this->InitialDataMS(['name', 'href']);
    curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata");
    $response = json_decode(curl_exec($ch), true);
    $response = $this->CheckResponse($response, $ch);
    foreach ($response['states'] as $key => $value) {
      $statusDataMS = $this->GetDataMS($value, ['name'], ['name'], $statusDataMS);
      $statusDataMS = $this->GetDataMS($value['meta'], ['href'], ['href'], $statusDataMS);
    }

    $languageId = $this->GetLanguageId();

    $agentAdded = ['phone' => [], 'email' => [], 'name' => [], 'href' => []];
    $serviceAdded = ['service' => [], 'href' => []];
    $statusAdded = ['name' => [], 'href' => []];

    $added = [];

    $posAdded = [];

    // Позиции заказа
    if ($bindingOC != 'sku') {
      $query = $this->db->query("SELECT pd.name, p.model, op.price, op.quantity, op.order_id, op.order_product_id FROM " . DB_PREFIX . "order_product op
        INNER JOIN " . DB_PREFIX . "product p USING (`product_id`)
        INNER JOIN " . DB_PREFIX . "product_description pd USING (`product_id`)
        INNER JOIN `" . DB_PREFIX . "order` o USING (`order_id`) 
        WHERE pd.language_id = '{$languageId}' AND `order_status_id` != '0'" . (isset($startVars['order_day_limit']) ? " AND DATEDIFF(NOW(), o.date_added) <= $dayNumLimit" : ""));
      $posDataSQL = $this->GetDataSQL($query, ['name', 'binding', 'price', 'quantity', 'order_id', 'order_product_id']);
    } else {
      $query = $this->db->query("SELECT pd.name, op.price, op.quantity, op.order_id, op.order_product_id, p.sku 
        FROM " . DB_PREFIX . "order_product op 
        INNER JOIN " . DB_PREFIX . "product p USING (`product_id`)
        INNER JOIN " . DB_PREFIX . "product_description pd USING (`product_id`)
        INNER JOIN `" . DB_PREFIX . "order` o USING (`order_id`) 
        WHERE pd.language_id = '{$languageId}' AND `order_status_id` != '0'" . (isset($startVars['order_day_limit']) ? " AND DATEDIFF(NOW(), o.date_added) <= $dayNumLimit" : ""));
      $posDataSQL = $this->GetDataSQL($query, ['name', 'price', 'quantity', 'order_id', 'order_product_id', 'binding']);
    }

    $query = $this->db->query("SELECT order_product_id, name, value 
      FROM " . DB_PREFIX . "order_option 
      INNER JOIN `" . DB_PREFIX . "order` USING (`order_id`)" 
      . (isset($startVars['order_day_limit']) ? " WHERE DATEDIFF(NOW(), date_added) <= $dayNumLimit" : ""));
    $optionDataSQL = $this->GetDataSQL($query, ['order_product_id', 'name', 'value']);

    $posDataMS['product_href'] = [];

    foreach ($posDataSQL['name'] as $key => $value) {
      if (in_array($startVars['order_prefix'] . $posDataSQL['order_id'][$key], $dataMS[$orderBinding], true) === false) {

        if (in_array($posDataSQL['order_product_id'][$key], $optionDataSQL['order_product_id'], true) !== false) {

          $optionValue = array_keys($optionDataSQL['order_product_id'], $posDataSQL['order_product_id'][$key]);
          
          // Не искать товар, если он уже был найден
          $addedOption = "_(";
          foreach ($optionValue as $key2 => $value2) {
            if ($key2 != 0)
              $addedOption .= ", ";
            $addedOption .= $optionDataSQL['value'][$value2];
          }
          $addedOption .= ")";

          if (isset($posAdded[$posDataSQL['binding'][$key] . '_' . $value . $addedOption])) {
            $posDataMS['product_href'][$key] = $posAdded[$posDataSQL['binding'][$key] . '_' . $value . $addedOption];
            continue;
          }

          $url = "https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=product";
          if ($startVars['binding_name'] == 1) {  
            $url = $url . sprintf(";%s=%s;%s=%s", $bindingMS, urlencode(str_replace([";", '\"', "\'"], ["\;", '"', "'"], $posDataSQL['binding'][$key])), "name", urlencode(str_replace([";", '\"', "\'"], ["\;", '"', "'"], $value)));
          } else {
            $url = $url . sprintf(";%s=%s", $bindingMS, urlencode(str_replace([";", '\"', "\'"], ["\;", '"', "'"], $posDataSQL['binding'][$key])));
          }

          curl_setopt($ch, CURLOPT_URL, $url);
          $response = json_decode(curl_exec($ch), true);
          $response = $this->CheckResponse($response, $ch);
          if ($response['meta']['size'] != 0) {
            $url = 'https://api.moysklad.ru/api/remap/1.2/entity/variant?filter=productid=' . $response['rows'][0]['id'] . '&search=';
            $url = $url . urlencode($optionDataSQL['value'][$optionValue[0]]);
            curl_setopt($ch, CURLOPT_URL, $url);
            $response = json_decode(curl_exec($ch), true);
            $response = $this->CheckResponse($response, $ch, "Поиск товара: ");

            if ($response['meta']['size'] != 0) {

              if ($response['meta']['size'] == 1) {
                $posDataMS['product_href'][$key] = $response['rows'][0]['meta']['href'];
                $posAdded[$posDataSQL['binding'][$key] . '_' . $value . $addedOption] = $response['rows'][0]['meta']['href'];
              } else {
                $optionValues = [];
                foreach ($optionValue as $key2 => $value2) {
                  array_push($optionValues, $optionDataSQL['value'][$value2]);
                }

                $posDataMS['product_href'][$key] = '';
                foreach ($response['rows'] as $key1 => $value1) {
                  $flag = false;
                  foreach ($value1['characteristics'] as $key2 => $value2) {
                    if (!in_array($value2['value'], $optionValues, true)) {
                      $flag = true;
                    }
                  }

                  if (!$flag) {
                    $posDataMS['product_href'][$key] = $value1['meta']['href'];
                    $posAdded[$posDataSQL['binding'][$key] . '_' . $value . $addedOption] = $value1['meta']['href'];
                    break;
                  }
                }
              }
            } else {
              $posDataMS['product_href'][$key] = '';
            }
            
          } else {
            $posDataMS['product_href'][$key] = '';
          }
        } else {
          // Не искать товар, если он уже был найден
          if (isset($posAdded[$posDataSQL['binding'][$key] . '_' . $value])) {
            $posDataMS['product_href'][$key] = $posAdded[$posDataSQL['binding'][$key] . '_' . $value];
            continue;
          }

          $url = "https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=product;type=bundle";
          if ($startVars['binding_name'] == 1) {  
            $url = $url . sprintf(";%s=%s;%s=%s", $bindingMS, urlencode(str_replace([";", '\"', "\'"], ["\;", '"', "'"], $posDataSQL['binding'][$key])), "name", urlencode(str_replace([";", '\"', "\'"], ["\;", '"', "'"], $value)));
          } else {
            $url = $url . sprintf(";%s=%s", $bindingMS, urlencode(str_replace([";", '\"', "\'"], ["\;", '"', "'"], $posDataSQL['binding'][$key])));
          }

          curl_setopt($ch, CURLOPT_URL, $url);
          $response = json_decode(curl_exec($ch), true);
          $response = $this->CheckResponse($response, $ch, "Поиск товара: ");
          if ($response['meta']['size'] != 0) {
            $posDataMS['product_href'][$key] = $response['rows'][0]['meta']['href'];
            $posAdded[$posDataSQL['binding'][$key] . '_' . $value] = $response['rows'][0]['meta']['href'];
          } else {
            $posDataMS['product_href'][$key] = '';
          }
        }
      }
    }

    // Заказ
    $query = $this->db->query("SELECT order_id, shipping_firstname, shipping_lastname, shipping_address_1, date_added, shipping_method, email, telephone, shipping_postcode, shipping_city, shipping_zone, comment, total, order_status_id, 
      " . DB_PREFIX . "order_status.name AS status_name, payment_method, shipping_code, payment_firstname, payment_lastname, payment_address_1, payment_postcode, payment_city, payment_zone, firstname, lastname
      FROM `" . DB_PREFIX . "order`
      LEFT JOIN " . DB_PREFIX . "order_status USING (`order_status_id`) 
      WHERE `order_status_id` != '0' AND " . DB_PREFIX . "order_status.language_id = $languageId" . (isset($startVars['order_day_limit']) ? " AND DATEDIFF(NOW(), date_added) <= $dayNumLimit" : ""));
    
    $dataSQL = $this->GetDataSQL($query, ['order_id', 'shipping_firstname', 'shipping_lastname', 'shipping_address_1', 'date_added', 'shipping_method', 'email', 'telephone', 'shipping_postcode', 'shipping_city', 'shipping_zone', 'comment', 'total', 'order_status_id', 'status_name', 'payment_method', 'shipping_code', 'payment_firstname', 'payment_lastname', 'payment_address_1', 'payment_postcode', 'payment_city', 'payment_zone', 'firstname', 'lastname']);

    $query = $this->db->query("SELECT order_id,  code, value
      FROM " . DB_PREFIX . "order_total 
      INNER JOIN `" . DB_PREFIX . "order` USING (`order_id`)" . (isset($startVars['order_day_limit']) ? " WHERE DATEDIFF(NOW(), date_added) <= $dayNumLimit" : ""));
    $dataSQLTotal = $this->GetDataSQL($query, ['order_id', 'code', 'value']);

    $orderTotal = [];
    foreach ($dataSQLTotal['order_id'] as $key => $value) {
      $orderTotal[$value][$dataSQLTotal['code'][$key]] = $dataSQLTotal['value'][$key];
    }

    $dataSQL['value'] = [];
    foreach ($dataSQL['order_id'] as $key => $value) {
      if (isset($orderTotal[$value])) {
        if (isset($orderTotal[$value]['shipping'])) {
          $dataSQL['value'][$key] = $orderTotal[$value]['shipping'];
          if ($orderTotal[$value]['sub_total'] + $orderTotal[$value]['shipping'] > $orderTotal[$value]['total']) {
            $discount = $orderTotal[$value]['sub_total'] + $orderTotal[$value]['shipping'] - $orderTotal[$value]['total'];
            $dataSQL['discount'][$key] = $discount * 100 / ($orderTotal[$value]['sub_total']);
          } else { 
            $dataSQL['discount'][$key] = '';
          }
        } else {
          $dataSQL['value'][$key] = '';
          if ($orderTotal[$value]['sub_total'] > $orderTotal[$value]['total']) {
            $discount = $orderTotal[$value]['sub_total'] - $orderTotal[$value]['total'];
            $dataSQL['discount'][$key] = $discount * 100 / $orderTotal[$value]['sub_total'];
          } else {
            $dataSQL['discount'][$key] = '';
          }
        }
      }
    }

    $postData = [];

    foreach ($dataSQL['order_id'] as $key => $value) {

      if (!isset($dataMS[$orderBinding]) || in_array($startVars['order_prefix'] . $dataSQL['order_id'][$key], $dataMS[$orderBinding], true) === false) {
        $skip = false;
        // Основные данные
        $postData[$key]['moment'] = $dataSQL['date_added'][$key];
        $postData[$key][$orderBinding] = $startVars['order_prefix'] . $dataSQL['order_id'][$key];
        $postData[$key]['organization'] = ['meta' => $metaOrg];
        $postData[$key]['store'] = ['meta' => $metaStore];
        if ($startVars['saleschannel'] != '0')
          $postData[$key]['salesChannel'] = ['meta' => $metaSalesChannel];

        // Проведение заказа
        if (isset($startVars['conduct_order']))
          $postData[$key]['applicable'] = true;
        else
          $postData[$key]['applicable'] = false;

        if (
          $dataSQL['shipping_firstname'][$key] == '' &&
          $dataSQL['shipping_lastname'][$key] == '' &&
          $dataSQL['shipping_address_1'][$key] == '' &&
          $dataSQL['shipping_postcode'][$key] == '' &&
          $dataSQL['shipping_city'][$key] == '' &&
          $dataSQL['shipping_zone'][$key] == '') 
        {
          $dataSQL['shipping_firstname'][$key] = $dataSQL['payment_firstname'][$key];
          $dataSQL['shipping_lastname'][$key] = $dataSQL['payment_lastname'][$key];
          $dataSQL['shipping_address_1'][$key] = $dataSQL['payment_address_1'][$key];
          $dataSQL['shipping_postcode'][$key] = $dataSQL['payment_postcode'][$key];
          $dataSQL['shipping_city'][$key] = $dataSQL['payment_city'][$key];
          $dataSQL['shipping_zone'][$key] = $dataSQL['payment_zone'][$key];
        }

        if (
          $dataSQL['shipping_firstname'][$key] == '' &&
          $dataSQL['shipping_lastname'][$key] == '' &&
          $dataSQL['shipping_address_1'][$key] == '' &&
          $dataSQL['shipping_postcode'][$key] == '' &&
          $dataSQL['shipping_city'][$key] == '' &&
          $dataSQL['shipping_zone'][$key] == '') 
        {
          $dataSQL['shipping_firstname'][$key] = $dataSQL['firstname'][$key];
          $dataSQL['shipping_lastname'][$key] = $dataSQL['lastname'][$key];
        }

        // Адрес
        $address = [];
        if ($dataSQL['shipping_postcode'][$key] != '')
          $address[] = $dataSQL['shipping_postcode'][$key];
        if ($dataSQL['shipping_zone'][$key] != '')
          $address[] = $dataSQL['shipping_zone'][$key];
        if ($dataSQL['shipping_city'][$key] != '')
          $address[] = $dataSQL['shipping_city'][$key];
        if ($dataSQL['shipping_address_1'][$key] != '')
          $address[] = $dataSQL['shipping_address_1'][$key];

        $address = implode(", ", $address);
        $postData[$key]['shipmentAddressFull']['addInfo'] = $address;
        $postData[$key]['shipmentAddressFull']['comment'] = $dataSQL['comment'][$key];
        
        // Статус
        $statusKey = array_search($dataSQL['status_name'][$key], $statusDataMS['name'], true);
        if ($statusKey !== false) {
          $metaStatus = ['href' => $statusDataMS['href'][$statusKey], 'type' => 'state', 'mediaType' => 'application/json'];
        } else {
          $addedSearch = array_search($dataSQL['status_name'][$key], $statusAdded['name'], true);
          if ($addedSearch !== false) {
            $metaStatus = ['href' => $statusAdded['href'][$addedSearch], 'type' => 'state', 'mediaType' => 'application/json'];
          } else {
            $postDataStatus = ['name' => $dataSQL['status_name'][$key], 'color' => 15106326, 'stateType' => 'Regular'];
            $postDataStatus = json_encode($postDataStatus, JSON_UNESCAPED_SLASHES);
            curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataStatus);
            $response = json_decode(curl_exec($ch), true);
            $response = $this->CheckResponse($response, $ch, "Создание статуса заказа: ");
            $metaStatus = ['href' => $response['meta']['href'], 'type' => 'state', 'mediaType' => 'application/json'];

            $statusAdded['name'][] = $dataSQL['status_name'][$key];
            $statusAdded['href'][] = $response['meta']['href'];
          }
        }

        $postData[$key]['state'] = ['meta' => $metaStatus];

        // Контрагент
        $agentName = trim($dataSQL['shipping_firstname'][$key] . ' ' . $dataSQL['shipping_lastname'][$key]);

        if ($agentName == '') {
          $agentName = "Неизвестно";
        }

        if (in_array($dataSQL['email'][$key], $defaultEmail)) {
          $dataSQL['email'][$key] = '';
        }

        if (isset($startVars['comment_info'])) {
          if ($orderBinding == 'name') {
            $postData[$key]['description'] = "Номер заказа: {$value}\nИмя: {$agentName}\nТелефон: {$dataSQL['telephone'][$key]}\nEmail: {$dataSQL['email'][$key]}\nАдрес: {$address}\nСпособ доставки: {$dataSQL['shipping_method'][$key]}\nСпособ оплаты: {$dataSQL['payment_method'][$key]}\nКомментарий: {$dataSQL['comment'][$key]}";
          } else {
            $postData[$key]['description'] .= "\nНомер заказа: {$value}\nИмя: {$agentName}\nТелефон: {$dataSQL['telephone'][$key]}\nEmail: {$dataSQL['email'][$key]}\nАдрес: {$address}\nСпособ доставки: {$dataSQL['shipping_method'][$key]}\nСпособ оплаты: {$dataSQL['payment_method'][$key]}\nКомментарий: {$dataSQL['comment'][$key]}";
          }
        }

        if (isset($startVars['quick_add'])) {
          // Оперативный режим
          $url = "https://api.moysklad.ru/api/remap/1.2/entity/counterparty?filter=";
          
          if ($dataSQL['telephone'][$key] != '') {
            $url .= "phone=" . urlencode($dataSQL['telephone'][$key]);
            
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_URL, $url);
            $response = json_decode(curl_exec($ch), true);
            $response = $this->CheckResponse($response, $ch, "Поиск контрагента: ");

            if ($response['meta']['size'] == 0 && $dataSQL['email'][$key] != '') {
              $url = "https://api.moysklad.ru/api/remap/1.2/entity/counterparty?filter=email=" . urlencode($dataSQL['email'][$key]);

              curl_setopt($ch, CURLOPT_POST, 0);
              curl_setopt($ch, CURLOPT_URL, $url);
              $response = json_decode(curl_exec($ch), true);
              $response = $this->CheckResponse($response, $ch, "Поиск контрагента: ");
            }
          } else if ($dataSQL['email'][$key] != '') {
            $url .= "email=" . urlencode($dataSQL['email'][$key]);

            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_URL, $url);
            $response = json_decode(curl_exec($ch), true);
            $response = $this->CheckResponse($response, $ch, "Поиск контрагента: ");
          } else {
            $url .= sprintf("name=%s;phone=;email=", urlencode($agentName));

            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_URL, $url);
            $response = json_decode(curl_exec($ch), true);
            $response = $this->CheckResponse($response, $ch, "Поиск контрагента: ");
          }

          if ($response['meta']['size'] != 0) {
            $metaAgent = ['href' => $response['rows'][0]['meta']['href'], 'type' => 'counterparty', 'mediaType' => 'application/json'];
          } else {
            $postDataAgent = ['name' => $agentName, 'phone' => $dataSQL['telephone'][$key], 'email' => $dataSQL['email'][$key], 'companyType' => $startVars['agent_type'], 'actualAddress' => $address];
            $postDataAgent = json_encode($postDataAgent, JSON_UNESCAPED_SLASHES);
            curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/counterparty");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataAgent);
            $response = json_decode(curl_exec($ch), true);
            $response = $this->CheckResponse($response, $ch, "Создание контрагента: ");
            $metaAgent = ['href' => $response['meta']['href'], 'type' => 'counterparty', 'mediaType' => 'application/json'];
          }
        } else {
          // Обычный режим
          if ($dataSQL['telephone'][$key] != '') {
            $agentSearch = array_search($dataSQL['telephone'][$key], $dataMS['agent_phone'], true);
            if ($agentSearch === false) {
              $addedSearch = array_search($dataSQL['telephone'][$key], $agentAdded['phone'], true);
              if ($addedSearch === false && $dataSQL['email'][$key] != '') {
                $agentSearch = array_search($dataSQL['email'][$key], $dataMS['agent_email'], true);
                if ($agentSearch === false)
                  $addedSearch = array_search($dataSQL['email'][$key], $agentAdded['email'], true);
              }
            }
          } elseif ($dataSQL['email'][$key] != '') {
            $agentSearch = array_search($dataSQL['email'][$key], $dataMS['agent_email'], true);
            if ($agentSearch === false)
              $addedSearch = array_search($dataSQL['email'][$key], $agentAdded['email'], true);
          } else {
            $agentSearch = false;
            foreach (array_keys($dataMS['agent_name'], $agentName) as $value1) {
              if ($dataMS['agent_phone'][$value1] == '' && $dataMS['agent_email'][$value1] == '') {
                $agentSearch = $value1;
                break;
              }
            }

            if ($agentSearch === false)
              $addedSearch = array_search($agentName, $agentAdded['name'], true);
          }

          if ($agentSearch !== false) {
            $metaAgent = ['href' => $dataMS['agent_href'][$agentSearch], 'type' => 'counterparty', 'mediaType' => 'application/json'];
          } elseif ($addedSearch !== false) {
            $metaAgent = ['href' => $agentAdded['href'][$addedSearch], 'type' => 'counterparty', 'mediaType' => 'application/json'];
          } else {
            $postDataAgent = ['name' => $agentName, 'phone' => $dataSQL['telephone'][$key], 'email' => $dataSQL['email'][$key], 'companyType' => $startVars['agent_type'], 'actualAddress' => $address];
            $postDataAgent = json_encode($postDataAgent, JSON_UNESCAPED_SLASHES);
            curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/counterparty");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataAgent);
            $response = json_decode(curl_exec($ch), true);
            $response = $this->CheckResponse($response, $ch, "Создание контрагента: ");
            $metaAgent = ['href' => $response['meta']['href'], 'type' => 'counterparty', 'mediaType' => 'application/json'];

            $agentAdded['phone'][$key] = $dataSQL['telephone'][$key];
            $agentAdded['email'][$key] = $dataSQL['email'][$key];
            $agentAdded['href'][$key] = $response['meta']['href'];
            if ($dataSQL['telephone'][$key] == '' && $dataSQL['email'][$key] == '') {
              $agentAdded['name'][$key] = $agentName;
              $agentAdded['href'][$key] = $response['meta']['href'];
            }  
          }
        }

        $postData[$key]['agent'] = ['meta' => $metaAgent];

        // Позиции
        $positions = [];
        
        foreach (array_keys($posDataSQL['order_id'], $value) as $key1 => $value1) {
          if ($posDataMS['product_href'][$value1] != '' && $posDataSQL['quantity'][$value1] > 0) {
            $positions[$key1]['quantity'] = (float)$posDataSQL['quantity'][$value1];
            if (isset($startVars['product_reserve'])) {
              $positions[$key1]['reserve'] = (float)$posDataSQL['quantity'][$value1];
            }
            $positions[$key1]['price'] = (float)$posDataSQL['price'][$value1] * 100;
            if ($dataSQL['discount'][$key] != '') {
              $positions[$key1]['discount'] = (float)$dataSQL['discount'][$key];
            }
            if (isset($startVars['nds'])) {
              $positions[$key1]['vat'] = (float)$startVars['nds'];
            }
            if (strpos($posDataMS['product_href'][$value1], 'product') !== false) {
              $metaPos = ['href' => $posDataMS['product_href'][$value1], 'type' => 'product', 'mediaType' => 'application/json'];
            } elseif (strpos($posDataMS['product_href'][$value1], 'bundle') !== false) {
              $metaPos = ['href' => $posDataMS['product_href'][$value1], 'type' => 'bundle', 'mediaType' => 'application/json'];
            } else {
              $metaPos = ['href' => $posDataMS['product_href'][$value1], 'type' => 'variant', 'mediaType' => 'application/json'];
            }
            $positions[$key1]['assortment'] = ['meta' => $metaPos];
          } else {
            
            $this->logText .= "Заказ {$dataSQL['order_id'][$key]}: в Моем Складе не найден товар " . stripcslashes($posDataSQL['binding'][$value1]) . " " . stripcslashes($posDataSQL['name'][$value1]) . PHP_EOL;
            if (!isset($startVars['not_skip_order'])) {
              $skip = true;
              break;
            } else {
              if ($orderBinding == 'name') {
                $postData[$key]['description'] = "Товар " . stripcslashes($posDataSQL['binding'][$value1]) . " " . stripcslashes($posDataSQL['name'][$value1]) . " не был найден\n";
              } else {
                $postData[$key]['description'] .= "\nТовар " . stripcslashes($posDataSQL['binding'][$value1]) . " " . stripcslashes($posDataSQL['name'][$value1]) . " не был найден\n";
              }
            }
          }
        }
          
        if (!isset($startVars['not_skip_order'])) {
          if ($skip || empty($positions)) {
            unset($postData[$key]);
            continue;
          }
        } 

        // Доставка
        if (isset($startVars['shipping_add']) && $dataSQL['value'][$key] != '' && $dataSQL['shipping_method'][$key] != "") {
          $dataSQL['shipping_method'][$key] = strip_tags($dataSQL['shipping_method'][$key]);
          $dataSQL['shipping_method'][$key] = str_replace(";", ",", $dataSQL['shipping_method'][$key]);
          $lenPos = count($positions);
          $positions[$lenPos]['quantity'] = 1;
          $positions[$lenPos]['price'] = (float)$dataSQL['value'][$key] * 100;
          if (isset($startVars['quick_add'])) {
            // Оперативный режим
            if ($startVars['shipping_binding'] == 'name') {
              $url = "https://api.moysklad.ru/api/remap/1.2/entity/service?filter=name=" . urlencode($dataSQL['shipping_method'][$key]);
            } else {
              $url = "https://api.moysklad.ru/api/remap/1.2/entity/service?filter=code=" . urlencode($dataSQL['shipping_code'][$key]);
            }

            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_URL, $url);
            $response = json_decode(curl_exec($ch), true);
            $response = $this->CheckResponse($response, $ch, "Поиск услуги доставки: ");

            if ($response['meta']['size'] != 0) {
              $metaPos = ['href' => $response['rows'][0]['meta']['href'], 'type' => 'service', 'mediaType' => 'application/json'];
              $positions[$lenPos]['assortment'] = ['meta' => $metaPos];
            } else {
              if ($startVars['shipping_binding'] == 'name')
                $postDataService = ['name' => $dataSQL['shipping_method'][$key]];
              else
                $postDataService = ['name' => $dataSQL['shipping_method'][$key], 'code' => $dataSQL['shipping_code'][$key]];

              $postDataService = json_encode($postDataService, JSON_UNESCAPED_SLASHES);
              curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/service");
              curl_setopt($ch, CURLOPT_POST, 1);
              curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataService);
              $response = json_decode(curl_exec($ch), true);
              $response = $this->CheckResponse($response, $ch, "Создание услуги доставки: ");
              $metaPos = ['href' => $response['meta']['href'], 'type' => 'service', 'mediaType' => 'application/json'];
              $positions[$lenPos]['assortment'] = ['meta' => $metaPos];
            }
          } else {
            // Обычный режим
            if ($startVars['shipping_binding'] == 'name') {
              $search = array_search($dataSQL['shipping_method'][$key], $serviceDataMS['service_name'], true);
            } else {
              $search = array_search($dataSQL['shipping_code'][$key], $serviceDataMS['service_code'], true);
            }

            if ($search !== false) {
              $metaPos = ['href' => $serviceDataMS['service_href'][$search], 'type' => 'service', 'mediaType' => 'application/json'];
              $positions[$lenPos]['assortment'] = ['meta' => $metaPos];
            } else {
              if ($startVars['shipping_binding'] == 'name')
                $addedSearch = array_search($dataSQL['shipping_method'][$key], $serviceAdded['service'], true);
              else
                $addedSearch = array_search($dataSQL['shipping_code'][$key], $serviceAdded['service'], true);
              if ($addedSearch !== false) {
                $metaPos = ['href' => $serviceAdded['href'][$addedSearch], 'type' => 'service', 'mediaType' => 'application/json'];
                $positions[$lenPos]['assortment'] = ['meta' => $metaPos];
              } else {
                if ($startVars['shipping_binding'] == 'name')
                  $postDataService = ['name' => $dataSQL['shipping_method'][$key]];
                else
                  $postDataService = ['name' => $dataSQL['shipping_method'][$key], 'code' => $dataSQL['shipping_code'][$key]];

                $postDataService = json_encode($postDataService, JSON_UNESCAPED_SLASHES);
                curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/service");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataService);
                $response = json_decode(curl_exec($ch), true);
                $response = $this->CheckResponse($response, $ch, "Создание услуги доставки: ");
                $metaPos = ['href' => $response['meta']['href'], 'type' => 'service', 'mediaType' => 'application/json'];
                $positions[$lenPos]['assortment'] = ['meta' => $metaPos];
              }
              
              if ($startVars['shipping_binding'] == 'name')
                $serviceAdded['service'][] = $dataSQL['shipping_method'][$key];
              else
                $serviceAdded['service'][] = $dataSQL['shipping_code'][$key];
              $serviceAdded['href'][] = $response['meta']['href'];
            }
          }
        }

        $postData[$key]['positions'] = $positions;
        array_push($added, $dataSQL['order_id'][$key]);
      }
    }

    $postData = array_values($postData);
    if (count($postData) > 0) {
      foreach (array_chunk($postData, 1000) as $postDataSmall) {
        $postDataSmall = json_encode($postDataSmall, JSON_UNESCAPED_SLASHES);
        curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/customerorder");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataSmall);
        $response = json_decode(curl_exec($ch), true);
        $response = $this->CheckResponse($response, $ch, "", false);
        foreach ($response as $responseKey => $value) {
          $value = $this->CheckResponse($value, $ch, "Заказ {$added[$responseKey]}: ", false);
        }
      }
    }

    
    $this->logText .= 'Добавлено заказов: ' . count($added) . PHP_EOL;
    foreach ($added as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    
    $this->LogWrite();    
    $this->Output('Успешно. Заказов добавлено: ' . count($added), 'success');
  }


  public function OrderUpdate()
  {
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }

    $this->logText = date('H:i:s d.m.Y') . ' Обновление заказов' . PHP_EOL;    

    $this->db->query("set session wait_timeout=28800");
    $startVars = $this->GetStartVars(['order_prefix', 'order_binding', 'order_day_limit', 'two_side_status']);

    if (isset($startVars['order_day_limit'])) {
      $dayNumLimit = $startVars['order_day_limit'];

      if (strpos($dayNumLimit, '.') !== false || $dayNumLimit < 0) {
        $this->Output('Ошибка! Количество дней должно быть целым неотрицательным числом', 'error_warning');
        exit();
      }

      $dateLimit = new DateTime();
      $dateLimit->modify("- $dayNumLimit day");
      $dateLimit = $dateLimit->format("Y-m-d");
    }

    $ch = $this->CurlInit($startVars['headers']);

    if (!isset($startVars['order_prefix'])) {
      $startVars['order_prefix'] = '';
    }

    if ($startVars['order_binding'] == 'number')
      $orderBinding = 'name';
    else
      $orderBinding = 'description';

    $dataMS = $this->InitialDataMS(['name', 'description', 'state_href', 'order_id']);

    $languageId = $this->GetLanguageId();

    // Заказы
    $offset = 0;
    $key = 0;
    do {
      $url = "https://api.moysklad.ru/api/remap/1.2/entity/customerorder?offset=" . $offset;
      
      if ($startVars['order_prefix'] != '') {
        $url .= "&filter=" . $orderBinding . "~=" . urlencode($startVars['order_prefix']);
      } elseif ($orderBinding == 'description') {
        $url .= "&filter=" . $orderBinding . "!=";
      }

      if (isset($startVars['order_day_limit'])) {
        $url .= "&filter=moment%3E%3D" . $dateLimit;
      }

      curl_setopt($ch, CURLOPT_URL, $url);

      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);
      foreach ($response['rows'] as $value) {
        $dataMS = $this->GetDataMS($value, ['name', 'id'], ['name', 'id'], $dataMS);
        if ($orderBinding == "name") {
          $dataMS = $this->GetDataMS($value, ['description'], ['description'], $dataMS);
        } else {
          $space = strpos($value['description'], " ", strlen($startVars['order_prefix']));
          if ($space !== false) {
            $value['description'] = mb_substr($value['description'], 0, mb_strpos($value['description'], " ", mb_strlen($startVars['order_prefix'])));
          } 
          $lineBreak = strpos($value['description'], "\n", strlen($startVars['order_prefix']));
          if ($lineBreak !== false) {
            $value['description'] = mb_substr($value['description'], 0, mb_strpos($value['description'], "\n", mb_strlen($startVars['order_prefix'])));
          } 

          if ($space !== false || $lineBreak !== false) {
            $dataMS['description'][$key] = $this->db->escape($value['description']);
          } else {
            $dataMS = $this->GetDataMS($value, ['description'], ['description'], $dataMS);
          }
        }

        $dataMS = $this->GetDataMS($value['state']['meta'], ['href'], ['state_href'], $dataMS);

        if ($startVars['two_side_status'] == 1) {
          $dataMS['state_moment'][$key] = "";
          curl_setopt($ch, CURLOPT_URL, $value['meta']['href'] . '/audit?limit=100');
          $responseAudit = json_decode(curl_exec($ch), true);
          $responseAudit = $this->CheckResponse($responseAudit, $ch);
          foreach ($responseAudit['rows'] as $audit) {
            if (isset($audit['diff']['state'])) {
              $dataMS['state_moment'][$key] = $audit['moment'];
              break;
            }
          }
        }

        $key++;
      }
      $offset += 1000;
    } while (isset($response['meta']['nextHref']));

    // Статусы
    $statusDataMS = $this->InitialDataMS(['state', 'state_href']);
    curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata");
    $response = json_decode(curl_exec($ch), true);
    $response = $this->CheckResponse($response, $ch);
    foreach ($response['states'] as $key => $value) {
      $statusDataMS = $this->GetDataMS($value, ['name'], ['state'], $statusDataMS);
      $statusDataMS = $this->GetDataMS($value['meta'], ['href'], ['state_href'], $statusDataMS);
    }

    // Заказ
    $query = $this->db->query("SELECT order_id, order_status_id, 
      " . DB_PREFIX . "order_status.name AS status_name
      FROM `" . DB_PREFIX . "order`
      LEFT JOIN " . DB_PREFIX . "order_status USING (`order_status_id`) 
      WHERE `order_status_id` != '0' AND " . DB_PREFIX . "order_status.language_id = $languageId" . (isset($startVars['order_day_limit']) ? " AND DATEDIFF(NOW(), date_added) <= $dayNumLimit" : ""));
    
    $dataSQL = $this->GetDataSQL($query, ['order_id', 'order_status_id', 'status_name']);

    if ($startVars['two_side_status'] == 1) {
      foreach ($dataSQL['order_id'] as $key => $value) {
        $query = $this->db->query("SELECT date_added FROM " . DB_PREFIX . "order_history WHERE `order_id` = $value ORDER BY date_added DESC LIMIT 1");
        $dataSQL['state_moment'][$key] = $query->row['date_added'];
      }
    }

    $orderUpdatedNum = 0;
    $added = [];
    $statusAdded = [];
    foreach ($dataMS[$orderBinding] as $key => $value) {   
      $postData = [];
      $postDataStatus = [];

      $dataMS['order_id'][$key] = str_replace($startVars['order_prefix'], '', $dataMS[$orderBinding][$key]);
      $orderKey = array_search($dataMS['order_id'][$key], $dataSQL['order_id'], true);
      $stateKeyMS = array_search($dataMS['state_href'][$key], $statusDataMS['state_href'], true);

      if ($orderKey !== false && $statusDataMS['state'][$stateKeyMS] != $dataSQL['status_name'][$orderKey]) {
        if ($startVars['two_side_status'] == 1 && $dataMS['state_moment'][$key] > $dataSQL['state_moment'][$orderKey])
          continue;
        $orderUpdatedNum++;
        array_push($added, $dataMS['order_id'][$key]);

        // Статус
        $stateKeySQL = array_search($dataSQL['status_name'][$orderKey], $statusDataMS['state'], true);
        if ($stateKeySQL !== false) {
          $postData['state'] = ['meta' => ['href'=> $statusDataMS['state_href'][$stateKeySQL], 'type' => 'state', 'mediaType' => 'application/json']];
        } else {
          $addedSearch = array_search($dataSQL['status_name'][$orderKey], $statusAdded, true);
          if ($addedSearch !== false) {
            $postData['state'] = ['meta' => ['href'=> $addedSearch, 'type' => 'state', 'mediaType' => 'application/json']];
          } else {
            $postDataStatus = ['name' => $dataSQL['status_name'][$orderKey], 'color' => 15106326, 'stateType' => 'Regular'];
            $postDataStatus = json_encode($postDataStatus, JSON_UNESCAPED_SLASHES);
            curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataStatus);
            $response = json_decode(curl_exec($ch), true);
            $response = $this->CheckResponse($response, $ch);
            $postData['state'] = ['meta' => ['href'=> $response['meta']['href'], 'type' => 'state', 'mediaType' => 'application/json']];
            $statusAdded[$response['meta']['href']] = $dataSQL['status_name'][$orderKey];
          }
        }

        $postData = json_encode($postData, JSON_UNESCAPED_SLASHES);
        curl_setopt($ch, CURLOPT_URL, "https://api.moysklad.ru/api/remap/1.2/entity/customerorder/" . $dataMS['id'][$key]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST , "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = json_decode(curl_exec($ch), true);
        $response = $this->CheckResponse($response, $ch);
      }
    }

    curl_close($ch);

    $this->logText .= 'Обновлено заказов: ' . $orderUpdatedNum . PHP_EOL;
    foreach ($added as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    
    $this->LogWrite();    
    $this->Output('Успешно. Заказов обновлено: ' . $orderUpdatedNum, 'success');
  }

  public function OrderUpdateOC()
  {
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }

    $this->logText = date('H:i:s d.m.Y') . ' Обновление заказов Opencart' . PHP_EOL;    

    $startVars = $this->GetStartVars(['order_prefix', 'order_binding', 'order_day_limit', 'two_side_status']);

    if (isset($startVars['order_day_limit'])) {
      $dayNumLimit = $startVars['order_day_limit'];

      if (strpos($dayNumLimit, '.') !== false || $dayNumLimit < 0) {
        $this->Output('Ошибка! Количество дней должно быть целым неотрицательным числом', 'error_warning');
        exit();
      }

      $dateLimit = new DateTime();
      $dateLimit->modify("- $dayNumLimit day");
      $dateLimit = $dateLimit->format("Y-m-d");
    }

    if ($startVars['order_binding'] == 'number')
      $orderBinding = 'name';
    else
      $orderBinding = 'description';

    $ch = $this->CurlInit($startVars['headers']);

    if (!isset($startVars['order_prefix'])) {
      $startVars['order_prefix'] = '';
    }

    $dataMS = $this->InitialDataMS(['description', 'name', 'state_href', 'id']);

    $languageId = $this->GetLanguageId();

    // Заказы
    $offset = 0;
    $key = 0;
    do {
      $url = "https://api.moysklad.ru/api/remap/1.2/entity/customerorder?offset=" . $offset;
      
      if ($startVars['order_prefix'] != '') {
        $url .= "&filter=" . $orderBinding . "~=" . urlencode($startVars['order_prefix']);
      } elseif ($orderBinding == 'description') {
        $url .= "&filter=" . $orderBinding . "!=";
      }

      if (isset($startVars['order_day_limit'])) {
        $url .= "&filter=moment%3E%3D" . $dateLimit;
      }

      curl_setopt($ch, CURLOPT_URL, $url);

      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      foreach ($response['rows'] as $value) {
        $dataMS = $this->GetDataMS($value, ['name', 'id'], ['name', 'id'], $dataMS);
        if ($orderBinding == "name") {
          $dataMS = $this->GetDataMS($value, ['description'], ['description'], $dataMS);
        } else {
          $space = strpos($value['description'], " ", strlen($startVars['order_prefix']));
          if ($space !== false) {
            $value['description'] = mb_substr($value['description'], 0, mb_strpos($value['description'], " ", mb_strlen($startVars['order_prefix'])));
          } 
          $lineBreak = strpos($value['description'], "\n", strlen($startVars['order_prefix']));
          if ($lineBreak !== false) {
            $value['description'] = mb_substr($value['description'], 0, mb_strpos($value['description'], "\n", mb_strlen($startVars['order_prefix'])));
          } 

          if ($space !== false || $lineBreak !== false) {
            $dataMS['description'][$key] = $this->db->escape($value['description']);
          } else {
            $dataMS = $this->GetDataMS($value, ['description'], ['description'], $dataMS);
          }
        }

        $dataMS = $this->GetDataMS($value['state']['meta'], ['href'], ['state_href'], $dataMS);

        if ($startVars['two_side_status'] == 1) {
          $dataMS['state_moment'][$key] = "";
          curl_setopt($ch, CURLOPT_URL, $value['meta']['href'] . '/audit?limit=100');
          $responseAudit = json_decode(curl_exec($ch), true);
          $responseAudit = $this->CheckResponse($responseAudit, $ch);
          foreach ($responseAudit['rows'] as $audit) {
            if (isset($audit['diff']['state'])) {
              $dataMS['state_moment'][$key] = $audit['moment'];
              break;
            }
          }
        }

        $key++;
      }
      $offset += 1000;
    } while (isset($response['meta']['nextHref']));

    // Статусы
    $statusDataMS = $this->InitialDataMS(['state', 'state_href']);

    $offset = 0;
    do {
      curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata");
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      foreach ($response['states'] as $key => $value) {
        $statusDataMS = $this->GetDataMS($value, ['name'], ['state'], $statusDataMS);
        $statusDataMS = $this->GetDataMS($value['meta'], ['href'], ['state_href'], $statusDataMS);
      }
      $offset += 1000;
    } while (isset($response['meta']['nextHref']));

    // Заказ
    $query = $this->db->query("SELECT order_id, order_status_id, 
      " . DB_PREFIX . "order_status.name AS status_name
      FROM `" . DB_PREFIX . "order`
      LEFT JOIN " . DB_PREFIX . "order_status USING (`order_status_id`) 
      WHERE `order_status_id` != '0' AND " . DB_PREFIX . "order_status.language_id = $languageId" 
      . (isset($startVars['order_day_limit']) ? " AND DATEDIFF(NOW(), date_added) <= $dayNumLimit" : ""));
    $dataSQL = $this->GetDataSQL($query, ['order_id', 'order_status_id', 'status_name']);

    if ($startVars['two_side_status'] == 1) {
      foreach ($dataSQL['order_id'] as $key => $value) {
        $query = $this->db->query("SELECT date_added FROM " . DB_PREFIX . "order_history WHERE `order_id` = $value ORDER BY date_added DESC LIMIT 1");
        $dataSQL['state_moment'][$key] = $query->row['date_added'];
      }
    }

    $query = $this->db->query("SELECT order_status_id, name FROM " . DB_PREFIX . "order_status WHERE `language_id` = $languageId");
    $dataStatusSQL = $this->GetDataSQL($query, ['order_status_id', 'name']);

    $lastId = max($dataStatusSQL['order_status_id']);

    $updateOrderStatus = '';
    $updateWhere = [];
    $insertStatus = [];
    $orderUpdatedNum = 0;
    $added = [];
    $dateAdded = date("Y-m-d H:i:s");

    $statusAdded = [];

    $this->load->model('checkout/order');

    foreach ($dataSQL['status_name'] as $key => $value) {
      $orderKey = array_search($startVars['order_prefix'] . $dataSQL['order_id'][$key], $dataMS[$orderBinding]);

      if ($orderKey === false) {
        continue;
      }

      $stateKeyMS = array_search($dataMS['state_href'][$orderKey], $statusDataMS['state_href']);

      if ($statusDataMS['state'][$stateKeyMS] != $dataSQL['status_name'][$key]) {

        if ($startVars['two_side_status'] == 1 && $dataMS['state_moment'][$orderKey] <= $dataSQL['state_moment'][$key])
          continue;

        $addedSearch = array_search($statusDataMS['state'][$stateKeyMS], $statusAdded, true);

        if ($addedSearch !== false) {
          $updateOrderStatus .= sprintf("WHEN `order_id` = '%s' THEN '%s' ", $dataSQL['order_id'][$key], $addedSearch);
          $this->model_checkout_order->addOrderHistory($dataSQL['order_id'][$key], $addedSearch, "", false);
          array_push($updateWhere, $dataSQL['order_id'][$key]);
        } else {
          $stateKeySQL = array_search($statusDataMS['state'][$stateKeyMS], $dataStatusSQL['name'], true);
          if ($stateKeySQL !== false) {
            $updateOrderStatus .= sprintf("WHEN `order_id` = '%s' THEN '%s' ", $dataSQL['order_id'][$key], $dataStatusSQL['order_status_id'][$stateKeySQL]);
            $this->model_checkout_order->addOrderHistory($dataSQL['order_id'][$key], $dataStatusSQL['order_status_id'][$stateKeySQL], "", false);
            array_push($updateWhere, $dataSQL['order_id'][$key]);
          } else {
            $lastId++;
            array_push($insertStatus, sprintf("('%s', '%s', '%s')", $lastId, $languageId, $statusDataMS['state'][$stateKeyMS]));
            $updateOrderStatus .= sprintf("WHEN `order_id` = '%s' THEN '%s' ", $dataSQL['order_id'][$key], $lastId);
            $this->model_checkout_order->addOrderHistory($dataSQL['order_id'][$key], $lastId, "", false);

            $statusAdded[$lastId] = $statusDataMS['state'][$stateKeyMS];
          }
        }

        $orderUpdatedNum++;
        array_push($added, $dataSQL['order_id'][$key]);
      }
    }

    if (count($insertStatus) != 0) {
      $insertStatus = implode(", " , $insertStatus);
      $this->db->query("INSERT INTO " . DB_PREFIX . "order_status (`order_status_id`, `language_id`, `name`) VALUES $insertStatus");
    }

    if ($updateOrderStatus != '') {
      $updateWhere = "'" . implode("', '", $updateWhere) . "'";
      $this->db->query("UPDATE `" . DB_PREFIX . "order` SET 
      order_status_id = CASE " . $updateOrderStatus . "END
      WHERE `order_id` IN ($updateWhere)");
    }

    $this->cache->delete('order_status');

    curl_close($ch);

    $this->logText .= 'Обновлено заказов Opencart: ' . $orderUpdatedNum . PHP_EOL;
    foreach ($added as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    
    $this->LogWrite();    
    $this->Output('Успешно. Заказов Opencart обновлено: ' . $orderUpdatedNum, 'success');
  }


  public function ProductAddMS()
  {
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }

    $this->db->query("set session wait_timeout=28800");
    $this->logText = date('H:i:s d.m.Y') . ' Добавление товаров в Мой Склад' . PHP_EOL;

    $startVars = $this->GetStartVars(["binding", "sale_price", "binding_name", "desc_update_ms", "article_update_ms", "weight_update_ms", "weight_unity", "manufacturer_update_ms", "manufacturer", "cat_update_ms", "supplier", "organization", "store", "uom", "category_binding", "unit_update_ms", "image_update_ms", "modif_update_ms", "stock_update_ms", "product_sql_offset", "image_size_ms", "from_group_sql"]);

    $this->CreateLock("ProductAddMS");

    if (!isset($startVars['manufacturer'])) {
      $startVars['manufacturer'] = '';
    }

    $languageId = $this->GetLanguageId();

    $bindingOC = substr($startVars['binding'], 0, strpos($startVars['binding'], '_'));
    $bindingMS = substr($startVars['binding'], strpos($startVars['binding'], '_') + 1);

    $ch = $this->CurlInit($startVars['headers']);

    // Получение групп товаров
    if (isset($startVars['cat_update_ms'])) {
      $dataCategoryMS = $this->InitialDataMS(['href', 'name']);
      
      $offset = 0;
      do { 
        curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/entity/productfolder?offset=' . $offset);
        $response = json_decode(curl_exec($ch), true);
        $response = $this->CheckResponse($response, $ch);

        foreach ($response['rows'] as $key => $row) {
          $dataCategoryMS = $this->GetDataMS($row['meta'], ['href'], ['href'], $dataCategoryMS);
          $dataCategoryMS = $this->GetDataMS($row, ['name', 'id'], ['name', 'id'], $dataCategoryMS);
        }

        $offset += 1000;
      } while (isset($response['meta']['nextHref']));
    }
    
    // Получение типов цен
    $priceMS = [];
    curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/context/companysettings/pricetype');
    $response = json_decode(curl_exec($ch), true);
    $response = $this->CheckResponse($response, $ch);

    $priceMS['href'] = $response[$startVars['sale_price']]['meta']['href'];

    // Единицы измерения
    if (isset($startVars['unit_update_ms']) && isset($startVars['uom'])) {
      curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/entity/uom');
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      $uomHref = "";
      foreach ($response['rows'] as $key => $value) {
        if ($value['name'] == $startVars['uom']) {
          $uomHref = $value['meta']['href'];
        }
      }
    }

    // Дополнителные поля
    if (isset($startVars['manufacturer_update_ms'])) {
      curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes');
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      foreach ($response['rows'] as $key => $value) {
        if (in_array($value['type'], ['text', 'string']) && $value['name'] == $startVars['manufacturer']) {
          $attributeHrefMS = $value['meta']['href'];
          break;
        } 
      }
    }

    // Товары
    $dataMS = $this->InitialDataMS(['binding', 'name']);
    $offset = 0;
    do {
      curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/entity/assortment?filter=type=product;type=bundle;archived=true;archived=false&offset=' . $offset);
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      //Занесение данных Мой Склад в массивы
      foreach ($response['rows'] as $key => $row) {
        $dataMS = $this->GetDataMS($row, [$bindingMS, 'name'], ['binding', 'name'], $dataMS);
      }

      $offset += 1000;
    } while (isset($response['meta']['nextHref']));

    $added = [];
    $addedVariant = [];

    // Проверка существования столбца syncms_id
    if ($startVars['category_binding'] == 'ms_id') {
      $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category` LIKE 'syncms_id'");
      if (!isset($query->row['Field'])) {
        $query = $this->db->query("ALTER TABLE " . DB_PREFIX . "category 
          ADD COLUMN syncms_id VARCHAR(255)");
      }
    }

    if ($this->cron || $startVars['product_sql_offset'] == 0) {
      $offset = 0;
    } else {
      $offset = $startVars['product_sql_offset'] - 1;
    }

    $limit = 100;

    while (true) {
      $query = $this->db->query("SELECT $bindingOC, p.product_id AS product_id, p.image AS image, quantity, price, " . ($bindingOC != 'sku' ? "sku, " : "") . "weight, pd.name AS product_name, pd.description AS product_description, 
      " . DB_PREFIX . "manufacturer.name AS manufacturer_name 
      FROM " . DB_PREFIX . "product p
      INNER JOIN " . DB_PREFIX . "product_description pd USING (`product_id`) 
      LEFT JOIN " . DB_PREFIX . "manufacturer USING (`manufacturer_id`)  
      LEFT JOIN " . DB_PREFIX . "product_to_category USING (`product_id`)
      LEFT JOIN " . DB_PREFIX . "category_description cd USING (`category_id`)
      WHERE pd.language_id = '$languageId' " . (isset($startVars['from_group_sql']) ? "AND TRIM(cd.name) = '{$startVars['from_group_sql']}'" : "") . "GROUP BY product_id LIMIT {$limit} OFFSET {$offset}");

      $needFields = ['binding', 'product_id', 'image', 'quantity', 'price', 'sku', 'weight', 'name', 'description', 'manufacturer_name'];

      if ($bindingOC == 'sku') {
        unset($needFields[array_search('sku', $needFields, true)]);
      }

      $needFields = array_values($needFields);

      $dataSQL = $this->GetDataSQL($query, $needFields);

      if (count($dataSQL['product_id']) == 0)
        break;

      // Удаление товаров, которые уже есть в МС
      if ($startVars['binding_name'] == 1) {
        // Модель + наименование
        foreach ($dataSQL['binding'] as $key => $value) {
          foreach (array_keys($dataMS['binding'], $value) as $value1) {
             if ($dataSQL['name'][$key] == $dataMS['name'][$value1]) {
              unset($dataSQL['binding'][$key], $dataSQL['product_id'][$key], $dataSQL['name'][$key], $dataSQL['description'][$key], $dataSQL['image'][$key], $dataSQL['quantity'][$key], $dataSQL['price'][$key], $dataSQL['sku'][$key], $dataSQL['weight'][$key], $dataSQL['manufacturer_name'][$key]);
              break;
             }
          } 
        }
      } else {
        // Только модель
        foreach ($dataSQL['binding'] as $key => $value) {
          if (in_array($value, $dataMS['binding'], true)) {
            unset($dataSQL['binding'][$key], $dataSQL['product_id'][$key], $dataSQL['name'][$key], $dataSQL['description'][$key], $dataSQL['image'][$key], $dataSQL['quantity'][$key], $dataSQL['price'][$key], $dataSQL['sku'][$key], $dataSQL['weight'][$key], $dataSQL['manufacturer_name'][$key]);
          }
        }
      }
      
      $dataSQL['binding'] = array_values($dataSQL['binding']);
      $dataSQL['product_id'] = array_values($dataSQL['product_id']);
      $dataSQL['name'] = array_values($dataSQL['name']);
      $dataSQL['description'] = array_values($dataSQL['description']);
      $dataSQL['image'] = array_values($dataSQL['image']);
      $dataSQL['quantity'] = array_values($dataSQL['quantity']);
      $dataSQL['price'] = array_values($dataSQL['price']);
      if ($bindingOC != 'sku') {
        $dataSQL['sku'] = array_values($dataSQL['sku']);
      }
      $dataSQL['weight'] = array_values($dataSQL['weight']);
      $dataSQL['manufacturer_name'] = array_values($dataSQL['manufacturer_name']);

      $implodeProductId = htmlspecialchars("'" . implode("', '", $dataSQL['product_id']) . "'", ENT_COMPAT);

      $dataSQL['name'] = array_map('stripcslashes', $dataSQL['name']);
      $dataSQL['binding'] = array_map('stripcslashes', $dataSQL['binding']);

      // Категории
      if (isset($startVars['cat_update_ms'])) {
        if ($startVars['category_binding'] == 'name') {
          $query = $this->db->query("SELECT cd.name, p2c.product_id FROM " . DB_PREFIX . "product_to_category p2c INNER JOIN " . DB_PREFIX . "category_description cd ON p2c.category_id = cd.category_id INNER JOIN " . DB_PREFIX . "category_path cp ON p2c.category_id = cp.category_id WHERE cd.language_id = $languageId AND cp.category_id = cp.path_id AND `product_id` IN ($implodeProductId) ORDER BY cp.level DESC");
          $dataCategorySQL = $this->GetDataSQL($query, ['binding', 'product_id']);
        } else {
          $query = $this->db->query("SELECT c.syncms_id, p2c.product_id FROM " . DB_PREFIX . "product_to_category p2c INNER JOIN " . DB_PREFIX . "category c ON p2c.category_id = c.category_id INNER JOIN " . DB_PREFIX . "category_path cp ON p2c.category_id = cp.category_id WHERE cp.category_id = cp.path_id AND `product_id` IN ($implodeProductId) ORDER BY cp.level DESC");
          $dataCategorySQL = $this->GetDataSQL($query, ['binding', 'product_id']);
        }

        $categories = [];
        foreach ($dataCategorySQL['product_id'] as $key => $value) {
          $categories[$value][] = $dataCategorySQL['binding'][$key];
        }
      }

      // Изображения
      if (isset($startVars['image_update_ms'])) {
        $query = $this->db->query("SELECT product_id, image FROM " . DB_PREFIX . "product_image WHERE `product_id` IN ($implodeProductId)");
        $dataImageSQL = $this->GetDataSQL($query, ['product_id', 'image']);

        $images = [];
        foreach ($dataImageSQL['product_id'] as $key => $value) {
          if (!isset($images[$value]) || count($images[$value]) < 9)
            $images[$value][] = $dataImageSQL['image'][$key];
        }
      }

      // Опции
      if (isset($startVars['modif_update_ms'])) {
        $query = $this->db->query("SELECT product_id, od.name name, ovd.name value, price, quantity, price_prefix 
          FROM " . DB_PREFIX . "product_option_value 
          INNER JOIN " . DB_PREFIX . "option_description od USING (option_id)
          INNER JOIN " . DB_PREFIX . "option_value_description ovd USING (option_value_id)
          WHERE `product_id` IN ($implodeProductId) AND od.language_id = $languageId AND ovd.language_id = $languageId");
        $dataOptionSQL = $this->GetDataSQL($query, ['product_id', 'name', 'value', 'price', 'quantity', 'price_prefix']);

        $options = [];
        foreach ($dataOptionSQL['product_id'] as $key => $value) {
          $options[$value][$dataOptionSQL['name'][$key]][] = ['value' => $dataOptionSQL['value'][$key], 'price' => $dataOptionSQL['price'][$key], 'quantity' => $dataOptionSQL['quantity'][$key], 'price_prefix' => $dataOptionSQL['price_prefix'][$key]];
        }
      }

       $this->load->model('tool/image');

      // Формирование массива
      $postData = [];
      $postModifData = [];
      $modifKey = 0;
      $modifQuantity = [];
      $modifPrice = [];
      foreach ($dataSQL['binding'] as $key => $value) {
        $postData[$key]['name'] = $dataSQL['name'][$key];
        $postData[$key][$bindingMS] = $value;
        // Описание
        if (isset($startVars['desc_update_ms'])) {
          $postData[$key]['description'] = strip_tags($dataSQL['description'][$key]);
        }
        // Вес
        if (isset($startVars['weight_update_ms'])) {
          $postData[$key]['weight'] = (float)$dataSQL['weight'][$key];
        }
        // Единица измерения
        if (isset($startVars['unit_update_ms']) && isset($startVars['uom']) && $uomHref != "") {
          $postData[$key]['uom'] = ['meta' => ['href' => $uomHref, 'type' => 'uom', 'mediaType' => 'application/json']];
        }
        // Артикул
        if ($bindingMS != 'article' && isset($startVars['article_update_ms'])) {
          if ($bindingOC == 'sku')
            $postData[$key]['article'] = $dataSQL['binding'][$key];
          else
            $postData[$key]['article'] = $dataSQL['sku'][$key];
        }
        // Группа
        if (isset($startVars['cat_update_ms'])) {
          if (isset($categories[$dataSQL['product_id'][$key]])) {
            foreach ($categories[$dataSQL['product_id'][$key]] as $category) {
              if ($startVars['category_binding'] == "ms_id")
                $groupKey = array_search($category, $dataCategoryMS['id']);
              else 
                $groupKey = array_search($category, $dataCategoryMS['name']);

              if ($groupKey !== false) {
                $postData[$key]['productFolder'] = ['meta' => ['href' => $dataCategoryMS['href'][$groupKey], 'type' => 'productfolder', 'mediaType' => 'application/json']];
                break;
              }
            }
          }
        }
        // Цена продажи
        $postData[$key]['salePrices'] = [['value' => (float)$dataSQL['price'][$key] * 100, 'priceType' => ['meta' => ['href' => $priceMS['href'], 'type' => 'pricetype', 'mediaType' => 'application/json']]]];
        // Произодитель
        if (isset($startVars['manufacturer_update_ms'])) {
          $postData[$key]['attributes'] = [['meta' => ['href' => $attributeHrefMS, 'type' => 'attributemetadata', 'mediaType' => 'application/json'], 'value' => $dataSQL['manufacturer_name'][$key]]];
        }
        // Изображения
        if (isset($startVars['image_update_ms'])) {
          if ($dataSQL['image'][$key] != '') {            
            if (isset($startVars['image_size_ms'])) {
              $explode = [];
              if (strpos($startVars['image_size_ms'], "x") !== false) {
                $explode = explode("x", $startVars['image_size_ms']);
              } else if (strpos($startVars['image_size_ms'], "х") !== false) {
                $explode = explode("х", $startVars['image_size_ms']);
              }

              if ($explode != []) {
                $ext = pathinfo($dataSQL['image'][$key], PATHINFO_EXTENSION);
                $dataSQL['image'][$key] = $this->model_tool_image->resize($dataSQL['image'][$key], $explode[0], $explode[1]);
                $dataSQL['image'][$key] = str_replace(HTTPS_SERVER . "image/", "", $dataSQL['image'][$key]);
                $dataSQL['image'][$key] = str_replace(HTTP_SERVER . "image/", "", $dataSQL['image'][$key]);
                $dataSQL['image'][$key] = str_replace(['/image/cache/webp/', '.webp'], ['/image/cache/', '.' . $ext], $dataSQL['image'][$key]);
              }
            }

            if (is_file(DIR_IMAGE . $dataSQL['image'][$key])) {
              $postData[$key]['images'][] = ["filename" => basename($dataSQL['image'][$key]), "content" => base64_encode(file_get_contents(DIR_IMAGE . $dataSQL['image'][$key]))];
            }
            if (isset($images[$dataSQL['product_id'][$key]])) {
              foreach ($images[$dataSQL['product_id'][$key]] as $image) {
                if (isset($startVars['image_size_ms'])) {
                  $explode = [];
                  if (strpos($startVars['image_size_ms'], "x") !== false) {
                    $explode = explode("x", $startVars['image_size_ms']);
                  } else if (strpos($startVars['image_size_ms'], "х") !== false) {
                    $explode = explode("х", $startVars['image_size_ms']);
                  }

                  if ($explode != []) {
                    $ext = pathinfo($image, PATHINFO_EXTENSION);
                    $image = $this->model_tool_image->resize($image, $explode[0], $explode[1]);
                    $image = str_replace(HTTPS_SERVER . "image/", "", $image);
                    $image = str_replace(HTTP_SERVER . "image/", "", $image);
                    $image = str_replace(['/image/cache/webp/', '.webp'], ['/image/cache/', '.' . $ext], $image);
                  }
                }
                if (is_file(DIR_IMAGE . $image)) {
                  $postData[$key]['images'][] = ["filename" => basename($image), "content" => base64_encode(file_get_contents(DIR_IMAGE . $image))]; 
                }
              }
            }
          }
        }
        // Модификации
        if (isset($startVars['modif_update_ms'])) {
          if (isset($options[$dataSQL['product_id'][$key]])) {
            $modifications = $this->Cartesian($options[$dataSQL['product_id'][$key]]);

            foreach ($modifications as $modification) {
              $price = $dataSQL['price'][$key];
              $quantity = 9999999999;
              foreach ($modification as $name => $option) {
                $postModifData[$modifKey]['characteristics'][] = ['name' => stripcslashes($name), 'value' => stripcslashes($option['value'])];
                if ($option['price_prefix'] == '+')
                  $price += $option['price'];
                else
                  $price -= $option['price'];
                
                if ($option['quantity'] < $quantity)
                  $quantity = $option['quantity'];
              }

              $postModifData[$modifKey]['salePrices'] = [['value' => (float)$price * 100, 'priceType' => ['meta' => ['href' => $priceMS['href'], 'type' => 'pricetype', 'mediaType' => 'application/json']]]];
              $postModifData[$modifKey]['product'] = $key;
              $modifQuantity[$modifKey] = $quantity;
              $modifPrice[$modifKey] = $price;
              $modifKey++;
              array_push($addedVariant, sprintf('%s_%s', $value, $dataSQL['name'][$key]));
            }
          }
        }
        array_push($added, sprintf('%s_%s', $value, $dataSQL['name'][$key]));
      } 

      if (count($postData) > 0) {
        $postData = json_encode($postData, JSON_UNESCAPED_SLASHES);
        curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/product");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = json_decode(curl_exec($ch), true);
        $response = $this->CheckResponse($response, $ch, "", false);

        // Модификации
        if (isset($startVars['modif_update_ms'])) {
          foreach ($postModifData as $key => $value) {
            if (isset($response[$postModifData[$key]['product']]['meta']))
              $postModifData[$key]['product'] = ['meta' => $response[$postModifData[$key]['product']]['meta']];
          }

          $responseModif = [];
          foreach (array_chunk($postModifData, 1000) as $postModifDataSmall) {
            if (!empty($postModifDataSmall)) {
              $postModifDataSmall = json_encode($postModifDataSmall, JSON_UNESCAPED_SLASHES);
              curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/variant");
              curl_setopt($ch, CURLOPT_POST, 1);
              curl_setopt($ch, CURLOPT_POSTFIELDS, $postModifDataSmall);
              $responseModifTmp = json_decode(curl_exec($ch), true);
              $responseModifTmp = $this->CheckResponse($responseModifTmp, $ch, "", false);
              $responseModif[] = $responseModifTmp;
            }
          }
        }

        $createSupply = true;
        $positions = [];
        if (isset($startVars['stock_update_ms']) && isset($startVars['supplier'])) {
          curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/counterparty?filter=externalCode=" . $startVars['supplier']);
          curl_setopt($ch, CURLOPT_POST, 0);
          $responseAgent = json_decode(curl_exec($ch), true);
          $responseAgent = $this->CheckResponse($responseAgent, $ch);

          if (isset($responseAgent['rows'][0]['meta']['href'])) {
            $postDataSupply = [];
            $postDataSupply["description"] = "Выгрузка товаров";
            $postDataSupply["organization"] = ["meta" => ["href" => $startVars['organization'], "type" => "organization", "mediaType" => "application/json"]];
            $postDataSupply["store"] = ["meta" => ["href" => $startVars['store'], "type" => "store", "mediaType" => "application/json"]];
            $postDataSupply["agent"] = ["meta" => ["href" => $responseAgent['rows'][0]['meta']['href'], "type" => "counterparty", "mediaType" => "application/json"]];
          } else {
            $createSupply = false;
          }
        } else {
          $createSupply = false;
        }

        // Товары
        foreach ($response as $key => $value) {
          $value = $this->CheckResponse($value, $ch, "Товар {$added[$key]}: ", false);
          if ($value && $createSupply && $dataSQL['quantity'][$key] > 0) {
            $positions[] = ["quantity" => (float)$dataSQL['quantity'][$key], "price" => $dataSQL['price'][$key] * 100, "assortment" => ["meta" => ["href" => $value["meta"]["href"], "type" => "product", "mediaType" => "application/json"]]];
          }
        }

        // Модификации
        if (isset($startVars['modif_update_ms']) && isset($responseModif)) {
          foreach ($responseModif as $key => $responseModifSmall) {
            foreach ($responseModifSmall as $key1 => $value) {
              $value = $this->CheckResponse($value, $ch, "Модификация товара {$addedVariant[$key1 + $key * 1000]}: ", false);
              if ($value && $createSupply && $modifQuantity[$key1 + $key * 1000] > 0) {
                $positions[] = ["quantity" => (float)$modifQuantity[$key1 + $key * 1000], "price" => $modifPrice[$key] * 100, "assortment" => ["meta" => ["href" => $value["meta"]["href"], "type" => "variant", "mediaType" => "application/json"]]];
              }
            }
          }
        }

        if ($createSupply && isset($positions)) {
          foreach (array_chunk($positions, 1000) as $positionsSmall) {
            $postDataSupply['positions'] = $positionsSmall;
            curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/supply");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postDataSupply, JSON_UNESCAPED_SLASHES));
            $response = json_decode(curl_exec($ch), true);
            $response = $this->CheckResponse($response, $ch, "Создание приемки: ", false);
          }
        }
      }

      if ($startVars['product_sql_offset'] != 0) {
        break;
      }
      
      $offset += $limit;
    }

    curl_close($ch);

    $added = array_unique($added);

    $this->logText .= 'Добавлено товаров в Мой Склад: ' . count($added) . PHP_EOL;
    foreach ($added as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    
    $this->LogWrite();
    $this->Output('Успешно. Товаров в Мой Склад добавлено: ' . count($added), 'success');
  }

  public function CategoryAddMS()
  {
    if (isset($this->request->get['cron'])) {
      $this->cron = true;
    } else {
      $this->cron = false;
    }

    $this->db->query("set session wait_timeout=28800");
    $this->logText = date('H:i:s d.m.Y') . ' Добавление категорий в Мой Склад' . PHP_EOL;
    
    $startVars = $this->GetStartVars(["category_binding"]);

    $this->CreateLock("CategoryAddMS");

    if (!isset($startVars['manufacturer'])) {
      $startVars['manufacturer'] = '';
    }

    $languageId = $this->GetLanguageId();

    $ch = $this->CurlInit($startVars['headers']);

    $dataMS = $this->InitialDataMS(['id', 'name']);

    // Получение групп
    $offset = 0;
    $key = 0;
    do {
      //Получение категорий по curl
      curl_setopt($ch, CURLOPT_URL, 'https://api.moysklad.ru/api/remap/1.2/entity/productfolder?filter=archived=true;archived=false&offset=' . $offset);
      $response = json_decode(curl_exec($ch), true);
      $response = $this->CheckResponse($response, $ch);

      foreach ($response['rows'] as $key => $row) {
        $dataMS = $this->GetDataMS($row, ['name'], ['name'], $dataMS);
        $dataMS['meta'][$key] = $row['meta'];
      }

      $offset += 1000;
    } while (isset($response['meta']['nextHref']));

    $added = [];

    // Категории ОС
    $query = $this->db->query("SELECT c.category_id, cd.name, parent_id
      FROM " . DB_PREFIX . "category c
      INNER JOIN " . DB_PREFIX . "category_description cd ON c.category_id = cd.category_id
      WHERE cd.language_id = $languageId");
    $dataCategorySQL = $this->GetDataSQL($query, ['category_id', 'name', 'parent_id']);

    $query = $this->db->query("SELECT c.category_id, cd.name, parent_id
      FROM " . DB_PREFIX . "category c
      INNER JOIN " . DB_PREFIX . "category_description cd ON c.category_id = cd.category_id
      WHERE cd.language_id = $languageId");
    $dataSQL = $this->GetDataSQL($query, ['category_id', 'name', 'parent_id']);

    // Удаление товаров, которые уже есть в МС
    foreach ($dataSQL['name'] as $key => $value) {
      if (in_array($value, $dataMS['name'], true)) {
        unset($dataSQL['category_id'][$key], $dataSQL['name'][$key], $dataSQL['parent_id'][$key]);
      }
    }
      
    $dataSQL['category_id'] = array_values($dataSQL['category_id']);
    $dataSQL['name'] = array_values($dataSQL['name']);
    $dataSQL['parent_id'] = array_values($dataSQL['parent_id']);

    $dataSQL['name'] = array_map('stripcslashes', $dataSQL['name']);

    // Формирование массива
    $postData = [];
    $parent = [];
    foreach ($dataSQL['name'] as $key => $value) {
      $postData[$key]['name'] = $value;
      
      if ($dataSQL['parent_id'][$key] != 0) {
        $parentSqlKey = array_search($dataSQL['parent_id'][$key], $dataCategorySQL['category_id'], true);
        $parentKey = array_search($dataCategorySQL['name'][$parentSqlKey], $dataMS['name'], true);
        if ($parentKey !== false) {
          $postData[$key]['productFolder'] = ['meta' => $dataMS['meta'][$parentKey]];
        } else {
          $parentSqlKey = array_search($dataSQL['parent_id'][$key], $dataSQL['category_id'], true);
          $parent[$key] = $parentSqlKey;
        }
      }
      
      array_push($added, sprintf('%s', $dataSQL['name'][$key]));
    }

    if (count($postData) > 0) {
      foreach (array_chunk($postData, 1000) as $postDataSmall) {
        $postDataParent = [];
        $postDataSmall = json_encode($postDataSmall, JSON_UNESCAPED_SLASHES);
        curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/productfolder");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataSmall);
        $response = json_decode(curl_exec($ch), true);
        $response = $this->CheckResponse($response, $ch, "", false);

        foreach ($response as $key => $value) {
          if (isset($parent[$key]) 
              && isset($value['meta']) 
              && isset($response[$parent[$key]]['meta'])) {
            $postDataParent[$key]['meta'] = $value['meta'];
            $postDataParent[$key]['productFolder'] = ['meta' => $response[$parent[$key]]['meta']];
          }
        }

        if (!empty($postDataParent)) {
          $postDataParent = array_values($postDataParent);
          $postDataParent = json_encode($postDataParent, JSON_UNESCAPED_SLASHES);
          curl_setopt($ch, CURLOPT_URL,"https://api.moysklad.ru/api/remap/1.2/entity/productfolder");
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataParent);
          $responseParent = json_decode(curl_exec($ch), true);
          $responseParent = $this->CheckResponse($responseParent, $ch, "", false);
        }

        foreach ($response as $key => $value) {
          $value = $this->CheckResponse($value, $ch);
        } 
      }
    }

    curl_close($ch);

    $added = array_unique($added);

    $this->logText .= 'Добавлено категорий в Мой Склад: ' . count($added) . PHP_EOL;
    foreach ($added as $key => $value) {
      $this->logText .= stripcslashes($value) . '; ';
    }
    
    $this->LogWrite();
    $this->Output('Успешно. Категорий в Мой Склад добавлено: ' . count($added), 'success');
  }

  private function LogWrite()
  {
    $resultText = '========================================================================' . PHP_EOL;
    $resultText .= $this->logText;
    $resultText .= PHP_EOL . PHP_EOL;

    $limitSize = 100 * 1024 * 1024;
    if ($this->version == '2.1')
      file_put_contents(DIR_APPLICATION . 'controller/module/syncms_log.txt', $resultText . file_get_contents(DIR_APPLICATION . 'controller/module/syncms_log.txt', false, null, 0, 0, $limitSize));
    else
      file_put_contents(DIR_APPLICATION . 'controller/extension/module/syncms_log.txt', $resultText . file_get_contents(DIR_APPLICATION . 'controller/extension/module/syncms_log.txt', false, null, 0, $limitSize));
  }

  public function LogClear()
  {
    $startVars = $this->GetStartVars();
    if ($this->version == '2.1')
      file_put_contents(DIR_APPLICATION . 'controller/module/syncms_log.txt', '');
    else
      file_put_contents(DIR_APPLICATION . 'controller/extension/module/syncms_log.txt', '');
    $this->session->data['success'] = 'Лог успешно очищен';
    $this->response->redirect($startVars['redirectURL']);
  }

  private function Cartesian(array $input)
  {
    $result = [[]];
    foreach ($input as $key => $values) {
      $append = [];
      foreach ($values as $value) {
        foreach ($result as $data) {
          $append[] = $data + [$key => $value];
        }
      }
      $result = $append;
    }

    return $result;
  }
}
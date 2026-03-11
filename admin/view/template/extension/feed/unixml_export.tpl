<?php if(isset($export_system)){ //окно системных настроек в списке экспортов ?>

  <div class="row top-row">
    <div class="col-sm-5">
      <h3 style="line-height:35px;">Выгрузка <strong><?php echo $export_system; ?></strong>
        <span class="goto_feed_setting btn btn-link" data-feed="<?php echo $export_system; ?>">Перейти в настройки</span>
      </h3>
    </div>
    <div class="col-sm-7 text-right">
      <span class="btn btn-success" id="export_system_message" style="display:none;">Сохранили</span>
      <span class="close1 btn btn-default" data-dismiss="modal" aria-label="Close" id="export_system_close" data-toggle="tooltip" data-placement="bottom" title="Закрыть без сохранения"><i class="fa fa-times" aria-hidden="true"></i> Закрыть</span>
      <span class="btn btn-success" id="save_export_system_item"><i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить файл и настройки</span>
    </div>
  </div>

  <ul class="nav nav-tabs nav-export-system" style="margin-top:15px;">
    <li class="active"><a href="#tab-system-xml" data-toggle="tab"><i class="fa fa-file-code-o" aria-hidden="true"></i> Структура XML</a></li>
    <li><a href="#tab-system-del" data-toggle="tab"><i class="fa fa-trash" aria-hidden="true"></i> Удалить фид</a></li>
  </ul>

  <div class="tab-content tabs-data" style="margin-top:-2px;">

    <!--tab-system-xml-->
    <div class="tab-pane active" id="tab-system-xml">
      <small style="position:absolute;margin-top:-20px;">Файл: <?php echo $server_path; ?><b><?php echo $file_path; ?></b> (Синтаксис php)</small>
      <textarea id="export_system_file" data-action="<?echo $action; ?>" <?php if($file_data_error){ ?>readonly="readonly"<?php } ?> style="overflow:scroll;width:100%;min-height:500px;border:1px solid #aaa;outline:none!important;"><?php echo $file_data; ?></textarea>
    </div>
    <!--/tab-system-xml-->

    <!--tab-system-del-->
    <div class="tab-pane" id="tab-system-del">
      <div style="font-weight:bold;font-size:24px;margin-bottom:15px;">Вы точно хотите удалить фид?</div>
      <div class="alert alert-info">Когда вы удалите выгрузку, сам файл <?php echo $export_system; ?>.php остается на сервере и настройки фида сохраняются. Это действие безопасное. Фид можно будет вернуть в любой момент через добавление указав Код выгрузки <b><?php echo $export_system; ?></b>. UniXML увидит и сам исполяемый файл и настройки которые были сохранены ранее.</div>
      <span class="btn btn-warning" data-feed="<?php echo $export_system; ?>" id="delete_feed">Удалить фид</span>
      <span class="btn btn-success" onclick="$('#export_system_close').click();">Нет, закрыть окно</span>
    </div>
    <!--/tab-system-del-->

  </div>

<?php } ?>
<?php if(isset($exports)){ //список экспортов ?>
  <?php foreach($exports as $export){ ?>
    <tr id="feed_list_<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>">
      <td class="text-center list_sort"><?php echo $export['export_num']; ?></td>
      <td class="text-left"><?php echo $export['name']; ?></td>
      <td class="text-center"><span class="export_list_status status<?php echo $export['status']; ?>" title="<?php if($export['status']){ ?>Включена<?php }else{ ?>Выключена<?php } ?>"></td>
      <td class="text-left">
        <div>Генерация на лету: <a href="<?php echo $export['link_direct']; ?>" target="_blank" title="Отроется в новом окне" data-toggle="tooltip"><?php echo $export['link_direct']; ?></a>
        <div>Генерация в файл:&nbsp; <a href="<?php echo $export['link_cron']; ?>" target="_blank" title="Отроется в новом окне" data-toggle="tooltip"><?php echo $export['link_cron']; ?></a>
        <div>Готовый xml файл:&nbsp; <a href="<?php echo $export['link_file']; ?>" target="_blank" title="Отроется в новом окне" data-toggle="tooltip"><?php echo $export['link_file']; ?></a>
      </td>
      <td class="text-right">
        <nobr>
          <span class="export_setting export_setting<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>" data-toggle="modal" data-target="#export_setting"><span data-toggle="tooltip" title="Редaктировать настройки выгрузки" class="btn btn-info"><i class="fa fa-pencil"></i></span></span>
          <span class="export_system export_system<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>" data-toggle="modal" data-target="#export_system"><span data-toggle="tooltip" title="Структура и системные настройки выгрузки" class="btn btn-default"><i class="fa fa-code" aria-hidden="true"></i></span></span>
          <span class="export_trash export_trash<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>"><span data-toggle="tooltip" title="Переместить в корзину (Убрать из списка, это не удаление)" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></span></span>
        </nobr>
      </td>
    </tr>
  <?php } ?>
<?php } ?>
<?php if(isset($trash)){ //список в корзине ?>
  <?php if($trash){ ?>
    <?php foreach($trash as $export){ ?>
    <tr id="feed_list_<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>">
      <td class="text-center list_sort"><?php echo $export['export_num']; ?></td>
      <td class="text-left"><?php echo $export['name']; ?></td>
      <td class="text-center"><span class="export_list_status status<?php echo $export['status']; ?>" title="<?php if($export['status']){ ?>Включена<?php }else{ ?>Выключена<?php } ?>"></td>
      <td class="text-left">
        <div>Генерация на лету: <a href="<?php echo $export['link_direct']; ?>" target="_blank" title="Отроется в новом окне" data-toggle="tooltip"><?php echo $export['link_direct']; ?></a>
        <div>Генерация в файл:&nbsp; <a href="<?php echo $export['link_cron']; ?>" target="_blank" title="Отроется в новом окне" data-toggle="tooltip"><?php echo $export['link_cron']; ?></a>
        <div>Готовый xml файл:&nbsp; <a href="<?php echo $export['link_file']; ?>" target="_blank" title="Отроется в новом окне" data-toggle="tooltip"><?php echo $export['link_file']; ?></a>
      </td>
      <td class="text-right">
        <nobr>
          <span class="export_setting export_setting<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>" data-toggle="modal" data-target="#export_setting"><span data-toggle="tooltip" title="Редaктировать настройки выгрузки" class="btn btn-info"><i class="fa fa-pencil"></i></span></span>
          <span class="export_system export_system<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>" data-toggle="modal" data-target="#export_system"><span data-toggle="tooltip" title="Структура и системные настройки выгрузки" class="btn btn-default"><i class="fa fa-code" aria-hidden="true"></i></span></span>
          <span class="export_to_list export_trash<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>"><span data-toggle="tooltip" title="Переместить в список фидов (Убрать с корзины)" class="btn btn-success"><i class="fa fa-repeat" aria-hidden="true"></i></span></span>
        </nobr>
      </td>
    </tr>
    <?php } ?>
  <?php }else{ ?>
    <tr>
      <td class="text-center" colspan="5">Выгрузки отсутствуют.</td>
    </tr>
  <?php } ?>
<?php } ?>
<?php if(isset($feed)){ //окно настройки экспорта ?>

  <div class="exp-imp">
    <div class="row top-row">
      <div class="col-sm-5">
        <h3 style="line-height:35px;">Настройка выгрузки <strong><?php echo $feed; ?></strong></h3>
        <span class="btn btn-default export_full_btn" onclick="$('#export_setting .modal-dialog').toggleClass('export_full');$(this).toggleClass('export_full');"><i class="fa fa-arrow-left" aria-hidden="true"></i><i class="fa fa-arrow-right" aria-hidden="true"></i><br>Расширить</span>
        <span class="btn btn-default export_full_btn2" style="display:none;" onclick="$('.export_full_btn').click();"><i class="fa fa-arrow-right" aria-hidden="true"></i><i class="fa fa-arrow-left" aria-hidden="true"></i><br>Сузить</span>
      </div>
      <div class="col-sm-7 text-right">
        <span class="btn btn-success" id="save_export_message" style="display:none;">Сохранили</span>
        <span class="close1 btn btn-default" data-dismiss="modal" aria-label="Close" id="export_setting_close" data-toggle="tooltip" data-placement="bottom" title="Закрыть без сохранения"><i class="fa fa-times" aria-hidden="true"></i> Закрыть</span>
        <a class="btn btn-info export-import" href="<?php echo $export_setting; ?>&feed=<?php echo $feed; ?>" data-toggle="tooltip" data-placement="bottom" title="Экспортировать/сохранить настройки для <?php echo $feed; ?>. Внимание! Перед тем как сделать экспорт сохраните текущие настройки."><i class="fa fa-upload" aria-hidden="true"></i> Экспорт настроек</a>
        <span class="btn btn-info export-import upload_file" data-feed="<?php echo $feed; ?>" data-toggle="tooltip" data-placement="bottom" title="Импортировать/загрузить настройки для <?php echo $feed; ?>. Внимание! При импортировании все настройки перезаписываются."><i class="fa fa-download" aria-hidden="true"></i> Импорт настроек</span>
        <span class="btn btn-success" id="save_export_item"><i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить настройки</span>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-10">

      <h3 class="setting_item_top">1. Основные настройки</h3>

      <form style="overflow-y:scroll;" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-unixml-export" class="form-horizontal">
        <input type="hidden" name="feed" value="<?php echo $feed; ?>">

          <!--export-block-1-->
          <div id="export-block-1" class="export-block-item">

            <h3>1. Основные настройки</h3>

            <!--1.1-->
              <div class="form-group" id="export-block-1-1">
                <span class="field_counter">1.1</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/1-1" target="_blank">
                    Статус выгрузки
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Статус при который будет включена или выключена выгрузка <?php echo $feed; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input style="display:none;" type="checkbox" <?php if($unixml_status){ ?>checked="checked"<?php } ?> class="checkbox_exp" id="unixml_<?php echo $feed; ?>_status" name="unixml_<?php echo $feed; ?>_status" value="1">
                  <label for="unixml_<?php echo $feed; ?>_status"></label>
                </div>
              </div>
            <!--/1.1-->

            <!--1.2-->
              <div class="form-group" id="export-block-1-2">
                <span class="field_counter">1.2</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/1-2" target="_blank">
                    Компания
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Названия магазина в фиде <?php echo $feed; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_name" type="text" name="unixml_<?php echo $feed; ?>_name" value="<?php echo $unixml_name; ?>" class="form-control">
                </div>
              </div>
            <!--/1.2-->

            <!--1.3-->
              <div class="form-group" id="export-block-1-3">
                <span class="field_counter">1.3</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/1-3" target="_blank">
                    Язык выгрузки
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      В выгрузку <?php echo $feed; ?> будут попадать данные на выбранном языке
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select name="unixml_<?php echo $feed; ?>_language" id="unixml_<?php echo $feed; ?>_language" class="form-control">
                    <?php foreach($languages as $language){ ?>
                      <option value="<?php echo $language['language_id']; ?>" <?php if($language['language_id'] == $unixml_language){ ?>selected="selected"<?php } ?>><?php echo $language['name']; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <!--/1.3-->

            <!--1.4-->
              <div class="form-group" id="export-block-1-4">
                <span class="field_counter">1.4</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/1-4" target="_blank">
                    Валюта выгрузки
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Цены в выгрузке <?php echo $feed; ?> будут пересчитаны согласно курса выбранной валюты
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select name="unixml_<?php echo $feed; ?>_currency" id="unixml_<?php echo $feed; ?>_currency" class="form-control">
                    <?php foreach($currencies as $currency){ ?>
                      <option value="<?php echo $currency['currency_id']; ?>" <?php if($currency['currency_id'] == $unixml_currency){ ?>selected="selected"<?php } ?>><?php echo $currency['title']; ?> (<?php echo $currency['code']; ?>) - курс <?php echo $currency['value']; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <!--/1.4-->

            <!--1.5-->
              <div class="form-group" id="export-block-1-5">
                <span class="field_counter">1.5</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/1-5" target="_blank">
                    Стоимость доставки
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Стоимость надо указывать целым числом без валюты
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_delivery_cost" placeholder="Стоимость надо указывать целым числом без валюты" type="text" name="unixml_<?php echo $feed; ?>_delivery_cost" value="<?php echo $unixml_delivery_cost; ?>" class="form-control">
                </div>
              </div>
            <!--/1.5-->

            <!--1.6-->
              <div class="form-group" id="export-block-1-6">
                <span class="field_counter">1.6</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/1-6" target="_blank">
                    Сроки доставки
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Можно указать например 2 - это будет 2 дня, или же 1-2 это будет день-два. Все зависит от конкретного маркетплейса и его правил заполнения сроков
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_delivery_time" placeholder="Можно указать например 2 - это будет 2 дня или же 1-2 это будет день-два" type="text" name="unixml_<?php echo $feed; ?>_delivery_time" value="<?php echo $unixml_delivery_time; ?>" class="form-control">
                </div>
              </div>
            <!--/1.6-->

            <!--1.7-->
              <div class="form-group" id="export-block-1-7">
                <span class="field_counter">1.7</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/1-7" target="_blank">
                    Час перескока
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Время после которого доставка будет считаться со следующего дня. Заполнение и применение зависит от конкретного маркетплейса и его правил
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_delivery_jump" placeholder="Время после которого доставка будет считаться со следующего дня" type="text" name="unixml_<?php echo $feed; ?>_delivery_jump" value="<?php echo $unixml_delivery_jump; ?>" class="form-control">
                </div>
              </div>
            <!--/1.7-->

          </div>
          <!--/export-block-1-->

          <!--export-block-2-->
          <div id="export-block-2" class="export-block-item">
            <h3>2. Фильтр товаров</h3>

            <!--2.1-->
              <div class="form-group" id="export-block-2-1">
                <span class="field_counter">2.1</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/2-1" target="_blank">
                    Товары
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      <b>Выгружаем только</b> - выгрузка только выбранных товаров несмотря на настройки категории, брендов и т.п.
                      <hr><b>Запретить выгружать</b> - выгрузка всех товаров учитывая настройки кроме выбранных. Они не попадут в выгрузку.
                      <hr><b>А также выгружаем</b> - выгрузка всех товаров учитывая настройки + выбранные, даже если они не проходят по настройкам.
                    </div>
                  </a>
                  <select name="unixml_<?php echo $feed; ?>_products_mode" class="form-control" style="margin-left:-15px;width:calc(100% + 15px);font-weight:400;margin-top:10px;">
                    <option value="" <?php if(!$unixml_products_mode){ ?>selected="selected"<?php } ?>>Выгружаем только</option>
                    <option value="1" <?php if($unixml_products_mode == 1){ ?>selected="selected"<?php } ?>>Запретить выгружать</option>
                    <option value="2" <?php if($unixml_products_mode == 2){ ?>selected="selected"<?php } ?>>А также выгружаем</option>
                  </select>
                </label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <input type="text" name="unixml_<?php echo $feed; ?>_product" value="" placeholder="Введите название товара или артикул" id="input-products" class="form-control" />
                    <div class="input-group-btn">
                      <span class="btn btn-danger" onclick="$('#input-products').val('');$('#export-block-2-1 .dropdown-menu').hide();">
                        <i class="fa fa-times-circle-o" aria-hidden="true"></i>
                      </span>
                    </div>
                  </div>
                  <div id="unixml_products" class="well well-sm" style="max-height: 300px; min-height: 35px; overflow: auto;">
                    <?php if($unixml_products){ ?>
                      <?php foreach($unixml_products as $product){ ?>
                        <div id="unixml_<?php echo $feed; ?>_products<?php echo $product['product_id']; ?>">
                          <i class="fa fa-minus-circle"></i>
                          <?php echo $product['name']; ?>
                          <a target="_blank" href="<?php echo $product['edit']; ?>" title="Редактировать. Откроется в новой вкладке" data-toggle="tooltip"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                          <a target="_blank" href="<?php echo $product['view']; ?>" title="Посмотреть товар. Откроется в новой вкладке" data-toggle="tooltip"><i class="fa fa-eye" aria-hidden="true"></i></a>
                          <input type="hidden" name="unixml_<?php echo $feed; ?>_products[]" value="<?php echo $product['product_id']; ?>" />
                        </div>
                      <?php } ?>
                    <?php } ?>
                  </div>
                </div>
              </div>
            <!--/2.1-->

            <!--2.2-->
              <div class="form-group" id="export-block-2-2">
                <span class="field_counter">2.2</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/2-2" target="_blank">
                    Категории
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Выгружать товары только из выбранных категорий.<br>Выбирать надо всегда конечные категории. Категории выше по иерархии будут в фиде в блоке categories.<br>
                      В некоторых маркетплейсах категории не выводятся - это нормально.<br>
                      Если после категории стоит плюс - в категории есть подкатегории и при клике на категорию откроются его подкатегории.<br>
                      Для выбора категории кликайте слева по ползунку
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <div id="unixml_categories" class="scrollbox" style="max-height:800px;border:1px solid #ccc;overflow:auto;">
                    <?php foreach($categories as $category) { ?>
                      <div data-id="<?php echo $category['category_id']; ?>">
                          <input id="category-<?php echo $category['category_id']; ?>" class="checkbox_exp_mini" type="checkbox" name="unixml_<?php echo $feed; ?>_categories[]" value="<?php echo $category['category_id']; ?>" <?php if (in_array($category['category_id'], $unixml_categories)) { ?>checked="checked"<?php } ?>>
                          <label for="category-<?php echo $category['category_id']; ?>"></label>
                          <span class="category_item_name_span"><?php echo $category['name']; ?> <?php if ($category['child']) { ?><span>+</span><?php } ?></span>

                          <?php if ($category['child']) { ?>
                            <div class="category_child_block child_<?php echo $category['category_id']; ?>">
                              <?php foreach($category['child'] as $child) { ?>
                                <div class="category_child_item">
                                  <input id="category-<?php echo $child['category_id']; ?>" class="checkbox_exp_mini" type="checkbox" name="unixml_<?php echo $feed; ?>_categories[]" value="<?php echo $child['category_id']; ?>" <?php if (in_array($child['category_id'], $unixml_categories)) { ?>checked="checked"<?php } ?>>
                                  <label for="category-<?php echo $child['category_id']; ?>"></label>
                                  <span class="category_item_name_span"><?php echo $child['name']; ?> <?php if ($child['child']) { ?><span>+</span><?php } ?></span>

                                  <?php if ($child['child']) { ?>
                                    <div class="category_child_block">
                                      <?php foreach($child['child'] as $child2) { ?>
                                        <div class="category_child_item">
                                          <input id="category-<?php echo $child2['category_id']; ?>" class="checkbox_exp_mini" type="checkbox" name="unixml_<?php echo $feed; ?>_categories[]" value="<?php echo $child2['category_id']; ?>" <?php if (in_array($child2['category_id'], $unixml_categories)) { ?>checked="checked"<?php } ?>>
                                          <label for="category-<?php echo $child2['category_id']; ?>"></label>
                                          <span class="category_item_name_span"><?php echo $child2['name']; ?> <?php if ($child2['child']) { ?><span>+</span><?php } ?></span>

                                          <?php if ($child2['child']) { ?>
                                            <div class="category_child_block">
                                              <?php foreach($child2['child'] as $child3) { ?>
                                                <div class="category_child_item">
                                                  <input id="category-<?php echo $child3['category_id']; ?>" class="checkbox_exp_mini" type="checkbox" name="unixml_<?php echo $feed; ?>_categories[]" value="<?php echo $child3['category_id']; ?>" <?php if (in_array($child3['category_id'], $unixml_categories)) { ?>checked="checked"<?php } ?>>
                                                  <label for="category-<?php echo $child3['category_id']; ?>"></label>
                                                  <span class="category_item_name_span"><?php echo $child3['name']; ?> <?php if ($child3['child']) { ?><span>+</span><?php } ?></span>

                                                  <?php if ($child3['child']) { ?>
                                                    <div class="category_child_block">
                                                      <?php foreach($child3['child'] as $child4) { ?>
                                                        <div class="category_child_item">
                                                          <input id="category-<?php echo $child4['category_id']; ?>" class="checkbox_exp_mini" type="checkbox" name="unixml_<?php echo $feed; ?>_categories[]" value="<?php echo $child4['category_id']; ?>" <?php if (in_array($child4['category_id'], $unixml_categories)) { ?>checked="checked"<?php } ?>>
                                                          <label for="category-<?php echo $child4['category_id']; ?>"></label>
                                                          <span class="category_item_name_span"><?php echo $child4['name']; ?> <?php if ($child4['child']) { ?><span>+</span><?php } ?></span>

                                                          <?php if ($child4['child']) { ?>
                                                            <div class="category_child_block">
                                                              <?php foreach($child4['child'] as $child5) { ?>
                                                                <div class="category_child_item">
                                                                  <input id="category-<?php echo $child5['category_id']; ?>" class="checkbox_exp_mini" type="checkbox" name="unixml_<?php echo $feed; ?>_categories[]" value="<?php echo $child5['category_id']; ?>" <?php if (in_array($child5['category_id'], $unixml_categories)) { ?>checked="checked"<?php } ?>>
                                                                  <label for="category-<?php echo $child5['category_id']; ?>"></label>
                                                                  <span class="category_item_name_span"><?php echo $child5['name']; ?> <?php if ($child5['child']) { ?><span>+</span><?php } ?></span>
                                                                </div>
                                                              <?php } ?>
                                                              <a class="select_all">Выбрать все</a> / <a class="unselect_all">Убрать все</a>
                                                            </div>
                                                          <?php } ?>

                                                        </div>
                                                      <?php } ?>
                                                      <a class="select_all">Выбрать все</a> / <a class="unselect_all">Убрать все</a>
                                                    </div>
                                                  <?php } ?>

                                                </div>
                                              <?php } ?>
                                              <a class="select_all">Выбрать все</a> / <a class="unselect_all">Убрать все</a>
                                            </div>
                                          <?php } ?>

                                        </div>
                                      <?php } ?>
                                      <a class="select_all">Выбрать все</a> / <a class="unselect_all">Убрать все</a>
                                    </div>
                                  <?php } ?>

                                </div>
                              <?php } ?>
                              <a class="select_all">Выбрать все</a> / <a class="unselect_all">Убрать все</a>
                            </div>
                          <?php } ?>

                      </div>
                    <?php } ?>
                  </div>
                  <a class="select_all">Выбрать все</a> / <a class="unselect_all">Убрать все</a>
                </div>
              </div>
            <!--/2.2-->

            <!--2.3-->
              <div class="form-group" id="export-block-2-3">
                <span class="field_counter">2.3</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/2-3" target="_blank">
                    Бренды
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Выгружать товары некоторых брендов.<br>
                      Для выбора производителя достаточно по нему кликнуть
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <div id="unixml_<?php echo $feed; ?>_brands" class="scrollbox" style="max-height:400px;border:1px solid #ccc;overflow:auto;">
                    <?php foreach($manufacturers as $manufacturer){ ?>
                      <div>
                        <input class="checkbox_exp_mini" type="checkbox" id="manufacturer-<?php echo $manufacturer['manufacturer_id']; ?>" name="unixml_<?php echo $feed; ?>_manufacturers[]" value="<?php echo $manufacturer['manufacturer_id']; ?>" <?php if (in_array($manufacturer['manufacturer_id'], $unixml_manufacturers)) { ?>checked="checked"<?php } ?> />
                        <label for="manufacturer-<?php echo $manufacturer['manufacturer_id']; ?>">&nbsp;<?php echo $manufacturer['name']; ?></label>
                      </div>
                     <?php } ?>
                  </div>
                  <a class="select_all">Выбрать все</a> / <a class="unselect_all">Убрать все</a>
                </div>
              </div>
            <!--/2.3-->

            <!--2.4-->
              <div class="form-group" id="export-block-2-4">
                <span class="field_counter">2.4</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/2-4" target="_blank">
                    Логика выгрузки
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Жесткая привязка - товары выбранных категорий и только выбранных брендов; Плюсуем - товары выбранных категорий а также товары выбранных брендов.<hr>
                      Для более точной фильтрации рекомендуется выбирать режим 1 с логикой только товары выбранных брендов и в выбранных категорий.
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select id="unixml_<?php echo $feed; ?>_andor" name="unixml_<?php echo $feed; ?>_andor" class="form-control">
                    <?php if($unixml_andor){ ?>
                      <option value="0">Товары выбранных категорий и только выбранных брендов</option>
                      <option value="1" selected="selected">Товары выбранных категорий а также товары выбранных брендов</option>
                     <?php }else{ ?>
                      <option value="0" selected="selected">Товары выбранных категорий и только выбранных брендов</option>
                      <option value="1">Товары выбранных категорий а также товары выбранных брендов</option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <!--/2.4-->

            <!--2.5-->
              <div class="form-group" id="export-block-2-5">
                <span class="field_counter">2.5</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/2-5" target="_blank">
                    Привязка к главной категории
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Если Вы используете SEOPRO там есть главная категория. Если она задана как конечная и во всех товарах проставлена главная категория то отмечайте привязываться.<hr>Если не используется SEOPRO или же в товарах не проставлена главная категория лучше не включайте. Выгрузка постарается забрать конечную категорию товара, если он привязан к многим.
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <?php if($seopro){ ?>
                    <select id="unixml_<?php echo $feed; ?>_seopro" name="unixml_<?php echo $feed; ?>_seopro" class="form-control">
                      <?php if($unixml_seopro){ ?>
                        <option value="1" selected="selected">Привязываемся к главной категории (главная проставлена во всех товарах и как конечная по цепочке)</option>
                        <option value="0">Не привязываемся к главной категории. Пусть UniXML сам найдет конечную категорию товара</option>
                       <?php }else{ ?>
                        <option value="1">Привязываемся к главной категории (главная проставлена во всех товарах и как конечная по цепочке)</option>
                        <option value="0" selected="selected">Не привязываемся к главной категории. Пусть UniXML сам найдет конечную категорию товара</option>
                      <?php } ?>
                    </select>
                  <?php }else{ ?>
                    У вас не стоит система формирования ЧПУ - SEOPRO. Но и без сеопро все будет работать.<br>Рекомендуется установить. Это полезно как для UniXML, так и для SEO в целом.
                  <?php } ?>
                </div>
              </div>
            <!--/2.5-->

            <!--2.6-->
              <div class="form-group" id="export-block-2-6">
                <span class="field_counter">2.6</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/2-6" target="_blank">
                    Привязка к наличию (количеству)
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Выгружать все товары даже с нулевым остатком. По умолчанию выгрузка забирает только товары в наличии.<br>Это нужно для магазинов которые не привязаны к остаткам товара или же для обновления данных при отсутствии товара в магазине
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select id="unixml_quantity" name="unixml_<?php echo $feed; ?>_quantity" class="form-control">
                    <?php if($unixml_quantity){ ?>
                      <option value="1" selected="selected">Не привязываемся, выгружаем даже то что не в наличии</option>
                      <option value="0">Привязываемся, выгружаем только в наличии</option>
                     <?php }else{ ?>
                      <option value="1">Не привязываемся, выгружаем даже то что не в наличии</option>
                      <option value="0" selected="selected">Привязываемся, выгружаем только в наличии</option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="form-group" id="hideblock_quantity" <?php if(!$unixml_quantity){ ?>style="display:none;"<?php } ?>>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/2-6" target="_blank">
                    Статус в наличии
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Статус товара во вкладке Данные при котором товар будет в наличии, даже при условии что его остаток 0
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select name="unixml_<?php echo $feed; ?>_stock" id="unixml_<?php echo $feed; ?>_stock" class="form-control">
                    <?php foreach($stock_statuses as $stock_status){ ?>
                      <?php if($stock_status['stock_status_id'] == $unixml_stock){ ?>
                        <option value="<?php echo $stock_status['stock_status_id']; ?>" selected="selected"><?php echo $stock_status['name']; ?></option>
                       <?php }else{ ?>
                        <option value="<?php echo $stock_status['stock_status_id']; ?>"><?php echo $stock_status['name']; ?></option>
                      <?php } ?>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <!--/2.6-->

            <!--2.7-->
              <div class="form-group" id="export-block-2-7">
                <span class="field_counter">2.7</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/2-6" target="_blank">
                    Привязка к фото
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Это настройка которая позволяет не выгружать товары без фото<hr>
                      Выгружать товары даже без фото - в таком случае в фид идут товары даже те у которых нет фото<hr>
                      Не выгружать товары без фото - такой режим фильтрует товары и в выгрузку идут те что имеют фото. Это важно для большинства маркетплейсов
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select id="unixml_<?php echo $feed; ?>_image" name="unixml_<?php echo $feed; ?>_image" class="form-control">
                    <?php if($unixml_image){ ?>
                      <option value="0">Выгружать товары даже без фото</option>
                      <option value="1" selected="selected">Не выгружать товары без фото</option>
                     <?php }else{ ?>
                      <option value="0" selected="selected">Выгружать товары даже без фото</option>
                      <option value="1">Не выгружать товары без фото</option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <!--/2.7-->

          </div>
          <!--/export-block-2-->

          <!--export-block-3-->
          <div id="export-block-3" class="export-block-item">
            <h3>3. Изменения контента</h3>

            <!--3.1-->
              <div class="form-group" id="export-block-3-1">
                <span class="field_counter">3.1</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-1" target="_blank">
                    Наценка/скидка на товар
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Это настройка которая задает корректировку цены, иными словами наценку или скидку. Записать можно в разных вариантах. Например +10% или же 10% - сработает одинаково и добавит всем товарам +10% к цене. Если написать -10% тогда будет скидка в 10%<hr>
                      Также можно и применять умножение или деление. Например прописав /2 - цены будут делиться на 2. Если прописать *1.45 то это умножит цену на заданное число 1.45<hr>
                      Можно задавать не только проценты или операторы умножения/деления, а также добавить +100 к ценам, прописав +100. И с минусом это также работает
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_markup" placeholder="10% - это 10% на цену или же 200 - это 200 единиц валюты" type="text" name="unixml_<?php echo $feed; ?>_markup" value="<?php echo $unixml_markup; ?>" class="form-control">
                </div>
              </div>
            <!--/3.1-->

            <!--3.2-->
              <div class="form-group" id="export-block-3-2">
                <span class="field_counter">3.2</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-2" target="_blank">
                     Умножать товар на опции
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Этот пункт позволяет размножить один товар на его вариации, как этого требуют маркетплейсы.<br>Например у вас есть футболка, в ней есть опции Цвет (Синий/Красный) и Размер (S/M/L). Выбрав в наборах эти опции на выходе мы получим 6 разных offer<hr>
                      <b>Обязательно почитайте информацию как все правильно настроить</b>, для этого достаточно кликнуть там где у вас сейчас курсор мыши
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <?php if($unixml_options){ //если есть опции в магазине ?>

                    <select id="unixml_<?php echo $feed; ?>_option_multiplier_status" name="unixml_<?php echo $feed; ?>_option_multiplier_status" class="form-control">
                      <?php if($unixml_option_multiplier_status){ ?>
                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                        <option value="0"><?php echo $text_disabled; ?></option>
                       <?php }else{ ?>
                        <option value="1"><?php echo $text_enabled; ?></option>
                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                      <?php } ?>
                    </select>

                    <div class="hideoption<?php echo $feed; ?>" <?php if(!$unixml_option_multiplier_status){ ?>style="display:none;"<?php } ?>>

                      <div class="option-block-list">

                        <?php if ($unixml_option_multiplier) { ?>
                          <?php foreach ($unixml_option_multiplier as $option_multiplier_key => $block) { ?>

                            <div class="option-block-item option-block-<?php echo $option_multiplier_key; ?>">
                              <div class="row mtb-10">
                                <div class="col-sm-12">
                                  <input type="text" placeholder="Добавить опции в набор" class="form-control get-select-option" data-option-block="<?php echo $option_multiplier_key; ?>">

                                  <div class="row">
                                    <div class="col-sm-9">
                                      <div class="scrollbox option_select_scroll">
                                        <?php foreach ($block as $option) { ?>

                                          <div class="option-list-item option-list-item-<?php echo $option['option_id']; ?>">
                                            <div class="option-list-item-name">id <?php echo $option['option_id']; ?>: <b><?php echo $option['name']; ?></b> <small>(в <?php echo $option['products']; ?> товарах)</small></div>
                                            <input type="hidden" value="<?php echo $option['option_id']; ?>" name="unixml_<?php echo $feed; ?>_option_multiplier[<?php echo $option_multiplier_key; ?>][]">
                                            <div class="option-list-item-values">Значения: <?php echo $option['values']; ?></div>
                                            <span class="delete-option-item"><i class="fa fa-times" aria-hidden="true"></i></span>
                                          </div>

                                         <?php } ?>
                                         <div class="option-list-placeholder" data-option-block="<?php echo $option_multiplier_key; ?>" style="display:none;">Добавьте опции для набора <i class="fa fa-level-up" aria-hidden="true"></i></div>
                                      </div>
                                    </div>
                                    <div class="col-sm-3 mt10">
                                      <div><b>Набор опций <?php echo $option_multiplier_key; ?></b></div>
                                      <span class="delete-option-block" data-option-block="<?php echo $option_multiplier_key; ?>">Удалить набор</span>
                                      <div style="margin-top:20px;">В выгрузке будет:<br><b>[[optionset<?php echo $option_multiplier_key; ?>]]</b></div>
                                    </div>
                                  </div>

                                </div>
                              </div>
                            </div>
                          <?php } ?>
                        <?php } ?>
                      </div>
                      <span class="btn btn-info add-option-block"><i class="fa fa-plus" aria-hidden="true"></i> Добавить набор опций</span>
                    </div>
                  <?php }else{ ?>
                    Опций в магазине пока что нет.<br>Как будут опции - будет настройка на какие опции умножать товар.
                  <?php } ?>
                </div><!--/col-sm-10-->
              </div><!--/#export-block-3-2-->
            <!--/3.2-->

            <!--3.3-->
              <div class="form-group" id="export-block-3-3">
                <span class="field_counter">3.3</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-3" target="_blank">
                     Шаблон генерации названий товаров
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      В некоторых случаях требуется специальное название товара определенной структуры. С помощью шаблона можно генерировать название товара на лету.
                      <hr>
                      <b>((таблица.поле))</b> - поле из базы данных. Таблица может быть p - product и pd - product_description. Пример: ((p.quantity)) или ((pd.meta_title))<br>
                      <b>{{атрибут}}</b> - название атрибута (если не найден - не выводится),<br>
                      <b>[[optionset3]]</b> - название опции (работает в случает умножения товара на опцию). Посмотреть можно в пунукте 3.2<br>
                      <b>{поле массива}</b> - любое поле массива товаров, например name (уже сгенерированное), manufacturer, special и т.п.<br>
                    </div>
                  </a>
                  <button type="button" class="mt10 btn btn-primary btn-xs" data-toggle="modal" data-target="#uxmLong">Какие поля доступны?</button>
                </label>
                <div class="col-sm-10">
                  <textarea style="min-height:65px;" id="unixml_<?php echo $feed; ?>_genname" name="unixml_<?php echo $feed; ?>_genname" class="form-control"><?php echo $unixml_genname; ?></textarea>
                </div>
              </div>
            <!--/3.3-->

            <!--3.4-->
            <div class="form-group" id="export-block-3-4">
              <span class="field_counter">3.4</span>
              <label class="col-sm-2 control-label pt0">
                <a href="https://unixml.pro/set/export/3-4" target="_blank">
                   Шаблон генерации описания товаров
                  <div class="help">
                    <small>Кликните для перехода на полное описание</small>
                    В некоторых случаях требуется специальное описание товара определенной структуры. С помощью шаблона можно генерировать описания товара на лету.
                    <hr>
                    <b>((таблица.поле))</b> - поле из базы данных. Таблица может быть p - product и pd - product_description. Пример: ((p.quantity)) или ((pd.meta_title))<br>
                    <b>{{атрибут}}</b> - название атрибута (если не найден - не выводится),<br>
                    <b>[[optionset3]]</b> - название опции (работает в случает умножения товара на опцию). Посмотреть можно в пунукте 3.2<br>
                    <b>{поле массива}</b> - любое поле массива товаров, например name (уже сгенерированное), manufacturer, special и т.п.<br>
                  </div>
                </a>
                <button type="button" class="mt10 btn btn-primary btn-xs" data-toggle="modal" data-target="#uxmLong">Какие поля доступны?</button>
              </label>
              <div class="col-sm-10">
                <textarea style="min-height:65px;" id="unixml_<?php echo $feed; ?>_gendesc" name="unixml_<?php echo $feed; ?>_gendesc" class="form-control"><?php echo $unixml_gendesc; ?></textarea>
              </div>
            </div>
            <!--/3.4-->

            <!--3.5-->
              <div class="form-group" id="export-block-3-5">
                <span class="field_counter">3.5</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-5" target="_blank">
                     Режим генерации
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Это настройка которая позволяет назначить когда генерировать описание
                      <hr>
                      Генерировать описание даже если оно есть (принудительно по шаблону выше) - При таком выборе генерация описания идет даже когда само описание есть в товаре.<br>
                      Генерировать описание только в случае если его нет - если выбираем этот вариант генерация описания идет только если в товаре нет описания.<hr>
                      Часто маркетплейсам нужны описания но их нет в магазине, тогда этот пункт подойдет для создания заглушки.
                    </div>
                  </a>
                  <button type="button" class="mt10 btn btn-primary btn-xs" data-toggle="modal" data-target="#uxmLong">Какие поля доступны?</button>
                </label>
                <div class="col-sm-10">
                  <select id="unixml_<?php echo $feed; ?>_gendesc_mode" name="unixml_<?php echo $feed; ?>_gendesc_mode" class="form-control">
                      <option value="" <?php if(!$unixml_gendesc_mode){ ?>selected="selected"<?php } ?>>Генерировать описание даже если оно есть (принудительно по шаблону выше)</option>
                      <option value="1" <?php if($unixml_gendesc_mode){ ?>selected="selected"<?php } ?>>Генерировать описание только в случае если его нет</option>
                  </select>
                </div>
              </div>
            <!--/3.5-->

            <!--3.6-->
              <div class="form-group" id="export-block-3-6">
                <span class="field_counter">3.6</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-6" target="_blank">
                     Режим очистки описаний
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Это настройка которая позволяет выбрать как чистить описание и чистить ли его вообще. Есть 3 режима работы настройки
                      <hr>
                      <b>Чистить описание от спецсимволов и html тегов (если нужен просто текст)</b> - в этом режиме любое описание с любыми тегами или форматированием будет идти в фиде как просто текст.<br>
                      <b>Чистить описание только от стилей и лишних тегов. Базовые теги оставляем (рекомендуется)</b> - в этом режиме на выходе мы получаем чистый отформатированный html код описания без ничего лишнего.<br>
                      <b>НЕ чистить описание - отдавать в выгрузку оригинал (1в1 как на сайте)</b> - при такой настройке в фиде описание будет 1 в 1 как в базе данных.
                    </div>
                  </a>
                  <button type="button" class="mt10 btn btn-primary btn-xs" data-toggle="modal" data-target="#uxmLong">Какие поля доступны?</button>
                </label>
                <div class="col-sm-10">
                  <select id="unixml_<?php echo $feed; ?>_clear_desc" name="unixml_<?php echo $feed; ?>_clear_desc" class="form-control">
                      <option value="" <?php if(!$unixml_clear_desc){ ?>selected="selected"<?php } ?>>Чистить описание от спецсимволов и html тегов (если нужен просто текст)</option>
                      <option value="2" <?php if($unixml_clear_desc == 2){ ?>selected="selected"<?php } ?>>Чистить описание только от стилей и лишних тегов. Базовые теги оставляем (рекомендуется)</option>
                      <option value="1" <?php if($unixml_clear_desc == 1){ ?>selected="selected"<?php } ?>>НЕ чистить описание - отдавать в выгрузку оригинал (1в1 как на сайте)</option>
                  </select>
                </div>
              </div>
            <!--/3.6-->

            <!--3.7-->
              <div class="form-group" id="export-block-3-7">
                <span class="field_counter">3.7</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-7" target="_blank">
                     Соответствие категорий, наценка для категории и свои теги категорий
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      В выгрузку можно отдавать другие названия категорий. Это нужно для того что бы было точное совпадение с категориями маркетплейса. Что бы выбрать категорию <b>обязательно кликните</b> по ней из выпадающего списка!<br>Наценка позволяет делать наценку на товары определенной категории.<br>Свои теги позволяют выводить для товаров определенной категории свою информацию
                      <hr>
                      <b>Теги и их значения пишем в формате (каждый тег и значение с новой строки, разделитель ==)</b>
                      <br>Что можно использовать в кастомных тегах:<br>
                      <b>((таблица.поле))</b> - поле из базы данных. Таблица может быть p - product и pd - product_description. Пример: ((p.quantity)) или ((pd.meta_title))<br>
                      <b>{{атрибут}}</b> - название атрибута (если не найден - не выводится),<br>
                      <b>[[optionset3]]</b> - название опции (работает в случает умножения товара на опцию). Посмотреть можно в пунукте 3.2<br>
                      <b>{поле массива}</b> - любое поле массива товаров, например name (уже сгенерированное), manufacturer, special и т.п.<br>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10 table-responsive" style="overflow: visible;">
                  <table id="unixml_<?php echo $feed; ?>_category_match" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                        <td class="text-left">Категория магазина</td>
                        <td class="text-left">Категория в выгрузке</td>
                        <td class="text-left">Наценка</td>
                        <td class="text-left">Теги и их значения</td>
                        <td></td>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($unixml_category_match as $xml_name) { ?>
                        <tr id="category_match_row<?php echo $category_match_row; ?>">
                          <td class="text-left" style="width: 22%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_category_match[<?php echo $category_match_row; ?>][category_name]" value="<?php echo $xml_name['category_name']; ?>" placeholder="Вводите что-то из названия категории" class="form-control" />
                            <input type="hidden" name="unixml_<?php echo $feed; ?>_category_match[<?php echo $category_match_row; ?>][category_id]" value="<?php echo $xml_name['category_id']; ?>" />
                          </td>
                          <td class="text-left" style="width: 22%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_category_match[<?php echo $category_match_row; ?>][xml_name]" value="<?php echo $xml_name['xml_name']; ?>" placeholder="Название этой категории в выгрузке" class="form-control" />
                          </td>
                          <td class="text-left" style="width: 13%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_category_match[<?php echo $category_match_row; ?>][markup]" value="<?php echo $xml_name['markup']; ?>" placeholder="Наценка на товары категории" class="form-control" />
                          </td>
                          <td class="text-left" style="width: 41%;">
                            <textarea name="unixml_<?php echo $feed; ?>_category_match[<?php echo $category_match_row; ?>][custom]" placeholder="Теги для товаров этой категории" class="form-control" ><?php echo $xml_name['custom']; ?></textarea>
                          </td>
                          <td class="text-center"><button type="button" onclick="$('#category_match_row<?php echo $category_match_row; ?>').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                        </tr>
                        <?php $category_match_row++; ?>
                      <?php } ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="4"></td>
                        <td class="text-center"><button type="button" onclick="addCategoryMatch('<?php echo $feed; ?>');" data-toggle="tooltip" title="Добавить" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            <!--/3.7-->

            <!--3.8-->
              <div class="form-group" id="export-block-3-8">
                <span class="field_counter">3.8</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/3-8" target="_blank">
                     Выгрузка атрибутов
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Некоторые маркетплейсы не требуют характеристик. По этому это это тот самый случай лучше выключить их что бы не нагружать базу данных
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select id="unixml_attribute_status" name="unixml_<?php echo $feed; ?>_attribute_status" class="form-control">
                    <?php if($unixml_attribute_status){ ?>
                      <option value="0">Выгружать атрибуты</option>
                      <option value="1" selected="selected">Не выгружать атрибуты</option>
                     <?php }else{ ?>
                      <option value="0" selected="selected">Выгружать атрибуты</option>
                      <option value="1">Не выгружать атрибуты</option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div id="hideattr" class="form-group" <?php if($unixml_attribute_status){ ?>style="display:none;"<?php } ?>>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-8" target="_blank">
                     Соответствие атрибутов
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      <b>Атрибут магазина</b> - здесь надо начинать вводить название и внизу появится список из найденных атрибутов. Для выбора - <b>обязательно кликнуть</b> по нужному что бы в систему передалось id атрибута.<br>
                      <b>Атрибут в выгрузке</b> - здесь можно просто прописать название как оно должно быть в фиде. Как правило пишут название такое как это свойство называется на маркетплейсе.
                      <hr>
                      Обратите внимание, если мы задаем соответствия то то что задано и будет выводится в фиде. Таким образом мы можем явно указать какие атрибуты выгружать и их же сразу переименовать.
                    </div>
                  </a>
                </label>
                <div class="col-sm-10 table-responsive" style="overflow: visible;">
                  <table id="unixml_attributes" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                        <td class="text-left"><?php echo $entry_attribute; ?></td>
                        <td class="text-left"><?php echo $entry_attribute_xml; ?></td>
                        <td></td>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($unixml_attributes as $xml_attribute) { ?>
                        <tr id="attribute-row<?php echo $attribute_row; ?>">
                          <td class="text-left" style="width: 40%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_attributes[<?php echo $attribute_row; ?>][attribute_name]" value="<?php echo $xml_attribute['attribute_name']; ?>" placeholder="<?php echo $entry_attribute; ?>" class="form-control" />
                            <input type="hidden" name="unixml_<?php echo $feed; ?>_attributes[<?php echo $attribute_row; ?>][attribute_id]" value="<?php echo $xml_attribute['attribute_id']; ?>" />
                          </td>
                          <td class="text-left">
                            <input type="text" name="unixml_<?php echo $feed; ?>_attributes[<?php echo $attribute_row; ?>][xml_name]" value="<?php echo $xml_attribute['xml_name']; ?>" placeholder="<?php echo $entry_attribute_xml; ?>" class="form-control" />
                          </td>
                          <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                        </tr>
                        <?php $attribute_row++; ?>
                      <?php } ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="2"></td>
                        <td class="text-center"><button type="button" onclick="addAttribute('<?php echo $feed; ?>');" data-toggle="tooltip" title="Добавить" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            <!--/3.8-->

            <!--3.9-->
              <div class="form-group" id="export-block-3-9">
                <span class="field_counter">3.9</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-9" target="_blank">
                     Наценки на группы товаров
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Это пункт настроек позволяет делать наценки на товар персонально или на группу товаров. Например нам надо сделать наценку на определенных 10 товаров +40%
                      <hr>
                      <b>Название группы</b> Это просто название внутри системы, чисто для обозначения группы товаров.<br>
                      <b>Товары</b> Здесь есть поле ввода для поиска товара (стандартный автокомплит) Вводите первые символы названия, артикул или id товара и из выпадающего списка кликаете.<br>
                      <b>Наценка</b> Здесь можете прописать наценку/скидку, все как в пункте 3.1. Например -10% или же +100.
                      <hr>
                      При добавлении сюда товаров другие скидки (общие и на категорию) срабатывать не будут!
                    </div>
                  </a>
                </label>
                <div class="col-sm-10 table-responsive" style="overflow-y:scroll;max-height:730px;">
                  <table id="unixml_product_markup_table" class="table table-striped2 table-bordered table-hover2">
                    <thead>
                      <tr>
                        <td class="text-left">Группа</td>
                        <td class="text-left">Товары</td>
                        <td class="text-left">Наценка</td>
                        <td></td>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($unixml_product_markup as $markup_item) { ?>
                        <tr data-row="<?php echo $product_markup_row; ?>" id="product_markup_row<?php echo $product_markup_row; ?>">
                          <td class="text-left va-top" style="width: 15%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_product_markup[<?php echo $product_markup_row; ?>][name]" value="<?php echo $markup_item['name']; ?>" placeholder="Название" autocomplete="off" class="form-control" />
                            <button class="btn btn-info importMarkup" data-feed="<?php echo $feed; ?>" data-row="<?php echo $product_markup_row; ?>" title="Импортировать товары" data-toggle="tooltip"><i class="fa fa-upload" aria-hidden="true"></i> Импорт</button>
                          </td>
                          <td class="text-left" style="width: 67%;">
                            <input type="text" value="" placeholder="Вводите название товара, артикул или id" id="input-markup-products<?php echo $product_markup_row; ?>" class="form-control" />
                            <div id="unixml_<?php echo $feed; ?>_markup_products<?php echo $product_markup_row; ?>" class="well well-sm" style="height: 250px; overflow: auto;">
                              <?php foreach($markup_item['products'] as $product){ ?>
                                <div id="unixml_<?php echo $feed; ?>_markup_products<?php echo $product_markup_row; ?>-<?php echo $product['product_id']; ?>"><i class="fa fa-minus-circle"></i>
                                  <?php echo $product['name']; ?>
                                  <a target="_blank" href="<?php echo $product['edit']; ?>" title="Редактировать. Откроется в новой вкладке" data-toggle="tooltip"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                  <a target="_blank" href="<?php echo $product['view']; ?>" title="Посмотреть товар. Откроется в новой вкладке" data-toggle="tooltip"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                  <input type="hidden" name="unixml_<?php echo $feed; ?>_product_markup[<?php echo $product_markup_row; ?>][products][]" value="<?php echo $product['product_id']; ?>" />
                                </div>
                              <?php } ?>
                            </div>
                          </td>
                          <td class="text-left va-top" style="width: 13%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_product_markup[<?php echo $product_markup_row; ?>][markup]" value="<?php echo $markup_item['markup']; ?>" placeholder="Наценка" class="form-control" />
                          </td>
                          <td class="text-center va-top"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="Удалить" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                        </tr>
                        <?php $product_markup_row++; ?>
                      <?php } ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="3">При добавлении сюда товаров другие скидки (общие и на категорию) срабатывать не будут.<br>Наценка может быть как "10%"" так и фиксированное число, например "100"</td>
                        <td class="text-center"><button type="button" onclick="addProductMarkup('<?php echo $feed; ?>');" data-toggle="tooltip" title="Добавить" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            <!--/3.9-->

            <!--3.10-->
              <div class="form-group" id="export-block-3-10">
                <span class="field_counter">3.10</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-10" target="_blank">
                     Замены слов
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Этот пункт настроек позволяет создать списки замен данных в фиде
                      <hr>
                      <b>Что меняем</b> Сюда пишем слово или строку которую необходимо заменить.<br>
                      <b>На что меняем</b> Сюда пишем слово или строку на которую необходимо заменить. Если надо вырезать то ничего здесь не пишем, оставляем пустым.<br>
                      <b>Где меняем</b> Здесь есть выбор где делать замены. Через этот пункт по сути можно сопоставлять значения атрибутов. Если надо что-то менять в категории тогда есть пункт 3.7<br>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10 table-responsive">
                  <table id="unixml_<?php echo $feed; ?>_replace_names" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                        <td class="text-left"><?php echo $entry_replace_from; ?></td>
                        <td class="text-left"><?php echo $entry_replace_to; ?></td>
                        <td class="text-left">Где меняем</td>
                        <td class="text-center"></td>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($unixml_replace_name as $xml_replace_item) { ?>
                        <tr id="replace_name-row<?php echo $replace_name_row; ?>">
                          <td class="text-left" style="width: 40%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][name_from]" value="<?php echo $xml_replace_item['name_from']; ?>" placeholder="<?php echo $entry_replace_from; ?>" class="form-control" />
                          </td>
                          <td class="text-left">
                            <input type="text" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][name_to]" value="<?php echo $xml_replace_item['name_to']; ?>" placeholder="<?php echo $entry_replace_to; ?>" class="form-control" />
                          </td>
                          <td class="text-left">
                           <div class="replace-where-div">
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="1" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-1" <?php if(in_array(1, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-1">&nbsp;В названии<label></div>
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="2" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-2" <?php if(in_array(2, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-2">&nbsp;В модели<label></div>
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="3" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-3" <?php if(in_array(3, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-3">&nbsp;В производителе<label></div>
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="4" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-4" <?php if(in_array(4, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-4">&nbsp;В описании<label></div>
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="5" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-5" <?php if(in_array(5, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-5">&nbsp;В ссылке<label></div>
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="6" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-6" <?php if(in_array(6, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-6">&nbsp;В фото<label></div>
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="7" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-7" <?php if(in_array(7, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-7">&nbsp;В названии атрибутов<label></div>
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="8" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-8" <?php if(in_array(8, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-8">&nbsp;В значении атрибутов<label></div>
                           </div>
                          </td>
                          <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                        </tr>
                        <?php $replace_name_row++; ?>
                      <?php } ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="3"></td>
                        <td class="text-center"><button type="button" onclick="addReplaceRow('<?php echo $feed; ?>');" data-toggle="tooltip" title="<?php echo $button_replace_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                      </tr>
                    </tfoot>
                  </table>

                </div>
              </div>
            <!--/3.10-->

            <!--3.11-->
              <div class="form-group" id="export-block-3-11">
                <span class="field_counter">3.11</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-11" target="_blank">
                     Дополнительные фото товара
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Некоторые маркетплейсы принимают дополнительные фото товара. Рекомендуется включать доп фото. Однако там где не надо, выключайте что бы лишний раз не нагружать сервер
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select id="unixml_<?php echo $feed; ?>_images" name="unixml_<?php echo $feed; ?>_images" class="form-control">
                    <?php if($unixml_images){ ?>
                      <option value="1" selected="selected">Выгружать дополнительные фото</option>
                      <option value="0">Не выгружать дополнительные фото</option>
                     <?php }else{ ?>
                      <option value="1">Выгружать дополнительные фото</option>
                      <option value="0" selected="selected">Не выгружать дополнительные фото</option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <!--/3.11-->

            <!--3.12-->
              <div class="form-group" id="export-block-3-12">
                <span class="field_counter">3.12</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-12" target="_blank">
                     Дополнительные статические параметры
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      В этом пункте можно добавить дополнительные параметры или теги с любыми данными
                      <hr>
                      <b>Название атрибута</b> Сюда можно написать как просто название, так и полноценный тег. Например если вам нужно вывести в фид для каждого товара количество &lt;quantity&gt;35&lt;/quantity&gt; в поле Название атрибута пишем &lt;quantity&gt;.
                      <br>Если вам нужно добавить параметр в фид &lt;param name="quantity"&gt;35&lt;/param&gt; в это поле пишем просто quantity. То есть то что в &lt;скобках&gt; то и будет выводится как полноценный тег.<br>
                      <b>Текст атрибута</b> Сюда можно прописать как статический текст, например для Яндекс Маркета sales_notes. Либо использовать любую переменную например атрибут {{Напряжение сети}} или же поле с базы данных ((p.viewed)) или же набор опций [[optionset3]] или поле массива {category}
                      <hr>
                      Если здесь прописаны дополнительные параметры - они будут выводится в каждом товаре фида.
                    </div>
                  </a>
                </label>
                <div class="col-sm-10 table-responsive">
                  <table id="unixml_additional_params" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                        <td class="text-left"><?php echo $entry_param_name; ?></td>
                        <td class="text-left"><?php echo $entry_param_text; ?></td>
                        <td></td>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($unixml_additional_params as $xml_attribute) { ?>
                        <tr id="param-row<?php echo $param_row; ?>">
                          <td class="text-left" style="width: 40%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_additional_params[<?php echo $param_row; ?>][param_name]" value="<?php echo $xml_attribute['param_name']; ?>" placeholder="Название или тег" class="form-control" />
                          </td>
                          <td class="text-left">
                            <input type="text" name="unixml_<?php echo $feed; ?>_additional_params[<?php echo $param_row; ?>][param_text]" value="<?php echo $xml_attribute['param_text']; ?>" placeholder="Значение" class="form-control" />
                          </td>
                          <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                        </tr>
                        <?php $param_row++; ?>
                      <?php } ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="2">
                          В качестве Текста атрибута можно указывать не только статический текст а и переменные:<br>{{атрибут}} ((таблица.поле)) [[optionset]] {поле массива}
                        </td>
                        <td class="text-center"><button type="button" onclick="addParam('<?php echo $feed; ?>');" data-toggle="tooltip" title="Добавить" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            <!--/3.12-->

            <!--3.13-->
              <div class="form-group" id="export-block-3-13">
                <span class="field_counter">3.13</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-13" target="_blank">
                     Приставка к ссылке товара (UTM и т.п.)
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Эта настройка дает возможность добавить к ссылке дополнительные параметры. Часто это utm но можно и любые. Также в приставку к ссылке можно встраивать любые замены и переменные, как и в других местах UniXML.
                      <hr>
                      Например приставка может быть такая: ?utm=test&pid={product_id}&piddb=((p.product_id))&sostattr={{Состояние}}&optset4=[[optionset4]]&optset3=[[optionset3]] где продемонстрирована возможность добавлению любых данных товара.
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_utm" placeholder="?utm_source=<?php echo $feed; ?>&utm_medium=cpc&utm_campaign=utm_metki" type="text" name="unixml_<?php echo $feed; ?>_utm" value="<?php echo $unixml_utm; ?>" class="form-control">
                </div>
              </div>
            <!--/3.13-->

          </div>
          <!--/export-block-3-->

          <!--export-block-4-->
          <div id="export-block-4" class="export-block-item">
            <h3>4. Кастомный код</h3>

            <!--4.1-->
              <div class="form-group" id="export-block-4-1">
                <span class="field_counter">4.1</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/4-1" target="_blank">
                     Дополнить запрос
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Сюда вставлять запрос в базу. В модуле запрос в упрощенном виде такой: SELECT нужные поля FROM нужные таблицы WHERE условия AND p.upc != 1 и далее запрос модуля. Где AND p.upc != 1 - это то что вставится с этого поля. Здесь можно добавить дополнительные условия для выгрузки.
                    </div>
                  </a>
                </label>

                <div class="col-sm-10">
                  <div class="input-group">
                    <span class="input-group-addon" id="input-_custom_xml1">SELECT поля FROM таблицы WHERE условия</span>
                    <input type="text" id="unixml_<?php echo $feed; ?>_custom_sql" value="<?php echo $unixml_custom_sql; ?>" placeholder="AND p.upc != 1 AND p.custom_field = 'value' -доп условия выборки" name="unixml_<?php echo $feed; ?>_custom_sql" class="form-control">
                    <span class="input-group-addon" id="input-_custom_xml2">Конец запроса</span>
                  </div>
                </div>
              </div>
            <!--/4.1-->

            <!--4.2-->
              <div class="form-group" id="export-block-4-2">
                <span class="field_counter">4.2</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/4-2" target="_blank">
                     Кастомный код ПОСЛЕ ЗАПРОСА в базу, ДО обхода в цикле
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Синтаксис php! Обрабатываем массив $query->rows - если идет вся выборка товаров с базы за один заход
                      <hr>
                      Здесь удобно размещать код выборки чего либо для последующей обработки в цикле товара
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <span class="input-group-addon" id="input-_custom_xml_after_sql">&lt;?php</span>
                    <textarea style="min-height:100px;" id="unixml_<?php echo $feed; ?>_custom_xml_after_sql" placeholder="Работа с массивом $rows. Например foreach ($rows as $key => $product) { и т.д. Доступен массив $data - там настройки, посмотреть можно например выполнив echo '<pre>';print_r($data);exit();" type="text" name="unixml_<?php echo $feed; ?>_custom_xml_after_sql" class="form-control"><?php echo $unixml_custom_xml_after_sql; ?></textarea>
                    <span class="input-group-addon" id="input-_custom_xml_after_sql">?&gt;</span>
                  </div>
                </div>
              </div>
            <!--/4.2-->

            <!--4.3-->
              <div class="form-group" id="export-block-4-3">
                <span class="field_counter">4.3</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/4-3" target="_blank">
                     Кастомный код в итерации цикла товара
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Синтаксис php! Работа идет с массивом $product - это массив с данными товара
                      <hr>
                      Здесь удобно размещать код обработки чего либо для редактирования данных конкретного товара
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <span class="input-group-addon" id="input-_custom_xml1">&lt;?php</span>
                    <textarea style="min-height:100px;" id="unixml_<?php echo $feed; ?>_custom_xml" placeholder="if($product['price'] < 1000){$product['price'] *= 1.1;}else{$product['price'] *= 1.05;}" type="text" name="unixml_<?php echo $feed; ?>_custom_xml" class="form-control"><?php echo $unixml_custom_xml; ?></textarea>
                    <span class="input-group-addon" id="input-_custom_xml2">?&gt;</span>
                  </div>
                </div>
              </div>
            <!--/4.3-->

            <!--4.4-->
              <div class="form-group" id="export-block-4-4">
                <span class="field_counter">4.4</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/4-4" target="_blank">
                     Кастомный код после обхода цикла
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Синтаксис php! Работаем с готовым массивом уже обработанных данных товаров $products
                      <hr>
                      Здесь уже готовый массив для формирования  XML
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <span class="input-group-addon" id="input-_custom_xml1">&lt;?php</span>
                    <textarea style="min-height:100px;" id="unixml_<?php echo $feed; ?>_custom_xml_final" placeholder="Здесь финальный массив" type="text" name="unixml_<?php echo $feed; ?>_custom_xml_final" class="form-control"><?php echo $unixml_custom_xml_final; ?></textarea>
                    <span class="input-group-addon" id="input-_custom_xml2">?&gt;</span>
                  </div>
                </div>
              </div>
            <!--/4.4-->

          </div>
          <!--/export-block-4-->

          <!--export-block-5-->
          <div id="export-block-5" class="export-block-item">
            <h3>5. Системные настройки</h3>

            <!--5.1-->
              <div class="form-group" id="export-block-5-1">
                <span class="field_counter">5.1</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/5-1" target="_blank">
                     Дополнительные поля c базы для выгрузки
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      В некоторых ситуациях надо сделать выборку дополнительных данных с базы. Так как UniXML это модуль с оптимизированным кодом ничего лишнего с базы не забирается. Что бы дополнительно забирать - их необходимо прописать в этом пункте. Разделитель запятая
                      <hr>
                      Обратите внимание, если вы прописали сюда выборку дополнительных полей это означает что они заберуться только с базы данных, никуда в фид они вставляться не будут. Если нужно поставить данные смотрите на пункт дополнительные статические параметры.
                    </div>
                  </a>
                  <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#uxmShort">Какие поля доступны?</button>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_fields" type="text" name="unixml_<?php echo $feed; ?>_fields" value="<?php echo $unixml_fields; ?>" class="form-control">
                </div>
              </div>
            <!--/5.1-->

            <!--5.2-->
              <div class="form-group" id="export-block-5-2">
                <span class="field_counter">5.2</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/5-2" target="_blank">
                     Из какого поля берем id товара
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Во многих маркетплейсах есть связующее звено - идентификатор. Указать что будет идентификатором можно в этой настройке. Внимание! Идентификатор должен быть только уникальным значением. Если стоит умножение товара на опции будет приставка опции для соблюдения уникальности идентификатора
                    </div>
                  </a>
                  <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#uxmShort">Какие поля доступны?</button>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_field_id" type="text" name="unixml_<?php echo $feed; ?>_field_id" value="<?php echo $unixml_field_id; ?>" class="form-control" placeholder="p.product_id">
                </div>
              </div>
            <!--/5.2-->

            <!--5.3-->
              <div class="form-group" id="export-block-5-3">
                <span class="field_counter">5.3</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/5-3" target="_blank">
                     Из какого поля цена
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Во многих магазинах цена для выгрузки находится в другом поле. Что бы цена шла с нужного поля, просто укажите его
                    </div>
                  </a>
                  <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#uxmShort">Какие поля доступны?</button>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_field_price" type="text" name="unixml_<?php echo $feed; ?>_field_price" value="<?php echo $unixml_field_price; ?>" class="form-control" placeholder="p.price">
                </div>
              </div>
            <!--/5.3-->

            <!--5.4-->
              <div class="form-group" id="export-block-5-4">
                <span class="field_counter">5.4</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/5-4" target="_blank">
                     Количество за раз
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Чем больше число товаров за раз - тем быстрее сгенерируется xml, но требуется больше оперативной памяти. Чем число меньше тем меньше оперативной памяти потребуется, но выгрузка будет дольше генерировать. Рекомендуется около 10 000 товаров за раз, но если будут ошибки о нехватки оперативной памяти уменьшайте до исчезновения ошибки
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_step" type="text" name="unixml_<?php echo $feed; ?>_step" value="<?php echo $unixml_step; ?>" class="form-control">
                </div>
              </div>
            <!--/5.4-->

            <!--5.5-->
              <div class="form-group" id="export-block-5-5">
                <span class="field_counter">5.5</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/5-5" target="_blank">
                     Логировать выгрузку
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Что бы понимать все ли ок, все ли работает и как быстро, можно ввести название файла - лога куда будут записываться информация о выгрузках
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <span class="input-group-addon" id="unixml_<?php echo $feed; ?>_log"><?php echo HTTPS_CATALOG; ?>system/storage/logs/</span>
                    <input id="unixml_<?php echo $feed; ?>_log" placeholder="Напр: unixml.log также можно и log_name.secret_ext - для защиты. Если пусто - не логируется" type="text" name="unixml_<?php echo $feed; ?>_log" value="<?php echo $unixml_log; ?>" class="form-control">
                  </div>
                </div>
              </div>
            <!--/5.5-->

            <!--5.6-->
              <div class="form-group" id="export-block-5-6">
                <span class="field_counter">5.6</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/5-6" target="_blank">
                     Ключ защиты от запуска
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      Ключ защиты позволяет задать свой параметр в ссылку для генерации xml. Это нужно что бы по ссылке постоянно не запускали генерацию которая нагружает сайт. Введя ключ выгрузка будет по адресу с приставкой &key=secret_key
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_secret" type="text" name="unixml_<?php echo $feed; ?>_secret" value="<?php echo $unixml_secret; ?>" class="form-control">
                  <small>что бы перейти по ссылке сначала сохраните настройки что бы заработала защита</small>
                </div>
              </div>
            <!--/5.6-->

            <!--5.7-->
              <div class="form-group" id="export-block-5-7">
                <span class="field_counter">5.7</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/5-7" target="_blank">
                     Название файла xml
                    <div class="help">
                      <small>Кликните для перехода на полное описание</small>
                      С помощью этого пункта настроек можно задать название файла для генерации. Это удобно тем что можно назвать его по своему например yandex34Hkfd.xml и оно будет уникальное. Это своего рода и защита от случайного получения файла.
                      <hr>
                      Пару примеров:<br>
                      Если прописать <b>123</b> то файл фида будет по ссылке site.com/price/123.xml<br>
                      Если прописать <b>yandex</b> а это файл уже есть на сервере, он будет по адресу site.com/price/yandex.xml и заменит старый<br>
                      Если прописать <b>123/435/yandex_34243</b> то фид будет по адресу site.com/price/123/435/yandex_34243.xml то есть создаст вложенные папки
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <span class="input-group-addon" id="unixml_<?php echo $feed; ?>_xml_name"><?php echo HTTPS_CATALOG; ?>price/</span>
                    <input id="unixml_xml_name" data-feed="<?php echo $feed; ?>" placeholder="Напр: unixml.log также можно и log_name.secret_ext - для защиты. Если пусто - не логируется" type="text" name="unixml_<?php echo $feed; ?>_xml_name" value="<?php echo $unixml_xml_name; ?>" class="form-control">
                    <span class="input-group-addon">.xml</span>
                  </div>
                </div>
              </div>
            <!--/5.7-->

          </div>
          <!--/export-block-5-->

          <!--export-block-6-->
          <div id="export-block-6" class="export-block-item">
            <h3>6. Информация</h3>

            <div class="form-group" id="export-block-6-1">
              <span class="field_counter">6.1</span>

              <div id="direct-link" style="display:none;"><?php echo $data_feed; ?><span><?php if($unixml_secret){ ?>&key=<?php echo $unixml_secret; ?><?php } ?></span></div>

              <!--6.1-->
                <div class="link-block row">
                  <label class="col-sm-2 control-label pt0">
                    <a href="https://unixml.pro/set/export/6-1" target="_blank">
                       Ссылка запуска прямой генерации
                      <div class="help">
                        <small>Кликните для перехода на полное описание</small>
                        По этой ссылке UniXML генерирует фид на лету и отдает в потоке. Например если эту ссылку открыть через браузер - вы увидите XML фид который будет только что сформирован.
                      </div>
                    </a>
                  </label>
                  <div class="col-sm-10" id="row-direct-link">
                    <div class="input-group">
                      <input placeholder="" type="text" readonly class="form-control">
                      <span class="input-group-addon tocopy">Скопировать <i class="fa fa-copy" aria-hidden="true"></i></span>
                      <span class="input-group-addon">
                        <a href="" target="_blank" title="Не забудьте сохранить настройки перед запуском! Отроется в новом окне." data-toggle="tooltip">Перейти <i class="fa fa-external-link" aria-hidden="true"></i></a>
                      </span>
                    </div>
                    <small>Ссылка для запуска генерации в потоке. Перейдя по ней фид генерируется непосредственно в браузер.</small>
                  </div>
                </div>

                <div class="link-block row">
                  <label class="col-sm-2 control-label pt0">
                    <a href="https://unixml.pro/set/export/6-1" target="_blank">
                       Ссылка запуска генерации в файл
                      <div class="help">
                        <small>Кликните для перехода на полное описание</small>
                        По этой ссылке генерируется файл выгрузки и при завершении процесса выводится статистика работы модуля в режиме генерации.
                      </div>
                    </a>
                  </label>
                  <div class="col-sm-10" id="row-cron-link">
                    <div class="input-group">
                      <input placeholder="" type="text" readonly class="form-control">
                      <span class="input-group-addon tocopy">Скопировать <i class="fa fa-copy" aria-hidden="true"></i></span>
                      <span class="input-group-addon">
                        <a href="" target="_blank" title="Не забудьте сохранить настройки перед запуском! Отроется в новом окне." data-toggle="tooltip">Перейти <i class="fa fa-external-link" aria-hidden="true"></i></a>
                      </span>
                    </div>
                    <small>Ссылка для запуска генерации в файл. Перейдя по ней фид генерируется в готовый XML файл. По завершении выводится статистика работы.</small>
                  </div>
                </div>

                <div class="link-block row">
                  <label class="col-sm-2 control-label pt0">
                    <a href="https://unixml.pro/set/export/6-1" target="_blank">
                       Команда для генерации по CRON
                      <div class="help">
                        <small>Кликните для перехода на полное описание</small>
                        Здесь пример команды запуска на сервере через CRON. На разных хостингах команда может быть разная
                      </div>
                    </a>
                  </label>
                  <div class="col-sm-10" id="row-cron-command">
                    <div class="input-group">
                      <input placeholder="" type="text" readonly class="form-control">
                      <span class="input-group-addon tocopy">Скопировать <i class="fa fa-copy" aria-hidden="true"></i></span>
                    </div>
                    <small>Команда CRON для генерации в файл. Настроив запуск на сервере фид генерируется в готовый XML файл. <b>Может отличаться на разных хостингах!</b>
                    <br>Например может быть и такая wget -q -O /dev/null "ссылка на генерацию" - лучше уточните в поддержке хостинга точную команду для CRON</small>
                  </div>
                </div>

                <div class="link-block row">
                  <label class="col-sm-2 control-label">
                    <a href="https://unixml.pro/set/export/6-1" target="_blank">
                       XML файл
                      <div class="help">
                        <small>Кликните для перехода на полное описание</small>
                         Здесь показана ссылка на готовый XML файл который генерируется по команде выше.
                      </div>
                    </a>
                  </label>
                  <div class="col-sm-10" id="row-file-link">
                    <div class="input-group">
                      <input placeholder="" type="text" readonly class="form-control">
                      <span class="input-group-addon fileinfo">Загрузка...</span>
                      <span class="input-group-addon tocopy">Скопировать <i class="fa fa-copy" aria-hidden="true"></i></span>
                      <span class="input-group-addon">
                        <a href="" target="_blank" title="Не забудьте сохранить настройки перед запуском! Отроется в новом окне." data-toggle="tooltip">Перейти <i class="fa fa-external-link" aria-hidden="true"></i></a>
                      </span>
                    </div>
                  </div>
                </div>
              <!--/6.1-->

            </div>

            <div class="form-group" id="export-block-6-2">
              <span class="field_counter">6.2</span>
              <label class="col-sm-2 control-label" for="<?php echo $data_feed; ?>">Информация по настройке</label>
              <div class="col-sm-10 control-label" style="text-align:left;">
                <h4>Общие и рекомендации по настройках</h4>
                <ul>
                  <li><b style="display:block;padding:6px 10px;background:#F7FDFF;border:1px dashed #39b3d7;">Посмотрите информацию на официальном сайте <a href="https://unixml.pro/info/feed/<?php echo $feed; ?>" target="_blank">https://unixml.pro/info/feed/<?php echo $feed; ?></a>.
                      Если откроется главная страница сайта https://unixml.pro это означает что пока для <?php echo $feed; ?> нет информации или необходимости в этой информации.
                      Посмотрите чуть позже или напишите автору по всем вопросам.</b></li>
                  <li>Всегда проверяйте корректность открытия файла или ссылки фида в браузере. Если товаров много то браузер может тормозить или зависнуть - тогда смотрите в любом другом валидаторе XML</li>
                  <li>Если у вас не генерируется фид или видите ошибку Allowed memory size - это означает что вы упираетесь в лимит по оперативной памяти на сервере. Рекомендуется уменьшить в пункте <a data-id="5-4" class="gotoset" title="Перейти к настройке" data-toggle="tooltip">5.4</a> число товаров за раз. Подробнее посмотрите в подсказке к пункту настроек</li>
                  <li>Проверьте время генерации и нагрузку на сервер во время генерации. Если это долго, ну скажем более 5-10 секунд, рекомендуется ставить генерацию на задание CRON и в маркетплейс(ы) отдавать уже готовый фид. Таким образом мы не будем нагружать сервер. Некоторые маркетплейсы запрашивают ваш фид каждый час и каждый раз генерировать не лучшая идея. Если все быстро и товаров не более чем пару тысяч - можете отдавать и прямую ссылку с пункта <a data-id="6-1" class="gotoset" title="Перейти к настройке" data-toggle="tooltip">6.1</a> "Ссылка запуска прямой генерации"</li>
                  <li>Для безопасности рекомендуется задавать Ключ защиты от запуска в пункте <a data-id="5-6" class="gotoset" title="Перейти к настройке" data-toggle="tooltip">5.6</a> а также задать нечитабельное название файла XML в пункте <a data-id="5-7" class="gotoset" title="Перейти к настройке" data-toggle="tooltip">5.7</a>, например 34hmdf7. Таким образом ссылку запуска будете знать только вы и никто случайно ее не найдет, как и готовый файл.</li>
                  <li>Когда возникают вопросы по настройке, перед тем как обратится в поддержку, пожалуйста <b>посмотрите информацию на официальном сайте</b> <a href="https://unixml.pro/set/export/" target="_blank" title="Откроется в новом окне" data-toggle="tooltip">https://unixml.pro/set/export/</a>. Если ничего не нашли - пишите автору по контактным данным <a href="https://unixml.pro/support/" target="_blank" title="Откроется в новом окне" data-toggle="tooltip">https://unixml.pro/support/</a></li>
                </ul>
                <h4>Если у вас выгружаются <b>не все товары</b> рекомендуется проверить следующие пункты настроек</h4>
                <ul>
                  <li><a data-id="2-1" class="gotoset" title="Перейти к настройке" data-toggle="tooltip">2.1</a> - посмотрите не внесли ли те товары в список, или же наоборот, внесли только некоторые, а остальные (не в списке) не будут выгружаться - это при условии "Выгружать только"</li>
                  <li><a data-id="2-2" class="gotoset" title="Перейти к настройке" data-toggle="tooltip">2.2</a> - проверьте выбраны ли категории товаров. Нужно выбирать конечные категории. И если стоит привязка на главную то выбираем те категории которые зазначены как главные в нужных товарах</li>
                  <li><a data-id="2-3" class="gotoset" title="Перейти к настройке" data-toggle="tooltip">2.3</a> - проверьте выбраны ли нужные производители, если нет привязки - ничего не надо выбирать</li>
                  <li><a data-id="2-4" class="gotoset" title="Перейти к настройке" data-toggle="tooltip">2.4</a> - если у вас есть лишние товары посмотрите на логику выгрузки. Если в этом пункте стоит "А также товары" то будет выборка категори а также бренды, товары которых могут быть из других категорий</li>
                  <li><a data-id="2-5" class="gotoset" title="Перейти к настройке" data-toggle="tooltip">2.5</a> - если вы поставили привязку на главную категорию тогда в тех товарах что должны заходить в фид должна быть выбрана главная категория. Если не ставить привязку на главную UniXML сам попробует ее найти.</li>
                  <li><a data-id="2-6" class="gotoset" title="Перейти к настройке" data-toggle="tooltip">2.6</a> - проверьте остатки в тех товарах которых нет в выгрузке, они должны быть больше 0, если выбрали привязку на наличие</li>
                  <li><a data-id="2-7" class="gotoset" title="Перейти к настройке" data-toggle="tooltip">2.7</a> - если стоит привязываться к фото, проверьте есть ли фото в товарах</li>
                  <li><a data-id="5-2" class="gotoset" title="Перейти к настройке" data-toggle="tooltip">5.2</a> - проверьте, если стоит id товара с другого поля, важно что бы поле было уникальным. В фиде offer id должны быть уникальные и если где-то у вас есть дубли то в фиде будет только один из дублей. Например если выбрали брать id товара из поля p.upc то надо быть уверенным что в поле upc уникальные значения для всех товаров</li>
                  <li><b>Также проверьте</b> в "невыгружаемых" товарах все ли данные у них есть. Например если в товаре не будет задана категория - он не попадает в выгрузку. Если в товаре нет бренда, но сами бренды вы поставили, товар не выгрузится. Если не нужна фильтрация по производителям то в пункте <a data-id="2-3" class="gotoset" title="Перейти к настройке" data-toggle="tooltip">2.3</a> ничего не надо выбирать. Также если не нужна фильтрация по категориям в пункте <a data-id="2-2" class="gotoset" title="Перейти к настройке" data-toggle="tooltip">2.2</a> ничего не задаем, однако следует помнить что товары без заданных категорий (в самом товаре) выгружаться не будут. Категория в товаре должна быть в любом случае.</li>
                </ul>
              </div>
            </div>

          </div>
          <!--/export-block-6-->

      </form>

      <div id="get-option-html">
        <div class="option-block-item option-block-777" data-option-block="777">
          <div class="row mtb-10">
            <div class="col-sm-12">
              <input type="text" placeholder="Добавить опции в набор" class="form-control get-select-option" data-option-block="777">

              <div class="row">
                <div class="col-sm-9">
                  <div class="scrollbox option_select_scroll"><div class="option-list-placeholder"  data-option-block="777">Добавьте опции для набора <i class="fa fa-level-up" aria-hidden="true"></i></div></div>
                </div>
                <div class="col-sm-3 mt10">
                  <div><b>Набор опций 777</b></div>
                  <span class="delete-option-block" data-option-block="777">Удалить набор</span>
                  <div style="margin-top:20px;">В выгрузке будет:<br><b>[[optionset777]]</b></div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

    </div>
    <div class="col-sm-2" style="padding:0;z-index:2;">

      <div class="export-navigation-fast">
        <input type="text" placeholder="Быстрая навигация" autofocus class="form-control">
        <ul class="export-navigation-list">
          <?php foreach($export_fields as $export_block){ ?>
          <li data-search="<?php echo $export_block['block_search']; ?>" data-id="export-block-<?php echo $export_block['block_id']; ?>">
            <span><?php echo $export_block['block_name']; ?></span>
            <ul>
              <?php foreach($export_block['block_fields'] as $block_field){ ?>
                <li data-search="<?php echo $block_field['field_search']; ?>" data-id="export-block-<?php echo $export_block['block_id']; ?>-<?php echo $block_field['field_id']; ?>">
                  <span><?php echo $export_block['block_id']; ?>.<?php echo $block_field['field_id']; ?> <?php echo $block_field['field_name']; ?></span>
                </li>
              <?php } ?>
            </ul>
          </li>
          <?php } ?>
        </ul>
      </div>

      <ul class="export-navigation">
        <li class="export-block-1 active">1. Основные настройки</li>
        <li class="export-block-2">2. Фильтр товаров</li>
        <li class="export-block-3">3. Изменения контента</li>
        <li class="export-block-4">4. Кастомный код</li>
        <li class="export-block-5">5. Системные настройки</li>
        <li class="export-block-6">6. Информация</li>
      </ul>
      <span class="load_system_setting_popup btn btn-default" data-feed="<?php echo $feed; ?>" title="Структура и системные настройки выгрузки" data-toggle="tooltip" data-placement="left"><i class="fa fa-code" aria-hidden="true"></i> Структура файла XML</span>
      <a href="https://unixml.pro/info/feed/<?php echo $feed; ?>" class="btn btn-warning howset" target="_blank" title="Откроется в новом окне. Если откроется главная страница сайта https://unixml.pro это означает что пока для <?php echo $feed; ?> нет информации или необходимости в этой информации. Посмотрите чуть позже или напишите автору по всем вопросам" data-toggle="tooltip" data-placement="left">
        Как настроить <?php echo $feed; ?>?
      </a>

    </div>
  </div>
  <span class="to-top">Наверх</span>

  <!--scripts after load popup-->
    <script>
      option_multiplier_key = '<?php echo $option_multiplier_key; ?>';
      category_match_row = '<?php echo $category_match_row; ?>';
      attribute_row = '<?php echo $attribute_row; ?>';
      product_markup_row = '<?php echo $product_markup_row; ?>';
      replace_name_row = '<?php echo $replace_name_row; ?>';
      param_row = <?php echo $param_row; ?>;
      $('<?php echo $minus; ?>').hide();
      setTimeout(function(){
        $('<?php echo $plus; ?>').show();
      },50);
      loaded_export_setting('<?php echo $feed; ?>');
    </script>
  <!--/scripts after load popup-->

<?php } ?>

<?php echo $header; ?>
<?php $v = '7.2.10.11'; ?>
<?php //$v = rand(1000,100000000000); ?>
<link href="view/javascript/extension/feed/unixml.css?v<?php echo $v; ?>" type="text/css" rel="stylesheet" />
<?php echo $column_left; ?>

<div id="content">

  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" data-placement="left" title="Все данные уже сохранены" class="btn btn-default">Выйти с модуля</a>
      </div>
      <h1><?php echo $heading_title_module; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
          <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>

  <div class="container-fluid">

    <ul class="nav nav-tabs nav-main-unixml">
      <li class="active"><a href="#tab-import" data-toggle="tab"><i class="fa fa-download" aria-hidden="true"></i> Загрузка в магазин (beta)</a></li>
      <li><a href="#tab-export" data-toggle="tab"><i class="fa fa-upload" aria-hidden="true"></i> Выгрузка из магазина</a></li>
      <li><a href="#tab-service" data-toggle="tab"><i class="fa fa-wrench" aria-hidden="true"></i> Сервис</a></li>
      <li><a href="#tab-info" data-toggle="tab"><i class="fa fa-life-ring" aria-hidden="true"></i> Информация</a></li>
    </ul>

    <?php if(!$a){ ?>

      <div>
        <?php echo $message; ?>
      </div>

    <?php }else{ ?>

      <div class="tab-content tabs-data" style="margin-top:-2px;">

        <!--tab-import-->
        <div class="tab-pane active" id="tab-import">
          <div class="container-fluid" style="padding:0;">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> Импорт/Обновление товаров из XML (YML) в магазин</h3>
              </div>
              <div class="panel-body">
                <div class="alert alert-warning">Импорт/обновление товаров из XML в Opencart - <strong>это тестовый функционал. По нему нет поддержки.</strong> Подсказать/помочь автор может, но без гарантий. Этот функционал по мере использования будет отлаживаться и будут внедряться пожелания пользователей. Все действия по импорту на собственный риск. Не забывайте всегда делать бекапы базы данных.</div>
                <div class="table-responsive">
                  <table class="table table-bordered table-hover">
                    <thead><tr><td class="text-left">Название</td><td class="text-left">Комментарий</td><td class="text-left">Файл/Ссылка XML</td><td class="text-left">Создан</td><td class="text-left">Статус</td><td class="text-right">Действия</td></tr></thead>
                    <tbody id="load_prices"><tr><td colspan="6" class="text-center">Загрузка...</td></tr></tbody>
                  </table>
                </div>
                <div class="text-right">
                  <button class="btn btn-success" data-toggle="modal" data-target="#upload_price">Добавить прайс</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--/tab-import-->

        <!--/tab-export-->
        <div class="tab-pane" id="tab-export">
          <input type="hidden" id="current_feed" name="current_feed" value="">
          <div class="panel panel-default">
            <div class="panel-heading">
              <div class="row">
                <div class="col-sm-8">
                  <h3 class="panel-title" style="line-height:35px;"><i class="fa fa-pencil"></i> Экспорт товаров из магазина в маркетплейсы XML (<span id="export_counter">0</span>)</h3>
                </div>
                <div class="col-sm-4">
                  <input type="text" class="form-control" id="feed_list_search" placeholder="Поиск по списку фидов">
                </div>
              </div>
            </div>
            <div class="panel-body">

               <div class="table-responsive">
                 <table class="table table-bordered table-hover">
                   <thead><tr><td class="text-center">#</td><td class="text-left">Выгрузка</td><td class="text-left">Статус</td><td class="text-left">Ссылки</td><td class="text-right">Действия</td></tr></thead>
                   <tbody id="load_exports"><tr><td colspan="6" class="text-center">Загрузка...</td></tr></tbody>
                 </table>
               </div>

               <div class="text-right">
                 <button class="btn btn-success" data-toggle="modal" data-target="#add_export">Добавить фид</button>
               </div>

            </div> <!--/panel-body-->
          </div>

          <!--trash-->
          <div class="panel panel-default">
            <div class="panel-heading">
              <div class="row">
                <div class="col-sm-8">
                  <h3 class="panel-title" id="trash_h3" style="line-height:35px;"><i class="fa fa-trash"></i> Корзина неиспользуемых фидов (<span id="trash_counter">0</span>)</h3>
                </div>
                <div class="col-sm-4 text-right">
                  <?php if(!$trash_toggle){ ?>
                    <button id="trash_list_toggle" class="btn btn-info">Свернуть <i class="fa fa-angle-down" aria-hidden="true"></i></button>
                  <?php }else{ ?>
                    <button id="trash_list_toggle" class="btn btn-info">Показать <i class="fa fa-angle-right" aria-hidden="true"></i></button>
                  <?php } ?>
                </div>
              </div>
            </div>
            <div class="panel-body" id="trash_list_toggle_body" <?php if($trash_toggle){ ?>style="display:none;"<?php } ?>>

              <div class="table-responsive">
                <table class="table table-bordered table-hover">
                  <thead><tr><td class="text-center">#</td><td class="text-left">Выгрузка</td><td class="text-left">Статус</td><td class="text-left">Ссылки</td><td class="text-right">Действия</td></tr></thead>
                  <tbody id="load_trash"><tr><td colspan="6" class="text-center">Загрузка...</td></tr></tbody>
                </table>
              </div>

            </div>
          </div>
          <!--trash-->

        </div>
        <!--/tab-export-->

        <!--tab-service-->
        <div class="tab-pane" id="tab-service">

          <div class="panel panel-default">

            <div class="panel-heading">
              <h3 class="panel-title"><i class="fa fa-pencil"></i> Сервисные функции UniXML - набор утилит для работы с контентом</h3>
            </div>

            <div class="panel-body">

              <div class="alert alert-danger text-left" role="alert" style="margin-top:10px;">
                <strong>Пожалуйста почитайте!</strong> Вы находитесь во вкладке Сервис. Здесь надо быть очень осторожным и перед каждым действием делать бекапы данных с которыми будете работать!
              </div>

              <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-service-photo" data-toggle="tab"><i class="fa fa-picture-o" aria-hidden="true"></i> Удаление лишних фото</a></li>
                <li><a href="#tab-service-product" data-toggle="tab"><i class="fa fa-database" aria-hidden="true"></i> Поиск дублей товаров</a></li>
                <li><a href="#tab-service-url" data-toggle="tab"><i class="fa fa-link" aria-hidden="true"></i><i class="fa fa-link" aria-hidden="true"></i> Поиск дублей ЧПУ</a></li>
                <li><a href="#tab-service-noimage" data-toggle="tab"><i class="fa fa-ban" aria-hidden="true"></i> Поиск товара без фото</a></li>
                <li><a href="#tab-service-delete" data-toggle="tab"><i class="fa fa-eraser" aria-hidden="true"></i> Удаление всех данных</a></li>
              </ul>

              <div class="tab-content">

                <!--tab-service-photo-->
                  <div class="tab-pane active" id="tab-service-photo">

                    <div class="form-group row">
                      <label class="col-sm-2 control-label" for="unixml_delete_direct">Выберите папку для сканирования фото</label>
                      <div class="col-sm-10">
                        <div class="input-group">
                          <span class="input-group-addon"><?php echo $image_catalog; ?></span>
                          <input type="text" name="unixml_delete_direct" id="unixml_delete_direct" value="unixml" placeholder="Если не указать папку чистка будет по всем вложенным" class="form-control" />
                        </div>
                      </div>
                    </div>

                    <div class="form-group row">
                      <label class="col-sm-2 control-label" for="unixml_delete_table">
                        В каких таблицах ищем фото
                        <small class="sub_label">Таблица=Поле (Каждая таблица с новой строки. Список таблиц для поиска можно посмотреть в phpmyadmin)</small>
                      </label>
                      <div class="col-sm-5">
                        <textarea style="height:122px;" name="unixml_delete_table" id="unixml_delete_table" class="form-control" placeholder="По умолчанию product=image и product_image=image"></textarea>
                      </div>
                      <div class="col-sm-5">
                        <div class="alert alert-warning">
                          <p><strong>Внимание!</strong> Удаление фото это серьезные работы по сайту.</p>
                          <p><b style="color:red;">Перед выполнением убедитесь что сделали бекап базы данных и папки image сайта!</b></p>
                          <p>Разработчик модуля не несет ответственности за неправильное использование функционала модуля. </p>
                        </div>
                      </div>
                    </div>

                    <button type="button" class="btn btn-success" id="start_delete"><i class="fa fa-wrench" aria-hidden="true"></i> Запустить поиск и удаление неиспользуемых фото</button>

                  </div>
                <!--/tab-service-photo-->

                <!--tab-service-product-->
                  <div class="tab-pane" id="tab-service-product">

                    <div class="form-group row">
                      <label class="col-sm-2 control-label" for="unixml_double_field">По какому параметру определяем дубль</label>
                      <div class="col-sm-5">
                        <select name="unixml_double_field" id="unixml_double_field" class="form-control">
                          <option value="product_description.name">Название товара (таблица product_description поле name)</option>
                          <option value="product.model">Модель товара (таблица product поле model)</option>
                          <option value="product.sku">Модель товара (таблица product поле sku)</option>
                        </select>
                      </div>
                      <div class="col-sm-5">
                        <button type="button" class="btn btn-success" id="search_double"><i class="fa fa-wrench" aria-hidden="true"></i> Запустить ПОИСК дублей товара на сайте</button>
                      </div>
                    </div>


                    <div id="double_skan_result"></div>
                    <button type="button" class="btn btn-warning" id="delete_double"><i class="fa fa-wrench" aria-hidden="true"></i> Запустить УДАЛЕНИЕ дублей товара на сайте</button>

                  </div>
                <!--/tab-service-product-->

                <!--tab-service-url-->
                <div class="tab-pane" id="tab-service-url">

                  <button type="button" class="btn btn-success" id="start_url"><i class="fa fa-wrench" aria-hidden="true"></i> Запустить поиск дублей ЧПУ на сайте</button>

                </div>
                <!--tab-service-url-->

                <!--tab-service-noimage-->
                  <div class="tab-pane" id="tab-service-noimage">

                    <button type="button" class="btn btn-success" id="start_noimage"><i class="fa fa-search" aria-hidden="true"></i> Запустить поиск товаров без фото или с битыми фото</button>
                    <div id="noimage_result"></div>
                    <button type="button" class="btn btn-success" id="delete_noimage"><i class="fa fa-eraser" aria-hidden="true"></i> Удалить битые файлы и отсутствующие фото с базы</button>

                  </div>
                <!--/tab-service-noimage-->

                <!--tab-service-delete-->
                  <div class="tab-pane" id="tab-service-delete">

                    <h3>Выберите что удалить в магазине:</h3>
                    <div id="delete_list">
                      <div><input type="checkbox" class="checkbox_exp" id="sd1" name="delete_data[]" value="1"><label for="sd1">Категории</label></div>
                      <div><input type="checkbox" class="checkbox_exp" id="sd2" name="delete_data[]" value="2"><label for="sd2">Товары</label></div>
                      <div><input type="checkbox" class="checkbox_exp" id="sd3" name="delete_data[]" value="3"><label for="sd3">Производители</label></div>
                      <div><input type="checkbox" class="checkbox_exp" id="sd4" name="delete_data[]" value="4"><label for="sd4" title="Удаляются записи в базе данных, физически фото остается. Удалить их можно с помощью UniXML">Фото товара</label></div>
                      <div><input type="checkbox" class="checkbox_exp" id="sd5" name="delete_data[]" value="5"><label for="sd5">Атрибуты</label></div>
                      <div><input type="checkbox" class="checkbox_exp" id="sd6" name="delete_data[]" value="6"><label for="sd6">Опции</label></div>
                      <div><input type="checkbox" class="checkbox_exp" id="sd7" name="delete_data[]" value="7"><label for="sd7">ЧПУ (товары, бренды, категории)</label></div>
                    </div>

                    <div class="alert alert-danger text-left" role="alert" style="margin-top:10px;">
                      <strong>Внимание!</strong> Действие необратимо и удаляет эти данные навсегда! Рекомендуется сделать бекап базы данных!
                    </div>

                    <button type="button" class="btn btn-danger" id="delete_data"><i class="fa fa-eraser" aria-hidden="true"></i> Удалить выбранные данные</button>
                    <span id="deletedata_result"></span>

                  </div>
                <!--/tab-service-delete-->

              </div><!--/tabs-->

            </div><!--/panel-body-->

          </div><!--/panel-->

        </div>
        <!--/tab-service-->

        <!--tab-info-->
        <div class="tab-pane" id="tab-info">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title"><i class="fa fa-pencil"></i> Информация о модуле и техническая поддержка</h3>
            </div>
            <div class="panel-body">
              <h3>Спасибо за использование модуля!</h3>
              <p>Разработчик модуля: <b>Прут Николай</b></p>
              <p>Сайт модуля: <a href="https://unixml.pro" target="_blank">https://unixml.pro</a></p>
              <p>Телеграм: <a href="https://t.me/PrutNikolay" target="_blank">https://t.me/PrutNikolay</a></p>
              <p>Профиль на форуме opencart: <a href="https://opencartforum.com/profile/18336-exploits/" target="_blank">Opencartforum</a></p>
              <p>Почта: <a href="mailto:info@microdata.pro">info@microdata.pro</a></p>
              <blockquote>Большая просьба! Когда пишите в поддержку сразу указывайте ваш домен и суть вопроса (максимально подробно) в одном сообщении.<br>
                Также просьба не дублировать обращения в разные каналы связи. Я все везде вижу и обрабатываю.</blockquote>
            </div>
          </div>
        </div>
        <!--/tab-info-->

      </div><!--/tabs-->

    <?php } //if not a ?>

  </div><!--/container-fluid-->


</div><!--/content-->

<!--modals-->

  <!--modal import process-->
    <div class="modal fade" id="price_start" tabindex="-1" role="dialog" aria-labelledby="price_startTitle" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <input type="hidden" id="ciwin">
            <span class="modal-title" id="price_startTitle">Импорт из XML в магазин
              <span id="current_import_status"></span>
            </span>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="price_start_close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="price_start_load">
            <div class="row" id="progress">
              <div class="col-sm-12">
                <h3 id="stat_title" style="max-width:300px;"><strong></strong><span style="color:#999;display:none;"></span></h3>
                <div class="import_status"></div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div class="row" id="server-import-response-log" style="display:none;">
              <div class="col-sm-12 text-left">
                <h3>Лог ответов сервера:</h3>
                <span id="server_query_counter"></span>
                <div>
                  <table class="table table-bordered table-statuses">
                    <thead><tr><td>#</td><td>Код</td><td>Ответ</td></tr></thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-6 text-left"><button id="close_process_window" class="btn btn-default" onclick="$('#price_start_close').click();">Закрыть окно</button></div>
              <div class="col-sm-6 text-right">
                <button id="pause_process" style="display:none;" class="btn btn-warning"><i class="fa fa-pause" aria-hidden="true"></i> Поставить на паузу</button>
                <button id="resume_process" style="display:none;" class="btn btn-success"><i class="fa fa-play" aria-hidden="true"></i> Возобновить импорт</button>
              </div>
            </div>
            <div id="server-import-error-log" style="text-align:left;"></div>
          </div>
        </div>
      </div>
    </div>
  <!--/modal import process-->

  <!--modal delete products-->
    <div class="modal fade" id="price_delete_product" tabindex="-1" role="dialog" aria-labelledby="price_delete_productTitle" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <input type="hidden" value="" id="id_delete_product_xml">
            <span class="modal-title" id="price_delete_productTitle">Удаление всех товров из этого XML</span>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="price_delete_product_close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="price_delete_product_load">
            <div class="alert alert-danger text-left" role="alert">
              <strong>Внимание!</strong><br>
                Действие необратимо и удаляет все товары найденные по связанному ключу из этого XML<br>
                Рекомендуется сделать бекап базы данных и папки image/catalog в случае удаления фото!
            </div>
            <div class="input-group">
              <input type="checkbox" class="checkbox_exp" id="di" name="delete_image" value="1">
              <label for="di" class="bnv">Удалить физически фото товаров на сервере</label>
              <span class="input-group-addon" data-toggle="popover" title="Физическое удаление фото" data-content="Если включить эту опцию UniXML удалит не только записи товаров в базе данных, но и также фото. Это действие необратимо. Фото ФИЗИЧЕСКИ И НАВСЕГДА удаляются!">?</span>
            </div>
            <hr>
            <div class="input-group">
              <input type="checkbox" class="checkbox_exp" id="dok" name="delete_ok" value="1">
              <label for="dok" class="bnv">Подтверждаю удаление товаров!</label>
            </div>
          </div>
          <div class="modal-footer">
            <div class="save_block row">
              <div class="col-sm-6 text-left"><button class="btn btn-default" onclick="$('#price_delete_product_close').click();">Отмена, не удалять!</button></div>
              <div class="col-sm-6 text-right"><button class="btn btn-danger" id="delete_price_products" style="display:none;"><i class="fa fa-trash" aria-hidden="true"></i> Удалить все товары этого XML</button></div>
            </div>
          </div>

        </div>
      </div>
    </div>
  <!--/modal delete products-->

  <!--modal price setting-->
    <div class="modal fade" id="price_setting" tabindex="-1" role="dialog" aria-labelledby="price_settingTitle" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <span class="modal-title" id="price_settingTitle">Редактировать настройки выгрузки</span>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="price_setting_close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="price_setting_load"><div class="text-center">Загрузка...</div></div>
          <div class="modal-footer">
            <div class="save_block row">
              <div class="col-sm-8">
                <div class="alert alert-success text-left" role="alert" style="height:58px;">
                  Ссылка запуска по CRON: <nobr><strong data-toggle="tooltip" title="CTRL+C (CMD+C) для копирования." style="display:block;" id="cron_link_info"><span id="import_cron_key"></span></strong></nobr>
                </div>
              </div>
              <div class="col-sm-4" style="padding-left:0;"><!--new-->
                <div class="alert alert-warning text-left" role="alert" style="height:58px;">
                  Связующий ключ:
                  <nobr><strong id="link_key_info"></strong></nobr>
                </div>
              </div>
            </div>
            <div class="save_block row">
              <div class="col-sm-3 text-left"><button class="btn btn-default" onclick="$('#price_setting_close').click();">Закрыть без сохранения</button></div>
              <div class="col-sm-2 text-center"><button class="btn btn-default" id="import_clear_data">Удалить данные импорта<br><small>Временные файлы, статистику, и тп</small></button></div>
              <div class="col-sm-3 text-right"><button class="btn btn-success" id="save_and_start"><i class="fa fa-play" aria-hidden="true"></i> Сохранить и запустить</button></div>
              <div class="col-sm-4 text-right" style="padding-left:0px;"><button style="width:100%;" class="btn btn-success" id="save_price_item"><i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить настройки</button></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <!--/modal price setting-->

  <!--modal price item setting-->
    <div class="modal fade" id="price_setting_item_set" style="z-index:1051;" tabindex="-1" role="dialog" aria-labelledby="price_setting_item_setTitle" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <span class="modal-title" id="price_setting_item_setTitle">Редактировать настройки поля</span>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="price_setting_item_set_close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="price_setting_item_set_load"><div class="text-center">Загрузка...</div></div>
          <div class="modal-footer">
            <div class="save_block row">
              <div class="col-sm-6 text-left"><button class="btn btn-default" onclick="$('#price_setting_item_set_close').click();">Закрыть</button></div>
              <div class="col-sm-6 text-right"><button class="btn btn-success" id="save_price_setting_item_set"><i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить</button></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <!--/modal price item setting-->

  <!--modal add price-->
    <div class="modal fade" id="upload_price" tabindex="-1" role="dialog" aria-labelledby="upload_priceTitle" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <span class="modal-title" id="upload_priceTitle">Добавить новый XMl файл для загрузки</span>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="upload_price_close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <h4>Название прайса</h4>
            <input type="text" class="form-control" name="price_name" id="price_name"><br>
            <h4>Комментарий</h4>
            <textarea class="form-control" style="height:100px;" name="price_comment" id="price_comment"></textarea><br>
            <div class="row">
              <div class="col-sm-4"><button id="button-upload" style="width:100%;" class="btn btn-info"><i class="fa fa-upload" aria-hidden="true"></i> Выбрать файл</button></div>
              <div class="col-sm-8 text-or"><input placeholder="Указать путь или ссылку к XML" type="text" name="price_file" id="price_file" class="form-control"></div>
            </div>
            <div class="row" style="margin-top:15px;">
              <div class="col-sm-12"><button id="price_save" disabled="disabled" style="width:100%;" class="btn btn-success"><i class="fa fa-save" aria-hidden="true"></i> Сохранить прайс</button></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <!--/modal add price-->

  <!--modal modal add export-->
    <div class="modal fade" id="add_export" tabindex="-1" role="dialog" aria-labelledby="add_exportTitle" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <span class="modal-title" id="add_exportTitle">Добавить новую выгрузку</span>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="add_export_close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <h4>Код выгрузки (латиница, нижний регистр, без пробелов и спецсимволов)</h4>
            <div class="alert alert-info">Код выгрузки будет как имя исполняеного файла, например если код feedname_secont то исполняемый файл будет feedname_secont.php. Допускается только латиница, нижний регистр, цифры (в конце) и нижнее подчеркивание</div>
            <input type="text" class="form-control" name="price_code" id="price_code" placeholder="Например: yandex или feedname_secont или feedname2"><br>
            <h4>Название выгрузки (без ограничений, будет в списке фидов)</h4>
            <input type="text" class="form-control" name="price_feed" id="price_feed"><br>
            <div class="alert alert-info">Новый фид будет сформирован на основе одного из существующих. При этом копируется исполняемый файл в которого будут свои настройки и ссылки для запуска. Если выбрать Копировать настройки то фид не только копируется по структуре а и копируются его настройки. Таким образом мы получаем полную копию выгрузки.</div>
            <div class="row">
              <div class="col-sm-5" style="padding-top:12px;">Создаем выгрузку на основе</div>
              <div class="col-sm-4">
                <select id="add_export_from" name="add_export_from" class="form-control">
                  <?php foreach($dir_feeds as $dir_feed){ ?>
                    <option value="<?php echo $dir_feed; ?>"><?php echo $dir_feed; ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="col-sm-3 text-right" style="padding-left:0;"><button id="example_view" class="btn btn-info">Посмотреть XML</button></div>
            </div>
            <div class="row" style="margin-top:15px;">
              <div class="col-sm-5" style="padding-top:12px;">
                <input type="checkbox" style="position:absolute;" checked="checked" class="checkbox_exp" id="copy_and_setting" name="copy_and_setting" value="1">
                <label for="copy_and_setting" style="font-weight:400;">Копировать и настройки</label>
              </div>
              <div class="col-sm-7"><button id="export_save" style="width:100%;" class="btn btn-success"><i class="fa fa-save" aria-hidden="true"></i> Создать выгрузку</button></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <!--/modal add export-->

  <!--modal export setting-->
    <div class="modal fade" id="export_setting" tabindex="-1" role="dialog" aria-labelledby="export_settingTitle" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-body" id="export_setting_load" style="padding-top:0px;">
            <div style="font-size:16px;line-height:35px;text-align:center;margin-top:24px;">Загрузка...</div>
          </div>
        </div>
      </div>
    </div>
  <!--/modal export setting-->

  <!--modal export system-->
    <div class="modal fade" style="z-index:1052;" id="export_system" tabindex="-1" role="dialog" aria-labelledby="export_systemTitle" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-body" id="export_system_load" style="padding-top:0px;">
            <div style="font-size:16px;line-height:35px;text-align:center;margin-top:24px;">Загрузка...</div>
          </div>
        </div>
      </div>
    </div>
  <!--/modal export system-->

  <!--modal export setting replace-->
    <div class="modal fade" id="export_setting_replace" tabindex="-1" role="dialog" aria-labelledby="export_systemTitle" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-body" id="export_system_load" style="padding-top:0px;">
            <div style="font-size:16px;line-height:35px;text-align:center;margin-top:24px;">Загрузка...</div>
          </div>
        </div>
      </div>
    </div>
  <!--/modal export setting replace-->

  <!--modal export long fields help-->
    <div class="modal fade" id="uxmLong" tabindex="-1" role="dialog" aria-labelledby="uxmLongTitle" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <span class="modal-title" id="uxmLongTitle">Доступность полей базы данных</span>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <h3>В выгрузке можно использовать:</h3>
            <div class="row">
              <div class="col-sm-6">
                <h4>Таблица product</h4>
                <ul>
                  <?php foreach($fields_product as $field){ ?>
                    <li>((<?php echo $field; ?>))</li>
                  <?php } ?>
                </ul>
              </div>
              <div class="col-sm-6">
                <h4>Таблица product_description</h4>
                <ul>
                  <?php foreach($fields_product_description as $field){ ?>
                    <li>((<?php echo $field; ?>))</li>
                  <?php } ?>
                </ul>
                <h4>Массив данных товара (с уже примененными заменами и генерациями)</h4>
                <ul>
                  <li>{product_id} - id товара</li>
                  <li>{name} - название</li>
                  <li>{url} - ссылка</li>
                  <li>{price} - цена</li>
                  <li>{special} - акция</li>
                  <li>{image} - фото</li>
                  <li>{category} - категория</li>
                  <li>{manufacturer} - бренд</li>
                  <li>{quantity} - количество</li>
                  <li>{description} - описание</li>
                  <li>и другие поля которые где-то указаны в настройках модуля. Например в генерации названия если указали location то поле будет в массиве товаров</li>
                </ul>
              </div>
            </div>

            <p>Поле которого не будет в базе - не выводится</p>
          </div>
        </div>
      </div>
    </div>
  <!--/modal export long fields help-->

  <!--modal export short fields help-->
    <div class="modal fade" id="uxmShort" tabindex="-1" role="dialog" aria-labelledby="uxmShortTitle" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <span class="modal-title" id="uxmShortTitle">Доступность полей базы данных</span>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <h3>Можно использовать поля:</h3>
            <div class="row">
              <div class="col-sm-6">
                <h4>Таблица product</h4>
                <ul>
                  <?php foreach($fields_product as $field){ ?>
                    <li><?php echo $field; ?></li>
                  <?php } ?>
                </ul>
              </div>
              <div class="col-sm-6">
                <h4>Таблица product_description</h4>
                <ul>
                  <?php foreach($fields_product_description as $field){ ?>
                    <li><?php echo $field; ?></li>
                  <?php } ?>
                </ul>
              </div>
            </div>

            <p>Поле которого не будет в базе - не будет забираться</p>
          </div>
        </div>
      </div>
    </div>
  <!--/modal export short fields help-->

  <!--modal export product import-->
    <div class="modal fade" id="import_to_markup" style="z-index:1051;" tabindex="-1" role="dialog" aria-labelledby="import_to_markupTitle" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <span class="modal-title" id="import_to_markupTitle">Импорт товаров в группу скидки</span>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <h3>Вставьте товары через разделитель:</h3>
            <div class="alert alert-warning">По умолчанию разделитель - новая строка. Значение по умолчанию это product_id. Вы можете поставить любое поле из таблиц product или product_description например pd.name или p.model - если есть список моделей. Если выбрать перезаписывать товары то импортируемый список заменит то что уже есть в группе, в ином случае импортируемые товары добавляются к уже соществующему списку.</div>
            <input type="hidden" id="import_feed">
            <input type="hidden" id="import_row">
            <textarea id="import_textarea" class="form-control" style="height:200px;"></textarea><br>
            <div class="row">
              <div class="col-sm-6">Разделитель товаров: <input id="import_serapator" class="form-control" style="display:inline-block;" placeholder="Если с новой строки - не заполняйте"></div>
              <div class="col-sm-6">Значение это поле: <input id="import_field" class="form-control" style="display:inline-block;" placeholder="Напр: p.model p.sku pd.name и т.п"></div>
            </div>
            <div id="import_stat" style="color:green;height:20px;line-height:20px;"></div>
            <div class="row">
              <div class="col-sm-6" style="padding-top:10px;">
                <input type="checkbox" style="position:absolute;" checked="checked" data-feed="price" class="checkbox_exp" id="clear_old" name="clear_old" value="price">
                <label for="clear_old">Перезаписать товары</label>
              </div>
              <div class="col-sm-6"><button id="import_start" style="width:100%;" class="btn btn-success"><i class="fa fa-play" aria-hidden="true"></i> Поехали!</button></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <!--/modal export product import-->

<!--/modals-->

<script>
  unixml_path = '<?php echo $path; ?>';
  unixml_token = '<?php echo $token; ?>';
</script>
<script type="text/javascript" src="view/javascript/extension/feed/unixml.js?v<?php echo $v; ?>; ?>"></script>

<?php echo $footer; ?>

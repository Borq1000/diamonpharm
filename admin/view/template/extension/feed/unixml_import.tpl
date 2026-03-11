<?php if(isset($prices)){ //список импортов ?>
  <?php if($prices){ ?>
    <?php foreach($prices as $price){ ?>
    <tr class="price_list_<?php echo $price['setting_id']; ?>">
      <td class="text-left"><?php echo $price['name']; ?></td>
      <td class="text-left"><?php echo $price['comment']; ?></td>
      <td class="text-left" title="<?php echo $price['file']; ?>"><?php echo $price['file_table']; ?></td>
      <td class="text-left"><?php echo $price['date']; ?></td>
      <td class="text-left status_in_list">
        <?php foreach($price['status'] as $stat){ ?>
          <div><nobr><?php echo $stat; ?></nobr></div>
        <?php } ?>
      </td>
      <td class="text-right">
        <nobr>
          <span class="price_start" data-id="<?php echo $price['setting_id']; ?>" data-name="<?php echo $price['name']; ?>" data-toggle="modal" data-target="#price_start"><span data-toggle="tooltip" title="Запустить выгрузку" class="btn btn-success"><i class="fa fa-play"></i></span></span>
          <span class="price_pause" data-name="<?php echo $price['name']; ?>" data-toggle="modal" data-target="#price_start" style="display:none;" data-id="<?php echo $price['setting_id']; ?>"><span data-toggle="tooltip" title="Посмотреть процесс импорта" class="btn btn-warning"><i class="fa fa-bar-chart"></i> Посмотреть &nbsp;</span></span>
          <span class="price_setting price_setting<?php echo $price['setting_id']; ?>" data-id="<?php echo $price['setting_id']; ?>" data-toggle="modal" data-target="#price_setting"><span data-toggle="tooltip" title="Редaктировать настройки прайса" class="btn btn-info"><i class="fa fa-pencil"></i></span></span>
          <span data-toggle="tooltip" title="Удалить прайс" class="price_delete btn btn-danger" data-id="<?php echo $price['setting_id']; ?>"><i class="fa fa-trash-o"></i></span>
          <span class="price_delete_product" data-id="<?php echo $price['setting_id']; ?>" data-toggle="modal" data-target="#price_delete_product"><span data-toggle="tooltip" title="Удалить все товары этого XML" class="btn btn-warning"><i class="fa fa-times"></i></span></span>
        </nobr>
      </td>
    </tr>
    <?php } ?>
  <?php }else{ ?>
    <tr>
      <td class="text-center" colspan="5">Прайсы отсутствуют. Для загрузки и настройки нажмите внизу на кнопку Добавить прайс</td>
    </tr>
  <?php } ?>
<?php } ?>

<?php if(isset($xml_example)){ //пример XML файла ?>
&lt;<span data-toggle="tooltip" data-placement="right" title="Корневой элемент каталога. Его нигде указывать не надо">yml_catalog</span>&gt;
  &lt;<span data-hover=".xst_root" data-toggle="tooltip" data-placement="right" title="Тег каталога - этот тег указываем в настройках. Внимание! В некоторых XML нет тега каталога. Если это так - просто ничего не указываем.">shop</span>&gt;
    &lt;<span data-hover=".xst_categories" data-toggle="tooltip" data-placement="right" title="Тег категорий - этот тег указываем в настройках">categories</span>&gt;
      &lt;<span data-hover=".xst_category" data-toggle="tooltip" data-placement="right" title="Тег категории - этот тег указываем в настройках">category</span> <span data-hover=".xst_category_id" data-toggle="tooltip" data-placement="right" title="id категории - если расположение в атрибуте как в примере указывайте название атрибута с приставкой @. Например @id">id</span>="1"&gt;<span data-hover=".xst_category_name" data-toggle="tooltip" data-placement="right" title="Название категории - если расположение в теге как в примере ничего не указываем.">Категория</span>&lt;/category&gt;
      &lt;category id="10" <span data-hover=".xst_category_parent_id" data-toggle="tooltip" data-placement="right" title="id родительской категории - если расположение в атрибуте как в примере указывайте название атрибута с приставкой @. Например @parentId">parentId</span>="1"&gt;Подкатегория&lt;/category&gt;
      &lt;category id="2"&gt;Категория 2&lt;/category&gt;
      &lt;category id="20" parentId="2"&gt;Подкатегория категории 2&lt;/category&gt;
    &lt;/categories&gt;
    &lt;<span data-hover=".xst_products" data-toggle="tooltip" data-placement="right" title="Тег товаров - этот тег указываем в настройках">offers</span>&gt;
      &lt;<span data-hover=".xst_product" data-toggle="tooltip" data-placement="right" title="Тег товара - этот тег указываем в настройках">offer</span> <span data-hover=".xst_product_id" data-toggle="tooltip" data-placement="right" title="id товара - если расположение в атрибуте как в примере указывайте название атрибута с приставкой @. Например @id">id</span>="1" <span data-hover=".xst_product_sku" data-toggle="tooltip" data-placement="right" title="Наличие или количество - этот тег указываем в настройках. Если указано в атрибутах как наличие то указываем в настройках с приставкой @">available</span>="true"&gt;
        &lt;<span data-hover=".xst_product_price" data-toggle="tooltip" data-placement="right" title="Цена товара - этот тег указываем в настройках">price</span>&gt;290.28&lt;/price&gt;
        &lt;<span data-hover=".xst_product_sku" data-toggle="tooltip" data-placement="right" title="Артикул товара - этот тег указываем в настройках">vendorCode</span>&gt;BH-1229-N&lt;/vendorCode&gt;
        &lt;<span data-hover=".xst_product_category_id" data-toggle="tooltip" data-placement="right" title="id категории товара - этот тег указываем в настройках">categoryId</span>&gt;10&lt;/categoryId&gt;
        &lt;<span data-hover=".xst_product_image" data-toggle="tooltip" data-placement="right" title="Главное фото товара - этот тег указываем в настройках">picture</span>&gt;https://site.com/image/product_image1.jpg&lt;/picture&gt;
        &lt;<span data-hover=".xst_product_images" data-toggle="tooltip" data-placement="right" title="Дополнительные фото товара - этот тег указываем в настройках. Может такое быть что главное и дополнительные фото идут под одним тегом. Указывайте везде один, UniXML все поймет :)">picture</span>&gt;https://site.com/image/product_image2.jpg&lt;/picture&gt;
        &lt;<span data-hover=".xst_product_name" data-toggle="tooltip" data-placement="right" title="Имя товара - этот тег указываем в настройках">name</span>&gt;&lt;![CDATA[Cковорода 20 см Carbon Metallic Line Berlinger Haus BH-1229-N]]&gt;&lt;/name&gt;
        &lt;<span data-hover=".xst_product_description" data-toggle="tooltip" data-placement="right" title="Описание товара - этот тег указываем в настройках">description</span>&gt;&lt;![CDATA[&lt;p&gt;Описание товара&lt;/p&gt;]]&gt;&lt;/description&gt;
        &lt;<span data-hover=".xst_product_manufacturer" data-toggle="tooltip" data-placement="right" title="Производитель - этот тег указываем в настройках">vendor</span>&gt;Berlinger Haus&lt;/vendor&gt;
        &lt;<span data-hover=".xst_product_model" data-toggle="tooltip" data-placement="right" title="Модель товара - этот тег указываем в настройках">code</span>&gt;3323680&lt;/code&gt;
        &lt;<span data-hover=".xst_product_attributes" data-toggle="tooltip" data-placement="right" title="Атрибуты - этот тег указываем в настройках в виде param @name это означает что само значение у нас в теге param а название характеристики в атрибуте name тега param">param</span> name="Верхний диаметр посуды"&gt;20 (см)&lt;/param&gt;
        &lt;param <span data-hover=".xst_product_attributes" data-toggle="tooltip" data-placement="right" title="Атрибуты - этот тег указываем в настройках в виде param @name это означает что само значение у нас в теге param а название характеристики в атрибуте name тега param">name</span>="Материал ручек"&gt;Бакелит&lt;/param&gt;
        &lt;param name="Многослойное дно"&gt;да&lt;/param&gt;
        &lt;param name="Форма"&gt;Круглая&lt;/param&gt;
      &lt;/offer&gt;
    &lt;/offers&gt;
  &lt;/shop&gt;
&lt;/yml_catalog&gt;
<?php } ?>

<?php if(isset($import_id)){ //настройки импорта ?>
  <div class="row" style="margin-top:5px;" id="setting_price_item">
    <div class="col-sm-6">
      <div class="price_edit_block"><input type="text" class="form-control1" value="<?php echo $price_name; ?>" name="price_name" placeholde="Название прайса"></div>
      <div class="price_edit_block"><input type="text" class="form-control1" value="<?php echo $price_comment; ?>" name="price_comment" placeholde="Комментарий"></div>
    </div>
    <div class="col-sm-6" style="padding-left:0;">
      <div class="price_file_block">
        <input type="text" class="form-control1" value="<?php echo $price_file; ?>" name="price_file" placeholde="Файл или ссылка XML">
        <button id="button-upload-popup" style="position:absolute;right:15px;top:2px;" class="btn btn-info" title="Выбрать файл" data-toggle="tooltip"><i class="fa fa-upload" aria-hidden="true"></i></button>
      </div>
      <div class="row">
        <div class="col-sm-3"><input type="text" name="login" id="price_login" placeholder="Логин" value="<?php echo $login; ?>" class="form-control" style="margin-top:4px;"></div>
        <div class="col-sm-3"><input type="text" name="pass" id="price_pass" placeholder="Пароль" value="<?php echo $pass; ?>" class="form-control" style="margin-top:4px;"></div>
        <div class="col-sm-2"><input type="text" name="import_limit" id="import_limit" title="Количество за раз" placeholder="Лимит за раз" value="<?php echo $import_limit; ?>" class="form-control" style="margin-top:4px;"></div>
        <div class="col-sm-4">
          <span class="import_read_xml" id="import_read_xml" data-toggle="tooltip" title="Не рекомендуется для больших прайсов!">Прочитать структуру xml</span>
        </div>
      </div>
    </div>
  </div>
  <hr style="margin:15px -15px;border-color:#777;">
  <div class="import-item-setting">
    <div class="row">

      <div class="col-sm-6">
        <h4><strong>Настройки XML файла</strong></h4>
        Для настройки смотрите структуру файла XML
        <div class="form-horizontal import_setting_block">
          <input type="hidden" value="<?php echo $import_id; ?>" name="unixml_import_id">
          <div class="form-group xst_root">
            <label class="col-sm-5 control-label text-right">Тег каталога</label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_root; ?>" type="text" name="unixml_import_xml_root" class="form-control text-center" placeholder="shop, catalog и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_categories" style="border-top: 1px solid #aaa;background:#fcfcfc;">
            <span class="load_item_set" data-set="nupd" data-item="categories" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Тег категорий</label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_categories; ?>" type="text" name="unixml_import_xml_categories" class="form-control text-center" placeholder="categories и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_category" style="background:#fcfcfc;">
            <span class="load_item_set" data-set="status,top" data-item="category" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Тег категории</label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_category; ?>" type="text" name="unixml_import_xml_category" class="form-control text-center" placeholder="category и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_category_id" style="background:#fcfcfc;">
            <label class="col-sm-5 control-label text-right">id категории</label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_category_id; ?>" type="text" name="unixml_import_xml_category_id" class="form-control text-center" placeholder="@id">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_category_parent_id" style="background:#fcfcfc;">
            <label class="col-sm-5 control-label text-right">id родителя</label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_category_parent_id; ?>" type="text" name="unixml_import_xml_category_parent_id" class="form-control text-center" placeholder="@parentId">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_category_name" style="background:#fcfcfc;">
            <span class="load_item_set" data-set="url,category_replace_name" data-item="category_name" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Название категории</label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_category_name; ?>" type="text" name="unixml_import_xml_category_name" class="form-control text-center">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_products" style="border-top: 1px solid #aaa;">
            <span class="load_item_set" data-set="nadd,prodis,proqua" data-item="products" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Тег товаров</label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_products; ?>" type="text" name="unixml_import_xml_products" class="form-control text-center" placeholder="offers и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product">
            <span class="load_item_set" data-set="status" data-item="product" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Тег товара</label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product; ?>" type="text" name="unixml_import_xml_product" class="form-control text-center" placeholder="offer и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_id">
            <span class="load_item_set" data-set="url,link,to,stop" data-item="product_id" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">id товара<small>p.product_id</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_id; ?>" type="text" name="unixml_import_xml_product_id" class="form-control text-center" placeholder="@id">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_name">
            <span class="load_item_set" data-set="url,nupd,link,tpl,to,replace,stop" data-item="product_name" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Название товара<small>pd.name</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_name; ?>" type="text" name="unixml_import_xml_product_name" class="form-control text-center" placeholder="name">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_model">
            <span class="load_item_set" data-set="url,nupd,link,calc,tpl,to,replace,stop" data-item="product_model" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Модель товара<small>p.model</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_model; ?>" type="text" name="unixml_import_xml_product_model" class="form-control text-center" placeholder="code model и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_sku">
            <span class="load_item_set" data-set="url,nupd,link,calc,tpl,to,replace" data-item="product_sku" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Артикул товара<small>p.sku</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_sku; ?>" type="text" name="unixml_import_xml_product_sku" class="form-control text-center" placeholder="sku vendorCode и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_manufacturer">
            <span class="load_item_set" data-set="nupd,to,replace_manufacturer" data-item="product_manufacturer" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Производитель<small>p.manufacturer_id</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_manufacturer; ?>" type="text" name="unixml_import_xml_product_manufacturer" class="form-control text-center" placeholder="vendor manufacturer и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_description">
            <span class="load_item_set" data-set="nupd,tpl,to,replace" data-item="product_description" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Описание<small>pd.description</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_description; ?>" type="text" name="unixml_import_xml_product_description" class="form-control text-center" placeholder="description и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_price">
            <span class="load_item_set" data-set="nupd,calc,tpl,price_filter,to,replace" data-item="product_price" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Цена товара<small>p.price</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_price; ?>" type="text" name="unixml_import_xml_product_price" class="form-control text-center" placeholder="price priceUSD и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_special">
            <span class="load_item_set" data-set="special,calc,tpl,to,replace" data-item="product_special" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><span title="Указывайте именно тег старой цены. Когда акция то в теге price идет скидка а в этом поле старая цена товара. Надо указать именно на это поле. UniMXL цену загрузит в скидку а старую цену, тег которой здесь указан, загрузит в цену." data-toggle="tooltip">Старая цена акции</span><small>ps.special</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo isset($unixml_import_xml_product_special)?$unixml_import_xml_product_special:''; ?>" type="text" name="unixml_import_xml_product_special" class="form-control text-center" placeholder="price если цена price_old, price_promo, special и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_quantity">
            <span class="load_item_set" data-set="nupd,calc,tpl,to,replace,sip" data-item="product_quantity" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Наличие/Кол-во<small>p.quantity</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_quantity; ?>" type="text" name="unixml_import_xml_product_quantity" class="form-control text-center" placeholder="@available quantity и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_category_id">
            <span class="load_item_set" data-set="nupd,tpl,replace_category" data-item="product_category_id" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">id категории товара<small>p2c.category_id</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_category_id; ?>" type="text" name="unixml_import_xml_product_category_id" class="form-control text-center" placeholder="categoryId category и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_image">
            <span class="load_item_set" data-set="nupd,replace,image,tpl,to" data-item="product_image" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Главное фото<small>p.image</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_image; ?>" type="text" name="unixml_import_xml_product_image" class="form-control text-center" placeholder="picture image и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_images">
            <span class="load_item_set" data-set="nupd,replace,image" data-item="product_images" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Остальные фото<small>pi.images</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_images; ?>" type="text" name="unixml_import_xml_product_images" class="form-control text-center" placeholder="picture image и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_attributes">
            <span class="load_item_set" data-set="nupd,attr,replace,replace_attribute" data-item="product_attributes" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Атрибуты<small>pa.attributes</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_attributes; ?>" type="text" name="unixml_import_xml_product_attributes" class="form-control text-center" placeholder="param @name и т.п.">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_options">
            <span class="load_item_set" data-set="nupd,replace_option,replace_option_value" data-item="product_options" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right">Опции<small>группировка разновидностей в опции</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_options; ?>" type="text" name="unixml_import_xml_product_options" class="form-control text-center" placeholder="@group_id">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>



          <div class="form-group xst_product_multilang">
            <div class="col-sm-12 table-responsive" style="margin-top:10px;">
              <b>Добавить фиды для других языков</b>
              <table id="lang_row_table" class="table table-striped table-bordered table-hover">
                <thead>
                  <tr>
                    <td class="text-left" style="width: 65%;">Ссылка на фид</td>
                    <td class="text-left" style="width: 25%;">Язык</td>
                    <td></td>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($unixml_import_xml_product_multilang as $lang_row => $row) { ?>
                    <tr id="lang_row<?php echo $lang_row; ?>">
                      <td class="text-left" style="width: 65%;">
                        <input type="text" name="unixml_import_xml_product_multilang[<?php echo $lang_row; ?>][file]" value="<?php echo $row['file']; ?>" placeholder="Ссылка на фид" class="form-control" />
                      </td>
                      <td class="text-left" style="width: 25%;">
                        <select name="unixml_import_xml_product_multilang[<?php echo $lang_row; ?>][lang]" class="form-control">
                          <?php foreach($languages as $lang){ ?>
                            <option value="<?php echo $lang['language_id']; ?>" <?php if($lang['language_id'] == $row['lang']){ ?>selected="selected"<?php } ?>><?php echo $lang['name']; ?></option>
                          <?php } ?>
                        </select>
                      </td>
                      <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="Удалить соответствие" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                    </tr>
                    <?php $lang_row++; ?>
                  <?php } ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="2"></td>
                    <td class="text-center"><button type="button" onclick="addLangRow();" data-toggle="tooltip" title="Добавить" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <input type="hidden" id="lang_row" value="<?php echo $lang_row; ?>" name="lang_row">

            <script id="example_lang_row" type="text/x-handlebars-template">
              <tr id="lang_row777888777">
                <td class="text-left" style="width: 65%;">
                  <input type="text" name="unixml_import_xml_product_multilang[777888777][file]" value="" placeholder="Ссылка на фид" class="form-control" />
                </td>
                <td class="text-left" style="width: 25%;">
                  <select name="unixml_import_xml_product_multilang[777888777][lang]" class="form-control">
                    <?php foreach($languages as $lang){ ?>
                      <option value="<?php echo $lang['language_id']; ?>"><?php echo $lang['name']; ?></option>
                    <?php } ?>
                  </select>
                </td>
                <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="Удалить" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
              </tr>
            </script>
          </div>



          <div class="form-group xst_product_custom_before">
            <label class="col-sm-12">Кастомный код ДО импорта. (<a href="https://unixml.pro/set/import/product_custom_before" target="_blank">Документация в новом окне</a>)</label>
            <div class="col-sm-12">
              <div class="input-group w200">
                <span class="input-group-addon">&lt;?php</span>
                <textarea style="min-height:200px;" name="unixml_import_xml_product_custom_before" class="form-control text-left" placeholder="Синтаксис php - выполняется до импорта"><?php echo $unixml_import_xml_product_custom_before; ?></textarea>
                <span class="input-group-addon">?&gt;</span>
              </div>
            </div>
          </div>

          <div class="form-group xst_product_custom">
            <label class="col-sm-12">Кастомный код при обходе товара. (<a href="https://unixml.pro/set/import/product_custom" target="_blank">Документация в новом окне</a>)</label>
            <div class="col-sm-12">
              <div class="input-group w200">
                <span class="input-group-addon">&lt;?php</span>
                <textarea style="min-height:200px;" name="unixml_import_xml_product_custom" class="form-control text-left" placeholder="Синтаксис php - выполняется в цикле обхода товаров прайса"><?php echo $unixml_import_xml_product_custom; ?></textarea>
                <span class="input-group-addon">?&gt;</span>
              </div>
            </div>
          </div>

          <div class="form-group xst_product_custom_after">
            <label class="col-sm-12">Кастомный код ПОСЛЕ импорта. (<a href="https://unixml.pro/set/import/product_custom_after" target="_blank">Документация в новом окне</a>)</label>
            <div class="col-sm-12">
              <div class="input-group w200">
                <span class="input-group-addon">&lt;?php</span>
                <textarea style="min-height:200px;" name="unixml_import_xml_product_custom_after" class="form-control text-left" placeholder="Синтаксис php - выполняется до импорта"><?php echo $unixml_import_xml_product_custom_after; ?></textarea>
                <span class="input-group-addon">?&gt;</span>
              </div>
            </div>
          </div>

        </div>

      </div>

      <div class="col-sm-6" style="padding-left:0;">
        <h4 class="h4_div h4_div_frst"><strong>Пример структуры XML файла</strong> <span>-</span></h4>
        <div>
          <div style="margin:9px 0 0px;">Для справки наводите мышкой на выделенные области</div>
          <div class="import_price_info">
            <pre class="xmlex"><?php echo $xmlex; ?></pre>
          </div>
        </div>
        <div id="xml_res"></div>
      </div>

    </div>
  </div>
  <script>
    title_html = 'Редaктировать настройки выгрузки <strong>#<?php echo $import_id; ?></strong>';
    <?php if(isset($date_edit)){ ?>
      title_html += '<span style="font-size:13px;position: absolute;right: 40px;top: 7px;color: #ccc;"><strong style="font-size:10px;font-weight:400;">Последние изменения</strong><br><?php echo $date_edit; ?></span>';
      $('#price_settingTitle').html(title_html);
    <?php } ?>

    $('.xmlex span[data-toggle="tooltip"]').hover(function(){
      $($(this).data('hover')).addClass('hovered');
      $($(this).data('hover')).find('input').focus();
    }, function(){
      $('.import_setting_block .form-group').removeClass('hovered');
      $('.import_setting_block .form-group input').focusout();
    });

    $('#import_cron_key').text('<?php echo $cron_link; ?>');
  </script>
<?php } ?>

<?php if(isset($setting_item)){ //настройки поля ?>
  <div id="setting_item">
    <script>
      $(document).ready(function(){
        $('[data-toggle="popover"]').popover({
          placement : 'right',
          html : true
        });
        //автовысота поля ввода
        function fixTextareaSize(textarea) {
          textarea.style.height = 'auto'
          textarea.style.height = textarea.scrollHeight + 2 + "px"
        }

        $('textarea').on('input', function (e) {
          fixTextareaSize(e.target);
        });

        $('textarea').each(function(e) {
          fixTextareaSize(this);
        });
        //автовысота поля ввода
      });
    </script>
    <h3 class="text-center">Настройки для <b><?php echo $setting_item; ?></b></h3>
    <hr style="margin-bottom:4px;">
    <input type="hidden" value="<?php echo $id; ?>" name="id">
    <input type="hidden" value="<?php echo $item; ?>" name="item">
    <?php if($sip){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4">Статус от наличиия</label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($sip_value){ ?>checked="checked"<?php } ?> class="checkbox_exp cb_green" id="nv2" name="sip_value" value="1">
              <label for="nv2" class="bnv green">Нет в наличии выключаем</label>
              <span class="input-group-addon" data-toggle="popover" title="Статус от наличиия" data-content="Если включена эа настройка то все товары в наличии включаются а все что не в наличии выключаются.">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($url){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4">ЧПУ c этого поля</label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($url_value){ ?>checked="checked"<?php } ?> class="checkbox_exp cb_green" id="nv2" name="url_value" value="1">
              <label for="nv2" class="bnv green">ЧПУ генерируется</label>
              <span class="input-group-addon" data-toggle="popover" title="Генерация ЧПУ из поля" data-content="Если включить генерацию ЧПУ исходя из поля <?php echo $setting_item; ?> то ЧПУ будет транслитерированным значением этого поля.">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($status){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4">Статус выключено у новых</label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($status_value){ ?>checked="checked"<?php } ?> class="checkbox_exp" id="nv1" name="status_value" value="1">
              <label for="nv1" class="bnv"><?php echo $setting_item; ?> включен</label>
              <span class="input-group-addon" data-toggle="popover" title="Статус выключить для новых" data-content="По умолчанию товары те что загружаются как новые в магазин имеют статус включен. В этой настройке можно поставить всем товарам статус выключено. Это полезно например для загрузки товаров на сайт и последующей их модерацией перед публикацией на сайте">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($top){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4">Топ категории в меню</label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($top_value){ ?>checked="checked"<?php } ?> class="checkbox_exp cb_green" id="nv6" name="top_value" value="1">
              <label for="nv6" class="bnv green">топ в главном меню</label>
              <span class="input-group-addon" data-toggle="popover" title="Топ категории в меню" data-content="Категории топ уровня выводить автоматически в меню. Ставить галочку Главное меню во вкладке Данные формы редактирования категории">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($nadd){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4">Не добавлять новые</label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($nadd_value){ ?>checked="checked"<?php } ?> class="checkbox_exp" id="nv" name="nadd_value" value="1">
              <label for="nv" class="bnv">добавляются</label>
              <span class="input-group-addon" data-toggle="popover" title="Не добавлять новые товары" data-content="Если включена эта опция новые товары не будут добавляться а те что уже есть будут обновляться с учетом настроек полей для обновления">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($prodis){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4">Товары поставщика которых нет в фиде - выключать</label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($prodis_value){ ?>checked="checked"<?php } ?> class="checkbox_exp" id="prodis" name="prodis_value" value="1">
              <label for="prodis" class="bnv">не выключаются</label>
              <span class="input-group-addon" data-toggle="popover" title="Не найденные выключать" data-content="Если включена эта опция то UniXML перед импортом выключает все товары загруженные ранее из этого прайса а те что есть в прайсе включает.<br>Иными словами товары которые были ранее но сейчас нет будут выключены в магазине">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($proqua){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4">Товары поставщика которых нет в фиде - количество 0</label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($proqua_value){ ?>checked="checked"<?php } ?> class="checkbox_exp" id="proqua" name="proqua_value" value="1">
              <label for="proqua" class="bnv green">нет в фиде - не трогаем остатки</label>
              <span class="input-group-addon" data-toggle="popover" title="Не найденные - количество 0" data-content="Если включена эта опция то UniXML перед импортом ставит всем товарам этого поставщика количество 0, а те что есть в прайсе обновляет на то количество что есть в фиде.<br>Иными словами товары которые были ранее, но сейчас нет - будут в остатке 0">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($nupd){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4">Запретить обновлять </label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($nupd_value){ ?>checked="checked"<?php } ?> class="checkbox_exp" id="nv" name="nupd_value" value="1">
              <label for="nv" class="bnv">обновляется</label>
              <span class="input-group-addon" data-toggle="popover" title="Запрет обновления" data-content="Если запрет обновления стоит для массива данных такие как категории, атрибуты и т.п. новые данные будут добавляться а те что есть не будут обновляться.<hr>Если стоит запрет обновления для категорий новые категории не будут создаваться а все новые товары будут заходить в папку UniXML - новые товары">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($special){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4">Поменять местами поля</label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($special_value){ ?>checked="checked"<?php } ?> class="checkbox_exp cb_green" id="nv5" name="special_value" value="1">
              <label for="nv5" class="bnv green">Это поле грузим в special</label>
              <span class="input-group-addon" data-toggle="popover" title="Загружать price в цену" data-content="Если включить замену price/special то это поле будет выгружаться в поле special то есть акционная цена. По умолчанию из-за структуры XML цены меняются местами">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($link){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label">Связующий ключ по полю</label>
          <div class="col-sm-7">
            <div class="input-group">
              <input value="<?php echo $link_value; ?>" type="text" name="link_value" class="form-control">
              <span class="input-group-addon" data-toggle="popover" title="Связующий ключ по полю" data-content="<?php echo $link_help; ?>">?</span>
            </div>
            <small style="position:absolute;color:#856404;font-size:10px;">Важный параметр, будьте внимательны!</small>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($to){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label">Загрузить значение и в поля</label>
          <div class="col-sm-7">
            <div class="input-group">
              <input value="<?php echo $to_value; ?>" type="text" name="to_value" class="form-control" placeholder="pd.meta_keyword, p.model, p.sku">
              <span class="input-group-addon" data-toggle="popover" title="Загрузить значение в поле" data-content="С помощью этой настройки можно указать в какое поле товара также загрузить значение <?php echo $setting_item; ?> из XML файла.<br>Например если указать здесь p.sku то UniXML запишет <?php echo $setting_item; ?> в sku товара<hr>Если в этой настройке задано поле оно запишется в любом случае, даже если в настройках этого поля стоит не обновлять. Также значение которое идет по умолчанию будет перезаписано. Например, если здесь прописать p.model то в модель товара зайдет именно это значение.<hr>Значение можно прописать через разделитель запятую и тогда будет загружаться в те поля что указаны">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($attr){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label text-right" style="padding-top:0px;">Загружать новые атрибуты в группу</label>
          <div class="col-sm-7">
            <div class="input-group w200">
              <select name="attr_value" class="form-control" style="width:100%;">
                <?php foreach($attribute_groups as $attribute_group){ ?>
                  <?php if($attribute_group['attribute_group_id'] == $attr_value){ ?>
                    <option value="<?php echo $attribute_group['attribute_group_id']; ?>" selected="selected"><?php echo $attribute_group['name']; ?></option>
                  <?php }else{ ?>
                    <option value="<?php echo $attribute_group['attribute_group_id']; ?>"><?php echo $attribute_group['name']; ?></option>
                  <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($calc){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label">Калькуляция</label>
          <div class="col-sm-7">
            <div class="input-group">
              <input value="<?php echo $calc_value; ?>" type="text" name="calc_value" class="form-control" placeholder="+20%, -20%, +100, -200, *3, /3">
              <span class="input-group-addon" data-toggle="popover" title="Наценка/скидка" data-content="Вы можете прописать любое действие с числом для корректировки значения, например +20%, -20%, +100, -200, *3, /3">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($price_filter){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label">НЕ ЗАГРУЖАТЬ товар если цена</label>
          <div class="col-sm-7">
            <div class="input-group">
              <input value="<?php echo $price_filter_value; ?>" type="text" name="price_filter_value" class="form-control" placeholder="=500 или >500 или <500">
              <span class="input-group-addon" data-toggle="popover" title="Шаблон генерации значения" data-content="Если указать =500 то с ценой 500 товар не будет загружаться. Если <500 то товар дешевле 500 не загружается. Если >500 то товар дороже 500 не загружается.">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($tpl){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label">Шаблон генерации значения</label>
          <div class="col-sm-7">
            <div class="input-group">
              <input value="<?php echo $tpl_value; ?>" type="text" name="tpl_value" class="form-control" placeholder="{{name}} {{model}}">
              <span class="input-group-addon" data-toggle="popover" title="Шаблон генерации значения" data-content="Вы можете прописать генерацию значения вставляя переменные описанные ниже. Доступные переменны товара:<br>name<br>model<br>sku<br>description<br>manufacturer<br>И другие из массива товара">?</span>
            </div>
          </div>
        </div>
        <div class="helper" style="margin-bottom:15px;padding:10px;border:1px solid #eee;background:#fafafa;">
          <strong>Можно использовать:</strong>
          <div><span class="fw600" data-toggle="tooltip" title="Можно использовать ключи массива товара">{{***}}</span> - переменная товара</div>
          <div><span class="fw600">{{RAND,10000-90000}}</span> -  случайное число, 10000-90000 изменяемый диапазон</div>
          <div><span class="fw600">{{NUMB}}</span> -  порядковый номер товара в XML файле</div>
        </div>
      </div>
    <?php } ?>
    <?php if($replace){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="row">
            <div class="col-sm-6" style="padding-right:7px;">
              <div class="input-group">
                <span class="input-group-addon" title="Может быть много значений. Разделитель запятая. Если в этом поле написать 1,2,3 а в поле на что один, два, три то будет соответствующая замена. Здесь важна последовательность. В какой последовательности введено, в такой и будет работать замена">Что меняем</span>
                <input value="<?php echo isset($replace_value_from)?$replace_value_from:''; ?>" type="text" name="replace_value_from" class="form-control" placeholder="">
              </div>
            </div>
            <div class="col-sm-6" style="padding-left:7px;">
              <div class="input-group">
                <span class="input-group-addon" title="Может быть много значений. Разделитель запятая. Если в этом поле написать один, два, три а в поле что 1, 2, 3 то будет соответствующая замена. Здесь важна последовательность. В какой последовательности введено, в такой и будет работать замена">На что меняем</span>
                <input value="<?php echo isset($replace_value_to)?$replace_value_to:''; ?>" type="text" name="replace_value_to" class="form-control" placeholder="">
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($category_replace_name){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="row">
            <div class="col-sm-12"><h3>Переименование категорий</h3></div>
          </div>
          <div class="row">
            <div class="col-sm-6" style="padding-right:7px;">
              <strong>Категории в XML</strong>
              <textarea style="min-height:100px;" name="category_replace_name_value_from" class="form-control"><?php echo isset($category_replace_name_value_from)?$category_replace_name_value_from:''; ?></textarea>
            </div>
            <div class="col-sm-6" style="padding-left:7px;">
              <strong>Переименуем в</strong>
              <textarea style="min-height:100px;" name="category_replace_name_value_to" class="form-control"><?php echo isset($category_replace_name_value_to)?$category_replace_name_value_to:''; ?></textarea>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              Каждая категория с новой строки. Заменяется построчно.<br>Категория с первой строки заменяется на категорию справа с первой строки.<br>Строка 10 заменяется на 10 строку справа, и т.д. по аналогии.
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($replace_option){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="row">
            <div class="col-sm-12"><h3>Группировать по опциям (<a href="https://unixml.pro/set/import/product_options" target="_blank">Инструкция</a>)</h3></div>
          </div>
          <div class="row">
            <div class="col-sm-6" style="padding-right:7px;">
              <strong>Атрибуты для группировки в XML</strong>
              <textarea style="min-height:100px;" name="replace_option_value_from" class="form-control"><?php echo isset($replace_option_value_from)?$replace_option_value_from:''; ?></textarea>
            </div>
            <div class="col-sm-6" style="padding-left:7px;">
              <strong>Переносим в опции</strong>
              <textarea style="min-height:100px;" name="replace_option_value_to" class="form-control"><?php echo isset($replace_option_value_to)?$replace_option_value_to:''; ?></textarea>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              Каждая опция с новой строки. Работает построчно.<br>Атрибут с первой строки загружается в опцию справа с первой строки.<br>Строка 10 в 10 строку справа, и т.д. по аналогии.
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($replace_option_value){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="row">
            <div class="col-sm-12"><h3>Соответствие значений опций</h3></div>
          </div>
          <div class="row">
            <div class="col-sm-6" style="padding-right:7px;">
              <strong>Значение опции в XML</strong>
              <textarea style="min-height:100px;" name="replace_option_value_value_from" class="form-control"><?php echo isset($replace_option_value_value_from)?$replace_option_value_value_from:''; ?></textarea>
            </div>
            <div class="col-sm-6" style="padding-left:7px;">
              <strong>Соответствует опции в магазине</strong>
              <textarea style="min-height:100px;" name="replace_option_value_value_to" class="form-control"><?php echo isset($replace_option_value_value_to)?$replace_option_value_value_to:''; ?></textarea>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              Каждое значение с новой строки. Работает построчно.<br>Опция с первой строки заменяется на опцию справа с первой строки.<br>Строка 10 в 10 строку справа, и т.д. по аналогии.
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($replace_manufacturer){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="col-sm-121 table-responsive">
            <h3>Соответствия производителей</h3>
            <table id="replace_manufacturer" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <td class="text-left">Производитель в XML</td>
                  <td class="text-left">Производитель в Opencart</td>
                  <td></td>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($replace_manufacturer_list as $row_key => $row) { ?>
                  <tr id="manufacturer_match_row<?php echo $row_key; ?>">
                    <td class="text-left" style="width: 45%;">
                      <input type="text" name="replace_manufacturer[<?php echo $row_key; ?>][xml]" value="<?php echo $row['xml']; ?>" placeholder="Производитель из XML" class="form-control" />
                    </td>
                    <td class="text-left" style="width: 45%;">
                      <select name="replace_manufacturer[<?php echo $row_key; ?>][oc]" class="form-control">
                        <option value="0" <?php if($row['oc'] == '0'){ ?>selected="selected"<?php } ?>>НЕ ЗАГРУЖАТЬ товары этого бренда</option>
                        <?php foreach($manufacturers as $manufacturer){ ?>
                          <option value="<?php echo $manufacturer['manufacturer_id']; ?>" <?php if($manufacturer['manufacturer_id'] == $row['oc']){ ?>selected="selected"<?php } ?>><?php echo $manufacturer['name']; ?></option>
                        <?php } ?>
                      </select>
                    </td>
                    <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="Удалить соответствие" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                  </tr>
                  <?php $row_key++; ?>
                <?php } ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2"></td>
                  <td class="text-center"><button type="button" onclick="addImportManufacturerMatch();" data-toggle="tooltip" title="Добавить" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                </tr>
              </tfoot>
            </table>
            <p>Обратите внимание, если не задано соответствие то бренд загружается в оригинале</p>
          </div>

          <input type="hidden" id="manufacturer_match_row" value="<?php echo $row_key; ?>" name="manufacturer_match_row">

          <script id="example_row_manufacturer" type="text/x-handlebars-template">
            <tr id="manufacturer_match_row777888777">
              <td class="text-left" style="width: 45%;">
                <input type="text" name="replace_manufacturer[777888777][xml]" value="" placeholder="Производитель из XML" class="form-control" />
              </td>
              <td class="text-left" style="width: 45%;">
                <select name="replace_manufacturer[777888777][oc]" class="form-control">
                  <option value="0">НЕ ЗАГРУЖАТЬ товары этого бренда</option>
                  <?php foreach($manufacturers as $manufacturer){ ?>
                    <option value="<?php echo $manufacturer['manufacturer_id']; ?>"><?php echo $manufacturer['name']; ?></option>
                  <?php } ?>
                </select>
              </td>
              <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="Удалить соответствие" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
            </tr>
          </script>

        </div>
      </div>
    <?php } ?>
    <?php if($replace_attribute){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="col-sm-121 table-responsive">
            <h3>Соответствия атрибутов</h3>
            <table id="replace_attribute" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <td class="text-left">Атрибут в XML</td>
                  <td class="text-left">Атрибут в Opencart</td>
                  <td></td>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($replace_attribute_list as $row_key => $row) { ?>
                  <tr id="attribute_match_row<?php echo $row_key; ?>">
                    <td class="text-left" style="width: 45%;">
                      <input type="text" name="replace_attribute[<?php echo $row_key; ?>][xml]" value="<?php echo $row['xml']; ?>" placeholder="Атрибут из XML" class="form-control" />
                    </td>
                    <td class="text-left" style="width: 45%;">
                      <select name="replace_attribute[<?php echo $row_key; ?>][oc]" class="form-control">
                        <option value="-1" <?php if($row['oc'] == '-1'){ ?>selected="selected"<?php } ?>>НЕ ЗАГРУЖАТЬ этот атрибут</option>
                        <option value="0" <?php if($row['oc'] == '0'){ ?>selected="selected"<?php } ?>>НЕ ЗАГРУЖАТЬ товары которые имеют этот атрибут</option>
                        <?php foreach($attributes as $attribute){ ?>
                          <option value="<?php echo $attribute['attribute_id']; ?>" <?php if($attribute['attribute_id'] == $row['oc']){ ?>selected="selected"<?php } ?>><?php echo $attribute['name']; ?></option>
                        <?php } ?>
                      </select>
                    </td>
                    <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="Удалить соответствие" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                  </tr>
                  <?php $row_key++; ?>
                <?php } ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2"></td>
                  <td class="text-center"><button type="button" onclick="addImportAttributeMatch();" data-toggle="tooltip" title="Добавить" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                </tr>
              </tfoot>
            </table>
            <p>Обратите внимание, если не задано соответствие то атрибут загружается в оригинале</p>
          </div>

          <input type="hidden" id="attribute_match_row" value="<?php echo $row_key; ?>" name="attribute_match_row">

          <script id="example_row_attribute" type="text/x-handlebars-template">
            <tr id="attribute_match_row777888777">
              <td class="text-left" style="width: 45%;">
                <input type="text" name="replace_attribute[777888777][xml]" value="" placeholder="Атрибут из XML" class="form-control" />
              </td>
              <td class="text-left" style="width: 45%;">
                <select name="replace_attribute[777888777][oc]" class="form-control">
                  <option value="-1">НЕ ЗАГРУЖАТЬ этот атрибут</option>
                  <option value="0">НЕ ЗАГРУЖАТЬ товары которые имеют этот атрибут</option>
                  <?php foreach($attributes as $attribute){ ?>
                    <option value="<?php echo $attribute['attribute_id']; ?>"><?php echo $attribute['name']; ?></option>
                  <?php } ?>
                </select>
              </td>
              <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="Удалить соответствие" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
            </tr>
          </script>

        </div>
      </div>
    <?php } ?>
    <?php if($replace_category){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="col-sm-121 table-responsive">
            <h3>Соответствия категорий</h3>
            <table id="replace_category" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <td class="text-left">Категория в XML</td>
                  <td class="text-left">Категория в Opencart</td>
                  <td></td>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($replace_category_list as $row_key => $row) { ?>
                  <tr id="category_match_row<?php echo $row_key; ?>">
                    <td class="text-left" style="width: 45%;">
                      <input type="text" name="replace_category[<?php echo $row_key; ?>][xml]" value="<?php echo $row['xml']; ?>" placeholder="Категория из XML" class="form-control" />
                    </td>
                    <td class="text-left" style="width: 45%;">
                      <input type="text" name="replace_category[<?php echo $row_key; ?>][ocname]" value="<?php echo $row['ocname']; ?>" placeholder="Категория из Opencart" class="form-control" />
                      <input type="hidden" name="replace_category[<?php echo $row_key; ?>][oc]" value="<?php echo $row['oc']; ?>" />
                    </td>
                    <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="Удалить соответствие" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                  </tr>
                  <?php $row_key++; ?>
                <?php } ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2"></td>
                  <td class="text-center"><button type="button" onclick="addImportCategoryMatch();" data-toggle="tooltip" title="Добавить" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                </tr>
              </tfoot>
            </table>
            <p>Обратите внимание, если не задано соответствие то категория загружается в оригинале</p>
          </div>

          <script>
            $('#replace_category tbody tr').each(function(index, element) {
              importcategoryautocomplete(index);
            });
          </script>

          <input type="hidden" id="category_match_row" value="<?php echo $row_key; ?>" name="category_match_row">

          <script id="example_row_category" type="text/x-handlebars-template">
            <tr id="category_match_row777888777">
              <td class="text-left" style="width: 45%;">
                <input type="text" name="replace_category[777888777][xml]" value="" placeholder="Категория из XML" class="form-control" />
              </td>
              <td class="text-left" style="width: 45%;">
                <input type="text" name="replace_category[777888777][ocname]" value="" placeholder="Категория из Opencart" class="form-control" />
                <input type="hidden" name="replace_category[777888777][oc]" value="" />
              </td>
              <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="Удалить соответствие" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
            </tr>
          </script>

        </div>
      </div>
    <?php } ?>
    <?php if($stop){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="col-sm-12">
            <h3>Запретить импорт</h3>
            <textarea type="text" name="stop_value" class="form-control" style="min-height:100px;" placeholder="Стоп лист. Каждое наименование с новой строки. Здесь указываем то что не надо загружать"><?php echo $stop_value; ?></textarea>
            <small>Стоп лист. Каждое наименование с новой строки. Здесь указываем то что не надо загружать</small>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
<?php } ?>

<?php if(isset($xml_orig)){ //загрузка оригинального XML из ссылки ?>
  <h4 class="h4_div"><strong>Содержимое XML файла</strong> <span>-</span></h4>
  <div>
    <div class="import_price_info">
      <pre class="xmlex"><?php echo $xml_orig; ?></pre>
    </div>
  </div>
<?php } ?>

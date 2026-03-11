<?php if (!$type_view){ ?><div class="close-cheaper" onclick="return closedivshadow<?php echo $module_id; ?>(); return false;"><svg class="svg-icon-cheaper"><use xlink:href="/catalog/view/javascript/cheaper30/icons.svg#svg-close"/></svg></div><?php } ?>
<div class="option-div option-div<?php echo $module_id; ?>">
  <div class="row">
	<div class="col-sm-12 col-xs-12"><h3 class="text-left"><?php echo $text_cheaper_h1; ?></h3></div>
	<?php if ($product_id){ ?>
		<div class="col-lg-4 col-md-4 col-sm-4 col-xs-3 col-xm-12">
			<div class="image">
				<?php if ($image){ ?><a href="<?php echo $href; ?>" title=""><img src="<?php echo $image; ?>" alt="" title="" id="images" class="img-responsive" /></a><?php } ?>
			</div>
		</div>
		<div class="col-lg-8 col-md-8 col-sm-8 col-xs-9 col-xm-12 border-right">
			<h4 class="text-left"><?php echo $text_cheaper_h4; ?></h4>
			<br />
			<h3><?php echo $name; ?></h3>
			<?php if ($price){ ?>
			<span class="price">
			  <ul class="list-unstyled text-left">
				<?php if ($special == false) { ?>
				<li>
				  <h2><?php echo $price; ?></h2>
				</li>
				<?php } else { ?>
				<li>
				  <h2><?php echo $special; ?></h2><span style="text-decoration: line-through;"><?php echo $price; ?></span>
				</li>
				<?php } ?>
				<?php if ($tax){ ?>
				<li><?php echo $text_tax; ?> <?php echo $tax; ?></li>
				<?php } ?>
			  </ul>
			</span>
			<?php } ?>
			<input type="hidden" id="price" value="<?php echo $pr; ?>" />
		</div>
	<?php } else { ?>
	<div class="col-sm-12 col-xs-12">
		<h4 class="text-left"><?php echo $text_cheaper_h4; ?></h4>
	</div>
	<?php } ?>
</div>
<!-- user comment ->>
<form class="" action="" method="post" id="cheaperForm<?php echo $module_id; ?>" data="1">
	<div class="option-div product">
		<div class="clearfix"></div>
		<div class="forming_quick col">
		  <div class="row">
			<input type="hidden" class="form-control" name="input_date" id="input_date" value="<?php echo $date; ?>" />
			<input type="hidden" name="input_product_id" id="input_product_id" value="<?php echo $product_id; ?>" />
			<?php if (isset($results)){ ?>
				<?php foreach ($results as $position => $result){ ?>
					<?php foreach ($result as $column => $fields){ ?>
						<?php if (count($result) == 1){ ?><?php $class_column = 'col-sm-12'; ?><?php } ?>
						<?php if (count($result) == 2){ ?><?php $class_column = 'col-sm-6'; ?><?php } ?>
						<?php if (count($result) == 3){ ?><?php $class_column = 'col-sm-4'; ?><?php } ?>
						<?php if (count($result) == 4){ ?><?php $class_column = 'col-sm-3'; ?><?php } ?>
						<div class="<?php echo $class_column; ?> <?php echo count($result); ?>">
						<?php foreach ($fields as $field){ ?>
							<div class="col-sm-12 form-group">
								<div class="input-group<?php if ($field['type'] == 'radio' or $field['type'] == 'checked'){ ?> border-block-module<?php } ?>">
									<?php if ($field['type'] == 'text'){ ?>
										<?php if ($field['icon']){ ?><span class="input-group-addon input_field<?php echo $field['id']; ?>"><i class="fa fa-<?php echo $field['icon']; ?>"></i></span><?php } ?>
										<input type="text" class="form-control<?php if ($field['regex'] == 'date'){ ?> date<?php } ?><?php if ($field['regex'] == 'time'){ ?> time<?php } ?><?php if ($field['regex'] == 'datetime'){ ?> datetime<?php } ?> form-required-module" name="input_field[<?php echo $field['id']; ?>]" id="input_field<?php echo $field['id']; ?>" placeholder="<?php echo $field['name']; ?><?php if ($field['required']){ ?><?php echo $text_cheaper30_required; ?><?php } ?>" value=""<?php if ($field['type'] == 'date'){ ?> data-date-format="DD/MM/YYYY"<?php } ?><?php if ($field['type'] == 'time'){ ?> data-time-format="HH/MM"<?php } ?><?php if ($field['type'] == 'datetime'){ ?> data-datetime-format="DD/MM/YYYY HH/MM"<?php } ?> />
									<?php } ?>
									<?php if ($field['type'] == 'textarea'){ ?>
										<?php if ($field['icon']){ ?><span class="input-group-addon input_field<?php echo $field['id']; ?>"><i class="fa fa-<?php echo $field['icon']; ?>"></i></span><?php } ?>
										<textarea type="text" class="form-control form-required-module" name="input_field[<?php echo $field['id']; ?>]" id="input_field<?php echo $field['id']; ?>" placeholder="<?php echo $field['name']; ?><?php if ($field['required']){ ?><?php echo $text_cheaper30_required; ?><?php } ?>"></textarea>
									<?php } ?>
									<?php if ($field['type'] == 'select'){ ?>
										<?php if ($field['query_value']){ ?>
										<div class="margin-bottom-module"><i class="fa fa-<?php echo $field['icon']; ?>"></i>&nbsp;&nbsp;<strong><?php echo $field['name']; ?><?php if ($field['required']){ ?><?php echo $text_cheaper30_required; ?><?php } ?></strong></div>
										<div class="">
											<select name="input_field[<?php echo $field['id']; ?>][]" class="form-control form-required-module">
												<option value=""> <?php echo $text_select; ?></option>
												<?php foreach ($field['query_value'] as $select_value){ ?>
													<option value="<?php echo $select_value['name']; ?>"<?php foreach ($select_value['value'] as $formula_id => $value){ ?> data-formula_<?php echo $formula_id; ?>="<?php echo $select_value['value'][$formula_id]; ?>"<?php } ?>> <?php echo $select_value['name']; ?></option>
												<?php } ?>
											</select>
										</div>
										<?php } ?>
									<?php } ?>
									<?php if ($field['type'] == 'radio'){ ?>
										<?php if ($field['query_value']){ ?>
										<div class="margin-bottom-module"><i class="fa fa-<?php echo $field['icon']; ?>"></i>&nbsp;&nbsp;<strong><?php echo $field['name']; ?><?php if ($field['required']){ ?><?php echo $text_cheaper30_required; ?><?php } ?></strong></div>
										<div class="">
											<?php foreach ($field['query_value'] as $select_value){ ?>
												<label class="text-left margin-right-module" data-class="checked-check<?php echo $field['id']; ?>"><input type="radio" name="input_field[<?php echo $field['id']; ?>][]" value="<?php echo $select_value['name']; ?>" class="form-required-module"<?php foreach ($select_value['value'] as $formula_id => $value){ ?> data-formula_<?php echo $formula_id; ?>="<?php echo $select_value['value'][$formula_id]; ?>"<?php } ?>> <?php echo $select_value['name']; ?></label>
											<?php } ?>
										</div>
										<?php } ?>
									<?php } ?>
									<?php if ($field['type'] == 'checked'){ ?>
										<?php if ($field['query_value']){ ?>
										<div class="margin-bottom-module"><i class="fa fa-<?php echo $field['icon']; ?>"></i>&nbsp;&nbsp;<strong><?php echo $field['name']; ?><?php if ($field['required']){ ?><?php echo $text_cheaper30_required; ?><?php } ?></strong></div>
										<div class="">
											<?php foreach ($field['query_value'] as $select_value){ ?>
												<label class="text-left margin-right-module" data-class="checked-check<?php echo $field['id']; ?>"><input type="checkbox" name="input_field[<?php echo $field['id']; ?>][]" value="<?php echo $select_value['name']; ?>" class="form-required-module"<?php foreach ($select_value['value'] as $formula_id => $value){ ?> data-formula_<?php echo $formula_id; ?>="<?php echo $select_value['value'][$formula_id]; ?>"<?php } ?>> <?php echo $select_value['name']; ?></label>
											<?php } ?>
										<?php } ?>
										</div>
									<?php } ?>
									<?php if ($field['type'] == 'file'){ ?>
										<div class="margin-bottom-module"><i class="fa fa-<?php echo $field['icon']; ?>"></i>&nbsp;&nbsp;<strong><?php echo $field['name']; ?><?php if ($field['required']){ ?><?php echo $text_cheaper30_required; ?><?php } ?></strong></div>
										<button type="button" data-module_id="<?php echo $module_id; ?>" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-default btn-block button-upload"><i class="fa fa-upload"></i> <?php echo $button_upload; ?></button>
										<input type="text" name="input_field[<?php echo $field['id']; ?>][file]" value="" id="input-file<?php echo $module_id; ?>" class="visibility_cheaper form-required-module" />
									<?php } ?>
								</div>
							</div>
						<?php } ?>
						</div>
					<?php } ?>
				<?php } ?>
			<?php } ?>
			<div class="clearfix"></div>
			<div class="col-sm-12 captcha-module<?php echo $module_id; ?>">
				<div class="input-group">
					<?php if ($captch == 'default'){ ?>
						<div class="form-group required marg-bottom-0">
							<label class="control-label display-block-module"><?php echo $entry_valid_default_legend; ?></label>
							<div class="input-group">
								<div id="captchaimage<?php echo $module_id; ?>" class="input-group-btn captchaimage"><a id="refreshimg" title="Click to refresh image"><img src="index.php?route=<?php echo $extension; ?>module/cheaper30/image/<?php echo $time; ?>&module_id=<?php echo $module_id; ?>" width="132" height="46" alt="Captcha image"></a></div>
								<input type="text" maxlength="6" name="captcha" id="captcha" class="form-control" style="height: 46px;" />
								<input type="hidden" name="submit" id="submit" value="Check" />
							</div>
						</div>
					<?php } else { ?>
						<?php echo $captcha; ?>
					<?php } ?>
				</div>
			</div>
			<div class="clearfix"></div>
			<?php if ($protection['text'] and $protection['text'] != ""){ ?>
				<div class="col-xs-12 col-sm-12 checkbox person<?php if ($protection['format'] == 'text'){ ?> text_person<?php } ?>">
					<?php if ($protection['format'] and $protection['format'] == 'checkbox'){ ?>
					  <div class="table">
						<div class="input-group-btn">
						  <input type="checkbox" name="input_zachita" id="zachita" value="1" />
						</div>
						<label for="zachita" class="table-cell-form-control"><span class="text_p"><?php echo $protection['text']; ?></span></label>
					  </div>
					<?php } ?>
					<?php if ($protection['format'] and $protection['format'] == 'text'){ ?>
					  <?php echo $protection['text']; ?>
					<?php } ?>
				</div><div class="clearfix"></div>
			<?php } ?>
			<div class="block-calculator ps-3 pe-3 mt-3"></div>
			<div class="col-lg-12 button-cheaper">
				<button type="submit" class="btn btn-primary col-xm-12 margin-bottom-xm" for="cheaperForm<?php echo $module_id; ?>"><i class="fa fa-envelope-o"></i>&nbsp;&nbsp;<span> <?php echo $text_send_cheaper30_module; ?></span></button>
				<?php if (!$type_view){ ?><button type="button" class="btn btn-white pull-right col-xm-12" onclick="return closedivshadow<?php echo $module_id; ?>(); return false;"><i class="fa fa-sign-out"></i><span>&nbsp;&nbsp;<?php echo $text_close_cheaper30_module; ?></span></button><?php } ?>
            </div><div class="clearfix"></div>
		  </div>
		</div>
	</div>
	<div id="temp_image" class="temp_image">
		<img data-image-src="catalog/view/javascript/cheaper30/success_module.gif" class="temp_image_success" />
	</div>
</form>
</div>
<script src="catalog/view/javascript/cheaper30/jsdelivr/jquery.validate.js" type="text/javascript"></script>
<script src="catalog/view/javascript/cheaper30/jsdelivr/additional-methods.js" type="text/javascript"></script>
<script src="catalog/view/javascript/jquery/datetimepicker/moment.js" type="text/javascript"></script>
<?php if (isset($session_language) && $session_language){ ?>
<!--<script src="catalog/view/javascript/jquery/datetimepicker/locale/<?php echo $session_language; ?>.js" type="text/javascript"></script>-->
<?php } ?>
<script src="catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<link href="catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet" media="screen" />
<script src="catalog/view/javascript/cheaper30/jsdelivr/jquery.mask.phone.js" type="text/javascript"></script>
<script><!--
$('.divshadow').addClass('divshadow<?php echo $module_id; ?>');
var divshadow<?php echo $module_id; ?> = '.divshadow.divshadow<?php echo $module_id; ?>';
<?php if ($type_view) { ?>
	var divshadow<?php echo $module_id; ?> = '.divshadowmodule.divshadowmodule<?php echo $module_id; ?>';
	$('.option-div<?php echo $module_id; ?>').wrap('<div class="divshadowmodule divshadowmodule<?php echo $module_id; ?>"></div>');
<?php } ?>
<?php if ($position == 'content_top' or $position == 'content_bottom') { ?>
	$(divshadow<?php echo $module_id; ?> + ' .col-block').removeAttr('class').addClass('col-sm-6 form-group col-block');
<?php } ?>
$('.divshadow .option-div').removeAttr('id');
$('.divshadow').addClass('<?php echo $theme; ?>');
function quick_pay_button<?php echo $module_id; ?>(){
	var href_itog = '';
	if (localStorage.getItem('href_itog')){
		href_itog = '&href=' + encodeURIComponent(localStorage.getItem('href_itog'));
	}
	var itog = '';
	if ($('.block-calculator').html() != undefined){
		$('.block-calculator > div').each(function(){
			var _this = $(this);
			_this.find('div').each(function(i,item){
				if (i == 1){
					itog += ' - ';
					itog += '/ss';
				}
				itog += $(this).text();
				if (i == 1){
					itog += '/n';
					itog += '/se';
				}
			});
		});
		itog = '&itog=' + itog;
	}
	var date = $('#input_date').val();
	var product_id = $('#input_product_id').val();
	var price = $('#price').val();
	var name_id ='<?=$name_id ?>';
    var phone_id ='<?=$phone_id ?>';

	data = $('.option-div<?php echo $module_id; ?> .forming_quick input[type=\'text\'], .option-div<?php echo $module_id; ?> .forming_quick textarea, .option-div<?php echo $module_id; ?> .forming_quick input[type=\'radio\']:checked, .option-div<?php echo $module_id; ?> .forming_quick input[type=\'checkbox\']:checked, .option-div<?php echo $module_id; ?> .forming_quick select, .option-div<?php echo $module_id; ?> .forming_quick input[type=\'hidden\']');
	$.ajax({
		url: 'index.php?route=<?php echo $extension; ?>module/cheaper30/quick',
		type: 'post',
		data: data.serialize() + '&name_id='+name_id+'&phone_id='+phone_id+'&date=' + date + '&prod_id=' + product_id + '&price=' + price + '&module_id=<?php echo $module_id; ?>' + href_itog + itog,
		dataType: 'json',
		beforeSend: function(){
			$(divshadow<?php echo $module_id; ?>).addClass('loading');
		},
		complete: function(json){
			$(divshadow<?php echo $module_id; ?>).removeClass('loading');
			<?php if ($captch == 'default'){ ?>
				captchaimage<?php echo $module_id; ?>();
			<?php } else { ?>
				<?php if ($captch == 'basic' or $captch == 'basic_captcha'){ ?>captchaThirdimage<?php echo $module_id; ?>();<?php } ?>
			<?php } ?>
		},
		success: function(json){
			
			$(divshadow<?php echo $module_id; ?> + ' > .option-div').append('<div id="window-cheaper"<?php if (!$type_view){ ?> class="popup_module"<?php } ?>></div>');
			
			if (json['message']){
				$('#window-cheaper').append('<img src="" class="hide_cheaper real_image_success img-responsive" />');
				$('#window-cheaper img.real_image_success').attr('src', $('#temp_image img.temp_image_success').attr('data-image-src')).removeClass('hide_cheaper');
			
				setTimeout(function(){
					<?php if (!$type_view){ ?>
						$(divshadow<?php echo $module_id; ?> + ' .option-div').css('height', '160px');
						$(divshadow<?php echo $module_id; ?> + ' .option-div > .row, ' + divshadow<?php echo $module_id; ?> + ' form').remove();
						centering(divshadow<?php echo $module_id; ?>);
					<?php } ?>
				},1000);
				if (json['message']['success_send']){
					setTimeout(function(){
						$('#window-cheaper').addClass('gif-background after-background<?php if ($position == "column_left" or $position == "column_right"){ ?> column-module<?php } ?>');
					},1000);
					setTimeout(function(){
						$('#window-cheaper').removeClass('gif-background');
						$('#window-cheaper .text-msg').addClass('text-anim');
						
					},3500);
					$(divshadow<?php echo $module_id; ?> + ' > .option-div #window-cheaper').append('<span class="text-msg">' + json['message']['success_send'] + '</span>');
					
					$('.divshadowmodule input[type=\'text\'],.divshadowmodule textarea').each(function(){
						$(this).val('').attr('value','');
					});
					$(divshadow<?php echo $module_id; ?> + ' .block-calculator').empty();
				}
				if (json['message']['error_send']){
					setTimeout(function(){
						$('#window-cheaper .text-msg').addClass('text-anim');
					},1000);
					$(divshadow<?php echo $module_id; ?> + ' > .option-div #window-cheaper').append('<span class="text-msg">' + json['message']['error_send'] + '</span>');
				}
				setTimeout(function(){
					$(divshadow<?php echo $module_id; ?> + ' #window-cheaper').remove();
					<?php if ($captch == 'basic' or $captch == 'basic_captcha'){ ?>captchaThirdimage<?php echo $module_id; ?>();<?php } ?>
					return closedivshadow<?php echo $module_id; ?>(); return false;
				},5000);
			}
			if (json['error']){
				$('.captcha-module<?php echo $module_id; ?>  > div').after('<label class="error-module">' + json['error'] + '</label>');
			}
		}
	});
	return false;
}
setTimeout(function(){
	$('.header-popup').addClass('show').addClass('animated').addClass('flipInX');
}, 1000);
//--></script>
<script>
	var class_bootstrap = 'col-lg-offset-4 col-lg-4 col-md-offset-3 col-md-6 col-sm-offset-2 col-sm-8 col-xs-offset-1 col-xs-10';
	<?php if ($class_bootstrap){ ?>
		class_bootstrap = '<?php echo $class_bootstrap; ?>';
	<?php } ?>
	function closedivshadow<?php echo $module_id; ?>(){
		$('body .divshadow').removeClass('animated').removeClass('bounceIn').addClass('animated bounceOut');
		setTimeout(function(){
			$('body .divshadow').empty();
			$('body .divshadow, body .modal-bg-cheaper').removeClass('show');
			$('.modal-bg').removeClass('show');
			$('body .divshadow').removeClass('cheaper30').removeClass('loading ' + class_bootstrap + ' divshadow<?php echo $module_id; ?>');
		},700);
	}
	$('body .divshadow .close').click(function() {
		closedivshadow<?php echo $module_id; ?>();
	});
	localStorage.setItem('swalCheaper', '1');
	$(document).keydown(function(e) {
		var container = $('body .divshadow.show');
		var containerfind = $('body').find('.divshadow.show').html();
		if (containerfind != null && e.keyCode === 27) {
			closedivshadow<?php echo $module_id; ?>();
			return false;
		}
	});
	$(document).ready(function() {
		$('body .divshadow').addClass('cheaper30').addClass(class_bootstrap);
		centering('.divshadow');
	});
	$('body').on('click', '.modal-bg-cheaper', function(){
		closedivshadow<?php echo $module_id; ?>(); return false;
	});
</script>
<?php if ($bootstrap){ ?><link href="catalog/view/javascript/cheaper30/bootstrap.min.css" type="text/css" rel="stylesheet" media="screen" /><?php } ?>
<?php if ($font){ ?><link href="catalog/view/javascript/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" /><?php } ?>
<script>
<?php if ($fields){ ?>
	$('#cheaperForm<?php echo $module_id; ?>').validate({
		rules: {
			<?php foreach ($results as $result){ ?>
				<?php foreach ($result as $fields){ ?>
					<?php foreach ($fields as $id => $field){ ?>
						'input_field[<?php echo $field['id']; ?>]<?php echo $field['type'] == "file" ? "[file]" : ""; ?><?php echo $field['type'] == "checked" or $field['type'] == "radio" or $field['type'] == "select" ? "[]" : ""; ?>': {
								required: <?php if ($field['required']){ ?>true<?php } else { ?>false<?php } ?>,
						<?php if ($field['regex'] != 'phoneUS') { ?>
							<?php if ($field['regex'] and $field['valid']){ ?>
								<?php if (is_array($field['valid'])){ ?>
									<?php echo $field['regex']; ?>: [<?php echo implode(',', $field['valid']); ?>],
								<?php } else { ?>
									<?php echo $field['regex']; ?>: <?php echo $field['valid']; ?>,
								<?php } ?>
							<?php } ?>
						<?php } ?>
						<?php if ($field['regex'] == 'email' or $field['regex'] == 'url' or $field['regex'] == 'date' or $field['regex'] == 'dateISO' or $field['regex'] == 'number' or $field['regex'] == 'phoneUS'){ ?>
							<?php echo $field['regex']; ?>: true,
						<?php } ?>
						require_from_group: [1, ".form-required-module"]
						},
					<?php } ?>
				<?php } ?>
			<?php } ?>
			<?php if ($protection['format'] and $protection['format'] == 'checkbox'){ ?>
				input_zachita: {
					required: true,
				},
				<?php foreach ($fields as $id => $field){ ?>
					<?php if ($field['required']){ ?>
						'input_field[<?php echo $field['id']; ?>]<?php echo $field['type'] == "file" ? "[file]" : ""; ?><?php echo $field['type'] == "checked" or $field['type'] == "radio" or $field['type'] == "select" ? "[]" : ""; ?>': {
							required: "<?php echo $error_required_from_group; ?>",
						},
					<?php } ?>
				<?php } ?>
			<?php } ?>
			<?php if ($captch == 'default'){ ?>
				captcha:{
					required: true,
					remote: 'index.php?route=<?php echo $extension; ?>module/cheaper30/process&module_id=<?php echo $module_id; ?>'
				}
			<?php } ?>
		},
		messages: {
			input_zachita: {
				required: "<?php echo $text_error_zachita; ?>",
			},
			<?php foreach ($results as $result){ ?>
				<?php foreach ($result as $fields){ ?>
					<?php foreach ($fields as $id => $field){ ?>
						<?php if ($field['required']){ ?>
							'input_field[<?php echo $field['id']; ?>]<?php echo ($field['type'] == "file" ? "[file]" : ""); ?><?php echo ($field['type'] == "checked" or $field['type'] == "radio" or $field['type'] == "select") ? "[]" : ""; ?>': {
								required: "<?php echo $error_required_from_group; ?>",
							},
						<?php } ?>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		},
		submitHandler: function() {
			return quick_pay_button<?php echo $module_id; ?>();
			return false;
		},
		
		
	});
	<?php if ($localization_scripts){ ?>
		$.getScript('<?php echo $localization_scripts; ?>');
	<?php } ?>
<?php } ?>
$.validator.addMethod( "require_from_group", function( value, element, options ) {
	var $fields = $( options[ 1 ], element.form ),
		$fieldsFirst = $fields.eq( 0 ),
		validator = $fieldsFirst.data( "valid_req_grp" ) ? $fieldsFirst.data( "valid_req_grp" ) : $.extend( {}, this ),
		isValid = $fields.filter( function() {
			return validator.elementValue( this );
		} ).length >= options[ 0 ];

	$fieldsFirst.data( "valid_req_grp", validator );

	if ( !$( element ).data( "being_validated" ) ) {
		$fields.data( "being_validated", true );
		$fields.each( function() {
			validator.element( this );
		} );
		$fields.data( "being_validated", false );
	}
	return isValid;
}, $.validator.format( '<?php echo $error_require_from_group; ?>' ) );
$('.date').datetimepicker({
	language: '<?php echo $lan; ?>',
	pickTime: false
});
$('.time').datetimepicker({
	language: '<?php echo $lan; ?>',
	pickDate: false
});
$('.datetime').datetimepicker({
	language: '<?php echo $lan; ?>',
	pickDate: true,
	pickTime: true
});
$('button.button-upload').on('click', function() {
	var node = this;
	$('#form-upload').remove();
	$('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');
	$('#form-upload input[name=\'file\']').trigger('click');
	if (typeof timer != 'undefined'){
    	clearInterval(timer);
	}
	timer = setInterval(function() {
		if ($('#form-upload input[name=\'file\']').val() != '') {
			clearInterval(timer);

			$.ajax({
				url: 'index.php?route=tool/upload',
				type: 'post',
				dataType: 'json',
				data: new FormData($('#form-upload')[0]),
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: function() {
					$(node).button('loading');
				},
				complete: function() {
					$(node).button('reset');
				},
				success: function(json) {
					$('.text-danger, .success-module, .error-module').remove();

					if (json['error']) {
						$(node).parent().find('input').after('<label class="error-module">' + json['error'] + '</label>');
					}

					if (json['success']) {
						$(node).parent().parent().find('.error-module').remove();
						$(node).parent().parent().find('.success-module').remove();
						$(node).parent().find('input').after('<label class="success-module">' + json['success'] + '</label>');
						$(node).parent().find('input').val(json['code']);
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	}, 500);
});
function captchaimage<?php echo $module_id; ?>(){
	$.post('index.php?route=<?php echo $extension; ?>module/cheaper30/newsession&module_id=<?php echo $module_id; ?>').done(function(){
		$("#captchaimage<?php echo $module_id; ?>").load('index.php?route=<?php echo $extension; ?>module/cheaper30/refreshimage&module_id=<?php echo $module_id; ?>',function(){
			$('#captcha').val('');
			$('body #captchaimage<?php echo $module_id; ?> #refreshimg').click(function(){
				captchaimage<?php echo $module_id; ?>();
				return false;
			});
			/*$('.divshadowmodule input[type=\'text\'],.divshadowmodule textarea').each(function(){
				$(this).val('').attr('value','');
			});*/
		});
	});
}
<?php if ($captch == 'basic' or $captch == 'basic_captcha'){ ?>
function captchaThirdimage<?php echo $module_id; ?>(){
	var html_captcha = '<?php echo $captcha; ?>';
	$.ajax({
		type: 'get',
		url: 'index.php?route=<?php echo $extension; ?>module/cheaper30/loadcaptcha&module_id=<?php echo $module_id; ?>',
		data: '',
		dataType: 'html',
		cache: false,
		success: function(html_captcha){
			$('.captcha-module<?php echo $module_id; ?> > .input-group').empty().append(html_captcha);
			restructureCaptcha<?php echo $module_id; ?>();
			$(".captcha-module<?php echo $module_id; ?> img").prop("src", "index.php?route=extension/captcha/basic_captcha/captcha/?" + new Date().valueOf());
		},
	});
}
<?php } ?>
function restructureCaptcha<?php echo $module_id; ?>(){
	<?php if ($captch == 'google' or $captch == 'google_captcha'){ ?>
		$('.captcha-module<?php echo $module_id; ?> .col-sm-2').addClass('hide');
		$('.captcha-module<?php echo $module_id; ?> .col-sm-10').removeClass('col-sm-10').addClass('col-sm-12');
	<?php } ?>
	<?php if ($captch == 'basic' or $captch == 'basic_captcha'){ ?>
		$('.captcha-module<?php echo $module_id; ?> .col-sm-2').removeClass('col-sm-2').addClass('display-block-module');
		$('.captcha-module<?php echo $module_id; ?> .col-sm-10').removeClass('col-sm-10').addClass('input-group');
		$('.captcha-module<?php echo $module_id; ?> img').wrap('<div class="input-group-btn"></div>');
		$('.captcha-module<?php echo $module_id; ?> .form-group .input-group').append($('.captcha-module<?php echo $module_id; ?> .input-group .form-control'));
		$('.captcha-module<?php echo $module_id; ?> .form-group').addClass('marg-bottom-0');
	<?php } ?>
	<?php if ($captch == 'default'){ ?>
		captchaimage<?php echo $module_id; ?>();
	<?php } ?>
	$('.captcha-module<?php echo $module_id; ?> label.control-label').removeClass('control-label');
}
setTimeout(function(){
	restructureCaptcha<?php echo $module_id; ?>();
},0);
if (!$('.person').text().replace(/\s/g, '')){
	$('.person').remove();
}
setTimeout(function(){
	$('.bootstrap-datetimepicker-widget').attr('style', 'z-index: 2147483647 !important');
},1000);
<?php if (isset($config_language)){ ?>
	<?php if ($fields){ ?>
		<?php foreach ($results as $result){ ?>
			<?php foreach ($result as $fields){ ?>
				<?php foreach ($fields as $field){ ?>
					<?php if ($field['regex'] == 'phoneUS'){ ?>
						$('#cheaperForm<?php echo $module_id; ?> input[name=\'input_field[<?php echo $field['id']; ?>]\']').mask('<?php echo $field['valid'][$config_language]; ?>',{autoclear: false});
					<?php } ?>
				<?php } ?>
			<?php } ?>
		<?php } ?>
	<?php } ?>
<?php } ?>

function nth_root(number, n){
	if (number < 0 && n % 2 === 1){
        return -Math.pow(-number, 1/n);
    } else {
        return Math.pow(number, 1/n)
	}
}
function toFixedRound(number, n){
	return number.toFixed(n);
}
function medium(a, ...sequence){
	
	var length = sequence.length + 1;
	var summa = a;
	
	sequence.forEach(function(item){
		summa = summa + item;
	});
	
	return summa/length;
}
function strlen(str){
	return str.toString().length;
}
function log(number, base){
    return Math.log(number) / Math.log(base);
}
function ctg(number) {
	return 1 / Math.tan(number);
}
function mod(number1,number2) {
	return number1 % number2;
}
function keyFormula<?php echo $module_id; ?>(type, _this){
	
		<?php if ($formulas) { ?>
			var htm = '';
			var results = [];
			$('.divshadowmodule<?php echo $module_id; ?> .block-calculator').empty();
			$('.divshadow<?php echo $module_id; ?> .block-calculator').empty();
			
			<?php foreach ($formulas as $formula_id => $formula) { ?>
				var formula = '';
				var test_show = false;
				<?php foreach ($formula as $formul) { ?>
					<?php if ($formul['type'] == 'field') { ?>
						formula += 'input_field[<?php echo $formul['value']; ?>]|';
					<?php } ?>
					<?php if ($formul['type'] == 'list') { ?>
						formula += 'input_field[<?php echo $formul['value']; ?>]|';
					<?php } ?>
					<?php if ($formul['type'] == 'symbol') { ?>
						formula += '<?php echo $formul['value']; ?>|';
					<?php } ?>
					<?php if ($formul['type'] == 'function') { ?>
						formula += '!<?php echo $formul['value']; ?>!|';
					<?php } ?>
					<?php if ($formul['type'] == 'condition') { ?>
						
						var itog_condition = [];
						<?php foreach ($formul['conditions'] as $sort_field => $condit) { ?>
							<?php foreach ($condit as $row => $cond) { ?>
								var formula_condition = '';
								
								<?php foreach ($cond as $parts => $con) { ?>
									
									<?php if ($parts != 'to') { ?>
										formula_condition += '<?php echo $parts; ?>';
									<?php } ?>
									
									<?php if ($parts == 'if') { ?>
										formula_condition += ' (';
									<?php } ?>
									
									<?php if ($parts == 'to' or $parts == 'else'){ ?>
										formula_condition += ' {';
									<?php } ?>
									
									<?php foreach ($con as $field) { ?>
									
										<?php if ($field['type'] == 'field') { ?>
											if ($('input[name=\'input_field[<?php echo $field['field_id']; ?>]\']').length != 0){
												formula_condition += '\'' + $('input[name=\'input_field[<?php echo $field['field_id']; ?>]\']').val() + '\'';
											}
											if ($('select[name=\'input_field[<?php echo $field['field_id']; ?>][]\']').length != 0){
												formula_condition += '\'' + $('select[name=\'input_field[<?php echo $field['field_id']; ?>][]\']').val() + '\'';
											}
											if ($('input[name=\'input_field[<?php echo $field['field_id']; ?>][]\'][type=\'radio\']:checked').length != 0){
												formula_condition += '\'' + $('input[name=\'input_field[<?php echo $field['field_id']; ?>][]\'][type=\'radio\']:checked').val() + '\'';
											}
											if ($('input[name=\'input_field[<?php echo $field['field_id']; ?>][]\'][type=\'checkbox\']:checked').length != 0){
												var valu = 0;
												$('input[name=\'input_field[<?php echo $field['field_id']; ?>][]\'][type=\'checkbox\']:checked').each(function(){
													valu = parseFloat(valu) + parseFloat($(this).val());
													valu = valu.toFixed(3);
												});
												formula_condition += '\'' + valu + '\'';
											}
										<?php } ?>
										<?php if ($field['type'] == 'symbol') { ?>
											formula_condition += '\'<?php echo $field['value']; ?>\'';
										<?php } ?>
										<?php if ($field['type'] == 'number') { ?>
											formula_condition += '\'<?php echo $field['value']; ?>\'';
										<?php } ?>
									
									<?php } ?>
									
									<?php if ($parts == 'if') { ?>
										formula_condition += ')';
									<?php } ?>
									
									<?php if ($parts == 'to' or $parts == 'else') { ?>
										formula_condition += '} ';
									<?php } ?>
									
								<?php } ?>
								
								['=','!=','>','<','>=','<='].forEach(function(item){
									var itemval = item;
									if (item == '='){itemval = '==';}
									
									formula_condition = formula_condition.replace('\'' + item + '\'', itemval);
								});
								
								var eval_formula_condition = eval(formula_condition);
								
								if (eval_formula_condition != undefined){
									itog_condition.push(eval_formula_condition);
								}
								
							<?php } ?>
						<?php } ?>
						
						if (itog_condition && itog_condition[itog_condition.length - 1] != undefined){
							formula += itog_condition[itog_condition.length - 1];
						}
						
					<?php } ?>
					<?php if ($formul['show']){ ?>
						test_show = true;
					<?php } ?>
				<?php } ?>
				
				if (formula){
					if (formula.indexOf('=') != '-1'){
						
						var first_formula = [];
						var last_formula = [];
						
						formula.split('=')[0].split('|').forEach(function(i, item){
							if (i){
								first_formula.push(i);
							}
						});
						formula.split('=')[1].split('|').forEach(function(i, item){
							if (i){
								last_formula.push(i);
							}
						});

						var temp_first_formula = first_formula;
						var temp_last_formula = last_formula;
						
						if (first_formula.length > 1 && last_formula.length == 1){
							first_formula = temp_last_formula;
							last_formula = temp_first_formula;
						} else {
							first_formula = temp_first_formula;
							last_formula = temp_last_formula;
						}
						
						var equal_field = first_formula[0];
						var calculation = '';
						var testNumber = [];
						var test_empty = 0;
						
						last_formula.forEach(function(item){
							if (item.indexOf('input_field') != '-1'){
								
								if ($('input[name^=\'' + item + '\']:checked').length !== 0){

										var summa_checkbox = 0;
										$('input[name^=\'' + item + '\'][data-formula_<?php echo $formula_id; ?>]:checked').each(function(){
											
											summa_checkbox = summa_checkbox + parseFloat($(this).attr('data-formula_<?php echo $formula_id; ?>'));
											
										});
										if (summa_checkbox != 0){
											calculation += summa_checkbox;
										}

								} else {
									
									if ($('input[name^=\'' + item + '\']:checked').attr('data-formula_<?php echo $formula_id; ?>') != undefined){
										calculation += $('input[name^=\'' + item + '\']').attr('data-formula_<?php echo $formula_id; ?>');
									}
								}

									if ($('select[name^=\'' + item + '\'] option:selected').attr('data-formula_<?php echo $formula_id; ?>') != undefined){
										calculation += $('select[name^=\'' + item + '\'] option:selected').attr('data-formula_<?php echo $formula_id; ?>');
									}

								if ($('input[name=\'' + item + '\']').length !== 0){
									
									var val_input =  $('input[name=\'' + item + '\']').val();
									if (val_input == 'NaN'){
										val_input = '';
									}
									calculation += val_input;
									if (!$('input[name=\'' + item + '\']').val()){
										test_empty = 1;
									}
								}
							
							} else {
								if (results[item] != undefined){
									calculation += results[item];
								} else {
									calculation += item;
								}
							}
						});
						
							['round','ceil','floor','toFixed','ceil','pow','sqrt','nth_root','min','max','medium','strlen','ln','lg','log','sin','cos','tan','ctg','mod'].forEach(function(item){
								if (calculation.indexOf('!' + item + '!') != -1){
									var count_function = calculation.split('!' + item + '!').length - 1;
									
									var new_function = 'Math.' + item;
									if (item == 'toFixed'){
										new_function = 'toFixedRound';
									}
									if (item == 'nth_root'){
										new_function = 'nth_root';
									}
									if (item == 'medium'){
										new_function = 'medium';
									}
									if (item == 'strlen'){
										new_function = 'strlen';
									}
									if (item == 'ln'){
										new_function = 'Math.log';
									}
									if (item == 'lg'){
										new_function = 'Math.log10';
									}
									if (item == 'log'){
										new_function = 'log';
									}
									if (item == 'ctg'){
										new_function = 'ctg';
									}
									if (item == 'mod'){
										new_function = 'mod';
									}
									
									for (i=1; i <= count_function; i++){
										calculation = calculation.replace('!' + item + '!', new_function);
									}
								}
							});
							
							var type_input = $('[name^=\'' + equal_field + '\']').attr('type');
							
							if (!$('[name^=\'' + equal_field + '\']').is(':focus')){
								$('[name^=\'' + equal_field + '\']').val('').attr('value', '');
							}
							
							var result = calculation;
							console.log(calculation);
							
							//try {
								var result = eval(calculation);
							/*} catch(err){
								
							}*/
							
							try {
								if (result.toString().indexOf('NaN') !== -1){
									result = '';
								}
							} catch(err) {

							
							}
							if (result < 0){
								result = '';
							}
							
							
							if (equal_field.indexOf('input_field') == 0){
								
									if (type_input == 'text'){
										$('input[name=\'' + equal_field + '\']').val(result).attr('value', result);
									}
									if (type_input == 'textarea'){
										$('textarea[name=\'' + equal_field + '\']').val(result).text(result);;
									}
									if (type_input == 'checkbox' || type_input == 'radio'){
										$('input[name^=\'' + equal_field + '\']').prop('checked',false);
										$('input[name^=\'' + equal_field + '\'][data-formula_<?php echo $formula_id; ?>=\'' + result + '\']').prop('checked',true);
									}
									if (type_input == 'select'){
										
										if ($('select[name^=\'' + equal_field + '\'] option[data-formula_<?php echo $formula_id; ?>=\'' + result + '\']').html() != undefined){
											$('select[name^=\'' + equal_field + '\'] option').removeAttr('selected').prop('selected', false);
											
											$('select[name^=\'' + equal_field + '\'] option[data-formula_<?php echo $formula_id; ?>=\'' + result + '\']').prop('selected', true).attr('selected', 'selected');
											
										} else {
											
											if ($('select[name^=\'' + equal_field + '\'] option:first') != undefined){
												$('select[name^=\'' + equal_field + '\'] option').removeAttr('selected').prop('selected', false);
												$('select[name^=\'' + equal_field + '\'] option:first').prop('selected', true).attr('selected', 'selected');

											}
										}
										$('select[name^=\'' + equal_field + '\'] option:selected').prop('selected', true);
									}
							
							} else {
								
								if (test_show){
									if (result != undefined){
										htm += '<div class="mb-3"><div><strong>' + equal_field + '</strong></div><div>' + result + '</div></div>';
									}
								}
								
								results[equal_field] = result;
							}
					}
				}
			<?php } ?>
			
			$('.divshadowmodule<?php echo $module_id; ?> .block-calculator').append(htm);
			$('.divshadow<?php echo $module_id; ?> .block-calculator').append(htm);
			
		<?php } ?>
}
$(document).on('keyup', '.option-div<?php echo $module_id; ?> input', function(e){
	var val1 = $(this).val().replace(',','.');
	$(this).val(val1).attr('value',val1);
	
	keyFormula<?php echo $module_id; ?>();
});
$(document).on('keyup', '.option-div<?php echo $module_id; ?> textarea', function(e){
	keyFormula<?php echo $module_id; ?>();
});
$(document).on('change', '.option-div<?php echo $module_id; ?> .forming_quick input[name^=\'input_field\'][type=\'checkbox\']', function(e){
	keyFormula<?php echo $module_id; ?>('checkbox', $(this));
});
$(document).on('change', '.option-div<?php echo $module_id; ?> .forming_quick input[name^=\'input_field\'][type=\'radio\']', function(e){
	keyFormula<?php echo $module_id; ?>('radio', $(this));
});
$(document).on('change', '.option-div<?php echo $module_id; ?> .forming_quick select[name^=\'input_field\']', function(e){
	keyFormula<?php echo $module_id; ?>('select', $(this));
});
</script>
<style>
<?php if ($style){ ?><?php echo $style; ?><?php } ?>
</style>
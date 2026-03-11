<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $text_module_request; ?> <i class="fa fa-caret-down fa-fw"></i></a>
	<ul class="dropdown-menu dropdown-menu-right">
		<?php foreach ($cheaperings as $key => $cheaper){ ?>
		<li><a href="<?php echo $cheaper['href']; ?>"><?php echo $cheaper['name']; ?>&nbsp;&nbsp;<span class="label<?php if ($cheaper['total'] > 0){ ?> label-danger <?php } else { ?> label-success <?php } ?>"><?php echo $cheaper['total']; ?></span></a></li>
		<?php } ?>
	</ul>
</li>
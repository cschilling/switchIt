<?php include('options.php'); ?>
<?php include('lang/'.$C_lang.'.php'); ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Bootstrap 101 Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="css/bootstrap-glyphicons.css" rel="stylesheet" media="screen">
    <link href="css/bootstrap-switch.css" rel="stylesheet" media="screen">
  </head>
  <body>
	<div style="margin-bottom: 60px;">
		<?php include('nav.php'); ?>
	</div>
  
    <legend>Wohnzimmer</legend>

	<table class="table table-striped">
	<!-- Switch All -->
	<tr>
		<td style="width: 70px;">
			<button type="button" class="btn btn-success" style="float: left;"><?php echo $L_on; ?></button>
		</td>
		<td>
			<span style="font-size: 14pt; font-style: italic;"><?php echo $L_all; ?></span>
		</td>
		<td style="width: 40px;">
			<button type="button" class="btn btn-danger" style="float: right;"><?php echo $L_off; ?></button>
		</td>
	</tr>
	<!-- /Switch All -->
	
	<!-- View -->
	<tr>
		<td style="width: 70px;">
			<button type="button" class="btn btn-success" style="float: left;"><?php echo $L_on; ?></button>
		</td>
		<td>
			<span style="font-size: 14pt;">Stehlampe</span>
		</td>
		<td style="width: 40px;">
			<button type="button" class="btn btn-danger" style="float: right;"><?php echo $L_off; ?></button>
		</td>
	</tr>
	<!-- /View -->

	<tr>
		<td style="width: 70px;">
			<button type="button" class="btn btn-success" style="float: left;"><?php echo $L_on; ?></button>
		</td>
		<td>
			<span style="font-size: 14pt;">Stehlampe 2</span>
		</td>
		<td style="width: 40px;">
			<button type="button" class="btn btn-danger" style="float: right;"><?php echo $L_off; ?></button>
		</td>
	</tr>

	<!-- Edit -->
	<tr>
		<td style="width: 70px;">
			<button type="button" class="btn btn-primary text-right" style="float: right;">
				<span class="glyphicon glyphicon-pencil" style="margin-right: 10px;"></span><?php echo $L_edit; ?>
			</button>
		</td>
		<td>
			<span style="font-size: 14pt;">Stehlampe 2</span>
		</td>
		<td style="width: 70px;">
			<button type="button" class="btn btn-danger text-right" style="float: right;">
				<span class="glyphicon glyphicon-remove-sign" style="margin-right: 10px;"></span><?php echo $L_del; ?>
			</button>
		</td>
	</tr>
	<!-- /Edit -->

	<!-- Add -->
	<tr>
		<td colspan="3">
			<button type="button" class="btn btn-primary text-right" style="float: right;">
				<span class="glyphicon glyphicon-plus-sign" style="margin-right: 10px;"></span><?php echo $L_add; ?>
			</button>
		</td>
	</tr>
	<!-- /Add -->
	
	
	<!-- New/Edit -->
	<tr>
		<td colspan="3">
			<legend><?php echo $L_dip; ?></legend>
		</td>
	</tr>
	
	<?php for ($i = 1; $i <= 5; $i++): ?>
	<tr>
		<td style="width: 70px;">
			<?php echo $i; ?>
		</td>
		<td colspan="2">
			<div class="switch" data-on="success" data-on-label="<?php echo $i; ?>" data-off="danger" data-off-label="<?php echo $i; ?>">
				<input type="checkbox" checked />
			</div>
		</td>
	</tr>
	<?php endfor; ?>
	
	<?php for ($i = 'A'; $i <= 'E'; $i++): ?>
	<tr>
		<td style="width: 70px;">
			<?php echo $i; ?>
		</td>
		<td colspan="2">
			<div class="switch" data-on="success" data-on-label="<?php echo $i; ?>" data-off="danger" data-off-label="<?php echo $i; ?>">
				<input type="checkbox" checked />
			</div>
		</td>
	</tr>
	<?php endfor; ?>

	

	<!-- /Edit -->
	
	</table>
	
	
	<legend>Balkon</legend>

    <!-- JavaScript plugins (requires jQuery) -->
    <script src="js/jquery-1-10-2.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
	
	<script src="js/bootstrap-switch.min.js"></script>
  </body>
</html>
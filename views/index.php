<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>REC Migrator</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pure/0.6.0/pure-min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pure/0.6.0/grids-responsive-min.css">
	<link rel="stylesheet" href="/css/app.css">
</head>
<body>
		
	<header>
		<h1>REC Migrator</h1>
		<p>Migrate old REC template code to the new Twig syntax</p>
	</header>

	<?php /* show error */ if ($view->error) { ?>
	<div class="error">
		<?php echo $view->error; ?>
	</div>
	<?php } ?>

	<?php /* show results */ if ($view->convertedHtml) { ?>
	<form class="pure-g pure-form">

		<div class="pure-u-1 pure-u-md-1-2">
			<p><strong>Converted HTML:</strong><br>
				<textarea class="pure-input-1" readonly rows="10"><?php echo escape($view->convertedHtml); ?></textarea>
			</p>
		</div>

		<div class="pure-u-1 pure-u-md-1-2">
			<p>Original HTML:<br>
				<textarea class="pure-input-1" readonly rows="10"><?php echo escape($view->userHtml); ?></textarea></li>
			</p>
		</div>

	</form>

	<h2>Re-run?</h2>
	<?php } ?>

	<form method="POST" class="pure-form pure-form-stacked">
		
		<fieldset>
			
			<!-- html to run against -->
			<label for="selector">Old HTML</label>
			<textarea class="pure-input-1" rows="6" name="html" id="html" placeholder="&lt;div&gt;Product #{product:id}&lt;/div&gt;..."></textarea>

			<button type="submit" class="pure-button pure-button-primary">Submit</button>

		</fieldset>
	</form>

	<footer>
		<small>&copy; <?php echo date('Y'); ?></small>
	</footer>

</body>
</html>

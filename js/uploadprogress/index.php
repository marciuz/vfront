<html>
	<head>
		<title>Test Upload Progress</title>
		<script type="text/javascript" src="LoadVars.js"><!--// http://www.devpro.it/javascript_id_92.html //--></script>
		<script type="text/javascript" src="BytesUploaded.js"><!--// http://www.devpro.it/javascript_id_96.html //--></script>
		<script type="text/javascript">
			var bUploaded = new BytesUploaded('whileuploading.php',500);
		</script>
	</head>
	<body style="font-family: sans-serif; font-size: 8pt;">
		<form enctype="multipart/form-data" method="post" action="index.php" onsubmit="bUploaded.start('fileprogress');">
			<div>
				<fieldset style="padding: 20px;">
					<legend>Add a file</legend>
					<input id="filename" type="file" name="gfile" />
					<input type="submit" value="aggiungi file" />
				</fieldset>
			</div>
		</form>
		<div id="fileprogress" style="font-weight: bold;"> </div>
		<pre><?php var_dump($_FILES); ?></pre>
	</body>
</html>
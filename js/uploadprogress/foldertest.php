<html>
	<head>
		<title>Test Temporary Folder</title>
	</head>
	<body style="font-family: sans-serif; font-size: 8pt;">
		<form enctype="multipart/form-data" method="post" action="foldertest.php">
			<div>
				<fieldset style="padding: 20px;">
					<legend>Add a file (choose a small file)</legend>
					<input id="filename" type="file" name="gfile" />
					<input type="submit" value="aggiungi file" />
				</fieldset>
			</div>
		</form>
		<div>
		<?php
			if(isset($_FILES["gfile"], $_FILES["gfile"]["tmp_name"])) {
				$_FILES["gfile"]["tmp_name"] = str_replace("\\", "/", $_FILES["gfile"]["tmp_name"]);
				$_FILES["gfile"]["tmp_name"] = substr(
					$_FILES["gfile"]["tmp_name"],
					0,
					(strpos(
						$_FILES["gfile"]["tmp_name"],
						basename($_FILES["gfile"]["tmp_name"])
					) - 1)
				);
				echo 	"Use this value for \$tmpdir var inside whileuploading.php file.<br /><strong>
					{$_FILES["gfile"]["tmp_name"]}</strong><br />
					(then first line should be \$tmpdir = '{$_FILES["gfile"]["tmp_name"]}'; )";
			}
		?></div>
	</body>
</html>
<?php 
require_once(PATH_TO_DIR.'/templates/header.php');
?>
<div id="form_db_tab">
	<form action='<?php create_config();?>' method="POST">
	<div>
		<div class="field_desc">Your Database Name</div>
		<div class="field"><input type='text' name="db_name" placeholder="database name" id="db_name" required></div>
	</div>	
	<div>
		<div class="field_desc">Your Database HOST</div>
		<div class="field"><input type='text' name="db_host" placeholder="database host" id="db_host" value="localhost" required></div>
	</div>
	<div>
		<div class="field_desc">Your Database User Name</div>
		<div class="field"><input type='text' name="db_user" placeholder="user name" id="db_user" required></div>
	</div>
	<div>
		<div class="field_desc">Your Database User Password</div>
		<div class="field"><input type='password' name="db_password" placeholder="user password" id="db_password" required></div>
	</div>
	<div>	
		<div class="field"><input type='submit' name="create_config" id="submit" value="SUBMIT"></div>
	</div>
	</form>
	<?php if(isset($_SESSION['db_error'])){ ?>
		<div class="result"><h3><?=$_SESSION['db_error']?></h3></div>
	<?php } ?>
</div>
<?php
unset($_SESSION['db_error']);
require_once(PATH_TO_DIR.'/templates/footer.php');

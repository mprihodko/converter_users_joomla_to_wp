<div class="dashboard">
	<div class="tab-items-bar">
		<ul class="tab-list" id="tab-list-menu">
			<li class="tab active">
				<a href="#convert_users">Convert Users</a>
			</li>
			<li class="tab">
				<a href="#convert_reports">Convert Reports</a>
			</li>		
		</ul>
	</div>
	<div class="tab-section">
		<ul class="tab-blocks">
			<li class="tab-content active" id="convert_users">
				<div class="tab-header"><h2>Convert Users</h2></div>
				<div class="tab-actions">
				<?php 				
					if(isset($_POST['merge_users'])){
						convert_users_tables();

					}
					?>
					<form action="" method="POST" enctype="multipart/form-data">
						<div>
							<span>Load joomla users table</span>
						</div>
						<div>
							<input type="file" name="joomla_db" id="joomla_db" required>							
						</div>
						<div>
							<span>Load wordpress users table</span>
						</div>
						<div>							
							<input type="file" name="wp_db_user" id="wp_db_user">
						</div>
						<div>
							<span>Load wordpress usermeta table</span>
						</div>
						<div>							
							<input type="file" name="wp_db_usermeta" id="wp_db_usermeta">
						</div>
						<div class="action_button">							
							<input type="submit" name="merge_users" value="Convert"> 
						</div>
					</form>	
				</div>
				<?php file_upload_result(); ?>
				<?php download_files(); ?>
				<?php if(file_exists(PATH_TO_DIR.'/uploads/tables_'.session_id().'/wp_users_'.session_id().'.sql') && file_exists(PATH_TO_DIR.'/uploads/tables_'.session_id().'/wp_usermeta_'.session_id().'.sql')){
					echo"<form action='' method='POST'>
							<button type='submit' id='download_button' value='".session_id()."' name='download_archive'>Download Archive</button>
						</form>";	
				}
				?>
				<div>
					
				</div>
			</li>
			<li class="tab-content" id="convert_reports">
				
			</li>
		</ul>
	</div>
</div>
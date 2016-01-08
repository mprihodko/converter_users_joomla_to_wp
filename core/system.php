<?php
/* connect to database ODBC*/
if(file_exists(PATH_TO_DIR.'/core/config.php')){
	require_once(PATH_TO_DIR.'/core/config.php');
}
if(defined('DB_NAME') && defined('DB_HOST') && defined('DB_USER') && defined('DB_PASSWORD')){

	@$sql=mysql_pconnect(DB_HOST, DB_USER,DB_PASSWORD);
	$dbselect=mysql_select_db(DB_NAME);	
	if(!$sql || !$dbselect){
		require_once(PATH_TO_DIR.'/core/connect_form.php');
		exit();
	} 
}else{
	require_once(PATH_TO_DIR.'/core/connect_form.php');
	exit();
}


function styles($css){	
	foreach ($css as $cssname => $path) {
		echo '<link rel="stylesheet" id="'.$cssname.'" href="'.BASE_URL.$path.'" type="text/css">';
	}
}
function create_config(){
	$error='';
	if(isset($_POST['create_config'])){	
		@$sql=mysql_pconnect($_POST['db_host'], $_POST['db_user'],$_POST['db_password']);
		$dbselect=mysql_select_db($_POST['db_name']);		
		if($sql && $dbselect){
			$fp = fopen(PATH_TO_DIR.'/core/config.php', 'w');
			$fc = '<?php 
	define("DB_NAME", "'.$_POST['db_name'].'"); 
	define("DB_HOST", "'.$_POST['db_host'].'"); 
	define("DB_USER", "'.$_POST['db_user'].'"); 
	define("DB_PASSWORD", "'.$_POST['db_password'].'"); 
?>';
			$fw=fwrite($fp, $fc);
			fclose($fp);
			load_db_dump(
						PATH_TO_DIR.'/assets/wp_to_joomla.sql',
						$_POST['db_host'],
						$_POST['db_user'],
						$_POST['db_password'],
						$_POST['db_name']
						);
		
		} else{
	   		$_SESSION['db_error']="Check the settings for the database connection";
		}
	}

}

function load_db_dump($file,$sqlserver,$user,$pass,$dest_db){
	$sql=mysql_connect($sqlserver,$user,$pass);	
	$result = mysql_list_tables($dest_db);
	while($table = mysql_fetch_array($result)) {
		$query = "DROP TABLE `".$table[0]."`";		
		mysql_query($query);
	}
	$a=file($file);
	foreach ($a as $n => $l) if (substr($l,0,2)=='--') unset($a[$n]);
	$a=explode(";\n",implode("\n",$a));
	unset($a[count($a)-1]);
	foreach ($a as $q) if ($q)
	if (!mysql_query($q)) { mysql_close($sql); }
	mysql_close($sql);
	header("Location: ".BASE_URL);
}
function mysql_table_seek($tablename, $dbname)
{
    $table_list = mysql_query("SHOW TABLES FROM `".$dbname."`");
    while ($row = mysql_fetch_row($table_list)) {
        if ($tablename==$row[0]) {
            return true;
        }
    }
    return false;
} 
function convert_users_tables(){
	
	if(isset($_POST['merge_users'])){
		$error=array();				
		foreach ($_FILES as $key => $value) {
			if(($value['type']=='application/sql' && $value['error']==0) || ($value['type']=='' && $key!="joomla_db")){
				if(!empty($value['name'])){				
					$files[$key]=$value;
				}else{
					switch($key){								
						case 'wp_db_user':
							$err["other"]='Incorrect files from field wordpress users table';
							break;
						case 'wp_db_usermeta':
							$err["other"]='Incorrect files from field wordpress usermeta table';
							break;					
					}
				}
			}else{
				$error[$key]=$value['name']." is incorrect file";		
			}			
		}
		if(count($files)==2){
			$error["other"]=$err["other"];		
		}		
		if(count($error) > 0){
			$_SESSION['file_uploads']=$error;		
		}else{			   
			$_SESSION['files']=$files;			
			$upload_dir=PATH_TO_DIR.'/uploads/tables_'.session_id();
			foreach ($files as $key => $value) {
				$tmp_name = $_FILES[$key]["tmp_name"];
				if(!file_exists($upload_dir))
					mkdir($upload_dir);
				move_uploaded_file($tmp_name, $upload_dir.'/'.$key.'.sql');
					$sqlfile=file($upload_dir.'/'.$key.'.sql');	
					foreach ($sqlfile as $str => $string) {
						if(substr($string, 0, 26)=="CREATE TABLE IF NOT EXISTS"){
							$sqlfile[$str]="CREATE TABLE IF NOT EXISTS `".$key.'_'.session_id()."` (";		
						}
						if(substr($string, 0, 11)=="INSERT INTO"){
							$sqlfile[$str] = preg_replace('~\INSERT INTO `.*?\`~','INSERT INTO `'.$key.'_'.session_id().'`',$string);							
						}
						if($string=="ALTER TABLE `wp_users`"){
							$sqlfile[$str]="ALTER TABLE `".$key.'_'.session_id()."` (";		
						}
						if($string=="ALTER TABLE `wp_usermeta`"){
							$sqlfile[$str]="ALTER TABLE `".$key.'_'.session_id()."` (";		
						}
					}					
					$newSQL=fopen($upload_dir.'/'.$key.'.sql', 'w');
					fwrite($newSQL, implode($sqlfile));
					fclose($newSQL);						
					$a=file($upload_dir.'/'.$key.'.sql');
					foreach ($a as $n => $l) if (substr($l,0,2)=='--') unset($a[$n]);
					$a=explode(";\n",implode("\n",$a));
					unset($a[count($a)-1]);
					foreach ($a as $q) if ($q)
					if (!mysql_query($q)) {  }
			}
			$wp_users="CREATE TABLE IF NOT EXISTS `wp_users_".session_id()."` (
														  `ID` bigint(20) unsigned NOT NULL,
														  `user_login` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
														  `user_pass` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
														  `user_nicename` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
														  `user_email` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
														  `user_url` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
														  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
														  `user_activation_key` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
														  `user_status` int(11) NOT NULL DEFAULT '0',
														  `display_name` varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
														) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
			mysql_query($wp_users);
			$wp_users_alter="ALTER TABLE `wp_users_".session_id()."`
							  ADD PRIMARY KEY (`ID`),
							  ADD KEY `user_login_key` (`user_login`),
							  ADD KEY `user_nicename` (`user_nicename`);";
			mysql_query($wp_users_alter);
			$wp_users_alter_2="ALTER TABLE `wp_users_".session_id()."`
  								MODIFY `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;";
			mysql_query($wp_users_alter_2);
			$wp_usermeta="CREATE TABLE IF NOT EXISTS `wp_usermeta_".session_id()."` (
														  `umeta_id` bigint(20) unsigned NOT NULL,
														  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
														  `meta_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
														  `meta_value` longtext COLLATE utf8_unicode_ci
														) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
			mysql_query($wp_usermeta);
			$wp_usermeta_alter="ALTER TABLE `wp_usermeta_".session_id()."`
								  ADD PRIMARY KEY (`umeta_id`),
								  ADD KEY `user_id` (`user_id`),
								  ADD KEY `meta_key` (`meta_key`(191));";
		    mysql_query($wp_usermeta_alter);
		    $wp_usermeta_alter_2="ALTER TABLE`wp_usermeta_".session_id()."`
  									MODIFY `umeta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;";
  			 mysql_query($wp_usermeta_alter_2);						
			foreach ($files as $key => $value) {
				$query="SELECT * FROM `".$key.'_'.session_id()."`";
				$result=mysql_query($query);
				$i=1;								
				while($data=mysql_fetch_assoc($result)){
					if($key=="joomla_db"){
						$wp_users_joomla[$i]=$data;
					}elseif($key=="wp_db_user"){
						$wp_users_wp[$i]=$data;
					}elseif($key=="wp_db_usermeta"){
						$wp_db_usermeta[$i]=$data;
					}
					$i++;					
				}
				$query = "DROP TABLE `".$key.'_'.session_id()."`";		
				mysql_query($query);
			}
			foreach ($wp_users_joomla as $num => $value) {
				@$users[$num]['wp_users']['ID']=$num;
				@$users[$num]['wp_users']["user_login"]=$value['username'];
				@$users[$num]['wp_users']["user_pass"]=$value['password'];
				@$users[$num]['wp_users']["user_nicename"]=$value['name'];
				@$users[$num]['wp_users']["user_email"]=$value['email'];
				@$users[$num]['wp_users']["user_url"]='';
				@$users[$num]['wp_users']["user_registered"]=$value['registerDate'];
				@$users[$num]['wp_users']["user_activation_key"]='';
				@$users[$num]['wp_users']["user_status"]="0";
				@$users[$num]['wp_users']["display_name"]=$value['name'];
				@$users[$num]['wp_usermeta']["nickname"]=$value['username'];	
				@$users[$num]['wp_usermeta']["first_name"]=substr($value['name'], 0, strpos($value['name'], " "));					
				@$users[$num]['wp_usermeta']["last_name"]=substr($value['name'], strpos($value['name'], " "));			
				@$users[$num]['wp_usermeta']["description"]='';
				@$users[$num]['wp_usermeta']["rich_editing"]='true';
				@$users[$num]['wp_usermeta']["comment_shortcuts"]='false';
				@$users[$num]['wp_usermeta']["admin_color"]='fresh';
				@$users[$num]['wp_usermeta']["use_ssl"]='-';
				@$users[$num]['wp_usermeta']["show_admin_bar_front"]='false';
				@$users[$num]['wp_usermeta']["wp_capabilities"]='a:1:{s:8:"customer";b:1:}';
				@$users[$num]['wp_usermeta']["wp_user_level"]='0';
				@$users[$num]['wp_usermeta']["dismissed_wp_pointers"]='';
				@$users[$num]['wp_usermeta']["author_facebook"]='';
				@$users[$num]['wp_usermeta']["author_custom"]='';
				@$users[$num]['wp_usermeta']["author_gplus"]='';
				@$users[$num]['wp_usermeta']["author_dribble"]='';
				@$users[$num]['wp_usermeta']["author_linkedin"]='';
				@$users[$num]['wp_usermeta']["author_twitter"]='';
			}
			foreach ($wp_users_wp as $numb => $value) {
				@$wp[$numb]['wp_users']['ID']=$numb;
				@$wp[$numb]['wp_users']["user_login"]=$value['user_login'];
				@$wp[$numb]['wp_users']["user_pass"]=$value['user_pass'];
				@$wp[$numb]['wp_users']["user_nicename"]=$value['user_nicename'];
				@$wp[$numb]['wp_users']["user_email"]=$value['user_email'];
				@$wp[$numb]['wp_users']["user_url"]=$value["user_url"];
				@$wp[$numb]['wp_users']["user_registered"]=$value['user_registered'];
				@$wp[$numb]['wp_users']["user_activation_key"]=$value["user_activation_key"];
				@$wp[$numb]['wp_users']["user_status"]=$value["user_status"];
				@$wp[$numb]['wp_users']["display_name"]=$value['display_name'];

			}	
			foreach ($wp_db_usermeta as $idmeta => $value) {
				@$wp[$value['user_id']]['wp_usermeta'][$value['meta_key']]=$value['meta_value'];
			}				
			$allusers=array_merge($users, $wp);
				echo "<pre>";
				// var_dump($allusers);
				echo "</pre>";
				$i=1;
				foreach ($allusers as $key => $value) {
					$tables[$i]=$value;
					$i++;
				}
				foreach ($tables as $key => $value) {
					foreach ($value as $wp => $val) {
						if($wp=="wp_users"){
							$query="INSERT INTO `wp_users_".session_id()."` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_activation_key`, `user_status`, `display_name`) VALUES
							 ('".$val["ID"]."', '".$val["user_login"]."', '".$val["user_pass"]."', '".$val["user_nicename"]."', '".$val["user_email"]."', '".$val["user_url"]."', '".$val["user_registered"]."', '".$val["user_activation_key"]."', '".$val["user_status"]."', '".$val["display_name"]."')";
							$result=mysql_query($query);								
						}elseif($wp=="wp_usermeta"){
							foreach ($val as $meta_key => $data) {
								$query="INSERT INTO `wp_usermeta_".session_id()."` (`user_id`, `meta_key`, `meta_value`) VALUES('".$key."', '".$meta_key."', '".$data."')";
								$result=mysql_query($query);	
							}
						}
					}
				}
			
				$backup_file = $upload_dir.'/wp_users_'.session_id().'.sql';				
				$mybackup = backup_tables(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME, "wp_users_".session_id());					
				$handle = fopen($backup_file,'w+');
				fwrite($handle,$mybackup);
				fclose($handle);
				$query = "DROP TABLE `wp_users_".session_id()."`";		
				mysql_query($query);
				
				$backup_file = $upload_dir.'/wp_usermeta_'.session_id().'.sql';				
				$mybackup = backup_tables(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME, "wp_usermeta_".session_id());					
				$handle = fopen($backup_file,'w+');
				fwrite($handle,$mybackup);
				fclose($handle);
				$query = "DROP TABLE `wp_usermeta_".session_id()."`";		
				mysql_query($query);		
		}
	}

}

function download_files(){
	if(isset($_POST['download_archive']) && $_POST['download_archive']==session_id()){
		$upload_dir=PATH_TO_DIR.'/uploads/tables_'.$_POST['download_archive'];
		$sqlfile=file($upload_dir.'/wp_users_'.$_POST['download_archive'].'.sql');	
		foreach ($sqlfile as $str => $string) {
			$sqlfile[$str] = preg_replace('~\`wp_users_'.session_id().'`~','`wp_users`',$string);
		}					
		$newSQL=fopen($upload_dir.'/wp_users_'.$_POST['download_archive'].'.sql', 'w');
		fwrite($newSQL, implode($sqlfile));
		fclose($newSQL);
		$upload_dir=PATH_TO_DIR.'/uploads/tables_'.$_POST['download_archive'];
		$sqlfile=file($upload_dir.'/wp_usermeta_'.$_POST['download_archive'].'.sql');	
		foreach ($sqlfile as $str => $string) {
			$sqlfile[$str] = preg_replace('~\`wp_usermeta_'.session_id().'`~','`wp_usermeta`',$string);
		}					
		$newSQL=fopen($upload_dir.'/wp_usermeta_'.$_POST['download_archive'].'.sql', 'w');
		fwrite($newSQL, implode($sqlfile));
		fclose($newSQL);	
		zipAdd($upload_dir, '/wp_users_'.session_id().'.sql', '/wp_usermeta_'.session_id().'.sql');

	}

}
function zipAdd($file_folder, $wp_users, $wp_usermeta){
	$error = "";	
	$zip = new ZipArchive(); 
	$zip_name ="wordpress_db.zip"; 
	if($zip->open($zip_name, ZIPARCHIVE::CREATE)==TRUE){
		$zip->addFile($file_folder.$wp_users, "wp_users.sql"); 
		$zip->addFile($file_folder.$wp_usermeta, "wp_usermeta.sql"); 
		$zip->close();
	}
	if(file_exists($zip_name)){
		header('Content-type: application/zip');
		header('Content-Disposition: attachment; filename="'.$zip_name.'"');
		readfile($zip_name);
		unlink($zip_name);
		unlink($file_folder.'/joomla_db.sql');
		unlink($file_folder.'/wp_db_user.sql');
		unlink($file_folder.'/wp_db_usermeta.sql');
		unlink($file_folder.$wp_users);
		unlink($file_folder.$wp_usermeta);
		rmdir($file_folder);
	}
}
function backup_tables($host, $user, $pass, $name, $tables = '*'){
  $data = "\n/*---------------------------------------------------------------".
          "\n  SQL DB BACKUP ".date("d.m.Y H:i")." ".
          "\n  HOST: {$host}".
          "\n  DATABASE: {$name}".
          "\n  TABLES: {$tables}".
          "\n  ---------------------------------------------------------------*/\n";
  $link = @mysql_connect($host,$user,$pass);
  mysql_select_db($name,$link);
  mysql_query( "SET NAMES `utf8` COLLATE `utf8_general_ci`" , $link ); // Unicode

  if($tables == '*'){ //get all of the tables
    $tables = array();
    $result = mysql_query("SHOW TABLES");
    while($row = mysql_fetch_row($result)){
      $tables[] = $row[0];
    }
  }else{
    $tables = is_array($tables) ? $tables : explode(',',$tables);
  }

  foreach($tables as $table){
    $data.= "\n/*---------------------------------------------------------------".
            "\n  TABLE: `{$table}`".
            "\n  ---------------------------------------------------------------*/\n";           
    $data.= "DROP TABLE IF EXISTS `{$table}`;\n";
    $res = mysql_query("SHOW CREATE TABLE `{$table}`", $link);
    $row = mysql_fetch_row($res);
    $data.= $row[1].";\n";

    $result = mysql_query("SELECT * FROM `{$table}`", $link);
    $num_rows = mysql_num_rows($result);    

    if($num_rows>0){
      $vals = Array(); $z=0;
      for($i=0; $i<$num_rows; $i++){
        $items = mysql_fetch_row($result);
        $vals[$z]="(";
        for($j=0; $j<count($items); $j++){
          if (isset($items[$j])) { $vals[$z].= "'".mysql_real_escape_string( $items[$j], $link )."'"; } else { $vals[$z].= "NULL"; }
          if ($j<(count($items)-1)){ $vals[$z].= ","; }
        }
        $vals[$z].= ")"; $z++;
      }
      $data.= "INSERT INTO `{$table}` VALUES ";      
      $data .= "  ".implode(";\nINSERT INTO `{$table}` VALUES ", $vals).";\n";
    }
  }
  
  return $data;
}

function file_upload_result(){
	if(isset($_SESSION['file_uploads'])){
		foreach ($_SESSION['file_uploads'] as $key => $value) {
			switch($key){
				case 'joomla_db':
					echo '<div class="result"><h3>'.$value.' from field joomla users table</h3></div>';
					break;
				case 'wp_db_user':
					echo '<div class="result"><h3>'.$value.' from field wordpress users table</h3></div>';
					break;
				case 'wp_db_usermeta':
					echo '<div class="result"><h3>'.$value.' from wordpress usermeta table</h3></div>';
					break;
				default: 
					echo '<div class="result"><h3>'.$value.' from wordpress usermeta table</h3></div>';
					break;
			}
		}
		unset($_SESSION['file_uploads']);
	} 	
	if(isset($_SESSION['files']) && !isset($_SESSION['file_uploads'])){	
		foreach ($_SESSION['files'] as $key => $value) {
			switch($key){
				case 'joomla_db':
					echo '<div class="result"><h3>'.$value['name'].' success uploaded</h3></div>';
					break;
				case 'wp_db_user':
					echo '<div class="result"><h3>'.$value['name'].' success uploaded</h3></div>';
					break;
				case 'wp_db_usermeta':
					echo '<div class="result"><h3>'.$value['name'].' success uploaded</h3></div>';
					break;
				case 'test':
					var_dump($value);
					break;
				default: 
					echo '<div class="result"><h3>'.$value['name'].' success uploaded</h3></div>';
					break;
			}
		}
				
		unset($_SESSION['files']);
	}
					
}
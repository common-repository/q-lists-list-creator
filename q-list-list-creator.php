<?php

/*
Plugin Name: Q-List
Plugin URI: http://www.weblimner.com/plugin/q-lists-list-creator/
Description: Create a list on your website and let members to vote the items of the list.
Author: Ali Sipahioglu
Author URI: http://www.weblimner.com
Version: 1.0.1
Change Log:
2010-30-01  1.0.1: Fixed a small bug.
2010-30-01  1.0: In this version, users can vote without being a member of your website. Cookies are used. Design is changed. Percentages are shown for the results.
2010-20-01  0.6: Ability to see the results of the votes
2010-08-01  0.5: First release
*/

add_action('admin_menu', 'qlist_menu');

function qlist_menu(){
	add_options_page('Q-List Options', 'Q-List Options', 'administrator', 'qlist_unique', 'qlist_options');
}

function qlist_options(){
	global $wpdb,$HTTP_POST_VARS;
	$i=1;$a=0;
	if($_POST['addnewform']){
		// Assign all the questions to an array
		$f_q= $_POST;
		for($a=0;$a<((count($f_q)/2)-1);$a++){
			$form_questions[$a]["question"]=$f_q["question".($a+1)];
			$form_questions[$a]["order"]=$f_q["order".($a+1)];
		}
		// Get them into the database
		for($a=0;$a<count($form_questions);$a++){
			$wpdb->insert($wpdb->prefix."qlists",array('question'=>$form_questions[$a]["question"],'q_order'=>$form_questions[$a]["order"]),array( '%s', '%d' ));
		}
	}
	if($_POST['updateform']>0){
		$wpdb->update($wpdb->prefix."qlists",array('question'=>$_POST['question'],'q_order'=>$_POST["order"]),array( 'q_id' => $_POST['updateform'] ),array( '%s', '%d' ),array( '%d' ));
	}
	if($_GET['delete']){
		$wpdb->query("DELETE FROM ".$wpdb->prefix."qlists WHERE q_id = '".$_GET['delete']."'");
	}
	echo '<div class="wrap">
			<h2>Q-List <b>Add New</b></h2>
			<form method="post" action="">
			<table class="form-table">
				<tr>
					<th scope="row">Question</th>
					<td><input type="text" name="question'.$i.'" size="60" /></td>
					<td>Order: <input type="text" name="order'.$i.'" size="5"/></td>
				</tr>
				<tr>
					<td colspan="3" align="center">
						<input type="hidden" name="addnewform" value="1"/>
						<input type="submit" value="Save" />
					</td>
				</tr>
		';
	echo 	'</table></form>';
	echo '<h2>Q-List <b>Edit</b></h2>
		
		';
		$db_questions = $wpdb->get_results("SELECT * from ".$wpdb->prefix."qlists order by q_order,question");
		foreach ($db_questions as $db_question) {$i++;
			echo 
				'
				<form method="post" action="">
					<table class="form-table">
						<tr>
							<th scope="row">Question #'.($i-1).'</th>
							<td><input type="text" name="question" size="60" value="'.$db_question->question.'"/></td>
							<td>
								Order: <input type="text" name="order" size="5" value="'.$db_question->q_order.'"/>
								<input type="hidden" name="updateform" value="'.$db_question->q_id.'"/>
								<input type="submit" value="Update" />
								<a href="options-general.php?page=qlist_unique&amp;delete='.$db_question->q_id.'" class="submitdelete deletion" onclick="return confirm(\'You sure you want to delete?\');">Delete</a>
							</td>
						</tr>
					</table>
				</form>
					';
		}

	echo 	'</div>';
	
}

register_activation_hook( __FILE__, 'qlist_activate' );

function qlist_activate() {
	global $wpdb;
	$qlist_db_version = "1.2";
	// Creating Questions Table
	$table_name = $wpdb->prefix . "qlists";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
		`q_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`question` VARCHAR( 250 ) NOT NULL ,
		`q_order` INT NOT NULL
		);";
	
	// Creating Answers Table
	$answers_table_name = $wpdb->prefix . "qlists_answers";

		$sql .= "CREATE TABLE " . $answers_table_name . " (
			`a_id` INT NOT NULL AUTO_INCREMENT  PRIMARY KEY ,
			`q_id` INT NOT NULL ,
			`u_id` CHAR ( 17 ) NOT NULL ,
			`answer` INT NOT NULL
			);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
	}
		
		$client_qlist_db_version = get_option('qlist_db_version');
	if($client_qlist_db_version!=$qlist_db_version){
		$answers_table_name = $wpdb->prefix . "qlists_answers";
		$sql_up = "CREATE TABLE " . $answers_table_name . " (
			`a_id` INT NOT NULL AUTO_INCREMENT  PRIMARY KEY ,
			`q_id` INT NOT NULL ,
			`u_id` CHAR ( 17 ) NOT NULL ,
			`answer` INT NOT NULL
			);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql_up);
	}

	add_option("qlist_db_version", $qlist_db_version); // If the database is ever needed to be updated
}
/* Before this point is all the administration options
 * ----------------------------------------------------------------------------------------------
 * ----------------------------------------------------------------------------------------------
 * ----------------------------------------------------------------------------------------------
 * Voting System
 */

function show_questions($listclass='',$isecho=''){
	global $current_user,$wpdb;
	$user_id=$_SESSION['user_id'];
	if($listclass==''){$listclass="qlist";}
	if(!isset($user_id)){$formclick = 'onclick="alert(\'You need to have cookies enabled to vote in your browser!\')"';}
	$db_questions = $wpdb->get_results("SELECT * from ".$wpdb->prefix."qlists order by q_order,question");
	
	if(empty($db_questions)){return "";}
	$qlist = '<div class="graph"><a name="qlist" onclick="javascript:show_checked_qlist();">Switch View</a>';
	$qlist .= '<form method="post" action="" '.$formclick.'><dl>';
	$qlist .= '';
		foreach ($db_questions as $db_question) {$i++;
			if($wpdb->get_var("Select count(a_id) from ".$wpdb->prefix."qlists_answers where u_id='$user_id' and q_id='".$db_question->q_id."' and answer='1'")){$isyes = ' checked="checked"';$uncheckedclass='';}else{$isyes='';$uncheckedclass='unchecked_qlist';}
			$qlist.='<dt class="bar-title '.$uncheckedclass.'"><input type="checkbox" name="box'.$db_question->q_id.'"'.$isyes.'/>';
			$qlist.=$db_question->question;
			$qlist .='<strong>'.$res.'</strong></dt>';
			$res = get_qlist_result($db_question->q_id);
			$qlist .= '<dd class="bar-container '.$uncheckedclass.'"><div style="width:'.$res.'"></div></dd>';
			$ids .= "$db_question->q_id,";
		}
		$qlist .= '<dt class="bar-title"><input type="hidden" value="'.$ids.'" name="qlistanswers"/><input type="submit" value="Save" /></dt>';
		$qlist .= '</dl></form></div>
				';
	return $qlist;
}

function get_qlist_result($id){
	global $wpdb;
	$res = $wpdb->get_results("Select answer from ".$wpdb->prefix."qlists_answers where q_id='$id'");
	$a=$b=0;
	foreach ($res as $r) {
		if($r->answer=="1"){$a++;}
		$b++;
	}
	return number_format((($a/$b)*100),2)."%";
}


add_filter('the_content', 'add_qlist_to_content');  
function add_qlist_to_content($content){
	if($_POST['qlistanswers']){
		post_answers();
	}
	if(preg_match_all("[qlist]",$content,$matches)){
		$content= str_replace("[qlist]",show_questions(),$content);
	}
	return $content;
}

function post_answers(){
	global $wpdb;
	$user_id=$_SESSION['user_id'];
	if($user_id){
	$vars = $_POST;
	$i=0;
		while (list($key, $value) = each($vars)){
			if(preg_match("/box(\d+)/",$key)){
				$answers_checked[$i] = str_replace("box","",$key);
				$i++;
			}
		}
		$all_q = explode(",",$_POST['qlistanswers']);
			
		for($a=0;$a<(count($all_q)-1);$a++){
			if(in_array($all_q[$a],$answers_checked)){
				$answers[$a]["q_id"]=$all_q[$a];
				$answers[$a]["answer"] = "1";
			}else{
				$answers[$a]["q_id"]=$all_q[$a];
				$answers[$a]["answer"] = "0";
			}
		}
		
		for($a=0;$a<count($answers);$a++){
			if(!$wpdb->get_var("Select count(a_id) from ".$wpdb->prefix."qlists_answers where u_id='$user_id' and q_id='".$answers[$a]['q_id']."'")){
				$wpdb->insert($wpdb->prefix."qlists_answers",array('answer'=>$answers[$a]["answer"],'q_id'=>$answers[$a]["q_id"],'u_id'=>$user_id),array( '%d', '%d', '%s' ));
			}else{
				$wpdb->update($wpdb->prefix."qlists_answers",array('answer'=>$answers[$a]["answer"]),array( 'q_id' => $answers[$a]['q_id'],'u_id'=>$user_id ),array( '%d' ),array( '%d', '%s' ));
			}
		}
	}
}

add_filter('wp_footer','add_qlist_js');

function add_qlist_js(){
	echo '<script type="text/javascript">
		function show_checked_qlist(){
			$(".unchecked_qlist").slideToggle("slow");
		}
	</script>';
}

add_action('wp_head', 'add_qlist_styles');

function add_qlist_styles(){
	wp_register_style('qlist_create', WP_PLUGIN_URL.'/q-list-list-creator/q-list.css');
	wp_enqueue_style('qlist_create');
	wp_print_styles();
}

add_action('init', 'set_userID_qlist');

function set_userID_qlist(){
	$expire=time()+60*60*24*30*12;
	if(!session_id()){session_start();}
	if(isset($_COOKIE['user_id'])){
		$user_id=$_COOKIE['user_id'];
		$_SESSION['user_id']=$user_id;
	}else{
		$user_id = uniqid("qid-");
		setcookie("user_id", $user_id, $expire);
		$_SESSION['user_id']=$user_id;
	}
}

<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Crud_model extends CI_Model {

	function __construct() {
		parent::__construct();
	}

	function clear_cache() {
		$this -> output -> set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this -> output -> set_header('Pragma: no-cache');
	}

	function get_type_name_by_id($table_name, $primary_key = '', $field = 'name') {
		//return $this->db->get_where($type, array($type . '_id' => $type_id))->row()->$field;
		if ($this -> db -> get_where($table_name, array($table_name . '_id' => $primary_key)) -> num_rows() > 0) {
			return $this -> db -> get_where($table_name, array($table_name . '_id' => $primary_key)) -> row() -> $field;
		} else {
			return NULL;
		}
	}

	function get_field_value($type, $search_field = '', $search_value = 'name', $show_field) {
		//return $this->db->get_where($type, array($type . '_id' => $type_id))->row()->$field;
		if ($this -> db -> get_where($type, array($search_field => $search_value)) -> num_rows() > 0) {
			return $this -> db -> get_where($type, array($search_field => $search_value)) -> row() -> $show_field;
		} else {
			return NULL;
		}
	}

	public function get_results_by_id($type, $type_id = '') {
		$result = $this -> db -> get($type) -> result_object();

		if ($type_id !== "") {
			$result = $this -> db -> get_where($type, array($type . "_id" => $type_id)) -> row();
		}

		return $result;
	}

	public function get_results_by_related_id($type, $id_field, $id, $all = false) {

		$result = $this -> db -> get_where($type, array($id_field => $id)) -> row();
		if ($all === true) {
			$result = $this -> db -> get_where($type, array($id_field => $id)) -> result_object();
		}
		return $result;
	}

	/** PREVILEDGES **/

	function admin_user($user_id = "") {

		$is_admin = false;

		$this -> db -> where(array("type<>" => "vote", "user_id" => $user_id));

		$result = $this -> db -> get("scope") -> num_rows();

		if ($result > 0)
			$is_admin = true;

		return $is_admin;
	}

	/**
	 * Scope Countries: return Array of countries ids the user has ability to vote or be
	 * voted in or administration
	 *
	 * @param integer user_id
	 * @param boolean show_your_country
	 * @return Array of countries ids the user has ability to vote or be voted in or administration
	 */
	function scope_countries($user_id = "", $show_your_country = false) {
		//Retrieve a record showing users ability to vote/ voted/admininster other countries data
		// $this->db->join('scope_country','scope_country.scope_id=scope.scope_id');
		// $country_scope = $this->db->get_where("scope",array("user_id"=>$user_id));

		$country_scope_str = "SELECT * FROM scope JOIN scope_country ON scope.scope_id = scope_country.scope_id  WHERE user_id = " . $user_id;
		$country_scope = $this -> db -> query($country_scope_str);

		//Getting user resident country id
		$user_country_id = $this -> db -> get_where("user", array("user.user_id" => $user_id)) -> row() -> country_id;

		$country_ids = array();

		/**
		 * Add your resident country in the array $country_ids if the $show_your_country == true
		 */
		if ($show_your_country === true) {
			$country_ids[0] = $user_country_id;
		}

		//If $scope->num_rows() > 0, user has ability to vote or be voted by other country users

		if ($country_scope -> num_rows() > 0) {
			foreach ($country_scope->result_object() as $country) {
				$country_ids[] = $country -> country_id;
			}
		}

		return $country_ids;
	}

	function user_teams($user_id = "", $user_country_id = "") {

		$this -> db -> join("teamset", "teamset.team_id=team.team_id");
		$teams = $this -> db -> get_where("team", array("country_id" => $user_country_id, "user_id" => $this -> session -> login_user_id));

		return $teams -> num_rows() > 0 ? implode(",", array_column($teams -> result_array(), "name")) : get_phrase("teams_not_set");

	}

	function scope_countries_by_name($user_id = "", $show_your_country = false) {
		$scope = $this -> db -> get_where("scope", array("user_id" => $user_id)) -> row();

		$user_country_id = $this -> db -> get_where("user", array("user_id" => $user_id)) -> row() -> country_id;

		$country_ids = array();

		if ($show_your_country === true) {
			$country_ids[] = $this -> db -> get_where("country", array("country_id" => $user_country_id)) -> row() -> name;
		}

		if ($this -> db -> get_where("scope", array("user_id" => $user_id)) -> num_rows() > 0) {
			$this -> db -> join("country", "country.country_id=scope_country.country_id");
			$result = $this -> db -> get_where("scope_country", array("scope_id" => $scope -> scope_id));
			$countries = $result -> result_object();

			foreach ($countries as $country) {
				$country_ids[] = $country -> name;
			}

		}

		return $country_ids;
	}

	/**
	 * country_scope_where - Builds a condition string for a query that will be used to return
	 * countries of voters and admins
	 * @param integer user_id
	 * @param string scope_type
	 * @return String: ('country_id' = 1 or 'country_id'=>27 or 'country_id'=>n)
	 */

	function country_scope_where($user_id = "", $scope_type = "vote") {

		$country_ids = array();

		/**
		 * If the scope type == to admin, list only your country of residence.
		 * This is used in the nominate view to filter only users of the logged user contry during
		 * nomination and administrating your country's system settings.
		 * ELSE
		 * If scope type == both or vote, all the countries that you have a scope are listed.
		 * It's used in the nomination view to list all users from all contries.
		 * Both allows you to vote and do administration for the countries.
		 */

		if ($scope_type === "admin") {
			$country_ids[0] = $this -> session -> country_id;
		} else {

			$country_ids = $this -> crud_model -> scope_countries($user_id, true);
		}
		/**
		 * Building a condition string for a query based on scope
		 * countries set when the scope type is either admin or vote/both
		 */

		$scope_cond = '(';
		$cnt = 1;
		foreach ($country_ids as $country_id) {

			if ($cnt == count($country_ids)) {
				/**
				 * Turn true when your scope type is admin or last loop instance
				 * when count == size of array of country ids
				 */
				$scope_cond .= "country_id = '" . $country_id . "')";
			} elseif ($cnt < count($country_ids)) {
				/**
				 * Turn true when your scope type is vote/both but in the first to n-1 loop
				 */
				$scope_cond .= "country_id = '" . $country_id . "' or ";
			}

			$cnt++;
		}

		//$scope_cond = "(country_id = 25 or country_id=28)";

		return $scope_cond;

	}

	public function get_entitlement_by_profile_id($profile_id = "") {

		return $this -> db -> get_where("access", array("profile_id" => $profile_id)) -> result_object();

	}

	public function check_profile_privilege($profile_id, $privilege = "") {

		$entitlements = $this -> get_entitlement_by_profile_id($profile_id);

		$new_array = array();

		foreach ($entitlements as $entitlement) {
			$new_array[] = $this -> db -> get_where("entitlement", array("entitlement_id" => $entitlement -> entitlement_id)) -> row() -> name;
		}

		return in_array($privilege, $new_array) == "1" ? true : false;

	}

	function user_teams_to_vote($user_id = "") {

		/**
		 * A sub query string to get teams of the logged in user
		 */
		$logged_in_user_teams_sub_query = " team.team_id NOT IN (SELECT team_id FROM teamset WHERE user_id = $user_id)";

		/**
		 * Object returning teams in a country but not associated to the logged in user
		 */
		$this -> db -> select(array('user_id', 'team.team_id'));
		$this -> db -> join('teamset', 'teamset.team_id=team.team_id');
		$this -> db -> group_by('team.team_id');
		$this -> db -> where($logged_in_user_teams_sub_query);
		$country_teams = $this -> db -> get_where("team", array("team.country_id" => $this -> session -> country_id, "teamset.user_id<>" => $user_id));

		/**
		 * Building an array of team_ids and use them to build a where clause query string
		 */
		$team_ids_array = array();
		$scope_cond = "";

		if ($country_teams -> num_rows() > 0) {

			$team_ids_array = array_column($country_teams -> result_array(), "team_id");
			// Array of team_ids

			$scope_cond .= '(';
			$cnt = 1;
			foreach ($team_ids_array as $team_id) {
				if ($cnt === count($team_ids_array)) {
					$scope_cond .= "team_id = " . $team_id . ")";
				} elseif ($cnt !== count($team_ids_array)) {
					$scope_cond .= "team_id = " . $team_id . " or ";
				}

				$cnt++;
			}

		}

		return $scope_cond;
	}

	/**
	 * users_with_country_scope_for_voting: Returns a condition string for query with users ids
	 * @param Integer country_id
	 * @return String
	 */

	function users_with_country_scope_for_voting($country_id = "") {

		/**
		 * Get records from user ids from scope table.
		 * The users should have a contry scope equal to the passed argument in the scope country table
		 */
		$this -> db -> select(array('scope.user_id'));
		$this -> db -> join('user', 'user.user_id=scope.user_id');
		$this -> db -> join('scope_country', 'scope_country.scope_id=scope.scope_id');
		$user_ids = $this -> db -> get_where("scope", array("scope_country.country_id" => $country_id, "type<>" => "admin", 'user.auth' => 1));

		/**
		 * Loop the users as you build query string
		 */
		$scope_cond = "";

		if ($user_ids -> num_rows() > 0) {

			$scope_cond = '(';
			$cnt = 1;
			foreach ($user_ids->result_object() as $user) {
				if ($cnt === count($user_ids -> result_object)) {
					$scope_cond .= "user_id = " . $user -> user_id . ")";
				} elseif ($cnt !== count($user_ids -> result_object)) {
					$scope_cond .= "user_id = " . $user -> user_id . " or ";
				}

				$cnt++;
			}

		}

		return $scope_cond;
	}

	/**
	 * Scope categories_in_grouping: return Array of Categories grouped per grouping
	 * ex. Staff Category, Manager Recognition group
	 *
	 * @param integer $grouping_id
	 * @param integer $user_id
	 * @param integer $staff_position
	 * @return return Array of Categories grouped per grouping
	 * ex. Staff Category, Manager Recognition group
	 */

	function categories_in_grouping($user_id = "", $staff_position = "", $use_grouping_name = false) {

		$show_categories = array();

		$user_country = $this -> session -> country_id;

		/**
		 * A Where condintion string to be used in the db query
		 */
		$condition_string = " category.status = 1 AND category.visibility IN (1,$user_country)";

		/**
		 * Appends staff position to Where condintion string where the user is a staff
		 */
		if ($staff_position == 1) {
			$condition_string .= " AND category.assignment = $staff_position";
		}

		//A query to return categories
		$this -> db -> where($condition_string);
		$this -> db -> select(array('category_id', 'category.name', 'category.description', 'category.grouping_id', 'category.visibility', 'category.assignment', 'category.unit', 'category.status', 'grouping.name as grouping_name'));
		$this -> db -> join('grouping', 'grouping.grouping_id=category.grouping_id');
		$categories = $this -> db -> get("category");

		/**
		 * Checks if the there are categories and create an array of categories grouped by groups
		 */

		if ($categories -> num_rows() > 0) {
			//Get categories grouped in groups ex. Staff Categories
			if ($use_grouping_name) {
				foreach ($categories->result_object() as $category) {
					$show_categories[$category -> grouping_name][] = $category;
				}
			} else {
				foreach ($categories->result_object() as $category) {
					$show_categories[$category -> grouping_id][] = $category;
				}
			}

		}

		return $show_categories;
	}

	////////BACKUP RESTORE/////////
	function create_backup($type) {
		$this -> load -> dbutil();

		$options = array('format' => 'txt', // gzip, zip, txt
		'add_drop' => TRUE, // Whether to add DROP TABLE statements to backup file
		'add_insert' => TRUE, // Whether to add INSERT data to backup file
		'newline' => "\n" // Newline character used in backup file
		);

		if ($type == 'all') {
			$tables = array('');
			$file_name = 'system_backup';
		} else {
			$tables = array('tables' => array($type));
			$file_name = 'backup_' . $type;
		}

		$backup = &$this -> dbutil -> backup(array_merge($options, $tables));

		$this -> load -> helper('download');
		force_download($file_name . '.sql', $backup);
	}

	/////////RESTORE TOTAL DB/ DB TABLE FROM UPLOADED BACKUP SQL FILE//////////
	function restore_backup() {
		move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/backup.sql');
		$this -> load -> dbutil();

		$prefs = array('filepath' => 'uploads/backup.sql', 'delete_after_upload' => TRUE, 'delimiter' => ';');
		$restore = &$this -> dbutil -> restore($prefs);
		unlink($prefs['filepath']);
	}

	/////////DELETE DATA FROM TABLES///////////////
	function truncate($type) {
		if ($type == 'all') {
			$this -> db -> truncate('student');
			$this -> db -> truncate('mark');
			$this -> db -> truncate('teacher');
			$this -> db -> truncate('subject');
			$this -> db -> truncate('class');
			$this -> db -> truncate('exam');
			$this -> db -> truncate('grade');
		} else {
			$this -> db -> truncate($type);
		}
	}

	////////IMAGE URL//////////
	function get_image_url($id = '') {
		if (file_exists('uploads/user_image/' . $id . '.jpg'))
			$image_url = base_url() . 'uploads/user_image/' . $id . '.jpg';
		else
			$image_url = base_url() . 'uploads/user.jpg';

		return $image_url;
	}

	////////private message//////
	function send_new_private_message() {
		$message = $this -> input -> post('message');
		$timestamp = strtotime(date("Y-m-d H:i:s"));

		$reciever = $this -> input -> post('reciever');
		$sender = $this -> session -> userdata('login_type') . '-' . $this -> session -> userdata('login_user_id');

		//check if the thread between those 2 users exists, if not create new thread
		$num1 = $this -> db -> get_where('message_thread', array('sender' => $sender, 'reciever' => $reciever)) -> num_rows();
		$num2 = $this -> db -> get_where('message_thread', array('sender' => $reciever, 'reciever' => $sender)) -> num_rows();

		if ($num1 == 0 && $num2 == 0) {
			$message_thread_code = substr(md5(rand(100000000, 20000000000)), 0, 15);
			$data_message_thread['message_thread_code'] = $message_thread_code;
			$data_message_thread['sender'] = $sender;
			$data_message_thread['reciever'] = $reciever;
			$this -> db -> insert('message_thread', $data_message_thread);
		}
		if ($num1 > 0)
			$message_thread_code = $this -> db -> get_where('message_thread', array('sender' => $sender, 'reciever' => $reciever)) -> row() -> message_thread_code;
		if ($num2 > 0)
			$message_thread_code = $this -> db -> get_where('message_thread', array('sender' => $reciever, 'reciever' => $sender)) -> row() -> message_thread_code;

		$data_message['message_thread_code'] = $message_thread_code;
		$data_message['message'] = $message;
		$data_message['sender'] = $sender;
		$data_message['timestamp'] = $timestamp;
		$this -> db -> insert('message', $data_message);

		// notify email to email reciever
		//$this->email_model->notify_email('new_message_notification', $this->db->insert_id());

		return $message_thread_code;
	}

	function send_reply_message($message_thread_code) {
		$message = $this -> input -> post('message');
		$timestamp = strtotime(date("Y-m-d H:i:s"));
		$sender = $this -> session -> userdata('login_type') . '-' . $this -> session -> userdata('login_user_id');

		$data_message['message_thread_code'] = $message_thread_code;
		$data_message['message'] = $message;
		$data_message['sender'] = $sender;
		$data_message['timestamp'] = $timestamp;
		$this -> db -> insert('message', $data_message);

		// notify email to email reciever
		//$this->email_model->notify_email('new_message_notification', $this->db->insert_id());
	}

	function mark_thread_messages_read($message_thread_code) {
		// mark read only the oponnent messages of this thread, not currently logged in user's sent messages
		$current_user = $this -> session -> userdata('login_type') . '-' . $this -> session -> userdata('login_user_id');
		$this -> db -> where('sender !=', $current_user);
		$this -> db -> where('message_thread_code', $message_thread_code);
		$this -> db -> update('message', array('read_status' => 1));
	}

	function count_unread_message_of_thread($message_thread_code) {
		$unread_message_counter = 0;
		$current_user = $this -> session -> userdata('login_type') . '-' . $this -> session -> userdata('login_user_id');
		$messages = $this -> db -> get_where('message', array('message_thread_code' => $message_thread_code)) -> result_array();
		foreach ($messages as $row) {
			if ($row['sender'] != $current_user && $row['read_status'] == '0')
				$unread_message_counter++;
		}
		return $unread_message_counter;
	}

	/**USER PREVILEDGES**/

	function user_privilege($param1 = "", $privilege = "") {

		$user_previledges = array();
		$arr = $this -> db -> join("entitlement", "entitlement.entitlement_id=access.entitlement_id") -> get_where('access', array("profile_id" => $param1)) -> result_object();

		foreach ($arr as $row) {
			$user_previledges[] = $row -> name;
		}

		return in_array($privilege, $user_previledges) ? true : false;

	}

	/**
	 * list_potential_nominees_per_category: Lists users/Teams/department depending on the category table
	 * unit field
	 * @param String $unit_table_name
	 * @param Object $category
	 * @return Object - Holds potential_nominees
	 */

	function list_potential_nominees_per_category($unit_table_name, $category) {
		$this -> db -> join('scope_country', 'scope_country.scope_id=scope.scope_id');
		$scope = $this -> db -> get_where("scope", array("scope.user_id" => $this -> session -> login_user_id, 'two_way' => 1));

		/** Add Unit filter controls here - Start
		 * The below if block sets the where conditions to determine the users to be
		 * listed in  the dropdown in the GUI
		 * **/
		$result = array();
		if ($unit_table_name === "user") {
			$this -> db -> select(array('user.user_id', 'user.firstname', 'user.lastname', 'user.role_id', 'user.country_id', 'user.email', 'user.manager_id'));
			/** User Filters Set here
			 *
			 * Users cannot nominate themselves
			 * Cannot vote inactive users
			 * Users can only nominate users in the country and those assigned to the countries with Scope type of Both or Vote and Two Way set to Yes
			 * Users can nominate other country staff if have a scope of either Two Way set as yes or no and Type set as Voting.
			 *
			 * Prevent listing yourself to the moninate unit dropdown and Inactive Users
			 * Filter users from other countries with the current user country within their scope and of type not equal to admin and scope two way set as yes
			 * Show scoped users only for categories with visibility set as All i.e. 1
			 *
			 * Set Manager User List here. Find managers categories (assignemnt == 2 )
			 * and then set users as the nominees fot the manager**/
			if ($category -> assignment == '2') {
				/**
				 *--------------------------------------------------------------------------------------
				 *
				 * List staff for manager recognition based on the following manager types
				 *
				 *--------------------------------------------------------------------------------------
				 * */

				$result = $this -> staff_hierachy($this -> session -> login_user_id);

			} 
			else {
				
				// List all the query condition strings to be used to build the $query_condition_string variable
				
				// String for select other users other than the logged one
				$not_user_cond = "user.user_id <> " . $this -> session -> login_user_id;
				
				// String for selecting users of the same role
				$bt_users_cond = "role_id = ".$this -> session -> role_id;
				
				// String to select users who have a scope to be voted for in the logged user country
				$user_with_user_country_scope_cond = $this -> crud_model -> users_with_country_scope_for_voting($this -> session -> country_id);
				
				// String for selecting countries that the logged user has scope for to vote its staff
				$country_scope_for_user_cond = " country_id = ".$this->session->country_id;
				
				// Get countries the users can see staff to vote for if has scope to it and it's of type voting or both
				if ($scope -> num_rows() > 0) {
					$country_scope_for_user_cond = $this -> crud_model -> country_scope_where($this -> session -> login_user_id, $scope -> row() -> type);
				} 
				
				// Start building the query condition variables
				$query_condition_string = $not_user_cond." AND (".$country_scope_for_user_cond." ";
				
				
				//Adds users of BT not that in that country
				if($this->session->is_bt_role)
				{
					$query_condition_string.= " OR ".$bt_users_cond;
					
					
				}
				
				if ($category -> visibility === '1' && $user_with_user_country_scope_cond !== "" ) {
					$query_condition_string .= ' OR '.$user_with_user_country_scope_cond;
				}
				
				
				
				$query_condition_string .= ")";
				
				//echo $query_condition_string;
				
				$this -> db -> order_by("user.country_id,user.firstname");
				$this -> db -> where($query_condition_string);

				$result = $this -> db -> get($unit_table_name) -> result_object();

			}

		}

		if ($unit_table_name === "team") {
			/** Team Filters Set here
			 * A user can only nominate a team from his or her residence country
			 * A user is not allowed to nominate teams the belong to
			 *
			 * Only list the current users country teams
			 * If a user do not belong to any team, then get all the special teams
			 * in the country of residence.
			 */
			$special_team_query_string = " country_id = " . $this -> session -> country_id;
			$model_team_query_string = $this -> crud_model -> user_teams_to_vote($this -> session -> login_user_id);
			if ($model_team_query_string !== "") {
				$special_team_query_string = $model_team_query_string;
			}

			//Get only active teams
			$this -> db -> where(array('team.status' => 1));
			$this -> db -> order_by("name");
			$this -> db -> where($special_team_query_string);

			$result = $this -> db -> get($unit_table_name) -> result_object();
		}

		if ($unit_table_name === "department") {
			/** Department Filters Set here**/
			$user_department_id = $this -> db -> get_where("role", array("role_id" => $this -> session -> role_id)) -> row() -> department_id;
			$this -> db -> order_by("name");
			$this -> db -> where(array("department_id<>" => $user_department_id));
			$result = $this -> db -> get($unit_table_name) -> result_object();
		}

		/** Add Unit filter controls here - End **/

		return $result;
	}

	function nomination_units_select_field($unit_table_name, $category, $units, $results) {
		$options = '<select class="form-control nominate validate" id="' . $category -> category_id . '">';

		$options .= '<option value="0">' . get_phrase("no_viable_option") . '</option>';

		if (count($results) > 0) {
			$select_none_viable = "";
			foreach ($units as $unit) {
				$options_html = "";
				if ($unit_table_name === "user") {
					$options_html = $unit -> firstname . ' ' . $unit -> lastname . ' [' . $this -> crud_model -> get_type_name_by_id("country", $unit -> country_id) . ']';
				} else {
					$options_html = $unit -> name;
				}

				$val = $unit_table_name;
				$id = $unit_table_name . '_id';
				$show_choice = "";
				$selected = "";

				foreach ($results as $result) {
					if ($category -> category_id === $result -> category_id) {
						$unit_trace = $this -> crud_model -> get_type_name_by_id('unit', $result -> nominated_unit);
						if ($unit_trace === $unit_table_name && $result -> nominee_id !== '0') {
							$show_choice = $this -> crud_model -> get_type_name_by_id($unit_table_name, $result -> nominee_id, $id);
						} elseif ($unit_trace === $unit_table_name && $result -> nominee_id === '0') {
							$show_choice = '0';
							$select_none_viable = "selected='selected'";
						}
					}
				}

				if ($show_choice === $unit -> $id) {
					$selected = "selected='selected'";
				}

				$options .= '<option value="' . $unit -> $id . '" ' . $selected . '>' . $options_html . '</option>';
			}

			$options .= '<option value="0" ' . $select_none_viable . '>' . get_phrase("no_viable_option") . '</option>';
		} else {

			foreach ($units as $unit) {
				$options_html = "";
				if ($unit_table_name === "user") {
					$options_html = $unit -> firstname . ' ' . $unit -> lastname . ' [' . $this -> crud_model -> get_type_name_by_id("country", $unit -> country_id) . ']';
				} else {
					$options_html = $unit -> name;
				}

				$val = $unit_table_name;
				$id = $unit_table_name . '_id';
				$options .= '<option value="' . $unit -> $id . '">' . $options_html . '</option>';

			}

			$options .= '<option value="0">' . get_phrase("no_viable_option") . '</option>';
		}
		$options .= "</select>";

		return $options;
	}

	function get_team_name_of_the_logged_in_user() {
		$team_str = get_phrase("not_set");
		$this -> db -> join('teamset', 'teamset.team_id=team.team_id');
		$teamset = $this -> db -> get_where("team", array("user_id" => $this -> session -> login_user_id));

		if ($teamset -> num_rows() > 0) {
			$team_str = implode(',', array_column($teamset -> result_array(), 'name'));
		}

		return $team_str;
	}

	function get_scope_countries_names_of_logged_in_user() {
		$country_string = "";
		$this -> db -> select('country.name');
		$this -> db -> join('country', 'country.country_id=scope_country.country_id');
		$this -> db -> join('scope', 'scope.scope_id=scope_country.scope_id');
		$countries = $this -> db -> get_where("scope_country", array("scope.user_id" => $this -> session -> login_user_id));

		if ($countries -> num_rows() > 0) {
			$country_string = implode(",", array_column($countries -> result_array(), 'name'));
		}

		return $country_string;
	}

	function get_user_scope_country_array($user_id) {

		$country_array = array();

		$this -> db -> select(array('country.country_id', 'country.name'));
		$this -> db -> join('country', 'country.country_id=scope_country.country_id');
		$this -> db -> join('scope', 'scope.scope_id=scope_country.scope_id');
		$countries = $this -> db -> get_where("scope_country", array("scope.user_id" => $user_id, 'country.name<>' => 'All')) -> result_object();

		$this -> db -> select(array('country.country_id', 'country.name'));
		$this -> db -> join('country', 'country.country_id=user.country_id');
		$user_country = $this -> db -> get_where('user', array('user.user_id' => $user_id)) -> row();

		$country_array[$user_country -> country_id] = $user_country -> name;

		foreach ($countries as $country) {
			$country_array[$country -> country_id] = $country -> name;
		}

		return $country_array;

	}

	function staff_hierachy($manager_id) {

		$this -> db -> select(array('user_id', 'firstname', 'lastname', 'country_id'));
		$first_level_reportees = $this -> db -> get_where('user', array('manager_id' => $manager_id, 'auth' => 1)) -> result_array();

		$hierachy[] = $first_level_reportees;
		//Ex. Sr Mgr Prgm Support/ Sr Mgr Partnership

		foreach ($first_level_reportees as $first_level_user) {

			if ($this -> session -> last_line_manager == 2)
				break;

			$this -> db -> select(array('user_id', 'firstname', 'lastname', 'country_id'));
			$second_level_reportees = $this -> db -> get_where('user', array('manager_id' => $first_level_user['user_id'], 'auth' => 1)) -> result_array();
			foreach ($second_level_reportees as $user) {
				if (count($second_level_reportees) > 0)
					array_push($hierachy[0], $user);
				else
					break;
			}

			foreach ($second_level_reportees as $second_level_user) {
				$this -> db -> select(array('user_id', 'firstname', 'lastname', 'country_id'));
				$third_level_reportees = $this -> db -> get_where('user', array('manager_id' => $second_level_user['user_id'], 'auth' => 1)) -> result_array();
				foreach ($third_level_reportees as $user) {
					if (count($third_level_reportees) > 0)
						array_push($hierachy[0], $user);
					else
						break;
				}

				foreach ($third_level_reportees as $third_level_user) {
					$this -> db -> select(array('user_id', 'firstname', 'lastname', 'country_id'));
					$forth_level_reportees = $this -> db -> get_where('user', array('manager_id' => $third_level_user['user_id'], 'auth' => 1)) -> result_array();
					foreach ($forth_level_reportees as $user) {
						if (count($forth_level_reportees) > 0)
							array_push($hierachy[0], $user);
						else
							break;
					}

					foreach ($forth_level_reportees as $forth_level_user) {
						$this -> db -> select(array('user_id', 'firstname', 'lastname', 'country_id'));
						$fifth_level_reportees = $this -> db -> get_where('user', array('manager_id' => $forth_level_user['user_id'], 'auth' => 1)) -> result_array();
						foreach ($fifth_level_reportees as $user) {
							if (count($fifth_level_reportees) > 0)
								array_push($hierachy[0], $user);
							else
								break;
						}

					}
				}
			}

		}

		$finale = array();

		foreach ($hierachy[0] as $item) {
			$finale[] = (object)$item;
		}

		return $finale;
	}

	function auto_create_new_survey() {

		$survey = $this -> db -> get_where('survey', array('status' => 1));

		$post_array = array();

		if ($survey -> num_rows() > 0) {
			$update_data['status'] = 0;
			$this -> db -> where(array('status' => 1));
			$this -> db -> update('survey', $update_data);

			$post_array['start_date'] = date("Y-m-d");

			$post_array['country_id'] = 1;
			$post_array['allow_user_edit'] = 1;
			$post_array['action_on_active_votes'] = 2;
			$post_array['status'] = 1;
			$post_array['created_by'] = 0;
			$post_array['created_date'] = date("Y-m-d h:i:s");
			$post_array['last_modified_by'] = 0;
			$post_array['last_modified_date'] = date("Y-m-d h:i:s");

			$survey_duration = $this -> db -> get_where('settings', array('type' => 'survey_duration')) -> row() -> description;

			$post_array['end_date'] = date("Y-m-d", strtotime('+' . $survey_duration . ' months'));

			$this -> db -> insert("survey", $post_array);

			//Check any submitted vote and submit it
			$submit_data['status'] = 1;
			$this -> db -> where(array('status' => 0));
			$this -> db -> update('result', $submit_data);

			$this -> email_model -> send_batch_emails('survey_invite');
		}

	}

	/*This a private method gets user_ids of managers in country of logged in user
	 * Pamerater: takes no parameters
	 * Return value: result_object
	 * Authors: Karisa & Onduso
	 *
	 */

	private function country_user_manager() {

		//Get distinct the manager ids of the users

		return $this -> db -> select('distinct(manager_id)') -> get_where('user', array('country_id' => $this -> session -> country_id, 'manager_id<>' => 0)) -> result_object();
	}

	/*This a public method gets all managers
	 * Pamerater: takes no parameters
	 * Return value: array
	 * Authors: Karisa & Onduso
	 *
	 */
	function get_managers() {

		//Get all users in the country
		$country_users_managers = $this -> country_user_manager();

		//Get a string of all manager ids separated by commas
		$manager_ids_array = array_column($country_users_managers, 'manager_id');

		//Filter managers resident in the country
		// Retrieve the details of the managers (department, firstname, lastname, department_id)
		$this -> db -> select(array('user_id', 'firstname', 'lastname', 'department.department_id as department_id', 'department.name as department'));
		$this -> db -> where_in('user_id', $manager_ids_array);
		$this -> db -> join('role', 'role.role_id=user.role_id');
		$this -> db -> join('department', 'department.department_id=role.department_id');
		$managers_in_the_country = $this -> db -> get_where('user', array('country_id' => $this -> session -> country_id)) -> result_object();

		//Group the details by department
		$managers_in_the_country_grouped_by_department = array();
		foreach ($managers_in_the_country as $manager) {
			$managers_in_the_country_grouped_by_department[$manager -> department_id][$manager -> user_id] = substr(trim($manager -> firstname), 0, 1) . '.' . $manager -> lastname . '\'s Team (' . $manager -> department . ')';
		}

		return $managers_in_the_country_grouped_by_department;
	}

}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Goal extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
    } 

	public function index()
	{
        //$this->load->view('backend/goal/post_goal');
	}
    public function post_goal(){
        // if ($this->session->userdata('user_login') != 1)
        // redirect(base_url(), 'refresh');


    /**Instatiate CRUD**/
    //     $crud = new grocery_CRUD();

    //    //$crud->set_theme('flexigrid');//flexigrid


	// 	/** Grid Subject **/
	// 	$crud->set_subject('Add Goal');

	// 	/**Select Category Table**/
	// 	$crud->set_table('goal');

    //     $crud->columns(array('goal_name','goal_start_date','goal_end_date'));

    //     $output = $crud->render();
	// 	$page_data['view_type']  = "post_goal";
	// 	$page_data['page_name']  = __FUNCTION__;
    //     $page_data['page_title'] = __FUNCTION__;
	// 	$output = array_merge($page_data,(array)$output);
    
     

      $data['goal_name']= $this->input->post('goalname');
      $data['theme_id']= $this->input->post('theme');
      $data['goal_start_date']= $this->input->post('startdate');
      $data['goal_end_date']= $this->input->post('enddate');

      $data['user_id']= 1;

      $data['goal_created_by']= 1;

      $data['goal_created_date']= date('y-m-d');

      $data['goal_last_modified_date']= date('Y-m-d h:i:s');;
      $data['goal_last_modified_by']= 1;

      //echo json_encode($data);exit;
      if($this->db->insert('goal',$data)){
          echo "Record Inserted";
      }else{
          echo "Record Failed to insert";

          print_r( $this->db->error());
      }


    }
    public function add_goal(){

        $this->load->view('backend/goal/add_goal');

    }
    public function list_goals($goal_id=''){
        
    }

    public function delete_goal($goal_id){
        
    }

    public function update_goal($goal_id){
        
    }
}

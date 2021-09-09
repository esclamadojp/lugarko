<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class New_Resident extends CI_Controller {
	/**
	*/
	private function modulename($type)
	{		
		if($type == 'link')
			return 'new_resident';
		else 
			return 'New Resident';
		#change on resident file...
		#changes on github again 1 - should be visible
	}

	public function index(){
		$this->load->helper('common_helper');
		$this->load->view($this->modulename('link').'/index');
		$this->load->view('templates/footer');
		#This is a test edit.
		#changes on github again 2
		#my changes on github desktop B - should be visible
	}

	public function userauthentication() 
	{
		try 
		{
			$user_name	= mysql_real_escape_string(strip_tags(trim($this->input->post('user_name'))));
			$password	= mysql_real_escape_string(strip_tags(trim($this->input->post('password'))));
			$type		= $this->input->post('type');
			
			if ($type == 'Resident')
			{
				$this->load->model('Cipher');
				$this->Cipher->secretpassphrase();			
				$encryptedtext = $this->Cipher->encrypt($password);

				$commandText = "SELECT 	
									a.id,
									a.admin,
									a.user_id,
									a.username,							    
									'Applicant' AS department_description,
								    CONCAT(b.fname, ' ', b.mname, ' ', b.lname) AS sname
								FROM users a 
									LEFT JOIN applicant_accounts b ON a.user_id = b.id
								WHERE a.username = '".mysql_real_escape_string($user_name)."' 
								    AND a.password = '$encryptedtext'
								    AND a.active = 1
								    AND a.type ='$type'";
				$result = $this->db->query($commandText);
				$query_result = $result->result(); 

				if(count($query_result) == 0) 
				{
					$this->load->library('session');
					$commandText = "insert into audit_logs (transaction_id, transaction_type, query_type, date_created, time_created) values (0, 'Failed Attempt! (Username:".mysql_real_escape_string($user_name).")', 'Login', '".date('Y-m-d')."', '".date('H:i:s')."')";
					$result = $this->db->query($commandText);
					$data = array("success"=> false, "data"=>"Username not found! Please contact system administrator.");
					die(json_encode($data));
				}

				#set session
				$this->load->library('session');

				$newdata = array(
					'id'			=> $query_result[0]->id,
					'admin'			=> null,
					'user_id'		=> $query_result[0]->user_id,
					'un'			=> $query_result[0]->username,
					'name'  		=> strtoupper($query_result[0]->sname),
					'department_description'=> $query_result[0]->department_description,
					'type'			=> $type,
					'logged_in' 	=> TRUE,
					'time' 			=> date('Y-m-d H:i:s')
				);
				$this->session->set_userdata($newdata);

				$route = "thumbnailmenu";	 

				$this->load->model('Logs'); $this->Logs->audit_logs(0, 'login', 'Login', 'Successfully Login!');
			}
			else
			{
				$this->load->model('Cipher');
				$this->Cipher->secretpassphrase();			
				$encryptedtext = $this->Cipher->encrypt($password);

				$commandText = "SELECT 	
									a.id,
									a.admin,							    
									a.user_id,
									a.username,	
								    b.department_id,
								    c.description AS department_description,
								    CONCAT(b.fname, ' ', b.mname, ' ', b.lname) AS sname
								FROM users a 
									JOIN staff b ON a.user_id = b.id
								    JOIN departments c ON b.department_id = c.id 
								WHERE a.username = '".mysql_real_escape_string($user_name)."' 
								    AND a.password = '$encryptedtext'
								    AND a.active = 1
								    AND a.type ='$type'";
				$result = $this->db->query($commandText);
				$query_result = $result->result(); 

				if(count($query_result) == 0) 
				{
					$this->load->library('session');
					$commandText = "insert into audit_logs (transaction_id, transaction_type, query_type, date_created, time_created) values (0, 'Failed Attempt! (Username:".mysql_real_escape_string($user_name).")', 'Login', '".date('Y-m-d')."', '".date('H:i:s')."')";
					$result = $this->db->query($commandText);
					$data = array("success"=> false, "data"=>"Username not found! Please contact system administrator.");
					die(json_encode($data));
				}

				#set session
				$this->load->library('session');

				$newdata = array(
					'id'			=> $query_result[0]->id,
					'admin'			=> $query_result[0]->admin,
					'user_id'		=> $query_result[0]->user_id,
					'un'			=> $query_result[0]->username,
					'name'  		=> strtoupper($query_result[0]->sname),
					'department_id'	=> $query_result[0]->department_id,
					'department_description'=> $query_result[0]->department_description,
					'type'			=> $type,
					'logged_in' 	=> TRUE,
					'time' 			=> date('Y-m-d H:i:s')
				);
				$this->session->set_userdata($newdata);

				$route = "thumbnailmenu";	 

				$this->load->model('Logs'); $this->Logs->audit_logs(0, 'login', 'Login', 'Successfully Login!');
			}

			$arr = array();  
			$arr['success'] = true;
			$arr['data'] = $route;
			$arr['name'] = strtoupper($query_result[0]->sname);
			die(json_encode($arr));
		}
		catch(Exception $e) 
		{
			$data = array("success"=> false, "data"=>$e->getMessage());
			die(json_encode($data));
		}
	}
}

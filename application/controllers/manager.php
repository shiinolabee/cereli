<?php
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	class Manager extends CI_Controller{
	
		private $emp_id;
		public $search_contents = '';
		public $current_page = null;
		public $current_date = null;
		public $has_search_filter = array('employees','reports');
		public $base_url;
		public $params,$employee_actions,$report_actions,$action_type,$total_employees,$get_available_employee_fullnames,$get_available_employee_ids,$get_available_employee_positions;
		public $system_version,$import_results = array(),$company_time_record_rules,$search_contents_home,$logs_settings_actions,$recent_activities_limit,$recent_activities_offset,$employee_limit,$employee_offset;
		protected $view_listing_type='tabs';		
		
		public function __construct(){
		
			parent::__construct();
			date_default_timezone_set('Asia/Manila');
			$this->base_url = '';
			$this->load->database();
			$this->load->library('pagination');
			$this->load->model(array('TimeManager')); // require the model TimeManger class
			$this->process_system_settings();
		}
		
		public function process_system_settings($return = false)
		{
			$get_system_details = $this->db->query("SELECT * FROM company_details");
			
			if($get_system_details->num_rows() > 0){				
				$return_array = array();				
				foreach($get_system_details->result() as $key => $row){
					$this->system_version = $row->system_version;	
					$return_array[$key] = $row;				
				}					
				if($return !== false){
					return $return_array;
				}			
			}
		}
		
		public function _remap($method,$params = array())
		{
			$method = 'process_'.$method;
			if(method_exists($this,$method))
				return call_user_func_array(array($this,$method),$params);
			show_404();
		}
		
		public function process_index($offset=10,$start=0)
		{
			$this->process_default();				
			$this->recent_activities_limit = $start;
			$this->recent_activities_offset = $offset;
			$this->process_logs_home();
			
			$this->initializePagination('/manager/index/'.$offset,$this->db->query("SELECT * FROM employee_activities")->num_rows(),$offset);	
			$this->load->view('index',$this->params);
		}		

		public function process_logs_home()
		{
			$this->logs_settings_actions = '
				<div class="logs-pagination-container">
					<label>Logs per page : </label>
					<select class="action-selector" onchange="Manager.__record_per_page_action(this,\'home\');">						
						<option value="10" '.(isset($this->recent_activities_offset) && $this->recent_activities_offset == 10 ? 'selected="selected"' : '').'>10</option>
						<option value="25" '.(isset($this->recent_activities_offset) && $this->recent_activities_offset == 25 ? 'selected="selected"' : '').'>25</option>										
						<option value="50" '.(isset($this->recent_activities_offset) && $this->recent_activities_offset == 50 ? 'selected="selected"' : '').'>50</option>										
						<option value="100" '.(isset($this->recent_activities_offset) && $this->recent_activities_offset == 100 ? 'selected="selected"' : '').'>100</option>										
					</select>	
				</div>
			';
			
			$get_company_details = $this->process_system_settings(true);
			
			if(count($get_company_details) > 0){
				$this->company_detail_contents = '
						<div class="company-details-container">
							<form action="" method="post" name="company_details" onsubmit="return false;">
						';	
				foreach($get_company_details as $key => $value){
						$this->company_detail_contents .= '
							<div class="company-field-container">
								<label title="Name of the company(double-click to edit)" ondblclick="javascript:Manager.__action_company_details(\'edit\',\'company_name\');">Company Name :</label>
								<input type="hidden" name="id" value="'.$value->id.'"/>
								<input type="text" id="company_name" name="company_name" onchange="Manager.__action_company_details(\'set\',\'company_name\');" value="'.$value->company_name.'" disabled="disabled"/>
								<span class="company-details-save-btn" id="company-save-btn-company_name" onclick="Manager.__action_company_details(\'save\');"><img src="'.base_url().'/resources/images/save.png" title="Save changes"/></span>
								<span class="company-details-cancel-btn" id="company-cancel-btn-company_name" onclick="Manager.__action_company_details(\'cancel\',\'company_name\');"><img src="'.base_url().'/resources/images/delete.png" title="Cancel editing"/></span>
							</div>
							
							<div class="company-field-container">
								<label title="Current address of the company.(double-click to edit)" ondblclick="javascript:Manager.__action_company_details(\'edit\',\'company_address\');">Company Address:</label>
								<input type="text" id="company_address" name="company_address" onchange="Manager.__action_company_details(\'set\',\'company_address\');" value="'.$value->company_address.'" disabled="disabled"/>
								<span class="company-details-save-btn" id="company-save-btn-company_address" onclick="Manager.__action_company_details(\'save\');"><img src="'.base_url().'/resources/images/save.png" title="Save changes"/></span>
								<span class="company-details-cancel-btn" id="company-cancel-btn-company_address" onclick="Manager.__action_company_details(\'cancel\',\'company_address\');"><img src="'.base_url().'/resources/images/delete.png" title="Cancel editing"/></span>
							</div>
							
							<div class="company-field-container">
								<label title="Contact no. of the company.(double-click to edit)" ondblclick="Manager.__action_company_details(\'edit\',\'contact_no\');">Contact no. :</label>
								<input type="text" id="contact_no" name="contact_no" onchange="Manager.__action_company_details(\'set\',\'contact_no\');" value="'.$value->contact_no.'" disabled="disabled"/>
								<span class="company-details-save-btn" id="company-save-btn-contact_no" onclick="Manager.__action_company_details(\'save\');"><img src="'.base_url().'/resources/images/save.png" title="Save changes"/></span>
								<span class="company-details-cancel-btn" id="company-cancel-btn-contact_no" onclick="Manager.__action_company_details(\'cancel\',\'contact_no\');"><img src="'.base_url().'/resources/images/delete.png" title="Cancel editing"/></span>
								<div class="time_record_rules_message_content"></div>	
							</div>

							<div class="company-field-container">
								<label title="The Site version/System version.(double-click to edit)" ondblclick="javascript:Manager.__action_company_details(\'edit\',\'system_version\');">System version :</label>
								<input type="text" id="system_version" name="system_version" onchange="Manager.__action_company_details(\'set\',\'system_version\');" value="'.$value->system_version.'" disabled="disabled"/>
								<span class="company-details-save-btn" id="company-save-btn-system_version" onclick="Manager.__action_company_details(\'save\');"><img src="'.base_url().'/resources/images/save.png" title="Save changes"/></span>
								<span class="company-details-cancel-btn" id="company-cancel-btn-system_version" onclick="Manager.__action_company_details(\'cancel\',\'system_version\');"><img src="'.base_url().'/resources/images/delete.png" title="Cancel editing"/></span>
							</div>
							
							<div class="company-field-container">
								<label title="The company status(Active/Inactive).(double-click to edit)" ondblclick="Manager.__action_company_details(\'edit\',\'company_status\');">Company status :</label>
								<input type="text" id="company_status" name="company_status" onchange="Manager.__action_company_details(\'set\',\'company_status\');" value="'.$value->company_status.'" disabled="disabled"/>
								<span class="company-details-save-btn" id="company-save-btn-company_status" onclick="Manager.__action_company_details(\'save\');"><img src="'.base_url().'/resources/images/save.png" title="Save changes"/></span>
								<span class="company-details-cancel-btn" id="company-cancel-btn-company_status" onclick="Manager.__action_company_details(\'cancel\',\'company_status\');"><img src="'.base_url().'/resources/images/delete.png" title="Cancel editing"/></span>
								<div class="company_details_message_content"></div>	
							</div>	
						';
				}				
				$this->company_detail_contents .= '
							</form>
						</div>
				';
			}
			
		}
		
		public function process_employees($offset=5,$start=0)
		{
			$this->process_default();			
			$this->employee_limit = $start;
			$this->employee_offset = $offset;
			$this->process_employee_actions();
			
			$this->initializePagination('/manager/employees/'.$offset,$this->db->query("SELECT * FROM employees")->num_rows(),$offset);
			$this->load->view('employees');
		}
		
		public function process_reports()
		{
			$this->process_default();
			$this->process_report_actions();			
			$this->load->view('reports');
		}
		
		public function process_report_actions()
		{			
			$this->process_search_contents();
			$this->employee_actions = '
				<div class="action-holder">
					<div class="action-container">
						<label>Choose action(import/export employee time records) : </label>
						<select class="action-selector" onchange="Manager.__select_action(this);">
							<option value="" selected="selected">----</option>
							<option value="export">Export</option>
							<option value="import">Import</option>
						</select>
						<span><a id="go_action" href="javascript:void(0);" onclick="Manager.__action(this);">Go</a></span>
						<span><a href="javascript:void(0);" onclick="Manager.__delete_all_employee_time_records(this);">Delete All Employee Time Records</a></span>
						<div class="pagination-container">
							<label>Records per page : </label>
							<select class="action-selector" onchange="Manager.__record_per_page_action(this,\'reports\');">
								<option value="5" selected="selected">5</option>
								<option value="10">10</option>
								<option value="25">25</option>										
								<option value="50">50</option>										
								<option value="100">100</option>										
							</select>						
						</div>						
						<br/>
						<label>Select type calendar search : </label>
						<select class="action-selector" onchange="Manager.__select_calendar_type_action(this);">
							<option value="" selected="selected">----</option>
							<option value="default">Default(Single date)</option>
							<option value="from-to">Date range search</option>
						</select>						
					</div>							
				</div>
			';
		
		}
		
		public function process_employee_actions(){		
		
			$this->employee_actions = '
						<div class="action-holder">
							<div class="action-container">
								<label>Choose action(import/export employee time records) : </label>
								<select class="action-selector" onchange="Manager.__select_action(this);">
									<option value="" selected="selected">----</option>
									<option value="export">Export</option>
									<option value="import">Import</option>
								</select>
								<span><a id="go_action" href="javascript:void(0);" onclick="Manager.__action(this);">Go</a></span>
								<span><a href="javascript:void(0);" onclick="Manager.__delete_all_employee_time_records();">Delete All Employee Time Records</a></span>
								<div class="pagination-container">
									<label>Change view listing: </label>
									<select class="action-selector" onchange="Manager.__change_view_listing(this);">
										<option value="table" '.(isset($this->view_listing_type) && $this->view_listing_type == 'table' ? 'selected="selected"' : '').'>Table Listing</option>
										<option value="tabs" '.(isset($this->view_listing_type) && $this->view_listing_type == 'tabs' ? 'selected="selected"' : '').'>Details listing(by Tabs)</option>
									</select>
									<br/>
									<label class="record-per-page-label">Records per page : </label>
									<select class="action-selector" onchange="Manager.__record_per_page_action(this,\'employees\');">
										<option value="2" '.(isset($this->employee_offset) && $this->employee_offset == 2 ? 'selected="selected"' : '').'>2</option>
										<option value="5" '.(isset($this->employee_offset) && $this->employee_offset == 5 ? 'selected="selected"' : '').'>5</option>
										<option value="10" '.(isset($this->employee_offset) && $this->employee_offset == 10 ? 'selected="selected"' : '').'>10</option>
										<option value="25" '.(isset($this->employee_offset) && $this->employee_offset == 25 ? 'selected="selected"' : '').'>25</option>										
										<option value="50" '.(isset($this->employee_offset) && $this->employee_offset == 50 ? 'selected="selected"' : '').'>50</option>										
										<option value="100" '.(isset($this->employee_offset) && $this->employee_offset == 100 ? 'selected="selected"' : '').'>100</option>										
									</select>	
								</div>
								<div class="message_content"></div>
								<div class="import-container">
									<form action="'.$this->base_url.'/manager/action/import/" id="form-import" method="post" enctype="multipart/form-data">
										<label>Select a file :</label>
										<input type="file" name="import-file" id="import-file"/>
										<span class="file-input-label"></span>
										<output id="list"></output>
										<input type="submit" id="import-submit"value="Import selected file">
									</form>
									<div class="import-progress">
										<div class="import-bar"></div >
										<div class="percent"></div >
									</div>
								</div>
								<div class="select-export">
									<div id="export-type">	
										<input type="radio" name="type" id="pdf"/><span><img src="'.$this->base_url.'/resources/images/pdf.png" style="width:16px;height:16px;" alt="pdf"/></span><label for="pdf"> pdf format(for Reports)</label>
										<input type="radio" name="type" id="csv" checked="checked"/><span><img src="'.$this->base_url.'/resources/images/csv.png" alt="csv"/></span><label for="csv"> csv format(for Excel application) </label>
									</div>
									<div class="trigger-select-export-container">
										<div class="trigger-pdf-option">
										</div>
										<div class="trigger-csv-option">
											<label>Choose type of exporting : </label>
											<select class="action-selector" id="export_type" onchange="Manager.__change_type_exporting(this);">
												<option value="single" selected="selected">Single</option>												
												<option value="multiple">Multiple</option>
											</select>
											<span class="span_select_all_employees"><input type="checkbox" value="all_employees" name="select_all_employees" style="width:25px;"/><label class="lbl-select-all-employees">Select all employees</label></span>
											<br/>	
											<label>Choose date for exporting : </label>
											<input type="text" readonly="readonly" id="trigger-csv-option-datepicker-from" placeholder="From"/>
											<input type="text" readonly="readonly" id="trigger-csv-option-datepicker-to" placeholder="To"/>
										</div>
									</div>
									<div class="select-export-employees note"><span><b>Note :</b> Only by 1 employee per export.</span></div>
								</div><br/>
								<label>Select type of calendar search : </label>
								<select class="action-selector" onchange="Manager.__select_calendar_type_action(this);">
									<option value="" selected="selected">----</option>
									<option value="default">Default(Single date)</option>
									<option value="from-to">Date range search</option>
								</select>																
							</div>							
						</div>
						<div class="calendar-container">
							<div class="calendar-types" id="calendar-option-1">	
								<input type="text" readonly="readonly" id="open-calendar" onchange="Manager.__calendar_search(this);" placeholder="Open Calendar"/>
								<div class="calendar-note note"><b>Note :</b> When date is put, system will find results of employee\'s time records on the table.</div>
							</div>
							<div class="calendar-types" id="calendar-option-2">
								<input type="text" readonly="readonly" id="open-calendar-from" onchange="Manager.__calendar_search_range(this,\'from\');" placeholder="From"/>
								<input type="text" readonly="readonly" id="open-calendar-to" onchange="Manager.__calendar_search_range(this,\'to\');" placeholder="To"/>
								<span><a href="javascript:void(0);" onclick="Manager.__calendar_submit_range();">Filter</a></span>
								<div class="calendar-note note"><b>Note :</b> When date is put, system will find results of employee\'s time records on the table.</div>
							</div>								
						</div>
					';
						
			$this->get_available_employee_ids = $this->TimeManager->getAvailableRecords('emp_id');
			$this->get_available_fullnames = $this->TimeManager->getAvailableRecords('fullname');
			$this->get_available_positions = $this->TimeManager->getAvailableRecords('position');					
			
		}
		
		public function process_time_record_details($type,$id,$emp_id=null)
		{
			if(strlen($type) > 0 && !empty($type)){
				if(strlen($id) > 0)				
					$result = $this->TimeManager->actionEmployeeTimeRecord($id,$type,$emp_id);
			}
			echo json_encode($result);			
		}
		
		public function process_employee_time_record($params=null,$start=0,$offset=5)
		{
			$params = sizeof(explode(',',$params)) > 1 ? explode(',',$params) : $params;
			$this->emp_id = is_array($params) && !is_null($params)? $this->param_encode_to_array($params) : $params;
			
			if(isset($_REQUEST['data'])){
				$request = explode('/',$_REQUEST['data']);
				$total_request = count($request);
				
				if($total_request > 1){
					$date = $request[0];
					$compare_data = $request[1];					
				}else{
					$date = $_REQUEST['data'];
					$compare_data = null;	
				}
			}
			
			if(isset($_REQUEST['view_type'])){
			
				if($_REQUEST['view_type'] == 'table'){
					$result = $this->TimeManager->getEmployeeTimeRecord($this->emp_id,$start,$offset,$date,$compare_data);			
				}else if($_REQUEST['view_type'] == 'tabs'){
					$result = $this->TimeManager->getEmployeeTimeRecordTabsFormat($this->emp_id,$start,$offset,$date,$compare_data);									
				}				
				echo json_encode($result);				
			}
			
		}
		
		public function process_employee_attended_dates($emp_id)
		{
			if(strlen($emp_id) > 0 && isset($emp_id)){
				$result = $this->TimeManager->getEmployeeAttendedDates($emp_id);
				echo json_encode($result);
			}else
				echo json_encode(array('error'=>'no time record details recorded.'));
		}
		
		public function process_employee_time_record_details($emp_id=null)
		{
			if(isset($emp_id) && !is_null($emp_id)){			
				$result = $this->TimeManager->getEmployeeTimeRecordDetails($emp_id);												
				echo $result;						
			}
		}
		
		public function process_employee_change_thumbnail($type,$id)
		{
			if(!is_null($type) && !is_null($id)){
				switch($type){
					case 'set' : 	$result = $this->TimeManager->getEmployeeChangeThumbnailForm($id);
									echo json_encode($result);
									break;
					case 'change' : $data = isset($_REQUEST['thumbnail-file']) ? $_REQUEST['thumbnail-file'] : null;
									$result = $this->TimeManager->saveEmployeeThumbnail($data,$_REQUEST['emp_id']);
									echo json_encode($result);
									break;
				}
				return false;
			}	
		}
		
		public function process_employee_details($id){
			$id = !is_null($id) ? $id : null;
			
			if(!is_null($id)){
				$result = $this->TimeManager->getEmployeeDetails($id);			
			}
			
			echo json_encode($result);
		}
		
		public function param_encode_to_array($params)
		{
			$temp = array();
			if(is_array($params) && sizeof($params) > 0){
				for($i=0;$i<sizeof($params);$i++){
					$temp[] = $params[$i]; 
				}				
				return $temp;
			}
			return false;
		}
		
		public function process_create_employee_time_record($type,$id=null,$fullname=null,$date=null,$remarks=null,$status_type=null)
		{
			if(isset($type) && !is_null($type)){
				$result = $this->TimeManager->getCreateEmployeeTimeRecordAction($type,$id,$fullname,$date,$remarks,$status_type);
				echo json_encode($result);			
			}
			
			return false;
			
		}
		
		public function process_action_employee_time_records($type=null)
		{
			if(!is_null($type)){
				switch($type){
					case 'delete_all' : $result = $this->db->query('TRUNCATE TABLE emp_time_record');
									if($result)
										echo json_encode(array('status'=>'All employee time records are deleted.'));
				}	
			}
			
			return false;
		}
		
		public function process_action($action_type,$type=null,$emp_id=null,$params=null,$start=0,$offset=5)
		{
			$this->action_type = $action_type;
			$this->emp_id = !empty($emp_id) ? $emp_id : null;
			$this->type = !empty($type) ? $type : 'csv';
			
			switch($this->action_type){
				case 'export' : $result = !is_null($this->emp_id) ? $this->TimeManager->exportRecord($this->emp_id,$this->type,$params) : $this->TimeManager->exportRecord(null,$this->type,$params);
								break;
				case 'import' : $result = $_FILES ? $this->TimeManager->importRecord($_FILES) : 'No files chosen.';
								echo $result;								
								break;
				case 'update' : $result = $this->TimeManager->updateEmployeeDetails($_REQUEST['data']);
								echo json_encode($result);
								break;
				case 'search' : $result = $this->TimeManager->searchEmployee($_REQUEST['data'],$_REQUEST['view_type'],$start,$offset);								
								echo json_encode($result);
								break;
				case 'update_time_record' : $result = $this->TimeManager->actionEmployeeTimeRecord($this->type,'edit',$_REQUEST['data']);
								echo json_encode($result);
								break;
				case 'transfer_time_record' : $result = $this->TimeManager->actionEmployeeTimeRecord($this->type,'transfer',$_REQUEST['data']);
  								echo json_encode($result);
								break;	
				case 'fetch_time_record' : $result = $this->TimeManager->getSortedTimeRecord($this->type,$this->emp_id,$params);
								echo json_encode($result);
								break;	
				case 'fetch_employees' : $result = $this->TimeManager->getEmployeeTimeRecord($this->emp_id,$start,$offset,null,null,$params);	
								echo json_encode($result);
								break;
				case 'fetch_employee_total_hours_rendered_and_others' : $result = $this->TimeManager->getEmployeeTotalHoursRenderedAndOthers($this->emp_id);	
								echo json_encode($result);
								break;		
				case 'fetch_employee_time_record_on_change_date' : $result = $this->TimeManager->getEmployeeTimeRecordOnChangeDate($type,$this->emp_id);	
								echo json_encode($result);
								break;
				case 'export-multiple' : $this->TimeManager->exportRecordMultiple($emp_id,$params);
										break;
			}
		}

		public function process_save_time_record_rules()
		{
			if(isset($_REQUEST['data'])){
				$result = $this->TimeManager->saveTimeRecordRules($_REQUEST['data']);				
				echo json_encode($result);
			}				
		}
		
		public function process_save_company_details()
		{
			if(isset($_REQUEST['data'])){
				$result = $this->TimeManager->saveCompanyDetails($_REQUEST['data']);				
				echo json_encode($result);
			}				
		}
		
		public function process_change_view_listing($type,$emp_id,$start=0,$offset=5)
		{
			if(isset($type) && !is_null($type) && !empty($type)){
				$result = array();
				$date = null;
				$compare_date=null;
				switch($type){
					case 'table' : $result = $this->TimeManager->getEmployeeTimeRecord($emp_id,$start,$offset,$date,$compare_date);		
								   break;
					case 'tabs' : $result = $this->TimeManager->getEmployeeTimeRecordTabsFormat($emp_id,$start,$offset,$date,$compare_date);
									$this->total_employees = count($result);
									break;					
				}
				echo json_encode($result);
			}
		}	
		
		public function process_default()
		{					
			$this->current_page = $this->uri->segment(2);
			$this->current_date = date('Y-m-d');
			$this->company_time_record_rules = $this->TimeManager->getCompanyTimeRecordRules();
			
			switch($this->current_page){
				case 'employees' : $this->current_page = 'Employees';
									break;				
				case 'reports' : $this->current_page = 'Reports';
									break;
				default: $this->current_page = 'Home';
									break;
			}
			$this->process_search_contents();
		}
		
		public function process_employee_recent_activities($start=0,$offset=10)
		{	
			$get_employee_recent_activities = $this->TimeManager->getEmployeeRecentActivities($start,$offset);			
			echo json_encode($get_employee_recent_activities);
		}
		
		public function initializePagination($url='',$total_rows=0,$offset)
		{
			$config['base_url'] = !is_null($url) ? $url : $this->base_url;
			$config['total_rows'] = $total_rows;
			$config['per_page'] = $offset;
			$config['uri_segment'] = 2;
			$config['num_links'] = 10;			
			
			/**
			* @ here configuration for the pagination navigations - start
			**/
			$config['first_link'] = 'First';
			$config['first_tag_open'] = '<div>';
			$config['first_tag_close'] = '</div>';
			$config['full_tag_open'] = '<div class="pagination-navigation-numbers">';
			$config['full_tag_close'] = '</div>';
			$config['last_link'] = 'Last Page &gt;';
			$config['next_link'] = 'Next Page &gt;';
			$config['prev_link'] = 'Previous &lt;';
			$config['next_tag_open'] = '<div class="pagination-next-container">';
			$config['next_tag_close'] = '</div>';
			
			/**
			* @ here configuration for the pagination navigations - end 
			**/
			
			$this->pagination->initialize($config); 	
		}
		
		public function process_search_contents()
		{
			$get_searchable_columns = '';
			if(in_array(strtolower($this->current_page),$this->has_search_filter))
				$get_searchable_columns = $this->TimeManager->getSearchableColumns(strtolower($this->current_page));			
				
			if($get_searchable_columns)
			{
				$counter = 0;
				$total = sizeof($get_searchable_columns);
				
				foreach($get_searchable_columns as $key => $value){
					foreach($value as $key1 => $value2){
					
							
						$value2 = str_replace('_'," ",$value2);
						$this->search_contents .= '	<div class="field-container" id="field-'.$value2.'">
													<div class="label-head">'.ucwords($value2).'</div>
													<input type="text" id="field-'.str_replace(' ','_',$value2).'" name="'.$value2.'"/>
													</div>';
						if($counter == ($total-1)){
							$this->search_contents .= '<div class="filter-container"><a href="javascript:void(0);" onclick="Manager.__search();"/>Filter</a></div>';		
						}
					}
					$counter++;
				}				
			}
		}
		
		public function process_logout()
		{
			echo $this->system_version;
		}
	
	}
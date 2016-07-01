<?php

	class TimeManager extends CI_Model{	
	
		public $current_time_record_date,$record_time_in=false,$current_record_time;
		public $current_date = '',$current_emp_id,$reset_row;
		public $row = 1,$time_row = false,$time_record = array('time'=>array()),$date_record = array(),$date_details = array();	
		public $start_shift_time,$end_shift_time,$required_hours_rendered,$current_import_time,$date_attended,$export_date_attended,$export_emp_id;
			
		
		public function getEmployeeTimeRecord($emp_id,$limit=0,$offset=5,$date=null,$compare_date=null,$orderby=null)
		{		
			$emp_id = is_array($emp_id) ? implode(',',$emp_id) : $emp_id;				
			$condition = is_null($emp_id) || empty($emp_id) || $emp_id == '*' ? '' : 'where emp_id IN('.$emp_id.')';
			$explode_orderby = !is_null($orderby) && $orderby != '' ? explode('-',$orderby) : '';
			$sortby = !is_null($orderby) && $orderby != '' ? 'ORDER BY '.$explode_orderby[0].' '.$explode_orderby[1] : '';
			$result_query = $this->db->query('SELECT * FROM employees '.$condition.' '.$sortby.' LIMIT '.$limit.','.$offset.'');			
			$results['query'] = 'SELECT * FROM employees '.$condition.' LIMIT '.$limit.','.$offset.'';
			if($result_query->num_rows() > 0){							
					$results['contents'] = '
						<table class="table-content-tab-result" cellpadding="0" cellspacing="0">'.$this->getEmployeeHeaders('employee_time_records',$orderby,$this->db->query('SELECT * FROM employees')->num_rows()).'<tbody>';		
			
					foreach($result_query->result() as $key => $value){
							
							if(!is_null($compare_date) && count(explode('-',$compare_date)) == 3)
								$date_condition = " AND date_attended BETWEEN '".$this->defaultFormat('Y-m-d',$date)."' AND '".$this->defaultFormat('Y-m-d',$compare_date)."'";
							else
								$date_condition = !is_null($date) && !empty($date) && count(explode('-',$date)) == 3 ? " AND date_attended = '".$this->defaultFormat('Y-m-d',$date)."'" : '';
							
							$total_time_record = 0;
							$get_time_record_details = $this->db->query("SELECT * FROM emp_time_record WHERE emp_id = '{$value->emp_id}' AND DATE_FORMAT(date_attended,'%b %Y') = DATE_FORMAT(NOW(),'%b %Y') {$date_condition}");						
							$total_time_record = $get_time_record_details->num_rows();						
							$results['date_query'] = "SELECT * FROM emp_time_record WHERE emp_id = '{$value->emp_id}' AND DATE_FORMAT(date_attended,'%b %Y') = DATE_FORMAT(NOW(),'%b %Y') {$date_condition}";
							$results['contents'] .= '							
								<tr id="record-'.$value->emp_id.'">
									<td>'.$value->emp_id.'</td>
									<td>'.ucwords($value->fullname).'</td>
									<td>'.$value->position.'</td>
									<td>'.$value->leave_limit.'</td>
									<td>'.$value->unpaid_leave_limit.'</td>
									<td>'.$value->absences.'</td>
									<td>'.$this->getEmployeeTotalLate($value->emp_id).'</td>
									<td>'.$this->getEmployeeTotalOvertimeRendered($value->emp_id).'</td>
									<td>'.$value->record_status.'</td>
									<td>
										<div class="employee-actions">
											<div class="employee-options" id="action-'.$value->emp_id.'">
												<span id="edit-employee-show-hide" onclick="Manager.__edit_employee(\''.$value->emp_id.'\');"><img src="'.base_url().'/resources/images/edit-employee.png" title="Edit employee details"/></div>
											</div>											
										</div>	
									</td>
									<td class="td-selecting-data"><input type="radio" name="check_all_employee" class="selected_employee" data-fullname="'.ucwords($value->fullname).'"id="'.$value->emp_id.'" value="'.$value->emp_id.'"/></td>
								</tr>';							
					}
					$results['contents'] .= '</tbody></table>';				
			}
			else
				$results['contents'] = '<div class="neutral-msg">No time detail records.</div>';
			
			return $results;
		}
		
		public function getEmployeeTimeRecordTabsFormat($emp_id,$start=0,$offset=5,$date=null,$compare_date=null)
		{
			$emp_id = !is_null($emp_id) && !empty($emp_id) ? $emp_id : $emp_id;
			$date = !is_null($date) && count(explode('-',$date)) == 3 ? $date : null;
			$compare_date = !is_null($compare_date) && count(explode('-',$compare_date)) == 3 ? $compare_date : null;
			$condition = $emp_id == '*' ? '' : 'where emp_id IN('.implode(',',$emp_id).')';
			$result_query = $this->db->query("SELECT * FROM employees {$condition}");
			
			if($result_query->num_rows() > 0){
				$results = array();			
				$results['contents'] = '
						<div class="tab-employee-result">
								<ul id="tab-ul">';
						foreach($result_query->result() as $row){
							$results['contents'] .= '<li data-id="'.$row->emp_id.'" id="li-'.$row->emp_id.'"><a href="'.$this->base_url.'/index.php/manager/employee_time_record_details/'.$row->emp_id.'" title="'.ucwords($row->fullname).' - '.($row->emp_id != '' ? $row->emp_id : 'not specified.').' ('.($row->position != '' ? $row->position : 'not specified.').')">'.ucwords($row->fullname).'</a></li>';
						}
						$results['contents'] .= '
								</ul>';										
				$results['contents'] .= '
						</div>
						<input type="hidden" value="'.$this->db->query('SELECT * FROM employees')->num_rows().'" id="data-total-employees"/>
				';				
			}
			else
				$results['contents'] = '<div class="neutral-msg">No time detail records.</div>';
			
			return $results;

		}
		
		public function getEmployeeAttendedDates($emp_id)
		{
			if(strlen($emp_id) > 0 && isset($emp_id)){
				$get_all_time_record = $this->db->query("SELECT * FROM emp_time_record WHERE emp_id = '{$emp_id}'");
				$results = array();		
				
				if($get_all_time_record->num_rows() > 0){
					$count = 1;
					$total = $get_all_time_record->num_rows();
					$total_dates = array('dates'=>array());
					foreach($get_all_time_record->result() as $row){
						$this->current_emp_id = $row->emp_id;
						if($this->checkSameEmployee($row->emp_id,$this->current_emp_id))									
							$total_dates['dates'][$this->current_emp_id][$count] = $this->defaultFormat('Y-m-d',$row->date_attended);	
						else									
							$total_dates['dates'][$row->emp_id][$count] = $this->defaultFormat('Y-m-d',$row->date_attended);	
						
						$count++;
					}
					$results['attended_dates'] = $total_dates;
				}else
					$results['error'] = "Calendar not drawn due to no time records.";			
				return $results;				
			}				
		}
		
		public function getEmployeeTimeRecordDetails($emp_id,$date=null,$compare_date=null)
		{
			if(!is_null($emp_id) && isset($emp_id)){
				$emp_id = !is_null($emp_id) && !empty($emp_id) ? $emp_id : $emp_id;
				$date = !is_null($date) && count(explode('-',$date)) == 3 ? $date : null;
				$compare_date = !is_null($compare_date) && count(explode('-',$compare_date)) == 3 ? $compare_date : null;
				$condition = $emp_id == '*' ? '' : 'where emp_id = "'.$emp_id.'"';
				$result_query = $this->db->query("SELECT * FROM employees {$condition}");			
				$results = '';			
			
				if($result_query->num_rows() > 0){				
					foreach($result_query->result() as $value){							
						if(!is_null($compare_date) && count(explode('-',$compare_date)) == 3)
							$date_condition = " AND date_attended BETWEEN '".$this->defaultFormat('Y-m-d',$date)."' AND '".$this->defaultFormat('Y-m-d',$compare_date)."'";
						else
							$date_condition = !is_null($date) && !empty($date) && count(explode('-',$date)) == 3 ? " AND date_attended = '".$this->defaultFormat('Y-m-d',$date)."'" : '';
						
						$get_time_record_details = $this->db->query("SELECT * FROM emp_time_record where emp_id = '{$value->emp_id}' AND DATE_FORMAT(date_attended,'%b %Y') = DATE_FORMAT(NOW(),'%b %Y') {$date_condition} ORDER BY date_attended ASC");						
						$total_time_record = $get_time_record_details->num_rows();						
						
						$calculations = $this->getEmployeeTotalHoursRenderedAndOthers($value->emp_id,date('Y-m'));
						
									
						$thumbnail = file_exists('uploads/thumbnails/'.$value->emp_id.'.png') ? $this->base_url.'/uploads/thumbnails/'.$value->emp_id.'.png' : $this->base_url.'/resources/images/profile-image.gif'; 
						
							$results .= '
									<div class="employee-time-record-tabs-wrapper" id="employee_time_records_tab_'.$value->emp_id.'">
										<div class="employee-time-record-tabs-container">
											<div class="employee-time-record-tabs-inner-container">
												<div class="tab-employee-details-wrapper">
													<div class="tab-employee-details-container">
														<div class="tab-employee-detail-thumbnail-container">
															<span class="tab-employee-detail-thumbnail"><img src="'.$thumbnail.'" title="'.$value->fullname.'" alt="'.$value->fullname.'"/></span>
														</div>
														<div class="tab-employee-details">
															<div class="tab-employee-details-options">																
																<span class="tab-employee-details-edit-details" onclick="Manager.__edit_employee(\''.$value->emp_id.'\');"><img src="'.$this->base_url.'/resources/images/edit-employee.png" alt="Edit details" title="Edit employee details"/><img class="sort-btn" src="'.$this->base_url.'/resources/images/down.png" onclick=""></span>
																<span class="tab-employee-details-change-thumbnail" onclick="Manager.__change_employee_thumbnail(\''.$value->emp_id.'\');">Change Thumbnail</span>																
															</div>
															<div class="tab-employee-details-fields">
																<label>Employee Name : </label>
																<span>'.($value->fullname != '' ? $value->fullname : 'not specified.').'</span>
															</div>
															<div class="tab-employee-details-fields">
																<label>Employee I.D. : </label>
																<span>'.($value->emp_id != '' ? $value->emp_id : 'not specified.').'</span>
															</div>
															<div class="tab-employee-details-options">
																<span class="tab-employee-details-select-employee">Select this employee for exporting : <input type="radio" data-fullname="'.ucwords($value->fullname).'" name="check_all_employee" value="'.$value->emp_id.'" id="'.$value->emp_id.'"/></span>
															</div>
															<div class="tab-employee-details-fields">
																<label>Position : </label>
																<span>'.($value->position != '' ? $value->position : 'not specified.').'</span>
															</div>
															<div class="tab-employee-details-fields">
																<label>Department assigned : </label>
																<span>not specified.</span>
															</div>
															<div class="tab-employee-details-fields">
																<label> Paid Leave : </label>
																<span>'.($value->leave_limit != '' ? $value->leave_limit : 'not specified.').'</span>
															</div>
															<div class="tab-employee-details-fields">
																<label>Unpaid Leave : </label>
																<span>'.($value->unpaid_leave_limit != '' ? $value->unpaid_leave_limit : 'not specified.').'</span>
															</div>
															<div class="tab-employee-details-fields">
																<label> Absences : </label>
																<span>'.($value->absences != '' ? $value->absences : 'not specified.').'</span>
															</div>
														</div>
													</div>
													<span class="time-record-tab-label" id="time-record-tab-label-'.$value->emp_id.'"> '.$total_time_record.' time record(s) on the month of '.date('F Y').'</span>
													<span class="time-record-tab-calculate-btn"><input type="button" value="Calculate total hours rendered,late, and overtime for all months" onclick="Manager.__calculate_total_hours_rendered_and_others(\''.$value->emp_id.'\');"/></span>
													<div class="time-record-tab-result" id="tab-result-'.$value->emp_id.'">&nbsp;</div>
												</div>
												<div class="employee-time-record-calendar-wrapper">
													<div class="action-calendar-info note">
														<span>
															<b>Info :</b> Changing the month on the calendar will get the time records of the employee on the time record table.<br/><br/>
															<b>Legend :</b> <span class="legend-red">&nbsp;</span> - Day-off &nbsp;&nbsp;<span class="legend-present">&nbsp;</span> - Noted(Absent,Leave,or Present)
														</span>
													</div>
													<div class="employee-time-record-details-calculations" id="employee-details-calculation-'.$value->emp_id.'">
														'.$calculations['contents'].'
													</div>
													<div class="employee-time-record-calendar" id="employee-calendar-'.$value->emp_id.'" data-emp-name="'.str_replace(' ','-',$value->fullname).'"></div>
												</div>
												<table class="time-record-content-table" id="time-record-table-'.$value->emp_id.'">
													<thead>
														<tr>
															<th colspan="2">Date Attended <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'date_attended-ASC\',\''.$value->emp_id.'\',\''.date('Y-m').'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
															<th colspan="2">(Morning) <br/> In - Out <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'time_1-ASC\',\''.$value->emp_id.'\',\''.date('Y-m').'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>															
															<th colspan="2">(Afternoon) <br/> In - Out <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'time_3-ASC\',\''.$value->emp_id.'\',\''.date('Y-m').'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
															<th colspan="2">(Overtime) <br/> In - Out <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'time_5-ASC\',\''.$value->emp_id.'\',\''.date('Y-m').'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
															<th>Total Hours Rendered</th>
															<th>Total Overtime Rendered</th>
															<th title="(hours:minutes:seconds)">Late/Tardy</th>
															<th>Remarks <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'remarks-ASC\',\''.$value->emp_id.'\',\''.date('Y-m').'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
															<th>Actions</th>
														</tr>
													</thead>
													<tbody>';	
													
													if($total_time_record > 0){
														foreach($get_time_record_details->result() as $value2){
															$time_values = array(
																	0=>$value2->time_1,
																	1=>$value2->time_2,
																	2=>$value2->time_3,
																	3=>$value2->time_4,
																	4=>$value2->time_5,
																	5=>$value2->time_6																
																);
															
															$time_values = $this->checkIfNotEmpty($time_values);	
															$results .= ' 
																<tr id="time-record-'.$value2->record_id.'" title="'.$this->defaultFormat("l, M d Y",$value2->date_attended).'">
																	<td colspan="2">'.$this->defaultFormat("M d Y",$value2->date_attended).'</td>
																	<td>'.$this->defaultFormat("g:i a",$value2->time_1,'time').'</td>
																	<td>'.$this->defaultFormat("g:i a",$value2->time_2,'time').'</td>
																	<td>'.$this->defaultFormat("g:i a",$value2->time_3,'time').'</td>
																	<td>'.$this->defaultFormat("g:i a",$value2->time_4,'time').'</td>
																	<td>'.$this->defaultFormat("g:i a",$value2->time_5,'time').'</td>
																	<td>'.$this->defaultFormat("g:i a",$value2->time_6,'time').'</td>
																	<td>'.$this->getTotalHoursRendered($time_values).'</td>
																	<td>'.$this->getComputedOvertime($time_values).'</td>
																	<td>'.$this->defaultFormat('H:i:s',$this->getComputedLate($value2->time_1,$time_values),'time').'</td>	
																	<td>'.stripslashes($value2->remarks).'</td>
																	<td align="center">
																		<span id="edit-employee" onclick="Manager.__action_employee_time_record(\''.$value2->record_id.'\',\'edit\',\'\');"><img src="'.$this->base_url.'/resources/images/edit-employee.png" style="width:18px;height:18px;" title="Edit time record details"/></span>
																		<span id="transfer-employee" onclick="Manager.__action_employee_time_record(\''.$value2->record_id.'\',\'transfer\',\''.$value->emp_id.'\');"><img src="'.$this->base_url.'/resources/images/transfer.png" title="Transfer time record to other employees"/></span>
																		<span id="delete-employee" onclick="Manager.__action_employee_time_record(\''.$value2->record_id.'\',\'delete\',\'\');"><img src="'.$this->base_url.'/resources/images/delete.png" title="Delete time record details"/></span>
																	</td>
																</tr>';											
															}																												
													}else
														$results .= '
																	<tr>
																		<td colspan="13">No time records on this month.</td>
																	</tr>
																';
										$results .='
													</tbody>
												</table>
											</div>
										</div>
									</div>
								';
					}
				}
				else
					$results = '<div class="neutral-msg">No time detail records.</div>';
					
				return $results;
			}
			
		}
		
		public function getEmployeeTimeRecordOnChangeDate($date,$emp_id)
		{
			if(!empty($date) && !is_null($emp_id) && !empty($emp_id)){
				$get_employee_time_record = $this->db->query("SELECT * FROM emp_time_record WHERE emp_id = '{$emp_id}' AND DATE_FORMAT(date_attended,'%Y-%m') = '$date'");
				$results = array();
				$results['data_query'] = "SELECT * FROM emp_time_record WHERE emp_id = '{$emp_id}' AND DATE_FORMAT(date_attended,'%Y-%m') = '$date'";
				$results['data_rows'] = $get_employee_time_record->num_rows();
				$results['contents'] = '
					<table class="time-record-content-table" id="time-record-table-'.$emp_id.'">
						<thead>
							<tr>
								<th colspan="2">Date Attended <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'date_attended-ASC\',\''.$emp_id.'\',\''.$date.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
								<th colspan="2">(Morning) <br/> In - Out <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'time_1-ASC\',\''.$emp_id.'\',\''.$date.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>															
								<th colspan="2">(Afternoon) <br/> In - Out <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'time_3-ASC\',\''.$emp_id.'\',\''.$date.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
								<th colspan="2">(Overtime) <br/> In - Out <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'time_5-ASC\',\''.$emp_id.'\',\''.$date.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
								<th>Total Hours Rendered</th>
								<th>Total Overtime Rendered</th>
								<th title="(hours:minutes:seconds)">Late/Tardy</th>
								<th>Remarks <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'remarks-ASC\',\''.$emp_id.'\',\''.$date.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>	
				';
				$calculations = $this->getEmployeeTotalHoursRenderedAndOthers($emp_id,$date);
				$results['attended_dates'] = '';
				$total_time_record = $get_employee_time_record->num_rows();
				$results['time_record_label'] = $total_time_record.' time record(s) on the month of '.$this->defaultFormat('F Y',$date);
				$results['month_calculations'] = $calculations['contents'];
				if($total_time_record > 0){
					$count = 1;
					$date_separator = '/';
					foreach($get_employee_time_record->result() as $value){
					
						$time_values = array(
								0=>$value->time_1,
								1=>$value->time_2,
								2=>$value->time_3,
								3=>$value->time_4,
								4=>$value->time_5,
								5=>$value->time_6								
							);
						
						$time_values = $this->checkIfNotEmpty($time_values);
						
						$results['contents'] .= ' <tr id="time-record-'.$value->record_id.'" title="'.$this->defaultFormat("l, M d Y",$value->date_attended).'">
							<td colspan="2">'.$this->defaultFormat("M d Y",$value->date_attended).'</td>
							<td>'.$this->defaultFormat("g:i a",$value->time_1,'time').'</td>
							<td>'.$this->defaultFormat("g:i a",$value->time_2,'time').'</td>
							<td>'.$this->defaultFormat("g:i a",$value->time_3,'time').'</td>
							<td>'.$this->defaultFormat("g:i a",$value->time_4,'time').'</td>
							<td>'.$this->defaultFormat("g:i a",$value->time_5,'time').'</td>
							<td>'.$this->defaultFormat("g:i a",$value->time_6,'time').'</td>
							<td>'.$this->getTotalHoursRendered($time_values).'</td>
							<td>'.$this->getComputedOvertime($time_values).'</td>
							<td>'.$this->defaultFormat("H:i:s",$this->getComputedLate($value->time_1,$time_values),'time').'</td>							
							<td>'.$value->remarks.'</td>
							<td align="center">
								<span id="edit-employee" onclick="Manager.__action_employee_time_record(\''.$value->record_id.'\',\'edit\',\'\');"><img src="'.$this->base_url.'/resources/images/edit-employee.png" style="width:18px;height:18px;" title="Edit details"/></span>
								<span id="transfer-employee" onclick="Manager.__action_employee_time_record(\''.$value->record_id.'\',\'transfer\',\''.$emp_id.'\');"><img src="'.$this->base_url.'/resources/images/transfer.png" title="Transfer time record to other employees"/></span>
								<span id="delete-employee" onclick="Manager.__action_employee_time_record(\''.$value->record_id.'\',\'delete\',\'\');"><img src="'.$this->base_url.'/resources/images/delete.png" title="Delete time record details"/></span>
							</td>
						</tr>';
						
						if($count == ($total_time_record))
							$date_separator = '';
						
						$results['attended_dates'] .= $this->defaultFormat('Y-m-d',$value->date_attended).$date_separator;
						$count++;
					}
					$results['contents'] .= '
							</tbody>
						</table>';	
				}else{
					$results['contents'] .= '
						<tr>
							<td colspan="13">No time records on this month.</td>
						</tr>
					';
				}
				
				return $results;
			}
			
		}
		
		public function getEmployeeChangeThumbnailForm($id)
		{
			if(!is_null($id) && !empty($id)){
				$results = array();
				$results['contents'] = '
						<div class="employee-change-thumbnail-wrapper">
							<div class="employee-change-thumbnail-container">
								<form id="form-change-thumbnail" onsubmit="return Manager.___action_save_thumbnail(this,\''.$id.'\');"  name="employee-change-thumbnail" method="post" action="javascript:void();" enctype="multipart/form-data">
									<div class="employee-field-container">	
										<label>Choose a photo/image: </label>
										<span><input type="file" name="thumbnail-file"/></span>
										<input type="hidden" name="emp_id" value="'.$id.'"/>
									</div>
									<div class="employee-field-container">
										<input type="submit" name="upload" value="Upload and save"/>
									</div>
								</form>
							</div>
						</div>
				';
				// action="'.$this->base_url.'/index.php/manager/employee_change_thumbnail/change/'.$id.'"
				return $results;
			}
		}
		
		public function saveEmployeeThumbnail($data,$id)
		{
			if(!empty($data) && !empty($id)){
				$target_path = 'uploads/thumbnails/';
				$target_path = $target_path . basename($data['thumbnail-file']['name']);				
				$move_uploaded = move_uploaded_file($data['thumbnail-file']['tmp_name'], $target_path);
				$results = array();
				
				$results['data'] = $data['thumbnail-file'];
				
				if($move_uploaded){
					$results['contents'] = 'Successful upload thumbnail.';
				}else
					$results['contents'] = 'Error uploading thumbnail.';
					
				return $results;
			}
		}
		
		public function getEmployeeTotalLate($emp_id)
		{
			if(isset($emp_id) && !empty($emp_id)){
				// $get_employee_time_records = $this->db->query("SELECT time_1,time_2,time_3,time_4,time_5,time_6 FROM emp_time_record WHERE emp_id = '".trim($emp_id)."'");
				$get_employee_time_records = $this->db->select('time_1,time_2,time_3,time_4,time_5,time_6')->from('emp_time_record')->like('emp_id',trim($emp_id))->get();
				
				if($get_employee_time_records->num_rows() > 0){
					$total_late = 0;
					foreach($get_employee_time_records->result() as $row){	
						$time_values = array(
								0=>$row->time_1,
								1=>$row->time_2,
								2=>$row->time_3,
								3=>$row->time_4,
								4=>$row->time_5,
								5=>$row->time_6								
							);
						
						$time_values = $this->checkIfNotEmpty($time_values);
						$late = $this->getComputedLate($row->time_1,$time_values);
						$total_late = $this->addHours($total_late,$late);
						
					}					
					return $this->defaultFormat('H:i:s',$total_late,'time');
				}
			
			}
		}
		
		public function getEmployeeTotalHoursRenderedAndOthers($emp_id,$date=null)
		{
			$date_condition = !is_null($date) && (count(explode('-',$date)) == 2) ? " AND DATE_FORMAT(date_attended,'%Y-%m') = '{$date}' " : '';
			$get_all_time_records = $this->db->query("SELECT time_1,time_2,time_3,time_4,time_5,time_6 FROM emp_time_record WHERE emp_id = '{$emp_id}' {$date_condition}");
			$results = array();
			
			if($get_all_time_records->num_rows() > 0){
				$time_record_total_hours_rendered = 0;
				$time_record_total_overtime_rendered = 0;
				$time_record_total_late = 0;
				$total_regular_hours = 0;
				foreach($get_all_time_records->result() as $row){
						$time_values = array(
								0=>$row->time_1,
								1=>$row->time_2,
								2=>$row->time_3,
								3=>$row->time_4,
								4=>$row->time_5,
								5=>$row->time_6								
							);
						
						$time_values = $this->checkIfNotEmpty($time_values);
						$time_record_total_hours_rendered = $this->addHours($time_record_total_hours_rendered,$this->getTotalHoursRendered($time_values));
						$time_record_total_overtime_rendered = $this->addHours($time_record_total_overtime_rendered,$this->getComputedOvertime($time_values));
						$time_record_total_late = $this->addHours($time_record_total_late,$this->getComputedLate($row->time_1,$time_values));
						
						$regular_hours = new DateTime(date("H:i:s",strtotime($this->getTotalHoursRendered($time_values))));
						$overtime_hours = new DateTime(date("H:i:s",strtotime($this->getComputedOvertime($time_values))));
						
						$regular_hours = $regular_hours->diff($overtime_hours)->format('%H:%I');
						$total_regular_hours = $this->addHours($total_regular_hours,$regular_hours);	
				}
				$results['contents'] = 'Total hours rendered : '.$time_record_total_hours_rendered.' hour(s) <br> Total regular hours : '.$total_regular_hours.' hour(s) <br/> Total overtime : '.$time_record_total_overtime_rendered.' hours or minute(s) <br/> Total late : '.$time_record_total_late.' hours or minute(s)';
			}else
				$results['contents'] = 'Total hours rendered : <br/> 00:00 hour(s) <br/>Total regular hours :<br/> 00:00 hour(s) <br/> Total overtime : <br/> 00:00 hour(s) or minute(s) <br/> Total late : <br/> 00:00 hour(s) or minute(s)';	
				
			return $results;
		}
		
		public function getEmployeeTotalOvertimeRendered($emp_id)
		{
			if(isset($emp_id) && !empty($emp_id)){
				$get_employee_time_records = $this->db->query("SELECT time_1,time_2,time_3,time_4,time_5,time_6 FROM emp_time_record WHERE emp_id = {$emp_id}");
				
				if($get_employee_time_records->num_rows() > 0){
					$total_overtime = 0;
					foreach($get_employee_time_records->result() as $row){	

						$time_values = array(
								0=>$row->time_1,
								1=>$row->time_2,
								2=>$row->time_3,
								3=>$row->time_4,
								4=>$row->time_5,
								5=>$row->time_6							
							);
						
						$time_values = $this->checkIfNotEmpty($time_values);		
						$overtime = $this->getComputedOvertime($time_values);
						$total_overtime = $this->addHours($total_overtime,$overtime);
						
					}
					
					return $this->defaultFormat('H:i',$total_overtime);					
				}
			}
		}
		
		public function checkIfNotEmpty($data = array())
		{
			if(isset($data) && !empty($data)){
				$return_data = array();
				$count = 0;
				foreach($data as $value){
					if($value != '00:00:00'){
						$return_data[$count] = $value;
						$count++;
					}						
				}
				return $return_data;
			}
		}		
		
		public function addHours($_hr1='00:00', $_hr2='00:00') 
		{ 
			// See: http://www.miraclesalad.com/webtools/timesheet.php
			$piece1 = explode(':', trim($_hr1));
			$piece2 = explode(':', trim($_hr2));
			  
			$piece1[1] = (! isset($piece1[1])) ? '' : $piece1[1];
			$piece2[1] = (! isset($piece2[1])) ? '' : $piece2[1]; 
			  
			$hr1 = trim($piece1[0]);
			$hr2 = trim($piece2[0]);
			$min1 = trim($piece1[1]);
			$min2 = trim($piece2[1]);
			$hr1 = ($hr1 == '') ? 0 : intval($hr1);
			$hr2 = ($hr2 == '') ? 0 : intval($hr2);
			$min1 = ($min1 == '') ? 0 : intval($min1);
			$min2 = ($min2 == '') ? 0 : intval($min2);  
			$HR =  $hr1 + $hr2;
			$MIN =  $min1 + $min2;
			
			if($MIN >= 60) {
			   $HR++;
			   $MIN -= 60;
			}
			
			$MIN = ($MIN < 10) ? ('0'.$MIN) : $MIN;
			$TOTAL = $HR .':'. $MIN;
			
			return $TOTAL; 
		}
		
		public function getComputedLate($time,$time_values=null)
		{
			$this->getRequiredHoursEmployee();
			if(!is_null($time_values)){
				if(count($time_values) < 2)
					return '00:00:00';
			}
			
			if(strtotime($time) > strtotime($this->start_shift_time)){
				$time = new DateTime($time);
				$start_shift_time = new DateTime($this->start_shift_time);				
				return $time->diff($start_shift_time)->format('%H:%I:%S');
				// return $this->calculateTime($this->start_shift_time,$time);
			}else
				return '00:00:00';											
		}
		
		public function calculateTime($start_time, $finish_time) 
		{  
			// http://stackoverflow.com/a/8742253
			$start_time = date('H:i', strtotime($start_time));
			$finish_time = date('H:i', strtotime($finish_time));

			$s_parts = explode(':', $start_time); 
			$e_parts = explode(':', $finish_time);     
			   
			$hs = $s_parts[0];    # start hour 
			$ms = $s_parts[1];    # start minute 
			$he = $e_parts[0];    # end hour 
			$me = $e_parts[1];    # end minute 
			   
			# end hour only < start hour if new day 
			if ($he < $hs) {
				$he += 24; 
			}          
			# end minute < start minute, decrease end hour by one and add 60 minutes 
			if ($me < $ms) { 
			   --$he; 
			   $me += 60; 
			}
			
			$hrs = $he - $hs; 
			if ($hrs < 10) { $hrs = '0'.$hrs; }        
			$mins = $me - $ms; 			
		
			if ($mins < 10) { $mins = '0'.$mins; }    
				$the_time = "$hrs:$mins";
				
			return $the_time; 		  
		}
		
		public function getTotalHoursRendered($data=array())
		{
			if(isset($data) && is_array($data) && count($data) > 0){
				$count = 0;
				$computed_hours = array();
				$total_hours_rendered = 0;
				
				if(count($data) >= 2){
					for($i=0;$i<count($data);$i+=2){
						if(isset($data[$i+1]) && $data[$i+1] != '00:00:00'){
							$first = new DateTime($data[$i]);
							$second = new DateTime($data[$i+1]);
							$diff = $first->diff($second);					
							$computed_hours[$count] = $diff->format('%H:%I:%S');							
						}else
							$computed_hours[$count] = $this->defaultFormat('H:i:s','00:00:00');
							
						$total_hours_rendered = $this->addHours($total_hours_rendered,$computed_hours[$count]); 
						$count++;				
					}			
					return $total_hours_rendered;							
				}else
					return '00:00';
			}
		}
		
		public function getComputedOvertime($data=array())
		{				
			if(isset($data) && is_array($data) && count($data) > 0){
				$total_hours_rendered = new DateTime($this->getTotalHoursRendered($data));
				$required_hours_rendered = new DateTime($this->getRequiredHoursEmployee());
				
				if(strtotime($this->getTotalHoursRendered($data)) > strtotime($this->getRequiredHoursEmployee())){					
					return $total_hours_rendered->diff($required_hours_rendered)->format('%H:%I');					
				}else
					return '00:00';
			}
			return false;
		}		
		
		public function getRequiredHoursEmployee()
		{
			$get_time_record_rules = $this->db->query("SELECT start_shift_time,end_shift_time,required_hours_rendered FROM time_record_rules");
			
			if($get_time_record_rules->num_rows() > 0){
				foreach($get_time_record_rules->result() as $row){
					$this->start_shift_time = $row->start_shift_time;
					$this->end_shift_time = $row->end_shift_time;
					$this->required_hours_rendered = $row->required_hours_rendered;					
				}
				return $this->required_hours_rendered;
			}
		}
		
		public function getSortedTimeRecord($field_type,$emp_id,$month_date=null)
		{
			if(strlen($field_type) > 0 && !empty($field_type)){
				$results = array();
				$date_condition = isset($month_date) && strlen($month_date) > 0 ? "AND DATE_FORMAT(date_attended,'%Y-%m') = '{$month_date}'" : '';
				$field = explode('-',$field_type);
				$get_time_record = $this->db->query("SELECT * FROM emp_time_record WHERE emp_id = '{$emp_id}' {$date_condition} ORDER BY {$field[0]} {$field[1]}");
				
				if(count($get_time_record->result()) > 0){
					$sort_by = $field[1] == 'ASC' ? 'DESC' : 'ASC';
				
					$results['contents'] = '
						<table class="time-record-content-table" id="time-record-table-'.$emp_id.'">
							<thead>
								<tr>
									<th colspan="2">Date Attended <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'date_attended-'.$sort_by.'\',\''.$emp_id.'\',\''.$month_date.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
									<th colspan="2">(Morning) <br/> In - Out <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'time_1-'.$sort_by.'\',\''.$emp_id.'\',\''.$month_date.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
									<th colspan="2">(Afternoon) <br/> In - Out <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'time_3-'.$sort_by.'\',\''.$emp_id.'\',\''.$month_date.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
									<th colspan="2">(Overtime) <br/> In - Out <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'time_5-'.$sort_by.'\',\''.$emp_id.'\',\''.$month_date.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
									<th>Total Hours Rendered</th>
									<th>Total Overtime Rendered</th>
									<th title="(hours:minutes:seconds)">Late/Tardy</th>
									<th>Remarks <img title="Sort" class="sort-btn" onclick="Manager.__sort_action(\'remarks-'.$sort_by.'\',\''.$emp_id.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
					';
					foreach($get_time_record->result() as $value){
					
						$time_values = array(
								0=>$value->time_1,
								1=>$value->time_2,
								2=>$value->time_3,
								3=>$value->time_4,
								4=>$value->time_5,
								5=>$value->time_6								
							);
						
						$time_values = $this->checkIfNotEmpty($time_values);
						
						$results['contents'] .= ' <tr id="time-record-'.$value->record_id.'" title="'.$this->defaultFormat("l, M d Y",$value->date_attended).'">
							<td colspan="2">'.$this->defaultFormat("M d Y",$value->date_attended).'</td>
							<td>'.$this->defaultFormat("g:i a",$value->time_1,'time').'</td>
							<td>'.$this->defaultFormat("g:i a",$value->time_2,'time').'</td>
							<td>'.$this->defaultFormat("g:i a",$value->time_3,'time').'</td>
							<td>'.$this->defaultFormat("g:i a",$value->time_4,'time').'</td>
							<td>'.$this->defaultFormat("g:i a",$value->time_5,'time').'</td>
							<td>'.$this->defaultFormat("g:i a",$value->time_6,'time').'</td>
							<td>'.$this->getTotalHoursRendered($time_values).'</td>
							<td>'.$this->getComputedOvertime($time_values).'</td>
							<td>'.$this->getComputedLate($value->time_1,$time_values).'</td>							
							<td>'.$value->remarks.'</td>
							<td align="center">
								<span id="edit-employee" onclick="Manager.__action_employee_time_record(\''.$value->record_id.'\',\'edit\',\'\');"><img src="'.$this->base_url.'/resources/images/edit-employee.png" style="width:18px;height:18px;" title="Edit details"/></span>
								<span id="transfer-employee" onclick="Manager.__action_employee_time_record(\''.$value->record_id.'\',\'transfer\',\''.$emp_id.'\');"><img src="'.$this->base_url.'/resources/images/transfer.png" title="Transfer time record to other employees"/></span>
								<span id="delete-employee" onclick="Manager.__action_employee_time_record(\''.$value->record_id.'\',\'delete\',\'\');"><img src="'.$this->base_url.'/resources/images/delete.png" title="Delete time record details"/></span>
							</td>
						</tr>';
					}
					$results['contents'] .= '
							</tbody>
						</table>';										
				}
				
				return $results;
			}
		}
		
		public function checkDateAttendedExists($record_id,$emp_id,$date_attended)
		{
			if(!is_null($emp_id) && !is_null($record_id) && !empty($date_attended)){
				$date_attended = $this->defaultFormat("Y-m-d",$date_attended);
				$check_date_attended_exists = $this->db->query("SELECT date_attended,emp_id FROM emp_time_record WHERE emp_id = '{$emp_id}' AND date_attended = '{$date_attended}' AND record_id <> '{$record_id}'");
				
				if($check_date_attended_exists->num_rows() > 0)
					return true;
				else
					return false;
			}
		}
		
		public function actionEmployeeTimeRecord($id,$type,$data=null)
		{
			if(!empty($id)){
				$results = array();
				switch($type){
					case 'edit' :
								if(!$this->checkDateAttendedExists($data['record_id'],$data['emp_id'],$data['date_attended'])){
									$data['date_attended'] = $this->defaultFormat("Y-m-d",$data['date_attended']);
									$this->db->where('record_id',$data['record_id']);
									$result = $this->db->update('emp_time_record',$data);
									if($result){
										$results['contents'] = 'Employee Time record details saved.';
									}
									else
										$results['contents'] = 'Employee Time record update error occurred.';									
								}else
									$results['contents'] = 'The date('.$data['date_attended'].') selected has already exists on the time record.';	
								break;	
					case 'delete' :
								$this->db->where('record_id',$id);
								$result = $this->db->delete('emp_time_record');
								if($result){
									$results['contents'] = 'Employee Time record details deleted.';
								}
								else
									$results['contents'] = 'Employee Time record delete error occurred.';	
								break;
					case 'fetch': return $this->getTimeRecordDetails($id);
								break;
					case 'fetch_transfer' : return $this->getTransferFormDetails($id,$data);
								break;
					case 'transfer' :									
									$transfer_record = $this->db->query("UPDATE emp_time_record SET emp_id='{$data['emp_id']}' WHERE record_id='{$id}'");
									 
									if($transfer_record){
										$results['contents'] = 'Time Record details transferring successful.';
									}else
										$results['contents'] = 'Transferring data occurred an error.';				 
									break;
				}
				
				return $results;
			}
		}
		
		public function getTransferFormDetails($id,$emp_id)
		{
			if(!empty($id) && strlen($id) > 0){
				
				$result = $this->db->query('SELECT * FROM employees where emp_id <> '.$emp_id.'');
				
				if($result->num_rows() > 0){
					$results = array();					
					$results['contents'] = '
						<div class="transfer-time-record-details-wrapper">
							<div class="transfer-time-record-details-container">';												
							foreach($this->db->query('SELECT * FROM emp_time_record where record_id='.$id.'')->result() as $row2){
								$time_values = array(
										0=>$row2->time_1,
										1=>$row2->time_2,
										2=>$row2->time_3,
										3=>$row2->time_4,
										4=>$row2->time_5,
										5=>$row2->time_6									
									);
								
								$time_values = $this->checkIfNotEmpty($time_values);		
								$results['contents'] .= '
									<div class="time-record-field-container">
										<div class="time-record-field-info note transfer-time-record-field-info">
											Time record details: '.$this->defaultFormat('g:i a',$time_values[0],'time').' - '.$this->defaultFormat('g:i a',$time_values[(count($time_values)-1)],'time').' '.$this->defaultFormat('M d Y',$row2->date_attended).'
										</div>								
									</div>								
								';						
							}						
						$results['contents'] .= '
							<div class="time-record-field-container">
								<div class="time-record-field-label">Transfer To :</div>
								<div class="transfer-time-record-field-input">
									<input type="hidden" name="record_id" value="'.$id.'"/>								
									<select name="emp_id">
								';
								foreach($result->result() as $row){
									$results['contents'] .= '	
										<option value="'.$row->emp_id.'">'.ucwords($row->fullname).'-'.$row->position.'(position)</option>
									';								
								}
								$results['contents'] .= '								
									</select>
									</div>								
							</div>
							
							<div class="dialog_message_content"></div>
						';
					$results['contents'] .= '
							</div>
						</div>
					';
				}
				
				return $results;
			}
		}
		
		public function getTimeRecordDetails($id)
		{
			if(!empty($id) && strlen($id) > 0){
				$result = $this->db->get_where('emp_time_record',array('record_id'=>$id));
				
				if($result->num_rows() > 0){
					$results = array();
					$results['contents'] = '
						<div class="time-record-details-wrapper">
							<div class="time-record-details-container">';
					foreach($result->result() as $row){
							$results['contents'] .= '					
							
							<div class="time-record-field-container">
								<div class="time-record-field-label">Date Attended :</div>
								<div class="time-record-field-input"><span><input type="hidden" name="record_id" value="'.$row->record_id.'"/>
									<input type="hidden" name="emp_id" value="'.$row->emp_id.'"/>
									<input type="text" name="date_attended" id="date_attended" value="'.$this->defaultFormat('M d Y',$row->date_attended).'"/></span></div>
								<div class="time-record-field-info note">The date when the employee attended the shift for the company.</div>
							</div>		
												
							
							<div class="time-record-field-container">
								<div class="time-record-field-label">Remarks :</div>
								<div class="time-record-field-input">
									<textarea rows="4" cols="25" name="remarks">'.$row->remarks.'</textarea>
									<div class="time-record-field-info note">Notes for the employee.</div>							
								</div>
							</div>			
							
							<div class="dialog_message_content"></div>
							';
					}
					$results['contents'] .= '
							</div>
						</div>
					';
				}
				
				return $results;
			}
		}
		
		public function getEmployeeHeaders($type,$orderby=null,$total_employees=null)
		{
			$headers = '';
			if(isset($orderby) && !is_null($orderby)){
				$explode_orderby = explode('-',$orderby);
				$orderby = $explode_orderby[1] == 'ASC' ? 'DESC' : 'ASC';  
			}
			else
				$orderby = 'DESC';
				
			if(!empty($type))
			{
				switch($type){
					case 'employee_time_records' :
								$headers = '
									<thead>
									<tr class="table-content-tab-header">
										<th>Employee I.D.<img title="Sort" class="sort-btn" onclick="Manager.__sort_employee_action(\'emp_id-'.$orderby.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
										<th>Full Name<img title="Sort" class="sort-btn" onclick="Manager.__sort_employee_action(\'fullname-'.$orderby.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
										<th>Position<img title="Sort" class="sort-btn" onclick="Manager.__sort_employee_action(\'position-'.$orderby.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
										<th>Leave spent<img title="Sort" class="sort-btn" onclick="Manager.__sort_employee_action(\'leave_limit-'.$orderby.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
										<th>Unpaid Leave spent<img title="Sort" class="sort-btn" onclick="Manager.__sort_employee_action(\'unpaid_leave_limit-'.$orderby.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
										<th>Absences<img title="Sort" class="sort-btn" onclick="Manager.__sort_employee_action(\'absences-'.$orderby.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
										<th><label title="Total late of the employee (format: hours:minutes)">Late</label><img title="Sort" class="sort-btn" onclick="Manager.__sort_employee_action(\'late-'.$orderby.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
										<th><label title="Total overtime rendered of the employee (format: hours:minutes)">Total Overtime Rendered</label><img title="Sort" class="sort-btn" onclick="Manager.__sort_employee_action(\'overtime-'.$orderby.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
										<th><label title="Status of employee (Active or Inactive)">Record Status<img title="Sort" class="sort-btn" onclick="Manager.__sort_employee_action(\'record_status-'.$orderby.'\');" src="'.$this->base_url.'/resources/images/down.png"/></th>
										<th>Actions</th>
										<th><select name="change_selecting_type" onchange="Manager.__change_selecting_type(this);">
												<option value="single" selected="selected">Single</option>
												<option value="multiple">Multiple</option>
											</select>
											<input type="hidden" value="'.$total_employees.'" id="data-total-employees">
										</th>
									</tr>
									</thead>
								';
				}
			}
			
			return $headers;
		}
		
		public function exportRecordMultiple($ids=null,$daterange=null)
		{
			$ids = !is_null($ids) && $ids == '*' ? '*' : $ids;
			$daterange = !is_null($daterange) && !empty($daterange) ? $daterange : null;
			
			$date_range = explode('*',$daterange);			
			
			$condition = $ids == '*' ? '' : "WHERE emp_id IN({$ids})";
			$employee_ids = $ids == '*' ? 'true' : $ids;
			
			if($employee_ids == 'true'){
				foreach($this->db->query("SELECT emp_id FROM employees")->result() as $row){
					$employee_ids_array[] = $row->emp_id;
				}
				$employee_ids = implode(',',$employee_ids_array);
			}

			$get_all_employee_details = $this->db->query("SELECT * FROM employees {$condition}");
		
			$company_details = array();				
			foreach($this->db->query("SELECT company_name,company_address FROM company_details")->result() as $value){
				$company_details = array(0=>$value->company_name,1=>$value->company_address);
			}
			
			if($get_all_employee_details->num_rows() > 0){
				
				$employee_details = array();
				
				foreach($get_all_employee_details->result() as $row => $value){
					 
					$time_record_total_overtime_rendered = 0;
					$time_record_total_late = 0;
					$total_regular_hours = 0;
					$get_employee_time_records = $this->db->query("SELECT DISTINCT * FROM emp_time_record WHERE emp_id = '{$value->emp_id}' AND date_attended BETWEEN '{$date_range[0]}' AND '{$date_range[1]}' ORDER BY date_attended ASC");
									
					$date_range_from = new DateTime($date_range[0]);
					$date_difference = $date_range_from->diff(new DateTime($date_range[1])); 
					$date_range_length = $date_difference->days;
					
					$time_records = array();
					foreach($get_employee_time_records->result() as $row){	
						array_push($time_records,$row->date_attended);
					}
					
					$count = 0;
					for($i=0;$i<=$date_range_length;$i++){		
						
						$this->date_attended = new DateTime($date_range[0]);
						$this->date_attended->add(new DateInterval('P'.$i.'D'));
						$date_attended = $this->date_attended->format('M d Y');
						
						if(!in_array($this->date_attended->format('Y-m-d'),$time_records)){
						
							foreach($get_employee_time_records->result() as $key => $value2){				
						
								$time_values = array(
										0=>$value2->time_1,
										1=>$value2->time_2,
										2=>$value2->time_3,
										3=>$value2->time_4,
										4=>$value2->time_5,
										5=>$value2->time_6											
									);
								
								$time_values = $this->checkIfNotEmpty($time_values);				
								
								$regular_hours = new DateTime(date("H:i",strtotime($this->getTotalHoursRendered($time_values))));
								$overtime_hours = new DateTime(date("H:i",strtotime($this->getComputedOvertime($time_values))));
								
								$regular_hours = $regular_hours->diff($overtime_hours)->format('%H:%I');										
									
								$time_record_total_overtime_rendered = $this->addHours($time_record_total_overtime_rendered,$this->getComputedOvertime($time_values));
								$total_regular_hours = $this->addHours($total_regular_hours,$regular_hours);								
								$time_record_total_late = $this->addHours($time_record_total_late,$this->getComputedLate($value2->time_1,$time_values));
								
							}	
							$employee_details[$value->emp_id][] = array(
								'emp_id'=>$value->emp_id,
								'fullname'=>$value->fullname,
								'position'=>$value->position,
								'department'=>'Not yet specified.',
								'leave_limit'=>$value->leave_limit,
								'unpaid_leave_limit'=>$value->unpaid_leave_limit,
								'absences'=>$value->absences,
								'total_regular_hours'=>$total_regular_hours,
								'total_overtime'=>$time_record_total_overtime_rendered,
								'total_late'=>$time_record_total_late,
								'date_attended'=>$date_attended,
								'time_1'=>'No time specified',
								'time_2'=>'No time specified',
								'time_3'=>'No time specified',
								'time_4'=>'No time specified',
								'time_5'=>'No time specified',
								'time_6'=>'No time specified',
								'regular_hours'=>'0',
								'overtime_hours'=>'0',
								'remarks'=>'Day-off'
							);		
							
						}else{
							$get_details_time_record = $this->db->query("SELECT * FROM emp_time_record where date_attended = '{$time_records[$count++]}' AND emp_id = '{$value->emp_id}'");
							
							foreach($get_details_time_record->result() as $key => $value2){						
							
							$time_values = array(
									0=>$value2->time_1,
									1=>$value2->time_2,
									2=>$value2->time_3,
									3=>$value2->time_4,
									4=>$value2->time_5,
									5=>$value2->time_6											
								);
							
							$time_values = $this->checkIfNotEmpty($time_values);				
							
							$regular_hours = new DateTime(date("H:i",strtotime($this->getTotalHoursRendered($time_values))));
							$overtime_hours = new DateTime(date("H:i",strtotime($this->getComputedOvertime($time_values))));
							
							$regular_hours = $regular_hours->diff($overtime_hours)->format('%H:%I');										
							
							$time_record_total_overtime_rendered = $this->addHours($time_record_total_overtime_rendered,$this->getComputedOvertime($time_values));
							$total_regular_hours = $this->addHours($total_regular_hours,$regular_hours);								
							$time_record_total_late = $this->addHours($time_record_total_late,$this->getComputedLate($value2->time_1,$time_values));
							
							
							$employee_details[$value->emp_id][] = array(
								'emp_id'=>$value->emp_id,
								'fullname'=>$value->fullname,
								'position'=>$value->position,
								'department'=>'Not yet specified.',
								'leave_limit'=>$value->leave_limit,
								'unpaid_leave_limit'=>$value->unpaid_leave_limit,
								'absences'=>$value->absences,		
								'total_regular_hours'=>$total_regular_hours,
								'total_overtime'=>$time_record_total_overtime_rendered,
								'total_late'=>$time_record_total_late,
								'date_attended'=>$this->defaultFormat('M d Y',$value2->date_attended),
								'time_1'=>$this->defaultFormat("g:i a",$value2->time_1,'time'),
								'time_2'=>$this->defaultFormat("g:i a",$value2->time_2,'time'),
								'time_3'=>$this->defaultFormat("g:i a",$value2->time_3,'time'),
								'time_4'=>$this->defaultFormat("g:i a",$value2->time_4,'time'),
								'time_5'=>$this->defaultFormat("g:i a",$value2->time_5,'time'),
								'time_6'=>$this->defaultFormat("g:i a",$value2->time_6,'time'),
								'regular_hours'=>($this->getTotalHoursRendered($time_values) != '00:00' && count($time_values) > 0 ? $regular_hours : "0"),
								'overtime_hours'=>($this->getComputedOvertime($time_values) != '00:00' && count($time_values) > 0 ? $this->getComputedOvertime($time_values) : "0"),
								'remarks'=>$value2->remarks
							);
						}
						}
					}						
									
				}
				
				$this->parseToCsvMultipleRecords('Cereli_DTR_'.$date_range[0].'_'.$date_range[1],$company_details,$employee_details,$daterange,$employee_ids);
			
			}
										
		}	
		
		public function exportRecord($emp_id=null,$type=null,$daterange)
		{
			$emp_id = !is_null($emp_id) && $emp_id != '*' ? $emp_id : null;
			$type = !is_null($type) ? $type : 'csv';
			$daterange = !is_null($daterange) && !empty($daterange) ? $daterange : '';
			
			$date_range = explode('*',$daterange);
			
			if(strlen($emp_id) > 0 && !is_null($emp_id)){				
				$get_employee_details = $this->db->query("SELECT * FROM employees WHERE emp_id IN({$emp_id})");
				
				if(sizeof($get_employee_details->result()) > 0){					
					$company_details = array();					
				
					foreach($this->db->query("SELECT company_name,company_address FROM company_details")->result() as $value){
						$company_details = array(0=>$value->company_name,1=>$value->company_address);
					}				
					$time_record_total_overtime_rendered = 0;
					$time_record_total_late = 0;
					$total_regular_hours = 0;
					
					foreach($get_employee_details->result() as $value){							
						$employee_details = array(
							$value->emp_id,
							$value->fullname,
							$value->position							
						);
						
						$get_time_records = $this->db->query("SELECT * FROM emp_time_record WHERE emp_id = '{$value->emp_id}' AND date_attended BETWEEN '{$date_range[0]}' AND '{$date_range[1]}' ORDER BY date_attended ASC");
						
						$date_range_from = new DateTime($date_range[0]);
						$date_difference = $date_range_from->diff(new DateTime($date_range[1]));
						
						$date_range_length = $date_difference->days;
						
						if($get_time_records->num_rows() > 0){	
							$time_records = array();
							foreach($get_time_records->result() as $row){	
								array_push($time_records,$row->date_attended);
							}
							
							$count = 0;
							for($i=0;$i<=$date_range_length;$i++){		
								
								$this->date_attended = new DateTime($date_range[0]);
								$this->date_attended->add(new DateInterval('P'.$i.'D'));
								$date_attended = $this->date_attended->format('M d Y');
								
								if(!in_array($this->date_attended->format('Y-m-d'),$time_records)){
									$result_records[] = array(
										$date_attended,
										'No time specified',
										'No time specified',
										'No time specified',
										'No time specified',
										'No time specified',
										'No time specified',
										'0',
										'0',
										'Day-off'
									);		
									
								}else{										
									$get_details_time_record = $this->db->query("SELECT * FROM emp_time_record where date_attended = '{$time_records[$count++]}' AND emp_id = '{$value->emp_id}'");
									foreach($get_details_time_record->result() as $row){								
										$time_values = array(
												0=>$row->time_1,
												1=>$row->time_2,
												2=>$row->time_3,
												3=>$row->time_4,
												4=>$row->time_5,
												5=>$row->time_6											
											);
										
										$time_values = $this->checkIfNotEmpty($time_values);				
										
										$regular_hours = new DateTime(date("H:i",strtotime($this->getTotalHoursRendered($time_values))));
										$overtime_hours = new DateTime(date("H:i",strtotime($this->getComputedOvertime($time_values))));
										
										$regular_hours = $regular_hours->diff($overtime_hours)->format('%H:%I');										
											
										
										$result_records[] = array(
											$this->defaultFormat('M d Y',$row->date_attended),
											$this->defaultFormat("g:i a",$row->time_1,'time'),
											$this->defaultFormat("g:i a",$row->time_2,'time'),
											$this->defaultFormat("g:i a",$row->time_3,'time'),
											$this->defaultFormat("g:i a",$row->time_4,'time'),
											$this->defaultFormat("g:i a",$row->time_5,'time'),
											$this->defaultFormat("g:i a",$row->time_6,'time'),
											($this->getTotalHoursRendered($time_values) != '00:00' && count($time_values) > 0 ? $regular_hours : "0"),
											($this->getComputedOvertime($time_values) != '00:00' && count($time_values) > 0 ? $this->getComputedOvertime($time_values) : "0"),
											$row->remarks
										);							
										
										$time_record_total_overtime_rendered = $this->addHours($time_record_total_overtime_rendered,$this->getComputedOvertime($time_values));
										$total_regular_hours = $this->addHours($total_regular_hours,$regular_hours);								
										$time_record_total_late = $this->addHours($time_record_total_late,$this->getComputedLate($row->time_1,$time_values));
																		
									}									
								}
							}
							
						}else{							
							for($i=0;$i<=$date_range_length;$i++){															
								if($i==0){
									$result_records[] = array(
										$this->defaultFormat('M d Y',$date_range[0]),
										'No time specified',
										'No time specified',
										'No time specified',
										'No time specified',
										'No time specified',
										'No time specified',
										'0',
										'0',
										'Day-off'
									);	
									$this->date_attended = $date_range[0];
								}else{
									$this->date_attended = new DateTime($date_range[0]);
									$this->date_attended->add(new DateInterval('P'.$i.'D'));
									$date_attended = $this->date_attended->format('M d Y');
									
									$result_records[] = array(
										$date_attended,
										'No time specified',
										'No time specified',
										'No time specified',
										'No time specified',
										'No time specified',
										'No time specified',
										'0',
										'0',
										'Day-off'
									);								
								}
							}
						}
						$this->date_attended = null;
						switch($type){
							case 'csv' : $this->parseToCsv($result_records,$employee_details,$company_details,$value->fullname.'_'.$date_range[0].'_'.$date_range[1].'_DTR',$total_regular_hours,$time_record_total_overtime_rendered,$value->leave_limit,$value->unpaid_leave_limit,$time_record_total_late);
							case 'pdf' : $this->parseToPdf($result_records,null,$value->fullname.'_'.$date_range[0].'_'.$date_range[1].'_DTR');
						}						
						
					}				
				}				
			}		
		}

	
		public function parseToCsvMultipleRecords($filename,$company_details,$employee_details,$daterange,$emp_id)
		{
			header("Pragma: public");	
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); /** required for certain browsers **/		
			header("Content-disposition: attachment;filename=$filename.csv");
			header("Content-type: binary/octet-stream; char-set=utf8");
			
			$date_range = explode('*',$daterange);
			$date_range_from = new DateTime($date_range[0]);
			$date_difference = $date_range_from->diff(new DateTime($date_range[1]));
			$date_range_length = $date_difference->days;
			
			$header_comma_separator = '';
			$record_comma_separator = '';
			$separator = "\n";
			$counter = 0;
			$data = '';
			$main_headers='';
			$center_headers='';
			$below_headers= '';
			$center_line_separator = ",";
			
			$main_headers .= '"","","","",'.str_replace('"', '""',ucwords($company_details[0])).$separator.'"","","","",'.str_replace('"', '""',ucwords($company_details[1])).$separator.$separator;
			
			for($i=0;$i<=$date_range_length;$i++){
			
				if($i==$date_range_length){
					$center_line_separator = "\n";
				}
				if($i==0){			
					$center_headers .= '"","","","","","","","","","","","","",';
					$center_headers .= date("M d Y",strtotime($date_range[0])).',"","","",""'.$center_line_separator;
				}else{
					$this->export_date_attended = new DateTime($date_range[0]);
					$this->export_date_attended->add(new DateInterval('P'.$i.'D'));
					$date_attended = $this->export_date_attended->format('M d Y');
					$center_headers .= '"","","","",'.$date_attended.',"","","",""'.$center_line_separator;
				}
			}
			$center_line_separator = ",";
			$center_headers .= 'Name,Employee No.,Department,Position,Total Regular Hours,Total Overtime,Total Late,Total Leave,Total Unpaid Leave,';
			
			for($i=0;$i<=$date_range_length;$i++){
				
				if($i==$date_range_length){
					$center_line_separator = "\n";
				}				
				$center_headers .= 'Morning,"",Afternoon,"",Overtime,"",Regular Hours,OT Hours,Remarks'.$center_line_separator;
				
			}
			
			$center_line_separator = ",";
			$data_emp_ids = null;
			$emp_ids = explode(',',$emp_id);
			
			
			for($i=0;$i<count($emp_ids);$i++){				
				for($j=0;$j<=$date_range_length;$j++){	
				
					if($i == 0 && $j == 0){
						$this->export_emp_id = $employee_details[$emp_ids[$i]][$j]['emp_id'];
						$data .= $employee_details[$this->export_emp_id][$j]['fullname'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['emp_id'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['department'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['position'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['total_regular_hours'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['total_overtime'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['total_late'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['leave_limit'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['unpaid_leave_limit'].$center_line_separator;
					}
					if($employee_details[$emp_ids[$i]][$j]['emp_id'] == $this->export_emp_id){						
						$data .= $employee_details[$this->export_emp_id][$j]['time_1'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['time_2'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['time_3'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['time_4'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['time_5'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['time_6'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['regular_hours'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['overtime_hours'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['remarks'].$center_line_separator;
					}else{
						$this->export_emp_id = $employee_details[$emp_ids[$i]][$j]['emp_id'];
						$data .= $separator.$employee_details[$this->export_emp_id][$j]['fullname'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['emp_id'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['department'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['position'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['total_regular_hours'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['total_overtime'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['total_late'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['leave_limit'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['unpaid_leave_limit'].$center_line_separator;
						$data .= $employee_details[$this->export_emp_id][$j]['time_1'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['time_2'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['time_3'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['time_4'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['time_5'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['time_6'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['regular_hours'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['overtime_hours'].$center_line_separator.$employee_details[$this->export_emp_id][$j]['remarks'].$center_line_separator;
					}
				}
			}
			echo "$main_headers\n$center_headers$data\n$below_headers";				
			// print_r($employee_details);			
			exit;
		
		}		
		
		
		public function parseToCsv($rows,$employee_details,$header,$filename,$total_regular_hours,$total_overtime,$total_leave,$total_unpaid_leave,$total_late)
		{
			header("Pragma: public");	
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers		
			header("Content-disposition: attachment;filename=$filename.csv");
			header("Content-type: binary/octet-stream; char-set=utf8");
			
			$header_comma_separator = '';
			$record_comma_separator = '';
			$separator = "\n";
			$counter = 0;
			$data = '';
			$headers='';
			$below_headers= '';
			
			$headers .= '"","","","",'.str_replace('"', '""',ucwords($header[0])).$separator.'"","","","",'.str_replace('"', '""',ucwords($header[1])).$separator.$separator;
			$headers .= 'Name : ,'.$employee_details[1].$separator.'Employee no. : ,'.$employee_details[0].$separator.'Department : ,'.'not yet specified'.$separator.'Position : ,'.$employee_details[2].$separator.$separator;
			$headers .= '"",Morning,"",Afternoon,"",Overtime,"",Regular Hours,OT Hours,Remarks'.$separator;
			$headers .= 'Date,In,Out,In,Out,In,Out,"","",""';		
		  
			foreach($rows as $row){
				$line = '';
				foreach($row as $name => $value){		

					if($counter == 10){							
						$record_comma_separator = '';
						$counter = 0;
					}
					if((!isset($value)) || ($value == ""))				
						$value = "\t";				
					else{
						$value = str_replace('"','""',$value);					
						$value = $record_comma_separator.'"'.$value.'"';
					}
					$record_comma_separator = ',';
					$line .= $value;
					$counter++;
				}

				$data .= trim($line).$separator;            
			}
			$data = str_replace("\r","",$data);
			
			$below_headers .= 'Total  Regular Hours :,'.$total_regular_hours.$separator;
			$below_headers .= 'Total  Overtime :,'.$total_overtime.$separator;
			$below_headers .= 'Total  Late :,'.$total_late.$separator;
			$below_headers .= 'Total  Leave :,'.$total_leave.$separator;
			$below_headers .= 'Total  Unpaid Leave :,'.$total_unpaid_leave.$separator.$separator.$separator;
			$below_headers .= '_____________________,';
			$below_headers .= '"","","","","",Approved By: _____________________'.$separator;		
			$below_headers .= 'Employee\'s Signature,';
			$below_headers .= '"","","","","","","",Supervisor'.$separator;
			
			echo "$headers\n$data\n$below_headers";	
			exit;
		}
		
		public function parseToPdf($result_records,$headers,$filename)
		{
			/* 	// Load the cezpdf library
			  $this->load->library('cezpdf');

			  // Select our font
			  $this->cezpdf->selectFont('../../system/fonts/Helvetica.afm');

			  // Write something in the PDF
			  $this->cezpdf->ezText('Hello World!',12,array('justification' => 'center'));

			  // Send the PDF to the web browser
			  $this->cezpdf->ezStream(); */
			  
			$this->output->enable_profiler(false);
			$this->load->library('parser');
			require_once(APPPATH.'third_party/html2pdf/html2pdf.class.php');

			// set vars
			$tpl_path = '../views/sample_pdf.php';
			$thefullpath = dirname(__FILE__).'/../../uploads/files/file_pdf.pdf';
			$preview = false;
			$previewpath = '../uploads/files/preview_pdf.pdf';


			// PDFs datas
			$datas = array(
			  'first_name' => 'Aldrin',
			  'last_name'  => 'Pernito',
			  'site_title' => 'Cereli Employee Time Manager',
			);

			// Encode datas to utf8
			$tpl_data = array_map('utf8_encode',$datas);


			// 
			// GENERATE PDF AND SAVE FILE (OR OUTPUT)
			//

			$content = $this->parser->parse($tpl_path, $tpl_data, TRUE);
			$html2pdf = new HTML2PDF('P','A4','en', true, 'UTF-8',3);
			$html2pdf->pdf->SetAuthor($tpl_data['site_title']);
			$html2pdf->pdf->SetTitle($tpl_data['site_title']);
			$html2pdf->pdf->SetSubject($tpl_data['site_title']);
			$html2pdf->pdf->SetKeywords($tpl_data['site_title']);
			$html2pdf->pdf->SetProtection(array('print'), '');//allow only view/print
			$html2pdf->WriteHTML($content);
			if (!$preview) //save
			  $html2pdf->Output($thefullpath, 'F');
			else { //save and load
			  $html2pdf->Output($previewpath, 'D');
			}

		}
		
		public function getCompanyTimeRecordRules()
		{			
			$get_time_record_rules = $this->db->query('SELECT * FROM time_record_rules');
			
			if($get_time_record_rules->num_rows() > 0){
				$results = '<div class="company-time-record-rules-container">
							<form action="" method="post" name="time-record-rules" onsubmit="return false;">';
				foreach($get_time_record_rules->result() as $row){
					$this->start_shift_time = $row->start_shift_time;
					$this->end_shift_time = $row->end_shift_time;
					$this->required_hours_rendered = $row->required_hours_rendered;
					$results .= '
							<div class="time-record-rules-container">
								<label title="The required hours to be rendered for the employee.(double-click to edit)" ondblclick="javascript:Manager.__action_time_record_rules(\'edit\',\'required_hours_rendered\');">Required hours rendered(by hours) :</label>
								<input type="hidden" name="id" value="'.$row->id.'"/>
								<input type="text" id="required_hours_rendered" name="required_hours_rendered" onchange="Manager.__action_time_record_rules(\'set\',\'required_hours_rendered\');" value="'.$this->defaultFormat('h:i:s',$row->required_hours_rendered,'time').'" disabled="disabled"/>
								<span class="time-record-rule-save-btn" id="save-btn-required_hours_rendered" onclick="Manager.__action_time_record_rules(\'save\');"><img src="'.base_url().'/resources/images/save.png" title="Save changes"/></span>
								<span class="time-record-rule-cancel-btn" id="cancel-btn-required_hours_rendered" onclick="Manager.__action_time_record_rules(\'cancel\',\'required_hours_rendered\');"><img src="'.base_url().'/resources/images/delete.png" title="Cancel editing"/></span>
							</div>
							
							<div class="time-record-rules-container">
								<label title="The start shift time of the company.(double-click to edit)" ondblclick="javascript:Manager.__action_time_record_rules(\'edit\',\'start_shift_time\');">Start shift time(hrs:min:sec format) :</label>
								<input type="text" id="start_shift_time" name="start_shift_time" onchange="Manager.__action_time_record_rules(\'set\',\'start_shift_time\');" value="'.$this->defaultFormat('H:i a',$row->start_shift_time,'time').'" title="'.$this->defaultFormat('h:i a',$row->start_shift_time,'time').'" disabled="disabled"/>
								<span class="time-record-rule-save-btn" id="save-btn-start_shift_time" onclick="Manager.__action_time_record_rules(\'save\');"><img src="'.base_url().'/resources/images/save.png" title="Save changes"/></span>
								<span class="time-record-rule-cancel-btn" id="cancel-btn-start_shift_time" onclick="Manager.__action_time_record_rules(\'cancel\',\'start_shift_time\');"><img src="'.base_url().'/resources/images/delete.png" title="Cancel editing"/></span>
							</div>
							
							<div class="time-record-rules-container">
								<label title="The start shift time of the company.(double-click to edit)" ondblclick="Manager.__action_time_record_rules(\'edit\',\'end_shift_time\');">End shift time(hrs:min:sec format) :</label>
								<input type="text" id="end_shift_time" name="end_shift_time" onchange="Manager.__action_time_record_rules(\'set\',\'end_shift_time\');" value="'.$this->defaultFormat('H:i a',$row->end_shift_time,'time').'" title="'.$this->defaultFormat('h:i a',$row->end_shift_time,'time').'" disabled="disabled"/>
								<span class="time-record-rule-save-btn" id="save-btn-end_shift_time" onclick="Manager.__action_time_record_rules(\'save\');"><img src="'.base_url().'/resources/images/save.png" title="Save changes"/></span>
								<span class="time-record-rule-cancel-btn" id="cancel-btn-end_shift_time" onclick="Manager.__action_time_record_rules(\'cancel\',\'end_shift_time\');"><img src="'.base_url().'/resources/images/delete.png" title="Cancel editing"/></span>
								<div class="time_record_rules_message_content"></div>	
							</div>							
						';						
				}
				$results .= '
							</form>
						</div>';
			}
			else
				$results = '<div class="company-time-record-rules-container">No Time Record Rules recorded.</div>';
				
			return $results;
		}
		
		
		public function getSearchableColumns($page)
		{
			$page = isset($page) ? $page : null;			
			if(!is_null($page))
			{
				switch($page)
				{					
					case 'employees' : 
									$get_searchable_columns = mysql_query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'cereli_employee_time_manager' AND TABLE_NAME = 'employees' AND COLUMN_NAME IN('emp_id','position','fullname')");
									$items = array();
									while($row = mysql_fetch_assoc($get_searchable_columns))
										$items[] = $row; 																
							
									break;
					case 'reports' : 
									$get_searchable_columns = mysql_query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'cereli_employee_time_manager' AND TABLE_NAME = 'employee_reports' AND COLUMN_NAME IN('type','status')");
									$items = array();
									while($row = mysql_fetch_assoc($get_searchable_columns))
										$items[] = $row; 																
							
									break;
				}
				return $items;
			}
			
			return false;
		}

		public function getEmployeeRecentActivities($limit=0,$offset=10)
		{
			$get_recent_activities = $this->db->query("SELECT * FROM employee_activities ORDER BY date_committed DESC LIMIT {$limit},{$offset}");
			$result = array();
			if($get_recent_activities->num_rows() > 0){								
				$result['total_rows'] = $get_recent_activities->num_rows();
				$result['contents'] = '';
				foreach($get_recent_activities->result() as $key => $value){
					$result['contents'] .= '
						<div class="recent-activities-container" id="record-'.$value->id.'">
							<div class="ra-date-committed"><span>Date committed : '.$this->defaultFormat("M d Y g:i a",$value->date_committed).'</span></div>
							<div class="ra-type"><span>Action type : '.$value->type.'</span></div>
							<div class="ra-remarks"><span>Remarks : '.$value->remarks.'</span></div>
							<div class="ra-status"><span>Status : '.$value->status.'</span></div>
						</div>
					';
				}
			}
			else
				$result['contents'] = 'No Recent Activities yet.';
			
			return $result;		
		}	
		
		public function defaultFormat($format,$date,$type='date')
		{	
			if($date == '00:00:00' || $date == '0000:00:00 00:00:00')
				return "no $type specified";				
			
			if(!empty($date)){
				return date($format,strtotime($date));
			}
			
		}
		
		public function getEmployeeDetails($id)
		{
			$id = !is_null($id) ? $id : null;
			
			$get_employee_details = $this->db->query('SELECT * FROM employees WHERE emp_id = \''.$id.'\'');
		
			if(count($get_employee_details->result()) > 0){
				
				$result = array();
				$result['contents'] = '<div class="employee-details-wrapper">
					<div class="employee-details-container">';
				foreach($get_employee_details->result() as $row){
					$result['contents'] .= '
						<div class="employee-field-container">
							<div class="employee-field-label">Employee I.D. :</div>
							<div class="employee-field-input"><span><input type="text" name="emp_id" value="'.$row->emp_id.'" title="Reference for the employees as identifier."/><input type="hidden" name="id" value="'.$row->id.'"/></span></div>
							<div class="employee-field-info note">Reference for the employees as identifier.</div>
						</div>

						<div class="employee-field-container">
							<div class="employee-field-label">Full Name :</div>
							<div class="employee-field-input"><span><input type="text" name="fullname" value="'.$row->fullname.'"/></span></div>
							<div class="employee-field-info note">The employee\'s full name used in the company.</div>
						</div>
						
						<div class="employee-field-container">
							<div class="employee-field-label">Position :</div>
							<div class="employee-field-input"><span><input type="text" name="position" value="'.$row->position.'"/></span></div>
							<div class="employee-field-info note">The employee\'s designation/position in the company.</div>
						</div>
						
						<div class="employee-field-container">
							<div class="employee-field-label">Leave Spent :</div>
							<div class="employee-field-input"><span><input type="text" name="leave_limit" value="'.$row->leave_limit.'"/></span></div>
							<div class="employee-field-info note">The employee\'s leave spent for the company.</div>
						</div>
						
						<div class="employee-field-container">
							<div class="employee-field-label">Unpaid Leave Spent :</div>
							<div class="employee-field-input"><span><input type="text" name="unpaid_leave_limit" value="'.$row->unpaid_leave_limit.'"/></span></div>
							<div class="employee-field-info note">The employee\'s unpaid leave spent for the company.</div>
						</div>
						
						<div class="employee-field-container">
							<div class="employee-field-label">Absences :</div>
							<div class="employee-field-input"><span><input type="text" name="absences" value="'.$row->absences.'"/></span></div>
							<div class="employee-field-info note">The employee\'s absences spent for the company.</div>
						</div>						
						
						<div class="employee-field-container">
							<div class="employee-field-label">Total Late/Tardy :</div>
							<div class="employee-field-input">
								<textarea rows="3" cols="25" name="late" readonly="readonly">'.$this->getEmployeeTotalLate($row->emp_id).'</textarea>
								<div class="employee-field-info note">The employee\'s late spent for the company.</div>							
							</div>
						</div>
						
						<div class="employee-field-container">
							<div class="employee-field-label">Total overtime rendered:</div>
							<div class="employee-field-input">
								<textarea rows="3" cols="25" name="overtime" readonly="readonly">'.$this->getEmployeeTotalOvertimeRendered($row->emp_id).'</textarea>
								<div class="employee-field-info note">The employee\'s overtime spent for the company.</div>							
							</div>
						</div>
						
						<div class="employee-field-container">
							<div class="employee-field-label">Record Status :</div>
							<div class="employee-field-input">
								<select name="record_status">
									<option value="Active" '.($row->record_status == 'Active' ? 'selected="selected"' : '').'>Active</option>
									<option value="Inactive" '.($row->record_status == 'Inactive' ? 'selected="selected"' : '').'>Inactive</option>
								</select>
							</div>
							<div class="employee-field-info note">Indicates the employee status as for the present moment for the company.</div>
						</div>						
						
						<div class="dialog_message_content"></div>						
					';
				}
				$result['contents'] .= '
						</div>
					</div>';
				return $result;
			}
			
			return false;
			
		}
		
		public function importRecord($file)
		{
			$file = !empty($file) ? $file : false;			
			$results = '';
			
			if($file['import-file']['size'] > 0){
				$target_path = 'uploads/files/';
				$target_path = $target_path . basename($file['import-file']['name']);				
				$move_uploaded = move_uploaded_file($file['import-file']['tmp_name'], $target_path);
				
				if($move_uploaded){
				
					//get the csv file 					
					$handle = fopen($target_path,"r"); 
					$insert_data_employee = array();$insert_data_time = array();					
					$insert_counter = 0;$data_counter = 0;
					
					if($handle !== false){					
						$counter = 0;
						$insert_data = array();	
						$row = 0;
						while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						
							if($this->row == 1){								
								$this->current_date = $data[6];					
								$this->current_emp_id = $data[2];					
								$this->current_import_time = $data[7];							
								$this->time_record['time'][$this->current_emp_id][$this->current_date]['date_attended']= $this->defaultFormat('Y-m-d',$data[6]);
								$this->time_record['time'][$this->current_emp_id][$this->current_date]['emp_id']= $data[2];			
								$this->time_record['time'][$this->current_emp_id][$this->current_date]['time_'.$this->row]= $this->defaultFormat('H:i:s',$data[7]);									
								$insert_data[$this->current_emp_id][$this->current_date] = $this->time_record['time'][$this->current_emp_id][$this->current_date];
							}
							
							if($this->checkSameEmployee($data[2],$this->current_emp_id)){					
								if($this->checkSameDate($data[6],$this->current_date)){	
									if($this->row != 1){
										if($this->checkTimeIsTimeout($data[7],$this->current_import_time)){										
											if($this->time_row == true){												
													$this->row = $this->row - 1;
											}
											if($row >= 3 || $row >= 2){
												$this->row = $this->row - ($row - 1);
											}
											$this->time_record['time'][$this->current_emp_id][$this->current_date]['time_'.$this->row]= $this->defaultFormat('H:i:s',$data[7]);										
											$this->time_row = false;
										}else{
											$this->time_row = true;									
											$row++;
										}
									}
								}else{	
									$row = 0;
									$this->time_record['time'][$this->current_emp_id][$this->current_date]['date_attended']= $this->defaultFormat('Y-m-d',$data[6]);
									$this->time_record['time'][$this->current_emp_id][$this->current_date]['emp_id']= $data[2];	
									$this->current_import_time = $data[7];								
									$this->time_record['time'][$this->current_emp_id][$this->current_date]['time_'.$this->row]= $this->defaultFormat('H:i:s',$data[7]);																	
								}		
								$insert_data[$this->current_emp_id][$this->current_date] = $this->time_record['time'][$this->current_emp_id][$this->current_date];
							}else{								
								$this->row = 1;						
								$this->current_date = $data[6];					
								$this->current_emp_id = $data[2];
								$this->current_import_time = $data[7];
								$this->time_record['time'][$data[2]][$data[6]]['date_attended']= $this->defaultFormat('Y-m-d',$data[6]);								
								$this->time_record['time'][$data[2]][$data[6]]['emp_id']= $data[2];	
								$this->time_record['time'][$data[2]][$data[6]]['time_'.$this->row]= $this->defaultFormat('H:i:s',$data[7]);																	
								$insert_data[$data[2]][$data[6]] = $this->time_record['time'][$data[2]][$data[6]];								
							}	

							if(!in_array($data[3],$insert_data_employee)){							
								$insert_data_employee[$this->current_emp_id]['emp_id'] = $data[2];
								$insert_data_employee[$this->current_emp_id]['fullname'] = $data[3];									
							}							
								
							$this->row++;						
							$counter++;
						}				

						fclose($handle);						

						foreach($insert_data as $key => $value) {	

							foreach($value as $time_record) {	

								if(!$this->checkEmployeeExists($key)) {									
									if($this->db->insert('employees',$insert_data_employee[$key])){
										$data_counter++;	
										$total_saved_time_records = $this->db->count_all_results();
									}
								}								
								
								/**
								* Check if employee time record already exists if yes, update the record else insert the new record
								**/
							
								$checkTimerecord = $this->db->where('emp_id',$key)->where('date_attended',$time_record['date_attended'])->get('emp_time_record');								
								
								if ( $checkTimerecord->num_rows() == 0 ) {									

									if($this->db->insert('emp_time_record',$this->sortTime($time_record))){
										$insert_counter++;	
										$total_saved_time_records = $this->db->count_all_results();
									}
								} else {								
									// update existing time record for the employee
									$updatedTimerecord = $this->filterTimeRecord($checkTimerecord->result(),$time_record);
									$insert_counter++;	
									$this->db->where('emp_id',$key)->where('date_attended',$time_record['date_attended'])->update('emp_time_record',$updatedTimerecord);
								}
							}
						}


						if($insert_counter > 0){							
							$results = 'Import file data('.$file['import-file']['name'].') successful.<br/>';
							
							$total_records_added = array(
									'total_employees'=>$data_counter,	
									'total_time_records'=>$insert_counter
								);
							
							$file_uploaded = explode('.',basename($file['import-file']['name']));							
							$result_save_activities = $this->saveActionToActivities('importing a file',$file_uploaded[1],$file['import-file']['name'],$total_records_added);

							$results .= $result_save_activities;
						}else{							
							$results = 'Import file action occurred an error.';
						}					
					}
					else
						$results = 'Import file action occurred an error.';
					
					return $results;
				}
			}
		}

		protected function filterTimeRecord( $oldTimerecord, $newTimerecord ) 
		{			

			$newTimerecord = array_slice($newTimerecord, 2);
			$oldTimerecord = array_slice((array)$oldTimerecord[0],3);

			unset($oldTimerecord['remarks']);
			$updatedTimerecord = array_unique(array_merge(array_values($oldTimerecord),array_values($newTimerecord)));		

			asort($updatedTimerecord);

			foreach ($updatedTimerecord as $key => $val) {
				if ( $val == '00:00:00' || null == $val) {
					unset($updatedTimerecord[$key]);
				} 
			}	

			$tempTimerecord = array();
			$counter = 1;
			foreach( array_values($updatedTimerecord) as $value) {
				$tempTimerecord['time_' . $counter] = $value;
				$counter++;
			}

			$updatedTimerecord = $tempTimerecord;
			unset($tempTimerecord);
			
			return $updatedTimerecord;
		}
		
		public function checkTimeIsTimeout($time,$compare_time)
		{
			if(is_null($compare_time) || $compare_time == null)
				return true;
			
			if(strtotime($time) >= strtotime($this->addHours($compare_time,'00:15'))){
				$this->current_import_time = $time;
				return true;
			}
			else 
				return false;	
				
		}
		
		public function saveTimeRecordRules($data=array())
		{
			if(isset($data) && is_array($data)){
				$this->db->where('id', $data['id']);
				$save_time_record_rules = $this->db->update('time_record_rules', $data); 
				$return = array();
				if($save_time_record_rules){					
					$return['status'] = 'true';
					$return['contents'] = 'Time record rules saved.';
				}else{
					$return['status'] = 'false';
					$return['contents'] = 'Saving time record rules occurred an error.';			
				}
				
				return $return;
			}
			
		}
		
		public function saveCompanyDetails($data=array())
		{
			if(isset($data) && is_array($data)){
				$this->db->where('id', $data['id']);
				$save_time_record_rules = $this->db->update('company_details', $data); 
				$return = array();
				if($save_time_record_rules){					
					$return['status'] = 'true';
					$return['contents'] = 'Company details saved.';
				}else{
					$return['status'] = 'false';
					$return['contents'] = 'Saving company details occurred an error.';			
				}
				
				return $return;
			}
			
		}
		
		public function sortTime($time_record)
		{
			if(is_array($time_record)){				
				$temp_1st = array();
				$temp_2nd = array();
				$date_and_emp_id = array_slice($time_record,0,2);
				$time_record = array_slice($time_record,2);
				$total = count($time_record);
				for($i=1;$i<$total;$i++){		
					$temp_1st[$i] = $time_record['time_'.$i];					
					if(strtotime($time_record['time_'.$i]) > strtotime($time_record['time_'.($i+1)])) {
							$time_record['time_'.$i] = $time_record['time_'.($i+1)];							
							$time_record['time_'.($i+1)] = $temp_1st[$i];							
							
												
					}
					for($j=1;$j<$total;$j++){
						$temp_2nd[$j] = $time_record['time_'.$j];							
						if(strtotime($time_record['time_'.$j]) > strtotime($time_record['time_'.($j+1)])) {
							$time_record['time_'.$j] = $time_record['time_'.($j+1)];							
							$time_record['time_'.($j+1)] = $temp_2nd[$j];			
						}
					}
				}					
				return array_merge($date_and_emp_id,$time_record);				
			}
		}
		
		public function checkSameDate($date,$comparedate){	
			if(strtotime($date) == strtotime($comparedate)){
				$this->current_date = $date;	
				return true;
			}elseif(strtotime($date) > strtotime($comparedate)){
				$this->row = 1;
				$this->current_date = $date;			
				return false;
			}		
		}

		public function checkSameEmployee($emp_id,$compare_emp_id){	
			if($emp_id == $compare_emp_id){
				$this->current_emp_id = $emp_id;		
				return true;
			}				
			return false;
		}		
		
		public function checkEmployeeExists($id)
		{
			if(!empty($id)){
				
				if(count($this->db->query('SELECT emp_id FROM employees where emp_id = '.$id.'')->result()) > 0){
					return true;
				}else
					return false;
			}			
			return false;
		}
		
		public function updateEmployeeDetails($data = array())
		{			
			if(!empty($data['emp_id']) && strlen($data['emp_id']) > 0){
				$results = array();					
				$this->db->where('id', $data['id']);
				$query_update_employee_details = $this->db->update('employees', $data); 
				
				if($query_update_employee_details){
					$details = 'details of the employee';
					$results['activities'] = $this->saveActionToActivities('updating employee details',$data['fullname'],$details,'1');
					$results['contents'] = 'Employee details saved.';
				}
			}
			
			return $results;
		}	
		
		public function searchEmployee($data_query=null,$view_type,$start,$offset)
		{
			if(!empty($data_query) && strlen($data_query) > 0){
				$results = array();			
				
				$get_employee_details = $this->db->query($data_query);
				
				if(sizeof($get_employee_details->result()) > 0){
					$id = array();
					foreach($get_employee_details->result() as $row){
						$id[] = $row->emp_id;
					}
					
					if(isset($view_type)){			
						if($view_type == 'table'){
							$results = $this->getEmployeeTimeRecord($id,$start,$offset,null,null);			
						}else if($view_type == 'tabs'){
							$results = $this->getEmployeeTimeRecordTabsFormat($id,$start,$offset,null,null);									
						}								
					}				
				}
				else
					$results['contents'] = '<div class="error-msg">Employee details not found.</div>';
				
				return $results;
			}
		}
		
		public function saveActionToActivities($action_type,$type_value,$data,$records)
		{
			$action_type = !is_null($action_type) && !empty($action_type) ? $action_type : null;
			$type_value = !is_null($type_value) && !empty($type_value) ? $type_value : null;
			$data = is_file($data) && !empty($data) ? $data : null;
			$records = !empty($records) && count($records) > 0 ? $records : null; 
			$results = '';
			$data_check = array('action_type'=>$action_type,'type_value'=>$type_value,'data'=>$data,'records'=>$records);
			
			if($this->isEmpty($data_check)){
				$data_store = array(
						'type'=>ucwords($data_check['action_type']),
						'date_committed'=>date('Y-m-d H:i:s'),
						'remarks'=>ucwords($data_check['data']).' '.($action_type == 'importing a file' ? $data_check['records']['total_employees'].' employees and '.$data_check['records']['total_time_records'].' time records ' : $data_check['records']).' record(s)',
						'status'=>'Success'
				);
				$save_as_activity = $this->db->insert('employee_activities',$data_store);
				
				if($save_as_activity)
					$results = ucwords($data_check['action_type']).' '.($action_type == 'importing a file' ? $data_check['records']['total_employees']+$data_check['records']['total_time_records'] : $data_check['records']).' is recorded on the employee activities.';
			}
			return $results;
			
		}
		
		public function isEmpty($data = array())
		{
			if(count($data) > 0){
				foreach($data as $key => $value):
					if(!is_null($value) || !empty($value)){
						return true;
						break;
					}
				endforeach;
			}
			
			return false;
		}
		
		public function getAvailableRecords($type)
		{		
			if(!empty($type) && strlen($type) > 0){
				$data = '';
				$get_available_records = $this->db->query("SELECT DISTINCT {$type} as data FROM employees");
				$total = count($get_available_records->result());
				
				if($total > 0){
					$counter = 0;
					$delimiter = ',';
					foreach($get_available_records->result() as $value){
					
						if($counter == ($total-1))
							$delimiter = '';						
						if(isset($value->data)){
							$data .= '"'.$value->data.'"'.$delimiter;
						}
						
						$counter++;
					}
					
					if(strlen($data) > 0){
						return $data;
					}
						return '';
				}
			}
		}
		
		public function getCreateEmployeeTimeRecordAction($type,$id=null,$fullname=null,$date=null,$remarks=null,$status_type=null)
		{
		
			if(isset($type) && !is_null($type)){
				$results = array();
				switch($type){
					case 'set' : 
								$results['contents'] = '
										<div class="employee-create-time-record-wrapper">										
											<div class="employee-create-time-record-content">
												<div class="employee-create-time-record-field-container">
													<label>Employee I.D. : </label> '.$id.'
													<input type="hidden" name="emp_id" value="'.$id.'"/>
												</div>
												<div class="employee-create-time-record-field-container">
													<label>Employee name : </label> '.str_replace('-',' ',$fullname).'													
												</div>
												<div class="employee-create-time-record-field-container">
													<label>Date Attended :</label> 
													<input type="text" name="date_attended" value="'.$this->defaultFormat('M d Y',$date).'" readonly="readonly"/>
												</div>												
												<div class="employee-create-time-record-field-container">
													<label>Type : </label> 
													<input type="radio" name="type" value="absent"/>Absent
													<input type="radio" name="type" value="paid"/>Paid Leave
													<input type="radio" name="type" value="unpaid"/>Unpaid Leave
												</div>
												<div class="employee-create-time-record-field-container">
													<label class="employee-create-time-record-remarks">Remarks : </label> 
													<textarea rows="4" cols="25" name="remarks" placeholder="Put remarks"></textarea>
												</div>
												<div class="employee-create-time-record-field-container">
													<div class="dialog_message_content"></div>
												</div>												
											</div>
										</div>
								';
								break;
					case 'save' : 
								$date = !is_null($date) && count(explode('-',$date)) >= 3 ? $this->defaultFormat('Y-m-d',$date) : '0000-00-00';
								$remarks = !is_null($remarks) && isset($remarks) && $remarks != 'undefined' ? str_replace('%20',' ',addslashes($remarks)) : 'No remarks';
								$results['data_query'] = "INSERT INTO emp_time_record(emp_id,date_attended,remarks) VALUES('{$id}','{$date}','{$remarks}')";
								if($this->db->query("SELECT emp_id FROM emp_time_record WHERE date_attended = '{$date}' AND emp_id = '{$id}'")->num_rows() == 0){
									$save_time_record = $this->db->query("INSERT INTO emp_time_record(emp_id,date_attended,remarks) VALUES('{$id}','{$date}','{$remarks}')");
									
									if(!is_null($status_type)){
										switch($status_type){
											case 'absent' : 
															$get_employee_absences = $this->db->query("SELECT absences FROM employees where emp_id = '{$id}'");
															foreach($get_employee_absences->result() as $row):
																$absences = $row->absences + 1;																							
															endforeach;															
															$save_employee_details = $this->db->query("UPDATE employees SET absences = '{$absences}' where emp_id = '{$id}'");
															break;
											case 'paid' : 
															$get_employee_paid_leave = $this->db->query("SELECT leave_limit FROM employees where emp_id = '{$id}'");
															foreach($get_employee_paid_leave->result() as $row):
																$paid_leave = $row->leave_limit + 1;																							
															endforeach;															
															$save_employee_details = $this->db->query("UPDATE employees SET leave_limit = '{$paid_leave}' where emp_id = '{$id}'");
															break;
											case 'unpaid' : 
															$get_employee_unpaid_leave = $this->db->query("SELECT unpaid_leave_limit FROM employees where emp_id = '{$id}'");
															foreach($get_employee_unpaid_leave->result() as $row):
																$unpaid_leave = $row->unpaid_leave_limit + 1;																							
															endforeach;															
															$save_employee_details = $this->db->query("UPDATE employees SET unpaid_leave_limit = '{$unpaid_leave}' where emp_id = '{$id}'");
															break;	
											default : break;
										}
									}
									if($save_time_record){
										$results['contents'] = 'Time record details saved.';
									}else
										$results['contents'] = 'Saving time record details occurred an error.';
								}else
									$results['contents'] = 'Time record details already exists.';
								break;
				}
				return $results;
				
			}
		}
	}
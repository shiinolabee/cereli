<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<?php echo $this->load->view('header'); ?>
<body>
	<div class="parent-wrapper">	
		<div class="menu-wrapper">
			<?php $this->load->view('menu');?>
		</div>
		<div class="content-wrapper">
			<?php $this->load->view('version_header');?>
			<?php $this->load->view('search_filter');?>
			<div class="content">
				<div class="content-label">
					<label>Employees</label>
				</div>		
				<?php 
					if($this->pagination->create_links()){
							echo '
								<div class="content-action-options">
									'.$this->pagination->create_links().'
								</div>
							';					
					}
				?>
				<div class="load-content" data-url="/manager/employee_time_record" data-content="<?php echo '/*/'.$this->employee_limit.'/'.$this->employee_offset;?>">Loading content...</div>
			</div>
			<div class="queue-export-content-container">
				<div class="queue-export-content-label">
					<label>Status</label>
					<div class="queue-export-show-hide-btn" onclick="Manager.__action_show_status_queue(this);" data-status="show">Show</div>
				</div>
				<div class="queue-export-contents-wrapper">
					<div class="queue-export-content-header">
						No actions made
					</div>					
				</div>
			</div>
		</div>
		<?php echo $this->load->view('footer');?>
	</div>
	<script type="text/javascript">
		
		$(function(){
			
			document.getElementById('import-file').addEventListener('change', Manager.__handler_file_select, false);			
			
			var employee_tags = [<?php echo $this->get_available_employee_ids.','.$this->get_available_fullnames.','.$this->get_available_positions; ?>];
	
			$('#field-emp_id,#field-fullname,#field-position').autocomplete({
			  source: employee_tags,
			  autoFocus:true
			});
			
			$('#import-submit').button();
			
			$('#open-calendar').datepicker({
				changeMonth:true,
				showButtonPanel: true,
				changeYear:true,
				dateFormat:'yy-mm-dd',
				numberOfMonths: 3,
				maxDate: Date.today().toString('YY-mm-DD'),
				onSelect:function(selectedDate,o){
					$(this).attr('title',new Date(selectedDate).toString('dddd, MMMM d,yyyy'));
					$(this).tooltip({
						position: {
							my: "center bottom-20",
							at: "center top",
							using: function( position, feedback ) {
								$( this ).css( position );
								$( "<div>" )
									.addClass( "arrow" )
									.addClass( feedback.vertical )
									.addClass( feedback.horizontal )
									.appendTo( this );
							}
						}
					});
					Manager.__calendar_search(this);
					Manager.initiate_state_tooltip();
				}
			});
			
			$('#open-calendar-from').datepicker({
				defaultDate: "+1w",
				showButtonPanel: true,
				changeMonth:true,
				changeYear:true,
				dateFormat:'yy-mm-dd',				
				numberOfMonths: 3,
				maxDate: Date.today().toString('YY-mm-DD'),
				onClose: function( selectedDate ) {
					$('#open-calendar-to').datepicker( "option", "minDate", selectedDate );
				},
				onSelect:function(selectedDate,o){
					$(this).attr('title',new Date(selectedDate).toString('dddd, MMMM d,yyyy'));
					$(this).tooltip({
						position: {
							my: "center bottom-20",
							at: "center top",
							using: function( position, feedback ) {
								$( this ).css( position );
								$( "<div>" )
									.addClass( "arrow" )
									.addClass( feedback.vertical )
									.addClass( feedback.horizontal )
									.appendTo( this );
							}
						}
					});
					Manager.__calendar_search_range(this,'from');
					Manager.initiate_state_tooltip();
				}
			});
			
			$('#open-calendar-to').datepicker({
				showButtonPanel: true,
				defaultDate: "+1w",
				changeMonth:true,
				changeYear:true,
				dateFormat:'yy-mm-dd',
				numberOfMonths: 3,
				maxDate: Date.today().toString('YY-mm-DD'),				
				onSelect:function(selectedDate,o){
					$(this).attr('title',new Date(selectedDate).toString('dddd, MMMM d,yyyy'));
					$(this).tooltip({
						position: {
							my: "center bottom-20",
							at: "center top",
							using: function( position, feedback ) {
								$( this ).css( position );
								$( "<div>" )
									.addClass( "arrow" )
									.addClass( feedback.vertical )
									.addClass( feedback.horizontal )
									.appendTo( this );
							}
						}
					});
					Manager.__calendar_search_range(this,'to');
					Manager.initiate_state_tooltip();
				}
			});
			
			$('#trigger-csv-option-datepicker-from').datepicker({
				defaultDate: "+1w",
				showButtonPanel: true,
				changeMonth:true,
				changeYear:true,
				dateFormat:'yy-mm-dd',				
				numberOfMonths: 3,
				maxDate: Date.today().toString('YY-mm-DD'),
				onClose: function( selectedDate ) {
					$('#trigger-csv-option-datepicker-to').datepicker( "option", "minDate", selectedDate );
				},
				onSelect:function(selectedDate,o){
					$(this).attr('title',new Date(selectedDate).toString('dddd, MMMM d,yyyy'));
					$(this).tooltip({
						position: {
							my: "center bottom-20",
							at: "center top",
							using: function( position, feedback ) {
								$( this ).css( position );
								$( "<div>" )
									.addClass( "arrow" )
									.addClass( feedback.vertical )
									.addClass( feedback.horizontal )
									.appendTo( this );
							}
						}
					});				
					Manager.initiate_state_tooltip();
				}
			});
			
			$('#trigger-csv-option-datepicker-to').datepicker({
				showButtonPanel: true,
				defaultDate: "+1w",
				changeMonth:true,
				changeYear:true,
				dateFormat:'yy-mm-dd',
				numberOfMonths: 3,
				maxDate: Date.today().toString('YY-mm-DD'),				
				onSelect:function(selectedDate,o){
					$(this).attr('title',new Date(selectedDate).toString('dddd, MMMM d,yyyy'));
					$(this).tooltip({
						position: {
							my: "center bottom-20",
							at: "center top",
							using: function( position, feedback ) {
								$( this ).css( position );
								$( "<div>" )
									.addClass( "arrow" )
									.addClass( feedback.vertical )
									.addClass( feedback.horizontal )
									.appendTo( this );
							}
						}
					});
					Manager.initiate_state_tooltip();
				}
			});
			
			$('.action-calendar').datepicker({
				showButtonPanel: true,
				dateFormat:'yy-mm-dd',
				maxDate: Date.today().toString('YY-mm-DD'),	
				showWeek: true,
				dayNames:[ "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ],
				onChangeMonthYear:function(changeYear,changeMonth,objectInstance){															
					Manager.__get_employee_time_records(changeYear,changeMonth,null);					
				}
			});
			
			var action_filter = $('#action-filter').tabs({
				collapsible: false
			});
			
			action_filter.find( ".ui-tabs-nav" ).sortable({
				axis: "x",
				stop: function() {
					action_filter.tabs( "refresh" );
				}
			});
			
			$('#start_shift_time,#end_shift_time').timepicker({
				hourGrid: 4,
				minuteGrid: 10,
				timeFormat: 'HH:mm tt'
			});	

			$('#required_hours_rendered').timepicker({
				hourGrid: 4,
				minuteGrid: 10,
				timeFormat: 'HH:mm:ss'
			});				
			   
			$('form#form-import').ajaxForm({
				beforeSend: function() {
					$('.message_content').empty();					
					var percentVal = '0%';
					$('.import-bar').width(percentVal);
					$('.import-progress').fadeIn('normal');
					$('.percent').html(percentVal);
				},
				uploadProgress: function(event, position, total, percentComplete) {
					var percentVal = percentComplete + '%';
					$('.import-bar').width(percentVal)
					$('.percent').html(percentVal);
					//console.log(percentVal, position, total);
				},
				success: function(data) {
					var percentVal = '100%';
					$('.import-bar').width(percentVal)
					$('.percent').html(percentVal);
					$('.message_content').addClass('success-msg');
					$('.message_content').html(data.contents);					
				},
				complete: function(xhr) {							
					$('.percent').html('Complete!');
					$('.message_content').html(xhr.responseText);
				},
				error:function(xhr,response,errorResponse){
					$('.message_content').removeClass('success-msg');
					$('.message_content').addClass('error-msg');
					$('.message_content').html(xhr.responseText);
				}
			}); 
			
		});	
	</script>
</body>
</html>
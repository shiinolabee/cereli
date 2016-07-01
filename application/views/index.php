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
			<?php $this->load->view('search_filter_home');?>
			<div class="content">
				<div class="content-label">
					<label>Recent Activities(Logs)</label>
				</div>
				<div class="content-action-options">
					<?php echo $this->pagination->create_links();?>
				</div>
				<div class="load-content" data-url="/index.php/manager/employee_recent_activities" data-content="<?php echo '/'.$this->recent_activities_limit.'/'.$this->recent_activities_offset;?>">Loading content...</div>
			</div>
		</div>
		<?php echo $this->load->view('footer');?>
	</div>
	<script type="text/javascript">
		$(document).ready(function(){
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
		});
	</script>
</body>
</html>
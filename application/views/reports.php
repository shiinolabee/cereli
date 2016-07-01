<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<?php echo $this->load->view('header'); ?>
<body>
	<div class="parent-wrapper">
		<div class="menu-wrapper">
			<?php $this->load->view('menu');?>
		</div>
		<div class="content-wrapper">
			<?php $this->load->view('search_filter');?>
			<div class="content">
				<div class="content-label">
					<label>Reports</label>
				</div>				
				<div class="load-content" data-url="/index.php/manager/employee_reports" data-content="">Loading...</div>
			</div>
		</div>
		<?php echo $this->load->view('footer');?>
	</div>
	<script>
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
		});
	</script>
</body>
</html>
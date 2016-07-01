<div class="menu-container">
	<div class="menu">
		<ul id="ul-menu">
			<li><a href="<?php echo base_url(); ?>" onclick="return Manager.__redirect(\'index\',this);" id="redirect-index" data-url="false" title="You can view here the recent activities of the company."><span><img src="<?php echo base_url(); ?>/resources/images/home.png"/></span>Home</a></li>
			<li><a href="<?php echo base_url(); ?>index.php/manager/employees" id="redirect-employees" data-url="false" title="You can see here the employee details and time records for the company."><span><img src="<?php echo base_url(); ?>/resources/images/employees.png"/></span>Employees</a></li>
			<li><a href="<?php echo base_url(); ?>index.php/manager/reports" id="redirect-reports" data-url="false" title="You can see here the employee time record reports for the company."><span><img src="<?php echo base_url(); ?>/resources/images/reports.png"/></span>Reports</a></li>
			<li><a href="<?php echo base_url(); ?>index.php/manager/accounting" id="redirect-accounting" data-url="false" title="You can see here the employee\'s transactions of time records and other reports."><span><img src="<?php echo base_url(); ?>/resources/images/accounting.png"/></span>Accounting</a></li>
			<li><a href="<?php echo base_url(); ?>index.php/manager/departments" id="redirect-departments" data-url="false" title="You can see here the different departments of the company."><span><img src="<?php echo base_url(); ?>/resources/images/departments.png"/></span>Departments</a></li>
			<li class="menu-right"><a href="<?php echo base_url(); ?>index.php/manager/logout" onclick="return Manager.__redirect(\'logout\',this);" id="redirect-logout" data-url="false"><span><img src="<?php echo base_url(); ?>/resources/images/logout.png"/></span>Log-out</a></li>
		</ul>
	</div>
</div>
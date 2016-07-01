<div class="search-content">
	<div id="action-filter">
		<ul id="tab-ul">
			<li><a href="#action-container" title="Here you can change the logs per page and etc.">Logs(Recent Activities) Settings</a></li>
			<li><a href="#search" title="You can search employees here on this tab via employee I.D,position and their name.">Search Filter</a></li>
			<li><a href="#company-time-record-rules" title="Company's time record rules">Time Record Rules</a></li>
			<li><a href="#company-details" title="Details and records of the company.">Company Details</a></li>
		</ul>
		<div class="search-wrapper" id="search">			
			<div class="search">
				<?php echo $this->search_contents_home;?>
			</div>
		</div>		
		<div id="company-time-record-rules">
			<div class="company-time-record-rules-wrapper">
				<label>Company Time Record Rules</label>
				<?php echo $this->company_time_record_rules; ?>
			</div>	
		</div>	
		<div id="action-container">
			<div class="log-settings-actions-wrapper">
				<?php echo $this->logs_settings_actions;?>
			</div>
		</div>
		<div id="company-details">
			<div class="company-details-wrapper">
				<label>Company Details</label>
				<?php echo $this->company_detail_contents; ?>
			</div>	
		</div>	
	</div>
</div>
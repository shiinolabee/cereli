<div class="search-content">
	<div id="action-filter">
		<ul id="tab-ul">
			<li><a href="#action-container" title="You can export,import records and also search via date(single,date range).">Actions</a></li>
			<li><a href="#search" title="You can search employees here on this tab via employee I.D,position and their name.">Search Filter</a></li>
			<li><a href="#company-time-record-rules" title="Company's time record rules">Time Record Rules</a></li>
		</ul>
		<div class="search-wrapper" id="search">			
			<div class="search">
				<?php echo $this->search_contents;?>
			</div>
		</div>
		<div id="company-time-record-rules">
			<div class="company-time-record-rules-wrapper">
				<label>Company Time Record Rules</label>
				<?php echo $this->company_time_record_rules; ?>
			</div>	
		</div>	
		<div id="action-container">
			<?php echo $this->employee_actions;?>
		</div>
	</div>
</div>
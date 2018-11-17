
var Manager = window.Manager || {};

Manager = {
	params : null,
	content_class:null,
	base_url:'',
	current_url:null,
	search_class:null,
	recent_base:null,
	current_load_content:null,
	show_hide_class:null,
	search_params:null, /** for search filter [url] */	
	search_option:null,
	message_content:null,
	dialog_employee_details_append:false, /** for dialog createinstance */
	dialog_time_record_append:false,/** for dialog create instance */
	dialog_time_record_transfer:false,/** for dialog create instance */
	dialog_employee_thumbnail_append:false,/** for dialog create instance */
	dialog_create_employee_time_record_append:false,/** for dialog create instance */
	page_limit:0,
	page_offset:5,
	date_search_from:'',
	date_search_to:'',
	view_listing_type:'tabs',	
	result_employee_ids:null,
	exported_employee_id: [],
	/***
	** initialize content loading		
	***/
	init : function(){
		this.base_url = '/cereli-new/';
		this.message_content = $('.message_content');
		this.__load();	
	},
	/***
	** load content via id from content wrapper child div
	***/
	__load : function(){
		this.current_load_content = $('.load-content');
		this.search_class = $('.search-wrapper');
		if(this.current_url !== null)
			this.recent_base.attr('data-url','true');
		this.__load_ajax('content',this.current_load_content.attr('data-url'),this.current_load_content.attr('data-content'),this.current_load_content);
	},
	
	__load_ajax : function(type,url,content,content_div){		

			console.log(this.base_url)	
			$.ajax({
				async:false,
				type:'post',				
				dataType:'json',
				url:this.base_url+url+content,
				data:{data:content,view_type:Manager.view_listing_type},
				success:function(data){					
					if(type == 'content'){
						content_div.show('slow',function(){
							$(this).html(data.contents);
						});				
					}else if(type == 'action'){
						Manager.message_content.addClass('note');
						Manager.message_content.show('slow',function(){
							$(this).html(data.contents);
						});
						setTimeout('Manager.message_content.fadeOut("slow")',3000);
					}	
					Manager.__initiate_date_calendars(data.attended_dates);
					Manager.__initiate_state_tabs();	
					Manager.initiate_state_tooltip();
				},
				error:function(){
					setTimeout('Manager.current_load_content.html("error");',400);
				}
			});			
		},
		
	__load_ajax_search : function(url,content,content_div){			
			$.ajax({
				async:false,
				type:'post',				
				dataType:'json',
				url:this.base_url+url,
				data:{data:content,view_type:Manager.view_listing_type},
				success:function(data){				
					content_div.show('slow',function(){
						$(this).html(data.contents);
					});	
					if(Manager.view_listing_type == 'tabs'){
						Manager.__initiate_date_calendars(data.attended_dates);
						Manager.__initiate_state_tabs();
						Manager.initiate_state_tooltip();
					}
				},
				error:function(){
					setTimeout('Manager.current_load_content.html("error");',400);
				}
			});			
		},
	
	__redirect : function(item,elem){
	
		var elem = $(elem);
		this.params = item;
		this.current_url = this.params;
		this.recent_base = $('#redirect-'+this.params+'');		
		
		if(this.recent_base.attr('data-url') == 'false'){
			return true;
		}else{
			return false;
		}
	},
	
	__show_hide_detail : function(elem,id){
		this.show_hide_class = $(elem);			
		
		if(this.show_hide_class.attr('data-status') == 'hide'){
			this.show_hide_class.css('background-image','url('+Manager.base_url+'/resources/images/show-filter-but.png'+')');
			this.show_hide_class.attr('data-status','show');
			this.show_hide_class.attr('title','Show details');
			$('tr#time-record-'+id).hide('slow');
		}else if(this.show_hide_class.attr('data-status') == 'show'){
			this.show_hide_class.css('background-image','url('+Manager.base_url+'/resources/images/show-filter-but-closed.png'+')');
			this.show_hide_class.attr('data-status','hide');
			this.show_hide_class.attr('title','Hide details');
			$('tr#time-record-'+id).show('slow');
		}else
			return false;
	},	
	
	__click_search_filter : function(type){
		if(type == 'open'){
			this.search_class.slideDown('slow');
			$('.search-label-parent').slideUp('fast');
		}else{
			this.search_class.slideUp('fast');
			$('.search-label-parent').slideDown('fast');
		}
	},
	
	__select_all_employee : function(elem){
		var elem = $(elem);
		var selected_employee = $('table.table-content-tab-result');		
		
		if(elem.is(':checked')){		
			$('.selected_employee').attr('checked','checked');						
		}else{					
			$('.selected_employee').attr('checked',false);
		}	
		
	},
	
	__select_action : function(elem){
		var elem = $(elem);
		this.search_option = elem;
		
		if(elem.val() == 'import'){			
			$('.import-container').show('normal');		
			if($('.select-export').css('display') != 'none')
				$('.select-export').hide('normal');
		}else if(elem.val() == 'export'){
			$('.select-export').show('normal');						
			if($('.import-container').css('display') != 'none')
				$('.import-container').hide('normal');			
		}else{
			$('.import-container').hide('normal');	
			$('.select-export').hide('normal');
			this.search_option = [];
			this.search_option_value = [];
		}
	},
	
	__action : function(elem){
		
		var elem = $(elem);
		var type = this.search_option;
		
		if(type !== '' && type !== 'undefined' && type != null && type.length > 0){
				if(type.val() == 'export')
					this.search_option_value = $('#export-type input[name="type"]:checked').attr('id');
				else if(type.val() == 'import')
					this.search_option_value = $('input[name="import-file"]').val().split('.')[0];
				
				if(this.search_option_value !== undefined && this.search_option_value !== ''){
					var params = '/index.php/manager/action/'+type.val()+'/'+this.search_option_value+'/';						
					var date_range = $('#trigger-csv-option-datepicker-from').val()+'*'+$('#trigger-csv-option-datepicker-to').val();
					if(type.val() == 'export'){	
					
						if($('#trigger-csv-option-datepicker-from').val() != '' && $('#trigger-csv-option-datepicker-to').val() != ''){							
							var export_type = $('select#export_type').val();			
							
							if(export_type == 'single'){
								var selected_employees = this.__get_selected_employees();							
								if(typeof selected_employees != 'undefined'){						
									elem.attr('href',this.base_url+params+selected_employees+'/'+date_range);
									elem.submit();			
									this.__mark_employee(selected_employees,date_range,'single');
									this.__reset_action_fields(type.val());
								}else
									alert("Select one employee first.");						
							}else{
								var selected_employees = this.__get_multiple_selected_employees();
								
								if(typeof selected_employees != 'undefined' && selected_employees != ''){
								
									var params = '/index.php/manager/action/export-multiple/csv/';
									var date_range = $('#trigger-csv-option-datepicker-from').val()+'*'+$('#trigger-csv-option-datepicker-to').val();
									elem.attr('href',this.base_url+params+selected_employees+'/'+date_range);									
									elem.submit();									
									this.__mark_employee(selected_employees,date_range,'multiple');
									this.__reset_action_fields(type.val());
								}else alert("Select at least one employee first.");							
							}
						}else	
							alert("Choose first a date to be exported.");				
					}else if(type.val() == 'import'){
						this.__reset_action_fields(type.val());
						$('#list ul').empty();
					}						
				}else
					alert("Select a type of action.");				
		}else
			alert("Select first an action.");

		return false;
	},
	
	__get_multiple_selected_employees : function(){
		
		if($('input[name="select_all_employees"]').is(':checked'))
			return '*';
			
		var selected_employees = [];
		
		$('input[name="check_all_employee"]:checked').each(function(i,o){
			selected_employees.push(o.defaultValue);
		});
		
		return selected_employees;
	},
	
	__reset_action_fields : function(type){
		
		setTimeout("$('.action-container').parent().find('a').attr('href','javascript:void(0);');",2000);
		if(type == 'import'){
			$('input[name="import-file"]').val('')
		}
		
		$('input[name="check_all_employee"]:checked').attr('checked',false);
		
		return false;
	},
	
	__mark_employee : function(id,date_range,type){
		
		if(typeof date_range != 'undefined'){
			var exploded_date_range = date_range.split('*');
			var date_from = new Date(exploded_date_range[0]).toString('MMM d,yyyy');
			var date_to = new Date(exploded_date_range[1]).toString('MMM d,yyyy');		
			var now = new Date().toString('h:mm tt');
		}
		
		if(typeof id != 'undefined'){							
			var total_employees = $('input#data-total-employees').val();		
			
			if(type == 'single'){
				$('#li-'+id).addClass('li-menu');
				/***
				* @ start
				* to append the action to the status panel 
				**/			
					var employee_name = $('input[name="check_all_employee"]:checked').attr('data-fullname');			
							
					if($.inArray(id,Manager.exported_employee_id) == -1){
						Manager.exported_employee_id.push(id);
						$('.queue-export-contents-wrapper').append('<div class="queue-export-content-details" id="queue-export-employee-'+id+'">'+employee_name+''
							+'<div class="queue-export-employee-time-records-queue">'+date_from+' to '+date_to+' - '+now+'</div></div>');		
					}else{
						$('#queue-export-employee-'+id+'').append('<div class="queue-export-employee-time-records-queue">'+date_from+' to '+date_to+' - '+now+'</div>');				
					}							
					var total_exported_employees = $('.queue-export-content-details').length;
				
					$('.queue-export-content-header').text('Exporting employee records('+total_exported_employees+' of '+total_employees+')');		
					
				/***
				* @ end
				* to append the action to the status panel 
				**/				
			}else if(id == '*' && type == 'multiple'){		
			
				$('.queue-export-content-header').text('Exporting multiple employee records('+total_employees+' of '+total_employees+')');
				$('.queue-export-contents-wrapper').append('<div class="queue-export-content-details" id="queue-export-employee-'+id+'">Multiple exporting time records('+total_employees+' of '+total_employees+')'
							+'<div class="queue-export-employee-time-records-queue">'+date_from+' to '+date_to+' - '+now+'</div></div>');
			}
		
			$('.queue-export-show-hide-btn').text('Hide');
			$('.queue-export-show-hide-btn').attr('data-status','hide');
			
			$('.queue-export-content-details').show('normal');
			
			if($('.queue-export-contents-wrapper').css('display') == 'none'){
				$('.queue-export-contents-wrapper').show('normal');
			}
		}
		return false;
	},
	
	__get_selected_employees : function(){	
		return $('input[name="check_all_employee"]:checked').attr('id');
	},
	
	__search : function(){
	
		var elem = $('.field-container');
		
		var field_search = {};
		field_search.emp_id = (elem.parent().find('input#field-emp_id').val() != '') ? elem.parent().find('input#field-emp_id').val() : null;
		field_search.fullname = (elem.parent().find('input#field-fullname').val() != '') ? elem.parent().find('input#field-fullname').val() : null;
		field_search.position = (elem.parent().find('input#field-position').val() != '') ? elem.parent().find('input#field-position').val() : null;	

		var data_query = 'SELECT * FROM employees ';
		var delimiter = 'AND ';
		var condition = 'WHERE ';
		this.search_params = '/index.php/manager/action/search/';
		
		if(field_search.emp_id != null)		
			data_query = data_query + 'WHERE emp_id = "'+field_search.emp_id+'"';			
		else if(field_search.emp_id == null && field_search.fullname != null){
			condition = 'WHERE ';
			delimiter = ' ';
		}
		else if(field_search.emp_id == null){
			condition = ' ';
			delimiter = ' ';
		}else
			delimiter = ' ';	
			
		if(field_search.fullname != null){
			if(field_search.emp_id != null)
				condition = ' ';
			data_query = data_query + condition +delimiter+'fullname LIKE "%'+field_search.fullname+'%"';	
			condition = ' ';
			delimiter = 'AND ';
		}else if(field_search.fullname == null && field_search.position != null){
			if(field_search.emp_id == null){
				condition = 'WHERE ';
			}else{			
				delimiter = 'AND ';
				condition = ' ';	
			}
		}else if(field_search.position != null){
			condition = 'WHERE ';	
			delimiter = ' ';
		}
		
		
		if(field_search.position != null)
			data_query = data_query + condition + delimiter+'position LIKE "%'+field_search.position+'%"';					
		
		this.__load_ajax_search(this.search_params,data_query,this.current_load_content);
	},
	
	__action_hover : function(id,type){
		var elem = $('#action-'+id+' span');

		if(type == 'on'){
			elem.fadeIn('fast');								
		}else if(type == 'off'){
			elem.fadeOut('slow');	
		}
	},	
	__action_employee_time_record : function(record_id,type,emp_id){
		
		if(record_id.length > 0){
			var dialog_content = '',action_type = '',values = '';
							
			if(type == 'edit'){												
				if(this.dialog_time_record_append == false){
					var dialog_instance = $('<div id="dialog_time_record_details"></div>');
					dialog_instance.appendTo('body');
					this.dialog_time_record_append = true;	
					
					dialog_instance.dialog({
						title: 'Edit Employee Time Record Details',
						width:565,
						height:'auto',
						draggable:true,
						position:'center',
						resizable:true,
						autoOpen:true,
						modal:true,
						buttons:{
							"Save details" : function(){
								
								var time_record = $('.time-record-details-container');
								var time_record_details = {};
								time_record_details.record_id = time_record.parent().find('input[name="record_id"]').val();
								time_record_details.emp_id = time_record.parent().find('input[name="emp_id"]').val();
								time_record_details.date_attended = time_record.parent().find('input#date_attended').val();												
								time_record_details.remarks = time_record.parent().find('textarea[name="remarks"]').val();	
																
								Manager.__dialog_action('/index.php/manager/action/','update_time_record',time_record_details,time_record_details.record_id);						
							},
							"Close" : function(){
								$(this).dialog('close');								
							}
						},
						beforeClose: function(){							
							$('#date_attended').datepicker( "option", "disabled", true );
						}
					});
				}
				else
					var dialog_instance = $('#dialog_time_record_details');					
				
				action_type = 'fetch';
				values = record_id;
				dialog_content = $('#dialog_time_record_details');
				
			}
			else if(type == 'transfer'){
				if(this.dialog_time_record_transfer == false){
					var dialog_instance = $('<div id="dialog_transfer_time_record"></div>');
					dialog_instance.appendTo('body');
					this.dialog_time_record_transfer = true;
					
					dialog_instance.dialog({
						title: 'Transfer Time Record To Another Employee',
						width:500,
						height:330,
						draggable:true,
						position:'center',
						resizable:false,
						autoOpen:true,
						modal:true,
						buttons:{
							"Transfer" : function(){

								if(confirm("Are you sure to transfer this details to another employee?")){
									var transfer_time_record = $('.transfer-time-record-details-container');
									var transfer_time_record_details = {};
									transfer_time_record_details.record_id = transfer_time_record.parent().find('input[name="record_id"]').val();
									transfer_time_record_details.emp_id = transfer_time_record.parent().find('select[name="emp_id"]').val();
																					
									Manager.__dialog_action('/index.php/manager/action/','transfer_time_record',transfer_time_record_details,transfer_time_record_details.record_id);														
								}
							},
							"Close" : function(){
								$(this).dialog('close');						
							}
						}
					
					});
				}
				else
					var dialog_instance = $('#dialog_transfer_time_record');					
				
				action_type = 'fetch_transfer';
				values = record_id+'/'+emp_id;
				dialog_content = $('#dialog_transfer_time_record');
			}
			else{
			
				if(confirm("Are you sure to delete this time record?")){
					action_type = 'delete';
					values = record_id;
					dialog_content = $('.message_content');				
				}else return false;
			}
			
			var params = '/index.php/manager/time_record_details/'+action_type+'/'+values;			
			
			$.ajax({
				async:false,
				type:'post',				
				dataType:'json',
				beforeSend:function(){
					if(dialog_instance != '' || dialog_instance != null){
						if(type != 'delete'){
							dialog_instance.dialog('open');
							dialog_content.html('Fetching data....');					
						}						
					}
				},
				url:this.base_url+params,
				success:function(data){					
					if(data.contents.length > 0){
						dialog_content.html(data.contents);		
						if(type == 'edit'){
							$('#date_attended').datepicker({
								changeMonth: true,
								changeYear: true,
								showButtonPanel: true,
								dateFormat:'M dd yy',
								maxDate: Date.today().toString('YY-mm-DD')								
							});						
						}else if(type == 'delete'){
							$('#time-record-'+record_id).fadeOut('slow');
						}
					}
				},
				error:function(){
					alert('error');
				}				
			});		
			
		}
		
	},
	
	__edit_employee : function(id){
		
		if(id.length > 0){
		
			if(this.dialog_employee_details_append == false){
				var dialog_instance = $('<div id="dialog_employee_details"></div>');
				dialog_instance.appendTo('body');
				this.dialog_employee_details_append = true;

				dialog_instance.dialog({
					title: 'Edit Employee Details',
					width:565,
					height:'auto',
					draggable:true,
					position:'center',
					resizable:false,
					autoOpen:true,
					modal:true,
					buttons:{
						"Save details" : function(){
							
							var employee = $('.employee-details-container');
							var employee_details = {};
							employee_details.id = employee.parent().find('input[name="id"]').val();
							employee_details.emp_id = employee.parent().find('input[name="emp_id"]').val();
							employee_details.fullname = employee.parent().find('input[name="fullname"]').val();
							employee_details.position = employee.parent().find('input[name="position"]').val();
							employee_details.leave_limit = employee.parent().find('input[name="leave_limit"]').val();
							employee_details.unpaid_leave_limit = employee.parent().find('input[name="unpaid_leave_limit"]').val();
							employee_details.absences = employee.parent().find('input[name="absences"]').val();
							employee_details.late = employee.parent().find('textarea[name="late"]').val();
							employee_details.overtime = employee.parent().find('textarea[name="overtime"]').val();
							employee_details.record_status = employee.parent().find('select[name="record_status"]').val();
							
							Manager.__dialog_action('/index.php/manager/action/','update',employee_details,employee_details.id);						
						},
						"Close" : function(){
							$(this).dialog('close');					
						}
					}
				});

			}else
				var dialog_instance = $('#dialog_employee_details');
			
			
			var params = '/index.php/manager/employee_details/'+id;			
			
			$.ajax({
				async:false,
				type:'post',				
				dataType:'json',
				beforeSend:function(i,o){
					dialog_instance.dialog('open');
					$('#dialog_employee_details').html('Fetching data....');
				},
				url:this.base_url+params,
				success:function(data){
					if(data.contents.length > 0){
						$('#dialog_employee_details').html(data.contents);	
					}
				}
			});		
		}
	},
	
	__dialog_action : function(url,type,values,id){		
		
		var dialog_message_content = $('.dialog_message_content');
		
		if(values){
			$.ajax({
				async:false,
				type:'post',
				dataType:'json',
				beforeSend:function(){
					dialog_message_content.fadeIn('fast');
					dialog_message_content.html('Saving data...');
				},
				url: this.base_url+url+type+'/'+id,
				data:{data:values},
				success:function(data){
					if(data.contents.length > 0){						
						dialog_message_content.html(data.contents);							
					}
				},
				error:function(){}
			});
		}		
	},
	
	__calendar_search : function(date){
		var elem = $(date),date = '';
	
		if(elem.val() != ''){
			date = elem.val().split('/').join('-');
			var params = '/index.php/manager/employee_time_record/';
			var content = '*/'+this.page_limit+'/'+this.page_offset+'/';
			
			this.__load_ajax('content',params+content,date,this.current_load_content);
			return false;
		}		
	},
	
	__calendar_search_range : function(elem,type){
		var elem = $(elem),date = '';
		var from = $('#open-calendar-from').val();
		var to = $('#open-calendar-to').val();
		
		if(type == 'from'){
			if(from != '' && from != 'undefined'){
				this.date_search_from = elem.val().split('/').join('-');				
			}else
				alert('Enter first "from" date.');
		}else if(type == 'to'){
			if(this.date_search_from != '' && this.date_search_from != null){
				if(from != '' && from != 'undefined'){
					this.date_search_to = elem.val().split('/').join('-');			
				}
				else
					alert('Enter first "to" date.');				
			}else
				alert('Enter first "from" date.'); return false;
		}
	},
	
	__calendar_submit_range : function(){
		var date = '',error='Please review : \n';
		
		if(this.date_search_from == '' || this.date_search_from == null)
			error += 'Enter first "from" date.\n';
		if(this.date_search_to == '' || this.date_search_to == null)
			error += 'Enter first "to" date.';
		
		if(this.date_search_from != '' && this.date_search_to != ''){
			var params = '/index.php/manager/employee_time_record/';
			var content = '*/'+this.page_limit+'/'+this.page_offset+'/';
			date = this.date_search_from+'/'+this.date_search_to;
					
			this.__load_ajax('content',params+content,date,this.current_load_content);
			return false;
		}
		else
			alert(error);
		
	},
	
	__is_valid_date : function(value) {
		var dateWrapper = new Date(value);
		return isNaN(dateWrapper.getDate());
	},
	
	__sort_action : function(type,id,month_attended){
		
		if(type != null && id != null){			
			var params = '/index.php/manager/action/fetch_time_record/'
			var content = type+'/'+id+'/'+month_attended;
			var content_div = $('table#time-record-table-'+id+'');
			this.__load_ajax('content',params,content,content_div);
		}
	},
	
	__sort_employee_action : function(type){
		
		if(type != null){
			var params = '/index.php/manager/action/fetch_employees/'
			var content = '*/*/'+type;
			var content_div = $('.load-content');
			this.__load_ajax('content',params,content,content_div);
		}
	},
		
	__handler_file_select : function(evt){
		var files = evt.target.files; // FileList object
		
		// files is a FileList of File objects. List some properties.
		var output = [];
		for (var i = 0, f; f = files[i]; i++) {
			filesize = f.size;
			output.push('<li><strong>', escape(f.name), '</strong> (', f.type || 'n/a', ') - ',
					  f.size, ' bytes <br/> Last modified: ',
					  f.lastModifiedDate ? f.lastModifiedDate.toLocaleDateString() : 'n/a',
					  '</li>');
		}				
		document.getElementById('list').innerHTML = '<ul>' + output.join('') + '</ul>';
	},
	
	__select_calendar_type_action : function(elem){
		var elem = $(elem);
		
		if(elem.val() == 'default'){
			$('#calendar-option-2').hide('normal');
			$('#calendar-option-1').show('normal');
		}else if(elem.val() == 'from-to'){
			$('#calendar-option-1').hide('normal');
			$('#calendar-option-2').show('normal');
		}else{
			$('#calendar-option-1').hide('normal');
			$('#calendar-option-2').hide('normal');
		}
		
		return false;
	},
	
	__menu_scroll_bind : function(evt){	
		if ($(window).scrollTop() > 50)		
             $('.menu-container').addClass('fix-menu');		
		else
		 $('.menu-container').removeClass('fix-menu');		
	},
	
	__record_per_page_action : function(elem,type){
		var elem = $(elem);
		
		if(elem.val() != ''){			
			Manager.page_offset = elem.val();
			
			if(type == 'home')
				window.location.href = this.base_url+'/index.php/manager/index/'+Manager.page_offset+'/0';
			else if(type == 'employees')
				window.location.href = this.base_url+'/index.php/manager/employees/'+Manager.page_offset+'/0';			
		}
	},
	
	__change_view_listing : function(elem){
		var elem = $(elem);
		
		Manager.view_listing_type = elem.val();
		if(elem.val() != ''){
			var params = '/index.php/manager/change_view_listing/';
			var content = elem.val()+this.current_load_content.attr('data-content');		
			Manager.view_listing_type = elem.val();
			
			$.ajax({
				async:false,
				type:'post',				
				dataType:'json',
				url:this.base_url+params+content,
				data:{data:content},
				success:function(data){								
					Manager.current_load_content.show('slow',function(){
						$(this).html(data.contents);					
						Manager.__initiate_state_tabs();
						// Manager.__initiate_date_calendars(data.attended_dates);
						// Manager.result_employee_ids = data.employee_ids;										
					});			
				},
				error:function(){
					setTimeout('Manager.current_load_content.html("error");',400);
				}
			});	
			
		}
	},
	
	__initiate_state_tabs: function(){	
		$('.tab-employee-result').tabs({
			cache: true,
			load: function( event, ui ){
				$('.employee-time-record-tabs-wrapper').dragscrollable({dragSelector: '.employee-time-record-tabs-container', acceptPropagatedEvent: false});	
				$('.time-record-tab-calculate-btn input, .tab-employee-details-edit-details, .tab-employee-details-change-thumbnail,.tab-employee-details-select-employee').button();			
				$('.employee-time-record-tabs-container').dragscrollable();		
				$('tr.time-record-tr').selectable();
				Manager.initiate_state_tooltip();						
			},
			beforeLoad: function( event, ui ) {				
				if (ui.tab.data("loaded")) {
                    event.preventDefault();					
                    return;
                }
                ui.ajaxSettings.cache = false,		
				/** if use json type
					ui.ajaxSettings.accepts = { json: "application/json, text/javascript" };
					ui.ajaxSettings.contentType = "application/json; charset=utf-8";
					ui.ajaxSettings.type = "GET";
					ui.ajaxSettings.dataTypes[0] = "json";				
				**/				
                ui.panel.html('<img src="'+Manager.base_url+'/resources/images/loader.gif" width="24" height="24" style="vertical-align:middle;"> Loading...');               
				ui.jqXHR.done(function(data) {										
                    ui.tab.data("loaded",true);				
					Manager.__employee_get_attended_dates(ui.tab[0].attributes[0].value);
					$('.load-content').animate({ scrollTop: $('.load-content')[0].scrollHeight}, 1000);
					$('.load-content').scrollTop($('.load-content')[0].scrollHeight);
                }),
                ui.jqXHR.fail(function () {
                    ui.panel.html("Couldn't load the data.");
                });	
			}
		}).scrollabletab({	
			'animationSpeed':50, //Default 100	
			'resizeHandles':'e,s,se', //Default 'e,s,se'
			'easing':'easeInOutExpo' //Default 'swing'		
		});				
	},
	
	__employee_get_attended_dates : function(id){
		
		if(id != ''){
			var params = '/index.php/manager/employee_attended_dates/'+id;
			$.ajax({
				dataType:'json',
				type:'get',
				url:this.base_url+params,
				beforeSend:function(){
					$('#employee-calendar-'+id).html('Drawing calendar...');
				},
				success:function(data){
					if(typeof data.attended_dates == 'object'){
						// console.log(typeof data.attended_dates);
						Manager.__initiate_date_calendars(data.attended_dates);						
					}else{
						$('#employee-calendar-'+id).addClass('error-msg');
						$('#employee-calendar-'+id).html(data.error);
						$('#employee-calendar-'+id).css({'margin':'4px','width':'56%','height':'auto','text-align':'center','letter-spacing':'1px','font-weight':'bold','font-size':'11px'});
					}					
				}				
			});
		}
		return false;
	}, 
	
	__initiate_date_calendars : function(dates){		
		
		if(typeof dates == 'object'){
			Object.keys(dates).forEach(function(key) {		
				Object.keys(dates[key]).forEach(function(emp_id) {			
					var dates_attended = [];	
					Object.keys(dates[key][emp_id]).forEach(function(keys) {						
						var split_dates = dates[key][emp_id][keys].split('-');
						dates_attended.push(new Date(split_dates[0],(split_dates[1]-1),split_dates[2]).toDateString());						
					});	
					
					$('#employee-calendar-'+emp_id+'').datepicker({			
						showButtonPanel: true,						
						dayNames:[ "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ],						
						dateFormat:'yy-mm-dd',	
						maxDate:Date.today().toString('YY-mm-DD'),
						onChangeMonthYear:function(changeYear,changeMonth,objectInstance){								
							var id = $(this).attr('id').split('-')[2];			
							Manager.__get_employee_time_records(changeYear,changeMonth,id);					
						},
						beforeShowDay: function(date) {						
							if($.inArray(new Date(date).toDateString(), dates_attended) != -1) {				
								return [false,'','The employee is present on this day.']; 	
							}else	return [true, 'date-not-attended-state', 'No time record on this day.(Click to make a time record)'];						
						},
						onSelect:function(selectedDate,objectInstance,o){							
							var id = $(this).attr('id').split('-')[2];
							var fullname = $(this).attr('data-emp-name');							
							Manager.__create_employee_time_record(id,fullname,selectedDate);							
						},
						prevText: "Previous month",
						nextText: "Next month"
					});	
				});			
			});			
		}		
	},
	
	__create_employee_time_record : function(id,fullname,selectedDate){
		
		if(typeof id != 'undefined' && selectedDate != 'undefined'){
			if(this.dialog_create_employee_time_record_append == false){
				var dialog_instance = $('<div id="dialog_create_time_record"></div>');
				dialog_instance.appendTo('body');
				
				this.dialog_create_employee_time_record_append = true;
				
				dialog_instance.dialog({
					title: 'Create Employee Time Record',
					width:500,
					height:360,
					draggable:true,
					position:'center',
					resizable:false,
					autoOpen:true,
					modal:true,
					buttons:{
						"Save time record" : function(){							
							var employee_create_time_record = $('.employee-create-time-record-content');
							var emp_id = employee_create_time_record.parent().find('input[name="emp_id"]').val();
							var date_attended = new Date(employee_create_time_record.parent().find('input[name="date_attended"]').val()).toString('yyyy-MM-dd');
							var type = employee_create_time_record.parent().find('input[name="type"]:checked').val();
							var remarks = employee_create_time_record.parent().find('textarea[name="remarks"]').val() != 'undefined' || employee_create_time_record.parent().find('textarea[name="remarks"]').val() != '' ? employee_create_time_record.parent().find('textarea[name="remarks"]').val() : 'No remarks';
							
							if(type == 'undefined')
								type = 'default';
								
							var params = '/index.php/manager/create_employee_time_record/save';
							var content = '/'+emp_id+'/'+'*/'+date_attended+'/'+remarks+'/'+type;
							var message_content = $('.dialog_message_content');							
							
							$.ajax({
								async:false,
								type:'post',				
								dataType:'json',
								url:Manager.base_url+params+content,
								beforeSend:function(){											
									message_content.fadeIn();			
									message_content.html('Saving data ...');			
								},
								success:function(data){								
									if(data.contents.length > 0){
										message_content.html(data.contents);																				
									}	
								},
								error:function(){
									setTimeout('Manager.current_load_content.html("error");',400);
								}
							});															
						},
						"Close" : function(){
							$(this).dialog('close');												
						}
					}
				});
				
			}else	
				var dialog_instance = $('#dialog_create_time_record');
				
			var params = '/index.php/manager/create_employee_time_record/set';
			var content = '/'+id+'/'+fullname+'/'+selectedDate;
			
			$.ajax({
				async:false,
				type:'post',				
				dataType:'json',
				url:this.base_url+params+content,
				beforeSend:function(){
					dialog_instance.dialog('open');
					dialog_instance.html('Fetching data ...');					
				},
				success:function(data){								
					if(data.contents.length > 0){
						dialog_instance.html(data.contents);	
					}	
				},
				error:function(){
					setTimeout('Manager.current_load_content.html("error");',400);
				}
			});	
		
		}	
		return false;
	},
	
	__get_employee_time_records : function(changeYear,changeMonth,id){
		
		if(changeYear != '' && changeMonth != '' && id != ''){
			var params = '/index.php/manager/action/fetch_employee_time_record_on_change_date/';
			var date = new Date(changeYear,changeMonth-1).toString('yyyy-MM');
			var content = date+'/'+id;
			var content_div = $('table#time-record-table-'+id+'');			
			
			$.ajax({
				async:false,
				type:'post',				
				dataType:'json',
				url:this.base_url+params+content,
				data:{data:content},
				success:function(data){								
					content_div.show('slow',function(){
						$(this).html(data.contents);
						$('#time-record-tab-label-'+id).html(data.time_record_label);
						$('#employee-details-calculation-'+id).html(data.month_calculations);
						Manager.__initiate_date_calendars(data.attended_dates);
						// Manager.__initiate_state_tabs();	
						Manager.initiate_state_tooltip();
					});			
				},
				error:function(){
					setTimeout('Manager.current_load_content.html("error");',400);
				}
			});	
		}
	},
	
	__change_employee_thumbnail : function(id){
		
		if(id != ''){
		
			if(Manager.dialog_employee_thumbnail_append == false){
				var dialog_instance = $('<div id="dialog_employee_change_thumbnail"></div>');
				dialog_instance.appendTo('body');
				Manager.dialog_employee_thumbnail_append = true;					
				
				dialog_instance.dialog({
					title: 'Change Employee Thumbnail',
					width:500,
					height:330,
					draggable:true,
					position:'center',
					resizable:false,
					autoOpen:true,
					modal:true,
					buttons:{						
						"Close" : function(){
							$(this).dialog('close');							
						}
					}				
				});
				
			}else	
				var dialog_instance = $('#dialog_employee_change_thumbnail');
				
			var params = '/index.php/manager/employee_change_thumbnail/set/';			
			
			$.ajax({
				async:false,
				type:'post',				
				dataType:'json',
				url:this.base_url+params+id,			
				beforeSend:function(){
					dialog_instance.dialog('open');
					dialog_instance.html('Fetching data ...');					
				},
				success:function(data){								
					if(data.contents.length > 0){
						dialog_instance.html(data.contents);	
					}	
				},
				error:function(){
					setTimeout('Manager.current_load_content.html("error");',400);
				}
			});	
				
		}
	},
	
	___action_save_thumbnail : function(form,id){
	
		var employee_change_thumbnail_result_content = $('.employee-change-thumbnail-result-content');
		
		// $('#form-change-thumbnail').ajaxForm({
			// url: Manager.base_url+'/index.php/manager/employee_change_thumbnail/change/'+id,
			// type: 'post',
			// dataType:'json',																
			// success: function(data) {
				// if(data.length > 0){
					// employee_change_thumbnail_result_content.show('slow',function(){
						// $(this).html(data.contents);
					// });
				// }
			// },
			// error: function (xhr, ajaxOptions, thrownError) {
				// alert(xhr.status);
			// }
		// });
		
		return false;
	},
	
	__calculate_total_hours_rendered_and_others : function(emp_id){
		var content_div = $('#tab-result-'+emp_id+'');
		if(emp_id != ''){
			var params = '/index.php/manager/action/fetch_employee_total_hours_rendered_and_others/'
			var content = '*/'+emp_id;
			
			$.ajax({
				async:false,
				type:'post',				
				dataType:'json',
				beforeSend:function(){
					content_div.html('Calculating...');
					content_div.addClass('time-record-tab-result-content');
				},
				url:this.base_url+params+content,
				data:{data:content},
				success:function(data){								
					content_div.show('slow',function(){						
						$(this).html(data.contents);								
					});		
				},
				error:function(){
					setTimeout('Manager.current_load_content.html("error");',400);
				}
			});	
		}
	},
	
	__progress : function(elem){
	
		var val = $(elem).progressbar( "value" ) || 0;
		
		$(elem).progressbar( "value", val + 5 );		
		if ( val < 99 ) {
			setTimeout("Manager.__progress(Manager.progressbar)",100);
		}else
			return true;
	},
	
	__action_time_record_rules : function(action,id){
		var form = $('form[name="time-record-rules"]');
		if(action == 'edit'){
			form.parent().find('input[name="'+id+'"]').removeAttr('disabled');			
			form.parent().find('span#cancel-btn-'+id).fadeIn('fast');
		}
		else if(action == 'set'){
			$('#save-btn-'+id).fadeIn('fast');
		}else if(action == 'cancel'){			
			form.parent().find('input[name="'+id+'"]').attr('disabled','disabled');
			form.parent().find('span#cancel-btn-'+id).fadeOut('fast');	
			$('span#save-btn-'+id).fadeOut('fast');
		}else if(action == 'save'){			
			var params = '/index.php/manager/save_time_record_rules/';
			var data = {};
			data.id = form.parent().find('input[type="hidden"][name="id"]').val();
			data.required_hours_rendered = form.parent().find('input[type="text"][name="required_hours_rendered"]').val();
			data.start_shift_time = form.parent().find('input[type="text"][name="start_shift_time"]').val();
			data.end_shift_time = form.parent().find('input[type="text"][name="end_shift_time"]').val();
			
			if(data){
				$.ajax({
					async:false,
					dataType:'json',
					data:{data:data},
					type:'post',
					url:Manager.base_url+params,
					beforeSend:function(){
						$('.time_record_rules_message_content').fadeIn('normal');
						$('.time_record_rules_message_content').addClass('time_record_rules_result_message');
						$('.time_record_rules_message_content').html('Saving...');
					},
					success:function(data){
						if(data.status == 'true'){
							$('.time_record_rules_message_content').addClass('time_record_rules_result_message');
							$('.time_record_rules_message_content').html(data.contents);
						}else{							
							$('.time_record_rules_message_content').removeClass('time_record_rules_result_message');
							$('.time_record_rules_message_content').addClass('error-msg');
							$('.time_record_rules_message_content').html(data.contents);
						}
						form.parent().find('span.time-record-rule-save-btn').fadeOut('fast');
						form.parent().find('span.time-record-rule-cancel-btn').fadeOut('fast');
						setTimeout('$(".time_record_rules_message_content").fadeOut("normal");',4000);
						form.parent().find('input[type="text"]').attr('disabled','disabled');					
						Manager.__load();
					},
					error: function(e){
						alert(e);
					}
				});
			}
		}
		return false;
	},
	
	__action_company_details : function(action,id){
		var form = $('form[name="company_details"]');
		if(action == 'edit'){
			form.parent().find('input[name="'+id+'"]').removeAttr('disabled');			
			form.parent().find('#company-cancel-btn-'+id).fadeIn('fast');
		}
		else if(action == 'set'){
			$('#company-save-btn-'+id).fadeIn('fast');
		}else if(action == 'cancel'){			
			form.parent().find('input[name="'+id+'"]').attr('disabled','disabled');
			form.parent().find('#company-cancel-btn-'+id).fadeOut('fast');		
			form.parent().find('#company-save-btn-'+id).fadeOut('fast');		
		}else if(action == 'save'){			
			var params = '/index.php/manager/save_company_details/';
			var data = {};
			data.id = form.parent().find('input[type="hidden"][name="id"]').val();
			data.company_name = form.parent().find('input[type="text"][name="company_name"]').val();
			data.company_address = form.parent().find('input[type="text"][name="company_address"]').val();
			data.contact_no = form.parent().find('input[type="text"][name="contact_no"]').val();
			data.system_version = form.parent().find('input[type="text"][name="system_version"]').val();
			data.company_status = form.parent().find('input[type="text"][name="company_status"]').val();
			
			if(data){
				$.ajax({
					async:false,
					dataType:'json',
					data:{data:data},
					type:'post',
					url:Manager.base_url+params,
					beforeSend:function(){
						$('.company_details_message_content').fadeIn('normal');
						$('.company_details_message_content').addClass('company_details_result_message');
						$('.company_details_message_content').html('Saving...');
					},
					success:function(data){
						if(data.status == 'true'){
							$('.company_details_message_content').addClass('company_details_result_message');
							$('.company_details_message_content').html(data.contents);
						}else{							
							$('.company_details_message_content').removeClass('company_details_result_message');
							$('.company_details_message_content').addClass('error-msg');
							$('.company_details_message_content').html(data.contents);
						}
						form.parent().find('span.company-details-save-btn').fadeOut('fast');
						form.parent().find('span.company-details-cancel-btn').fadeOut('fast');
						setTimeout('$(".company_details_message_content").fadeOut("normal");',4000);
						form.parent().find('input[type="text"]').attr('disabled','disabled');					
						Manager.__load();
					},
					error: function(e){
						alert(e);
					}
				});
			}
		}
		return false;
		
	},
	
	initiate_state_tooltip : function(){
		$('ul#ul-menu li, ul#tab-ul li, [title],[input]').tooltip({
			track: true,
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
	},
	
	initiate_selectable_employees : function(){
		$('.tab-employee-details-select-employee').selectable({
			stop: function() {
				var result = $( "#select-result" ).empty();
				$( ".ui-selected", this ).each(function() {
					var index = $( "#selectable li" ).index( this );
					result.append( " #" + ( index + 1 ) );
				});
			}
		});
	},
	
	__action_show_status_queue : function(elem){
		
		var elem = $(elem);
		
		if(elem.attr('data-status') == 'show'){
			elem.text('Hide');
			elem.parent().parent().find('.queue-export-contents-wrapper').show('normal');
			elem.attr('data-status','hide');
		}else if(elem.attr('data-status') == 'hide'){
			elem.text('Show');
			elem.parent().parent().find('.queue-export-contents-wrapper').hide('normal');
			elem.attr('data-status','show');			
		}
		
		return false;
	},
	
	__change_selecting_type : function(elem){
	
		var elem = $(elem);
		
		if(elem.val() == 'single'){
			$('td.td-selecting-data').parent().find('input').attr('type','radio');
		}else if(elem.val() == 'multiple'){
			$('td.td-selecting-data').parent().find('input').attr('type','checkbox');
		}
	},
	
	__trigger_select_employee : function(elem,id){
		
		var elem = $(elem);

		if(elem.find('td.td-selecting-data input').attr('type') == 'checkbox'){
			if(elem.find('td.td-selecting-data input').is(':checked'))
				elem.find('td.td-selecting-data input').removeAttr('checked');
			else
				elem.find('td.td-selecting-data input').attr('checked',true);		
		}
		else
			elem.find('td.td-selecting-data input').attr('checked',true);
	},
	
	__delete_all_employee_time_records : function(){
		
		if(confirm("Deleting all employee time records will be permanent and cannot be retrieved. Are you sure to proceed this operation?")){
			var params = '/index.php/manager/action_employee_time_records/delete_all';
			$.ajax({
				type:"get",
				url: Manager.base_url+params,
				dataType:"json",
				beforeSend : function(){
					$('.message_content').html('Deleting all time records...');
					$('.message_content').addClass('delete-all-employee-time-records-result-message');					
				},
				success:function(data){
					if(data.status.length > 0){
						$('.message_content').html(data.status);
					}
				},
				error:function(){}
			})
		}
	},
	
	__change_type_exporting : function(elem){
		
		var elem = $(elem);
		
		if(elem.val() == 'multiple'){
			$('span.span_select_all_employees').show('normal');
			$('.select-export-employees span').html('<b>Note :</b> Select atleast one employee to export.');
		}else{
			$('span.span_select_all_employees').hide('normal');
			$('.select-export-employees span').html('<b>Note :</b> Only by 1 employee per export.');
		}
			
		return false;
	}
	
};

window.onload = function(){ 
	Manager.init();	
	Manager.initiate_state_tooltip();
}

window.onscroll = function(evt){
	Manager.__menu_scroll_bind(evt);
}

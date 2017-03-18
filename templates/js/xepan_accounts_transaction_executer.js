jQuery.widget("ui.transaction_executer", {
	// selectorHeader:'.transaction-header',
	// selectorFooter:'.transaction-footer',
	// selectorLeftSide:'.transaction-left-side',
	// selectorRightSide:'.transaction-right-side',
	selectorLedger:'.tr-row-ledger',
	ledger_ajax_url:'index.php?page=xepan_accounts_transactionwidget_ledger',
	save_ajax_url:'index.php?page=xepan_accounts_transactionwidget_save',
	selectorAmount:'.tr-row-amount',
	selectorLeftSideRow:'.tr-row.DR',
	selectorRightSideRow:'.tr-row.CR',
	selectorExchangeRate:'.tr-row-exchange-rate',
	left_sum:0,
	right_sum:0,
	total_left_sum:0,
	total_right_sum:0,
	left_side:{},
	right_side:{},

	options:{
		entry_template:{
			
		},
		currency_list:[],
	},

	_create: function(){
		var self = this;

		self.loadData();
		self.addLiveEvents();
		self.doCalc();
	},

	loadData: function(){
		var self = this;

		self.section = this.element;

		var date_picker_wrapper = $('<div class="form-group main-box">').appendTo(self.element);
		$('<label for="datepickerDate">Date</label>').appendTo(date_picker_wrapper);
		var date_picker_group = $('<div class="input-group">').appendTo(date_picker_wrapper);
		$('<span class="input-group-addon"><i class="fa fa-calendar"></i></span>').appendTo(date_picker_group)

		self.transaction_date = $('<input type="text" style="text-align:center;" name="startDate" id="transaction-date" class="transaction-date tra-form-field" />').appendTo(date_picker_group);
		$(self.transaction_date).datepicker({dateFormat: 'dd-MM-yy'});
		
		var default_date = new Date();

		entry_data = JSON.parse(self.options.entry_template);
		$.each(entry_data,function(tr_id,transaction_data){
			
			// setting up date picker
			if(transaction_data.transaction_date){
				var date = new Date(transaction_data.transaction_date);
				// alert(transaction_data.transaction_date);
				$(self.transaction_date).datepicker('setDate',date);
				// console.log(self.default_date);
			}
			
			// alert(default_date+" = "+transaction_data.transaction_date);

			self.lister = $('<div class="well transaction-row"></div>').appendTo(self.element);
			self.lister.attr('id','tra_'+tr_id);

			self.header = $('<div>').appendTo(self.lister);
			// set header values
			$('<h2 class="transaction-name text-center">'+transaction_data.name+'</h2>').appendTo(self.header);

			this.balance_output = $('<div class="balance_output text-center xepan-push-small " style="border:3px solid green;width:20%;margin:auto;padding:5px">').appendTo(self.header);
			this.balance_output.html('====').attr('id','tra_balance_'+tr_id);

			self.row_section = $('<div class="row"></div>').appendTo(self.lister);
			self.left_side = $('<div class="col-md-6 col-sm-6 col-xs-6 col-lg-6">').appendTo(self.row_section);
			self.right_side = $('<div class="col-md-6 col-sm-6 col-xs-6 col-lg-6">').appendTo(self.row_section);
				
			self.footer = $('<div class="transaction-footer"></div>').appendTo(self.lister);
						
			//narration
			var narration_field = [
								'<div class="form-group">',
									'<label for="'+tr_id+'">Narration</label>',
									'<textarea class="form-control tra-narration" id="'+tr_id+'" rows="3">'+((transaction_data.narration)?(transaction_data.narration):"")+'</textarea>',
								'</div>',
								].join("");
			$(narration_field).appendTo(self.footer);


			// console.log(transaction_data.rows);
			$.each(transaction_data.rows,function(tr_row_id,row_data){
				self.addRow(row_data);
			});
			
		});

		self.saveButton = $('<div class="btn btn-primary btn-block transaction-save">Save</div>').appendTo(self.element);
	},

	addRow:function(row_data){
		var self = this;
		var str_plus = '<span class="input-group-addon tr-row-add-ledger">+</span>';
		if(parseInt(row_data.is_allow_add_ledger) != 1)
			str_plus = "";

		var currency_html = "";
		if(parseInt(row_data.is_include_currency) == 1){
			
			var currency_list =  JSON.parse(self.options.currency_list);
			var currency_options = '<option value="0"> Please Select</option>';
			$.each(currency_list,function(key, currency_data){
				if(row_data.currency == key)
					currency_options += '<option selected value="'+key+'"> '+currency_data.name+'</option>';
				else
					currency_options += '<option value="'+key+'"> '+currency_data.name+'</option>';
			});


			if(row_data.exchange_rate == "undefined" || row_data.exchange_rate == undefined)
				row_data.exchange_rate = 1;

		  	currency_html	= [
		  				'<div class="row">',
							'<div class="col-md-8 col-sm-8 col-lg-8 col-xs-8">',
								'<div class="form-group">',
									'<label>Currency Name</label>',
	          						'<select data-field="tr-row-currency" class="tr-row-currency tra-form-field">',
	          						' '+currency_options,
	          						'</select>',
								'</div>',
							'</div>',
							'<div class="col-md-4 col-sm-4 col-lg-4 col-xs-4">',
								'<div class="form-group">',
									'<label>Currency Rate</label>',
									'<input data-field="tr-row-exch-currency-rate" class="tr-row-exchange-rate tra-form-field" placeholder="currency rate" value="'+row_data.exchange_rate+'" />',
								'</div>',
							'</div>',
						'</div>',
						].join("");
		}

		var row_html = [
						'<div class="well tr-row '+row_data.side+'" data-ledger_name="'+row_data.ledger_name+'" data-title="'+row_data.title+'" data-code="'+row_data.code+'" data-side="'+row_data.side+'" data-group="'+row_data.group+'" data-balance_sheet="'+row_data.balance_sheet+'" data-parent_group="'+row_data.parent_group+'" data-ledger="'+row_data.ledger+'" data-ledger_type="'+row_data.ledger_type+'" data-is_ledger_changable="'+row_data.is_ledger_changable+'" data-is_allow_add_ledger="'+row_data.is_allow_add_ledger+'" data-is_include_currency="'+row_data.is_include_currency+'" data-entry_template_id="'+row_data.entry_template_id+'" data-amount="'+row_data.amount+'" data-currency="'+row_data.currency+'" data-exchange_rate="'+row_data.exchange_rate+'" >',
						'<div class="tr-row-duplicate btn btn-primary"><i class="fa fa-copy"></i></div>',						
						'<div class="tr-row-remove btn btn-danger"><i class="fa fa-trash"></i></div>',						
						'<div class="row">',
							'<div class="col-md-8 col-sm-8 col-lg-8 col-xs-8">',
								'<div class="form-group">',
									'<label>'+row_data.title+'</label>',
									'<div class="input-group" style="width:100%;">',
		          						'<input type="text" data-field="tr-row-ledger" placeholder="select ledger" class="tr-row-ledger ui-autocomplete-input form-control tra-form-field"/>',
										' '+str_plus,
									'</div>',
								'</div>',
							'</div>',
							'<div class="col-md-4 col-sm-4 col-lg-4 col-xs-4">',
								'<div class="form-group">',
									'<label>Amount</label>',
									'<input class="tr-row-amount tra-form-field" data-field="tr-row-amount" placeholder="amount" value="'+((row_data.amount)?(row_data.amount):0)+'" />',
								'</div>',
							'</div>',
						'<input class="tr-row-ledger-id" type="hidden" value="'+row_data.ledger+'"/>',
						'</div>',
						' '+currency_html,
						'</div>',
						].join("");

		var side = self.left_side;
		if(row_data.side == "CR")  side = self.right_side;
		$(row_html).appendTo(side);

	},

	// headerBalance: function(){
	// 	var self = this;
		
	// 	this.balance_output = $('<div class="balance_output text-center xepan-push-small " style="border:3px solid green;width:20%;margin:auto;padding:5px">').appendTo(self.header);
	// 	this.balance_output.html('====');
	// },

	addLiveEvents: function(){
		var self = this;

		// MAKE ITEM FIELD AUTO COMLETE
		$(self.selectorLedger).livequery(function(){ 
			var autocomplete_field = this;
			var ledger_name = $(autocomplete_field).closest('div.tr-row').attr('data-ledger_name');
		  	if(ledger_name == "null" || ledger_name == undefined || !ledger_name.length ){
		  		ledger_name = "";
		  	}

		    // use the helper function hover to bind a mouseover and mouseout event 
		    var autocomplete  = $(this).autocomplete({
				source:function( request, response ) {
				    	$.ajax( {
				    		url: self.ledger_ajax_url,
				    		dataType: "json",
				    		data: {
				    			term: request.term,
				    			code: $(autocomplete_field).closest('div.tr-row').attr('data-code'),
				    			side: $(autocomplete_field).closest('div.tr-row').attr('data-side'),
				    			group: $(autocomplete_field).closest('div.tr-row').attr('data-group'),
				    			ledger: $(autocomplete_field).closest('div.tr-row').attr('data-ledger'),
				    			ledger_type: $(autocomplete_field).closest('div.tr-row').attr('data-ledger_type'),
				    			is_ledger_changable: $(autocomplete_field).closest('div.tr-row').attr('data-is_ledger_changable'),
				    			is_allow_add_ledger: $(autocomplete_field).closest('div.tr-row').attr('data-is_allow_add_ledger'),
				    			is_include_currency: $(autocomplete_field).closest('div.tr-row').attr('data-is_include_currency'),
				    			entry_template_id: $(autocomplete_field).closest('div.tr-row').attr('data-entry_template_id'),
				    			currency: $(autocomplete_field).closest('div.tr-row').attr('data-currency'),
				    			exchange_rate: $(autocomplete_field).closest('div.tr-row').attr('data-exchange_rate')
							},
				          	success: function( data ) {
				            	response( data );
				          	}
				        });
				    },
				minLength:1,
				select: function( event, ui ) {
					$tr = $(this).closest('.tr-row');
					$tr.find('.tr-row-ledger-id').val(ui.item.id);
			   },
			}).val(ledger_name);
		    // ,funciton(){
		    	// if field not found then
		    // }
		});

		// amount field chnage
		$(self.selectorAmount).livequery(function(){
			
			$(this).keyup(function(e){
				self.doCalc();
			});

		});

		// duplicate options
		$('.tr-row-duplicate').livequery(function(){
			$(this).click(function(e){
				var current_row = $(this).closest('.tr-row');
				var new_row = $(current_row).clone();
				$(new_row).insertAfter(current_row);
				$(new_row).attr('data-ledger_name',"");
				
				$(new_row).find(self.selectorAmount).val(0);
				$(new_row).find(self.selectorLedger).val("");
				$(new_row).find('.tr-row-currency').val(0);
				$(new_row).find('.tr-row-exchange-rate').val(1);

				self.doCalc();
			});
			
		});

		// tr-row-remove
		$('.tr-row-remove').livequery(function(){
			$(this).click(function(e){
				$(this).closest('.tr-row').remove();
				self.doCalc();
			});
			
		});

		// tr-error-box remove
		// Remove Error Box after change
		$('.tra-form-field').livequery(function(){
			$(this).change(function(){
				$(this).closest('.form-group')
					.find('.error-message')
					.remove()
					;
				$(this).removeClass('tra-field-error');
			});
		});

		// save button
		$('.transaction-save').livequery(function(){

			$(this).click(function(e){
				if(self.total_left_sum != self.total_right_sum){
					alert('credit and debit amount must be same '+self.total_left_sum+" = "+self.total_right_sum);
					return;
				}
				var data_object = {};
				var entry_temp_data = JSON.parse(self.options.entry_template);
					
				var all_clear = true;
				$.each(entry_temp_data,function(entry_tr_id,entry_data){
					var temp = {};
					temp.entry_template_transaction_id = entry_data.entry_template_transaction_id;
					temp.entry_template_id = entry_data.entry_template_id;
					temp.name = entry_data.name;
					temp.type = entry_data.type;
					temp.is_system_default = entry_data.is_system_default;
					temp.editing_transaction_id = entry_data.editing_transaction_id;
					temp.narration = $(self.element).find('#tra_'+entry_tr_id+' .tra-narration').val();
					temp.transaction_date = $(self.element).find('#transaction-date').datepicker().val();

					data_object[entry_tr_id] = temp;
					
					var all_row_data = {};
					var count = 0
					$(self.element).find('#tra_'+entry_tr_id+' .tr-row').each(function(index,obj){
						var one_row_data = {};

						// transaction date
						$tran_date = $(self.element).find('#transaction-date');
						if(!$tran_date.datepicker().val()){
							// alert($tran_date.datepicker().val());
							self.showFieldError($tran_date,"please select transaction date");
							if(all_clear) all_clear = false;
							return false;
						}

						$ledger = $(this).find('.tr-row-ledger');
						$amount = $(this).find('.tr-row-amount');
						
						var ledger_value = $.trim($ledger.val());
						var amount_value = $.trim($amount.val());

						// validation
						// amount cannot be null
						if(amount_value && !isNumber(amount_value)){
							self.showFieldError($amount,"amount must be number");
							all_clear = false;
							return false;
						}

						// if ledger not selected and amount selected
						if( (ledger_value == "" || ledger_value == null || ledger_value == undefined) && amount_value > 0){
							self.showFieldError($ledger,"please select ledger");
							if(all_clear) all_clear = false;
							return false;
						}

						// if ledger selected but amount is not selected
						if( ledger_value.length > 0 && ( amount_value =="" || amount_value == null || amount_value == undefined || !isNumber(amount_value) )){
							self.showFieldError($amount,"amount must not be empty");
							if(all_clear) all_clear = false;
							return false;
						}

						// if both values are empty then bypass this row
						if(
							(ledger_value == "" || ledger_value == null || ledger_value == undefined)
							&&
							(amount_value == "" || amount_value == null || amount_value == undefined || amount_value == 0)
						){
							return true;
						}

						//currency and exchange rate validation
						$currency = $(this).find('.tr-row-currency');
						$exchange_rate = $(this).find('.tr-row-exchange-rate');
						var currency_value = $currency.val();
						var exchange_rate_value = $exchange_rate.val();

						if($currency.length && (currency_value == 0 || currency_value == undefined || currency_value == "" || currency_value == null)){
							self.showFieldError($currency,"please select currency");
							if(all_clear) all_clear = false;
							return false;
						}

						if($exchange_rate.length && (exchange_rate_value == null || exchange_rate_value == "" || exchange_rate_value == undefined || exchange_rate_value == 0 || exchange_rate_value == "undefined")){
							self.showFieldError($exchange_rate,"currency/exchange rate must not be empty or zero");
							if(all_clear) all_clear = false;
							return false;
						}

	//============== end validation =================================

						// implementing array
						$($(obj)[0].attributes).each(function() {
							attr_name = this.nodeName;
							attr_value = this.nodeValue;
							one_row_data[attr_name] = attr_value;
						});
						
					
						//replace selected values
						// amount
						one_row_data['data-amount'] = ($(this).find(self.selectorAmount).val())?($(this).find(self.selectorAmount).val()):0;
						
						// ledger
						if($(this).find('.tr-row-ledger').val()){
							one_row_data['data-ledger'] = $(this).find('.tr-row-ledger-id').val();
							one_row_data['data-ledger_name'] = $(this).find('.tr-row-ledger').val();
						}
						
						//currency
						if($(this).find('.tr-row-currency').val())
							one_row_data['data-currency'] = $(this).find('.tr-row-currency').val();

						//exchange rate
						if($(this).find('.tr-row-exchange-rate').val())
							one_row_data['data-exchange_rate'] = $(this).find('.tr-row-exchange-rate').val();

						all_row_data[count] = one_row_data;
						count++;
					});

					data_object[entry_tr_id].rows = all_row_data;
				});			
				
				if(!all_clear){
					return;
				} 
				
				//calling save page
				$.ajax({
					url: self.save_ajax_url,					
					type: 'POST',
					datatype:'json',
					data: {
						transaction_data: JSON.stringify(data_object)
					}
				}).done(function(ret){
					if(ret == "success"){
						$.univ().successMessage('saved successfully');
					}else{
						$.univ().errorMessage('something wrong');
					}

				});
				
				// console.log(data_object);
			});

		});

		// add new ledger dialog button
		$('.tr-row-add-ledger').livequery(function(){
			$(this).click(function(e){

				$row = $(this).closest('.tr-row');

				form = "<div id='posform'>";
				form += "</div>";
				new_ledger_dialog = $(form).dialog({
					autoOpen: true,
			      	height: 300,
			      	width:300,
					modal: true,
					buttons: {
						'Save and Select': function(){

						},
						Cancel: function() {
							new_ledger_dialog.dialog( "close" );
						}
					},
					close: function() {
						$(this).remove();
					}
				});
			});
		});
	},

	showFieldError:function($field_obj,msg="please select"){
		$('html,body').animate({
        	scrollTop: $field_obj.offset().top
        	},'slow');

		$field_obj.addClass('tra-field-error');
		$field_obj.closest('.form-group').find('.error-message').remove();
		$('<div class="error-message">'+msg+'</div>').appendTo($field_obj.closest('.form-group'));
		
	},

	doCalc: function(){
		var self = this;
		self.total_right_sum = 0;
		self.total_left_sum = 0;
		// IF any row has exchange_rate take amount* exchange_rate as final amount for that row
		var is_cr_dr_equal = 1;		
		$(self.element).find('.transaction-row ').each(function(index,transaction_row){

			self.left_sum = 0;
			self.right_sum = 0;
			$(transaction_row).find(self.selectorLeftSideRow).each(function(index,obj){
				if(isNumber($(obj).find(self.selectorAmount).val())){
					// $(obj).find(self.selectorAmount).css('border','2px solid green');
					var amount = parseFloat($(obj).find(self.selectorAmount).val());
					var exchange_rate = 1;
					if($(obj).find(self.selectorExchangeRate).length > 0){
						if(isNumber($(obj).find(self.selectorExchangeRate).first().val()))
							exchange_rate = $(obj).find(self.selectorExchangeRate).first().val();
						amount = amount * exchange_rate;
					}
					self.left_sum += amount;
					self.total_left_sum += amount;
				}
			});

			// left side
			$(transaction_row).find(self.selectorRightSideRow).each(function(index,obj){
				if(isNumber($(obj).find(self.selectorAmount).val())){
					
					// $(obj).find(self.selectorAmount).css('border','2px solid red');

					var amount = parseFloat($(obj).find(self.selectorAmount).val());
					var exchange_rate = 1;
					if($(obj).find(self.selectorExchangeRate).length > 0){
						if(isNumber($(obj).find(self.selectorExchangeRate).first().val()))
							exchange_rate = $(obj).find(self.selectorExchangeRate).first().val();
						amount = amount * exchange_rate;
					}
					self.right_sum += amount;
					self.total_right_sum += amount;
				}
			});
			
			// console.log("left sum"+self.left_sum);
			// console.log("left sum"+self.right_sum);
			self.balance_output = $(transaction_row).find('.balance_output');
			self.showOutput();

			if( is_cr_dr_equal && (self.left_sum != self.right_sum)){
				is_cr_dr_equal = 0;
			}
		});


		if(is_cr_dr_equal == 1){
			$(self.element).find('.transaction-save').html('Save').removeClass('disabled btn-warning');
		}else{
			$(self.element).find('.transaction-save').html('debit and credit amount must be same').addClass('disabled btn-warning');
		}
	},

	showOutput: function(){
		var self = this;
		// console.log(self.left_sum);
		// console.log(self.right_sum);

		if(self.left_sum > self.right_sum){
			self.balance_output.html('+ Left ' + (self.left_sum - self.right_sum));
			self.balance_output.css('border','3px solid red');
			self.balance_output.css('margin-left','0');
			self.balance_output.css('margin-right','auto');
		} 
			
		if(self.left_sum < self.right_sum){
			self.balance_output.html('+ Right ' + (self.right_sum - self.left_sum));
			self.balance_output.css('border','3px solid red');
			self.balance_output.css('margin-left','auto');
			self.balance_output.css('margin-right','0');
			
		} 
		if(self.left_sum === self.right_sum){
			self.balance_output.html('====');
			self.balance_output.css('border','3px solid green');
			self.balance_output.css('margin','auto');
		} 
	}
});

function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

$.ui.autocomplete.prototype._renderItem = function(ul, item){

	return $("<li></li>")
		.data("item.autocomplete", item)
		// this is autocomplete list that is generated
		.append("<a class='item-autocomplete-list'> " + item.name +"</a>")
		.appendTo(ul)
		;
};
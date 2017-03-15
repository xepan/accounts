jQuery.widget("ui.transaction_executer", {
	selectorHeader:'.transaction-header',
	selectorLeftSide:'.transaction-left-side',
	selectorRightSide:'.transaction-right-side',
	selectorLedger:'.tr-row-ledger',
	ledger_ajax_url:'index.php?page=xepan_accounts_transactionwidget_ledger',
	selectorAmount:'.tr-row-amount',
	selectorLeftSideRow:'.tr-row.DR',
	selectorRightSideRow:'.tr-row.CR',
	selectorExchangeRate:'.tr-row-exchange-rate',

	options:{
		entry_template:{
			
		},
		currency_list:[]

	},

	_create: function(){
		var self = this;

		self.loadData();
		self.headerBalance();
		self.addLiveEvents();

	},

	loadData: function(){
		var self = this;

		this.section = this.element;
		self.header = $(this.element).find(self.selectorHeader);
		self.left_side = $(this.element).find(self.selectorLeftSide);
		self.right_side = $(this.element).find(self.selectorRightSide);

		entry_data = JSON.parse(self.options.entry_template);

		$.each(entry_data,function(tr_id,transaction_data){
			// set header values
			self.header.find('.transaction-name').html(transaction_data.name);

			$.each(transaction_data.rows,function(tr_row_id,row_data){
				self.addRow(row_data);
			});
		});
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

		  	currency_html	= [
		  				'<div class="row">',
							'<div class="col-md-8 col-sm-8 col-lg-8 col-xs-8">',
								'<div class="input-group">',
									'<label>Currency Name</label>',
	          						'<select data-field="tr-row-currency" class="tr-row-currency">',
	          						' '+currency_options,
	          						'</select>',
								'</div>',
							'</div>',
							'<div class="col-md-4 col-sm-4 col-lg-4 col-xs-4">',
								'<div class="form-group">',
									'<label>Currency Rate</label>',
									'<input data-field="tr-row-exch-currency-rate" class="tr-row-exchange-rate" placeholder="currency rate" value="'+row_data.exchange_rate+'" />',
								'</div>',
							'</div>',
						'</div>',
						].join("");
		}


		var row_html = [
						'<div class="well tr-row '+row_data.side+'" data-ledger_name="'+row_data.ledger_name+'" data-title="'+row_data.title+'" data-code="'+row_data.code+'" data-side="'+row_data.side+'" data-group="'+row_data.group+'" data-balance_sheet="'+row_data.balance_sheet+'" data-parent_group="'+row_data.parent_group+'" data-ledger="'+row_data.ledger+'" data-ledger_type="'+row_data.ledger_type+'" data-is_ledger_changable="'+row_data.is_ledger_changable+'" data-is_allow_add_ledger="'+row_data.is_allow_add_ledger+'" data-is_include_currency="'+row_data.is_include_currency+'" data-entry_template_id="'+row_data.entry_template_id+'" data-amount="'+row_data.amount+'" data-currency="'+row_data.currency+'" data-exchange_rate="'+row_data.exchange_rate+'" >',
						'<div class="row">',
							'<div class="col-md-8 col-sm-8 col-lg-8 col-xs-8">',
								'<div class="form-group">',
									'<label>'+row_data.title+'</label>',
									'<div class="input-group" style="width:100%;">',
		          						'<input type="text" data-field="tr-row-ledger" placeholder="select ledger" class="tr-row-ledger ui-autocomplete-input form-control"/>',
										' '+str_plus,
									'</div>',
								'</div>',
							'</div>',
							'<div class="col-md-4 col-sm-4 col-lg-4 col-xs-4">',
								'<div class="form-group">',
									'<label>Amount</label>',
									'<input class="tr-row-amount" data-field="tr-row-amount" placeholder="amount" value="'+((row_data.amount)?(row_data.amount):0)+'" />',
								'</div>',
							'</div>',
						'</div>',
						' '+currency_html,
						'</div>',
						].join("");

		var side = self.left_side;
		if(row_data.side == "CR")  side = self.right_side;
		$(row_html).appendTo(side);

	},

	headerBalance: function(){
		var self = this;
		
		this.balance_output = $('<div class="balance_output text-center xepan-push-small " style="border:3px solid green;width:20%;margin:auto;padding:5px">').appendTo(self.selectorHeader);
		this.balance_output.html('====');
	},

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
				// select: function( event, ui ) {
				// 	// after select auto fill qty and price
				// 	$tr = $(this).closest('.col-data');
				// 	$tr.find('.price-field').val(ui.item.price);
				// 	$tr.find('.item-id-field').val(ui.item.id);
				// 	$tr.find('.item-custom-field').val(ui.item.custom_field);
				// 	$tr.find('.item-read-only-custom-field').val(ui.item.read_only_custom_field);

				// 	// on selct get custom field of item
				// 	$.ajax({
				// 		url:self.item_detail_ajax_url,
				// 		data:{
				// 			item_id:ui.item.id
				// 		},
				// 		success: function( data ) {
				// 			$tr.find('.item-read-only-custom-field').val(data);
				// 			self.showCustomFieldForm($tr);
			 //          	},
			 //          	error: function(XMLHttpRequest, textStatus, errorThrown) {
			 //              alert("Error getting prospect list: " + textStatus);
			 //            }
				// 	});
			 //    }
			}).val(ledger_name);
		    // ,funciton(){
		    	// if field not found then
		    // }
		});

		// amount field chnage
		$(self.selectorAmount).livequery(function(){
			
			$(this).keyup(function(e){
				self.left_sum=0;
				self.right_sum=0;
				self.doCalc();
				self.showOutput();
			});

		});
	},

	doCalc: function(){
		var self = this;
		// IF any row has exchange_rate take amount* exchange_rate as final amount for that row
		$(self.element).find(self.selectorLeftSideRow).each(function(index,obj){

			if(isNumber($(obj).find(self.selectorAmount).val())){
				var amount = parseFloat($(obj).find(self.selectorAmount).val());
				var exchange_rate = 1;
				if($(obj).find(self.selectorExchangeRate).length > 0){
					if(isNumber($(obj).find(self.selectorExchangeRate).first().val()))
						exchange_rate = $(obj).find(self.selectorExchangeRate).first().val();
					amount = amount * exchange_rate;
				}
				self.left_sum += amount;
			}
		});

		// left side
		$(self.element).find(self.selectorRightSideRow).each(function(index,obj){
			if(isNumber($(obj).find(self.selectorAmount).val())){
				var amount = parseFloat($(obj).find(self.selectorAmount).val());
				var exchange_rate = 1;
				if($(obj).find(self.selectorExchangeRate).length > 0){
					if(isNumber($(obj).find(self.selectorExchangeRate).first().val()))
						exchange_rate = $(obj).find(self.selectorExchangeRate).first().val();
					amount = amount * exchange_rate;
				}
				self.right_sum += amount;
			}
		});
	},

	showOutput: function(){
		var self = this;
		// console.log(self.left_sum);
		// console.log(self.right_sum);

		if(self.left_sum > self.right_sum){
			this.balance_output.html('+ Left ' + (self.left_sum - self.right_sum));
			this.balance_output.css('border','3px solid red');
			this.balance_output.css('margin-left','0');
			this.balance_output.css('margin-right','auto');
		} 
			
		if(self.left_sum < self.right_sum){
			this.balance_output.html('+ Right ' + (self.right_sum - self.left_sum));
			this.balance_output.css('border','3px solid red');
			this.balance_output.css('margin-left','auto');
			this.balance_output.css('margin-right','0');
			
		} 
		if(self.left_sum === self.right_sum){
			this.balance_output.html('====');
			this.balance_output.css('border','3px solid green');
			this.balance_output.css('margin','auto');
		} 
	}
});

function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}
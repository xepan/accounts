jQuery.widget("ui.transaction_executer", {
	selectorHeader:'.transaction-header',
	selectorLeftSide:'.transaction-left-side',
	selectorRightSide:'.transaction-right-side',
	selectorLedger:'.tr-row-ledger',
	ledger_ajax_url:'index.php?page=xepan_accounts_transactionwidget_ledger',

	options:{
		entry_template:{
		
		},

	},

	_create: function(){
		var self = this;

		self.loadData();
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
			self.header.find('h1').html(transaction_data.name);

			$.each(transaction_data.rows,function(tr_row_id,row_data){
				self.addRow(row_data);
			});
		});
	},

	addRow:function(row_data){
		var self = this;


		var row_html = [
						'<div class="well tr-row" data-title="'+row_data.title+'" data-code="'+row_data.code+'" data-side="'+row_data.side+'" data-group="'+row_data.group+'" data-balance_sheet="'+row_data.balance_sheet+'" data-parent_group="'+row_data.parent_group+'" data-ledger="'+row_data.ledger+'" data-ledger_type="'+row_data.ledger_type+'" data-is_ledger_changable="'+row_data.is_ledger_changable+'" data-is_allow_add_ledger="'+row_data.is_allow_add_ledger+'" data-is_include_currency="'+row_data.is_include_currency+'" data-entry_template_id="'+row_data.entry_template_id+'" data-amount="'+row_data.amount+'" data-currency="'+row_data.currency+'" data-exchange_rate="'+row_data.exchange_rate+'" >',
						'<div class="ledger-group">',
          					'<input data-field="tr-row-ledger" placeholder="ledger" class="tr-row-ledger"/>',
						'</div>',
						'<input data-field="tr-row-amount" value="'+row_data.amount+'" />',
						'</div>',
						].join("");

		var side = self.left_side;
		if(row_data.side == "CR")  side = self.right_side;
		$(row_html).appendTo(side);

	},

	addLiveEvents: function(){
		var self = this;
		// MAKE ITEM FIELD AUTO COMLETE
		$(self.selectorLedger).livequery(function(){ 
			var autocomplete_field = this;
		    // use the helper function hover to bind a mouseover and mouseout event 
		    $(this).autocomplete({
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
			});
		    // ,funciton(){
		    	// if field not found then
		    // }
		});
	}


});
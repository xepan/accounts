jQuery.widget("ui.transaction_executer", {
	selectorHeader:'.transaction-header',
	selectorLeftSide:'.transaction-left-side',
	selectorRightSide:'.transaction-right-side',

	options:{
		entry_template:{
		
		},

	},

	_create: function(){
		var self = this;

		self.loadData();
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
						'<div class="well tr_row" data-side="'+row_data.side+'">',
						'<p>'+row_data.title+'</p>',
						'</div>',
						].join("");

		var side = self.left_side;
		if(row_data.side == "CR")  side = self.right_side;
		
		$(row_html).appendTo(side);

	}

});
jQuery.widget("ui.xepan_accounts_widget", {

	left_sum: 0,
	right_sum : 0,

	_create: function(){
		var self=this;

		this.section=this.element;
		// $(this.section).find('.amount,.exchange_rate').forceNumeric();
		this.balance_output= $('<div class="balance_output text-center xepan-push-small " style="border:3px solid black;width:20%;margin:auto;padding:5px">').prependTo(this.element);
		this.balance_output.html('====');
		$(this.section).find('.amount,.exchange_rate').keyup(function(e){
			// if(isNumber($(this).val())){
				self.left_sum=0;
				self.right_sum=0;
				self.doCalc();
				self.showOutput();
			// }
		});
	},

	doCalc: function(){
		var self = this;

		// IF any row has exchange_rate take amount* exchange_rate as final amount for that row
		
		$(self.element).find('.transaction-row.left').each(function(index,obj){
			if(isNumber($(obj).find('.amount').val())){
				var amount = parseFloat($(obj).find('.amount').val());
				var exchange_rate = 1;
				if($(obj).find('.exchange_rate').length > 0){
					if(isNumber($(obj).find('.exchange_rate').first().val()))
						exchange_rate = $(obj).find('.exchange_rate').first().val();
					amount = amount * exchange_rate;
				}
				self.left_sum += amount;
			}
		});

		$(self.element).find('.transaction-row.right').each(function(index,obj){
			if(isNumber($(obj).find('.amount').val())){
				var amount = parseFloat($(obj).find('.amount').val());
				var exchange_rate = 1;
				if($(obj).find('.exchange_rate').length > 0){
					if(isNumber($(obj).find('.exchange_rate').first().val()))
						exchange_rate = $(obj).find('.exchange_rate').first().val();
					amount = amount * exchange_rate;
				}
				self.right_sum += amount;
			}
		});
	},

	showOutput: function(){
		var self=this;
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
			this.balance_output.css('border','3px solid red');
			this.balance_output.css('margin','auto');
		} 
	}

});

function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

// jQuery.fn.forceNumeric = function () {

//              return this.each(function () {
//                  $(this).keydown(function (e) {
//                      var key = e.which || e.keyCode;

//                      if (!e.shiftKey && !e.altKey && !e.ctrlKey &&
//                      // numbers   
//                          key >= 48 && key <= 57 ||
//                      // Numeric keypad
//                          key >= 96 && key <= 105 ||
//                      // comma, period and minus, . on keypad
//                         key == 190 || key == 188 || key == 109 || key == 110 ||
//                      // Backspace and Tab and Enter
//                         key == 8 || key == 9 || key == 13 ||
//                      // Home and End
//                         key == 35 || key == 36 ||
//                      // left and right arrows
//                         key == 37 || key == 39 ||
//                      // Del and Ins
//                         key == 46 || key == 45)
//                          return true;

//                      return false;
//                  });
//              });
//          }
/** 
 * Show info limit when limit is set
 */

$(document).ready(function() {

	$("#inputAmount").blur(function(event) {
		event.preventDefault();
		
		var amount = $('#inputAmount').val();
		correctAmount = amount.replace(",", ".");
		var dateExpense = $('#inputDate').val();
		var expenseCategory = $('#expenseCategories').val();
		
		if((isNaN(correctAmount) == false) && (expenseCategory != undefined)) {
			$.post("ajaxInfo", {
				correctAmount: correctAmount,
				dateExpense: dateExpense,
				expenseCategory: expenseCategory
			}, function(data) {
			$("#infoLimit").html(data);
			});
		} else {
			$("#infoLimit").html('');
		}
	});
	
	$("#inputDate").blur(function(event) {
		event.preventDefault();
		
		var amount = $('#inputAmount').val();
		correctAmount = amount.replace(",", ".");
		var dateExpense = $('#inputDate').val();
		var expenseCategory = $('#expenseCategories').val();
		
		if((isNaN(correctAmount) == false) && (expenseCategory != undefined)) {
			$.post("ajaxInfo", {
				correctAmount: correctAmount,
				dateExpense: dateExpense,
				expenseCategory: expenseCategory
			}, function(data) {
			$("#infoLimit").html(data);
			});
		} else {
			$("#infoLimit").html('');
		}
	});
	
	$("#expenseCategories").blur(function(event) {
		event.preventDefault();
		
		var amount = $('#inputAmount').val();
		amount = amount.replace(",", ".");
		var dateExpense = $('#inputDate').val();
		var expenseCategory = $('#expenseCategories').val();
		
		if((isNaN(correctAmount) == false) && (expenseCategory != undefined)) {
			$.post("ajaxInfo", {
				correctAmount: correctAmount,
				dateExpense: dateExpense,
				expenseCategory: expenseCategory
			}, function(data) {
			$("#infoLimit").html(data);
			});
		} else {
			$("#infoLimit").html('');
		}
	});
	
});

$(document).on('click', '#out-tree .table.table-hover thead th', function() {	
	var searchWord = $('#out-tree div[name="searchWrapper"] input').val();
	$.ajax({
		type: 'POST',
		data: {order: $(this).attr('name'),
				sort: $(this).attr('sort'),
				searchWord: searchWord
			},
		success: function(response) {
			var response = JSON.parse(response);
			if ($('#out-tree table[name="employeeTable"]')) {
				$('#out-tree table[name="employeeTable"]').replaceWith(response.newTable);
			}
		}
	})
});

$(document).on('click', '#out-tree div[name="searchWrapper"] button', function() {	
	var searchWord = $('#out-tree div[name="searchWrapper"] input').val();
	if (searchWord.length > 0) {
		$.ajax({
			type: 'POST',
			data: 'searchWord='+searchWord,
			success: function(response) {
				var response = JSON.parse(response);				
				if ($('#out-tree table[name="employeeTable"]') && response.newTable != false) {
					$('#out-tree table[name="employeeTable"]').replaceWith(response.newTable);
				} else if (response.msg.length > 0) {
					alert(response.msg);
				}
			}
		})
	}
});
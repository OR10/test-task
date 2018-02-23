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

$(document).on('click', '#out-tree table[name="employeeTable"] td:has(i[name="editBtn"])', function() {
	$('#employeeModal').modal("show");
	var dataRow = $(this).closest("tr");
	var employeeId = $(dataRow).find('td[name="employeeId"]').text();
	var fullName = $(dataRow).find('td[name="fullName"]').text();
	var positionId = $(dataRow).find('td[name="positionName"]').attr('positionId');
	var salary = $(dataRow).find('td[name="salary"]').text();
	var parentId = $(dataRow).find('td[name="parentName"]').attr('parentId');
	var recruitingDate = $(dataRow).find('td[name="recruitingDate"]').text();

	setModalFields();
	setModalFields(fullName, positionId, salary, parentId, recruitingDate);

	$('#saveBtn').off();
	$('#saveBtn').on('click', function() {
		var newFullName = $('#employeeModal .modal-body input[name="fullName"]').val()
		var newPositionId = $('#employeeModal .modal-body select[name="positions"]').find(":selected").attr('value');
		var newSalary = $('#employeeModal .modal-body input[name="salary"]').val()
		var newParentId = $('#employeeModal .modal-body select[name="employees"]').find(":selected").attr('value');
		var newRecruitingDate = $('#employeeModal .modal-body input[name="recruitingDate"]').val();

		if (fullName.localeCompare(newFullName) != 0 
			|| positionId != newPositionId 
			|| salary.localeCompare(newSalary) != 0 
			|| parentId != newParentId 
			|| recruitingDate.localeCompare(newRecruitingDate) != 0) {

			$.ajax({
				type: 'POST',
				url: '/update-employee',
				data: {
					employeeId: employeeId,
					fullName: newFullName,
					positionId: newPositionId,
					salary: newSalary,
					parentId: newParentId,
					recruitingDate: newRecruitingDate
				},
				success: function(response) {
					var response = JSON.parse(response);
					if (response.error !== undefined && response.error.length > 0) {
						alert(response.error);
					} else {
						$('#employeeModal').modal("hide");
						if ($('#out-tree table[name="employeeTable"]')) {
							$('#out-tree table[name="employeeTable"]').replaceWith(response.newTable);
						}
						$('#employeeModal').on('hidden.bs.modal', function () {
					    	alert('Data has been successfully updated!');
					    	$('#employeeModal').off('hidden.bs.modal');
						});
					}
				}
			});
		}
	});
});

function setModalFields(p1='', p2='', p3='', p4='', p5='') {
	$('#employeeModal .modal-body input[name="fullName"]').val(p1);
	$('#employeeModal .modal-body select[name="positions"] option[value="'+p2+'"]').prop('selected', true);
	$('#employeeModal .modal-body input[name="salary"]').val(p3);
	$('#employeeModal .modal-body select[name="employees"] option[value="'+p4+'"]').prop('selected', true);
	$('#employeeModal .modal-body input[name="recruitingDate"]').val(p5);
}

$('#addEmployee').on('click', function() {
	$('#employeeModal').modal("show");
	$('#employeeModal h4.modal-title').text('Add new Employee');
	setModalFields();

	$('#saveBtn').off();
	$('#saveBtn').on('click', function() {		
		var fullName = $('#employeeModal .modal-body input[name="fullName"]').val()
		var positionLevel = $('#employeeModal .modal-body select[name="positions"]').find(":selected").attr('level');
		var positionId = $('#employeeModal .modal-body select[name="positions"]').find(":selected").attr('value');
		var salary = $('#employeeModal .modal-body input[name="salary"]').val()
		var parentLevel = $('#employeeModal .modal-body select[name="employees"]').find(":selected").attr('level');
		var parentId = $('#employeeModal .modal-body select[name="employees"]').find(":selected").attr('value');
		var recruitingDate = $('#employeeModal .modal-body input[name="recruitingDate"]').val();

		if (fullName.length > 0
			&& positionLevel > parentLevel
			&& salary.length > 0 && isNaN(salary) == false
			&& recruitingDate.length > 0) {
			dateObj = new Date(recruitingDate);
			var year = dateObj.getFullYear();
			var month = parseInt(dateObj.getMonth().toString()) + 1;
			var day = dateObj.getDate().toString();

			if (String(month).length == 1) {
				month = '0'+month;
			}
			if (day.length == 1) {
				day = '0'+day;
			}

			if (isNaN(year) == false && isNaN(month) == false && isNaN(day) == false) {
				var date = year+'-'+month+'-'+day;
				$.ajax({
					type: 'POST',
					url: '/add-employee',
					data: {
						fullName: fullName,
						positionId: positionId,
						salary: salary,
						parentId: parentId,
						recruitingDate: date
					},
					success: function(response) {
						var response = JSON.parse(response);
						if (response.error !== undefined && response.error.length > 0) {
							console.log(response.error);
						} else {
							$('#employeeModal').modal("hide");
							if ($('#out-tree table[name="employeeTable"]')) {
								$('#out-tree table[name="employeeTable"]').replaceWith(response.newTable);
							}
							$('#employeeModal').on('hidden.bs.modal', function () {
						    	alert('Employee has been successfully added!');
						    	$('#employeeModal').off('hidden.bs.modal');
							});							
						}

					}
				})
			} else {
				alert('Bad date!');
			}
		}
	});
});

$('#out-tree table td[name="deleteCell"]').on('click', function() {
	var href = $('#out-tree table td[name="deleteCell"] a').attr('href');
	window.location.href = href;
	// $('#out-tree table td[name="deleteCell"] a').click();
	// $('#out-tree table td[name="deleteCell"]').off();
});
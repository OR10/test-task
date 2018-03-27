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
			if ($('#employeeTable')) {
				$('#employeeTable').replaceWith(response.newTable);
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
				if ($('#employeeTable') && response.newTable != false) {
					$('#employeeTable').replaceWith(response.newTable);
				} else if (response.msg.length > 0) {
					alert(response.msg);
				}
			}
		})
	}
});

// When change current employee's position in modal window
function checkCurrentPositionChange() {
	var currentEmployeeLevel = $(document).find('#currentPosition select[name="positions"]').find(':selected').attr('level');
	$(document).find('#parentEmployee select[name="employees"] option').each(function() {
		var parentLevel = $(this).attr('level');
		if (currentEmployeeLevel - parentLevel != 1) {
			$(this).attr('disabled', 'disabled');
		} else if ($(this).attr('disabled') !== undefined) {
			$(this).removeAttr('disabled');
		}
	})
}

$(document).ready(function() {	
	$('#currentPosition select[name="positions"]').on('change', function() {
		checkCurrentPositionChange();
	});
})

$(document).on('click', '#out-tree table[name="employeeTable"] td:has(i[name="editBtn"])', function() {
	$('#employeeModal').modal("show");
	$('#employeeModal h4.modal-title').text('Edit Employee');
	// Reset old downloaded image from the form if exists
	$("#uploadImage").replaceWith($("#uploadImage").val('').clone(true));
	var dataRow = $(this).closest("tr");
	var employeeId = $(dataRow).find('td[name="employeeId"]').text();
	var fullName = $(dataRow).find('td[name="fullName"]').text();
	var positionId = $(dataRow).find('td[name="positionName"]').attr('positionId');
	var salary = $(dataRow).find('td[name="salary"]').text();
	var parentId = $(dataRow).find('td[name="parentName"]').attr('parentId');
	var recruitingDate = $(dataRow).find('td[name="recruitingDate"]').text();

	setModalFields();
	setModalFields(fullName, positionId, salary, parentId, recruitingDate);
	checkCurrentPositionChange();

	var fileName = $(dataRow).find('#headshot img').attr('src');
	if (fileName !== undefined) {
		if ($('#currentModalImg').length) {
			$('#currentModalImg').attr('src', fileName);
		} else {
			$('#uploadImage').before(function() {
				var tag = '<div class="form-group">';
				tag += '<label>Current image</label>';
				tag += '<img id="currentModalImg" src="'+fileName+'">';
				tag += '</div>';

				return tag;
			});
		}
	}

	$('#saveBtn').off();
	$('#saveBtn').on('click', function() {
		var newFullName = $('#employeeModal .modal-body input[name="fullName"]').val()
		var newPositionId = $('#employeeModal .modal-body select[name="positions"]').find(":selected").attr('value');
		var newSalary = $('#employeeModal .modal-body input[name="salary"]').val()
		var newParentId = $('#employeeModal .modal-body select[name="employees"]').find(":selected").attr('value');
		var newRecruitingDate = $('#employeeModal .modal-body input[name="recruitingDate"]').val();
		var newFile = $('#uploadImage input:file')[0].files[0];

		if (fullName.localeCompare(newFullName) != 0 
			|| positionId != newPositionId 
			|| salary.localeCompare(newSalary) != 0 
			|| parentId != newParentId 
			|| recruitingDate.localeCompare(newRecruitingDate) != 0
			|| newFile != undefined) {

			var formData = new FormData();
			formData.append('employeeId', employeeId);
			formData.append('fullName', newFullName);
			formData.append('positionId', newPositionId);
			formData.append('salary', newSalary);
			formData.append('parentId', newParentId);
			formData.append('recruitingDate', newRecruitingDate);
        	formData.append('image_image', newFile);

			$.ajax({
				type: 'POST',
				url: '/update-employee',
				processData: false,
				contentType: false,
				data: formData,
				success: function(response) {
					var response = JSON.parse(response);
					if (response.error !== null && response.error !== undefined && response.error.length > 0) {
						alert(response.error);
					} else {
						$('#employeeModal').modal("hide");
						if ($('#employeeTable')) {
							$('#employeeTable').replaceWith(response.newTable);
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

$(document).on('click', '#addEmployee', function() {
	$('#employeeModal').modal("show");
	$('#employeeModal h4.modal-title').text('Add new Employee');
	// Reset old downloaded image from the form if exists
	$("#uploadImage").replaceWith($("#uploadImage").val('').clone(true));
	if ($('#currentModalImg').length) {
		$('#currentModalImg').closest('div.form-group').remove();
	}
	setModalFields();
	checkCurrentPositionChange();

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
			&& (positionLevel - parentLevel == 1 || positionId == undefined)
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

				var file = $('#uploadImage input:file')[0].files[0];
				var formData = new FormData();
				if (file) {
	        		formData.append('image_image', file);
				}
				formData.append('fullName', fullName);
				formData.append('positionId', positionId);
				formData.append('salary', salary);
				formData.append('parentId', parentId);
				formData.append('recruitingDate', date);

				$.ajax({
					type: 'POST',
					url: '/add-employee',
        			processData: false,
					contentType: false,
					data: formData,
					success: function(response) {
						var response = JSON.parse(response);
						if (response.error !== undefined && response.error !== null && response.error.length > 0) {
							alert(response.error);
						} else {
							$('#employeeModal').modal("hide");
							if ($('#employeeTable')) {
								$('#employeeTable').replaceWith(response.newTable);
							}
							$("#parentEmployee").replaceWith(response.parentEmployee);
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
		} else {
			alert('Some data is not correct!');
		}
	});
});

$('#out-tree table td[name="deleteCell"]').on('click', function(e) {
	var href = $(this).children('a').attr('href');
	window.location.href = href;
	e.preventDefault();
});

$(document).ready(function() {
	$('#employees-tree a').each(function() {
		if ($(this).attr('level') > 2) {
			$(this).hide();
		}
	})
	$('#employees-tree a').on('click', function(e) {
		e.preventDefault();
		if ($(this).attr('level') == 2) {
			showNext($(this));
		}
	})
});

function showNext(currentElem) {
	var nextElem = $(currentElem).next();
	if (nextElem.attr('level') != 2) {
		if ($(nextElem).is(':hidden')) {
			$(nextElem).show('slow');
		} else {
			$(nextElem).hide('slow');
		}
		showNext(nextElem);
	} else {
		return;
	}
}
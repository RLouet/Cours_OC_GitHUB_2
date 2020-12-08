$(document).ready(function() {

	let $confirmModal = $('#confirmModal');

	$confirmModal.on('show.bs.modal', function(event) {
		let $button = $(event.relatedTarget);
		let action = $button.data('action');
		let id = $button.closest('tr').data('id');
		let username = $button.closest('tr').data('username');
		let $confirmButton = $('.confirm-btn', this);

		switch (action) {
			case 'bannir':
				$messageField = $('<label for="MessageField">Message (optionnel)</label><textarea rows="5" name="message_field" id="MessageField" class="form-control"></textarea>');
				$('form', this).attr('action', 'banish');
				$('.fields-container', this).html($messageField);
				break;
			case 'débannir':
				$('form', this).attr('action', 'unbanish');
				break;
			case 'supprimer':
				$('form', this).attr('action', 'delete');
				break;
			case 'promouvoir':
				$('form', this).attr('action', 'up');
				break;
			case 'rétrograder':
				$('form', this).attr('action', 'down');
				break;
		}

		if (action == "bannir") {
		}
		if (action == "bannir") {
			$messageField = $('<label for="MessageField">Message (optionnel)</label><textarea rows="5" name="message_field" id="MessageField" class="form-control"></textarea>');
			$('form', this).attr('action', 'banish');
			$('.fields-container', this).html($messageField);
		}

		$('.confirm-item-username', this).html(username);
		$('.action', this).html(action);
		$('.id', this).html(id);
		$('.id-field', this).val(id);
		$confirmButton.html(action);

	});

	$confirmModal.on('hide.bs.modal', function(event) {
		$('.form-error', $(this)).addClass('hidden');
		$('.fields-container', this).html("");
		$('form', this).removeAttr('action');
	});

	$('form', $confirmModal).submit(function (e) {
		e.preventDefault();
		let $form = $(this);
		let formAction = $(this).attr('action');
		$('.form-error', $(this)).addClass('hidden');
		let formData = $form.serialize();
		$.ajax({
			url: window.location.origin + '/ajax/' + formAction + 'User',
			method: "POST",
			data: formData,
			dataType: "json",
			success: function (data) {
				if (!data.success) {
					var errorMessage = '<ul>';
					for (var k in data.errors) {
						errorMessage += '<li>' + data.errors[k] + '</li>';
					}
					errorMessage += '</li';
					$('.modal-error .form-error span', $confirmModal).html(errorMessage);
					$('.modal-error .form-error', $confirmModal).removeClass('hidden');
				} else {
					window.location.reload();
				}
			},
			error: function (e) {
				$('.modal-error .form-error span', $confirmModal).html('Erreur Ajax');
				$('.modal-error .form-error', $confirmModal).removeClass('hidden');
			}
		})
	});
})
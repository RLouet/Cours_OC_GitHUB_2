$(document).ready(function() {

	let $deleteModal = $('#deleteModal');

	$deleteModal.on('show.bs.modal', function(event) {

		let $button = $(event.relatedTarget);
		let id = $button.data('id');
		let name = $button.data('name');
		let $deleteButton = $('.delete-btn', this);

		$('.delete-item-name', this).html(name);
		$deleteButton.data('id', id);
	});

	$('.delete-btn', $deleteModal).click(function () {
		$.ajax({
			url: window.location.origin + '/ajax/deletePost',
			method: "POST",
			data: {
				'id': $(this).data('id')
			},
			dataType: "json",
			success: function (data) {
				if (!data.success) {
					var errorMessage = '<ul>';
					for (var k in data.errors) {
						errorMessage += '<li>' + data.errors[k] + '</li>';
					}
					errorMessage += '</li';
					$('.delete-error .form-error span', $deleteModal).html(errorMessage);
					$('.delete-error .form-error', $deleteModal).removeClass('hidden');
				} else {
					let $itemBox = $('.post-item-' + data.deleted);
					$itemBox.remove();
					$deleteModal.modal('hide');
					showFlashMessage('success', 'Le post a bien été supprimé.')
				}
			},
			error: function (e) {
				$('.delete-error .form-error span', $deleteModal).html('Erreur Ajax');
				$('.delete-error .form-error', $deleteModal).removeClass('hidden');
			}
		})
	});

	$deleteModal.on('hide.bs.modal', function(event) {
		$('.form-error', $(this)).addClass('hidden');
	});
})
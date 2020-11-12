$(document).ready(function() {
	$('#passwordModal form').submit(function (e) {
		e.preventDefault();
		let $form = $(this);
		let formAction = $(this).attr('action');
		$('.form-error', $(this)).addClass('hidden');
		let formData = $form.serialize();

		let oldPassword = $('.pw-old input', $(this)).val();
		let newPassword = $('.pw-new input', $(this)).val();
		let confPassword = $('.pw-conf input', $(this)).val();

		if (newPassword !== confPassword) {
			$('input.form-control', $(this)).val("");
			$('.pw-conf .form-error', $form).removeClass('hidden');
			return
		}

		//alert (formData);
		$.ajax({
			url: formAction,
			method: "POST",
			data: formData,
			dataType: "json",
			success: function (data) {
				if (!data.success){
					if (data.token_error) {
						document.location.reload();
					}
					if (data.old_error) {
						$('.pw-old .form-error', $form).removeClass('hidden');
					}
					if (data.new_error) {
						$('.pw-new .form-error', $form).removeClass('hidden');
					}
					if (data.conf_error) {
						$('.pw-conf .form-error', $form).removeClass('hidden');
					}
					if (data.user_error) {
						$('.pw-general-error .form-error span', $form).html('Votre profil est invalide');
						$('.pw-general-error .form-error', $form).removeClass('hidden');
					}
					if (data.db_error) {
						$('.pw-general-error .form-error span', $form).html("Erreur lors de l'enregistrement");
						$('.pw-general-error .form-error', $form).removeClass('hidden');
					}
					$('input.form-control', $(this)).val("");
				} else {
					$('#passwordModal').modal('hide');

					showFlashMessage('success', 'Votre mot de passe a bien été modifié')
				}
			},
			error: function (e) {
				//alert('ajax');
				$('.sk-general-error .form-error span', $form).html('Erreur Ajax');
				$('.sk-general-error .form-error', $form).removeClass('hidden');
			}
		})
	});

	$('#passwordModal').on('hide.bs.modal', function(event) {
		$('input.form-control', this).val('');

		$('.form-error', $(this)).addClass('hidden');
	});

	$('#socialModal form').submit(function (e) {
		e.preventDefault();
		let $form = $(this);
		let formAction = $(this).attr('action');
		$('.form-error', $(this)).addClass('hidden');
		let formData = new FormData(this);

		$.ajax({
			url: formAction,
			method: "POST",
			data: formData,
			dataType: "json",
			cache:false,
			contentType: false,
			processData: false,
			success: function (data) {
				if (!data.success){
					var errorMessage = '<ul>';
					for (var k in data.errors) {
						errorMessage += '<li>' + data.errors[k] + '</li>';
					}
					errorMessage += '</li';
					$('.sn-general-error .form-error div', $form).html(errorMessage);
					$('.sn-general-error .form-error', $form).removeClass('hidden');

					for (var fe in data.form_errors) {
						if (data.form_errors[fe] === 2) {
							$('.sn-name .form-error', $form).removeClass('hidden');
						}
						if (data.form_errors[fe] === 4) {
							$('.sn-url .form-error', $form).removeClass('hidden');
						}
					}
				} else {
					let $socialBox = $('.social-box-' + data.entity.id );
					let action = 'modifié';
					$('#socialModal').modal('hide');
					if (!$socialBox.length) {
						action = 'ajouté';
						$('#addSocialNetworkBtn').before('<div class="col-md-6 col-lg-4 mb-4 px-sm-0 px-md-3 social-box-' + data.entity.id + '">\n' +
							'                            <div class="no-img-effect rounded over-hide p-4 call-box-5">\n' +
							'                                <div class="row">\n' +
							'                                    <div class="col-5">\n' +
							'                                        <img src="" class="sn-logo" data-file="">\n' +
							'                                    </div>\n' +
							'                                    <div class="col-7">\n' +
							'                                        <h5 class="sn-name"></h5>\n' +
							'                                    </div>\n' +
							'                                    <div class="col-12 text-center mt-2">\n' +
							'                                        <p class="sn-url"></p>\n' +
							'                                    </div>\n' +
							'                                    <div class="col-12 row justify-content-between">\n' +
							'                                        <div class="col-4 text-center">\n' +
							'                                            <button class="btn btn-sm btn-danger btn-delete" data-toggle="modal" data-target="#deleteModal" data-id="' + data.entity.id + '" data-name="' + data.entity.name + '" data-type="réseau social">Supprimer</button>\n' +
							'                                        </div>\n' +
							'                                        <div class="col-4 text-center">\n' +
							'                                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#socialModal" data-action="edit" data-id="' + data.entity.id + '">Modifier</button>\n' +
							'                                        </div>\n' +
							'                                    </div>\n' +
							'                                </div>\n' +
							'                            </div>\n' +
							'                        </div>');
						$socialBox = $('.social-box-' + data.entity.id );
					}
					//$socialBox.remove();
					$('.sn-logo', $socialBox).attr('src', window.location.origin + '/uploads/icons/' + data.entity.blogId + '/' + data.entity.logo).data('file', data.entity.logo);
					$('.sn-name', $socialBox).html(data.entity.name);
					$('.btn-delete', $socialBox).data('name', data.entity.name);
					$('.sn-url', $socialBox).html(data.entity.url);
					showFlashMessage('success', 'Le réseau social a bien été ' + action + '.')
				}
			},
			error: function (e) {
				//alert('ajax');
				$('.sn-general-error .form-error span', $form).html('Erreur Ajax');
				$('.sn-general-error .form-error', $form).removeClass('hidden');
			}
		})
	});

})
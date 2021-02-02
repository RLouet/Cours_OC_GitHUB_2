$(document).ready(function() {
	$("#passwordModal form").submit(function (e) {
		e.preventDefault();
		let $form = $(this);
		let formAction = $(this).attr("action");
		$(".form-error", $(this)).addClass("hidden");
		let formData = $form.serialize();

		let oldPassword = $(".pw-old input", $(this)).val();
		let newPassword = $(".pw-new input", $(this)).val();
		let confPassword = $(".pw-conf input", $(this)).val();

		if (newPassword !== confPassword) {
			$("input.form-control", $(this)).val("");
			$(".pw-conf .form-error", $form).removeClass("hidden");
			return;
		}

		$.ajax({
			url: formAction,
			method: "POST",
			data: formData,
			dataType: "json",
			success(data) {
				if (!data.success){
					if (data.token_error) {
						document.location.reload();
					}
					if (data.old_error) {
						$(".pw-old .form-error", $form).removeClass("hidden");
					}
					if (data.new_error) {
						$(".pw-new .form-error", $form).removeClass("hidden");
					}
					if (data.conf_error) {
						$(".pw-conf .form-error", $form).removeClass("hidden");
					}
					if (data.user_error) {
						$(".pw-general-error .form-error span", $form).html("Votre profil est invalide");
						$(".pw-general-error .form-error", $form).removeClass("hidden");
					}
					if (data.db_error) {
						$(".pw-general-error .form-error span", $form).html("Erreur lors de l'enregistrement");
						$(".pw-general-error .form-error", $form).removeClass("hidden");
					}
					$("input.form-control", $(this)).val("");
				} else {
					$("#passwordModal").modal("hide");

					showFlashMessage("success", "Votre mot de passe a bien été modifié");
				}
			},
			error(e) {
				$(".sk-general-error .form-error span", $form).html("Erreur Ajax");
				$(".sk-general-error .form-error", $form).removeClass("hidden");
			}
		});
	});

	$("#passwordModal").on("hide.bs.modal", function(event) {
		$("input.form-control", this).val("");

		$(".form-error", $(this)).addClass("hidden");
	});

});
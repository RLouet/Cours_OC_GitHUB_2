/*global showFlashMessage*/
$(document).ready(function() {

	let $confirmModal = $("#confirmModal");

	$confirmModal.on("show.bs.modal", function(event) {
		let $button = $(event.relatedTarget);
		let action = $button.data("action");
		let id = $button.closest("tr").data("id");
		let username = $button.closest("tr").data("username");
		let $confirmButton = $(".confirm-btn", this);

		switch (action) {
			case "bannir":
				$("form", this).attr("action", "banish");
				$(".delete-messages-switch", this).removeClass("hidden");
				$("form .confirm-notice", this).html("L'utilisateur ne pourra plus se connecter ni s'inscrire avec la même adresse Email.");
				break;
			case "débannir":
				$("form", this).attr("action", "unbanish");
				$("form .confirm-notice", this).html("L'utilisateur pourra à nouveau se connecter et publier sur le blog.");
				break;
			case "supprimer":
				$("form", this).attr("action", "delete");
				$("form .confirm-notice", this).html("L'utitilisateur ainsi que ses posts et commentaires seront supprimés du blog.");
				break;
			case "promouvoir":
				$("form", this).attr("action", "up");
				$("form .confirm-notice", this).html("L'utilisateur pourra créer et publier des posts.");
				break;
			case "rétrograder":
				$("form", this).attr("action", "down");
				$("form .confirm-notice", this).html("L'utilisateur ne pourra plus créer ni publier de post.<br>Ses anciens posts resteront visibles");
				break;
		}

		$(".confirm-item-username", this).html(username);
		$(".action", this).html(action);
		$(".id", this).html(id);
		$(".id-field", this).val(id);
		$confirmButton.html(action);

	});

	$confirmModal.on("hide.bs.modal", function(event) {
		$(".form-error", $(this)).addClass("hidden");
		$(".delete-messages-switch", this).addClass("hidden");
		$("form", this).removeAttr("action");
		$("form .confirm-notice", this).html("");
		$("#MessageField", this).val("");
	});

	$("form", $confirmModal).submit(function (e) {
		e.preventDefault();
		let $form = $(this);
		let formAction = $(this).attr("action");
		$(".form-error", $(this)).addClass("hidden");
		let formData = $form.serialize();
		let userId = $("input[name=id]",$form).val();
		$.ajax({
			url: window.location.origin + "/ajax/" + formAction + "User",
			method: "POST",
			data: formData,
			dataType: "json",
			success(data) {
				if (!data.success) {
					let errorMessage = "<ul>";
					for (const error of data.errors) {
						errorMessage += "<li>" + error + "</li>";
					}
					errorMessage += "</ul>";
					$(".modal-error .form-error span", $confirmModal).html(errorMessage);
					$(".modal-error .form-error", $confirmModal).removeClass("hidden");
				} else {
					if (formAction === "up" || formAction === "down" || formAction === "delete") {
						window.location.reload();
					}
					if (formAction === "banish" || formAction === "unbanish") {
						let $userRow = $(".user-item-" + userId);
						let $banishBtn = $(".banish-btn", $userRow);
						if (formAction === "banish") {
							$userRow.addClass("table-danger");
							$banishBtn.attr("title", "Débannir").data("action", "débannir");
							$("span.fa", $banishBtn).removeClass("fa-unlock").addClass("fa-lock");
							$(".state-cell", $userRow).html("Banni");
							showFlashMessage("success", "L'utilisateur a bien été banni.");
						}
						if (formAction === "unbanish") {
							$userRow.removeClass("table-danger");
							$banishBtn.attr("title", "bannir").data("action", "bannir");
							$("span.fa", $banishBtn).removeClass("fa-lock").addClass("fa-unlock");
							$(".state-cell", $userRow).html("Activé");
							showFlashMessage("success", "L'utilisateur a bien été débanni.");

						}
						$confirmModal.modal("hide");
					}
				}
			},
			error(e) {
				$(".modal-error .form-error span", $confirmModal).html("Erreur Ajax");
				$(".modal-error .form-error", $confirmModal).removeClass("hidden");
			}
		});
	});

	$("[name='delete_messages']").bootstrapSwitch();
	$(".bootstrap-switch-handle-off").html("NON");
	$(".bootstrap-switch-handle-on").html("OUI");
});
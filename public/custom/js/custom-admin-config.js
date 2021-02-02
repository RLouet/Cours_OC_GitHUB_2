$(document).ready(function() {

	let $deleteModal = $("#deleteModal");

	$deleteModal.on("show.bs.modal", function(event) {

		let $button = $(event.relatedTarget);
		let id = $button.data("id");
		let name = $button.data("name");
		let type = $button.data("type");
		let $deleteButton = $(".delete-btn", this);

		$(".delete-item-name", this).html(name);
		$(".delete-item-type", this).html(type);
		$deleteButton.data("id", id);
		if (type === "skill") {
			$deleteButton.data("method", "deleteSkill");
			$deleteButton.data("box-prefix", ".skill-item-");
		}
		if (type === "réseau social") {
			$deleteButton.data("method", "deleteSocialNetwork");
			$deleteButton.data("box-prefix", ".social-box-");
		}
		$deleteButton.data("type", type);
	});

	$(".delete-btn", $deleteModal).click(function () {
		let method = $(this).data("method");
		let boxPrefix = $(this).data("box-prefix");
		let type = $(this).data("type");
		$.ajax({
			url: window.location.origin + "/ajax/" + method,
			method: "POST",
			data: {
				"id": $(this).data("id")
			},
			dataType: "json",
			success(data) {
				if (!data.success) {
					let errorMessage = "<ul>";
					for (let k in data.errors) {
						if (Object.prototype.hasOwnProperty.call(data.errors, k)) {
							errorMessage += "<li>" + data.errors[k] + "</li>";
						}
					}
					errorMessage += "</ul>";
					$(".delete-error .form-error span", $deleteModal).html(errorMessage);
					$(".delete-error .form-error", $deleteModal).removeClass("hidden");
				} else {
					let $itemBox = $(boxPrefix + data.deleted);
					$itemBox.remove();
					$deleteModal.modal("hide");
					showFlashMessage("success", "Le " + type + " a bien été supprimé.");
				}
			},
			error(e) {
				$(".delete-error .form-error span", $deleteModal).html("Erreur Ajax");
				$(".delete-error .form-error", $deleteModal).removeClass("hidden");
			}
		});
	});

	$("#skillModal").on("show.bs.modal", function(event) {

		let $button = $(event.relatedTarget);
		let action = $button.data("action");

		if (action === "edit" && Number.isInteger($button.data("id"))) {
			$("h5.modal-title span", this).html("Modifier le ");

			let $skillBox = $(".skill-item-" + $button.data("id"));
			$("#skillInput", this).val($(".skill-value",$skillBox).text());

			let hiddenFields = "<input type='hidden' name='id' value='" + $button.data("id") + "'>";
			$(".hidden-fields", this).html(hiddenFields);

			$(".valid-btn", this).val("Modifier");
		} else {
			$("h5.modal-title span", this).html("Ajouter un ");

			$(".valid-btn", this).val("Ajouter");
		}
	});

	$("#skillModal form").submit(function (e) {
		e.preventDefault();
		let $form = $(this);
		let formAction = $(this).attr("action");
		$(".form-error", $(this)).addClass("hidden");
		let formData = $form.serialize();

		$.ajax({
			url: formAction,
			method: "POST",
			data: formData,
			dataType: "json",
			success(data) {
				if (!data.success){
					let errorMessage = "<ul>";
					for (let k in data.errors) {
						if (Object.prototype.hasOwnProperty.call(data.errors, k)) {
							errorMessage += "<li>" + data.errors[k] + "</li>";
						}
					}
					errorMessage += "</ul>";
					$(".sk-general-error .form-error span", $form).html(errorMessage);
					$(".sk-general-error .form-error", $form).removeClass("hidden");

					for (let fe in data.form_errors) {
						if (data.form_errors[fe] === 2) {
							$(".sk-value .form-error", $form).removeClass("hidden");
						}
					}
				} else {
					let $skillBox = $(".skill-item-" + data.entity.id );
					let action = "modifié";
					$("#skillModal").modal("hide");
					if (!$skillBox.length) {
						action = "ajouté";
						$(".skills-list").append("<div class='col-auto mt-3 skill-item-" + data.entity.id + "'>\n" +
							"                        <span class='text-light-green font-weight-bold bigger-1 skill-value'>" + data.entity.value + "</span>\n" +
							"                        <div class='text-center'>\n" +
							"                            <button class='fa-btn' data-toggle='modal' data-target='#skillModal' data-action='edit' data-id='" + data.entity.id + "'><i class='fa fa-edit'></i></button>\n" +
							"                            <button class='fa-btn btn-delete' data-toggle='modal' data-target='#deleteModal' data-id='" + data.entity.id + "' data-name='" + data.entity.value + "' data-type='skill'><i class='fa fa-trash'></i></button>\n" +
							"                        </div>\n" +
							"                    </div>");
					}
					$(".skill-value", $skillBox).html(data.entity.value);
					$(".btn-delete", $skillBox).data("name", data.entity.value);
					showFlashMessage("success", "Le skill a bien été " + action + ".");
				}
			},
			error(e) {
				//alert('ajax');
				$(".sk-general-error .form-error span", $form).html("Erreur Ajax");
				$(".sk-general-error .form-error", $form).removeClass("hidden");
			}
		});
	});

	$("#socialModal").on("show.bs.modal", function(event) {

		let $button = $(event.relatedTarget);
		let action = $button.data("action");

		if (action === "edit" && Number.isInteger($button.data("id"))) {
			$("h5.modal-title span", this).html("Modifier le ");

			let $networkBox = $(".social-box-" + $button.data("id"));
			$("#socialNameInput", this).val($(".sn-name",$networkBox).text());
			$("#socialUrlInput", this).val($(".sn-url",$networkBox).text());
			$("#socialLogoPreview", this).attr("src", $(".sn-logo",$networkBox).attr("src"));
			$("#socialLogoInput", this).val("");

			let hiddenFields = "<input type='hidden' name='old_logo' value='" + $(".sn-logo",$networkBox).data("file") + "'><input type='hidden' name='id' value='" + $button.data("id") + "'>";
			$(".hidden-fields", this).html(hiddenFields);

			$(".valid-btn", this).val("Modifier");
		} else {
			$("h5.modal-title span", this).html("Ajouter un ");

			$("#socialLogoPreview").attr("src", path + "/uploads/icons/empty-icon_128-128.png");

			$(".valid-btn", this).val("Ajouter");
		}
	});

	$("#socialModal, #skillModal, #deleteModal").on("hide.bs.modal", function(event) {
		$("input[type=text]", this).val("");
		$("input[type=file]", this).val("");
		$(".hidden-fields input", this).remove();

		$(".form-error", $(this)).addClass("hidden");
	});

	$("#socialModal form").submit(function (e) {
		e.preventDefault();
		let $form = $(this);
		let formAction = $(this).attr("action");
		$(".form-error", $(this)).addClass("hidden");
		let formData = new FormData(this);

		$.ajax({
			url: formAction,
			method: "POST",
			data: formData,
			dataType: "json",
			cache:false,
			contentType: false,
			processData: false,
			success(data) {
				if (!data.success){
					let errorMessage = "<ul>";
					for (let k in data.errors) {
						if (Object.prototype.hasOwnProperty.call(data.errors, k)) {
							errorMessage += "<li>" + data.errors[k] + "</li>";
						}
					}
					errorMessage += "</ul>";
					$(".sn-general-error .form-error div", $form).html(errorMessage);
					$(".sn-general-error .form-error", $form).removeClass("hidden");

					for (let fe in data.form_errors) {
						if (Object.prototype.hasOwnProperty.call(data.form_errors, fe)) {
							if (data.form_errors[fe] === 2) {
								$(".sn-name .form-error", $form).removeClass("hidden");
							}
							if (data.form_errors[fe] === 4) {
								$(".sn-url .form-error", $form).removeClass("hidden");
							}
						}
					}
				} else {
					let $socialBox = $(".social-box-" + data.entity.id );
					let action = "modifié";
					$("#socialModal").modal("hide");
					if (!$socialBox.length) {
						action = "ajouté";
						$socialBox = $("<div class='col-md-6 col-lg-4 mb-4 px-sm-0 px-md-3 social-box-" + data.entity.id + "'>\n" +
							"                            <div class='no-img-effect rounded over-hide p-4 call-box-5'>\n" +
							"                                <div class='row'>\n" +
							"                                    <div class='col-5'>\n" +
							"                                        <img src='' alt='Logo du réseau social' class='sn-logo' data-file=''>\n" +
							"                                    </div>\n" +
							"                                    <div class='col-7'>\n" +
							"                                        <h5 class='sn-name'></h5>\n" +
							"                                    </div>\n" +
							"                                    <div class='col-12 text-center mt-2'>\n" +
							"                                        <p class='sn-url'></p>\n" +
							"                                    </div>\n" +
							"                                    <div class='col-12 row justify-content-between'>\n" +
							"                                        <div class='col-4 text-center'>\n" +
							"                                            <button class='btn btn-sm btn-danger btn-delete' data-toggle='modal' data-target='#deleteModal' data-id='" + data.entity.id + "' data-name='" + data.entity.name + "' data-type='réseau social'>Supprimer</button>\n" +
							"                                        </div>\n" +
							"                                        <div class='col-4 text-center'>\n" +
							"                                            <button class='btn btn-sm btn-primary' data-toggle='modal' data-target='#socialModal' data-action='edit' data-id='" + data.entity.id + "'>Modifier</button>\n" +
							"                                        </div>\n" +
							"                                    </div>\n" +
							"                                </div>\n" +
							"                            </div>\n" +
							"                        </div>");
						$("#addSocialNetworkBtn").before($socialBox);
						//$socialBox = $('.social-box-' + data.entity.id );
					}
					//$socialBox.remove();
					$(".sn-logo", $socialBox).attr("src", window.location.origin + "/uploads/icons/" + data.entity.blogId + "/" + data.entity.logo).data("file", data.entity.logo);
					$(".sn-name", $socialBox).html(data.entity.name);
					$(".btn-delete", $socialBox).data("name", data.entity.name);
					$(".sn-url", $socialBox).html(data.entity.url);
					showFlashMessage("success", "Le réseau social a bien été " + action + ".");
				}
			},
			error(e) {
				//alert('ajax');
				$(".sn-general-error .form-error span", $form).html("Erreur Ajax");
				$(".sn-general-error .form-error", $form).removeClass("hidden");
			}
		});
	});

	$("#socialLogoInput").change(function(e) {
		if (e.target.files.length > 0) {
			let src = URL.createObjectURL(e.target.files[0]);
			$("#socialLogoPreview").attr("src", src);
		}
	});

	$("#blogLogoInput").change(function(e) {
		if (e.target.files.length > 0) {
			let src = URL.createObjectURL(e.target.files[0]);
			$("#blogLogoPreview").attr("src", src);
		}
	});

	$("#blogCvInput").change(function(e) {
		if (e.target.files.length > 0) {
			let src = URL.createObjectURL(e.target.files[0]);
			let name = e.target.files[0].name;
			$("#blogCvPreview").attr("href", src).html("Nouveau CV : " + name).removeClass("hidden");
		} else {
			$("#blogCvPreview").attr("href", "#").html("erreur").removeClass("hidden");
		}
	});
});
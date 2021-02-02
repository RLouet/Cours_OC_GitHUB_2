$(document).ready(function() {

	let $deleteModal = $("#deleteModal");

	$deleteModal.on("show.bs.modal", function(event) {

		let $button = $(event.relatedTarget);
		let id = $button.data("id");
		let name = $button.data("name");
		let $deleteButton = $(".delete-btn", this);

		$(".delete-item-name", this).html(name);
		$deleteButton.data("id", id);
	});

	$(".delete-btn", $deleteModal).click(function () {
		$.ajax({
			url: window.location.origin + "/ajax/deletePost",
			method: "POST",
			data: {
				"id": $(this).data("id")
			},
			dataType: "json",
			success(data) {
				if (!data.success) {
					let errorMessage = "<ul>";
					for (let k in data.errors) {
						errorMessage += "<li>" + data.errors[k] + "</li>";
					}
					errorMessage += "</ul>";
					$(".delete-error .form-error span", $deleteModal).html(errorMessage);
					$(".delete-error .form-error", $deleteModal).removeClass("hidden");
				} else {
					let $itemBox = $(".post-item-" + data.deleted);
					$itemBox.remove();
					$deleteModal.modal("hide");
					showFlashMessage("success", "Le post a bien été supprimé.")
				}
			},
			error(e) {
				$(".delete-error .form-error span", $deleteModal).html("Erreur Ajax");
				$(".delete-error .form-error", $deleteModal).removeClass("hidden");
			}
		})
	});

	$deleteModal.on("hide.bs.modal", function(event) {
		$(".form-error", $(this)).addClass("hidden");
	});


	// --------------- Pagination ---------------
	$("#ViewMore").click(function () {
		let offset = $("[class*=\"post-item-\"]").length;
		let userId = $(this).data("user");
		$.ajax({
			url: window.location.origin + "/ajax/loadPosts",
			method: "POST",
			data: {
				"offset": offset,
			},
			dataType: "json",
			success(data) {
				if (data.end) {
					$("#ViewMore").parent().remove();
				}
				for (let k in data.posts) {
					let post = data.posts[k];
					let heroUrl = path + "/img/blog/1.jpg";
					if (post.heroUrl) {
						heroUrl = path + "/uploads/blog/" + post.userId + "/" + post.id + "/" + post.heroUrl;
					}
					let heroAlt = "";
					if (post.heroName) {
						heroAlt = post.heroName;
					}
					let postManagementButtons = "";
					if (post.userId === userId || post.userBanished || !post.userAdmin) {
						postManagementButtons = "<div class=\"row justify-content-between\">\n" +
							"                        <a class=\"btn btn-primary btn-sm col-auto\" href=\"" + path + "/admin/posts/" + post.id + "/edit\">Modifier</a>\n" +
							"                        <button class=\"btn btn-danger btn-sm col-auto\" data-toggle=\"modal\" data-target=\"#deleteModal\" data-id=\"" + post.id + "\" data-name=\"" + post.title + "\">Supprimer</button>\n" +
							"                    </div>\n";
					}
					let item = $("<div class=\"grid-box float-inline quarter with-margin drop-shadow rounded post-item-" + post.id + "\">\n" +
						"                            <div class=\"blog-box-1 blog-home blog-admin background-white over-hide\">\n" +
						"                                    <a href=\"" + path + "/admin/posts/" + post.id + "/view\">\n" +
						"                                        <div class=\"portfolio-box-1\">\n" +
						"                                            <img  src=\"" + heroUrl + "\" alt=\"" + heroAlt + "\"  class=\"blog-home-img\"/>\n" +
						"                                            <div class=\"portfolio-mask-2 rounded\"></div>\n" +
						"                                            <h5 class=\"on-center text-center\">Lire la suite ...</h5>\n" +
						"                                        </div>\n" +
						"                                    </a>\n" +
						"                                <div class=\"padding-in\">\n" +
						"                                    <a href=\"" + path + "/admin/posts/" + post.id + "/view\"><h5 class=\"mt-3\">" + post.title + "</h5></a>\n" +
						"                                    <p class=\"mt-3\">" + post.chapo + "</p>\n" +
						"                                    <a href=\"" + path + "/admin/posts/" + post.id + "/view\" class=\"btn-link btn-primary pl-0 mt-4\">Lire la suite</a>\n" +
						"                                    <div class=\"separator-wrap pt-3\">\n" +
						"                                        <span class=\"separator\"><span class=\"separator-line\"></span></span>\n" +
						"                                    </div>\n" +
						"                                    <div class=\"author-wrap mt-3\">\n" +
						"                                        <p> Par <span class=\"text-primary\">" + post.username + "</span>, le <mark>" + post.date + "</mark></p>\n" +
						"                                    </div>\n" +
						postManagementButtons +
						"                                </div>\n" +
						"                            </div>\n" +
						"                        </div>");
					$(".post-list").append(item);
				}
			},
			error: function (e) {
				showFlashMessage("danger", "Une erreur s'est produite.");
			}
		})
	});
})
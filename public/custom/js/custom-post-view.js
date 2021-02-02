$(document).ready(function() {

    // Activate Carousel

    $(".carousel").carousel({
        interval: 5000
    });


    // Confirm Modal

    let $confirmModal = $("#confirmModal");

    $confirmModal.on("show.bs.modal", function(event) {

        let $button = $(event.relatedTarget);
        let id = $button.data("id");
        let action = $button.data("action");
        if (!action) {
            action = "supprimer";
        }
        $(".modal-action", this).html(action);
        let $confirmButton = $(".confirm-btn", this);

        $confirmButton.data("id", id);
        $confirmButton.data("action", action);
    });
    $(".confirm-btn", $confirmModal).click(function () {
        let action = $(this).data("action");
        $.ajax({
            url: window.location.origin + "/ajax/moderateComment",
            method: "POST",
            data: {
                "id": $(this).data("id"),
                "action": action,
                "token": $(this).data("token"),
            },
            dataType: "json",
            success(data) {
                if (!data.success) {
                    var errorMessage = "<ul>";
                    for (var k in data.errors) {
                        errorMessage += "<li>" + data.errors[k] + "</li>";
                    }
                    errorMessage += "</ul>";
                    $(".delete-error .form-error span", $confirmModal).html(errorMessage);
                    $(".delete-error .form-error", $confirmModal).removeClass("hidden");
                } else {
                    let $itemBox = $(".comment-item-" + data.comment);
                    if (action === "supprimer") {
                        $itemBox.remove();
                        showFlashMessage("success", "Le commentaire a bien été supprimé.")
                    }
                    if (action === "valider") {
                        $("div", $itemBox).removeClass("background-dark");
                        $("div", $itemBox).removeClass("color-white rounded-3");
                        $(".waiting-validation",$itemBox).remove();
                        $(".validate-btn",$itemBox).remove();
                        showFlashMessage("success", "Le commentaire a bien été validé.")
                    }
                    $confirmModal.modal("hide");
                }
            },
            error(e) {
                $(".delete-error .form-error span", $confirmModal).html("Erreur Ajax");
                $(".delete-error .form-error", $confirmModal).removeClass("hidden");
            }
        })
    });

    $confirmModal.on("hide.bs.modal", function(event) {
        $(".form-error", $(this)).addClass("hidden");
    });


    // --------------- Pagination ---------------
    $("#ViewMore").click(function () {
        let offset = $(".comment-item").length;
        let postId = $(this).data("post");
        let user = $(this).data("user");
        //alert(offset);
        $.ajax({
            url: window.location.origin + "/ajax/loadPostComments",
            method: "POST",
            data: {
                "offset": offset,
                "post_id": postId,
            },
            dataType: "json",
            success(data) {
                if (data.end) {
                    $("#ViewMore").parent().remove();
                }
                for (let k in data.comments) {
                    let comment = data.comments[k];
                    //alert(comment.id)
                    let notValidatedClass = "";
                    let notValidatedBadge = "";
                    let commentManagementButtons = "";
                    if (!comment.validated) {
                        notValidatedClass = " background-dark color-white rounded-3";
                        notValidatedBadge = "<div class='text-center waiting-validation'><span class='badge badge-pill badge-warning'>En attente de validation</span></div>\n";
                    }
                    if (user === "admin" || user === comment.userId) {
                        commentManagementButtons = "<div class='text-center col-12'>\n";
                        if (user === "admin" && !comment.validated) {
                            commentManagementButtons += "<button type='button' class='btn btn-primary btn-sm m-2 validate-btn' data-toggle='modal' data-target='#confirmModal' data-id=" + comment.id + " data-action='valider'>Valider</button>\n";
                        }
                        commentManagementButtons += "<button type='button' class='btn btn-danger btn-sm m-2' data-toggle='modal' data-target='#confirmModal' data-id=" + comment.id + ">Supprimer</button>\n" +
                            "</div>";
                    }
                    let item = $("<div class='comment-item-" + comment.id + " comment-item'>\n" +
                        "                                                <div class='separator-wrap pt-2'>\n" +
                        "                                                    <span class='separator col-12'><span class='separator-line'></span></span>\n" +
                        "                                                </div>\n" +
                        "                                                <div class='col-12 pt-1" + notValidatedClass + "'>\n" +
                        notValidatedBadge +
                        "                                                    <div class='author-wrap mt-1'>\n" +
                        "                                                        <p> Par <span class='font-weight-bold'>" + comment.username + "</span>, le <mark>" + comment.date + "</mark></p>\n" +
                        "                                                    </div>\n" +
                        "                                                    <p>" + comment.content + "</p>\n" +
                        commentManagementButtons +
                        "                                                </div>\n" +
                        "                                            </div>");
                    $(".comment-list").append(item);
                }
            },
            error(e) {
                showFlashMessage("danger", "Une erreur s'est produite.");
            }
        })
    });

});
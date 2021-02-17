$(document).ready(function() {

    function initImagePreview($field) {
        const maxSize = 4;
        const maxRes = [1280, 1024];
        const minRes = [500, 350];
        $field.change(function(e) {
            e.preventDefault();

            let $container = $(this).closest(".post-image-item");
            let $preview = $(".post-image-preview", $container);
            let $previewLoader = $(".img-prev-ol", $container);

            // If is an image : check and change
            if (e.target.files.length > 0) {
                $previewLoader.show();
                const oldVal = $(this).data('old');
                const image = e.target.files[0];
                const src = URL.createObjectURL(image);
                const size = image.size / 1024 / 1024;
                const oldSrc = $preview.attr("src");
                $preview.attr("src", src);

                // Check size
                if (size > maxSize) {
                    alert ("L'image est trop lourde (max : " + maxSize + " Mo) !");
                    $preview.attr("src", oldSrc);
                    $previewLoader.hide();
                    $(this).val(oldVal);
                    return false;
                }

                // Check resolution
                checkImageResolution(image, $previewLoader, $preview, oldSrc, oldVal, $(this), minRes, maxRes);

                return true;
            }
            return true;
        });
    }

    function checkImageResolution(image, loader, preview, oldSrc, oldVal, field, minRes, maxRes) {
         readFile(image, function (e) {
            const img = new Image();
            img.src = e.target.result;
            img.onload = function () {
                const w = this.width;
                const h = this.height;
                if ( w >= minRes[0] && w <= maxRes[0] && h >= minRes[1] && h <= maxRes[1]) {
                    $("img", field.parent()).removeClass("img-prev-alert");
                    $(".empty-alert", field.parent()).hide();
                    loader.hide();
                    field.data('old', field.val());
                    return;
                }
                alert ("L'image n'a pas la bonne résolution ( Max : " + maxRes[0] + "/" + maxRes[1] + "px, Min : " + minRes[0] + "/" + minRes[1] + "px ) !");
                preview.attr("src", oldSrc);
                loader.hide();
                field.val(oldVal);
            }
         });
    }
    function readFile(file, onLoadCallback) {
        const reader = new FileReader();
        reader.onload = onLoadCallback;
        reader.readAsDataURL(file);
    }

    let $deleteModal = $("#deleteModal");

    $deleteModal.on("show.bs.modal", function(event) {

        let $button = $(event.relatedTarget);
        let id = $button.data("id");
        let type = $button.data("type");
        let name = $(".post-image-name-field", $button.closest(".post-image-item")).val();
        let $deleteButton = $(".delete-btn", this);

        $(".delete-item-name", this).html(name);
        $deleteButton.data("id", id);
        $deleteButton.data("type", type);
    });

    $(".delete-btn", $deleteModal).click(function () {
        let type = $(this).data("type");
        let id = $(this).data("id");
        if (type === "new-image") {
            $(".new-post-image-" + id).remove();
        }
        if (type === "old-image") {
            $(".old-post-image-" + id).remove();
            let $imageToDeleteField = $("<input type='hidden' name='images_to_delete[]' value='" + id + "'>");
            $("#imagesToDelete").append($imageToDeleteField);
        }
        $deleteModal.modal("hide");
    });

    $deleteModal.on("hide.bs.modal", function(event) {
        $(".form-error", $(this)).addClass("hidden");
    });

    initImagePreview($(".post-image-input"));

    let postImageCount = $('.post-image-item').length;

    $("#addPostImage").click(function (e) {
        e.preventDefault();
        let $template = $("  <div class='col-md-6 col-lg-4 col-xl-3 mt-3 post-image-item new-post-image-" + postImageCount + "'>\n" +
            "                    <div class='image-preview-container'>" +
            "                        <label for='newPostImageInput" + postImageCount + "'>" +
            "                            <div>" +
            "                                <img src='" + window.location.origin + "/img/blog/1.jpg' alt='Nouvelle image' class='img-fluid post-image-preview'>" +
            "                                <div class=\"img-prev-ol hidden\">\n" +
            "                                    <div class=\"spinner\"></div>\n" +
            "                                </div>" +
            "                            </div>" +
            "                        </label>" +
            "                        <div class='empty-alert'>" +
            "                            <span>L'image doit être définie.</span>" +
            "                        </div>" +
            "                        <input type='file' name='new_post_image[" + postImageCount + "]' id='newPostImageInput" + postImageCount + "' class='form-control post-image-input' accept='image/*'>" +
            "                    </div>\n" +
            "                    <div><input type='text' placeholder='Description' class='form-control post-image-name-field text-light-green' name='new_post_image[" + postImageCount + "][name]' required></div>\n" +
            "                    <div class='row justify-content-around'>\n" +
            "                        <div class='pt-2'>" +
            "                            <input type='radio' id='radioHeroNew" + postImageCount + "' name='hero' value='new-" + postImageCount + "'>\n" +
            "                            <label for='radioHeroNew" + postImageCount + "'>Hero</label>\n" +
            "                        </div>"+
            "                        <div>" +
            "                            <label for='newPostImageInput" + postImageCount + "' class='fa-btn'><i class='fa fa-edit'></i></label>\n" +
            "                            <button type='button' class='fa-btn btn-delete' data-toggle='modal' data-target='#deleteModal' data-id=\"" + postImageCount + "\" data-name=\"nom de l'image\" data-type='new-image'><i class='fa fa-trash'></i></button>\n" +
            "                        </div>"+
            "                    </div>\n" +
            "                </div>");

        $("input[name='hero']", $template).attr("required", 1);
        $(".images-list").append($template);
        postImageCount++;
        initImagePreview($(".post-image-input", $template));
        $(".post-image-input", $template).click();
    });
/*
    $("#PostForm").submit(function (e) {
        let $NewImageInputs = $("input[type=\"file\"][name^=\"new_post_image\"]", $(this));
        let valid = true
        $NewImageInputs.each(function () {
            if (!$(this).val()) {
                valid = false;
                $("img", $(this).parent()).addClass("img-prev-alert");
                $(".empty-alert", $(this).parent()).show();
                let targetTop = document.getElementById("PostImagesItems").offsetTop;
                $("html, body").animate({scrollTop:targetTop}, 800);
            }
        });
        //alert("submit");
        return valid;
    });*/
});
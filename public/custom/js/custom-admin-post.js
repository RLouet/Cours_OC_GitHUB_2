$(document).ready(function() {

    let $deleteModal = $('#deleteModal');

    $deleteModal.on('show.bs.modal', function(event) {

        let $button = $(event.relatedTarget);
        let id = $button.data('id');
        let type = $button.data('type');
        let name = $('.post-image-name-field', $button.closest('.post-image-item')).val();
        let $deleteButton = $('.delete-btn', this);

        $('.delete-item-name', this).html(name);
        $deleteButton.data('id', id);
        $deleteButton.data('type', type);
    });

    $('.delete-btn', $deleteModal).click(function () {
        let type = $(this).data('type');
        let id = $(this).data('id');
        if (type === "new-image") {
            $('.new-post-image-' + id).remove();
        }
        if (type === "old-image") {
            $('.old-post-image-' + id).remove();
            let $imageToDeleteField = $("<input type='hidden' name='images_to_delete[]' value='" + id + "'>");
            $('#imagesToDelete').append($imageToDeleteField);
        }
        $deleteModal.modal('hide');
    });

    $deleteModal.on('hide.bs.modal', function(event) {
        $('.form-error', $(this)).addClass('hidden');
    });

    initImagePreview($('.post-image-input'));

    let postImageCount = 0;

    $('#addPostImage').click(function (e) {
        e.preventDefault();
        let $template = $("  <div class='col-md-6 col-lg-4 col-xl-3 mt-3 post-image-item new-post-image-" + postImageCount + "'>\n" +
            "                    <div><img src='" + path + "/img/blog/1.jpg' alt='Nouvelle image' class='img-fluid post-image-preview'></div>\n" +
            "                    <div><input type='text' placeholder='Description' class='form-control post-image-name-field' name='new_post_image[" + postImageCount + "][name]' required></div>\n" +
            "                    <input type='file' name='new_post_image[" + postImageCount + "]' id='newPostImageInput" + postImageCount + "' class='form-control post-image-input' accept='image/*' required>" +
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

        $("input[name='hero']", $template).attr('required', 1);
        $('.images-list').append($template);
        initImagePreview($('.post-image-input', $template));
        $('.post-image-input', $template).click();
    })

    function initImagePreview($field) {
        $field.change(function(e) {
            let $container = $(this).closest('.post-image-item');
            if (e.target.files.length > 0) {
                var src = URL.createObjectURL(e.target.files[0]);
                $('.post-image-preview', $container).attr('src', src);
            }
        });
    }
})
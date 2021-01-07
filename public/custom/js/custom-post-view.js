$(document).ready(function() {

    // Activate Carousel

    $('.carousel').carousel({
        interval: 5000
    });


    // Confirm Modal

    let $confirmModal = $('#confirmModal');

    $confirmModal.on('show.bs.modal', function(event) {

        let $button = $(event.relatedTarget);
        let id = $button.data('id');
        let action = $button.data('action');
        if (!action) {
            action = 'supprimer';
        }
        $('.modal-action', this).html(action);
        let $confirmButton = $('.confirm-btn', this);

        $confirmButton.data('id', id);
        $confirmButton.data('action', action);
    });
    $('.confirm-btn', $confirmModal).click(function () {
        let action = $(this).data('action');
        $.ajax({
            url: window.location.origin + '/ajax/moderateComment',
            method: "POST",
            data: {
                'id': $(this).data('id'),
                'action': action,
                'token': $(this).data('token'),
            },
            dataType: "json",
            success: function (data) {
                if (!data.success) {
                    var errorMessage = '<ul>';
                    for (var k in data.errors) {
                        errorMessage += '<li>' + data.errors[k] + '</li>';
                    }
                    errorMessage += '</li';
                    $('.delete-error .form-error span', $confirmModal).html(errorMessage);
                    $('.delete-error .form-error', $confirmModal).removeClass('hidden');
                } else {
                    let $itemBox = $('.comment-item-' + data.comment);
                    if (action == "supprimer") {
                        $itemBox.remove();
                        showFlashMessage('success', 'Le commentaire a bien été supprimé.')
                    }
                    if (action == "valider") {
                        $("div", $itemBox).removeClass('background-dark');
                        $("div", $itemBox).removeClass('color-white rounded-3');
                        $(".waiting-validation",$itemBox).remove();
                        $(".validate-btn",$itemBox).remove();
                        showFlashMessage('success', 'Le commentaire a bien été validé.')
                    }
                    $confirmModal.modal('hide');
                }
            },
            error: function (e) {
                $('.delete-error .form-error span', $confirmModal).html('Erreur Ajax');
                $('.delete-error .form-error', $confirmModal).removeClass('hidden');
            }
        })
    });

    $confirmModal.on('hide.bs.modal', function(event) {
        $('.form-error', $(this)).addClass('hidden');
    });

});
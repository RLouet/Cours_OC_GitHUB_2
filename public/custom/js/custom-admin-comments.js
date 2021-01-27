$(document).ready(function() {

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
                    errorMessage += '</ul>';
                    $('.delete-error .form-error span', $confirmModal).html(errorMessage);
                    $('.delete-error .form-error', $confirmModal).removeClass('hidden');
                } else {
                    let $itemBox = $('.comment-item-' + data.comment);
                    $itemBox.remove();
                    if (action == "supprimer") {
                        showFlashMessage('success', 'Le commentaire a bien été supprimé.')
                    }
                    if (action == "valider") {
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

    // --------------- Pagination ---------------
    $('#ViewMore').click(function () {
        let offset = $('.comment-item').length;
        //alert(offset);
        $.ajax({
            url: window.location.origin + '/ajax/loadUnvalidatedComments',
            method: "POST",
            data: {
                'offset': offset,
            },
            dataType: "json",
            success: function (data) {
                if (data.end) {
                    $('#ViewMore').parent().remove();
                }
                for (let k in data.comments) {
                    let comment = data.comments[k];
                    //alert(comment.id)
                    let item = $('<div class="comment-item-' + comment.id + ' col-md-4 my-2 comment-item">\n' +
                        '                                <div class="col-12 background-white rounded-3 p-2 blog-box-1">\n' +
                        '                                    <a href="' + path + '/admin/posts/' + comment.postId + '/view" class="btn-primary"><h5>' + comment.postTitle + ' (#' + comment.postId + ')</h5></a>\n' +
                        '                                    <a href="' + path + '/admin/posts/' + comment.postId + '/view" class="btn-link btn-primary pl-0 mt-4">Voir le post</a>\n' +
                        '                                    <div class="separator-wrap pt-3">\n' +
                        '                                        <span class="separator"><span class="separator-line"></span></span>\n' +
                        '                                    </div>\n' +
                        '                                    <p></p>\n' +
                        '                                    <div class="author-wrap mt-1">\n' +
                        '                                        <p> Par <span class="font-weight-bold">'+ comment.username + '</span>, le <mark>' + comment.date + '</mark></p>\n' +
                        '                                    </div>\n' +
                        '                                    <p>' + comment.content + '</p>\n' +
                        '                                    <div class="text-center col-12">\n' +
                        '                                        <button type="button" class="btn btn-primary btn-sm mx-2 validate-btn" data-toggle="modal" data-target="#confirmModal" data-id=' + comment.id + ' data-action="valider">Valider</button>\n' +
                        '                                        <button type="button" class="btn btn-danger btn-sm mx-2" data-toggle="modal" data-target="#confirmModal" data-id=' + comment.id + '>Supprimer</button>\n' +
                        '                                    </div>\n' +
                        '                                </div>\n' +
                        '                            </div>');
                    $('.comment-list').append(item);
                }
            },
            error: function (e) {
                showFlashMessage('danger', 'Une erreur s\'est produite.');
            }
        })
    });

});
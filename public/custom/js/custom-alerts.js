$(document).ready(function() {
    $(".alert", $("#flashMessages")).each(function () {
        let $alertContainer = $(this);
        setTimeout(function(){
            $alertContainer.alert("close");
        }, 15000);
    });
});

function showFlashMessage(type, message) {
    let $flashContainer = $("#flashMessages");
    let $flashAlert = $("<div class=\"alert alert-" + type + " alert-dismissible fade show\" role=\"alert\">\n" +
        "            <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Fermer\">\n" +
        "                <span aria-hidden=\"true\"><i class=\"funky-ui-icon icon-Close\"></i></span>\n" +
        "            </button>\n" +
        "            <div>" + message + "</div>\n" +
        "        </div>");
    $flashContainer.append($flashAlert);
    setTimeout(function(){
        $flashAlert.alert("close");
    }, 10000);
}
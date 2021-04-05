import $ from 'jquery';

$('#add-image').click(  ({
    // Je récupère le numéro des futurs champs que je vrais créer
    const index = +$('#widgets-counter').val();

    //je récupère les entrées du prototype
    const tmpl = $('#annonce_images').data('prototype').replace(/__name__/g, index);

    //j'injecte ce code au sein de la div
    $('#annonce_images').append(tmpl);

    $('#widgets-counter').val(index + 1)
    // je gère le boutton supprimer
    handleDeleteButtons();
});

// Delete le button
function handleDeleteButtons() {
    $("button[data-action='delete']").click(function () {
        const target = this.dataset.target;
        $(target).remove();
    })
}

// Met à jour le compteur
function updateCounter() {
    const count = +$('#annonce_images div.form-group').length;

    $('#widgets-counter').val(count);
}
updateCounter();
handleDeleteButtons();
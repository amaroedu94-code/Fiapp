
document.addEventListener("DOMContentLoaded", function () {
    const formularios = document.querySelectorAll(".form-excluir");

    formularios.forEach(function (formulario) {
        formulario.addEventListener("submit", function (evento) {
            const confirmar = confirm(
                "Tem certeza que deseja excluir este alimento?"
            );

            if (!confirmar) {
                evento.preventDefault();
            }
        });
    });
});

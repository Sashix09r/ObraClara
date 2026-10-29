/**
 * ObraClara V2 — JavaScript mínimo, sem dependências.
 * Melhora a navegação e a confirmação de ações, mas nenhuma regra
 * de negócio ou permissão depende deste ficheiro.
 */
(function () {
    'use strict';

    // Menu lateral em ecrãs pequenos.
    var body = document.body;
    var toggle = document.querySelector('[data-nav-toggle]');
    var backdrop = document.querySelector('[data-nav-backdrop]');

    function setNav(open) {
        body.classList.toggle('nav-open', open);

        if (toggle) {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        if (backdrop) {
            backdrop.hidden = !open;
        }
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            setNav(!body.classList.contains('nav-open'));
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', function () {
            setNav(false);
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && body.classList.contains('nav-open')) {
            setNav(false);
            if (toggle) {
                toggle.focus();
            }
        }
    });

    // Fechar mensagens de feedback.
    document.querySelectorAll('[data-dismiss]').forEach(function (button) {
        button.addEventListener('click', function () {
            var alert = button.closest('.alert');
            if (alert) {
                alert.remove();
            }
        });
    });

    // Confirmação explícita antes de ações definitivas (arquivar, submeter).
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-confirm') || 'Confirmar esta ação?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // Destacar a decisão escolhida e exigir observação ao rejeitar.
    var decideForm = document.querySelector('.decide-form');
    if (decideForm) {
        var choices = decideForm.querySelectorAll('.choice');
        var note = decideForm.querySelector('#note');

        function refreshChoices() {
            choices.forEach(function (choice) {
                var input = choice.querySelector('input[type="radio"]');
                choice.classList.toggle('is-selected', !!(input && input.checked));
            });

            var reject = decideForm.querySelector('input[value="rejeitar"]');
            if (note && reject) {
                note.required = reject.checked;
            }
        }

        decideForm.querySelectorAll('input[name="decision"]').forEach(function (input) {
            input.addEventListener('change', refreshChoices);
        });

        refreshChoices();
    }
})();

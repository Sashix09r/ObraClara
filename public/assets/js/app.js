/**
 * ObraClara V2 — JavaScript mínimo, sem dependências.
 * Nenhuma regra de negócio ou permissão depende deste ficheiro.
 */
(function () {
    'use strict';

    var body = document.body;
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ------------------------------------------------------------------
     * Menu lateral
     * ------------------------------------------------------------------ */
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

    window.addEventListener('resize', function () {
        if (window.innerWidth > 820 && body.classList.contains('nav-open')) {
            setNav(false);
        }
    });

    /* ------------------------------------------------------------------
     * Mensagens, confirmações e decisão
     * ------------------------------------------------------------------ */
    document.querySelectorAll('[data-dismiss]').forEach(function (button) {
        button.addEventListener('click', function () {
            var alert = button.closest('.alert');
            if (alert) {
                alert.remove();
            }
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-confirm') || body.getAttribute('data-confirm-default') || '';
            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

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

    /* ------------------------------------------------------------------
     * Mostrar/ocultar palavra-passe
     * ------------------------------------------------------------------ */
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        var input = document.getElementById(button.getAttribute('aria-controls') || '');

        if (!input) {
            return;
        }

        button.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed', show ? 'true' : 'false');
            button.setAttribute(
                'aria-label',
                show
                    ? (button.getAttribute('data-label-hide') || 'Ocultar palavra-passe')
                    : (button.getAttribute('data-label-show') || 'Mostrar palavra-passe')
            );
            input.focus({ preventScroll: true });
        });
    });

    /* ------------------------------------------------------------------
     * Copiar link de convite
     * ------------------------------------------------------------------ */
    var toastTimer = null;

    function showToast(message) {
        var toast = document.querySelector('[data-toast]');
        if (!toast || !message) {
            return;
        }

        toast.textContent = message;
        toast.hidden = false;
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(function () {
            toast.hidden = true;
        }, 3200);
    }

    document.querySelectorAll('[data-copy-target]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.getAttribute('data-copy-target') || '');
            if (!input) {
                return;
            }

            var value = input.value;
            var done = function () {
                showToast(button.getAttribute('data-copy-label') || 'Copiado');
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(value).then(done).catch(function () {
                    input.select();
                    document.execCommand('copy');
                    done();
                });
            } else {
                input.select();
                try {
                    document.execCommand('copy');
                    done();
                } catch (error) {
                    input.focus();
                }
            }
        });
    });

    /* ------------------------------------------------------------------
     * Visita guiada contextual
     * ------------------------------------------------------------------ */
    var TOUR_STEPS = {
        dashboard: [
            'dashboard-kpis',
            'nav-dashboard',
            'nav-clients',
            'nav-projects',
            'nav-history',
            'nav-team',
            'profile-menu',
            'language-switch'
        ],
        project: [
            'project-header',
            'project-budgets',
            'project-changes',
            'project-decide',
            'project-history',
            'project-export',
            'project-new'
        ],
        team: [
            'team-users',
            'team-invites',
            'team-invite-form'
        ],
        profile: [
            'profile-info',
            'profile-security',
            'profile-tour',
            'language-switch'
        ]
    };

    var tourRoot = document.querySelector('[data-tour]');
    var spotlight = tourRoot ? tourRoot.querySelector('[data-tour-spotlight]') : null;
    var popover = tourRoot ? tourRoot.querySelector('[data-tour-popover]') : null;
    var tourTitle = tourRoot ? tourRoot.querySelector('[data-tour-heading]') : null;
    var tourText = tourRoot ? tourRoot.querySelector('[data-tour-body]') : null;
    var tourProgress = tourRoot ? tourRoot.querySelector('[data-tour-progress]') : null;
    var prevButton = tourRoot ? tourRoot.querySelector('[data-tour-prev]') : null;
    var nextButton = tourRoot ? tourRoot.querySelector('[data-tour-next]') : null;
    var skipButton = tourRoot ? tourRoot.querySelector('[data-tour-skip]') : null;
    var welcome = document.querySelector('[data-tour-welcome]');
    var tourSteps = [];
    var tourIndex = 0;
    var tourRenderToken = 0;
    var lastFocus = null;

    function visible(element) {
        return !!(element && (element.offsetWidth || element.offsetHeight || element.getClientRects().length));
    }

    function stepsFor(page) {
        var ids = TOUR_STEPS[page] || TOUR_STEPS.dashboard;
        var seen = {};

        return ids.map(function (id) {
            return document.querySelector('[data-tour-id="' + id + '"]');
        }).filter(function (element) {
            if (!visible(element) || !element.getAttribute('data-tour-title')) {
                return false;
            }

            var key = element.getAttribute('data-tour-id');
            if (seen[key]) {
                return false;
            }
            seen[key] = true;

            return true;
        });
    }

    function positionTour(element) {
        if (!visible(element) || !popover || !spotlight) {
            return;
        }

        var rect = element.getBoundingClientRect();
        var pad = 7;
        var top = Math.max(4, rect.top - pad);
        var left = Math.max(4, rect.left - pad);
        var width = Math.min(window.innerWidth - 8, rect.width + (pad * 2));
        var height = Math.min(window.innerHeight - 8, rect.height + (pad * 2));

        spotlight.style.top = top + 'px';
        spotlight.style.left = left + 'px';
        spotlight.style.width = width + 'px';
        spotlight.style.height = height + 'px';

        var popWidth = Math.min(370, window.innerWidth - 24);
        popover.style.width = popWidth + 'px';

        var popHeight = popover.offsetHeight || 190;
        var below = rect.bottom + 14;
        var above = rect.top - popHeight - 14;
        var popTop = below + popHeight < window.innerHeight - 12 ? below : Math.max(12, above);
        var popLeft = Math.min(
            Math.max(12, rect.left + (rect.width / 2) - (popWidth / 2)),
            window.innerWidth - popWidth - 12
        );

        popover.style.top = popTop + 'px';
        popover.style.left = popLeft + 'px';
    }

    function renderTourStep() {
        if (!tourRoot || tourSteps.length === 0) {
            return;
        }

        if (tourIndex < 0) {
            tourIndex = 0;
        }

        if (tourIndex >= tourSteps.length) {
            finishTour(true);
            return;
        }

        var element = tourSteps[tourIndex];
        var token = ++tourRenderToken;

        if (!visible(element)) {
            tourIndex++;
            renderTourStep();
            return;
        }

        if (element.closest('.sidebar') && window.innerWidth <= 820) {
            setNav(true);
        }

        element.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });

        if (tourTitle) {
            tourTitle.textContent = element.getAttribute('data-tour-title') || '';
        }
        if (tourText) {
            tourText.textContent = element.getAttribute('data-tour-text') || '';
        }
        if (tourProgress) {
            tourProgress.textContent = (body.getAttribute('data-tour-step') || 'Passo :current de :total')
                .replace(':current', String(tourIndex + 1))
                .replace(':total', String(tourSteps.length));
        }
        if (prevButton) {
            prevButton.disabled = tourIndex === 0;
        }
        if (nextButton) {
            nextButton.textContent = tourIndex === tourSteps.length - 1
                ? (body.getAttribute('data-tour-finish') || 'Concluir')
                : (body.getAttribute('data-tour-next') || 'Seguinte');
        }

        window.setTimeout(function () {
            if (token !== tourRenderToken) {
                return;
            }

            positionTour(element);

            if (popover) {
                popover.focus({ preventScroll: true });
            }
        }, reduceMotion ? 0 : 280);
    }

    function openTour(page) {
        tourSteps = stepsFor(page);

        if (tourSteps.length === 0) {
            return false;
        }

        lastFocus = document.activeElement;
        tourIndex = 0;
        tourRoot.hidden = false;
        body.classList.add('tour-open');
        renderTourStep();

        return true;
    }

    function closeTour() {
        if (tourRoot) {
            tourRoot.hidden = true;
        }
        body.classList.remove('tour-open');
        tourRenderToken++;

        if (lastFocus && typeof lastFocus.focus === 'function') {
            lastFocus.focus({ preventScroll: true });
        }
    }

    function markTourComplete(action) {
        var endpoint = body.getAttribute('data-tour-endpoint');

        if (!endpoint) {
            return;
        }

        var data = new URLSearchParams();
        data.set('_token', body.getAttribute('data-csrf') || '');
        data.set('action', action || 'complete');

        fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
            body: data.toString()
        }).catch(function () {
            // A visita continua utilizável mesmo sem rede; nada de dados é alterado.
        });
    }

    function finishTour(showMessage) {
        closeTour();
        if (showMessage) {
            showToast(body.getAttribute('data-tour-done') || '');
        }
        markTourComplete('complete');
    }

    if (tourRoot && popover) {
        if (nextButton) {
            nextButton.addEventListener('click', function () {
                tourIndex++;
                renderTourStep();
            });
        }

        if (prevButton) {
            prevButton.addEventListener('click', function () {
                tourIndex = Math.max(0, tourIndex - 1);
                renderTourStep();
            });
        }

        if (skipButton) {
            skipButton.addEventListener('click', function () {
                finishTour(false);
            });
        }

        document.addEventListener('keydown', function (event) {
            if (tourRoot.hidden) {
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                finishTour(false);
                return;
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                tourIndex++;
                renderTourStep();
                return;
            }

            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                tourIndex = Math.max(0, tourIndex - 1);
                renderTourStep();
                return;
            }

            if (event.key !== 'Tab') {
                return;
            }

            var focusable = popover.querySelectorAll('button:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])');
            if (focusable.length === 0) {
                return;
            }

            var first = focusable[0];
            var last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });

        window.addEventListener('resize', function () {
            if (!tourRoot.hidden && tourSteps[tourIndex]) {
                positionTour(tourSteps[tourIndex]);
            }
        });
    }

    document.querySelectorAll('[data-tour-help]').forEach(function (button) {
        button.addEventListener('click', function () {
            var target = button.getAttribute('data-tour-target') || body.getAttribute('data-tour-page') || 'dashboard';

            if (target === body.getAttribute('data-tour-page') && openTour(target)) {
                return;
            }

            window.location.href = (body.getAttribute('data-home') || '/') + '?tour=1';
        });
    });

    if (welcome) {
        var startButton = welcome.querySelector('[data-tour-welcome-start]');
        var laterButton = welcome.querySelector('[data-tour-welcome-later]');

        if (startButton) {
            startButton.addEventListener('click', function () {
                welcome.hidden = true;
                openTour(body.getAttribute('data-tour-page') || 'dashboard');
            });
        }

        if (laterButton) {
            laterButton.addEventListener('click', function () {
                welcome.hidden = true;
                markTourComplete('skip');
            });
        }

        if (body.getAttribute('data-tour-forced') === '1') {
            welcome.hidden = true;
            openTour(body.getAttribute('data-tour-page') || 'dashboard');
        } else if (body.getAttribute('data-tour-state') === 'new') {
            welcome.hidden = false;
            startButton.focus({ preventScroll: true });
        }
    }
})();

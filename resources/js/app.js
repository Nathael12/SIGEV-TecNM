const temaOscuro = () => document.documentElement.classList.contains('dark');

window.confirmarAccion = function ({
    titulo,
    texto,
    textoConfirmar = 'Confirmar',
    textoCancelar = 'Cancelar',
    colorConfirmar = '#2563eb',
    icono = 'warning',
}) {
    if (!window.Swal) return Promise.resolve({ isConfirmed: false });

    const oscuro = temaOscuro();

    return window.Swal.fire({
        title: titulo,
        text: texto,
        icon: icono,
        showCancelButton: true,
        confirmButtonText: textoConfirmar,
        cancelButtonText: textoCancelar,
        confirmButtonColor: colorConfirmar,
        cancelButtonColor: oscuro ? '#52525b' : '#6b7280',
        background: oscuro ? '#18181b' : '#ffffff',
        color: oscuro ? '#f4f4f5' : '#27272a',
        reverseButtons: true,
        focusCancel: true,
    });
};

window.mostrarToast = function ({ icono = 'success', titulo, texto }) {
    if (!window.Swal || !titulo) return;

    const oscuro = temaOscuro();

    return window.Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icono,
        title: titulo,
        text: texto || undefined,
        showConfirmButton: false,
        timer: 4500,
        timerProgressBar: true,
        background: oscuro ? '#18181b' : '#ffffff',
        color: oscuro ? '#f4f4f5' : '#27272a',
    });
};

function iniciarEnvio(formulario, boton) {
    if (formulario.dataset.envioIniciado === '1') return;
    formulario.dataset.envioIniciado = '1';

    const texto = formulario.dataset.loadingText || 'Procesando...';
    if (boton) {
        boton.disabled = true;
        boton.setAttribute('aria-busy', 'true');

        const etiqueta = boton.querySelector('[data-submit-label]');
        const indicador = boton.querySelector('[data-submit-spinner]');
        if (etiqueta) etiqueta.textContent = texto;
        else boton.textContent = texto;
        indicador?.classList.remove('hidden');
    }
}

document.addEventListener('submit', async (evento) => {
    const formulario = evento.target;
    if (!(formulario instanceof HTMLFormElement)) return;

    const boton = evento.submitter instanceof HTMLButtonElement
        ? evento.submitter
        : formulario.querySelector('button[type="submit"]');

    if (formulario.dataset.swalConfirm) {
        if (formulario.dataset.swalConfirmed === '1') {
            iniciarEnvio(formulario, boton);
            return;
        }

        evento.preventDefault();
        if (formulario.dataset.swalWaiting === '1') return;
        formulario.dataset.swalWaiting = '1';

        const resultado = await window.confirmarAccion({
            titulo: formulario.dataset.swalTitle || '¿Confirmas esta acción?',
            texto: formulario.dataset.swalText || '',
            textoConfirmar: formulario.dataset.swalConfirmText || 'Confirmar',
            colorConfirmar: formulario.dataset.swalColor || '#2563eb',
            icono: formulario.dataset.swalIcon || 'warning',
        });

        formulario.dataset.swalWaiting = '0';
        if (!resultado.isConfirmed) return;

        formulario.dataset.swalConfirmed = '1';
        if (boton) formulario.requestSubmit(boton);
        else formulario.requestSubmit();
        return;
    }

    if (formulario.dataset.submitLoading === '1') {
        if (formulario.dataset.envioIniciado === '1') {
            evento.preventDefault();
            return;
        }
        iniciarEnvio(formulario, boton);
    }
}, true);

function mostrarMensajesFlash() {
    const mostrados = new Set();

    document.querySelectorAll('[data-swal-flash]').forEach((elemento) => {
        const clave = JSON.stringify([elemento.dataset.icon, elemento.dataset.title, elemento.dataset.text]);
        if (!mostrados.has(clave)) {
            mostrados.add(clave);
            window.mostrarToast({
                icono: elemento.dataset.icon || 'success',
                titulo: elemento.dataset.title,
                texto: elemento.dataset.text,
            });
        }
        elemento.remove();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mostrarMensajesFlash, { once: true });
} else {
    mostrarMensajesFlash();
}
document.addEventListener('livewire:navigated', mostrarMensajesFlash);

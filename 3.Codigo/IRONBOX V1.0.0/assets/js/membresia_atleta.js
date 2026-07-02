(function () {
    'use strict';

    const API_URL = '../controllers/MembresiaController.php';

    const atletaSelect = document.getElementById('idAtleta');
    const tipoMembresia = document.getElementById('tipoMembresia');
    const fechaVencimiento = document.getElementById('fechaVencimiento');
    const precioMembresia = document.getElementById('precioMembresia');
    const estadoMembresia = document.getElementById('estadoMembresia');
    const statusMessage = document.getElementById('statusMessage');
    const pagoForm = document.getElementById('pagoForm');
    const submitButton = document.getElementById('submitButton');
    const refreshButton = document.getElementById('refreshButton');
    const cancelMembershipButton = document.getElementById('cancelMembershipButton');
    const drawer = document.getElementById('formDrawer');
    const scrim = document.getElementById('drawerScrim');
    const registrarPagoButton = document.getElementById('registrarPagoButton');
    const drawerClose = document.getElementById('drawerClose');

    let usarSesionAtleta = false;
    let usuarioActual = null;

    document.addEventListener('DOMContentLoaded', iniciar);
    atletaSelect.addEventListener('change', cargarMiMembresia);
    pagoForm.addEventListener('submit', pagarMembresia);
    refreshButton.addEventListener('click', cargarMiMembresia);
    cancelMembershipButton.addEventListener('click', cancelarMembresia);
    registrarPagoButton.addEventListener('click', abrirDrawer);
    drawerClose.addEventListener('click', cerrarFormulario);
    scrim.addEventListener('click', cerrarFormulario);
    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape' && drawer.classList.contains('open')) {
            cerrarFormulario();
        }
    });

    function abrirDrawer() {
        drawer.classList.add('open');
        scrim.classList.add('open');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.classList.add('no-scroll');
        window.setTimeout(() => document.getElementById('metodoPago').focus(), 60);
    }

    function cerrarDrawer() {
        drawer.classList.remove('open');
        scrim.classList.remove('open');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('no-scroll');
    }

    function cerrarFormulario() {
        pagoForm.reset();
        cerrarDrawer();
    }

    async function iniciar() {
        usuarioActual = await obtenerSesion();
        usarSesionAtleta = usuarioActual && usuarioActual.rol === 'Atleta';

        if (usarSesionAtleta) {
            await cargarAtletas();
            preseleccionarAtletaPorCorreo(usuarioActual.correo);
            atletaSelect.disabled = true;
        } else {
            await cargarAtletas();
            preseleccionarAtletaDesdeUrl();
        }

        await cargarMiMembresia();
    }

    async function cargarAtletas() {
        const respuesta = await solicitar('atletas');
        atletaSelect.innerHTML = '<option value="">Seleccione un atleta</option>';

        respuesta.data.forEach((atleta) => {
            const option = document.createElement('option');
            option.value = atleta.id;
            option.textContent = atleta.nombre;
            option.dataset.correo = atleta.correo;
            atletaSelect.appendChild(option);
        });
    }

    async function cargarMiMembresia() {
        const idAtleta = obtenerAtletaSeleccionado();
        if (!usarSesionAtleta && !idAtleta) {
            renderizarMembresia(null);
            return;
        }

        try {
            const respuesta = await solicitar('miMembresia', idAtleta ? { idAtleta } : {});
            renderizarMembresia(respuesta.data);
        } catch (error) {
            mostrarEstado(error.message, 'error');
        }
    }

    async function pagarMembresia(evento) {
        evento.preventDefault();

        const idAtleta = obtenerAtletaSeleccionado();
        if (!usarSesionAtleta && !idAtleta) {
            mostrarEstado('Seleccione un atleta antes de pagar.', 'error');
            return;
        }

        try {
            submitButton.disabled = true;
            await enviar('pagarMembresia', {
                ...(idAtleta ? { idAtleta } : {}),
                metodoPago: document.getElementById('metodoPago').value,
                fechaPago: obtenerFechaActual(),
            });
            pagoForm.reset();
            mostrarEstado('Pago simulado registrado correctamente.', 'ok');
            cerrarDrawer();
            await cargarMiMembresia();
        } catch (error) {
            mostrarEstado(error.message, 'error');
        } finally {
            submitButton.disabled = false;
        }
    }

    async function cancelarMembresia() {
        const idAtleta = obtenerAtletaSeleccionado();
        if (!usarSesionAtleta && !idAtleta) {
            mostrarEstado('Seleccione un atleta antes de cancelar.', 'error');
            return;
        }

        if (!window.confirm('Desea cancelar su membresia?')) {
            return;
        }

        try {
            cancelMembershipButton.disabled = true;
            await enviar('cancelarMembresia', idAtleta ? { idAtleta } : {});
            mostrarEstado('Membresia cancelada correctamente.', 'ok');
            await cargarMiMembresia();
        } catch (error) {
            mostrarEstado(error.message, 'error');
        } finally {
            cancelMembershipButton.disabled = false;
        }
    }

    function renderizarMembresia(membresia) {
        if (!membresia) {
            tipoMembresia.textContent = '-';
            fechaVencimiento.textContent = '-';
            precioMembresia.textContent = '-';
            estadoMembresia.textContent = 'Sin membresia';
            estadoMembresia.className = 'tag Pendiente';
            return;
        }

        tipoMembresia.textContent = membresia.tipo;
        fechaVencimiento.textContent = formatearFecha(membresia.fechaVencimiento);
        precioMembresia.textContent = formatearMoneda(membresia.precio);
        estadoMembresia.textContent = membresia.estado;
        estadoMembresia.className = `tag ${membresia.estado}`;
    }

    async function solicitar(accion, parametros) {
        const query = new URLSearchParams({ action: accion, ...(parametros || {}) });
        const respuesta = await fetch(`${API_URL}?${query.toString()}`);
        return procesarRespuesta(respuesta);
    }

    async function obtenerSesion() {
        const respuesta = await fetch('../controllers/AuthController.php?action=me');
        if (!respuesta.ok) {
            return null;
        }

        const cuerpo = await respuesta.json();
        return cuerpo.success ? cuerpo.data : null;
    }

    async function enviar(accion, datos) {
        const respuesta = await fetch(`${API_URL}?action=${encodeURIComponent(accion)}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos),
        });

        return procesarRespuesta(respuesta);
    }

    async function procesarRespuesta(respuesta) {
        const cuerpo = await respuesta.json();
        if (!respuesta.ok || cuerpo.success === false) {
            throw new Error(cuerpo.message || 'No se pudo completar la operacion.');
        }

        return cuerpo;
    }

    function preseleccionarAtletaDesdeUrl() {
        const parametros = new URLSearchParams(window.location.search);
        const idAtleta = parametros.get('idAtleta') || parametros.get('id_atleta');

        if (idAtleta) {
            atletaSelect.value = idAtleta;
        }
    }

    function preseleccionarAtletaPorCorreo(correo) {
        if (!correo) {
            return;
        }

        const buscado = correo.trim().toLowerCase();
        const option = Array.from(atletaSelect.options).find((item) => {
            return (item.dataset.correo || '').toLowerCase() === buscado;
        });

        if (option) {
            atletaSelect.value = option.value;
        }
    }

    function obtenerAtletaSeleccionado() {
        return Number(document.getElementById('idAtleta').value || 0);
    }

    function obtenerFechaActual() {
        const hoy = new Date();
        const year = hoy.getFullYear();
        const month = String(hoy.getMonth() + 1).padStart(2, '0');
        const day = String(hoy.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function formatearFecha(valor) {
        const partes = valor.split('-');
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }

    function formatearMoneda(valor) {
        return `$${Number(valor).toFixed(2)}`;
    }

    function mostrarEstado(mensaje, tipo) {
        statusMessage.textContent = mensaje;
        statusMessage.className = `status show ${tipo}`;
    }
})();

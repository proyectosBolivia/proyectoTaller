function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function verSolicitud(solicitud) {
    const detalles = `
        <div class="details-grid">
            <div class="detail-item">
                <strong>ID:</strong> #${solicitud.id}
            </div>
            <div class="detail-item">
                <strong>Cliente:</strong> ${solicitud.cliente_nombre}
            </div>
            <div class="detail-item">
                <strong>Servicio:</strong> ${solicitud.servicio_nombre}
            </div>
            <div class="detail-item">
                <strong>Tipo de Vehículo:</strong> ${solicitud.tipo_vehiculo}
            </div>
            <div class="detail-item">
                <strong>Marca:</strong> ${solicitud.marca_vehiculo}
            </div>
            <div class="detail-item">
                <strong>Modelo:</strong> ${solicitud.modelo_vehiculo}
            </div>
            <div class="detail-item">
                <strong>Año:</strong> ${solicitud.año_vehiculo}
            </div>
            <div class="detail-item">
                <strong>Placa:</strong> ${solicitud.placa_vehiculo}
            </div>
            <div class="detail-item full-width">
                <strong>Descripción del Problema:</strong><br>
                ${solicitud.descripcion_problema || 'No especificada'}
            </div>
            <div class="detail-item">
                <strong>Estado:</strong> 
                <span class="badge badge-${solicitud.estado}">${solicitud.estado.replace('_', ' ')}</span>
            </div>
            <div class="detail-item">
                <strong>Fecha de Solicitud:</strong> ${new Date(solicitud.fecha_solicitud).toLocaleString()}
            </div>
        </div>
    `;

    document.getElementById('detallesSolicitud').innerHTML = detalles;
    document.getElementById('modalVerSolicitud').style.display = 'block';
}

async function asignarSolicitud(solicitudId) {
    document.getElementById('solicitud_id_asignar').value = solicitudId;

    try {
        const response = await fetch('../api/trabajadores.php?action=listar');
        const trabajadores = await response.json();

        const select = document.getElementById('trabajador_id');
        select.innerHTML = '<option value="">Seleccione un trabajador</option>';

        trabajadores.forEach(trabajador => {
            if (trabajador.activo == 1) {
                const option = document.createElement('option');
                option.value = trabajador.id;
                option.textContent = trabajador.nombre;
                select.appendChild(option);
            }
        });

        document.getElementById('modalAsignar').style.display = 'block';
    } catch (error) {
        alert('Error al cargar trabajadores: ' + error.message);
    }
}

async function guardarAsignacion(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    formData.append('action', 'crear');

    try {
        const response = await fetch('../api/asignaciones.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            closeModal('modalAsignar');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error al procesar la solicitud: ' + error.message);
    }
}

function cambiarEstado(solicitudId, estadoActual) {
    document.getElementById('solicitud_id_estado').value = solicitudId;
    document.getElementById('nuevo_estado').value = estadoActual;
    document.getElementById('modalCambiarEstado').style.display = 'block';
}

async function guardarCambioEstado(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    formData.append('action', 'cambiar_estado');

    try {
        const response = await fetch('../api/solicitudes.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            closeModal('modalCambiarEstado');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error al procesar la solicitud: ' + error.message);
    }
}

window.onclick = function (event) {
    const modals = document.getElementsByClassName('modal');
    for (let modal of modals) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
}
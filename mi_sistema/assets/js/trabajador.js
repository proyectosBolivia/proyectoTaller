function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

async function cambiarEstado(asignacionId, nuevoEstado) {
    if (!confirm('¿Desea cambiar el estado de esta asignación?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'actualizar_estado');
    formData.append('asignacion_id', asignacionId);
    formData.append('estado', nuevoEstado);

    try {
        const response = await fetch('../api/asignaciones.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error al procesar la solicitud: ' + error.message);
    }
}

function completarTrabajo(asignacionId) {
    document.getElementById('asignacion_id_reporte').value = asignacionId;
    document.getElementById('modalReporte').style.display = 'block';
}

async function guardarReporte(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    formData.append('action', 'crear');

    try {
        const response = await fetch('../api/reportes_trabajo.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            closeModal('modalReporte');
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
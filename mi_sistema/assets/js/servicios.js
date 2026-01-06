function openModalServicio() {
    document.getElementById('modalServicio').style.display = 'block';
}

function closeModalServicio() {
    document.getElementById('modalServicio').style.display = 'none';
    document.getElementById('formServicio').reset();
    document.getElementById('servicio_id').value = '';
    document.getElementById('modalTituloServicio').textContent = 'Nuevo Servicio';
}

function editarServicio(servicio) {
    document.getElementById('servicio_id').value = servicio.id;
    document.getElementById('nombre').value = servicio.nombre;
    document.getElementById('descripcion').value = servicio.descripcion || '';
    document.getElementById('precio_estimado').value = servicio.precio_estimado;
    document.getElementById('duracion_estimada').value = servicio.duracion_estimada;
    document.getElementById('modalTituloServicio').textContent = 'Editar Servicio';
    openModalServicio();
}

async function guardarServicio(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const id = formData.get('id');
    const action = id ? 'actualizar' : 'crear';

    formData.append('action', action);

    try {
        const response = await fetch('../api/servicios.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            closeModalServicio();
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error al procesar la solicitud: ' + error.message);
    }
}

async function eliminarServicio(id) {
    if (!confirm('¿Está seguro de eliminar este servicio?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'eliminar');
    formData.append('id', id);

    try {
        const response = await fetch('../api/servicios.php', {
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

window.onclick = function (event) {
    const modal = document.getElementById('modalServicio');
    if (event.target === modal) {
        closeModalServicio();
    }
}
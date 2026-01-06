function openModalVehiculo() {
    document.getElementById('modalVehiculo').style.display = 'block';
}

function closeModalVehiculo() {
    document.getElementById('modalVehiculo').style.display = 'none';
    document.getElementById('formVehiculo').reset();
    document.getElementById('vehiculo_id').value = '';
    document.getElementById('modalTituloVehiculo').textContent = 'Nuevo Tipo de Vehículo';
}

function editarVehiculo(vehiculo) {
    document.getElementById('vehiculo_id').value = vehiculo.id;
    document.getElementById('nombre').value = vehiculo.nombre;
    document.getElementById('descripcion').value = vehiculo.descripcion || '';
    document.getElementById('modalTituloVehiculo').textContent = 'Editar Tipo de Vehículo';
    openModalVehiculo();
}

async function guardarVehiculo(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const id = formData.get('id');
    const action = id ? 'actualizar' : 'crear';

    formData.append('action', action);

    try {
        const response = await fetch('../api/vehiculos.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            closeModalVehiculo();
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error al procesar la solicitud: ' + error.message);
    }
}

async function eliminarVehiculo(id) {
    if (!confirm('¿Está seguro de eliminar este tipo de vehículo?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'eliminar');
    formData.append('id', id);

    try {
        const response = await fetch('../api/vehiculos.php', {
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
    const modal = document.getElementById('modalVehiculo');
    if (event.target === modal) {
        closeModalVehiculo();
    }
}
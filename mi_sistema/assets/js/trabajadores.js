function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    if (modalId === 'modalTrabajador') {
        document.getElementById('formTrabajador').reset();
        document.getElementById('trabajador_id').value = '';
        document.getElementById('modalTitulo').textContent = 'Nuevo Trabajador';
        document.getElementById('password').required = true;
        document.getElementById('passRequired').style.display = 'inline';
    }
}

function editarTrabajador(trabajador) {
    document.getElementById('trabajador_id').value = trabajador.id;
    document.getElementById('nombre').value = trabajador.nombre;
    document.getElementById('email').value = trabajador.email;
    document.getElementById('telefono').value = trabajador.telefono;
    document.getElementById('activo').value = trabajador.activo;
    document.getElementById('password').required = false;
    document.getElementById('passRequired').style.display = 'none';
    document.getElementById('modalTitulo').textContent = 'Editar Trabajador';
    openModal('modalTrabajador');
}

async function guardarTrabajador(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const id = formData.get('id');
    const action = id ? 'actualizar' : 'crear';

    formData.append('action', action);

    try {
        const response = await fetch('../api/trabajadores.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            closeModal('modalTrabajador');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error al procesar la solicitud: ' + error.message);
    }
}

async function eliminarTrabajador(id) {
    if (!confirm('¿Está seguro de eliminar este trabajador?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'eliminar');
    formData.append('id', id);

    try {
        const response = await fetch('../api/trabajadores.php', {
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

// Cerrar modal al hacer clic fuera
window.onclick = function (event) {
    const modals = document.getElementsByClassName('modal');
    for (let modal of modals) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
}
// Hash password using SHA-512
async function hashPassword(password) {
    const encoder = new TextEncoder();
    const data = encoder.encode(password);
    const hashBuffer = await crypto.subtle.digest('SHA-512', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    return hashHex;
}

async function login() {
    console.log("Intentando iniciar sesión...");

    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    // Hash the password before sending
    const hashedPassword = await hashPassword(passwordInput.value);

    // Crear FormData manualmente para tener control
    const formData = new FormData();
    formData.append('action', 'login'); // Importante para que el backend sepa qué hacer
    formData.append('email', emailInput.value);
    formData.append('password', hashedPassword);

    fetch('../Backend/index.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json()) // Esperamos JSON del backend
        .then(data => {
            console.log("Respuesta:", data);
            if (data.success) {
                // Save user to localStorage
                localStorage.setItem('nombreUsuario', emailInput.value);

                // Recargar la página. Como ahora estamos logueados (cookie de sesión),
                // el GET del backend nos servirá el index.html (Dashboard)
                navigateTo('dashboard');
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Ocurrió un error de conexión");
        });
}

async function register() {
    console.log("Intentando registrar...");
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    // Hash the password before sending
    const hashedPassword = await hashPassword(passwordInput.value);

    // Crear FormData manualmente para tener control
    const formData = new FormData();
    formData.append('action', 'register');
    formData.append('email', emailInput.value);
    formData.append('password', hashedPassword);

    fetch('../Backend/index.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json()) // Esperamos JSON del backend
        .then(data => {
            console.log("Respuesta:", data);
            if (data.success) {
                alert("Usuario registrado correctamente");
                navigateTo('login');
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Ocurrió un error de conexión");
        });
}

function logout() {
    console.log("Cerrando sesión...");

    const formData = new FormData();
    formData.append('action', 'logout');

    fetch('../Backend/index.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                localStorage.removeItem('nombreUsuario');
                navigateTo('login');
            } else {
                alert("Error: " + data.message);
            }
        });
}

function navigateTo(page) {
    const params = new URLSearchParams();
    params.append('action', 'navigate');
    params.append('page', page);

    fetch('../Backend/index.php?' + params.toString(), {
        method: 'GET'
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'ok') {
                window.location.href = "../Frontend/" + data.page;
            } else {
                alert("Error: " + data.message);
            }
        });
}

function getResolvedTicketCount() {
    fetch('../Backend/index.php?action=getResolvedTicketCount', {
        method: 'GET'
    })
        .then(response => response.json())
        .then(data => {
            console.log("Resolved ticket count data:", data);
            if (data.status === 'ok') {
                document.getElementById('resolvedTicketCount').textContent = data.count;
            } else {
                console.error("Error fetching resolved ticket count");
            }
        })
        .catch(error => console.error('Error:', error));
}

function getTicketCount() {
    fetch('../Backend/index.php?action=getTicketCount', {
        method: 'GET'
    })
        .then(response => response.json())
        .then(data => {
            console.log("Ticket count data:", data);
            if (data.status === 'ok') {
                document.getElementById('ticketCount').textContent = data.count;
            } else {
                console.error("Error fetching ticket count");
            }
        })
        .catch(error => console.error('Error:', error));
}

function getEsperandoLlamadaCount() {
    fetch('../Backend/index.php?action=getEsperandoLlamadaCount', {
        method: 'GET'
    })
        .then(response => response.json())
        .then(data => {
            console.log("Esperando Llamada count data:", data);
            if (data.status === 'ok') {
                document.getElementById('esperandoLlamadaCount').textContent = data.count;
            } else {
                console.error("Error fetching esperando llamada count");
            }
        })
        .catch(error => console.error('Error:', error));
}

function getCreatedTodayCount() {
    fetch('../Backend/index.php?action=getCreatedTodayCount', {
        method: 'GET'
    })
        .then(response => response.json())
        .then(data => {
            console.log("Created Today count data:", data);
            if (data.status === 'ok') {
                document.getElementById('createdTodayCount').textContent = data.count;
            } else {
                console.error("Error fetching created today count");
            }
        })
        .catch(error => console.error('Error:', error));
}


document.addEventListener('DOMContentLoaded', () => {
    // Mostrar nombre de usuario
    const nombreUsuarioElement = document.getElementById('nombreUsuario');
    if (nombreUsuarioElement) {
        const fullUser = localStorage.getItem('nombreUsuario') || '';
        nombreUsuarioElement.textContent = fullUser.split('@')[0];
    }

    // Si estamos en el dashboard (existe #userCount), cargar datos
    const ticketCountElement = document.getElementById('ticketCount');
    if (ticketCountElement) {
        getTicketCount();
    }

    const esperandoLlamadaCountElement = document.getElementById('esperandoLlamadaCount');
    if (esperandoLlamadaCountElement) {
        getEsperandoLlamadaCount();
    }

    const createdTodayCountElement = document.getElementById('createdTodayCount');
    if (createdTodayCountElement) {
        getCreatedTodayCount();
    }

    // Si existe la tabla de tickets, inicializar dashboard
    const ticketsTable = document.getElementById('ticketsTable');
    if (ticketsTable) {
        initDashboard();
    }
});

// --- TICKET DASHBOARD LOGIC ---
let currentPage = 1;
let currentLimit = 10;
let currentSearch = '';
let currentFilters = {
    estado: ''
};

function initDashboard() {
    // Event Listeners
    document.getElementById('searchInput').addEventListener('input', (e) => {
        currentSearch = e.target.value;
        currentPage = 1;
        loadTickets();
    });

    document.getElementById('filterEstado').addEventListener('change', (e) => {
        currentFilters.estado = e.target.value;
        currentPage = 1;
        loadTickets();
    });

    document.getElementById('ticketForm').addEventListener('submit', handleTicketSubmit);

    // Form Dropdown Logic
    setupTicketFormLogic();

    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeTicketModal();
        }
    });

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        const modal = document.getElementById('ticketModal');
        if (e.target === modal) {
            closeTicketModal();
        }
    });

    loadTickets();
}

function setupTicketFormLogic() {
    const descSelect = document.getElementById('ticketDescripcion');
    const estadoSelect = document.getElementById('ticketEstado');

    // Logic: Alta/Cambio usuario -> Carga/Faltantes = "-"
    descSelect.addEventListener('change', (e) => {
        const val = e.target.value;
        if (val.includes('Alta nuevo usuario') || val.includes('Cambio de usuario')) {
            document.getElementById('ticketCarga').value = '-';
            document.getElementById('ticketFaltantes').value = '-';
        }
    });

    // Logic: Resuelto -> Carga = "DATOS CARGADOS", Faltantes = "-"
    estadoSelect.addEventListener('change', (e) => {
        if (e.target.value === 'RESUELTO') {
            document.getElementById('ticketCarga').value = 'DATOS CARGADOS';
            document.getElementById('ticketFaltantes').value = '-';
        }
    });
}

function loadTickets() {
    const params = new URLSearchParams();
    params.append('action', 'get_tickets');
    params.append('page', currentPage);
    params.append('limit', currentLimit);
    if (currentSearch) params.append('search', currentSearch);
    if (currentFilters.estado) params.append('filter_estado', currentFilters.estado);

    fetch(`../Backend/index.php?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            renderTickets(data.data);
            renderPagination(data);
        })
        .catch(err => console.error("Error loading tickets:", err));
}

function renderTickets(tickets) {
    const tbody = document.querySelector('#ticketsTable tbody');
    tbody.innerHTML = '';

    if (!tickets || tickets.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No se encontraron tickets</td></tr>';
        return;
    }

    tickets.forEach(ticket => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${ticket.enlace}</td>
            <td>${ticket.descripcion}</td>
            <td><span class="badge ${ticket.estado ? ticket.estado.toLowerCase().replace(/ /g, '-') : ''}">${ticket.estado}</span></td>
            <td>${ticket.fecha}</td>
            <td>
                <button onclick="editTicket('${ticket.enlace}')">Editar</button>
                <button onclick="deleteTicket('${ticket.enlace}')" class="btn-delete-action">Eliminar</button>
            </td>
        `;
        // Store data for edit (easy way)
        tr.dataset.ticket = JSON.stringify(ticket);
        tbody.appendChild(tr);
    });
}

function renderPagination(meta) {
    const container = document.getElementById('paginationControls');
    container.innerHTML = '';

    const totalPages = meta.totalPages;

    if (totalPages <= 1) return;

    // Prev
    const prevBtn = document.createElement('button');
    prevBtn.innerText = 'Anterior';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; loadTickets(); } };
    container.appendChild(prevBtn);

    // Page info
    const info = document.createElement('span');
    info.innerText = ` Página ${meta.page} de ${totalPages} `;
    container.appendChild(info);

    // Next
    const nextBtn = document.createElement('button');
    nextBtn.innerText = 'Siguiente';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; loadTickets(); } };
    container.appendChild(nextBtn);
}

// Modal Logic
function openTicketModal(ticket = null) {
    const modal = document.getElementById('ticketModal');
    const form = document.getElementById('ticketForm');
    const title = document.getElementById('modalTitle');

    if (ticket) {
        title.innerText = 'Editar Ticket';
        document.getElementById('ticketId').value = ticket.enlace;
        document.getElementById('ticketEnlace').value = ticket.enlace;
        document.getElementById('ticketEnlace').readOnly = true; // No cambiar ID
        document.getElementById('ticketDescripcion').value = ticket.descripcion || '';
        document.getElementById('ticketEstado').value = (ticket.estado || 'RESUELTO').toUpperCase();
        document.getElementById('ticketCarga').value = (ticket.carga_datos || '').toUpperCase();
        document.getElementById('ticketDemoras').value = (ticket.demoras || '').toUpperCase();
        document.getElementById('ticketFaltantes').value = (ticket.datos_faltantes || '').toUpperCase();

        // Convert DD/MM/YYYY to YYYY-MM-DD for input[type="date"]
        let isoDate = ticket.fecha;
        if (ticket.fecha && ticket.fecha.includes('/')) {
            const parts = ticket.fecha.split('/');
            if (parts.length === 3) isoDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
        }
        document.getElementById('ticketFecha').value = isoDate;
    } else {
        title.innerText = 'Nuevo Ticket';
        form.reset();
        document.getElementById('ticketId').value = '';
        document.getElementById('ticketEnlace').readOnly = false;
        document.getElementById('ticketFecha').value = new Date().toISOString().split('T')[0];

        // Default values requested by user
        document.getElementById('ticketEstado').value = 'RESUELTO';
        document.getElementById('ticketCarga').value = 'DATOS CARGADOS';
        document.getElementById('ticketDemoras').value = 'NINGUNA';
        document.getElementById('ticketFaltantes').value = '-';
    }

    modal.style.display = 'block';
}

function closeTicketModal() {
    document.getElementById('ticketModal').style.display = 'none';
}

function editTicket(id) {
    // Find ticket data from DOM or refetch. 
    // Here we use the data attribute we stored
    const rows = Array.from(document.querySelectorAll('#ticketsTable tbody tr'));
    const row = rows.find(r => {
        const t = JSON.parse(r.dataset.ticket || '{}');
        return t.enlace === id;
    });

    if (row) {
        const ticket = JSON.parse(row.dataset.ticket);
        openTicketModal(ticket);
    }
}

async function deleteTicket(id) {
    if (!confirm('¿Seguro que deseas eliminar este ticket?')) return;

    const formData = new FormData();
    formData.append('action', 'delete_ticket'); // Se debe manejar JSON en backend tambien
    // Our backend expects JSON body for tickets, but let's see. 
    // Backend/index.php handles JSON input for 'delete_ticket', so we should send JSON.

    try {
        const res = await fetch('../Backend/index.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'delete_ticket', id: id }),
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            loadTickets();
            showToast('Ticket eliminado correctamente', 'success');
        } else {
            showToast("Error: " + (data.message || data.error), 'error');
        }
    } catch (e) {
        console.error(e);
        showToast("Error de conexión", 'error');
    }
}

async function handleTicketSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const isEdit = document.getElementById('ticketId').value !== '';

    const data = {
        action: isEdit ? 'update_ticket' : 'create_ticket',
        enlace: form.enlace_input.value,
        id: isEdit ? document.getElementById('ticketId').value : undefined, // Para update buscamos por este ID
        descripcion: form.descripcion.value,
        estado: form.estado.value,
        carga_datos: form.carga_datos.value,
        demoras: form.demoras.value,
        datos_faltantes: form.datos_faltantes.value,
        fecha: formatDateToDDMMYYYY(form.fecha.value)
    };

    try {
        const res = await fetch('../Backend/index.php', {
            method: 'POST',
            body: JSON.stringify(data),
            headers: { 'Content-Type': 'application/json' }
        });
        const result = await res.json();

        if (result.success) {
            closeTicketModal();
            loadTickets();
            showToast(isEdit ? 'Ticket actualizado correctamente' : 'Ticket creado correctamente', 'success');
        } else {
            showToast("Error: " + (result.message || result.error), 'error');
        }
    } catch (err) {
        console.error(err);
        showToast("Error al guardar", 'error');
    }
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerText = message;

    // Click to dismiss
    toast.onclick = () => {
        toast.classList.add('fadeOut');
        setTimeout(() => toast.remove(), 300);
    };

    container.appendChild(toast);

    // Auto remove after 3s
    setTimeout(() => {
        if (toast.parentNode) {
            toast.classList.add('fadeOut');
            setTimeout(() => toast.remove(), 300);
        }
    }, 3000);
}

function formatDateToDDMMYYYY(dateString) {
    if (!dateString) return '';
    // Input date is YYYY-MM-DD
    const parts = dateString.split('-');
    if (parts.length !== 3) return dateString; // fallback
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

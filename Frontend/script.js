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

function getUserCount() {
    fetch('../Backend/index.php?action=getUserCount', {
        method: 'GET'
    })
        .then(response => response.json())
        .then(data => {
            console.log("User count data:", data);
            if (data.status === 'ok') {
                document.getElementById('userCount').textContent = data.count;
            } else {
                console.error("Error fetching user count");
            }
        })
        .catch(error => console.error('Error:', error));
}


document.addEventListener('DOMContentLoaded', () => {
    // Si estamos en el dashboard (existe #userCount), cargar datos
    const userCountElement = document.getElementById('userCount');
    if (userCountElement) {
        getUserCount();
    }
});
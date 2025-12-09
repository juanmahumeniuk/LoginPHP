function login() {
    console.log("Intentando iniciar sesión...");

    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    // Crear FormData manualmente para tener control
    const formData = new FormData();
    formData.append('action', 'login'); // Importante para que el backend sepa qué hacer
    formData.append('email', emailInput.value);
    formData.append('password', passwordInput.value);

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
                window.location.reload();
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
                window.location.reload(); // Recargar para que el backend nos sirva el login.html
                window.location.href = '../Backend/login.php';
            }
        });
}

// Vincular el botón del formulario al click
document.addEventListener('DOMContentLoaded', () => {
    // Si estamos en la página de login, interceptar el botón
    const loginButton = document.querySelector('.login-card .btn');
    if (loginButton) {
        loginButton.addEventListener('click', (e) => {
            e.preventDefault(); // Evitar submit tradicional
            login();
        });
    }
});
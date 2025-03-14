function showErrorDialog(message) {
    // Crear fondo oscuro semitransparente
    const overlay = document.createElement("div");
    overlay.style.position = "fixed";
    overlay.style.top = "0";
    overlay.style.left = "0";
    overlay.style.width = "100%";
    overlay.style.height = "100%";
    overlay.style.background = "rgba(0, 0, 0, 0.5)";
    overlay.style.display = "flex";
    overlay.style.justifyContent = "center";
    overlay.style.alignItems = "center";
    overlay.style.zIndex = "1000";

    // Crear diálogo
    const dialog = document.createElement("div");
    dialog.style.background = "#fff";
    dialog.style.padding = "20px";
    dialog.style.borderRadius = "10px";
    dialog.style.boxShadow = "0 5px 15px rgba(0, 0, 0, 0.3)";
    dialog.style.textAlign = "center";
    dialog.style.fontFamily = "Arial, sans-serif";
    dialog.style.width = "300px";
    dialog.style.animation = "fadeIn 0.3s ease-out";

    // Crear mensaje de error
    const messageText = document.createElement("p");
    messageText.textContent = message;
    messageText.style.color = "#d9534f";
    messageText.style.fontSize = "16px";
    messageText.style.marginBottom = "15px";

    // Crear botón de cierre
    const closeButton = document.createElement("button");
    closeButton.innerHTML = "Aceptar";
    closeButton.style.background = "#d9534f";
    closeButton.style.color = "white";
    closeButton.style.border = "none";
    closeButton.style.padding = "10px 20px";
    closeButton.style.cursor = "pointer";
    closeButton.style.borderRadius = "5px";
    closeButton.style.fontSize = "14px";
    closeButton.style.transition = "0.3s";

    closeButton.onmouseover = function () {
        closeButton.style.background = "#c9302c";
    };

    closeButton.onmouseout = function () {
        closeButton.style.background = "#d9534f";
    };

    closeButton.onclick = function () {
        document.body.removeChild(overlay);
        window.location = "login.php";
    };

    // Agregar elementos al diálogo
    dialog.appendChild(messageText);
    dialog.appendChild(closeButton);
    overlay.appendChild(dialog);
    document.body.appendChild(overlay);
}

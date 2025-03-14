// confirmDialog.js
function showConfirmDialog(message, onConfirm, onCancel) {
    // Crear el contenedor del diálogo
    const dialog = document.createElement("div");
    dialog.style.position = "fixed";
    dialog.style.top = "50%";
    dialog.style.left = "50%";
    dialog.style.transform = "translate(-50%, -50%)";
    dialog.style.background = "#ffffff";
    dialog.style.padding = "20px";
    dialog.style.borderRadius = "8px";
    dialog.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
    dialog.style.textAlign = "center";
    dialog.style.fontFamily = "Arial, sans-serif";
    dialog.style.zIndex = "1000";

    // Crear el mensaje del diálogo
    const messageText = document.createElement("p");
    messageText.textContent = message;
    messageText.style.color = "#333";
    messageText.style.marginBottom = "20px";

    // Crear el botón "Sí"
    const yesButton = document.createElement("button");
    yesButton.textContent = "Sí";
    yesButton.style.background = "#007bff";
    yesButton.style.color = "white";
    yesButton.style.border = "none";
    yesButton.style.padding = "10px 20px";
    yesButton.style.cursor = "pointer";
    yesButton.style.borderRadius = "5px";
    yesButton.style.marginRight = "10px";

    // Crear el botón "No"
    const noButton = document.createElement("button");
    noButton.textContent = "No";
    noButton.style.background = "#dc3545";
    noButton.style.color = "white";
    noButton.style.border = "none";
    noButton.style.padding = "10px 20px";
    noButton.style.cursor = "pointer";
    noButton.style.borderRadius = "5px";

    // Añadir el mensaje y los botones al diálogo
    dialog.appendChild(messageText);
    dialog.appendChild(yesButton);
    dialog.appendChild(noButton);

    // Añadir el diálogo al cuerpo del documento
    document.body.appendChild(dialog);

    // Manejar el clic en "Sí"
    yesButton.onclick = function () {
        document.body.removeChild(dialog); // Eliminar el diálogo
        onConfirm(); // Ejecutar la función de confirmación
    };

    // Manejar el clic en "No"
    noButton.onclick = function () {
        document.body.removeChild(dialog); // Eliminar el diálogo
        onCancel(); // Ejecutar la función de cancelación
    };
}
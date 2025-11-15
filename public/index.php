<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador de Compra API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        input[type="number"],
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        #simpleResponse {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            white-space: pre-wrap;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            color: #333;
            font-size: 0.9em;
            display: none;
        }

        .success {
            border-left: 5px solid #28a745;
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            border-left: 5px solid #dc3545;
            background-color: #f8d7da;
            color: #721c24;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-content {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 500px;
            transform: translateY(-50px);
            transition: transform 0.3s ease;
            animation: fadeIn 0.3s forwards;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal-overlay.active .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
            padding-left: 5px;
            padding-right: 5px;
        }

        .modal-header h3 {
            margin: 0;
            color: #007bff;
            font-size: 1.5em;
            max-width: 90%;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .close-button {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #aaa;
            transition: color 0.2s;
        }

        .close-button:hover {
            color: #333;
        }

        .modal-body {
            line-height: 1.6;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dotted #eee;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: bold;
            color: #555;
        }

        .detail-value {
            color: #333;
            font-weight: 500;
        }

        .success-message {
            color: #28a745;
            text-align: center;
            font-size: 1.2em;
            margin-bottom: 15px;
            font-weight: bold;
        }

        /* Estilo para el mensaje de éxito (se reutiliza como mensaje general) */
        .success-message {
            color: #28a745;
            text-align: center;
            font-size: 1.2em;
            margin-bottom: 15px;
            font-weight: bold;
        }

        /* Nuevo estilo para el título/cabecera del modal de error */
        .error-modal-content .modal-header h3 {
            color: #dc3545 !important;
            /* Rojo para el título de error */
        }

        /* Nuevo estilo para el mensaje principal de error en el modal */
        .error-modal-content .success-message {
            color: #721c24 !important;
            /* Rojo oscuro para el mensaje */
            /* Se podría añadir un fondo sutil si se desea: background-color: #f8d7da; */
        }

        /* Nuevo estilo para el contenedor del modal de error (fondo o borde sutil) */
        .error-modal-content {
            border: 2px solid #dc3545;
            /* Borde rojo */
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>🛍️ Realizar una Compra</h2>
        <form id="compraForm">
            <div>
                <label for="cuenta_numero">Tarjeta número:</label>
                <input type="text" id="cuenta_numero" name="cuenta_numero" required>
            </div>
            <div>
                <label for="nombre">Nombre del Producto:</label>
                <input type="text" id="nombre" name="nombre" required value="Smartphone X">
            </div>
            <div>
                <label for="precio">Precio:</label>
                <input type="number" id="precio" name="precio" required min="0.01" step="0.01" value="499.99">
            </div>
            <button type="submit">Procesar Compra</button>
        </form>

        <div class="modal-overlay" id="compraModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modalTitle">Detalle de la Transacción</h3>
                    <button class="close-button" onclick="closeModal()">X</button>
                </div>
                <div class="modal-body">
                    <p class="success-message" id="modalMessage"></p>

                    <h4>Información de la Compra:</h4>
                    <div id="compraDetails">
                    </div>

                    <p style="margin-top: 20px; text-align: center; font-style: italic; color: #777;">
                        Transacción procesada correctamente.
                    </p>
                </div>
            </div>
        </div>
        <div id="simpleResponse" style="margin-top: 20px; padding: 15px; border-radius: 4px; white-space: pre-wrap; display:none;"></div>
    </div>

<script>
        const modal = document.getElementById('compraModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const compraDetails = document.getElementById('compraDetails');
        const simpleResponse = document.getElementById('simpleResponse');

        // Función para cerrar el modal
        function closeModal() {
            modal.classList.remove('active');
            // Limpiar los detalles al cerrar
            compraDetails.innerHTML = '';
            modalMessage.textContent = '';
            // Resetear estilos/título del modal
            modalTitle.textContent = 'Detalle de la Transacción';
            // Remover las clases de estilo de error si existen
            modal.querySelector('.modal-content').classList.remove('error-modal-content');
            // Asegurar que el pie de página de éxito esté visible al cerrar
            const successFooter = modal.querySelector('.modal-body p[style*="font-style: italic"]');
            if (successFooter) successFooter.style.display = 'block';
        }

        // Función para abrir el modal (unificada para éxito y error)
        function showModal(isSuccess, title, message, detailsHtml) {
            simpleResponse.style.display = 'none'; // Asegurar que el div simpleResponse esté oculto

            modalTitle.textContent = title;
            modalMessage.textContent = message;
            compraDetails.innerHTML = detailsHtml;

            // Aplicar estilo y visibilidad del pie de página según el resultado
            const successFooter = modal.querySelector('.modal-body p[style*="font-style: italic"]');

            if (!isSuccess) {
                // Estilo de error
                modal.querySelector('.modal-content').classList.add('error-modal-content');
                // Ocultar el pie de página de éxito en caso de error
                if (successFooter) successFooter.style.display = 'none';

            } else {
                // Estilo de éxito (asegurar que no haya estilo de error)
                modal.querySelector('.modal-content').classList.remove('error-modal-content');
                // Mostrar el pie de página de éxito
                if (successFooter) successFooter.style.display = 'block';
            }

            modal.classList.add('active');
        }


        // Evento para cerrar el modal al hacer clic fuera del contenido
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        document.getElementById('compraForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            const form = event.target;
            closeModal(); // Asegurarse de que el modal esté cerrado

            // 1. Recopilar datos del formulario
            const data = {
                cuenta_numero: form.cuenta_numero.value,
                nombre: form.nombre.value,
                precio: form.precio.value
            };

            // **IMPORTANTE**: Reemplaza esta URL con la URL real donde está tu script PHP
            const apiEndpoint = './comprar/';

            try {
                // Mostrar estado de procesamiento (temporalmente en simpleResponse)
                simpleResponse.textContent = 'Procesando compra...';
                simpleResponse.style.display = 'block';
                simpleResponse.className = ''; // Limpiar clases de error/éxito

                const response = await fetch(apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                // 3. Procesar la respuesta
                const result = await response.json();
                simpleResponse.style.display = 'none'; // Ocultar el estado de procesamiento

                if (response.ok && result.status === 'success') {
                    // ** Lógica para el modal de ÉXITO **

                    // 3a. Mapear y formatear los datos del JSON
                    const details = [{
                            label: 'Producto',
                            value: result.metadata.nombre_producto
                        },
                        {
                            label: 'Cuenta ID',
                            value: result.metadata.cuenta_numero
                        },
                        {
                            label: 'Monto Debitado',
                            value: `$ ${parseFloat(result.data.monto_debitado).toFixed(2)}`
                        },
                        {
                            label: 'ID de Compra',
                            value: result.data.id_compra
                        },
                        {
                            label: 'Saldo Anterior',
                            value: `$ ${parseFloat(result.data.saldo_anterior).toFixed(2)}`
                        },
                        {
                            label: 'Saldo Actual',
                            value: `$ ${parseFloat(result.data.saldo_actual).toFixed(2)}`,
                            isHighlight: true
                        }
                    ];

                    // 3b. Construir el HTML de los detalles
                    const detailsHtml = details.map(item => `
                    <div class="detail-row" ${item.isHighlight ? 'style="background-color: #e6f7ff; border-radius: 4px;"' : ''}>
                        <span class="detail-label">${item.label}:</span>
                        <span class="detail-value" ${item.isHighlight ? 'style="font-weight: bold; color: #007bff;"' : ''}>${item.value}</span>
                    </div>
                    `).join('');

                    // 3c. Mostrar el modal de ÉXITO
                    showModal(
                        true, // isSuccess
                        '✅ Transacción Exitosa', // title
                        result.message || 'La compra se procesó correctamente.', // message
                        detailsHtml // detailsHtml
                    );


                } else {
                    // ** Lógica para errores (mostrar en modal con detalles mínimos) **
                    const statusCode = response.status || 500;
                    const userMessage = result.message || 'Error desconocido. Revise los detalles del servidor.';
                    const developerMessage = result.developer_message || 'No hay mensaje detallado del desarrollador.';
                    
                    const errorHeader = `❌ ERROR ${statusCode}: ${userMessage}`;
                    
                    // Solo se muestra el mensaje de error del desarrollador
                    const errorDetailsHtml = `
                        <div class="detail-row" style="background-color: #f8d7da; border-radius: 4px;">
                            <span class="detail-label" style="font-weight: bold;">Mensaje de Error:</span>
                            <span class="detail-value" style="color: #721c24; font-weight: bold;">${developerMessage}</span>
                        </div>
                    `;

                    // Mostrar el modal de ERROR
                    showModal(
                        false, // isSuccess
                        errorHeader, // title
                        'Ha ocurrido un problema al procesar su compra.', // message
                        errorDetailsHtml // detailsHtml
                    );
                }

            } catch (error) {
                // Manejo de errores de red o parsing
                const errorDetailsHtml = `
                    <div class="detail-row">
                        <span class="detail-label">Mensaje:</span>
                        <span class="detail-value" style="color: #721c24; font-weight: bold;">${error.message}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Sugerencia:</span>
                        <span class="detail-value">Verifique la URL del API y la conexión.</span>
                    </div>
                `;
                
                showModal(
                    false, // isSuccess
                    '🚨 Error de Conexión', // title
                    'No se pudo establecer comunicación con el servidor.', // message
                    errorDetailsHtml // detailsHtml
                );
            }
        });
    </script>

</body>

</html>
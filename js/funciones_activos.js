////    Función para guardar un nuevo activo
        function guardarActivo() {
            // 1. Obtener el formulario HTML
            var formElement = document.getElementById('formActivos');
            
            // 2. Crear objeto FormData (Captura automáticamente todos los inputs, selects y archivos)
            var formData = new FormData(formElement);

            // 3. Agregar datos manuales que no estén en inputs o que requieran lógica extra
            formData.append('opcion', 'nuevoActivo'); // Tu identificador para PHP

            // Lógica del Checkbox (FormData solo lo incluye si está checked, aquí forzamos 1 o 0)
            var isChecked = document.getElementById('checkEsAccesorio').checked;
            formData.set('es_accesorio', isChecked ? '1' : '0'); 
            // Nota: Usamos .set() para sobrescribir si el input ya existía en el form

            // 4. Enviar vía AJAX
            $.ajax({
                url: 'acciones_activos.php',
                method: 'POST',
                data: formData,         // Enviamos el objeto FormData directo
                
                // --- ESTAS DOS LÍNEAS SON OBLIGATORIAS PARA ARCHIVOS ---
                processData: false,     // Evita que jQuery transforme la data a string
                contentType: false,     // Evita que jQuery pongan cabeceras incorrectas
                // -------------------------------------------------------
                
                dataType: 'json',
                success: function(data) {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: "¡Guardado!",
                            text: "La actividad se registró con éxito.",
                            icon: "success",
                            confirmButtonText: "Aceptar"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.href = 'verActivos'; // Redirige a la lista de activos
                                formElement.reset(); // Limpia el formulario
                            }
                        });
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: data.message,
                            icon: "error"
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error(textStatus, errorThrown);
                    Swal.fire({
                        title: "Error de Servidor",
                        text: "No se pudo registrar la actividad. Revise la consola.",
                        icon: "error"
                    });
                }
            });
        }

////    funcion para ver los activos
        function verActivos() {
            $.ajax({
                url: 'acciones_activos.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    opcion: 'verActivos'
                },
                success: function(data) {                    
                    // 1. Obtener instancia de DataTable
                    var table = $('#tablaActivos').DataTable();
                    
                    // 2. Limpiar tabla
                    table.clear(); 
                    
                    // 3. Iterar y crear filas
                    data.forEach(function(activo) {
                        
                        // Formateo de moneda para que se vea bien
                        let formatoDinero = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

                        // Lógica visual para Tipos (Badges)
                        let badgeTipo = '';
                        if(activo.tipo_activo.includes('COMPUTO')) {
                            badgeTipo = '<span class="badge bg-primary">'+activo.tipo_activo+'</span>';
                        } else if (activo.tipo_activo.includes('OFICINA')) {
                            badgeTipo = '<span class="badge bg-secondary">'+activo.tipo_activo+'</span>';
                        } else if (activo.tipo_activo.includes('MAQUINAS')) {
                            badgeTipo = '<span class="badge bg-info text-dark">'+activo.tipo_activo+'</span>';
                        } else if (activo.tipo_activo.includes('HERRAMIENTAS')) {
                            badgeTipo = '<span class="badge bg-warning text-dark">'+activo.tipo_activo+'</span>';
                        } else {
                            badgeTipo = '<span class="badge bg-dark text-white">'+activo.tipo_activo+'</span>';
                        }


                        let opciones = `
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-sm btn-outline-primary" title="Ver Detalles" onclick="verDetallesActivo(${activo.id})">
                                    <i class="fas fa-fw fa-eye"></i> 
                                </button>
                                <button class="btn btn-sm btn-outline-warning" title="Editar Activo" onclick="editarActivo(${activo.id})">
                                    <i class="fas fa-fw fa-pen"></i> 
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Eliminar Activo" onclick="eliminarActivo(${activo.id})">
                                    <i class="fas fa-fw fa-trash"></i>
                                </button>
                            </div>
                        `;
                        
                        var fila = [
                            badgeTipo,
                            activo.descripcion,
                            activo.marca + ' / ' + activo.modelo,                            
                            `<span class="fw-bold">${activo.nave + ' / ' + activo.ubicacion }</span>`,
                            activo.usuario,
                            formatoDinero.format(activo.costo) + ' / ' +
                            `<span class="fw-bold text-success">${formatoDinero.format(activo.remanente)}</span>`,
                            activo.observaciones,
                            opciones
                        ];
                        
                        // 4. Agregar fila a DataTables
                        table.row.add(fila);
                    });
                    
                    // 5. Dibujar (Renderizar) cambios
                    table.draw();


                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Swal.fire({
                        title: "No se pudieron cargar los activos!",
                        icon: "error",      
                        draggable: true
                    });
                }
            });
        }

////    función para ver detalles del activo
        function verDetallesActivo(idActivo) {
            // Redirigir a la página de detalles con el ID del activo como parámetro
            window.location.href = 'detallesActivo.php?id=' + idActivo;
        }

//// funcion para cargar detalle del activo
        function cargarDetalleActivo() {
            // 1. Obtener el ID de la URL
            var urlParams = new URLSearchParams(window.location.search);
            var idActivo = urlParams.get('id');
                
            // Validación simple: Si no hay ID en la URL, no intentamos buscar
            if (!idActivo) return;

            $.ajax({
                url: 'acciones_activos.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    opcion: 'detalleActivo',
                    idActivo: idActivo
                },
                success: function(data) {
                    
                    if (data.status === 'success') {
                        var activo = data.activo;                        
                        // --- A. CONFIGURACIÓN DE FORMATO DE MONEDA ---
                        const formatoDinero = new Intl.NumberFormat('es-MX', { 
                            style: 'currency', 
                            currency: 'MXN' 
                        });

                        // --- B. LLENADO DE CAMPOS BÁSICOS ---
                        // Nota: Los IDs (#detalle...) deben existir en tu HTML
                        $('#detalleDescripcion').text(activo.descripcion);
                        $('#detalleMarca').text(activo.marca);
                        $('#detalleModelo').text(activo.modelo);
                        $('#detalleNoSerie').text(activo.no_serie || 'Sin Número de Serie'); // Manejo de nulos
                        $('#detalleIdInterno').text(activo.id_interno);
                        
                        // Ubicación y Usuario
                        $('#detalleNave').text(activo.nave);
                        $('#detalleUsuario').text(activo.usuario);
                        // Si agregaste región en el SQL:
                        if(activo.region) $('#detalleRegion').text(activo.region);

                        // --- C. DATOS FINANCIEROS ---
                        $('#detalleMoi').text(formatoDinero.format(activo.moi));
                        $('#detalleCosto').text(formatoDinero.format(activo.costo));
                        $('#detalleDepreciacion').text(formatoDinero.format(activo.depreciacion));
                        
                        // Lógica de color para el Remanente
                        var remanenteVal = parseFloat(activo.remanente);
                        var elRemanente = $('#detalleRemanente');
                        
                        elRemanente.text(formatoDinero.format(remanenteVal));
                        
                        if (remanenteVal > 0) {
                            elRemanente.addClass('text-success fw-bold').removeClass('text-danger');
                        } else {
                            elRemanente.addClass('text-danger fw-bold').removeClass('text-success');
                        }

                        $('#detalleObservaciones').text(activo.observaciones || "Sin observaciones.");

                        // --- D. LÓGICA DE TIPO DE ACTIVO Y CAMPOS TÉCNICOS ---
                        // Elementos del DOM
                        var badgeContainer = $('#detalleTipoBadge'); // Contenedor para la etiqueta
                        var seccionTecnica = $('#seccionComputo');   // El div que tiene CPU y Monitor
                        
                        // 1. Es Computadora
                        if (activo.tipo_activo && activo.tipo_activo.includes('COMPUTO', 'CÓMPUTO')) {
                            // Badge Azul
                            badgeContainer.html('<h3><span class="badge bg-primary"><i class="bi bi-laptop"></i> CÓMPUTO</span></h3>');
                            
                            // Mostrar Sección Técnica y llenar datos
                            seccionTecnica.removeClass('d-none');
                            $('#detalleCpu').text(activo.cpu_info || 'N/A');
                            $('#detalleMonitor').text(activo.monitor_info || 'N/A');

                        // 2. Es Accesorio (Si agregaste el campo 'es_accesorio' al SQL)
                        } else if (activo.es_accesorio == 1) {
                            // Badge Gris
                            badgeContainer.html('<span class="badge bg-secondary"><i class="bi bi-plug"></i> ACCESORIO</span>');
                            seccionTecnica.addClass('d-none'); // Ocultar técnica

                        // 3. Otro (Mobiliario, Vehículo, etc.)
                        } else {
                            // Badge Info Standard
                            badgeContainer.html('<span class="badge bg-info text-dark">' + activo.tipo_activo + '</span>');
                            seccionTecnica.addClass('d-none'); // Ocultar técnica
                        }

                    } else {
                        // El ID no existe en BD
                        Swal.fire({
                            title: "Activo no encontrado",
                            text: data.message || "El ID solicitado no existe.",
                            icon: "warning"
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Error AJAX:", errorThrown);
                    Swal.fire({
                        title: "Error de Conexión",
                        text: "No se pudieron cargar los detalles. Intente recargar la página.",
                        icon: "error"
                    });
                }
            });
        }


////    funciión para obtener datos del formulario
        function getFormData(formId) {
            var formArray = $('#' + formId).serializeArray();
            var formData = {};
            formArray.forEach(function(item) {
                formData[item.name] = item.value;
            });
            return formData;
        }        
////    funcnion para cargar empleados en el select
        function getEmpleados(seleccionado) {
            opcion = "getEmpleados";

            $.ajax({
                url: 'acciones_activos.php',
                method: 'POST',
                dataType: 'json',
                data: {opcion},
                success: function(data) {
                    var select = $(seleccionado);
                    i = 0;
                    data.forEach(function(usuarios) {
                        if (i = 0) {
                            var option = $('<option></option>').attr('value', '0').text('Selecciona...');
                            select.append(option);
                        }
                        var option = $('<option></option>').attr('value', usuarios.noEmpleado).text(usuarios.nombre);
                        select.append(option);
                    });

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Swal.fire({
                        title: "La solicitúd no se pudo procesar!",
                        icon: "error",
                        draggable: true
                    });

                }
            });

        }
        
////    Función para calcular el remanente
        function calcularRemanente() {
            var moi = parseFloat(document.getElementById('inputMoi').value) || 0;
            var depreciacion = parseFloat(document.getElementById('inputDepreciacion').value) || 0;
            var remanente = moi - depreciacion;
            document.getElementById('inputRemanente').value = remanente.toFixed(2);
        }

/////   Función para convertir texto a mayúsculas y quitar acentos
        function convertirTexto(e) {
            // Convertir a mayúsculas y quitar acentos
            e.value = e.value
            .toUpperCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
        }
////    Función para obtener el valor de una cookie
        function getCookie(name) {
            let value = "; " + document.cookie;
            let parts = value.split("; " + name + "=");
            if (parts.length === 2) return parts.pop().split(";").shift();
        }

////    Función para eliminar un activo
    function eliminarActivo(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esto! El activo se eliminará permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminarlo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario dice que SÍ, hacemos la petición AJAX
                $.ajax({
                    url: 'acciones_activos.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        opcion: 'eliminarActivo',
                        idActivo: id
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire(
                                '¡Eliminado!',
                                'El activo ha sido eliminado.',
                                'success'
                            );
                            // Recargamos la tabla para que desaparezca la fila
                            verActivos(); 
                        } else {
                            Swal.fire('Error', response.message || 'No se pudo eliminar', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Hubo un problema de conexión', 'error');
                    }
                });
            }
        });
    }

    function editarActivo(id) {
        // Redirigir a la página de edición con el ID del activo como parámetro
        window.location.href = 'editarActivo.php?id=' + id;
    }
// FUNCIÓN PARA LLENAR EL FORMULARIO
    function cargarDatosParaEditar(id) {
        $.ajax({
            url: 'acciones_activos.php',
            method: 'POST',
            dataType: 'json',
            data: { opcion: 'detalleActivo', idActivo: id }, // Reusamos tu función de detalle
            success: function(data) {
                if(data.status === 'success') {
                    const act = data.activo;
                    
                    // Llenar campos simples
                    $('#editId').val(act.id);
                    $('#editDescripcion').val(act.descripcion);
                    $('#editMarca').val(act.marca);
                    $('#editModelo').val(act.modelo);
                    $('#editSerie').val(act.no_serie);
                    $('#editIdInterno').val(act.id_interno);
                    
                    $('#editMoi').val(act.moi);
                    $('#editDepreciacion').val(act.depreciacion);
                    $('#editRemanente').val(act.remanente);
                    $('#editObservaciones').val(act.observaciones);
                    
                    // Llenar Selects (Deben existir los options previamente)
                    // Nota: Si usas catálogos dinámicos, asegúrate de que carguen antes de hacer esto
                    $('#editTipoActivo').val(act.id_tipo_activo || 1).trigger('change'); // Trigger para mostrar/ocultar campos
                    $('#editNave').val(act.id_nave);
                    $('#editUsuario').val(act.id_usuario); // OJO: Tu consulta SQL debe devolver id_usuario, no solo el nombre

                    // Llenar Checkbox
                    $('#editEsAccesorio').prop('checked', act.es_accesorio == 1);

                    // Llenar Técnicos
                    $('#editCpu').val(act.cpu_info);
                    $('#editMonitor').val(act.monitor_info);

                } else {
                    Swal.fire('Error', 'No se encontró el activo', 'error');
                }
            }
        });
    }

    // FUNCION PARA CARGAR REGIONES
    function getRegiones(seleccionado) {
        opcion = "getRegiones";
        $.ajax({
            url: 'acciones_activos.php',
            method: 'POST',
            dataType: 'json',
            data: {opcion},
            success: function(data) {
                var select = $(seleccionado);
                i = 0;
                data.forEach(function(region) {
                    if (i = 0) {
                        var option = $('<option></option>').attr('value', '').text('Selecciona...');
                        select.append(option);
                    }   
                    var option = $('<option></option>').attr('value', region.id).text(region.region);
                    select.append(option);
                });  
            },  
            error: function(jqXHR, textStatus, errorThrown) {
                Swal.fire({
                    title: "La solicitúd no se pudo procesar!",
                    icon: "error",
                    draggable: true
                });
            }
        });
    }
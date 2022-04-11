<?php

namespace App\Custom;

use App\Custom\WebServiceSiesa;
use App\Models\ConexionesModel;
use App\Models\LogErrorImportacionModel;
use App\Traits\TraitHerramientas;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Custom\PedidoCore;


class FacturaCore
{
    use TraitHerramientas;

    public function subirFacturaSiesa($factura, $detallesFactura)
    {
        if (count($detallesFactura) > 0) {
            $importar = true;
            $cadena = "";
            $cadena .= str_pad(1, 7, "0", STR_PAD_LEFT) . "00000001001\n"; // Linea 1
            if ($factura['tipo_documento'] == 'FEV') {
                
                // Crear estructura para VENTA (Docto. ventas comercial v1)
                $cadena .= str_pad(2, 7, "0", STR_PAD_LEFT); //Numero de registros
                $cadena .= str_pad(461, 4, "0", STR_PAD_LEFT); //Tipo de registro
                $cadena .= '00'; //Subtipo de registro
                $cadena .= '01'; //version del tipo de registro
                $cadena .= '001'; //Compañia
                $cadena .= '1'; //Indicador para liquidar impuestos
                $cadena .= '1'; //Indicador para liquidar retenciones
                $cadena .= '0'; //Indica si el numero consecutivo de docto es manual o automático
                $cadena .= $factura['centro_operacion']; //Centro de operación del documento
                $cadena .= str_pad($factura['tipo_documento'], 3, " ", STR_PAD_RIGHT); //Tipo de documento
                $cadena .= str_pad($factura['numero_factura'], 8, "0", STR_PAD_LEFT); //Numero documento
                $cadena .= substr($factura['fecha_factura'], 0, 4).substr($factura['fecha_factura'], 5, 2).substr($factura['fecha_factura'], 8, 2); //Fecha del documento
                $cadena .= str_pad($factura['nit'], 15, " ", STR_PAD_RIGHT); //Tercero cliente a facturar
                $cadena .= '520'; //Clase interna del documento
                $cadena .= '1'; //Estado del documento
                $cadena .= '0'; //Estado de impresión del documento
                $cadena .= str_pad($factura['sucursal_cliente'], 3, "0", STR_PAD_LEFT); //Sucursal cliente a facturar
                $cadena .= '0001'; //Tipo de cliente
                $cadena .= $factura['centro_operacion']; //Centro de operacion de la factura
                $cadena .= str_pad('', 15, " ", STR_PAD_LEFT); //Cliente de contado
                $cadena .= str_pad($factura['nit'], 15, " ", STR_PAD_RIGHT); //Tercero cliente a remisionar
                $cadena .= str_pad($factura['sucursal_cliente'], 3, "0", STR_PAD_LEFT);//Sucursal cliente a remisionar
                $cadena .= str_pad($factura['cedula_vendedor'], 15, " ", STR_PAD_RIGHT);//Tercero vendedor
                $cadena .= str_pad($factura['numero_factura'], 10, "0", STR_PAD_LEFT); //Referencia del documento
                $cadena .= str_pad('', 12, " ", STR_PAD_RIGHT); //Numero orden de compra
                $cadena .= str_pad('', 10, " ", STR_PAD_RIGHT); //Numero de cargue
                $cadena .= '00D'; //Condicion de pago
                $cadena .= 'COP'; //Moneda documento
                $cadena .= 'COP'; //Moneda base de conversión
                $cadena .= '00000001.0000'; //Tasa de conversión
                $cadena .= 'COP'; //Moneda local
                $cadena .= '00000001.0000'; //Tasa local
                $cadena .= str_pad('n1.1:'.$factura['observaciones_factura'], 2000, " ", STR_PAD_RIGHT); //Observaciones del documento
                $cadena .= '000'; //Punto de envio
                $cadena .= '1'; //Indicador de contacto
                $cadena .= str_pad('', 50, " ", STR_PAD_RIGHT); //Contacto
                $cadena .= str_pad('', 40, " ", STR_PAD_RIGHT); //Direccion 1
                $cadena .= str_pad('', 40, " ", STR_PAD_RIGHT); //Direccion 2
                $cadena .= str_pad('', 40, " ", STR_PAD_RIGHT); //Direccion 3
                $cadena .= str_pad('', 3, " ", STR_PAD_RIGHT); //Pais
                $cadena .= str_pad('', 2, " ", STR_PAD_RIGHT); //Departamento/Estado
                $cadena .= str_pad('', 3, " ", STR_PAD_RIGHT); //Ciudad
                $cadena .= str_pad('', 40, " ", STR_PAD_RIGHT); //Barrio
                $cadena .= str_pad('', 20, " ", STR_PAD_RIGHT); //Telefono
                $cadena .= str_pad('', 20, " ", STR_PAD_RIGHT); //fax
                $cadena .= str_pad('', 10, " ", STR_PAD_RIGHT); //Codigo postal
                $cadena .= str_pad('', 50, " ", STR_PAD_RIGHT); //E-mail
                $cadena .= str_pad('', 10, " ", STR_PAD_RIGHT); //Codigo del vehiculo
                $cadena .= str_pad('', 15, " ", STR_PAD_RIGHT); //Codigo transportador
                $cadena .= str_pad('', 3, " ", STR_PAD_RIGHT); //Código sucursal transportador
                $cadena .= str_pad('', 15, " ", STR_PAD_RIGHT); //Código conductor
                $cadena .= str_pad('', 50, " ", STR_PAD_RIGHT); //Nombre conductor
                $cadena .= str_pad('', 15, " ", STR_PAD_RIGHT); //Identificación del conductor
                $cadena .= str_pad('', 30, " ", STR_PAD_RIGHT); //Numero de guia
                $cadena .= '0000000000.0000'; //Cajas/bultos
                $cadena .= '000000000000000.0000'; //Peso
                $cadena .= '000000000000000.0000'; //Volumen
                $cadena .= '000000000000000.0000'; //Valor asegurado
                $cadena .= str_pad('', 255, " ", STR_PAD_RIGHT); //Notas
                $cadena .= str_pad('', 3, " ", STR_PAD_LEFT); //Caja de recaudo
                $cadena .= '0'; //Genera Kit
                $cadena .= str_pad('', 3, " ", STR_PAD_RIGHT); //Tipo de documento de proceso
                $cadena .= str_pad('', 5, " ", STR_PAD_RIGHT); //Bodega de componentes del kit
                $cadena .= str_pad('', 2, " ", STR_PAD_RIGHT); //Motivo de salida proceso
                $cadena .= str_pad('', 2, " ", STR_PAD_RIGHT); //Motivo de entrada proceso
                $cadena .= '070'; //Clase de documento proceso
                $cadena .= "\n";
                
                //-------- cxc (Cuotas CxC)

                $cadena .= str_pad(3, 7, "0", STR_PAD_LEFT); //Numero de registro
                $cadena .= str_pad(353, 4, "0", STR_PAD_LEFT); //Tipo de registro
                $cadena .= '03'; //Subtipo de registro
                $cadena .= '01'; //version del tipo de registro
                $cadena .= '001'; //Compañia
                $cadena .= $factura['centro_operacion']; //Centro de operación del documento
                $cadena .= str_pad($factura['tipo_documento'], 3, " ", STR_PAD_RIGHT); //Tipo de documento
                $cadena .= str_pad($factura['numero_factura'], 8, "0", STR_PAD_LEFT); //Numero documento
                $cadena .= str_pad('', 3, " ", STR_PAD_LEFT); //Tipo documento de cruce
                $cadena .= str_pad($factura['numero_factura'], 8, "0", STR_PAD_LEFT); //Numero de documento de cruce
                $cadena .= '000'; //Numero de cuota
                $cadena .= '000000000000000.0000'; // Valor cuota o valor a cruzar
                $cadena .= '100.00'; //Porcentaje de la cuota respecto al  total del documento.
                $cadena .= substr($factura['fecha_venc'], 0, 4).substr($factura['fecha_venc'], 5, 2).substr($factura['fecha_venc'], 8, 2); //Fecha de vencimiento de la cuota -- OJO SUMAR 3 DIAS MAS PARA FECHA VENCIMIENTO
                $cadena .= '000000000000000.0000'; //Valor descuento pronto pago
                $cadena .= '000.00'; //Porcentaje del pronto pago con respecto a la cuota
                $cadena .= substr($factura['fecha_factura'], 0, 4).substr($factura['fecha_factura'], 5, 2).substr($factura['fecha_factura'], 8, 2); //Fecha de pronto pago de la cuota
                $cadena .= "\n";
                
                //-------Relacion documentos

                $cadena .= str_pad(4, 7, "0", STR_PAD_LEFT); //Numero de registro
                $cadena .= str_pad(461, 4, "0", STR_PAD_LEFT); //Tipo de registro
                $cadena .= '02'; //Subtipo de registro
                $cadena .= '01'; //version del tipo de registro
                $cadena .= '001'; //Compañia
                $cadena .= $factura['centro_operacion']; //Centro de operación del documento
                $cadena .= str_pad($factura['tipo_documento'], 3, " ", STR_PAD_RIGHT); //Tipo de documento
                $cadena .= str_pad($factura['numero_factura'], 8, "0", STR_PAD_LEFT); //Numero documento
                $cadena .= $factura['centro_operacion']; //Centro de operación del documento
                $cadena .= str_pad($factura['tipo_documento_remision'], 3, " ", STR_PAD_RIGHT); //Tipo de documento remision
                $cadena .= str_pad($factura['numero_documento_remision'], 8, "0", STR_PAD_LEFT); //Numero documento
                $cadena .= "\n";

                //Creacion Detalle factura - movimientos factura (Movimientos versión 01)
                $contador = 5;
                $contadorDetalleFactura = 1;
                foreach ($detallesFactura as $key => $detalleFactura) {
                    //---Declarando variables
                    $listaPrecio = $detalleFactura['lista_precio'];
                    $objValidprepack = new PedidoCore();
                    $validprepack = $objValidprepack->validarCodPrepack($detalleFactura['codigo_producto']);
                    $prepack = ($validprepack === true) ? $detalleFactura['codigo_producto'] : '';
                    if (!empty($prepack)) {
                        $codPrepack = $objValidprepack->obtenerCodigoPrepackSiesa('1', $prepack);
                        if (!empty($codPrepack)) {
                            # code...
                        } else {
                            $error = 'El siguiente prepack en la factura relacionada no existe ' . $codPrepack;
                            $estado = "3";
                            $importar = false;
                            $this->logErrorImportarFactura($error, $estado, $factura['centro_operacion'], $factura['bodega'], $factura['tipo_documento'], $factura['numero_factura']);
                        }
                        // Realizar codigo cadena prepack para factura
                    } else {
                        $productoSiesa = $this->obtenerCodigoProductoSiesa($detalleFactura['codigo_producto']);
            
                        if (!empty($productoSiesa)) {
                            $codigoProductoSiesa = $productoSiesa[0]['codigo_producto'];
                                            
                            $cadena .= str_pad($contador, 7, "0", STR_PAD_LEFT); //Numero consecutivo
                            $cadena .= '0470'; //Tipo registro
                            $cadena .= '01'; //Subtipo registro
                            $cadena .= '01'; //Version del tipo de registro
                            $cadena .= '001'; //compañia
                            $cadena .= $factura['centro_operacion']; //Centro de operacion
                            $cadena .= $factura['tipo_documento']; //Tipo de documento
                            $cadena .= str_pad($factura['numero_factura'], 8, "0", STR_PAD_LEFT); //Consecutivo de documento
                            $cadena .= str_pad($contadorDetalleFactura, 10, "0", STR_PAD_LEFT); //Numero de registro
                            $cadena .= str_pad($codigoProductoSiesa, 7, "0", STR_PAD_LEFT); //Item
                            $cadena .= str_pad('', 20, " ", STR_PAD_LEFT); //Referencia item
                            $cadena .= str_pad('', 20, " ", STR_PAD_LEFT); //Codigo de barras
                            $cadena .= str_pad('', 4, " ", STR_PAD_LEFT); //Extencion 1
                            $cadena .= str_pad('', 4, " ", STR_PAD_LEFT); //Extencion 2
                            $cadena .= str_pad($factura['bodega'], 5, " ", STR_PAD_RIGHT); //Bodega
                            $cadena .= str_pad('', 10, " ", STR_PAD_RIGHT);//Ubicacion
                            $cadena .= str_pad('', 15, " ", STR_PAD_LEFT); //Lote
                            $cadena .= '501'; //Concepto  ----> Ojo: cuando nos definan el tipo de documento para devolucion colocar condicional
                            $cadena .= str_pad('1', 2," ",STR_PAD_RIGHT); //Motivo
                            $cadena .= '0'; //Indicador de obsequio
                            $cadena .= $factura['centro_operacion']; //Centro de operacion movimiento
                            $cadena .= '01'; //Unidad de negocio movimiento
                            $cadena .= str_pad('', 15, " ", STR_PAD_LEFT); //Centro de costo movimiento
                            $cadena .= str_pad('', 15, " ", STR_PAD_LEFT); //Proyecto
                            $cadena .= str_pad($listaPrecio, 3, " ", STR_PAD_RIGHT) ; //Lista de precio
                            $cadena .= 'UNID'; //Unidad de medida precio
                            $cadena .= 'UNID'; //Unidad de medida del movimiento
                            $cadena .= str_pad(intval($detalleFactura['cantidad']), 15, "0", STR_PAD_LEFT) . '.0000'; //Cantidad base
                            $cadena .= str_pad('', 15, "0", STR_PAD_LEFT) . '.0000'; //Cantidad adicional
                            $cadena .= str_pad(intval($detalleFactura['valor_bruto']), 15, "0", STR_PAD_LEFT) > '0' ? str_pad(intval($detalleFactura['valor_bruto']), 15, "0", STR_PAD_LEFT). '.0000' : str_pad('1', 15, "0", STR_PAD_LEFT) . '.0000'; //Valor bruto
                            $cadena .= '2'; //Naturaleza de la transaccion
                            $cadena .= '0'; //Solo valor
                            $cadena .= '0'; //Impuestos asumidos
                            $cadena .= str_pad('', 255, " ", STR_PAD_LEFT); //Notas
                            $cadena .= str_pad('', 2000, " ", STR_PAD_LEFT); //Descripcion
                            $cadena .= str_pad('', 40, " ", STR_PAD_LEFT); //Descripcion item
                            $cadena .= str_pad('', 4, " ", STR_PAD_LEFT); //Unidad de medida de inventario del item.
                            $cadena .= "\n";
                            $contador++;
                            $contadorDetalleFactura++;
                        } else {
                            $error = 'El siguiente producto en la factura relacionada no existe '.$detalleFactura['codigo_producto'];
                            $estado = "3";
                            $importar = false;
                            $this->logErrorImportarFactura($error, $estado, $factura['centro_operacion'], $factura['bodega'], $factura['tipo_documento'], $factura['numero_factura']);
                        }
                    }
                }
            } else {

                // Crear estructura para DEVOLUCION (Docto. ventas comercial v3)
                $cadena .= str_pad(2, 7, "0", STR_PAD_LEFT); //Numero de registros
                $cadena .= str_pad(461, 4, "0", STR_PAD_LEFT); //Tipo de registro
                $cadena .= '00'; //Subtipo de registro
                $cadena .= '03'; //version del tipo de registro
                $cadena .= '001'; //Compañia
                $cadena .= '0'; //Indica si el numero consecutivo de docto es manual o automático
                $cadena .= $factura['centro_operacion']; //Centro de operación del documento
                $cadena .= str_pad($factura['tipo_documento'], 3, " ", STR_PAD_RIGHT); //Tipo de documento
                $cadena .= str_pad($factura['numero_factura'], 8, "0", STR_PAD_LEFT); //Numero documento
                $cadena .= substr($factura['fecha_factura'], 0, 4).substr($factura['fecha_factura'], 5, 2).substr($factura['fecha_factura'], 8, 2); //Fecha del documento
                $cadena .= '1'; //Estado del documento
                $cadena .= '0'; //Estado de impresión del documento
                $cadena .= str_pad($factura['tipo_documento_remision'], 3, " ", STR_PAD_RIGHT); // Tipo de documento A DEVOLVER
                $cadena .= $factura['numero_documento_remision']; // Numero de documento A DEVOLVER
                $cadena .= str_pad('', 10, " ", STR_PAD_RIGHT); //Codigo de vehiculo
                $cadena .= str_pad('', 15, " ", STR_PAD_RIGHT); //Codigo transportador
                $cadena .= str_pad('', 3, " ", STR_PAD_RIGHT); //Codigo sucursal transportador
                $cadena .= str_pad('', 15, " ", STR_PAD_RIGHT); //Codigo conductor
                $cadena .= str_pad('', 50, " ", STR_PAD_RIGHT); //Nombre conductor
                $cadena .= str_pad('', 15, " ", STR_PAD_RIGHT); //Identificacion del conductor
                $cadena .= str_pad('', 30, " ", STR_PAD_RIGHT); //Numero de guia
                $cadena .= '0000000000.0000'; //Cajas/bultos
                $cadena .= '000000000000000.0000'; //Peso
                $cadena .= '000000000000000.0000'; //Volumen
                $cadena .= '000000000000000.0000'; //Valor asegurado
                $cadena .= str_pad('', 255, " ", STR_PAD_RIGHT); //Notas
                $cadena .= "\n";
                
                //-------- cxc DEVOLUCION (Cuotas CxC)

                $cadena .= str_pad(3, 7, "0", STR_PAD_LEFT); //Numero de registro
                $cadena .= str_pad(353, 4, "0", STR_PAD_LEFT); //Tipo de registro
                $cadena .= '03'; //Subtipo de registro
                $cadena .= '01'; //version del tipo de registro
                $cadena .= '001'; //Compañia
                $cadena .= $factura['centro_operacion']; //Centro de operación del documento
                $cadena .= str_pad($factura['tipo_documento'], 3, " ", STR_PAD_RIGHT); //Tipo de documento
                $cadena .= str_pad($factura['numero_factura'], 8, "0", STR_PAD_LEFT); //Numero documento
                $cadena .= str_pad('', 3, " ", STR_PAD_LEFT); //Tipo documento de cruce
                $cadena .= str_pad($factura['numero_factura'], 8, "0", STR_PAD_LEFT); //Numero de documento de cruce
                $cadena .= '000'; //Numero de cuota
                $cadena .= '000000000000000.0000'; // Valor cuota o valor a cruzar
                $cadena .= '100.00'; //Porcentaje de la cuota respecto al  total del documento.
                $cadena .= substr($factura['fecha_venc'], 0, 4).substr($factura['fecha_venc'], 5, 2).substr($factura['fecha_venc'], 8, 2); //Fecha de vencimiento de la cuota -- OJO SUMAR 3 DIAS MAS PARA FECHA VENCIMIENTO
                $cadena .= '000000000000000.0000'; //Valor descuento pronto pago
                $cadena .= '000.00'; //Porcentaje del pronto pago con respecto a la cuota
                $cadena .= substr($factura['fecha_factura'], 0, 4).substr($factura['fecha_factura'], 5, 2).substr($factura['fecha_factura'], 8, 2); //Fecha de pronto pago de la cuota
                $cadena .= "\n";
                
                //-------Relacion documentos DEVOLUCION

                $cadena .= str_pad(4, 7, "0", STR_PAD_LEFT); //Numero de registro
                $cadena .= str_pad(461, 4, "0", STR_PAD_LEFT); //Tipo de registro
                $cadena .= '02'; //Subtipo de registro
                $cadena .= '01'; //version del tipo de registro
                $cadena .= '001'; //Compañia
                $cadena .= $factura['centro_operacion']; //Centro de operación del documento
                $cadena .= str_pad($factura['tipo_documento'], 3, " ", STR_PAD_RIGHT); //Tipo de documento
                $cadena .= str_pad($factura['numero_factura'], 8, "0", STR_PAD_LEFT); //Numero documento
                $cadena .= $factura['centro_operacion']; //Centro de operación del documento
                $cadena .= str_pad($factura['tipo_documento_remision'], 3, " ", STR_PAD_RIGHT); //Tipo de documento remision
                $cadena .= str_pad($factura['numero_documento_remision'], 8, "0", STR_PAD_LEFT); //Numero documento
                $cadena .= "\n";


                //Creacion Detalle DEVOLUCION - movimientos DEVOLUCION
                $contador = 5;
                $contadorDetalleFactura = 1;
                foreach ($detallesFactura as $key => $detalleFactura) {
                    //---Declarando variables
                    $listaPrecio = $detalleFactura['lista_precio'];
                    $productoSiesa = $this->obtenerCodigoProductoSiesa($detalleFactura['codigo_producto']);
                    
                    if (!empty($productoSiesa)) {
                        $codigoProductoSiesa = $productoSiesa[0]['codigo_producto'];

                        $cadena .= str_pad($contador, 7, "0", STR_PAD_LEFT); //Numero consecutivo
                        $cadena .= '0470'; //Tipo registro
                        $cadena .= '01'; //Subtipo registro
                        $cadena .= '12'; //Version del tipo de registro
                        $cadena .= '001'; //compañia
                        $cadena .= $factura['centro_operacion']; //Centro de operacion
                        $cadena .= $factura['tipo_documento']; //Tipo de documento
                        $cadena .= str_pad($factura['numero_factura'], 8, "0", STR_PAD_LEFT); //Consecutivo de documento
                        $cadena .= str_pad($contadorDetalleFactura, 10, "0", STR_PAD_LEFT); //Numero de registro
                        $cadena .= str_pad($codigoProductoSiesa, 7, "0", STR_PAD_LEFT); //Item
                        $cadena .= str_pad('', 20, " ", STR_PAD_LEFT); //Referencia item
                        $cadena .= str_pad('', 20, " ", STR_PAD_LEFT); //Codigo de barras
                        $cadena .= str_pad('', 4, " ", STR_PAD_LEFT); //Extencion 1
                        $cadena .= str_pad('', 4, " ", STR_PAD_LEFT); //Extencion 2
                        $cadena .= str_pad($factura['bodega'], 5, " ", STR_PAD_RIGHT); //Bodega
                        $cadena .= str_pad('', 10, " ", STR_PAD_RIGHT);//Ubicacion
                        $cadena .= str_pad('', 15, " ", STR_PAD_LEFT); //Lote
                        $cadena .= '501'; //Concepto  ----> Ojo: cuando nos definan el tipo de documento para devolucion colocar condicional
                        $cadena .= str_pad('1', 2," ",STR_PAD_RIGHT); //Motivo
                        $cadena .= '0'; //Indicador de obsequio
                        $cadena .= $factura['centro_operacion']; //Centro de operacion movimiento
                        $cadena .= '01'; //Unidad de negocio movimiento
                        $cadena .= str_pad('', 15, " ", STR_PAD_LEFT); //Centro de costo movimiento
                        $cadena .= str_pad('', 15, " ", STR_PAD_LEFT); //Proyecto
                        $cadena .= str_pad($listaPrecio, 3, " ", STR_PAD_RIGHT) ; //Lista de precio
                        $cadena .= 'UNID'; //Unidad de medida precio
                        $cadena .= 'UNID'; //Unidad de medida del movimiento
                        $cadena .= str_pad(intval($detalleFactura['cantidad']), 15, "0", STR_PAD_LEFT) . '.0000'; //Cantidad base
                        $cadena .= str_pad('', 15, "0", STR_PAD_LEFT) . '.0000'; //Cantidad adicional
                        $cadena .= str_pad(intval($detalleFactura['valor_bruto']), 15, "0", STR_PAD_LEFT) > '0' ? str_pad(intval($detalleFactura['valor_bruto']), 15, "0", STR_PAD_LEFT) . '.0000' : str_pad('1', 15, "0", STR_PAD_LEFT) . '.0000'; //Valor bruto
                        $cadena .= '2'; //Naturaleza de la transaccion
                        $cadena .= '0'; //Solo valor
                        $cadena .= '0'; //Impuestos asumidos
                        $cadena .= str_pad('', 255, " ", STR_PAD_LEFT); //Notas
                        $cadena .= str_pad('', 2000, " ", STR_PAD_LEFT); //Descripcion
                        $cadena .= str_pad('', 40, " ", STR_PAD_LEFT); //Descripcion item
                        $cadena .= str_pad('', 4, " ", STR_PAD_LEFT); //Unidad de medida de inventario del item.
                        $cadena .= "\n";
                        $contador++;
                        $contadorDetalleFactura++;
                    } else {
                        $error = 'El siguiente producto en la factura relacionada no existe '.$detalleFactura['codigo_producto'];
                        $estado = "3";
                        $importar = false;
                        $this->logErrorImportarFactura($error, $estado, $factura['centro_operacion'], $factura['bodega'], $factura['tipo_documento'], $factura['numero_factura']);
                    }
                }
            }
            $cadena .= str_pad($contador, 7, "0", STR_PAD_LEFT) . "99990001001";

            $lineas = explode("\n", $cadena);

            $nombreArchivo = $factura['tipo_documento'] . str_pad($factura['numero_factura'], 12, "0", STR_PAD_LEFT) . '.txt';
            Storage::disk('local')->put('juandhoyos/facturas/txt/' . $nombreArchivo, $cadena);
            $xmlFactura = $this->crearXmlFactura($lineas, $factura['numero_factura'], $factura['tipo_documento']);

            if (!$this->existeFacturaSiesa('1', $factura['tipo_documento'], $factura['numero_factura']) && $importar == true) {
                $resp = $this->getWebServiceSiesa(28)->importarXml($xmlFactura);
                
                if (!is_array($resp) && empty($resp)) {
                    $error = 'Ok';
                    $estado = "2";
                    $this->logErrorImportarFactura($error, $estado, $factura['centro_operacion'], $factura['bodega'], $factura['tipo_documento'], $factura['numero_factura']);
                } else {
                    if (is_array($resp)) {
                        $error=$resp['error'];
                        $estado = "4";
                        $this->logErrorImportarFactura($error, $estado, $factura['centro_operacion'], $factura['bodega'], $factura['tipo_documento'], $factura['numero_factura']);
                    } else {
                        $mensaje = "";
                        foreach ($resp->NewDataSet->Table as $key => $errores) {
                            $error = "";
                            foreach ($errores as $key => $detalleError) {
                                if ($key == 'f_detalle') {
                                    $error = $detalleError;
                                }
                            }
                        }

                        if (strrpos($error, "el tercero vendedor no existe o no esta configurado como vendedor")!==false) {
                            $error.=" Nombre vendedor: ".$factura['nombre_vendedor']." Cedula vendedor: ".$factura['cedula_vendedor'];
                            $estado = "3";
                            $this->logErrorImportarFactura($error, $estado, $factura['centro_operacion'], $factura['bodega'], $factura['tipo_documento'], $factura['numero_factura']);
                        } else {
                            $estado = "3";
                            $this->logErrorImportarFactura($error, $estado, $factura['centro_operacion'], $factura['bodega'], $factura['tipo_documento'], $factura['numero_factura']);
                        }
                    }
                }
            } elseif ($this->existeFacturaSiesa('1', $factura['tipo_documento'], $factura['numero_factura'])) {
                $error = "Este pedido ya fue registrado anteriormente, por favor verificar. Fecha de ejecucion: " . date('Y-m-d h:i:s');
                $estado = "2";
                $this->logErrorImportarFactura($error, $estado, $factura['centro_operacion'], $factura['bodega'], $factura['tipo_documento'], $factura['numero_factura']);
            }
        } else {
            $error = 'La factura no tiene productos asignados';
            $estado = "3";
            $this->logErrorImportarFactura($error, $estado, $factura['centro_operacion'], $factura['bodega'], $factura['tipo_documento'], $factura['numero_factura']);
        }
    }

    public function obtenerCodigoProductoSiesa($productoEcom)
    {
        $parametros = [
            ['PARAMETRO1' => $productoEcom],
        ];
        return $this->getWebServiceSiesa(34)->ejecutarConsulta($parametros);
    }

    public function getWebServiceSiesa($idConexion)
    {
        return new WebServiceSiesa($idConexion);
    }

    public function logErrorImportarFactura($mensaje, $estado, $centroOperacion, $bodega, $tipoDocumento, $numeroFactura)
    {
        $objErrorImpFact = new LogErrorImportacionModel();
        $result = $objErrorImpFact->actualizarEstadoDocumentoFac($mensaje, $estado, $centroOperacion, $bodega, $tipoDocumento, $numeroFactura);
    }

    public function existeFacturaSiesa($idCia, $tipoDocumento, $numDoctoReferencia)
    {
        $parametros = [
            ['PARAMETRO1' => $idCia],
            ['PARAMETRO2' => $tipoDocumento],
            ['PARAMETRO3' => $numDoctoReferencia],
        ];

        $resultado = $this->getWebServiceSiesa(37)->ejecutarConsulta($parametros);

        if (!empty($resultado)) {
            return true;
        } else {
            return false;
        }
    }

    public function crearXmlFactura($lineas, $idOrder, $tipDoc)
    {
        $datosConexionSiesa = $this->getConexionesModel()->getConexionXid(14);
        $xmlFactura = "<?xml version='1.0' encoding='utf-8'?>
        <Importar>
        <NombreConexion>" . $datosConexionSiesa->siesa_conexion . "</NombreConexion>
        <IdCia>" . $datosConexionSiesa->siesa_id_cia . "</IdCia>
        <Usuario>" . $datosConexionSiesa->siesa_usuario . "</Usuario>
        <Clave>" . $datosConexionSiesa->siesa_clave . "</Clave>
        <Datos>\n";
        $datos = "";
        foreach ($lineas as $key => $linea) {
            $xmlFactura .= "        <Linea>" . $linea . "</Linea>\n";
            $datos .= "        <Linea>" . $linea . "</Linea>\n";
        }
        $xmlFactura .= "        </Datos>
        </Importar>";

        $nombreArchivo = $tipDoc . str_pad($idOrder, 12, "0", STR_PAD_LEFT) . '.xml';
        Storage::disk('local')->put('juandhoyos/facturas/xml/' . $nombreArchivo, $xmlFactura);

        return $datos;
    }

    public function getConexionesModel()
    {
        return new ConexionesModel();
    }
}

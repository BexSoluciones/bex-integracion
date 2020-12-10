<?php

namespace App\Http\Controllers\integracionecom\v1;

use App\Custom\WebServiceSiesa;
use App\Http\Controllers\Controller;
use App\Models\ConexionesModel;
use App\Traits\TraitHerramientas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Log;

$currentPath = Route::getFacadeRoot()->current()->uri();

class CompraDevolucionCompraController extends Controller
{
    use TraitHerramientas;

    public function getComprasDevolucionesCompra(Request $request)
    {

        $filtros = $request->input('filter');
        $erroresValidacionFiltro = $this->validarFiltros($filtros);

        if (count($erroresValidacionFiltro) > 0) {
            return response()->json([
                'code' => 412,
                'errors' => $erroresValidacionFiltro,
            ], 412);
        }

        $this->armarSqlCompraDevoCompra($filtros);

        exit();
        // $parametrosValidos=$this->validacionParametros();

        Log::info($request->all());
        $idConexion = 29; //id conexion get compras y devoluciones de compra
        $idConexionConteo = 31; //conteo get compras y devoluciones
        $pagina = $request->input('page') && is_numeric($request->input('page')) ? $request->input('page') : null;
        $rutaActual = Route::getFacadeRoot()->current()->uri();

        if (!is_null($pagina)) {
            $filasXpagina = $request->input('per_page') && ctype_digit($request->input('per_page')) ? $request->input('per_page') : 1000;
            $desde = (((int) ($pagina) - 1) * (int) ($filasXpagina));
            $hasta = (int) ($filasXpagina);
            $anterior = $pagina > 2 ? $pagina - 1 : 1;
            $siguiente = $pagina + 1;

            $parametros = [
                ['desde' => $desde],
                ['hasta' => $hasta],
            ];

            Log::info($parametros);

            $objConteo = $this->getWebServiceSiesa($idConexionConteo);
            $datosConteo = $objConteo->ejecutarConsulta($parametros, false);

            $objWebserviceSiesa = $this->getWebServiceSiesa($idConexion);
            $datos = $objWebserviceSiesa->ejecutarConsulta($parametros, true);

            Log::info("============datos conteo===========");
            Log::info($datosConteo);

            $totalRegistros = $datosConteo[0]['conteo'];
            $totalPaginas = ceil($totalRegistros / $filasXpagina);

            $respuesta = [
                'code' => 200,
                'data' => $datos,
                'links' => [
                    'previous' => $pagina == 1 ? null : url('/') . '/' . $rutaActual . '?page=' . $anterior,
                    'next' => url('/') . '/' . $rutaActual . '?page=' . $siguiente,
                ],
                'meta' => [
                    'total_rows' => $totalRegistros,
                    'total_page' => $totalPaginas,

                ],
            ];

        } else {

            $objWebserviceSiesa = $this->getWebServiceSiesa($idConexion);
            $datos = $objWebserviceSiesa->ejecutarConsulta([], false);

            $respuesta = [
                'code' => 200,
                'data' => $datos,
            ];
        }

        return response()->json($respuesta, 200);

    }

    public function validarFiltros($param)
    {

        $contador = 0;
        $errores = [];
        foreach ($param as $key => $value) {

            $value = $this->protegerInyeccionSql($value);

            switch ($key) {
                case 'tipo_doc':

                    if ($this->noEmpty($value) === false || $this->validarTipoDoc($value) == false) {
                        $errores['tipo_doc'] = 'El campo Tipo de Documento no valido, debe ser de tipo EMC o DP';
                    }

                    break;
                case 'bodega':

                    if ($this->noEmpty($value) === false || $this->validarBodega($value) === false) {
                        $errores['bodega'] = 'El campo Bodega no es valido, esta vacio o no es un digito';
                    }

                    break;
                case 'consec_doc':

                    if ($this->noEmpty($value) === false || $this->validarConsecDoc($value) == false) {
                        $errores['consec_doc'] = 'El campo consec_doc no es valido, esta vacio o no es un digito';
                    }

                    break;
                case 'fecha_doc':

                    // if ($this->noEmpty($value) === false || $this->validarTipoDoc($value) == false) {
                    //     $errores['tipo_doc'] = 'Tipo de documento no valido, debe ser de tipo EMC o DP';
                    // }

                    break;

            }

        }

        Log::info($errores);
        return $errores;

    }

    public function validarTipoDoc($param)
    {

        if ($param == 'EMC' || $param == 'DP') {
            return true;
        }
        return false;
    }

    public function validarBodega($param)
    {
        if (ctype_digit($param)) {
            return true;
        }
        return false;
    }

    public function validarConsecDoc($param)
    {
        if (ctype_digit($param)) {
            return true;
        }
        return false;
    }

    public function validarFechaDocumento($param)
    {

    }

    public function noEmpty($data)
    {

        if (empty($data)) {
            return false;
        }
        return true;
    }

    public function armarSqlCompraDevoCompra($filtros)
    {

        $where = [];
        $contador = 0;
        foreach ($filtros as $filtro => $value) {

            switch ($filtro) {
                case 'tipo_doc':

                    $where[$contador] = [$filtro, '=', $value];
                    $contador++;
                    break;
                case 'bodega':

                    $where[$contador] = [$filtro, '=', $value];
                    $contador++;
                    break;
                case 'consec_doc':

                    $where[$contador] = [$filtro, '=', $value];
                    $contador++;
                    break;
                case 'fecha_doc':

                    break;
            }

        }

        Log::info($where);
        $arrayCadenaWhere=[];
        foreach ($where as $keya => $filtro) {

            Log::info($filtro);

            $filtroConcatenado='';
            foreach ($filtro as $keyb => $value) {
                if($keyb==2){
                    $filtroConcatenado.='"'.$value.'"';
                }else{
                    $filtroConcatenado.=$value;
                }
                
            }

            $arrayCadenaWhere[$keya]= $filtroConcatenado;
            
        }

        $cadenaWhere=' WHERE '.implode(' and ',$arrayCadenaWhere);

        Log::info('========mostrando cadena=========');
        Log::info($cadenaWhere);

        $sql = '
        SELECT * FROM 
        (SELECT
            t350_co_docto_contable.f350_id_cia as cia,
            t350_co_docto_contable.f350_id_co as centro_operacion,
            t350_co_docto_contable.f350_id_tipo_docto as tipo_doc,
            t350_co_docto_contable.f350_consec_docto as consec_doc,
            t350_co_docto_contable.f350_fecha as fecha_doc,
            t350_co_docto_contable.f350_id_periodo as periodo_poc,
            t350_co_docto_contable.f350_rowid_tercero AS tercero,
            t200_mm_terceros.f200_nit as nit,
            t200_mm_terceros.F200_razon_social as razon_social,
            t202_mm_proveedores.f202_id_sucursal as sucursal_terc,
            t350_co_docto_contable.f350_total_base_gravable as valor_base_gravable,
            t350_co_docto_contable.f350_notas as observacion,
            t470_cm_movto_invent.f470_rowid_item_ext as item,
            t470_cm_movto_invent.f470_rowid_bodega as bodega,
            t470_cm_movto_invent.f470_ind_impuesto_precio_venta as impuesto,
            t470_cm_movto_invent.f470_id_unidad_medida as unid_medida,
            t470_cm_movto_invent.f470_cant_1 as cantidad,
            t470_cm_movto_invent.f470_costo_prom_uni as costo_prom_unit,
            t470_cm_movto_invent.f470_costo_prom_tot as costo_prom_total,
            t470_cm_movto_invent.f470_precio_uni as precio_unit,
            t470_cm_movto_invent.f470_vlr_bruto valor_bruto,
            t470_cm_movto_invent.f470_vlr_imp as valor_impuesto,
            t470_cm_movto_invent.f470_vlr_neto as valor_neto,
            t470_cm_movto_invent.f470_desc_variable as descuento,
            t470_cm_movto_invent.f470_id_causal_devol as causal_devolucion,
            t470_cm_movto_invent.f470_rowid_docto as id_movimiento
        FROM t350_co_docto_contable INNER JOIN t470_cm_movto_invent
            ON (f350_id_cia = f470_id_cia AND f350_rowid = f470_rowid_docto)
            INNER JOIN t200_mm_terceros ON (f350_rowid_tercero =  f200_rowid)
            INNER JOIN t202_mm_proveedores ON (f350_rowid_tercero = f202_rowid_tercero)
        WHERE (f350_id_tipo_docto = "EMC" OR f350_id_tipo_docto = "DP") AND t350_co_docto_contable.f350_fecha >= "2020-10-20"
        ) AS a'.$cadenaWhere;

        Log::info($sql);

    }

    public function protegerInyeccionSql($string)
    {

        $listaNegra = ['drop', 'select', 'delete', 'truncate', 'insert', 'update', 'create'];
        $string = str_replace($listaNegra, '', $string);
        return $string;

    }

    public function getWebServiceSiesa($idConexion)
    {
        return new WebServiceSiesa($idConexion);
    }

    public function getConexionesModel()
    {
        return new ConexionesModel();
    }

}
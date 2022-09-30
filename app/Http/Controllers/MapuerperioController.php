<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Helpers\JwtAuth;
//use App\Helpers\JwtAuthu;

use App\Mapuerperio;
use App\Responsables;
use App\Coordinadores;
use App\Jurisdiccion;
use App\Unidades;

class MapuerperioController extends Controller
{
    public function __construct()
    {
       
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index()
    {
        $puerperio = Mapuerperio::all() -> load('unidades','responsables','coordinadores','jurisdiccion');

        if(!empty($puerperio) && sizeof($puerperio) )
        {
            $data = [
                 'code' => 200,
                 'status' => 'success',
                 'puerperio' => $puerperio
            ];        
        }else{

            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No existe información disponible'
            ];    
        }

        return response()->json($data, $data['code'] );
    }

    public function show($id)
    {
        $puerperio = Mapuerperio::find($id);

        if(is_object($puerperio) && !empty($puerperio))
        {
            $puerperio -> load('unidades', 'responsables', 'coordinadores', 'jurisdiccion'); 
            
            $data = [
                'code'     => 200,
                'status'   => 'success',
                'puerperioById' => $puerperio
            ];
        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No existe información disponible'
            ];
        }

        return response() -> json($data, $data['code']);
    }

    public function store(Request $request)
    {
        $json         = $request->input('json', null);
        $params       = json_decode($json);
        $params_array = json_decode($json, true);

        if(!empty($params_array))
        {
            //si no van vacios obtenemos al usuario identificado
            $jwtAuth = new JwtAuth();
            $token   = $request -> header('Authorization', null);
            $user    = $jwtAuth -> checkToken($token, true);

            //validamos los datos enviados
            $validate = \Validator::make($params_array, [
                'unidad_id'  => 'required|unique:ma_puerperio',
                'responsable_id' => 'required',
                'coordinador_id' => 'required',
                'jurisdiccion_id' => 'required',
                'meta_puerperio'  => 'required|numeric',
                'ano'        => 'numeric'
            ]);

            //comprobamos si los datos se validaros correctamente
            if($validate -> fails())
            {
                $data = [
                    'code'    => 400,
                    'status'  => 'error',
                    'message' => 'Los datos no se han validado correctamente'
                ];
            }else{
                //si la validacion es correcta guardamos en la bd la nueva inform de cito
                $puerperio = new Mapuerperio();
                $puerperio -> unidad_id = $params -> unidad_id;
                $puerperio -> responsable_id = $params -> responsable_id;
                $puerperio -> coordinador_id = $params -> coordinador_id;
                $puerperio -> jurisdiccion_id = $params -> jurisdiccion_id;
                $puerperio -> meta_puerperio = $params -> meta_puerperio;
                $puerperio -> ano       = $params -> ano;

                $puerperio -> save();

                $data = [
                    'code'             => 200,
                    'status'           => 'success',
                    'puerperio' => $puerperio
                ];
            }

        }else { //else  si no se reciben los datos correctamente
                $data =[
                    'code'    => 400,
                    'status'  => 'error',
                    'message' => 'No se ha recibido la información correctamente'
                ];
        }

        return response() -> json($data, $data['code']);
    }

    public function update(Request $request, $id)
    {
        $json = $request -> input('json', null);
        $params_array =  json_decode($json, true);

        //Datos para devolver
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'No se ah Actualizado la Información'
        );

        if(!empty($params_array))
        {
            //validamos datos
            $validate = \Validator::make($params_array, [
                // 'unidad_id'  => 'required',
                // 'responsable_id'  => 'required',
                // 'coordinador_id'  => 'required',
                'meta_puerperio'  => 'numeric',
                'enero'      => 'numeric',
                'febrero'    => 'numeric',
                'marzo'      => 'numeric',
                'abril'      => 'numeric',
                'mayo'       => 'numeric',
                'junio'      => 'numeric',
                'julio'      => 'numeric',
                'agosto'     => 'numeric',
                'septiembre' => 'numeric',
                'octubre'    => 'numeric',
                'noviembre'  => 'numeric',
                'diciembre'  => 'numeric',
                'total_puerperio' => 'numeric',
                'ano'        => 'numeric'
            ]);

            if($validate->fails())
            {
                $data['errors'] = $validate -> errors();
                return response() -> json($data, $data['code']);
            }

            //eliminamos los datos que no deseamos actualizar
            //unset($params_array['ano']); ejemplo

            $where = [
                'id' => $id,
            ];

            $puerperio_update = Mapuerperio::updateOrCreate($where, $params_array);

            //devolvemos los datos correctos
            $data = array(
                'code' => 200,
                'status' => 'success',
                'CHANGES' => $params_array
            );
        }

        return response() -> json($data, $data['code']);
    }

    public function destroy(Request $request, $id)
    {
        $puerperio_del = Mapuerperio::where('id', $id) -> first();

        if(!empty($puerperio_del))
        {
            $puerperio_del -> delete();

            $data = [
                'code' => 200,
                'status' => 'success',
                'message' => 'Registro eliminado correctamente',
                'puerperio_del' => $puerperio_del
            ];
        }else{

            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al eliminar la información solicitada'
            ];
        }
        
        return response()->json($data, $data['code']);
    }

    public function getpuerperioByUnidades($id)
    {
        $puerperioxunidad = Mapuerperio::where('unidad_id', $id) ->get() -> load('unidades','responsables','coordinadores','jurisdiccion');

        if(sizeof($puerperioxunidad))
        {
            $data =[
                'code'           => 200,
                'status'         => 'success',
                'puerperioxunidad' => $puerperioxunidad
            ];
        }else{
            $data =[
                'code'    => 400,
                'status'  => 'error',
                'message' => 'No existe información disponible'
            ];
        }

        return response() -> json($data, $data['code']);
    }

    public function getpuerperioByResponsable($id)
    {
        $puerperioxresponsable = Mapuerperio::where('responsable_id', $id) ->get() -> load('unidades','responsables','coordinadores','jurisdiccion');

        if(sizeof($puerperioxresponsable))
        {
            $data =[
                'code'           => 200,
                'status'         => 'success',
                'puerperioxresponsable' => $puerperioxresponsable
            ];
        }else{
            $data =[
                'code'    => 400,
                'status'  => 'error',
                'message' => 'No existe información disponible'
            ];
        }

        return response() -> json($data, $data['code']);
    }

    public function getpuerperioByCoordinador($id)
    {
        $puerperioxcoord = Mapuerperio::where('coordinador_id', $id) ->get() -> load('unidades','responsables','coordinadores','jurisdiccion');

        if(sizeof($puerperioxcoord))
        {
            $data =[
                'code'           => 200,
                'status'         => 'success',
                'puerperioxcoord' => $puerperioxcoord
            ];
        }else{
            $data =[
                'code'    => 400,
                'status'  => 'error',
                'message' => 'No existe información disponible'
            ];
        }

        return response() -> json($data, $data['code']);
    }

    public function getpuerperioByJurisdiccion($id)
    {
        $puerperioxjur = Mapuerperio::where('jurisdiccion_id', $id) ->get() -> load('unidades','responsables','coordinadores','jurisdiccion');

        if(sizeof($puerperioxjur))
        {
            $data =[
                'code'           => 200,
                'status'         => 'success',
                'puerperioxjur' => $puerperioxjur
            ];
        }else{
            $data =[
                'code'    => 400,
                'status'  => 'error',
                'message' => 'No existe información disponible'
            ];
        }

        return response() -> json($data, $data['code']);
    }

}

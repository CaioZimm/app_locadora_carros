<?php

namespace App\Http\Controllers;

use App\Models\Carro;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCarroRequest;
use App\Http\Requests\UpdateCarroRequest;
use App\Repositories\CarroRepository;

class CarroController extends Controller
{
    public function __construct(Carro $carro) {
        $this->carro = $carro;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $carroRepository = new CarroRepository($this->carro);

        if ($request->has('atributos_modelo')) {
            $atributos_modelo = 'modelo:id,'.$request->atributos_modelo;
            $carroRepository->selectAtributosRegistrosRelacionados($atributos_modelo);
        } else {
            $carroRepository->selectAtributosRegistrosRelacionados('modelo');
        }
        
        if ($request->has('filtro')) {
            $carroRepository->filtro($request->filtro);
        }

        if ($request->has('atributos')) {
            $carroRepository->selectAtributos($request->atributos);
        } 

        return response()->json($carroRepository->getResultado(), 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCarroRequest $request)
    {
        $request->validate($this->carro->regras()); // Stateless

        $carro = $this->carro->create([
            'modelo_id' => $request->modelo_id,
            'placa' => $request->placa,
            'disponivel' => $request->disponivel,
            'km' => $request->km
        ]);

        return response()->json($carro, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $carro = $this->carro->with('modelo')->find($id);
        if($carro === null){
            return response()->json(['erro' => 'Recurso pesquisado não existe'], 404); // Json
        }

        return response()->json($carro, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Carro $carro)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCarroRequest $request, $id)
    {
        $carro = $this->carro->find($id);

        if($carro === null){
            return response()->json(['erro' => 'Impossível realizar a atualização. O recurso solicitado não existe'], 404);
        }

        if($request->method() === 'PATCH') {

            $regrasDinamicas = array();

            // Percorrer todas as regras definidas no Model;
            foreach ($carro->regras() as $input => $regra) {
                // Coletar apenas as regras aplicáveis aos parâmetros parciais da requisião PATH;
                if(array_key_exists($input, $request->all())){
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate($regrasDinamicas);

        } else {
            $request->validate($carro->regras());
        }

        // Preencher o objeto $marca com os dados do Request
        $carro->fill($request->all());
        $carro->save();

        return response()->json($carro, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $carro = $this->carro->find($id);

        if($carro === null){
            return response()->json(['erro' => 'Impossível realizar a exclusão. O recurso solicitado não existe'], 404);
        }
       
        $carro->delete();
        return response()->json(['msg' => 'O carro foi removido com sucesso!'], 200);
    }
}

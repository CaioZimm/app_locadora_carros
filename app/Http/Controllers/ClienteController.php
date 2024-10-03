<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Repositories\ClienteRepository;

class ClienteController extends Controller
{
    public function __construct(Cliente $cliente) {
        $this->cliente = $cliente;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $clienteRepository = new ClienteRepository($this->cliente);
        
        if ($request->has('filtro')) {
            $clienteRepository->filtro($request->filtro);
        }

        if ($request->has('atributos')) {
            $clienteRepository->selectAtributos($request->atributos);
        } 

        return response()->json($clienteRepository->getResultado(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClienteRequest $request)
    {
        $request->validate($this->cliente->regras()); // Stateless

        $cliente = $this->cliente->create([
            'nome' => $request->nome
        ]);

        return response()->json($cliente, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cliente = $this->cliente->find($id);
        if($cliente === null){
            return response()->json(['erro' => 'Recurso pesquisado não existe'], 404); // Json
        }

        return response()->json($cliente, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClienteRequest $request, $id)
    {
        $cliente = $this->cliente->find($id);

        if($cliente === null){
            return response()->json(['erro' => 'Impossível realizar a atualização. O recurso solicitado não existe'], 404);
        }

        if($request->method() === 'PATCH') {

            $regrasDinamicas = array();

            // Percorrer todas as regras definidas no Model;
            foreach ($cliente->regras() as $input => $regra) {
                // Coletar apenas as regras aplicáveis aos parâmetros parciais da requisião PATH;
                if(array_key_exists($input, $request->all())){
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate($regrasDinamicas);

        } else {
            $request->validate($cliente->regras());
        }

        // Preencher o objeto $marca com os dados do Request
        $cliente->fill($request->all());
        $cliente->save();

        return response()->json($cliente, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cliente = $this->cliente->find($id);

        if($cliente === null){
            return response()->json(['erro' => 'Impossível realizar a exclusão. O recurso solicitado não existe'], 404);
        }
       
        $cliente->delete();
        return response()->json(['msg' => 'O cliente foi removido com sucesso!'], 200);
    }
}

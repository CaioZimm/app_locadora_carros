<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Modelo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ModeloRepository;

class ModeloController extends Controller
{
    public function __construct(Modelo $modelo) {
        $this->modelo = $modelo;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modeloRepository = new ModeloRepository($this->modelo);

        // Filtros para Determinados Campos (id, nome, imagem);
        if ($request->has('atributos_marca')) {
            $atributos_marca = 'marca:id,'. $request->atributos_marca;
            $atributos_marca = $atributos_marca;
            $modeloRepository->selectAtributosRegistrosRelacionados($atributos_marca);
        } else {
            $modeloRepository->selectAtributosRegistrosRelacionados('marca');
        }

        if ($request->has('filtro')) {
            $modeloRepository->filtro($request->filtro);
        }

        if ($request->has('atributos')) {
            $modeloRepository->selectAtributos($request->atributos);
        } 

        // $this->modelo->with('marca')->get()
        return response()->json($modeloRepository->getResultado(), 200);

        // all() -> Ciando um objeto de consulta + get() = Collection
        // get() -> possibil de modificaridade a consulta -> Collection 
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
    public function store(Request $request)
    {
        $request->validate($this->modelo->regras()); // Stateless
 
        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens/modelos', 'public');

        $modelo = $this->modelo->create([
            'marca_id' => $request->marca_id,
            'nome' =>  $request->nome,
            'imagem' => $imagem_urn,
            'numero_portas' => $request->numero_portas,
            'lugares' => $request->lugares,
            'air_bag' => $request->air_bag,
            'abs' => $request->abs
        ]);

        return response()->json($modelo, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $modelo = $this->modelo->with('marca')->find($id);
        if($modelo === null){
            return response()->json(['erro' => 'Recurso pesquisado não existe'], 404); // Json
        }

        return response()->json($modelo, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Modelo $modelo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Modelo $modelo)
    {
        // $modelo = $this->modelo->find($id);

        if($modelo === null){
            return response()->json(['erro' => 'Impossível realizar a atualização. O recurso solicitado não existe'], 404);
        }

        if($request->method() === 'PATCH') {

            $regrasDinamicas = array();

            // Percorrer todas as regras definidas no Model;
            foreach ($modelo->regras() as $input => $regra) {
                // Coletar apenas as regras aplicáveis aos parâmetros parciais da requisião PATH;
                if(array_key_exists($input, $request->all())){
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate($regrasDinamicas);

        } else {
            $request->validate($modelo->regras());
        }

        // Remoção do arquivo antigo, caso um novo arquivo tenha sido envidao no Request;
        if($request->file('imagem')) {
            Storage::disk('public')->delete($modelo->imagem);
        }
        
        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens/modelos', 'public');
        
        $modelo->fill($request->all());
        $modelo->imagem = $imagem_urn;
        $modelo->save();

        // $modelo->update([
        //     'marca_id' => $request->marca_id,
        //     'nome' =>  $request->nome,
        //     'imagem' => $imagem_urn,
        //     'numero_portas' => $request->numero_portas,
        //     'lugares' => $request->lugares,
        //     'air_bag' => $request->air_bag,
        //     'abs' => $request->abs
        // ]);

        return response()->json($modelo, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Modelo $modelo)
    {
        // $modelo = $this->modelo->find($id);

        if($modelo === null){
            return response()->json(['erro' => 'Impossível realizar a exclusão. O recurso solicitado não existe'], 404);
        }
        
        // Remoção do arquivo antigo
        Storage::disk('public')->delete($modelo->imagem);

        $modelo->delete();
        return response()->json(['msg' => 'O modelo foi removido com sucesso!'], 200);
    }
}
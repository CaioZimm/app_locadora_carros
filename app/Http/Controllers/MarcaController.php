<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Marca;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\MarcaRepository;

class MarcaController extends Controller
{
    public function __construct(Marca $marca) {
        $this->marca = $marca;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $marcaRepository = new MarcaRepository($this->marca);

        if ($request->has('atributos_modelos')) {
            $atributos_modelos = 'modelos:id,'.$request->atributos_modelos;
            $atributos_modelos = $atributos_modelos;
            $marcaRepository->selectAtributosRegistrosRelacionados($atributos_modelos);
        } else {
            $marcaRepository->selectAtributosRegistrosRelacionados('modelos');
        }
        
        if ($request->has('filtro')) {
            $marcaRepository->filtro($request->filtro);
        }

        if ($request->has('atributos')) {
            $marcaRepository->selectAtributos($request->atributos);
        } 

        return response()->json($marcaRepository->getResultado(), 200);

        // ------------------------------------------------------------------------------ //

        /* $marcas = array();

        if ($request->has('atributos_modelos')) {
            $atributos_modelos = $request->atributos_modelos;
            $marcas = $this->marca->with('modelos:id,'.$atributos_modelos);
        } else {
            $marcas = $this->marca->with('modelos');
        }

        if ($request->has('filtro')) {
            
            $filtros = explode(';', $request->filtro);

            foreach ($filtros as $key => $condicao) {
                $c = explode(':', $condicao);
                $marcas = $marcas->where($c[0], $c[1], $c[2]);
            }
        }

        if ($request->has('atributos')) {
            $atributos = $request->atributos;
            $marcas = $marcas->selectRaw($atributos)->get();
        } else {
            $marcas = $marcas->get();
        } */
        
        // $marcas = Marca::all();
        // $marca = $this->marca->with('modelos')->get();
        // return response()->json($marcas, 200);
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
        // $marca = Marca::create($request->all());

        // Tratativa dos parâmetros Nome e Imagem;
        // $regras = [
        //     'nome' => 'required | unique:marcas | min:3',
        //     'imagem' => 'required'
        // ];
        // $feedback = [
        //     'required' => 'O campo :attribute é obrigatório',
        //     'nome.unique' => 'O nome da marca já existe',
        //     'nome.min' => 'O nome deve ter no mínimo 3 caracteres'
        // ];

        $request->validate($this->marca->regras(), $this->marca->feedback()); // Stateless
 
        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens', 'public');

        $marca = $this->marca->create([
            'nome' =>  $request->nome,
            'imagem' => $imagem_urn
        ]);

        // Outro Metodo de Inserção para a Marca
        // $marca->nome = $request->nome;
        // $marca->imagem = $imagem_urn;
        // $marca->save();

        return response()->json($marca, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $marca = $this->marca->with('modelos')->find($id);
        if($marca === null){
            return response()->json(['erro' => 'Recurso pesquisado não existe'], 404); // Json
        }

        return response()->json($marca, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Marca $marca)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        /*
        print_r($request->all()); //Os dados atualizados;
        echo '<hr>';
        print_r($marca->getAttributes()); //Os dados antigos;
        */

        // $marca->update($request->all());

        $marca = $this->marca->find($id);

        if($marca === null){
            return response()->json(['erro' => 'Impossível realizar a atualização. O recurso solicitado não existe'], 404);
        }

        if($request->method() === 'PATCH') {

            $regrasDinamicas = array();

            // Percorrer todas as regras definidas no Model;
            foreach ($marca->regras() as $input => $regra) {
                // Coletar apenas as regras aplicáveis aos parâmetros parciais da requisião PATH;
                if(array_key_exists($input, $request->all())){
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate($regrasDinamicas, $marca->feedback());

        } else {
            $request->validate($marca->regras(), $marca->feedback());
        }

        // Remoção do arquivo antigo, caso um novo arquivo tenha sido envidao no Request;
        if($request->file('imagem')) {
            Storage::disk('public')->delete($marca->imagem);
        }
        
        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens', 'public');


        // Preencher o objeto $marca com os dados do Request
        $marca->fill($request->all());
        $marca->imagem = $imagem_urn;
        $marca->save();

        /*
        $marca->update([
            'nome' =>  $request->nome,
            'imagem' => $imagem_urn
        ]);
        */
        
        return response()->json($marca, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $marca = $this->marca->find($id);

        if($marca === null){
            return response()->json(['erro' => 'Impossível realizar a exclusão. O recurso solicitado não existe'], 404);
        }
        
        // Remoção do arquivo antigo
        Storage::disk('public')->delete($marca->imagem);

        $marca->delete();
        return response()->json(['msg' => 'A marca foi removida com sucesso!'], 200);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;
    protected $fillable = ['nome', 'imagem'];

    public function regras() {
        return [
            'nome' => ['required', 'unique:marcas,nome,'.$this->id.'', 'min:3'],
            'imagem' => ['required', 'file', 'mimes:png']
        ];
        /*
            Parâmetros do método de validação Unique;
            1) Tabela; 
            2) Nome da coluna que será pesquisada na tabale;
            3) Id do registro que será desconsiderado na pesquisa;
        */
    }

    public function feedback() {
        return [
            'required' => 'O campo :attribute é obrigatório',
            'nome.unique' => 'O nome da marca já existe',
            'nome.min' => 'O nome deve ter no mínimo 3 caracteres',
            'imagem.mimes' => 'O aruivo deve ser uma imagem do tipo PNG'
        ];
    }

    public function modelos() {
        // UMA marca pode POSSUIR MUITOS modelo
        return $this->hasMany('App\Models\Modelo');
    }
}

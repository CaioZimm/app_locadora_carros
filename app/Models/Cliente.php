<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = ['nome'];

    public function regras() {
        return [
            'nome' => 'required'
        ];
    }

    // public function locacao() {
    //     return $this->hasMany('App\Models\Locacao');
    // }
}

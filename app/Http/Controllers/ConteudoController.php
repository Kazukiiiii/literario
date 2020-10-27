<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conteudo;
use App\Models\Area;
use Illuminate\Support\Facades\Storage;

class ConteudoController extends Controller
{
    function convertAreaToArray(Area $area) {
        return ['id'=>$area->id,'nome'=>$area->nome,'descricao'=>$area->descricao,'img'=>$area->img,'icone'=>$area->icone];
    }
    
    function carregarPaginaSecoes(Request $req) {
        $professorAssociado = Conteudo::select('id')->where('id_professor',auth()->user()->id)->get();
        if ($professorAssociado=='[]'){
        $conteudo = new Conteudo; 
        $conteudo->id_professor = auth()->user()->id;
        $conteudo -> save();
        }
        $idConteudo = Conteudo::select('id')->where('id_professor',auth()->user()->id)->get();
        $areasFixas = [['nome'=>'Literatura','nivel'=>1,'icone'=>'book'],['nome'=>'Gramática','nivel'=>1,'icone'=>'book'],['nome'=>'Redação','nivel'=>1,'icone'=>'book']];

        // foreach ($areasFixas as $campo => $valor)
        // {
        //     echo $areasFixas['nome']
        //     $area = New Area();
        //     $area->nome = $areasFixas['nome'];
        //     $area->nivel = $areasFixas['nivel'];
        //     $area->icone=$areasFixas['icone'];
        //     $area->id_conteudo = $idConteudo;
        //     $area->save();
        // }
            

        $req->session()->put('perfil','professor');
        if (session('perfil') == 'professor') {
            $menus = [];
            foreach(Area::where('nivel',1)->get() as $area) {
                array_push($menus, $this->convertAreaToArray($area));
            }
            array_unshift($menus, ['id'=>1,'nome'=>'Estatística','icone'=>'timeline']); //Estatística não ficará no banco, pois não segue a estrutura de módulos -> exceção

            
            $menus = array_map(function ($menu) {
                $conteudos = [];
                foreach(Area::where('nivel',2)->where('id_area_relacionada',$menu['id'])->get() as $area) {
                    array_push($conteudos, $this->convertAreaToArray($area));
                }
                $menu['conteudo'] = $conteudos;
                return $menu;
            }, $menus);

            return view('professor.principal',['qtdNotificacoes'=>'5','menus'=>$menus,'css'=>'principal']);
        } else {
            return view('aluno.principal');
        }
    }

    function carregarPaginaSubsecoes($idArea, Request $req) {
        $req->session()->put('perfil','professor');
        if (session('perfil') == 'professor') {
            $menus = [];
            if ($idArea == '2') {
                $conteudosSec1T = [['id'=>1,'nome'=>'Conteúdo 1','descricao'=>'Descrição do conteúdo'],['id'=>2,'nome'=>'Conteúdo 2','descricao'=>'Descrição do conteúdo'],['id'=>3,'nome'=>'Conteúdo 3','descricao'=>'Descrição do conteúdo'],['id'=>4,'nome'=>'Conteúdo 4','descricao'=>'Descrição do conteúdo']];
                $subsecoesT = [['id'=>1,'nome'=>'Fase N','conteudo'=>$conteudosSec1T]];
                array_push($menus, ['id'=>1,'nome'=>'Trovadorismo','icone'=>'book','subsecao'=>$subsecoesT]);
                $conteudosSec1H = [['id'=>1,'nome'=>'Conteúdo 1','descricao'=>'Descrição do conteúdo']];
                $subsecoesH = [['id'=>1,'nome'=>'Fase N','conteudo'=>$conteudosSec1H]];
                array_push($menus, ['id'=>2,'nome'=>'Humanismo','icone'=>'book','subsecao'=>$subsecoesH]);
                $conteudosSec1Q = [['id'=>1,'nome'=>'Conteúdo 1','descricao'=>'Descrição do conteúdo']];
                $subsecoesQ = [['id'=>1,'nome'=>'Fase N','conteudo'=>$conteudosSec1Q]];
                array_push($menus, ['id'=>3,'nome'=>'Quinhentismo','icone'=>'book','subsecao'=>$subsecoesQ]);
            }

            return view('professor.conteudo',['qtdNotificacoes'=>'5','menus'=>$menus,'css'=>'conteudo','idArea'=>$idArea]);
        } else {
            return view('aluno.conteudo');
        }
    }
    
    function cadastrarSecao($idArea, Request $req) {
        $area = new Area();
        $idConteudo = Conteudo::select('id')->where('id_professor',auth()->user()->id)->get()->first();
        $area->nome=$req->input('nome');
        $area->descricao = $req->input('descricao');
        $area->id_conteudo = $idConteudo['id'];
        $md5Name = md5_file($req->file('img')->getRealPath());
        $guessExtension = $req->file('img')->guessExtension();
        $file = $req->file('img')->storeAs('images', $md5Name.'.'.$guessExtension);
        $area->img = $file;
        $area->nivel = 2;
        $area->icone='book';
        $area->id_area_relacionada = $idArea;
        $area->save();
        return redirect(url()->previous());
    }

    function cadastrarSubsecao($idArea, $idSecao, Request $req) {
        $nome = $req->input('nome');
        return redirect(url()->previous());
    }

    function cadastrarConteudo($idArea, $idSecao, $idSubsecao, Request $req) {
        $nome = $req->input('nome');
        $descricao = $req->input('descricao');
        $md5Name = md5_file($req->file('arq')->getRealPath());
        $guessExtension = $req->file('arq')->guessExtension();
        $file = $req->file('arq')->storeAs('storage', $md5Name.'.'.$guessExtension);
        return redirect(url()->previous());
    }

    function baixarConteudo($idArea, $idSecao, $idSubsecao, $idConteudo) {
        $fileUrl = '9930843dfed0d94ffe8ccb8158ce6baa.pdf';
        return Storage::download(Storage::url($fileUrl));
    }
}
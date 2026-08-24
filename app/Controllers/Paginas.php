<?php

class Paginas extends Controller{
    public function index(){
        $dados = ['titulo'=>"Início",
                  'descricao' => ""
        ];
        $this->view('paginas/inicio', $dados);
    }//fim da função index

    public function sugestoes(){
        $dados = ['titulo'=>"RDD -Sugestões",
                  'descricao'=>"RDD - Sugestões"
        ];
        $this->view('paginas/sugestoes', $dados);
    }//fim da função sugestoes
    public function contato(){
        $dados = ['titulo'=>"Contato",
                  'descricao'=>"Página de Contatos"
        ];
        $this->view('paginas/contato', $dados);
    }//fim da função sobre

}//fim da classe Paginas

?>
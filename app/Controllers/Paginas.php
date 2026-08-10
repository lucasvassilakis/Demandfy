<?php

class Paginas extends Controller{
    public function index(){
        $dados = ['titulo'=>"Página Inicial",
                  'descricao' => "Aula de PW com  3º matutino, php orientado a objetos"
        ];
        $this->view('paginas/home', $dados);
    }//fim da função index

    public function sobre(){
        $dados = ['titulo'=>"Página Sobre nós",
                  'descricao'=>"Aqui você encontra as noticias mais confiaveis..."
        ];
        $this->view('paginas/sobre', $dados);
    }//fim da função sobre
    public function contato(){
        $dados = ['titulo'=>"Contato",
                  'descricao'=>"Página de Contatos"
        ];
        $this->view('paginas/contato', $dados);
    }//fim da função sobre

}//fim da classe Paginas

?>
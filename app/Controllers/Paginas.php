<?php

class Paginas extends Controller
{
    public function index()
    {
        $dados = [
            'titulo' => 'Início',
            'descricao' => '',
        ];

        $this->view('paginas/inicio', $dados);
    }

    public function sugestoes()
    {
        $dados = [
            'titulo' => 'RDD -Sugestões',
            'descricao' => 'RDD - Sugestões',
        ];

        $this->view('paginas/sugestoes', $dados);
    }

    public function calendario()
    {
        $dados = [
            'titulo' => 'Calendário',
            'descricao' => 'Calendário de Eventos',
        ];

        $this->view('paginas/calendario', $dados);
    }

    public function avisos()
    {
        $dados = [
            'titulo' => 'Avisos',
            'descricao' => 'Avisos e Comunicados',
        ];

        $this->view('paginas/avisos', $dados);
    }

    public function notificacoes()
    {
        $dados = [
            'titulo' => 'Notificações',
            'descricao' => 'Notificações do Sistema',
        ];

        $this->view('paginas/notificacoes', $dados);
    }

    public function perfil()
    {
        $dados = [
            'titulo' => 'Perfil',
            'descricao' => 'Página de Perfil',
        ];

        $this->view('paginas/perfil', $dados);
    }

    public function configuracoes()
    {
        $dados = [
            'titulo' => 'Configurações',
            'descricao' => 'Página de Configurações',
        ];

        $this->view('paginas/configuracoes', $dados);
    }

    public function login()
    {
        $dados = [
            'titulo' => 'Login',
            'descricao' => 'Página de Login',
        ];

        $this->view('paginas/login', $dados);
    }

    public function cadastro()
    {
        $dados = [
            'titulo' => 'Cadastro',
            'descricao' => 'Página de Cadastro',
        ];

        $this->view('paginas/cadastro', $dados);
    }

    public function contato()
    {
        $dados = [
            'titulo' => 'Contato',
            'descricao' => 'Página de Contatos',
        ];

        $this->view('paginas/contato', $dados);
    }
}
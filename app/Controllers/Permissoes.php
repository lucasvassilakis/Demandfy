<?php

class Permissoes extends Controller
{
    private $permissaoModel;

    public function __construct()
    {
        $this->permissaoModel = $this->model('Permissao');
    }

    public function atualizar()
    {
        $this->exigirLogin();
        $this->exigirPost('');
        $this->validarCsrf('');

        $usuarioId = filter_var(Entrada::post('usuario_id'), FILTER_VALIDATE_INT);
        $permissao = Entrada::post('permissao');
        $acao = Entrada::post('acao');

        if ($usuarioId === false || $usuarioId <= 0
            || !is_string($permissao)
            || !in_array($acao, ['conceder', 'revogar'], true)) {
            Sessao::mensagem('geral', 'Dados de permissão inválidos.', 'erro');
            Url::redirecionar('');
        }

        $atualizado = $this->permissaoModel->atualizar(
            Sessao::usuarioId(),
            Sessao::ehAdministrador(),
            $usuarioId,
            $permissao,
            $acao === 'conceder'
        );

        if (!$atualizado) {
            Sessao::mensagem('geral', 'Você não pode alterar essa permissão ou o usuário não existe.', 'erro');
            Url::redirecionar('');
        }

        Sessao::mensagem('geral', 'Permissão atualizada com sucesso.');
        Url::redirecionar('');
    }
}

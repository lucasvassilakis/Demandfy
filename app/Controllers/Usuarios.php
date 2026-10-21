<?php
/*
 * CONTROLLER USUARIOS
 * ------------------------------------------------------------------
 * Baseado no controller Usuarios do framework2026 (cadastrar, login, sair),
 * agora com perfil e configurações (troca de senha).
 *
 * URLs (algumas são atalhos definidos em Libraries/Rota.php):
 *   /cadastro       -> cadastrar()
 *   /login          -> login()
 *   /sair           -> sair()
 *   /perfil         -> perfil()
 *   /configuracoes  -> configuracoes()
 *
 * Padrão usado nos formulários:
 *   - GET  : mostra o formulário vazio;
 *   - POST : valida; se houver erro, mostra o formulário de novo com as mensagens
 *            e os valores digitados; se der certo, salva e REDIRECIONA
 *            (evita reenviar o formulário ao apertar F5).
 */
class Usuarios extends Controller
{
    private $usuarioModel;
    private $notificacaoModel;

    public function __construct()
    {
        $this->usuarioModel = $this->model('Usuario');
        $this->notificacaoModel = $this->model('Notificacao');
    }

    // ------------------------------------------------------------------
    // CADASTRO
    // ------------------------------------------------------------------
    public function cadastrar()
    {
        // Quem já está logado não precisa se cadastrar.
        if (Sessao::estaLogado()) {
            Url::redirecionar('');
        }

        $dados = [
            'titulo'  => 'Cadastro',
            'valores' => ['nome' => '', 'email' => '', 'telefone' => ''],
            'erros'   => [],
        ];

        if (Entrada::ehPost()) {
            // Guardamos o que a pessoa digitou para preencher o formulário de novo se houver erro.
            $dados['valores'] = [
                'nome'     => Entrada::post('nome'),
                'email'    => mb_strtolower(Entrada::post('email')),
                'telefone' => Entrada::post('telefone'),
            ];
            // Senhas não são "aparadas" (trim) nem devolvidas ao formulário.
            $senha = isset($_POST['senha']) && is_string($_POST['senha']) ? $_POST['senha'] : '';
            $confirma = isset($_POST['confirma_senha']) && is_string($_POST['confirma_senha']) ? $_POST['confirma_senha'] : '';
            $v = $dados['valores'];
            $telefone = Checa::soDigitos($v['telefone']);

            if (!Csrf::validar()) {
                $dados['erros']['geral'] = 'Formulário expirado. Recarregue a página e tente novamente.';
            } else {
                // --- Validações (uma mensagem por campo) ---
                if ($v['nome'] === '') {
                    $dados['erros']['nome'] = 'Preencha o campo nome.';
                } elseif (!Checa::nomeValido($v['nome'])) {
                    $dados['erros']['nome'] = 'O nome informado é inválido.';
                }

                if ($v['email'] === '') {
                    $dados['erros']['email'] = 'Preencha o campo e-mail.';
                } elseif (!Checa::emailValido($v['email'])) {
                    $dados['erros']['email'] = 'O e-mail informado é inválido.';
                } elseif ($this->usuarioModel->emailExiste($v['email'])) {
                    $dados['erros']['email'] = 'Este e-mail já está cadastrado.';
                }

                if ($v['telefone'] === '') {
                    $dados['erros']['telefone'] = 'Preencha o campo telefone.';
                } elseif (!Checa::telefoneValido($v['telefone'])) {
                    $dados['erros']['telefone'] = 'Informe o telefone com DDD (10 ou 11 números).';
                } elseif ($this->usuarioModel->telefoneExiste($telefone)) {
                    $dados['erros']['telefone'] = 'Este telefone já está cadastrado.';
                }

                if ($senha === '') {
                    $dados['erros']['senha'] = 'Preencha o campo senha.';
                } elseif (strlen($senha) < 6) {
                    $dados['erros']['senha'] = 'A senha deve ter no mínimo 6 caracteres.';
                }

                if ($confirma === '') {
                    $dados['erros']['confirma_senha'] = 'Confirme a senha.';
                } elseif ($senha !== $confirma) {
                    $dados['erros']['confirma_senha'] = 'As senhas são diferentes.';
                }

                // --- Sem erros: grava no banco ---
                if (empty($dados['erros'])) {
                    $novoId = $this->usuarioModel->armazenar([
                        'nome'     => $v['nome'],
                        'email'    => $v['email'],
                        'telefone' => $telefone,
                        // password_hash gera um hash seguro; a senha original nunca é guardada.
                        'senha'    => password_hash($senha, PASSWORD_DEFAULT),
                    ]);

                    if ($novoId) {
                        $this->notificacaoModel->criar(
                            $novoId,
                            'aviso',
                            'Bem-vindo ao Demandfy!',
                            'Seu cadastro foi concluído. Complete seu perfil e envie sua primeira demanda.'
                        );
                        Sessao::mensagem('geral', 'Cadastro realizado com sucesso! Faça login para entrar.');
                        Url::redirecionar('login');
                    }
                    $dados['erros']['geral'] = 'Não foi possível concluir o cadastro. Tente novamente.';
                }
            }
        }

        $this->viewSolo('usuarios/cadastrar', $dados);
    }

    // ------------------------------------------------------------------
    // LOGIN
    // ------------------------------------------------------------------
    public function login()
    {
        if (Sessao::estaLogado()) {
            Url::redirecionar('');
        }

        $dados = [
            'titulo'  => 'Login',
            'valores' => ['login' => ''],
            'erros'   => [],
        ];

        if (Entrada::ehPost()) {
            $login = Entrada::post('login');
            $senha = isset($_POST['senha']) && is_string($_POST['senha']) ? $_POST['senha'] : '';
            $dados['valores']['login'] = $login;

            if (!Csrf::validar()) {
                $dados['erros']['geral'] = 'Formulário expirado. Recarregue a página e tente novamente.';
            } elseif ($login === '' || $senha === '') {
                if ($login === '') {
                    $dados['erros']['login'] = 'Preencha o e-mail ou celular.';
                }
                if ($senha === '') {
                    $dados['erros']['senha'] = 'Preencha o campo senha.';
                }
            } else {
                $usuario = $this->usuarioModel->checarLogin(mb_strtolower($login), $senha);

                if ($usuario) {
                    Sessao::logar($usuario);
                    Url::redirecionar('');
                }
                // Mensagem genérica de propósito: não revela se o e-mail existe.
                $dados['erros']['geral'] = 'E-mail/celular ou senha inválidos.';
            }
        }

        $this->viewSolo('usuarios/login', $dados);
    }

    // ------------------------------------------------------------------
    // SAIR (logout)
    // ------------------------------------------------------------------
    public function sair()
    {
        Sessao::sair();
        Sessao::mensagem('geral', 'Você saiu do sistema.');
        Url::redirecionar('login');
    }

    // ------------------------------------------------------------------
    // PERFIL (ver e editar os próprios dados)
    // ------------------------------------------------------------------
    public function perfil()
    {
        $this->exigirLogin();

        $usuario = $this->usuarioModel->lerUsuarioPorId(Sessao::usuarioId());
        if (!$usuario) {
            // Usuário apagado do banco com a sessão ainda aberta: encerra.
            Sessao::sair();
            Url::redirecionar('login');
        }

        $dados = [
            'titulo'  => 'Perfil',
            'editando' => false, // true = formulário já abre liberado para edição (quando há erro)
            'valores' => [
                'nome'         => $usuario->nome,
                'email'        => $usuario->email,
                'telefone'     => Checa::telefoneFormatado($usuario->telefone),
                'instituicao'  => (string) $usuario->instituicao,
                'curso'        => (string) $usuario->curso,
                'ano_ingresso' => $usuario->ano_ingresso === null ? '' : (string) $usuario->ano_ingresso,
                'situacao'     => (string) $usuario->situacao,
                'matricula'    => (string) $usuario->matricula,
                'foto_perfil'  => (string) ($usuario->foto_perfil ?? ''),
            ],
            'erros'   => [],
        ];

        if (Entrada::ehPost()) {
            $this->validarCsrf('perfil');
            $fotoAtual = (string) ($usuario->foto_perfil ?? '');
            $fotoNova = null;
            $arquivoFoto = isset($_FILES['foto_perfil']) && is_array($_FILES['foto_perfil'])
                ? $_FILES['foto_perfil']
                : null;

            if ($arquivoFoto && $arquivoFoto['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($arquivoFoto['error'] === UPLOAD_ERR_INI_SIZE || $arquivoFoto['error'] === UPLOAD_ERR_FORM_SIZE
                    || ($arquivoFoto['error'] === UPLOAD_ERR_OK && $arquivoFoto['size'] > 5 * 1024 * 1024)) {
                    $dados['erros']['foto_perfil'] = 'A foto deve ter no máximo 5 MB.';
                } elseif ($arquivoFoto['error'] !== UPLOAD_ERR_OK) {
                    $dados['erros']['foto_perfil'] = 'Não foi possível enviar a foto. Tente novamente.';
                } elseif (!is_uploaded_file($arquivoFoto['tmp_name'])) {
                    $dados['erros']['foto_perfil'] = 'Arquivo de foto inválido.';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = $finfo ? finfo_file($finfo, $arquivoFoto['tmp_name']) : false;
                    if ($finfo) {
                        finfo_close($finfo);
                    }

                    $extensoes = [
                        'image/jpeg' => 'jpg',
                        'image/png'  => 'png',
                        'image/webp' => 'webp',
                    ];
                    $informacoesImagem = @getimagesize($arquivoFoto['tmp_name']);
                    if (!isset($extensoes[$mime]) || $informacoesImagem === false || $informacoesImagem['mime'] !== $mime) {
                        $dados['erros']['foto_perfil'] = 'Envie uma imagem JPG, PNG ou WebP válida.';
                    } else {
                        $diretorioFotos = dirname(__DIR__, 2) . '/public/uploads/perfis';
                        if (!is_dir($diretorioFotos) && !mkdir($diretorioFotos, 0755, true) && !is_dir($diretorioFotos)) {
                            $dados['erros']['foto_perfil'] = 'Não foi possível preparar o armazenamento da foto.';
                        } else {
                            $nomeFoto = bin2hex(random_bytes(16)) . '.' . $extensoes[$mime];
                            $destinoFoto = $diretorioFotos . '/' . $nomeFoto;
                            if (move_uploaded_file($arquivoFoto['tmp_name'], $destinoFoto)) {
                                $fotoNova = 'uploads/perfis/' . $nomeFoto;
                                $dados['valores']['foto_perfil'] = $fotoNova;
                            } else {
                                $dados['erros']['foto_perfil'] = 'Não foi possível salvar a foto enviada.';
                            }
                        }
                    }
                }
            }

            $v = [
                'nome'         => Entrada::post('nome'),
                'email'        => mb_strtolower(Entrada::post('email')),
                'telefone'     => Entrada::post('telefone'),
                'instituicao'  => Entrada::post('instituicao'),
                'curso'        => Entrada::post('curso'),
                'ano_ingresso' => Entrada::post('ano_ingresso'),
                'situacao'     => Entrada::post('situacao'),
                'matricula'    => Entrada::post('matricula'),
                'foto_perfil'  => $fotoNova === null ? $fotoAtual : $fotoNova,
            ];
            $dados['valores'] = $v;
            $dados['editando'] = true;
            $telefone = Checa::soDigitos($v['telefone']);
            $id = Sessao::usuarioId();

            if ($v['nome'] === '' || !Checa::nomeValido($v['nome'])) {
                $dados['erros']['nome'] = 'Informe um nome válido.';
            }

            if (!Checa::emailValido($v['email'])) {
                $dados['erros']['email'] = 'Informe um e-mail válido.';
            } elseif ($this->usuarioModel->emailExiste($v['email'], $id)) {
                $dados['erros']['email'] = 'Este e-mail já está em uso.';
            }

            if (!Checa::telefoneValido($v['telefone'])) {
                $dados['erros']['telefone'] = 'Informe o telefone com DDD (10 ou 11 números).';
            } elseif ($this->usuarioModel->telefoneExiste($telefone, $id)) {
                $dados['erros']['telefone'] = 'Este telefone já está em uso.';
            }

            // Ano de ingresso é opcional, mas se vier deve ser um ano razoável.
            $ano = null;
            if ($v['ano_ingresso'] !== '') {
                $ano = ctype_digit($v['ano_ingresso']) ? (int) $v['ano_ingresso'] : 0;
                if ($ano < 1990 || $ano > (int) date('Y') + 1) {
                    $dados['erros']['ano_ingresso'] = 'Ano inválido.';
                }
            }

            // Limites de tamanho dos campos de texto (batem com o banco).
            foreach (['instituicao' => 120, 'curso' => 120, 'situacao' => 30, 'matricula' => 30] as $campo => $max) {
                if (mb_strlen($v[$campo]) > $max) {
                    $dados['erros'][$campo] = 'Máximo de ' . $max . ' caracteres.';
                }
            }

            if (empty($dados['erros'])) {
                $ok = $this->usuarioModel->atualizarPerfil($id, [
                    'nome'         => $v['nome'],
                    'email'        => $v['email'],
                    'telefone'     => $telefone,
                    'instituicao'  => $v['instituicao'],
                    'curso'        => $v['curso'],
                    'ano_ingresso' => $ano,
                    'situacao'     => $v['situacao'],
                    'matricula'    => $v['matricula'],
                    'foto_perfil'  => $fotoNova === null ? $fotoAtual : $fotoNova,
                ]);

                if ($ok) {
                    if ($fotoNova !== null && preg_match('~^uploads/perfis/[a-f0-9]{32}\.(?:jpg|png|webp)$~D', $fotoAtual)) {
                        $caminhoFotoAntiga = dirname(__DIR__, 2) . '/public/' . $fotoAtual;
                        if (is_file($caminhoFotoAntiga) && !unlink($caminhoFotoAntiga)) {
                            error_log('Demandfy - não foi possível remover a foto antiga do perfil.');
                        }
                    }
                    // Mantém a sessão em dia com o nome/e-mail novos.
                    $_SESSION['usuario_nome'] = $v['nome'];
                    $_SESSION['usuario_email'] = $v['email'];
                    Sessao::mensagem('geral', 'Perfil atualizado com sucesso!');
                    Url::redirecionar('perfil');
                }
                if ($fotoNova !== null && is_file($destinoFoto) && !unlink($destinoFoto)) {
                    error_log('Demandfy - não foi possível remover a foto de perfil enviada após falha no banco.');
                }
                $dados['valores']['foto_perfil'] = $fotoAtual;
                $dados['erros']['geral'] = 'Não foi possível salvar o perfil.';
            }
            if (!empty($dados['erros']) && $fotoNova !== null && is_file($destinoFoto)) {
                if (!unlink($destinoFoto)) {
                    error_log('Demandfy - não foi possível remover a nova foto de perfil após falha na validação.');
                }
                $dados['valores']['foto_perfil'] = $fotoAtual;
            }
        }

        $this->view('usuarios/perfil', $dados);
    }

    // ------------------------------------------------------------------
    // CONFIGURAÇÕES (troca de senha)
    // ------------------------------------------------------------------
    public function configuracoes()
    {
        $this->exigirLogin();

        $dados = [
            'titulo' => 'Configurações',
            'erros'  => [],
        ];

        if (Entrada::ehPost()) {
            $this->validarCsrf('configuracoes');

            $atual = isset($_POST['senha_atual']) && is_string($_POST['senha_atual']) ? $_POST['senha_atual'] : '';
            $nova = isset($_POST['nova_senha']) && is_string($_POST['nova_senha']) ? $_POST['nova_senha'] : '';
            $confirma = isset($_POST['confirma_senha']) && is_string($_POST['confirma_senha']) ? $_POST['confirma_senha'] : '';

            $usuario = $this->usuarioModel->lerUsuarioPorId(Sessao::usuarioId());

            if ($atual === '') {
                $dados['erros']['senha_atual'] = 'Informe a senha atual.';
            } elseif (!$usuario || !password_verify($atual, $usuario->senha)) {
                $dados['erros']['senha_atual'] = 'A senha atual está incorreta.';
            }

            if (strlen($nova) < 6) {
                $dados['erros']['nova_senha'] = 'A nova senha deve ter no mínimo 6 caracteres.';
            }

            if ($nova !== $confirma) {
                $dados['erros']['confirma_senha'] = 'As senhas são diferentes.';
            }

            if (empty($dados['erros'])) {
                $this->usuarioModel->atualizarSenha($usuario->id, password_hash($nova, PASSWORD_DEFAULT));
                Sessao::mensagem('geral', 'Senha alterada com sucesso!');
                Url::redirecionar('configuracoes');
            }
        }

        $this->view('usuarios/configuracoes', $dados);
    }
}

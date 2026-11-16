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
    const USUARIOS_POR_PAGINA = 5;

    private $usuarioModel;
    private $notificacaoModel;

    public function __construct()
    {
        $this->usuarioModel = $this->model('Usuario');
        $this->notificacaoModel = $this->model('Notificacao');
    }

    public function index()
    {
        $this->exigirLogin();
        $ehAdministrador = Sessao::ehAdministrador();
        if (!$ehAdministrador && !Sessao::ehModerador()) {
            Sessao::mensagem('geral', 'Você não tem permissão para visualizar os usuários.', 'erro');
            Url::redirecionar('');
        }

        $busca = isset($_GET['busca']) && is_string($_GET['busca'])
            ? trim(mb_substr($_GET['busca'], 0, 100))
            : '';
        $perfil = isset($_GET['perfil']) && is_string($_GET['perfil'])
            && in_array($_GET['perfil'], ['admin', 'padrao'], true)
            ? $_GET['perfil']
            : '';
        $permissaoModel = $this->model('Permissao');
        $total = $permissaoModel->contarUsuariosGerenciaveis($busca, $perfil);
        $totalPaginas = max(1, (int) ceil($total / self::USUARIOS_POR_PAGINA));
        $pagina = isset($_GET['pagina']) && is_scalar($_GET['pagina'])
            ? filter_var($_GET['pagina'], FILTER_VALIDATE_INT)
            : false;
        $pagina = $pagina === false ? 1 : min(max(1, (int) $pagina), $totalPaginas);
        $usuarioAtual = Sessao::usuarioId();
        $usuarios = $permissaoModel->listarUsuariosGerenciaveis(
            self::USUARIOS_POR_PAGINA,
            ($pagina - 1) * self::USUARIOS_POR_PAGINA,
            $busca,
            $perfil,
            $ehAdministrador ? $usuarioAtual : 0
        );
        foreach ($usuarios as $usuario) {
            $partesNome = preg_split('/\s+/u', trim($usuario->nome), -1, PREG_SPLIT_NO_EMPTY);
            $usuario->iniciais = $partesNome
                ? mb_strtoupper(mb_substr($partesNome[0], 0, 1)
                    . (count($partesNome) > 1 ? mb_substr($partesNome[count($partesNome) - 1], 0, 1) : ''))
                : '?';
        }

        $parametros = [];
        if ($busca !== '') {
            $parametros['busca'] = $busca;
        }
        if ($perfil !== '') {
            $parametros['perfil'] = $perfil;
        }
        $queryFiltros = http_build_query($parametros);

        $this->view('usuarios/index', [
            'titulo' => 'Usuários',
            'usuarios' => $usuarios,
            'total' => $total,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'deslocamento' => ($pagina - 1) * self::USUARIOS_POR_PAGINA,
            'busca' => $busca,
            'perfil' => $perfil,
            'queryFiltros' => $queryFiltros,
            'podeGerenciarNiveis' => $ehAdministrador,
            'usuarioAtual' => $usuarioAtual,
        ]);
    }

    public function alterarNivel()
    {
        $this->exigirLogin();
        $this->exigirPost('usuarios');
        $this->validarCsrf('usuarios');

        if (!Sessao::ehAdministrador()) {
            Sessao::mensagem('geral', 'Somente administradores podem alterar o perfil de usuário.', 'erro');
            Url::redirecionar('usuarios');
        }

        $usuarioId = isset($_POST['usuario_id']) && is_scalar($_POST['usuario_id'])
            ? filter_var($_POST['usuario_id'], FILTER_VALIDATE_INT)
            : false;
        $nivel = isset($_POST['nivel']) && is_string($_POST['nivel']) ? $_POST['nivel'] : '';
        if ($usuarioId === false || $usuarioId <= 0 || !in_array($nivel, ['admin', 'padrao'], true)) {
            Sessao::mensagem('geral', 'Os dados do perfil selecionado são inválidos.', 'erro');
            Url::redirecionar('usuarios');
        }

        $atorId = Sessao::usuarioId();
        $permissaoModel = $this->model('Permissao');
        if (!$permissaoModel->alterarNivelAdministrador($atorId, $usuarioId, $nivel)) {
            Sessao::mensagem('geral', 'Não foi possível alterar o perfil deste usuário.', 'erro');
            Url::redirecionar('usuarios?' . $this->queryRetornoUsuarios());
        }

        if ($usuarioId === $atorId && $nivel === 'padrao') {
            Sessao::definirAdministrador(false);
            Sessao::mensagem('geral', 'Seu perfil foi alterado para usuário padrão.');
            Url::redirecionar('');
        }

        Sessao::mensagem('geral', 'Perfil do usuário atualizado com sucesso.');
        Url::redirecionar('usuarios?' . $this->queryRetornoUsuarios());
    }

    public function excluir()
    {
        $this->exigirLogin();
        $this->exigirPost('usuarios');
        $this->validarCsrf('usuarios');

        if (!Sessao::ehAdministrador()) {
            Sessao::mensagem('geral', 'Somente administradores podem excluir usuários.', 'erro');
            Url::redirecionar('usuarios');
        }

        $usuarioId = isset($_POST['usuario_id']) && is_scalar($_POST['usuario_id'])
            ? filter_var($_POST['usuario_id'], FILTER_VALIDATE_INT)
            : false;
        if ($usuarioId === false || $usuarioId <= 0) {
            Sessao::mensagem('geral', 'O usuário selecionado é inválido.', 'erro');
            Url::redirecionar('usuarios');
        }

        $resultado = $this->model('Permissao')->excluirUsuarioAdministrador(
            Sessao::usuarioId(),
            (int) $usuarioId
        );
        if ($resultado['status'] === 'excluido') {
            $fotoPerfil = (string) ($resultado['foto_perfil'] ?? '');
            if (preg_match('~^uploads/perfis/[a-f0-9]{32}\.(?:jpg|png|webp)$~D', $fotoPerfil)) {
                $caminhoFoto = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public'
                    . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $fotoPerfil);
                if (is_file($caminhoFoto) && !unlink($caminhoFoto)) {
                    error_log('Demandfy - não foi possível remover a foto de perfil do usuário excluído.');
                }
            }
            Sessao::mensagem('geral', 'Usuário excluído com sucesso.');
        } elseif ($resultado['status'] === 'proprio') {
            Sessao::mensagem('geral', 'Você não pode excluir a própria conta por esta tela.', 'erro');
        } elseif ($resultado['status'] === 'com_registros') {
            Sessao::mensagem(
                'geral',
                'Este usuário possui demandas, sugestões, avisos, instituições, respostas ou histórico vinculados e não pode ser excluído.',
                'erro'
            );
        } elseif ($resultado['status'] === 'inexistente') {
            Sessao::mensagem('geral', 'O usuário selecionado não foi encontrado.', 'erro');
        } else {
            Sessao::mensagem('geral', 'Não foi possível excluir este usuário.', 'erro');
        }

        Url::redirecionar('usuarios?' . $this->queryRetornoUsuarios());
    }

    private function queryRetornoUsuarios()
    {
        $parametros = [];
        if (isset($_POST['busca']) && is_string($_POST['busca'])) {
            $busca = trim(mb_substr($_POST['busca'], 0, 100));
            if ($busca !== '') {
                $parametros['busca'] = $busca;
            }
        }
        if (isset($_POST['perfil']) && is_string($_POST['perfil'])
            && in_array($_POST['perfil'], ['admin', 'padrao'], true)) {
            $parametros['perfil'] = $_POST['perfil'];
        }
        if (isset($_POST['pagina']) && is_scalar($_POST['pagina'])) {
            $pagina = filter_var($_POST['pagina'], FILTER_VALIDATE_INT);
            if ($pagina !== false && $pagina > 1) {
                $parametros['pagina'] = $pagina;
            }
        }
        return http_build_query($parametros);
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
                'email'    => Checa::emailPadrao(Entrada::post('email')),
                'telefone' => Entrada::post('telefone'),
            ];
            // Senhas não são "aparadas" (trim) nem devolvidas ao formulário.
            // A leitura agora passa por helper dedicado para padronizar a entrada e
            // centralizar a regra em um único ponto da aplicação.
            $senha = Entrada::postSenha('senha');
            $confirma = Entrada::postSenha('confirma_senha');
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
                    $dados['erros']['telefone'] = 'Telefone inválido. Use o formato (DD) 9 9999-9999 ou (DD) 9999-9999.';
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
                            'Bem-vindo ao Demandfy!',
                            'Seu cadastro foi concluído. Complete seu perfil e envie sua primeira demanda.'
                        );
                        $this->notificacaoModel->notificarModeradores(
                            'Novo usuário',
                            'Um novo usuário se cadastrou: ' . $v['nome'] . '.',
                            $novoId
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
            $senha = Entrada::postSenha('senha');
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
                $usuario = $this->usuarioModel->checarLogin(Checa::emailPadrao($login), $senha);

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

    public function esqueciSenha()
    {
        if (Sessao::estaLogado()) {
            Url::redirecionar('');
        }

        $dados = [
            'titulo'  => 'Recuperar senha',
            'valores' => ['login' => ''],
            'erros'   => [],
        ];

        if (Entrada::ehPost()) {
            $this->validarCsrf('esqueci-senha');

            $login = Entrada::post('login');
            $dados['valores']['login'] = $login;

            if ($login === '') {
                $dados['erros']['login'] = 'Informe seu e-mail ou celular.';
            } else {
                require_once APP . '/Libraries/EmailService.php';

                if (!\App\Libraries\EmailService::estaConfigurado()) {
                    $dados['erros']['geral'] = 'O serviço de e-mail não está configurado. Configure o SMTP e tente novamente.';
                } else {
                    $usuario = $this->usuarioModel->buscarPorEmailOuTelefone($login);

                    if ($usuario) {
                        $token = bin2hex(random_bytes(32));
                        $tokenHash = hash('sha256', $token);
                        $expiraEm = date('Y-m-d H:i:s', time() + 3600);

                        if (!$this->usuarioModel->armazenarTokenRedefinicao($usuario->id, $tokenHash, $expiraEm)) {
                            $dados['erros']['geral'] = 'Não foi possível iniciar a recuperação. Tente novamente.';
                        } else {
                            $emailService = new \App\Libraries\EmailService();
                            if (!$emailService->enviarRecuperacaoSenha($usuario->email, $token)) {
                                if (!$this->usuarioModel->invalidarTokenRedefinicao($usuario->id)) {
                                    error_log('Demandfy: não foi possível invalidar o token após falha no envio.');
                                }
                                $dados['erros']['geral'] = 'Não foi possível enviar o e-mail. Verifique a configuração SMTP e tente novamente.';
                            }
                        }
                    }

                    if (empty($dados['erros'])) {
                        Sessao::mensagem('geral', 'Se o e-mail ou celular estiver cadastrado, você receberá um link para redefinir a senha.');
                        Url::redirecionar('login');
                    }
                }
            }
        }

        $this->viewSolo('usuarios/esqueci-senha', $dados);
    }

    public function redefinirSenha()
    {
        if (Sessao::estaLogado()) {
            Url::redirecionar('');
        }

        $token = isset($_GET['token']) && is_string($_GET['token']) ? $_GET['token'] : '';
        $dados = [
            'titulo' => 'Nova senha',
            'token' => $token,
            'erros' => [],
        ];

        $tokenHash = preg_match('/\A[a-f0-9]{64}\z/', $token) ? hash('sha256', $token) : '';
        if ($tokenHash === '' || !$this->usuarioModel->tokenRedefinicaoValido($tokenHash)) {
            Sessao::mensagem('geral', 'Link de recuperação inválido ou expirado.', 'erro');
            Url::redirecionar('login');
        }

        if (Entrada::ehPost()) {
            $this->validarCsrf('redefinir-senha');

            $nova = Entrada::postSenha('nova_senha');
            $confirma = Entrada::postSenha('confirma_senha');

            if ($nova === '') {
                $dados['erros']['nova_senha'] = 'Informe a nova senha.';
            } elseif (strlen($nova) < 6) {
                $dados['erros']['nova_senha'] = 'A nova senha deve ter no mínimo 6 caracteres.';
            }

            if ($confirma === '') {
                $dados['erros']['confirma_senha'] = 'Confirme a nova senha.';
            } elseif ($nova !== $confirma) {
                $dados['erros']['confirma_senha'] = 'As senhas são diferentes.';
            }

            if (empty($dados['erros'])) {
                if ($this->usuarioModel->redefinirSenhaComToken(
                    $tokenHash,
                    password_hash($nova, PASSWORD_DEFAULT)
                )) {
                    Sessao::mensagem('geral', 'Senha redefinida com sucesso! Faça login com a nova senha.');
                    Url::redirecionar('login');
                }

                $dados['erros']['geral'] = 'O link é inválido, expirou ou já foi usado. Solicite um novo link.';
            }
        }

        $this->viewSolo('usuarios/redefinir-senha', $dados);
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

        $podeEditarFuncao = Sessao::ehAdministrador()
            || Sessao::temPermissao(Permissao::GERENCIAR_PERMISSOES);
        $funcaoGremio = (string) ($usuario->funcao_gremio ?? '');

        $dados = [
            'titulo'  => 'Perfil',
            'editando' => false, // true = formulário já abre liberado para edição (quando há erro)
            'mostrar_funcao' => $podeEditarFuncao,
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
                'funcao_gremio' => $funcaoGremio,
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
                'email'        => Checa::emailPadrao(Entrada::post('email')),
                'telefone'     => Entrada::post('telefone'),
                'instituicao'  => Entrada::post('instituicao'),
                'curso'        => Entrada::post('curso'),
                'ano_ingresso' => Entrada::post('ano_ingresso'),
                'situacao'     => (string) $usuario->situacao,
                'matricula'    => Entrada::post('matricula'),
                'foto_perfil'  => $fotoNova === null ? $fotoAtual : $fotoNova,
                'funcao_gremio' => $podeEditarFuncao
                    ? Entrada::post('funcao_gremio')
                    : $funcaoGremio,
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
                $dados['erros']['telefone'] = 'Telefone inválido. Use o formato (DD) 9 9999-9999 ou (DD) 9999-9999.';
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
            foreach (['instituicao' => 300, 'curso' => 200] as $campo => $max) {
                if (mb_strlen($v[$campo]) > $max) {
                    $dados['erros'][$campo] = 'Máximo de ' . $max . ' caracteres.';
                }
            }

            if (mb_strlen($v['funcao_gremio']) > 100) {
                $dados['erros']['funcao_gremio'] = 'Máximo de 100 caracteres.';
            }

            if ($v['matricula'] !== ''
                && (!ctype_digit($v['matricula']) || strlen($v['matricula']) > 13)) {
                $dados['erros']['matricula'] = 'Informe uma matrícula numérica de até 13 dígitos.';
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
                    'funcao_gremio' => $v['funcao_gremio'],
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

        $usuario = $this->usuarioModel->lerUsuarioPorId(Sessao::usuarioId());
        if (!$usuario) {
            Sessao::mensagem('geral', 'Não foi possível carregar as configurações da conta.', 'erro');
            Url::redirecionar('sair');
        }

        $dados = [
            'titulo'       => 'Configurações',
            'erros'        => [],
            'preferencias' => [
                'notificacao_email' => (bool) $usuario->notificacao_email,
                'notificacao_site'  => (bool) $usuario->notificacao_site,
                'tema'              => $usuario->tema,
            ],
        ];
        Sessao::definirTema($usuario->tema);

        if (Entrada::ehPost()) {
            $this->validarCsrf('configuracoes');

            $notificacaoEmail = Entrada::post('notificacao_email');
            $notificacaoSite = Entrada::post('notificacao_site');
            $dados['preferencias'] = [
                'notificacao_email' => $notificacaoEmail === ''
                    ? (bool) $usuario->notificacao_email
                    : $notificacaoEmail === '1',
                'notificacao_site'  => $notificacaoSite === '1',
                'tema'              => $usuario->tema,
            ];

            if (!in_array($notificacaoEmail, ['', '0', '1'], true)
                || !in_array($notificacaoSite, ['', '0', '1'], true)) {
                $dados['erros']['geral'] = 'As preferências de notificação são inválidas.';
            }
            $atual = Entrada::postSenha('senha_atual');
            $nova = Entrada::postSenha('nova_senha');
            $confirma = Entrada::postSenha('confirma_senha');
            $alterarSenha = $atual !== '' || $nova !== '' || $confirma !== '';

            if ($alterarSenha) {
                if ($atual === '') {
                    $dados['erros']['senha_atual'] = 'Informe a senha atual.';
                } elseif (!password_verify($atual, $usuario->senha)) {
                    $dados['erros']['senha_atual'] = 'A senha atual está incorreta.';
                }

                if (strlen($nova) < 6) {
                    $dados['erros']['nova_senha'] = 'A nova senha deve ter no mínimo 6 caracteres.';
                }

                if ($nova !== $confirma) {
                    $dados['erros']['confirma_senha'] = 'As senhas são diferentes.';
                }
            }

            if (empty($dados['erros'])) {
                $senhaHash = $alterarSenha ? password_hash($nova, PASSWORD_DEFAULT) : null;
                if ($this->usuarioModel->atualizarConfiguracoes(
                    $usuario->id,
                    $dados['preferencias'],
                    $senhaHash
                )) {
                    Sessao::definirTema($dados['preferencias']['tema']);
                    $mensagem = $alterarSenha
                        ? 'Configurações salvas e senha alterada com sucesso!'
                        : 'Configurações salvas com sucesso!';
                    Sessao::mensagem('geral', $mensagem);
                    Url::redirecionar('configuracoes');
                }
                $dados['erros']['geral'] = 'Não foi possível salvar as configurações. Tente novamente.';
            }
        }

        $this->view('usuarios/configuracoes', $dados);
    }

    public function excluirConta()
    {
        $this->exigirLogin();
        $this->exigirPost('configuracoes');
        $this->validarCsrf('configuracoes');

        $resultado = $this->model('Permissao')->excluirContaPropria(Sessao::usuarioId());
        if ($resultado['status'] === 'excluido') {
            $fotoPerfil = (string) ($resultado['foto_perfil'] ?? '');
            if (preg_match('~^uploads/perfis/[a-f0-9]{32}\.(?:jpg|png|webp)$~D', $fotoPerfil)) {
                $caminhoFoto = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public'
                    . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $fotoPerfil);
                if (is_file($caminhoFoto) && !unlink($caminhoFoto)) {
                    error_log('Demandfy - não foi possível remover a foto de perfil da conta excluída.');
                }
            }

            Sessao::sair();
            Sessao::mensagem('geral', 'Sua conta foi excluída com sucesso.');
            Url::redirecionar('login');
        }

        if ($resultado['status'] === 'com_registros') {
            Sessao::mensagem(
                'geral',
                'Sua conta não pode ser excluída porque há demandas, sugestões, avisos, instituições, respostas ou histórico vinculados. Remova esses registros antes de tentar novamente.',
                'erro'
            );
        } else {
            Sessao::mensagem('geral', 'Não foi possível excluir sua conta. Atualize a página e tente novamente.', 'erro');
        }

        Url::redirecionar('configuracoes');
    }
}

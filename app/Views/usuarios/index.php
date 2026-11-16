<?php
$inicio = $dados['total'] > 0 ? $dados['deslocamento'] + 1 : 0;
$fim = min($dados['deslocamento'] + count($dados['usuarios']), $dados['total']);
$queryPaginacao = $dados['queryFiltros'];
$paginaAnterior = $dados['pagina'] - 1;
$proximaPagina = $dados['pagina'] + 1;
$usuariosJs = dirname(APP) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'user-management.js';
?>
<main class="usuarios-pagina">
    <header class="usuarios-cabecalho">
        <h1 class="usuarios-titulo">Usuários</h1>
        <form class="usuarios-filtros" method="get" action="<?= URL ?>/usuarios">
            <label class="usuarios-busca">
                <span class="sr-only">Buscar usuários por nome ou e-mail</span>
                <input type="search" name="busca" maxlength="100" value="<?= Html::e($dados['busca']) ?>" placeholder="Buscar por nome ou e-mail">
                <button type="submit" aria-label="Buscar">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="10.8" cy="10.8" r="6.4"></circle>
                        <path d="m15.5 15.5 5 5"></path>
                    </svg>
                </button>
            </label>
            <label class="usuarios-filtro-perfil">
                <span class="sr-only">Filtrar por perfil</span>
                <select name="perfil">
                    <option value=""<?= $dados['perfil'] === '' ? ' selected' : '' ?>>Perfil</option>
                    <option value="admin"<?= $dados['perfil'] === 'admin' ? ' selected' : '' ?>>Administrador</option>
                    <option value="padrao"<?= $dados['perfil'] === 'padrao' ? ' selected' : '' ?>>Padrão</option>
                </select>
            </label>
        </form>
    </header>

    <section class="usuarios-painel" aria-label="Lista de usuários">
        <div class="usuarios-tabela-wrap">
            <table class="usuarios-tabela">
                <thead>
                    <tr>
                        <th scope="col" class="usuarios-col-pessoa">Usuário</th>
                        <th scope="col" class="usuarios-col-email">E-mail</th>
                        <th scope="col" class="usuarios-col-perfil">Perfil</th>
                        <?php if ($dados['podeGerenciarNiveis']): ?>
                            <th scope="col" class="usuarios-col-acoes"><span class="sr-only">Ações</span></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$dados['usuarios']): ?>
                        <tr>
                            <td class="usuarios-vazio" colspan="<?= $dados['podeGerenciarNiveis'] ? '4' : '3' ?>">
                                Nenhum usuário encontrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dados['usuarios'] as $usuario): ?>
                            <tr>
                                <td class="usuarios-col-pessoa">
                                    <div class="usuarios-pessoa">
                                        <?php if (!empty($usuario->foto_perfil)): ?>
                                            <img class="usuarios-avatar" src="<?= URL ?>/<?= Html::e($usuario->foto_perfil) ?>" alt="" aria-hidden="true">
                                        <?php else: ?>
                                            <span class="usuarios-avatar" aria-hidden="true"><?= Html::e($usuario->iniciais) ?></span>
                                        <?php endif; ?>
                                        <span class="usuarios-nome" title="<?= Html::e($usuario->nome) ?>"><?= Html::e($usuario->nome) ?></span>
                                    </div>
                                </td>
                                <td class="usuarios-col-email">
                                    <span class="usuarios-email" title="<?= Html::e($usuario->email) ?>"><?= Html::e($usuario->email) ?></span>
                                </td>
                                <td class="usuarios-col-perfil">
                                    <?php if ($dados['podeGerenciarNiveis']): ?>
                                        <form class="usuarios-nivel-form" method="post" action="<?= URL ?>/usuarios/alterarNivel">
                                            <?= Csrf::campo() ?>
                                            <input type="hidden" name="usuario_id" value="<?= (int) $usuario->id ?>">
                                            <input type="hidden" name="busca" value="<?= Html::e($dados['busca']) ?>">
                                            <input type="hidden" name="perfil" value="<?= Html::e($dados['perfil']) ?>">
                                            <input type="hidden" name="pagina" value="<?= (int) $dados['pagina'] ?>">
                                            <select name="nivel"
                                                    aria-label="Perfil de <?= Html::e($usuario->nome) ?>"
                                                    data-perfil-original="<?= $usuario->administrador ? 'admin' : 'padrao' ?>">
                                                <option value="admin"<?= $usuario->administrador ? ' selected' : '' ?>>Administrador</option>
                                                    <option value="padrao"<?= !$usuario->administrador ? ' selected' : '' ?><?= $usuario->administrador && (int) $usuario->id !== (int) $dados['usuarioAtual'] ? ' disabled' : '' ?>>Padrão</option>
                                            </select>
                                            <button type="submit"
                                                    aria-label="Salvar perfil de <?= Html::e($usuario->nome) ?>"
                                                    title="Salvar perfil"
                                                    data-proprio="<?= (int) $usuario->id === (int) $dados['usuarioAtual'] ? '1' : '0' ?>"
                                                    disabled>
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M5 12.5 9.5 17 19 7.5"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="usuarios-perfil-badge<?= $usuario->administrador ? ' administrador' : '' ?>">
                                            <?= $usuario->administrador ? 'Administrador' : 'Padrão' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($dados['podeGerenciarNiveis']): ?>
                                    <td class="usuarios-col-acoes">
                                        <?php if ((int) $usuario->id !== (int) $dados['usuarioAtual']): ?>
                                            <form class="usuarios-excluir-form"
                                                  method="post"
                                                  action="<?= URL ?>/usuarios/excluir"
                                                  data-usuario-nome="<?= Html::e($usuario->nome) ?>">
                                                <?= Csrf::campo() ?>
                                                <input type="hidden" name="usuario_id" value="<?= (int) $usuario->id ?>">
                                                <input type="hidden" name="busca" value="<?= Html::e($dados['busca']) ?>">
                                                <input type="hidden" name="perfil" value="<?= Html::e($dados['perfil']) ?>">
                                                <input type="hidden" name="pagina" value="<?= (int) $dados['pagina'] ?>">
                                                <button type="submit"
                                                        aria-label="Excluir usuário <?= Html::e($usuario->nome) ?>"
                                                        title="Excluir usuário">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M4 7h16M10 11v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <footer class="usuarios-paginacao">
            <?php if ($dados['pagina'] > 1): ?>
                <a href="<?= URL ?>/usuarios?<?= Html::e($queryPaginacao) ?><?= $queryPaginacao !== '' ? '&amp;' : '' ?>pagina=<?= (int) $paginaAnterior ?>" aria-label="Página anterior">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14.5 5-7 7 7 7M8 12h12"></path></svg>
                </a>
            <?php else: ?>
                <button type="button" aria-label="Página anterior" disabled>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14.5 5-7 7 7 7M8 12h12"></path></svg>
                </button>
            <?php endif; ?>

            <span><?= (int) $inicio ?>–<?= (int) $fim ?> de <?= (int) $dados['total'] ?> usuários</span>

            <?php if ($dados['pagina'] < $dados['totalPaginas']): ?>
                <a href="<?= URL ?>/usuarios?<?= Html::e($queryPaginacao) ?><?= $queryPaginacao !== '' ? '&amp;' : '' ?>pagina=<?= (int) $proximaPagina ?>" aria-label="Próxima página">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.5 5 7 7-7 7M4 12h12"></path></svg>
                </a>
            <?php else: ?>
                <button type="button" aria-label="Próxima página" disabled>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.5 5 7 7-7 7M4 12h12"></path></svg>
                </button>
            <?php endif; ?>
        </footer>
    </section>
</main>
<script src="<?= URL ?>/js/user-management.js?v=<?= (int) filemtime($usuariosJs) ?>" defer></script>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Cadastro - Demandfy</title>
	<link rel="stylesheet" href="<?php echo URL; ?>/css/variables.css">
	<link rel="stylesheet" href="<?php echo URL; ?>/css/login.css">
</head>
<body>
	<div class="login-container">
		<div class="login-panel">
			<h1 class="login-title">Cadastro</h1>
            
			<form class="login-form" method="POST" action="#">
				<div class="form-group">
					<label for="nome" class="form-label">Nome completo</label>
					<input type="text" id="nome" name="nome" class="form-input" required>
				</div>

				<div class="form-group">
					<label for="email" class="form-label">E-mail</label>
					<input type="email" id="email" name="email" class="form-input" required>
				</div>

				<div class="form-group">
					<label for="telefone" class="form-label">Nr telefone</label>
					<input type="text" id="telefone" name="telefone" class="form-input" required>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="senha" class="form-label">Senha</label>
						<input type="password" id="senha" name="senha" class="form-input" required>
					</div>

					<div class="form-group">
						<label for="conf_senha" class="form-label">Confirmar senha</label>
						<input type="password" id="conf_senha" name="conf_senha" class="form-input" required>
					</div>
				</div>

				<button type="submit" class="btn-submit">Enviar</button>
			</form>

			<div class="login-footer">
				<p class="footer-section">Já tem login? <a href="<?php echo URL; ?>/login" class="link-blue">Log In</a></p>
			</div>
		</div>
	</div>

	<!-- Imagem decorativa na direita -->
	<div class="login-decoration">
		<img src="<?php echo URL; ?>/img/Rectangle 20.png" alt="Decoração" class="decoration-img">
	</div>

	<!-- Logo do Demandfy -->
	<div class="login-logo">
		<img src="<?php echo URL; ?>/img/DemandfyBig.png" alt="Demandfy Logo" class="logo-img">
		<div class="logo-texts">
			<p class="logo-line1">Sistema de Representação Estudantil</p>
			<p class="logo-line2">Plataforma digital para comunicação, análise e gerenciamento das demandas estudantis</p>
		</div>
	</div>

	<script>
		// Após cadastro redireciona para a página de login
		document.addEventListener('DOMContentLoaded', function () {
			var form = document.querySelector('.login-form');
			if (form) {
				form.addEventListener('submit', function (e) {
					e.preventDefault();
					window.location.href = '<?php echo URL; ?>/login';
				});
			}
		});
	</script>
    
</body>
</html>

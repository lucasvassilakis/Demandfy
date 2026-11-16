<?php

namespace App\Libraries;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../../vendor/autoload.php';

class EmailService
{
    private static function configuracao()
    {
        $host = trim((string) (getenv('DEMANDFY_SMTP_HOST') ?: ''));
        $usuario = trim((string) (getenv('DEMANDFY_SMTP_USER') ?: ''));
        $senha = (string) (getenv('DEMANDFY_SMTP_PASSWORD') ?: '');
        $porta = filter_var(getenv('DEMANDFY_SMTP_PORT') ?: '587', FILTER_VALIDATE_INT);
        $seguranca = strtolower(trim((string) (getenv('DEMANDFY_SMTP_SECURE') ?: 'tls')));
        $remetente = trim((string) (getenv('DEMANDFY_SMTP_FROM') ?: $usuario));
        $nomeRemetente = trim((string) (getenv('DEMANDFY_SMTP_FROM_NAME') ?: 'Demandfy'));

        if ($host === '' || $usuario === '' || $senha === ''
            || $porta === false || $porta < 1 || $porta > 65535
            || !in_array($seguranca, ['tls', 'ssl'], true)
            || !filter_var($remetente, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return [
            'host' => $host,
            'usuario' => $usuario,
            'senha' => $senha,
            'porta' => $porta,
            'seguranca' => $seguranca,
            'remetente' => $remetente,
            'nomeRemetente' => $nomeRemetente,
        ];
    }

    public static function estaConfigurado()
    {
        return self::configuracao() !== false;
    }

    public function enviarRecuperacaoSenha($emailDestino, $token)
    {
        $link = rtrim(URL, '/') . '/redefinir-senha?token=' . rawurlencode($token);
        $linkHtml = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
        $html = '<p>Recebemos uma solicitação para redefinir a senha da sua conta Demandfy.</p>'
            . '<p><a href="' . $linkHtml . '">Criar uma nova senha</a></p>'
            . '<p>Este link pode ser usado uma única vez e expira em 1 hora. '
            . 'Se você não solicitou a redefinição, ignore este e-mail.</p>';
        $texto = "Recebemos uma solicitação para redefinir a senha da sua conta Demandfy.\n\n"
            . "Acesse o link para criar uma nova senha: {$link}\n\n"
            . 'Este link pode ser usado uma única vez e expira em 1 hora. '
            . 'Se você não solicitou a redefinição, ignore este e-mail.';

        return $this->enviar($emailDestino, 'Recuperação de senha - Demandfy', $html, $texto);
    }

    public function enviarNotificacao($emailDestino, $titulo, $mensagem)
    {
        $tituloSeguro = htmlspecialchars((string) $titulo, ENT_QUOTES, 'UTF-8');
        $mensagemSegura = nl2br(htmlspecialchars((string) $mensagem, ENT_QUOTES, 'UTF-8'));
        $html = '<p>Você recebeu uma nova notificação no Demandfy:</p>'
            . '<h2>' . $tituloSeguro . '</h2><p>' . $mensagemSegura . '</p>'
            . '<p>Acesse o Demandfy para ver mais detalhes.</p>';
        $texto = (string) $titulo . "\n\n" . (string) $mensagem;

        return $this->enviar($emailDestino, 'Nova notificação - Demandfy', $html, $texto);
    }

    private function enviar($emailDestino, $assunto, $html, $texto)
    {
        $configuracao = self::configuracao();
        if ($configuracao === false) {
            error_log('Demandfy: serviço SMTP não configurado; o e-mail não foi enviado.');
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $configuracao['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $configuracao['usuario'];
            $mail->Password = $configuracao['senha'];
            $mail->SMTPSecure = $configuracao['seguranca'] === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $configuracao['porta'];
            $mail->Timeout = 15;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($configuracao['remetente'], $configuracao['nomeRemetente']);
            $mail->addAddress($emailDestino);
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $html;
            $mail->AltBody = $texto;
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Demandfy: falha no envio SMTP: ' . $mail->ErrorInfo);
            return false;
        }
    }
}

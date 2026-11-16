<?php
/*
 * HELPER CHECA (validações e formatações)
 * ------------------------------------------------------------------
 * MUDANÇAS em relação ao framework2026:
 *  - O nome da classe agora é "Checa" (antes "checa", o que quebra em servidor Linux).
 *  - Os métodos antigos checarNome()/checarEmail() devolviam TRUE quando o dado era
 *    INVÁLIDO (confuso). Agora os métodos se chamam nomeValido(), emailValido()... e
 *    devolvem TRUE quando o dado está CORRETO.
 *  - A expressão regular do nome tinha um erro (aA-zZ); foi refeita com \p{L} (qualquer letra).
 *  - Novos: telefone, data/hora relativa ("há 5 minutos") e rótulos.
 */
class Checa
{
    // Nome: só letras (com acento), espaços, apóstrofo e hífen; mínimo 3 caracteres.
    public static function nomeValido($nome)
    {
        return mb_strlen($nome) >= 3
            && preg_match('/^\p{L}+(?:[ \'\-]\p{L}+)*$/u', $nome) === 1;
    }

    public static function emailValido($email)
    {
        return filter_var(trim((string) $email), FILTER_VALIDATE_EMAIL) !== false;
    }

    // Normaliza e-mails para uso interno: trim + minúsculas. Isso faz o login ficar
    // mais amigável para o usuário e evita duplicidade de e-mails em caixa diferente.
    public static function emailPadrao($email)
    {
        return strtolower(trim((string) $email));
    }

    // Validação opcional para e-mails do Gmail. Útil quando a regra do sistema for
    // exigir contas Google, mas sem bloquear outros provedores por padrão.
    public static function gmailValido($email)
    {
        $email = self::emailPadrao($email);
        return self::emailValido($email) && preg_match('/@gmail\.com$/i', $email) === 1;
    }

    // Remove tudo que não é número. "(69) 99900-0000" -> "69999000000"
    public static function soDigitos($texto)
    {
        return preg_replace('/\D+/', '', (string) $texto);
    }

    // Telefone brasileiro com DDD: 10 dígitos (fixo) ou 11 (celular).
    // Também aceita entrada com DDI/DDD no padrão WhatsApp, como +55 69 99999-9999.
    public static function telefoneValido($telefone)
    {
        return self::telefonePadronizado($telefone) !== null;
    }

    // Gera um formato ideal: (69) 9 9999-9999 ou (69) 9999-9999.
    public static function telefonePadronizado($telefone)
    {
        $d = self::soDigitos((string) $telefone);

        // WhatsApp/BR costuma vir com +55; remove a parte internacional antes da validação.
        if (strlen($d) > 11 && substr($d, 0, 2) === '55') {
            $d = substr($d, 2);
        }

        if (strlen($d) === 11 && $d[2] === '9') {
            return '(' . substr($d, 0, 2) . ') ' . substr($d, 2, 1) . ' ' . substr($d, 3, 4) . '-' . substr($d, 7, 4);
        }

        if (strlen($d) === 10) {
            return '(' . substr($d, 0, 2) . ') ' . substr($d, 2, 4) . '-' . substr($d, 6, 4);
        }

        return null;
    }

    // Mostra o telefone formatado: 69999000000 -> (69) 9 9999-9999
    public static function telefoneFormatado($telefone)
    {
        $formatado = self::telefonePadronizado($telefone);
        return $formatado ?? (string) $telefone;
    }

    // Data do banco -> 31/12/2026 (ou 31/12/2026 14:30 se $comHora = true)
    public static function dataBr($data, $comHora = false)
    {
        if (empty($data)) {
            return '';
        }
        return date($comHora ? 'd/m/Y H:i' : 'd/m/Y', strtotime($data));
    }

    // Data do banco -> "Há 5 minutos", "Há 2 horas", "Há 3 dias"
    public static function tempoAtras($data)
    {
        $segundos = time() - strtotime($data);

        if ($segundos < 60) {
            return 'Agora mesmo';
        }
        $minutos = (int) floor($segundos / 60);
        if ($minutos < 60) {
            return 'Há ' . $minutos . ($minutos === 1 ? ' minuto' : ' minutos');
        }
        $horas = (int) floor($minutos / 60);
        if ($horas < 24) {
            return 'Há ' . $horas . ($horas === 1 ? ' hora' : ' horas');
        }
        $dias = (int) floor($horas / 24);
        if ($dias < 30) {
            return 'Há ' . $dias . ($dias === 1 ? ' dia' : ' dias');
        }
        return self::dataBr($data);
    }
}

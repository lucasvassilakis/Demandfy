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
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Remove tudo que não é número. "(69) 99900-0000" -> "69999000000"
    public static function soDigitos($texto)
    {
        return preg_replace('/\D+/', '', (string) $texto);
    }

    // Telefone brasileiro com DDD: 10 dígitos (fixo) ou 11 (celular).
    public static function telefoneValido($telefone)
    {
        $digitos = self::soDigitos($telefone);
        return strlen($digitos) === 10 || strlen($digitos) === 11;
    }

    // Mostra o telefone formatado: 69999000000 -> (69) 99900-0000
    public static function telefoneFormatado($telefone)
    {
        $d = self::soDigitos($telefone);
        if (strlen($d) === 11) {
            return '(' . substr($d, 0, 2) . ') ' . substr($d, 2, 5) . '-' . substr($d, 7);
        }
        if (strlen($d) === 10) {
            return '(' . substr($d, 0, 2) . ') ' . substr($d, 2, 4) . '-' . substr($d, 6);
        }
        return (string) $telefone;
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

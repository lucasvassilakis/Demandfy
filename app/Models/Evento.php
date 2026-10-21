<?php
/*
 * MODEL EVENTO (calendário, somente leitura no sistema).
 * Para cadastrar eventos, por enquanto, use o phpMyAdmin (tabela "eventos").
 */
class Evento
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Todos os eventos de um mês. $inicio e $fim no formato 'AAAA-MM-DD'.
    public function listarPorPeriodo($inicio, $fim)
    {
        $this->db->query('SELECT * FROM eventos
                          WHERE data_evento BETWEEN :inicio AND :fim
                          ORDER BY data_evento, hora_inicio, id');
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fim', $fim);
        return $this->db->resultados();
    }
}

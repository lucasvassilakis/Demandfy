<?php

class Rota
{
   private $controlador = 'Paginas';
   private $metodo = 'index';
   private $parametros = [];

   public function __construct()
   {
      $url = $this->url() ? $this->url() : [0];

      if (file_exists('../app/Controllers/' . ucwords($url[0]) . '.php')) {
         $this->controlador = ucwords($url[0]);
         unset($url[0]);
      }

      require_once '../app/Controllers/' . $this->controlador . '.php';
      $this->controlador = new $this->controlador;

      if (isset($url[0]) && method_exists($this->controlador, $url[0])) {
         $this->metodo = $url[0];
         unset($url[0]);
      } elseif (isset($url[1]) && method_exists($this->controlador, $url[1])) {
         $this->metodo = $url[1];
         unset($url[1]);
      }

      $this->parametros = $url ? array_values($url) : [];
      call_user_func_array([$this->controlador, $this->metodo], $this->parametros);
   }

   private function url()
   {
      $url = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL);

      if (isset($url)) {
         return explode('/', trim(rtrim($url, '/')));
      }
   }
}
<?php

namespace App\Controllers;

use App\core\SessionManager;
use App\core\Token;
use App\core\Request;
use App\core\View;
use App\core\Redirect;
use App\core\DB;
use Xinvoice;
        
class BusquedaController
{

    public function rango_fechas()
    {
        $dbSettings = array(
            'hostname' => 'localhost',
            'database' => 'sistema_apa',
            'username' => 'root',
            'password' => '',
            'dbtype'   => 'mysql',
        );

        $pdocrud = DB::PDOCrud(false, "","", $dbSettings);
        $render = $pdocrud->dbTable("servicio")->render();

        View::render('busqueda_rango_fechas', [
            'render' => $render
        ]);
    }
}
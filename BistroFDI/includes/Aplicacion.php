<?php
declare(strict_types=1);

class Aplicacion
{
    public const ATRIBUTOS_PETICION = 'attsPeticion';

    private static ?self $instancia = null;

    public static function getInstance(): self
    {
        if (!self::$instancia) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /** @var array{host:string,user:string,pass:string,bd:string} */
    private array $bdDatosConexion = [];

    private bool $inicializada = false;

    private ?mysqli $conn = null;

    private array $atributosPeticion = [];

    private function __construct() {}

    /**
     * @param array{host:string,user:string,pass:string,bd:string} $bdDatosConexion
     */
    public function init(array $bdDatosConexion): void
    {
        if ($this->inicializada) {
            return;
        }

        $this->bdDatosConexion = $bdDatosConexion;
        $this->inicializada = true;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->atributosPeticion = $_SESSION[self::ATRIBUTOS_PETICION] ?? [];
        unset($_SESSION[self::ATRIBUTOS_PETICION]);
    }

    private function compruebaInstanciaInicializada(): void
    {
        if (!$this->inicializada) {
            http_response_code(500);
            exit("Aplicacion no inicializada. Llama a \$app->init(...) en config.php");
        }
    }

    public function shutdown(): void
    {
        $this->compruebaInstanciaInicializada();

        if ($this->conn !== null && !$this->conn->connect_errno) {
            $this->conn->close();
        }
        $this->conn = null;
    }

    public function getConexionBd(): mysqli
    {
        $this->compruebaInstanciaInicializada();

        if ($this->conn === null) {
            $bdHost = $this->bdDatosConexion['host'];
            $bdUser = $this->bdDatosConexion['user'];
            $bdPass = $this->bdDatosConexion['pass'];
            $bdName = $this->bdDatosConexion['bd'];

            $conn = new mysqli($bdHost, $bdUser, $bdPass, $bdName);

            if ($conn->connect_errno) {
                http_response_code(500);
                exit("Error de conexión a la BD ({$conn->connect_errno}): {$conn->connect_error}");
            }

            if (!$conn->set_charset('utf8mb4')) {
                http_response_code(500);
                exit("Error al configurar charset ({$conn->errno}): {$conn->error}");
            }

            $this->conn = $conn;
        }

        return $this->conn;
    }

    // “Flash” (para mensajes tipo “Usuario creado”, etc.)
    public function putAtributoPeticion(string $clave, mixed $valor): void
    {
        if (!isset($_SESSION[self::ATRIBUTOS_PETICION])) {
            $_SESSION[self::ATRIBUTOS_PETICION] = [];
        }
        $_SESSION[self::ATRIBUTOS_PETICION][$clave] = $valor;
    }

    public function getAtributoPeticion(string $clave): mixed
    {
        $result = $this->atributosPeticion[$clave] ?? null;

        if ($result === null && isset($_SESSION[self::ATRIBUTOS_PETICION])) {
            $result = $_SESSION[self::ATRIBUTOS_PETICION][$clave] ?? null;
        }

        return $result;
    }
}
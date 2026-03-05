<?php
declare(strict_types=1);

abstract class FormularioBase
{
    protected string $formId;
    protected string $method;
    protected string $action;
    protected ?string $enctype;
    protected ?string $urlRedireccion;

    protected array $errores = [];

    protected bool $csrf;
    protected array $formAttrs;

    public function __construct(string $formId, array $opciones = [])
    {
        $this->formId = $formId;

        $opcionesPorDefecto = [
            'action' => null,                
            'method' => 'POST',
            'enctype' => null,
            'urlRedireccion' => null,

            'csrf' => true,
            'formAttrs' => [],              
        ];

        $opciones = array_merge($opcionesPorDefecto, $opciones);

        $this->method = strtoupper((string)$opciones['method']);
        $this->enctype = $opciones['enctype'] !== null ? (string)$opciones['enctype'] : null;
        $this->urlRedireccion = $opciones['urlRedireccion'] !== null ? (string)$opciones['urlRedireccion'] : null;

        $this->csrf = (bool)$opciones['csrf'];
        $this->formAttrs = is_array($opciones['formAttrs']) ? $opciones['formAttrs'] : [];

        $actionOpt = $opciones['action'];
        if (is_string($actionOpt) && $actionOpt !== '') {
            $this->action = $actionOpt;
        } else {
            $this->action = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function gestiona(): ?string
    {
        $datos = &$_POST;
        if ($this->method === 'GET') {
            $datos = &$_GET;
        }

        $this->errores = [];

        if (!$this->formularioEnviado($datos)) {
            return $this->generaFormulario();
        }

        if ($this->csrf && !$this->csrfValido($datos)) {
            $this->errores[] = 'La sesión ha caducado. Vuelve a intentarlo.';
            return $this->generaFormulario($datos);
        }

        $this->procesaFormulario($datos);

        if (count($this->errores) > 0) {
            return $this->generaFormulario($datos);
        }

        if ($this->urlRedireccion !== null) {
            header("Location: {$this->urlRedireccion}");
            exit();
        }

        return null;
    }

    private function formularioEnviado(array &$datos): bool
    {
        return isset($datos['formId']) && $datos['formId'] === $this->formId;
    }

    private function generaFormulario(array &$datos = []): string
    {
        $htmlCampos = $this->generaCamposFormulario($datos);

        $enctypeAtt = $this->enctype ? " enctype=\"{$this->enctype}\"" : '';
        $attrs = $this->renderFormAttrs();

        $csrfInput = '';
        if ($this->csrf) {
            $token = $this->csrfTokenGenera();
            $csrfInput = "<input type=\"hidden\" name=\"csrf\" value=\"{$token}\">";
        }

        return <<<HTML
<form method="{$this->method}" action="{$this->action}" id="{$this->formId}"{$enctypeAtt}{$attrs}>
  <input type="hidden" name="formId" value="{$this->formId}">
  {$csrfInput}
  {$htmlCampos}
</form>
HTML;
    }

    private function renderFormAttrs(): string
    {
        if (empty($this->formAttrs)) return '';

        $out = '';
        foreach ($this->formAttrs as $k => $v) {
            if (!is_string($k)) continue;
            $k = preg_replace('/[^a-zA-Z0-9_\-:]/', '', $k);
            if ($k === '') continue;

            $v = htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $out .= " {$k}=\"{$v}\"";
        }
        return $out;
    }

    private function csrfTokenGenera(): string
    {
        $key = "csrf_{$this->formId}";
        if (empty($_SESSION[$key]) || !is_string($_SESSION[$key])) {
            $_SESSION[$key] = bin2hex(random_bytes(16));
        }
        return $_SESSION[$key];
    }

    private function csrfValido(array $datos): bool
    {
        $key = "csrf_{$this->formId}";
        $token = $datos['csrf'] ?? '';
        return isset($_SESSION[$key]) && is_string($_SESSION[$key]) && is_string($token) && hash_equals($_SESSION[$key], $token);
    }

    public static function generaListaErroresGlobales(array $errores = []): string
    {
        $keys = array_filter(array_keys($errores), fn($k) => is_int($k) || ctype_digit((string)$k));
        if (count($keys) === 0) return '';

        $html = '<ul class="errores">';
        foreach ($keys as $k) {
            $msg = htmlspecialchars((string)$errores[$k], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= "<li>{$msg}</li>";
        }
        $html .= '</ul>';
        return $html;
    }

    public static function generarError(string $campo, array $errores): string
    {
        if (!isset($errores[$campo])) return '';
        $msg = htmlspecialchars((string)$errores[$campo], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return "<span class=\"form-field-error\">{$msg}</span>";
    }

    public static function generaErroresCampos(array $campos, array $errores): array
    {
        $out = [];
        foreach ($campos as $campo) {
            $out[(string)$campo] = self::generarError((string)$campo, $errores);
        }
        return $out;
    }

    abstract protected function generaCamposFormulario(array &$datos): string;
    abstract protected function procesaFormulario(array &$datos): void;
}
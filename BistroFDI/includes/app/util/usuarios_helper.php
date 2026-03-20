<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/dto/UsuarioDTO.php';

function usuarioRolPrincipal(UsuarioDTO $usuario): string
{
    $roles = $usuario->getRoles();
    return (count($roles) > 0) ? (string)$roles[0]->getNombre() : 'cliente';
}

function usuarioUrlEditar(UsuarioDTO $usuario): string
{
    return RUTA_VISTAS . '/editar_perfil.php?id=' . (int)$usuario->getId();
}

function usuarioUrlCambiarRol(UsuarioDTO $usuario): string
{
    return RUTA_VISTAS . '/gerente/cambiar_rol.php?id=' . (int)$usuario->getId();
}

function usuarioNombreVisible(UsuarioDTO $usuario): string
{
    return (string)$usuario->getNombreUsuario();
}

function usuarioId(UsuarioDTO $usuario): int
{
    return (int)$usuario->getId();
}

function usuarioEsSesionActual(UsuarioDTO $usuario): bool
{
    $idSesion = (int)($_SESSION['usuario_id'] ?? 0);
    return $idSesion > 0 && $idSesion === (int)$usuario->getId();
}

function usuarioViewData(UsuarioDTO $usuario): array
{
    return [
        'id' => usuarioId($usuario),
        'nombreUsuario' => usuarioNombreVisible($usuario),
        'rol' => usuarioRolPrincipal($usuario),
        'urlEditar' => usuarioUrlEditar($usuario),
        'urlCambiarRol' => usuarioUrlCambiarRol($usuario),
        'esSesionActual' => usuarioEsSesionActual($usuario),
    ];
}
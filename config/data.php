<?php
/**
 * AUTOLAND — Clase Data v2
 * Admin ve y gestiona TODO.
 * Vendedor ve y gestiona SOLO lo que él registró.
 */
require_once __DIR__ . '/conexion.php';

class Data
{
    private $con;

    public function __construct()
    {
        $this->con = (new Conexion())->getConnection();
    }

    private function esAdmin($rol): bool { return $rol === 'admin'; }

    /* ══════════ USUARIO ══════════ */

    public function listarVendedores()
    {
        $stmt = $this->con->prepare(
            "SELECT idUsuario, nombreReal, usuario, activo, fechaCreado
             FROM usuario WHERE rol = 'vendedor' ORDER BY fechaCreado DESC");
        $stmt->execute();
        return $stmt;
    }

    public function crearVendedor($nombreReal, $usuario, $password)
    {
        $stmt = $this->con->prepare(
            "INSERT INTO usuario (nombreReal, usuario, password, rol)
             VALUES (?, ?, ?, 'vendedor')");
        $stmt->execute([$nombreReal, $usuario, password_hash($password, PASSWORD_DEFAULT)]);
        return $stmt;
    }

    public function toggleVendedor($idUsuario, $activo)
    {
        $stmt = $this->con->prepare(
            "UPDATE usuario SET activo = ? WHERE idUsuario = ? AND rol = 'vendedor'");
        $stmt->execute([$activo, $idUsuario]);
        return $stmt;
    }

    public function eliminarVendedor($idUsuario)
    {
        $stmt = $this->con->prepare(
            "DELETE FROM usuario WHERE idUsuario = ? AND rol = 'vendedor'");
        $stmt->execute([$idUsuario]);
        return $stmt;
    }

    public function usuarioExiste($usuario): bool
    {
        $stmt = $this->con->prepare("SELECT COUNT(*) FROM usuario WHERE usuario = ?");
        $stmt->execute([$usuario]);
        return (bool) $stmt->fetchColumn();
    }

    public function cambiarPasswordAdmin($idUsuario, $nuevaPass)
    {
        $stmt = $this->con->prepare("UPDATE usuario SET password = ? WHERE idUsuario = ?");
        $stmt->execute([password_hash($nuevaPass, PASSWORD_DEFAULT), $idUsuario]);
        return $stmt;
    }

    public function contarVendedores()
    {
        $stmt = $this->con->prepare("SELECT COUNT(*) FROM usuario WHERE rol='vendedor' AND activo=1");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /* ══════════ INDIVIDUO ══════════ */

    public function insertIndividuo($nombres, $apellidoP, $apellidoM, $dni, $telefono, $direccion, $edad, $sexo, $idUsuario)
    {
        $stmt = $this->con->prepare(
            "INSERT INTO individuo
                (nombreIndividuo,apellidoPaterno,apellidoMaterno,
                 dni,telefono,direccion,edadIndividuo,sexoIndividuo,idUsuario)
             VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$nombres,$apellidoP,$apellidoM,$dni,$telefono,$direccion,$edad,$sexo,$idUsuario]);
        return $stmt;
    }

    public function listarIndividuo($idUsuario, $rol)
    {
        if ($this->esAdmin($rol)) {
            $sql = "SELECT i.*, u.nombreReal AS registradoPor
                    FROM individuo i LEFT JOIN usuario u ON i.idUsuario=u.idUsuario
                    ORDER BY i.apellidoPaterno";
            $stmt = $this->con->prepare($sql); $stmt->execute();
        } else {
            $sql = "SELECT i.*, u.nombreReal AS registradoPor
                    FROM individuo i LEFT JOIN usuario u ON i.idUsuario=u.idUsuario
                    WHERE i.idUsuario=? ORDER BY i.apellidoPaterno";
            $stmt = $this->con->prepare($sql); $stmt->execute([$idUsuario]);
        }
        return $stmt;
    }

    public function buscarIndividuo($id)
    {
        $stmt = $this->con->prepare("SELECT * FROM individuo WHERE idIndividuo=?");
        $stmt->execute([$id]); return $stmt;
    }

    public function buscarIndividuoPorTexto($texto, $idUsuario, $rol)
    {
        $like = "%$texto%";
        if ($this->esAdmin($rol)) {
            $sql = "SELECT i.*, u.nombreReal AS registradoPor
                    FROM individuo i LEFT JOIN usuario u ON i.idUsuario=u.idUsuario
                    WHERE i.nombreIndividuo LIKE ? OR i.apellidoPaterno LIKE ?
                       OR i.apellidoMaterno LIKE ? OR i.dni LIKE ?
                    ORDER BY i.apellidoPaterno";
            $stmt = $this->con->prepare($sql); $stmt->execute([$like,$like,$like,$like]);
        } else {
            $sql = "SELECT i.*, u.nombreReal AS registradoPor
                    FROM individuo i LEFT JOIN usuario u ON i.idUsuario=u.idUsuario
                    WHERE i.idUsuario=?
                      AND (i.nombreIndividuo LIKE ? OR i.apellidoPaterno LIKE ?
                        OR i.apellidoMaterno LIKE ? OR i.dni LIKE ?)
                    ORDER BY i.apellidoPaterno";
            $stmt = $this->con->prepare($sql); $stmt->execute([$idUsuario,$like,$like,$like,$like]);
        }
        return $stmt;
    }

    public function actualizarIndividuo($id,$nombres,$apellidoP,$apellidoM,$dni,$telefono,$direccion,$edad,$sexo,$idUsuario,$rol)
    {
        if (!$this->esAdmin($rol)) {
            $c = $this->con->prepare("SELECT idUsuario FROM individuo WHERE idIndividuo=?");
            $c->execute([$id]); $r = $c->fetch();
            if (!$r || $r['idUsuario'] != $idUsuario) return false;
        }
        $stmt = $this->con->prepare(
            "UPDATE individuo SET nombreIndividuo=?,apellidoPaterno=?,apellidoMaterno=?,
             dni=?,telefono=?,direccion=?,edadIndividuo=?,sexoIndividuo=? WHERE idIndividuo=?");
        $stmt->execute([$nombres,$apellidoP,$apellidoM,$dni,$telefono,$direccion,$edad,$sexo,$id]);
        return $stmt;
    }

    public function eliminarIndividuo($id, $idUsuario, $rol)
    {
        if (!$this->esAdmin($rol)) {
            $c = $this->con->prepare("SELECT idUsuario FROM individuo WHERE idIndividuo=?");
            $c->execute([$id]); $r = $c->fetch();
            if (!$r || $r['idUsuario'] != $idUsuario) return false;
        }
        $stmt = $this->con->prepare("DELETE FROM individuo WHERE idIndividuo=?");
        $stmt->execute([$id]); return $stmt;
    }

    public function contarIndividuos($idUsuario, $rol)
    {
        if ($this->esAdmin($rol)) {
            $stmt = $this->con->prepare("SELECT COUNT(*) FROM individuo");
            $stmt->execute();
        } else {
            $stmt = $this->con->prepare("SELECT COUNT(*) FROM individuo WHERE idUsuario=?");
            $stmt->execute([$idUsuario]);
        }
        return (int) $stmt->fetchColumn();
    }

    /* ══════════ AUTO ══════════ */

    public function insertAuto($marca,$modelo,$anio,$color,$precio,$kilometraje,$combustible,$idIndividuo,$idUsuario)
    {
        $stmt = $this->con->prepare(
            "INSERT INTO auto (marca,modelo,anio,color,precio,kilometraje,combustible,idIndividuo,idUsuario)
             VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$marca,$modelo,$anio,$color,$precio,$kilometraje,$combustible,$idIndividuo,$idUsuario]);
        return $stmt;
    }

    public function listarAuto($idUsuario, $rol)
    {
        if ($this->esAdmin($rol)) {
            $sql = "SELECT a.*, CONCAT(i.nombreIndividuo,' ',i.apellidoPaterno) AS propietario,
                           u.nombreReal AS registradoPor
                    FROM auto a
                    LEFT JOIN individuo i ON a.idIndividuo=i.idIndividuo
                    LEFT JOIN usuario   u ON a.idUsuario=u.idUsuario
                    ORDER BY a.marca,a.modelo";
            $stmt = $this->con->prepare($sql); $stmt->execute();
        } else {
            $sql = "SELECT a.*, CONCAT(i.nombreIndividuo,' ',i.apellidoPaterno) AS propietario,
                           u.nombreReal AS registradoPor
                    FROM auto a
                    LEFT JOIN individuo i ON a.idIndividuo=i.idIndividuo
                    LEFT JOIN usuario   u ON a.idUsuario=u.idUsuario
                    WHERE a.idUsuario=? ORDER BY a.marca,a.modelo";
            $stmt = $this->con->prepare($sql); $stmt->execute([$idUsuario]);
        }
        return $stmt;
    }

    public function buscarAuto($idAuto)
    {
        $stmt = $this->con->prepare(
            "SELECT a.*, CONCAT(i.nombreIndividuo,' ',i.apellidoPaterno) AS propietario
             FROM auto a LEFT JOIN individuo i ON a.idIndividuo=i.idIndividuo
             WHERE a.idAuto=?");
        $stmt->execute([$idAuto]); return $stmt;
    }

    public function buscarAutoPorTexto($texto, $idUsuario, $rol)
    {
        $like = "%$texto%";
        if ($this->esAdmin($rol)) {
            $sql = "SELECT a.*, CONCAT(i.nombreIndividuo,' ',i.apellidoPaterno) AS propietario
                    FROM auto a LEFT JOIN individuo i ON a.idIndividuo=i.idIndividuo
                    WHERE a.marca LIKE ? OR a.modelo LIKE ? OR a.color LIKE ?
                    ORDER BY a.marca";
            $stmt = $this->con->prepare($sql); $stmt->execute([$like,$like,$like]);
        } else {
            $sql = "SELECT a.*, CONCAT(i.nombreIndividuo,' ',i.apellidoPaterno) AS propietario
                    FROM auto a LEFT JOIN individuo i ON a.idIndividuo=i.idIndividuo
                    WHERE a.idUsuario=? AND (a.marca LIKE ? OR a.modelo LIKE ? OR a.color LIKE ?)
                    ORDER BY a.marca";
            $stmt = $this->con->prepare($sql); $stmt->execute([$idUsuario,$like,$like,$like]);
        }
        return $stmt;
    }

    public function actualizarAuto($idAuto,$marca,$modelo,$anio,$color,$precio,$kilometraje,$combustible,$idIndividuo,$idUsuario,$rol)
    {
        if (!$this->esAdmin($rol)) {
            $c = $this->con->prepare("SELECT idUsuario FROM auto WHERE idAuto=?");
            $c->execute([$idAuto]); $r = $c->fetch();
            if (!$r || $r['idUsuario'] != $idUsuario) return false;
        }
        $stmt = $this->con->prepare(
            "UPDATE auto SET marca=?,modelo=?,anio=?,color=?,precio=?,
             kilometraje=?,combustible=?,idIndividuo=? WHERE idAuto=?");
        $stmt->execute([$marca,$modelo,$anio,$color,$precio,$kilometraje,$combustible,$idIndividuo,$idAuto]);
        return $stmt;
    }

    public function eliminarAuto($idAuto, $idUsuario, $rol)
    {
        if (!$this->esAdmin($rol)) {
            $c = $this->con->prepare("SELECT idUsuario FROM auto WHERE idAuto=?");
            $c->execute([$idAuto]); $r = $c->fetch();
            if (!$r || $r['idUsuario'] != $idUsuario) return false;
        }
        $stmt = $this->con->prepare("DELETE FROM auto WHERE idAuto=?");
        $stmt->execute([$idAuto]); return $stmt;
    }

    public function contarAutos($idUsuario, $rol)
    {
        if ($this->esAdmin($rol)) {
            $stmt = $this->con->prepare("SELECT COUNT(*) FROM auto");
            $stmt->execute();
        } else {
            $stmt = $this->con->prepare("SELECT COUNT(*) FROM auto WHERE idUsuario=?");
            $stmt->execute([$idUsuario]);
        }
        return (int) $stmt->fetchColumn();
    }

    public function valorInventario($idUsuario, $rol)
    {
        if ($this->esAdmin($rol)) {
            $stmt = $this->con->prepare("SELECT COALESCE(SUM(precio),0) FROM auto");
            $stmt->execute();
        } else {
            $stmt = $this->con->prepare("SELECT COALESCE(SUM(precio),0) FROM auto WHERE idUsuario=?");
            $stmt->execute([$idUsuario]);
        }
        return (float) $stmt->fetchColumn();
    }

    public function top5AutosMasCaros($idUsuario, $rol)
    {
        if ($this->esAdmin($rol)) {
            $sql = "SELECT CONCAT(marca,' ',modelo) AS nombre, precio
                    FROM auto ORDER BY precio DESC LIMIT 5";
            $stmt = $this->con->prepare($sql); $stmt->execute();
        } else {
            $sql = "SELECT CONCAT(marca,' ',modelo) AS nombre, precio
                    FROM auto WHERE idUsuario=? ORDER BY precio DESC LIMIT 5";
            $stmt = $this->con->prepare($sql); $stmt->execute([$idUsuario]);
        }
        return $stmt->fetchAll();
    }

    public function listarIndividuoSelect($idUsuario, $rol)
    {
        if ($this->esAdmin($rol)) {
            $sql = "SELECT idIndividuo, CONCAT(nombreIndividuo,' ',apellidoPaterno) AS nombre
                    FROM individuo ORDER BY apellidoPaterno";
            $stmt = $this->con->prepare($sql); $stmt->execute();
        } else {
            $sql = "SELECT idIndividuo, CONCAT(nombreIndividuo,' ',apellidoPaterno) AS nombre
                    FROM individuo WHERE idUsuario=? ORDER BY apellidoPaterno";
            $stmt = $this->con->prepare($sql); $stmt->execute([$idUsuario]);
        }
        return $stmt->fetchAll();
    }
}

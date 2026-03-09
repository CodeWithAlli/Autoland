<?php
class Individuo
{
    protected $id;
    protected $nombres;
    protected $apellidoP;
    protected $apellidoM;
    protected $dni;
    protected $telefono;
    protected $direccion;
    protected $edad;
    protected $sexo;

    //constructor vacio y get/set

    public function __construct() {}

    public function getId()
    {
        return $this->id;
    }
    public function setId($id): void
    {
        $this->id = $id;
    }
    public function getNombres()
    {
        return $this->nombres;
    }
    public function setNombres($nombres): void
    {
        $this->nombres = $nombres;
    }
    public function getApellidoP()
    {
        return $this->apellidoP;
    }
    public function setApellidoP($apellidoP): void
    {
        $this->apellidoP = $apellidoP;
    }
    public function getApellidoM()
    {
        return $this->apellidoM;
    }
    public function setApellidoM($apellidoM): void
    {
        $this->apellidoM = $apellidoM;
    }
    public function getDni()
    {
        return $this->dni;
    }
    public function setDni($dni): void
    {
        $this->dni = $dni;
    }
    public function getTelefono()
    {
        return $this->telefono;
    }
    public function setTelefono($telefono): void
    {
        $this->telefono = $telefono;
    }
    public function getDireccion()
    {
        return $this->direccion;
    }
    public function setDireccion($direccion): void
    {
        $this->direccion = $direccion;
    }
    public function getEdad()
    {
        return $this->edad;
    }
    public function setEdad($edad): void
    {
        if (is_numeric($edad) && $edad >= 0) { //ayuda a evitar numeros negativos
            $this->edad = $edad;
        }
    }
    public function getSexo()
    {
        return $this->sexo;
    }
    public function setSexo($sexo): void
    {
        $this->sexo = $sexo;
    }

    //tostring Define la forma en que un objeto se convierte a texto
    public function __toString()
    {
        return
            "ID: " . $this->id . "<br>" .
            "Nombres: " . $this->nombres . "<br>" .
            "Apellido Paterno: " . $this->apellidoP . "<br>" .
            "Apellido Materno: " . $this->apellidoM . "<br>" .
            "DNI: " . $this->dni . "<br>" .
            "Teléfono: " . $this->telefono . "<br>" .
            "Dirección: " . $this->direccion . "<br>" .
            "Edad: " . $this->edad . "<br>" .
            "Sexo: " . $this->sexo;
    }
} //fin de clase






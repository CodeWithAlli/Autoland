import { useEffect, useState } from "react";
import { supabase } from "../lib/supabase";
import { useAuth } from "../context/AuthContext";
import {
  IconoPersonas,
  IconoAuto,
  IconoMoneda,
  IconoUsuarios,
  IconoGrafico,
} from "../components/Iconos";
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  PieChart,
  Pie,
  Cell,
  Legend,
} from "recharts";

const COLORES_PASTEL = ["#b5651d", "#15202b", "#5b6472", "#1d7a4c", "#9aa1ac"];

function formatoMoneda(valor) {
  return new Intl.NumberFormat("es-PE", {
    style: "currency",
    currency: "PEN",
    maximumFractionDigits: 0,
  }).format(valor || 0);
}

export default function Dashboard() {
  const { perfil, esAdmin } = useAuth();

  const [stats, setStats] = useState({
    clientes: 0,
    autos: 0,
    valorInventario: 0,
    vendedores: 0,
    negociaciones: 0,
    vendidas: 0,
  });

  const [top5, setTop5] = useState([]);
  const [distribCombustible, setDistribCombustible] = useState([]);
  const [distribNegociaciones, setDistribNegociaciones] = useState([]);
  const [cargando, setCargando] = useState(true);

  useEffect(() => {
    cargarDatos();
  }, [perfil]);

  async function cargarDatos() {
    if (!perfil) return;

    setCargando(true);

    try {
      const filtroDueno = esAdmin ? {} : { creado_por: perfil.id };

      const [
        { count: clientes },
        { count: autos },
        autosData,
        negociacionesData,
      ] = await Promise.all([
        supabase
          .from("individuos")
          .select("id", { count: "exact", head: true })
          .match(filtroDueno),
        supabase
          .from("autos")
          .select("id", { count: "exact", head: true })
          .match(filtroDueno),
        supabase
          .from("autos")
          .select("marca, modelo, precio, combustible")
          .match(filtroDueno),
        supabase.from("negociaciones").select("estado").match(filtroDueno),
      ]);

      const autosLista = autosData.data || [];
      const negociacionesLista = negociacionesData.data || [];

      const valorInventario = autosLista.reduce(
        (acc, a) => acc + Number(a.precio || 0),
        0,
      );
      let vendedores = 0;

      if (esAdmin) {
        const { count } = await supabase
          .from("perfiles")
          .select("id", { count: "exact", head: true })
          .eq("rol", "vendedor")
          .eq("activo", true);

        vendedores = count || 0;
      }

      const top5Ordenado = [...autosLista]
        .sort((a, b) => Number(b.precio) - Number(a.precio))
        .slice(0, 5)
        .map((a) => ({
          nombre: `${a.marca} ${a.modelo}`,
          precio: Number(a.precio),
        }));

      const porCombustible = {};

      autosLista.forEach((a) => {
        const key = a.combustible || "No especificado";
        porCombustible[key] = (porCombustible[key] || 0) + 1;
      });

      const distribComb = Object.entries(porCombustible).map(
        ([nombre, valor]) => ({ nombre, valor }),
      );

      const totalNegociaciones = negociacionesLista.length;

      const ventasCerradas = negociacionesLista.filter(
        (n) => n.estado === "Vendido",
      ).length;

      const porEstado = {};

      negociacionesLista.forEach((n) => {
        const estado = n.estado || "Sin estado";
        porEstado[estado] = (porEstado[estado] || 0) + 1;
      });

      const distribEstados = Object.entries(porEstado).map(
        ([nombre, valor]) => ({ nombre, valor }),
      );

      setStats({
        clientes: clientes || 0,
        autos: autos || 0,
        valorInventario,
        vendedores,
        negociaciones: totalNegociaciones,
        vendidas: ventasCerradas,
      });

      setTop5(top5Ordenado);
      setDistribCombustible(distribComb);
      setDistribNegociaciones(distribEstados);
    } catch (error) {
      console.error(error);
    } finally {
      setCargando(false);
    }
  }

  return (
    <div>
      <div className="kpi-grid">
        <div className="kpi">
          <div className="kpi-label">
            <IconoPersonas size={14} /> Clientes
          </div>
          <div className="kpi-valor">{stats.clientes}</div>
          <div className="kpi-pie">
            {esAdmin ? "Total registrados" : "Registrados por ti"}
          </div>
        </div>
        <div className="kpi">
          <div className="kpi-label">
            <IconoAuto size={14} /> Autos en inventario
          </div>
          <div className="kpi-valor">{stats.autos}</div>
          <div className="kpi-pie">
            {esAdmin ? "Total en el sistema" : "Registrados por ti"}
          </div>
        </div>
        <div className="kpi">
          <div className="kpi-label">
            <IconoMoneda size={14} /> Valor del inventario
          </div>
          <div className="kpi-valor" style={{ fontSize: 22 }}>
            {formatoMoneda(stats.valorInventario)}
          </div>
          <div className="kpi-pie">Suma de precios registrados</div>
        </div>
        <div className="kpi">
          <div className="kpi-label">
            <IconoGrafico size={14} /> Negociaciones
          </div>
          <div className="kpi-valor">{stats.negociaciones}</div>
          <div className="kpi-pie">Consultas y ventas registradas</div>
        </div>
        <div className="kpi">
          <div className="kpi-label">
            <IconoMoneda size={14} /> Ventas cerradas
          </div>

          <div className="kpi-valor">{stats.vendidas}</div>

          <div className="kpi-pie">Negociaciones completadas</div>
        </div>
        {esAdmin && (
          <div className="kpi">
            <div className="kpi-label">
              <IconoUsuarios size={14} /> Vendedores activos
            </div>
            <div className="kpi-valor">{stats.vendedores}</div>
            <div className="kpi-pie">Cuentas habilitadas</div>
          </div>
        )}
      </div>

      <div className="dos-columnas">
        <div className="card">
          <div className="card-header">
            <div>
              <div className="card-titulo">Top 5 — autos más caros</div>
              <div className="card-subtitulo">
                Por precio de venta registrado
              </div>
            </div>
            <IconoGrafico size={18} className="celda-suave" />
          </div>
          <div className="card-body">
            {top5.length === 0 ? (
              <EstadoVacioGrafico cargando={cargando} />
            ) : (
              <ResponsiveContainer width="100%" height={260}>
                <BarChart
                  data={top5}
                  layout="vertical"
                  margin={{ left: 10, right: 20 }}
                >
                  <CartesianGrid stroke="#eef1f5" horizontal={false} />
                  <XAxis
                    type="number"
                    tickFormatter={(v) => formatoMoneda(v)}
                    fontSize={11}
                    stroke="#9aa1ac"
                  />
                  <YAxis
                    type="category"
                    dataKey="nombre"
                    width={140}
                    fontSize={12}
                    stroke="#5b6472"
                  />
                  <Tooltip formatter={(v) => formatoMoneda(v)} />
                  <Bar
                    dataKey="precio"
                    fill="#b5651d"
                    radius={[0, 4, 4, 0]}
                    barSize={22}
                  />
                </BarChart>
              </ResponsiveContainer>
            )}
          </div>
        </div>

        <div className="card">
          <div className="card-header">
            <div>
              <div className="card-titulo">Distribución por combustible</div>
              <div className="card-subtitulo">Composición del inventario</div>
            </div>
          </div>

          <div className="card-body">
            {distribCombustible.length === 0 ? (
              <EstadoVacioGrafico cargando={cargando} />
            ) : (
              <ResponsiveContainer width="100%" height={260}>
                <PieChart>
                  <Pie
                    data={distribCombustible}
                    dataKey="valor"
                    nameKey="nombre"
                    innerRadius={55}
                    outerRadius={85}
                    paddingAngle={2}
                  >
                    {distribCombustible.map((_, i) => (
                      <Cell
                        key={i}
                        fill={COLORES_PASTEL[i % COLORES_PASTEL.length]}
                      />
                    ))}
                  </Pie>

                  <Legend
                    iconType="circle"
                    iconSize={8}
                    wrapperStyle={{ fontSize: 12 }}
                  />

                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            )}
          </div>
        </div>

        <div className="card">
          <div className="card-header">
            <div>
              <div className="card-titulo">Negociaciones por estado</div>

              <div className="card-subtitulo">
                Estado actual de las negociaciones
              </div>
            </div>
          </div>

          <div className="card-body">
            {distribNegociaciones.length === 0 ? (
              <EstadoVacioGrafico cargando={cargando} />
            ) : (
              <ResponsiveContainer width="100%" height={260}>
                <PieChart>
                  <Pie
                    data={distribNegociaciones}
                    dataKey="valor"
                    nameKey="nombre"
                    innerRadius={55}
                    outerRadius={85}
                    paddingAngle={2}
                  >
                    {distribNegociaciones.map((_, i) => (
                      <Cell
                        key={i}
                        fill={COLORES_PASTEL[i % COLORES_PASTEL.length]}
                      />
                    ))}
                  </Pie>

                  <Legend
                    iconType="circle"
                    iconSize={8}
                    wrapperStyle={{
                      fontSize: 12,
                    }}
                  />

                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

function EstadoVacioGrafico({ cargando }) {
  return (
    <div
      className="vacio"
      style={{
        height: 220,
        display: "flex",
        flexDirection: "column",
        justifyContent: "center",
      }}
    >
      <div className="vacio-titulo">
        {cargando ? "Cargando…" : "Aún no hay datos suficientes"}
      </div>
      {!cargando && (
        <div className="vacio-sub">
          Registra autos para ver estadísticas aquí.
        </div>
      )}
    </div>
  );
}
